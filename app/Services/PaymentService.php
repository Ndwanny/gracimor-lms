<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Loan;
use App\Models\LoanBalance;
use App\Models\LoanSchedule;
use App\Models\Payment;
use App\Models\PaymentAllocation;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    public function __construct(
        private readonly LoanService $loanService
    ) {}

    // ── Receipt number ────────────────────────────────────────────────────

    public function generateReceiptNumber(): string
    {
        $last = Payment::withTrashed()
            ->orderByDesc('id')
            ->lockForUpdate()
            ->value('receipt_number');

        if (! $last) {
            return 'RCP-00001';
        }

        $n = (int) substr($last, 4);

        return 'RCP-' . str_pad($n + 1, 5, '0', STR_PAD_LEFT);
    }

    // ── Record payment ────────────────────────────────────────────────────

    /**
     * Record a payment against a loan.
     *
     * Steps:
     *  1. Validate the loan is active and the amount is positive
     *  2. Determine allocation: penalty → interest → principal (oldest-first)
     *  3. Persist Payment, PaymentAllocation rows
     *  4. Update LoanSchedule statuses
     *  5. Upsert LoanBalance
     *  6. Close loan if balance reaches zero
     *
     * @param  Loan   $loan
     * @param  User   $by
     * @param  array  $data  Keys: amount_received, payment_method, payment_date,
     *                             payment_reference, payment_type, notes
     * @return Payment
     */
    public function record(Loan $loan, User $by, array $data): Payment
    {
        if (! $loan->canRecordPayment()) {
            throw ValidationException::withMessages([
                'loan' => "Payments cannot be recorded against loan {$loan->loan_number} (status: {$loan->status}).",
            ]);
        }

        $amountReceived = (float) $data['amount_received'];

        if ($amountReceived <= 0) {
            throw ValidationException::withMessages(['amount_received' => 'Payment amount must be greater than zero.']);
        }

        return DB::transaction(function () use ($loan, $by, $data, $amountReceived) {

            $balance = $loan->balance;

            if (! $balance) {
                throw ValidationException::withMessages(['loan' => 'Loan balance record not found. Please contact support.']);
            }

            $balanceBefore = (float) $balance->total_outstanding;

            // ── Allocate payment ──────────────────────────────────────
            [$allocations, $breakdown] = $this->allocate($loan, $amountReceived);

            // ── Persist payment ───────────────────────────────────────
            $payment = Payment::create([
                'receipt_number'   => $this->generateReceiptNumber(),
                'loan_id'          => $loan->id,
                'borrower_id'      => $loan->borrower_id,
                'amount_received'  => $amountReceived,
                'towards_principal'=> $breakdown['principal'],
                'towards_interest' => $breakdown['interest'],
                'towards_penalty'  => $breakdown['penalty'],
                'overpayment'      => max(0, $breakdown['overpayment']),
                'balance_before'   => $balanceBefore,
                'balance_after'    => max(0, $balanceBefore - $amountReceived + max(0, $breakdown['overpayment'])),
                'payment_type'     => $data['payment_type'] ?? $this->resolvePaymentType($allocations),
                'payment_method'   => $data['payment_method'],
                'payment_reference'=> $data['payment_reference'] ?? null,
                'payment_provider' => $data['payment_provider'] ?? null,
                'payment_date'     => $data['payment_date'] ?? today(),
                'notes'            => $data['notes'] ?? null,
                'recorded_by'      => $by->id,
            ]);

            // ── Persist allocations ───────────────────────────────────
            foreach ($allocations as $alloc) {
                PaymentAllocation::create([
                    'payment_id'          => $payment->id,
                    'loan_schedule_id'    => $alloc['schedule_id'],
                    'loan_id'             => $loan->id,
                    'allocated_principal' => $alloc['principal'],
                    'allocated_interest'  => $alloc['interest'],
                    'allocated_penalty'   => $alloc['penalty'],
                    'allocated_total'     => $alloc['principal'] + $alloc['interest'] + $alloc['penalty'],
                    'instalment_fully_paid'=> $alloc['fully_paid'],
                ]);

                // Update schedule row
                $row = LoanSchedule::find($alloc['schedule_id']);
                $row->increment('amount_paid', $alloc['principal'] + $alloc['interest']);
                $row->increment('penalty_paid', $alloc['penalty']);

                if ($alloc['fully_paid']) {
                    $row->update(['status' => 'paid', 'paid_at' => $data['payment_date'] ?? today()]);
                } elseif ($row->fresh()->amount_paid > 0) {
                    $row->update(['status' => 'partial']);
                }
            }

            // ── Update loan balance ───────────────────────────────────
            $this->updateBalance($loan, $payment, $breakdown);

            // ── Close loan if fully repaid ────────────────────────────
            $freshBalance = $loan->balance()->first();
            if ($freshBalance && $freshBalance->total_outstanding <= 0.01) {
                $this->loanService->close($loan, 'Balance reached zero via payment ' . $payment->receipt_number . '.');
            }

            AuditLog::record('payment.recorded', $payment, [], $payment->toArray());

            return $payment;
        });
    }

    // ── Early settlement ──────────────────────────────────────────────────

    /**
     * Process early settlement — pay off all remaining balance at once
     * with a prorated interest discount applied.
     *
     * @param  Loan   $loan
     * @param  User   $by
     * @param  array  $data  Keys: payment_method, payment_date, payment_reference
     * @return Payment
     */
    public function earlySettle(Loan $loan, User $by, array $data): Payment
    {
        if (! $loan->canEarlySettle()) {
            throw ValidationException::withMessages([
                'loan' => "Loan {$loan->loan_number} is not eligible for early settlement.",
            ]);
        }

        return DB::transaction(function () use ($loan, $by, $data) {
            $balance = $loan->balance;

            // Effective months = the instalment period the settlement date falls in.
            // Rule: find the first due date that is >= settlement date — its instalment
            // number IS the effective month count (e.g. settling before/on due date 2
            // means the borrower held the loan for 2 months → use 2-month rate).
            $settlementDate = Carbon::parse($data['payment_date'] ?? today());
            $nextDueRow = $loan->schedule()
                ->where('due_date', '>=', $settlementDate->toDateString())
                ->orderBy('instalment_number')
                ->first();
            $effectiveMonths = $nextDueRow
                ? $nextDueRow->instalment_number
                : $loan->term_months;
            $effectiveMonths = max(1, min($effectiveMonths, $loan->term_months));

            // Replace original rate with the tier rate for actual months held
            $tieredRate    = LoanCalculatorService::TERM_RATES[$effectiveMonths]
                          ?? LoanCalculatorService::TERM_RATES[$loan->term_months];

            $loanPrincipal    = (float) $balance->principal_disbursed;
            $newTotalInterest = round($loanPrincipal * ($tieredRate / 100), 2);
            $interestDiscount = max(0, round((float) $loan->total_interest - $newTotalInterest, 2));

            // Settlement = (principal + new_interest) − total_already_paid
            $totalAlreadyPaid = (float) $balance->principal_paid
                              + (float) $balance->interest_paid
                              + (float) $balance->penalty_paid;
            $settlementAmount = max(0, round($loanPrincipal + $newTotalInterest - $totalAlreadyPaid, 2));

            // ── Align balance so the allocation engine can zero it correctly ──────
            // Reduce interest_charged, interest_outstanding and total_outstanding to
            // the re-tiered amount so record() reads the correct balance_before and
            // balance_after = 0 on the receipt.
            if ($interestDiscount > 0) {
                $balance->interest_charged     = max(0, (float) $balance->interest_charged     - $interestDiscount);
                $balance->interest_outstanding = max(0, (float) $balance->interest_outstanding - $interestDiscount);
                $balance->total_outstanding    = max(0, (float) $balance->total_outstanding    - $interestDiscount);
                $balance->save();
            }

            // ── Redistribute remaining interest across unpaid schedule rows ────────
            // The allocate() engine reads per-row interest_portion; we must update
            // those rows so they sum to the re-tiered remaining interest.
            $remainingRows = LoanSchedule::where('loan_id', $loan->id)
                ->whereNotIn('status', ['paid', 'waived'])
                ->orderBy('instalment_number')
                ->get();

            if ($remainingRows->count() > 0 && $interestDiscount > 0) {
                $interestStillOwed = max(0, round($newTotalInterest - (float) $balance->interest_paid, 2));
                $n                 = $remainingRows->count();
                $baseInterest      = round($interestStillOwed / $n, 2);
                $lastInterest      = round($interestStillOwed - ($baseInterest * ($n - 1)), 2);

                foreach ($remainingRows as $idx => $row) {
                    $rowInterest = ($idx === $n - 1) ? $lastInterest : $baseInterest;
                    $row->update([
                        'interest_portion' => $rowInterest,
                        'total_due'        => round((float) $row->principal_portion + $rowInterest, 2),
                    ]);
                }
            }

            // ── Record the payment (allocate() will now zero the balance) ─────────
            $payment = $this->record($loan, $by, [
                ...$data,
                'amount_received' => $settlementAmount,
                'payment_type'    => 'early_settlement',
            ]);

            // ── Mark the loan ─────────────────────────────────────────────────────
            $loan->update([
                'is_early_settled'          => true,
                'early_settled_at'          => $data['payment_date'] ?? today(),
                'early_settlement_amount'   => $settlementAmount,
                'early_settlement_discount' => $interestDiscount,
            ]);

            // Safety net: force-close if rounding left a tiny residual
            if (! $loan->fresh()->isClosed()) {
                $freshBal = $loan->balance()->first();
                if ($freshBal && $freshBal->total_outstanding <= 1.00) {
                    $freshBal->update([
                        'interest_outstanding' => 0,
                        'total_outstanding'    => max(0,
                            (float) $freshBal->principal_outstanding + (float) $freshBal->penalty_outstanding
                        ),
                    ]);
                    $this->loanService->close($loan, 'Early settlement with interest discount applied.');
                }
            }

            AuditLog::record('loan.early_settled', $loan, [], [
                'settlement_amount' => $settlementAmount,
                'interest_discount' => $interestDiscount,
                'effective_months'  => $effectiveMonths,
                'tiered_rate'       => $tieredRate,
            ]);

            return $payment;
        });
    }

    // ── Reversal ──────────────────────────────────────────────────────────

    /**
     * Reverse a payment that was recorded in error.
     *
     * Reverses the balance adjustments and reopens any schedule rows
     * that were marked as paid by this payment.
     */
    public function reverse(Payment $payment, User $by, string $reason): Payment
    {
        if ($payment->is_reversed) {
            throw ValidationException::withMessages(['payment' => 'This payment has already been reversed.']);
        }

        return DB::transaction(function () use ($payment, $by, $reason) {
            // Restore schedule rows
            foreach ($payment->allocations as $alloc) {
                $row = LoanSchedule::find($alloc->loan_schedule_id);
                $row->decrement('amount_paid', $alloc->allocated_principal + $alloc->allocated_interest);
                $row->decrement('penalty_paid', $alloc->allocated_penalty);

                $row->update([
                    'status'  => $row->fresh()->due_date < today() ? 'overdue' : 'due',
                    'paid_at' => null,
                ]);
            }

            // Reverse balance
            $balance = $payment->loan->balance;
            if ($balance) {
                $balance->increment('principal_outstanding', $payment->towards_principal);
                $balance->increment('interest_outstanding',  $payment->towards_interest);
                $balance->increment('penalty_outstanding',   $payment->towards_penalty);
                $balance->increment('total_outstanding',     $payment->amount_received);
                $balance->decrement('principal_paid',        $payment->towards_principal);
                $balance->decrement('interest_paid',         $payment->towards_interest);
                $balance->decrement('penalty_paid',          $payment->towards_penalty);
                $balance->decrement('instalments_paid',      $payment->allocations->where('instalment_fully_paid', true)->count());
            }

            // Mark as reversed
            $payment->update([
                'is_reversed'    => true,
                'reversed_at'    => now(),
                'reversed_by'    => $by->id,
                'reversal_reason'=> $reason,
            ]);

            // Reopen loan if it was closed by this payment
            $loan = $payment->loan;
            if ($loan->isClosed() && ! $loan->is_early_settled) {
                $loan->update(['status' => 'active']);
            }

            AuditLog::record('payment.reversed', $payment, [], ['reason' => $reason]);

            return $payment->fresh();
        });
    }

    // ── Preview (no DB writes) ────────────────────────────────────────────

    /**
     * Return the allocation breakdown for a given amount WITHOUT persisting.
     * Used by the live-calculator UI endpoint.
     */
    public function preview(Loan $loan, float $amount): array
    {
        [, $breakdown] = $this->allocate($loan, $amount);

        return [
            'towards_penalty'   => $breakdown['penalty'],
            'towards_interest'  => $breakdown['interest'],
            'towards_principal' => $breakdown['principal'],
            'overpayment'       => max(0, $breakdown['overpayment']),
            'new_balance'       => max(0, ($loan->balance?->total_outstanding ?? 0) - $amount),
        ];
    }

    // ── Private: allocation engine ────────────────────────────────────────

    /**
     * Allocate a payment amount across open schedule rows.
     *
     * Priority order:
     *   1. Outstanding penalties (oldest first)
     *   2. Interest portion of overdue/due instalments
     *   3. Principal portion
     *
     * Returns:
     *   [0] array of per-row allocation maps
     *   [1] totals breakdown: penalty, interest, principal, overpayment
     */
    private function allocate(Loan $loan, float $amount): array
    {
        $remaining = $amount;

        // Fetch unpaid schedule rows ordered oldest first
        $openRows = LoanSchedule::where('loan_id', $loan->id)
            ->whereNotIn('status', ['paid', 'waived'])
            ->orderBy('instalment_number')
            ->get();

        $allocations       = [];
        $totalPenalty      = 0;
        $totalInterest     = 0;
        $totalPrincipal    = 0;

        foreach ($openRows as $row) {
            if ($remaining <= 0) break;

            $penaltyOwed    = max(0, (float)$row->penalty_amount  - (float)$row->penalty_paid);
            $interestOwed   = max(0, (float)$row->interest_portion - max(0, (float)$row->amount_paid - (float)$row->principal_portion));
            $principalOwed  = max(0, (float)$row->principal_portion - max(0, (float)$row->amount_paid - $interestOwed));

            // 1. Pay penalty first
            $penPaid = min($remaining, $penaltyOwed);
            $remaining  -= $penPaid;
            $totalPenalty += $penPaid;

            // 2. Pay interest
            $intPaid = min($remaining, $interestOwed);
            $remaining  -= $intPaid;
            $totalInterest += $intPaid;

            // 3. Pay principal
            $prinPaid = min($remaining, $principalOwed);
            $remaining  -= $prinPaid;
            $totalPrincipal += $prinPaid;

            $fullyPaid = ($penPaid  >= $penaltyOwed - 0.01)
                      && ($intPaid  >= $interestOwed - 0.01)
                      && ($prinPaid >= $principalOwed - 0.01);

            $allocations[] = [
                'schedule_id' => $row->id,
                'penalty'     => round($penPaid,  2),
                'interest'    => round($intPaid,  2),
                'principal'   => round($prinPaid, 2),
                'fully_paid'  => $fullyPaid,
            ];
        }

        $breakdown = [
            'penalty'     => round($totalPenalty, 2),
            'interest'    => round($totalInterest, 2),
            'principal'   => round($totalPrincipal, 2),
            'overpayment' => round($remaining, 2),  // any excess beyond total due
        ];

        return [$allocations, $breakdown];
    }

    /**
     * Update the loan_balances row after a payment.
     */
    private function updateBalance(Loan $loan, Payment $payment, array $breakdown): void
    {
        $balance = $loan->balance;

        $balance->increment('principal_paid',  $breakdown['principal']);
        $balance->increment('interest_paid',   $breakdown['interest']);
        $balance->increment('penalty_paid',    $breakdown['penalty']);

        $fullyPaidCount = PaymentAllocation::where('payment_id', $payment->id)
            ->where('instalment_fully_paid', true)
            ->count();

        $overdueCount = LoanSchedule::where('loan_id', $loan->id)
            ->where('status', 'overdue')
            ->count();

        $balance->decrement('instalments_overdue', min($overdueCount, $fullyPaidCount));
        $balance->increment('instalments_paid', $fullyPaidCount);
        $balance->update([
            'last_payment_at'     => now(),
            'last_payment_amount' => $payment->amount_received,
        ]);

        // Recalculate all outstanding columns
        $balance->refresh();
        $balance->recalculate();
        $balance->save();
    }

    /**
     * Aggregated payment totals for a date range (used by /payments/summary).
     */
    public function periodSummary(string $dateFrom, string $dateTo, ?int $officerId = null): array
    {
        $query = Payment::whereBetween('payment_date', [$dateFrom, $dateTo]);

        if ($officerId) {
            $query->where('recorded_by', $officerId);
        }

        $totals = $query->selectRaw('
            COUNT(*) as receipt_count,
            SUM(amount_received) as total_collected,
            SUM(towards_principal) as principal_collected,
            SUM(towards_interest) as interest_collected,
            SUM(towards_penalty) as penalty_collected
        ')->first();

        $byMethod = (clone $query)
            ->selectRaw('payment_method, COUNT(*) as count, SUM(amount_received) as total')
            ->groupBy('payment_method')
            ->get();

        return [
            'period'     => ['from' => $dateFrom, 'to' => $dateTo],
            'totals'     => $totals,
            'by_method'  => $byMethod,
        ];
    }

    /**
     * Infer the payment_type from the allocations.
     */
    private function resolvePaymentType(array $allocations): string
    {
        if (empty($allocations)) return 'instalment';

        $fullyPaidCount = count(array_filter($allocations, fn ($a) => $a['fully_paid']));

        if ($fullyPaidCount === 0) return 'partial';
        if ($fullyPaidCount === 1) return 'instalment';

        return 'instalment'; // multi-instalment catch-up
    }
}
