<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Loan;
use App\Models\LoanBalance;
use App\Models\LoanSchedule;
use App\Models\LoanStatusHistory;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LoanService
{
    public function __construct(
        private readonly LoanCalculatorService $calculator
    ) {}

    // ── Number generation ─────────────────────────────────────────────────

    /**
     * Generate next loan number: LN-YYYYNNNNN (e.g. LN-20260032).
     */
    public function generateLoanNumber(): string
    {
        $year = now()->year;

        $last = Loan::withTrashed()
            ->where('loan_number', 'like', "LN-{$year}%")
            ->orderByDesc('id')
            ->lockForUpdate()
            ->value('loan_number');

        if (! $last) {
            return "LN-{$year}00001";
        }

        $n = (int) substr($last, strlen("LN-{$year}"));

        return "LN-{$year}" . str_pad($n + 1, 5, '0', STR_PAD_LEFT);
    }

    // ── Application ───────────────────────────────────────────────────────

    /**
     * Create a new loan application (status = pending).
     *
     * Calculates totals from the product defaults / officer-overridden values,
     * records the application, and logs the status transition.
     *
     * @param  array  $data  Validated fields from LoanApplicationRequest
     * @param  User   $by    The officer submitting the application
     * @return Loan
     */
    public function apply(array $data, User $by): Loan
    {
        return DB::transaction(function () use ($data, $by) {
            // Rate is always determined by the loan term — fixed flat-rate model.
            $termMonths    = (int) $data['term_months'];
            $interestRate  = $this->calculator->rateForTerm($termMonths);
            $interestMethod = 'flat_rate';

            $summary = $this->calculator->summarise(
                principal:         (float) $data['principal_amount'],
                annualRatePercent: $interestRate,
                termMonths:        $termMonths,
                method:            $interestMethod,
            );

            $loan = Loan::create([
                ...$data,
                'loan_number'        => $this->generateLoanNumber(),
                'interest_rate'      => $interestRate,
                'interest_method'    => $interestMethod,
                'total_interest'     => $summary['total_interest'],
                'total_repayable'    => $summary['total_repayable'],
                'monthly_instalment' => $summary['monthly_instalment'],
                'status'             => 'pending',
                'applied_by'         => $by->id,
            ]);

            $this->recordStatusTransition($loan, null, 'pending', 'Loan application submitted.', $by);
            AuditLog::record('loan.applied', $loan, [], $loan->toArray());

            return $loan;
        });
    }

    // ── Approval ──────────────────────────────────────────────────────────

    /**
     * Approve a pending loan.
     *
     * The approver may adjust the principal (e.g. approve a lower amount).
     * Recalculates totals if the approved amount differs from the applied amount.
     *
     * @param  Loan   $loan
     * @param  User   $by
     * @param  float|null $approvedAmount  Pass null to approve at full requested amount
     * @param  string $notes
     * @return Loan
     * @throws ValidationException
     */
    public function approve(Loan $loan, User $by, ?float $approvedAmount = null, string $notes = ''): Loan
    {
        if (! $loan->canBeApproved()) {
            throw ValidationException::withMessages(['loan' => "Loan {$loan->loan_number} cannot be approved (status: {$loan->status})."]);
        }

        if (! $by->canApproveLoan()) {
            throw ValidationException::withMessages(['user' => 'You do not have permission to approve loans.']);
        }

        return DB::transaction(function () use ($loan, $by, $approvedAmount, $notes) {
            $old = $loan->toArray();

            $updates = [
                'status'         => 'approved',
                'approved_by'    => $by->id,
                'approved_at'    => now(),
                'approval_notes' => $notes,
            ];

            // Recalculate if a different amount was approved
            if ($approvedAmount && abs($approvedAmount - $loan->principal_amount) > 0.01) {
                $summary = $this->calculator->summarise(
                    $approvedAmount, $loan->interest_rate, $loan->term_months, $loan->interest_method
                );
                $updates = array_merge($updates, [
                    'principal_amount'   => $approvedAmount,
                    'total_interest'     => $summary['total_interest'],
                    'total_repayable'    => $summary['total_repayable'],
                    'monthly_instalment' => $summary['monthly_instalment'],
                ]);
            }

            $loan->update($updates);

            $this->recordStatusTransition($loan, 'pending', 'approved', $notes, $by, [
                'approved_amount' => $loan->principal_amount,
            ]);

            AuditLog::record('loan.approved', $loan, $old, $loan->fresh()->toArray());

            return $loan->fresh();
        });
    }

    // ── Rejection ─────────────────────────────────────────────────────────

    /**
     * Reject a pending loan application.
     */
    public function reject(Loan $loan, User $by, string $reason): Loan
    {
        if (! $loan->canBeRejected()) {
            throw ValidationException::withMessages(['loan' => "Loan {$loan->loan_number} cannot be rejected (status: {$loan->status})."]);
        }

        return DB::transaction(function () use ($loan, $by, $reason) {
            $old = $loan->toArray();

            $loan->update([
                'status'           => 'rejected',
                'rejection_reason' => $reason,
                'rejected_by'      => $by->id,
                'rejected_at'      => now(),
            ]);

            $this->recordStatusTransition($loan, 'pending', 'rejected', $reason, $by);
            AuditLog::record('loan.rejected', $loan, $old, ['status' => 'rejected', 'reason' => $reason]);

            return $loan->fresh();
        });
    }

    // ── Disbursement ──────────────────────────────────────────────────────

    /**
     * Disburse an approved loan.
     *
     * - Generates the full amortisation schedule
     * - Creates the loan_balances row
     * - Sets loan status to 'active'
     * - Pledges the collateral asset
     *
     * @param  Loan   $loan
     * @param  User   $by
     * @param  array  $data  Keys: disbursement_method, disbursement_reference, disbursed_at
     * @return Loan
     */
    public function disburse(Loan $loan, User $by, array $data): Loan
    {
        if (! $loan->canBeDisbursed()) {
            throw ValidationException::withMessages(['loan' => "Loan {$loan->loan_number} cannot be disbursed (status: {$loan->status})."]);
        }

        if (! $by->canDisburseFunds()) {
            throw ValidationException::withMessages(['user' => 'You do not have permission to disburse funds.']);
        }

        return DB::transaction(function () use ($loan, $by, $data) {
            $disbursedAt        = Carbon::parse($data['disbursed_at'] ?? today());
            $firstRepaymentDate = Carbon::parse($loan->first_repayment_date ?? $disbursedAt->copy()->addMonth());
            $maturityDate       = $firstRepaymentDate->copy()->addMonths($loan->term_months - 1);

            // 1. Update loan record
            $old = $loan->toArray();
            $loan->update([
                'status'                 => 'active',
                'disbursed_at'           => $disbursedAt,
                'maturity_date'          => $maturityDate,
                'first_repayment_date'   => $firstRepaymentDate,
                'disbursement_method'    => $data['disbursement_method'],
                'disbursement_reference' => $data['disbursement_reference'] ?? null,
                'disbursed_by'           => $by->id,
                'disburse_notes'         => $data['notes'] ?? null,
            ]);

            // 2. Generate amortisation schedule
            $this->generateSchedule($loan, $firstRepaymentDate);

            // 3. Initialise loan balance
            $this->initialiseBalance($loan);

            // 4. Pledge collateral
            if ($loan->collateral) {
                $loan->collateral->update(['status' => 'pledged']);
            }

            // 5. Record history and audit
            $this->recordStatusTransition($loan, 'approved', 'active', $data['notes'] ?? '', $by, [
                'disbursed_amount' => $loan->principal_amount,
                'method'           => $data['disbursement_method'],
            ]);

            AuditLog::record('loan.disbursed', $loan, $old, $loan->fresh()->toArray());

            return $loan->fresh();
        });
    }

    // ── Schedule generation ───────────────────────────────────────────────

    /**
     * Build and persist the amortisation schedule rows for a loan.
     * Called automatically during disbursement.
     */
    public function generateSchedule(Loan $loan, Carbon $firstRepaymentDate): void
    {
        // Remove any existing schedule rows (re-generation scenario)
        LoanSchedule::where('loan_id', $loan->id)->delete();

        $rows = $this->calculator->buildSchedule(
            principal:          (float) $loan->principal_amount,
            annualRatePercent:  (float) $loan->interest_rate,
            termMonths:         $loan->term_months,
            method:             $loan->interest_method,
            firstRepaymentDate: $firstRepaymentDate,
        );

        $today = today()->toDateString();

        $inserts = array_map(fn ($row) => array_merge($row, [
            'loan_id'    => $loan->id,
            'status'     => $row['due_date'] <= $today ? 'due' : 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]), $rows);

        LoanSchedule::insert($inserts);
    }

    /**
     * Initialise the loan_balances row after disbursement.
     */
    private function initialiseBalance(Loan $loan): LoanBalance
    {
        $scheduleCount = $loan->schedule()->count();

        return LoanBalance::create([
            'loan_id'               => $loan->id,
            'principal_disbursed'   => $loan->principal_amount,
            'principal_paid'        => 0,
            'principal_outstanding' => $loan->principal_amount,
            'interest_charged'      => $loan->total_interest,
            'interest_paid'         => 0,
            'interest_outstanding'  => $loan->total_interest,
            'penalty_charged'       => 0,
            'penalty_paid'          => 0,
            'penalty_outstanding'   => 0,
            'total_outstanding'     => $loan->total_repayable,
            'instalments_total'     => $scheduleCount,
            'instalments_paid'      => 0,
            'instalments_overdue'   => 0,
        ]);
    }

    // ── Close loan ────────────────────────────────────────────────────────

    /**
     * Mark a loan as closed (called by PaymentService when balance reaches zero).
     */
    public function close(Loan $loan, string $reason = 'Full repayment completed.'): Loan
    {
        if ($loan->isClosed()) {
            return $loan;
        }

        return DB::transaction(function () use ($loan, $reason) {
            $loan->update(['status' => 'closed']);

            if ($loan->collateral) {
                $loan->collateral->update(['status' => 'released']);
            }

            $this->recordStatusTransition($loan, 'active', 'closed', $reason, null, [], true);
            AuditLog::record('loan.closed', $loan, [], ['status' => 'closed']);

            return $loan->fresh();
        });
    }

    // ── Status history ────────────────────────────────────────────────────

    private function recordStatusTransition(
        Loan $loan,
        ?string $from,
        string $to,
        string $notes,
        ?User $by,
        array $metadata = [],
        bool $isSystem = false
    ): void {
        LoanStatusHistory::create([
            'loan_id'          => $loan->id,
            'from_status'      => $from,
            'to_status'        => $to,
            'notes'            => $notes,
            'changed_by'       => $by?->id,
            'is_system_action' => $isSystem,
            'metadata'         => $metadata ?: null,
        ]);
    }
}
