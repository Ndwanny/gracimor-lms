<?php

namespace App\Services;

use App\Models\LoanProduct;
use Illuminate\Support\Carbon;

/**
 * LoanCalculatorService
 *
 * Pure calculation engine — no database writes.
 * Called by LoanService and the live-calculator API endpoint.
 */
class LoanCalculatorService
{
    /**
     * Fixed flat interest rates by loan term (months).
     * Rate is the TOTAL interest rate for the full loan period — not annual.
     */
    public const TERM_RATES = [
        1 => 10.0,
        2 => 18.0,
        3 => 28.0,
        4 => 38.0,
        6 => 48.0,
    ];

    /**
     * Return the fixed interest rate for a given loan term.
     * Throws if the term is not one of the allowed durations.
     */
    public function rateForTerm(int $months): float
    {
        if (!isset(self::TERM_RATES[$months])) {
            throw new \InvalidArgumentException("Invalid loan term: {$months}. Allowed: 1, 2, 3, 4, 6 months.");
        }
        return self::TERM_RATES[$months];
    }

    /**
     * Calculate the full amortisation schedule for a loan.
     *
     * Returns an array of instalment rows, each containing:
     *   instalment_number, due_date, principal_portion, interest_portion,
     *   total_due, opening_balance, closing_balance
     *
     * @param  float        $principal         Loan amount
     * @param  float        $annualRatePercent  Annual interest rate (e.g. 28.0)
     * @param  int          $termMonths        Number of monthly instalments
     * @param  string       $method            'reducing_balance' | 'flat_rate'
     * @param  Carbon       $firstRepaymentDate
     * @return array
     */
    public function buildSchedule(
        float $principal,
        float $annualRatePercent,
        int $termMonths,
        string $method,
        Carbon $firstRepaymentDate
    ): array {
        return $method === 'flat_rate'
            ? $this->buildFlatRateSchedule($principal, $annualRatePercent, $termMonths, $firstRepaymentDate)
            : $this->buildReducingBalanceSchedule($principal, $annualRatePercent, $termMonths, $firstRepaymentDate);
    }

    // ── Reducing Balance (PMT formula) ────────────────────────────────────

    private function buildReducingBalanceSchedule(
        float $principal,
        float $annualRatePercent,
        int $termMonths,
        Carbon $firstRepaymentDate
    ): array {
        $monthlyRate = $annualRatePercent / 100 / 12;
        $monthlyPmt  = $this->pmt($monthlyRate, $termMonths, $principal);

        $schedule       = [];
        $openingBalance = $principal;

        for ($i = 1; $i <= $termMonths; $i++) {
            $interest   = round($openingBalance * $monthlyRate, 2);
            $principal_ = round($monthlyPmt - $interest, 2);

            // Final instalment: absorb rounding residual
            if ($i === $termMonths) {
                $principal_ = round($openingBalance, 2);
            }

            $closingBalance = round($openingBalance - $principal_, 2);

            $schedule[] = [
                'instalment_number' => $i,
                'due_date'          => $firstRepaymentDate->copy()->addMonths($i - 1)->toDateString(),
                'principal_portion' => $principal_,
                'interest_portion'  => $interest,
                'total_due'         => round($principal_ + $interest, 2),
                'opening_balance'   => $openingBalance,
                'closing_balance'   => max(0, $closingBalance),
            ];

            $openingBalance = max(0, $closingBalance);
        }

        return $schedule;
    }

    // ── Flat Rate ──────────────────────────────────────────────────────────

    private function buildFlatRateSchedule(
        float $principal,
        float $annualRatePercent,
        int $termMonths,
        Carbon $firstRepaymentDate
    ): array {
        // Rate is the total flat rate for the full loan period (e.g. 38% for 4 months).
        $totalInterest    = round($principal * ($annualRatePercent / 100), 2);
        $totalRepayable   = $principal + $totalInterest;
        $monthlyPrincipal = round($principal / $termMonths, 2);
        $monthlyInterest  = round($totalInterest / $termMonths, 2);
        $monthlyTotal     = round($monthlyPrincipal + $monthlyInterest, 2);

        $schedule       = [];
        $openingBalance = $principal;

        for ($i = 1; $i <= $termMonths; $i++) {
            $prin_ = ($i === $termMonths)
                ? round($openingBalance, 2)
                : $monthlyPrincipal;

            $closingBalance = max(0, round($openingBalance - $prin_, 2));

            $schedule[] = [
                'instalment_number' => $i,
                'due_date'          => $firstRepaymentDate->copy()->addMonths($i - 1)->toDateString(),
                'principal_portion' => $prin_,
                'interest_portion'  => $monthlyInterest,
                'total_due'         => round($prin_ + $monthlyInterest, 2),
                'opening_balance'   => $openingBalance,
                'closing_balance'   => $closingBalance,
            ];

            $openingBalance = $closingBalance;
        }

        return $schedule;
    }

    // ── Summary figures ───────────────────────────────────────────────────

    /**
     * Return totals from a built schedule (or calculate without building the full array).
     *
     * Returns: monthly_instalment, total_interest, total_repayable
     */
    public function summarise(
        float $principal,
        float $annualRatePercent,
        int $termMonths,
        string $method
    ): array {
        $dummyDate = Carbon::today();
        $schedule  = $this->buildSchedule($principal, $annualRatePercent, $termMonths, $method, $dummyDate);

        $totalInterest  = collect($schedule)->sum('interest_portion');
        $totalRepayable = $principal + $totalInterest;
        $monthlyPmt     = $schedule[0]['total_due'] ?? 0;

        return [
            'monthly_instalment' => round($monthlyPmt, 2),
            'total_interest'     => round($totalInterest, 2),
            'total_repayable'    => round($totalRepayable, 2),
        ];
    }

    /**
     * Calculate the early settlement amount using prorated interest.
     *
     * @param  float  $principalOutstanding
     * @param  float  $annualRatePercent
     * @param  int    $monthsUsed          Instalments already consumed
     * @param  int    $termMonths          Total loan term
     * @param  string $method              'prorated' | 'rebate_78' | 'none'
     * @return array  [settlement_amount, interest_discount]
     */
    public function earlySettlementAmount(
        float $principalOutstanding,
        float $annualRatePercent,
        int $monthsUsed,
        int $termMonths,
        float $totalInterest,
        string $method = 'prorated'
    ): array {
        if ($method === 'none') {
            return [
                'settlement_amount'   => round($principalOutstanding, 2),
                'interest_discount'   => 0.0,
            ];
        }

        if ($method === 'rebate_78') {
            // Rule of 78 (Sum-of-Digits)
            $totalDigits       = array_sum(range(1, $termMonths));
            $remainingMonths   = $termMonths - $monthsUsed;
            $remainingDigits   = array_sum(range(1, $remainingMonths));
            $interestDiscount  = round($totalInterest * ($remainingDigits / $totalDigits), 2);
        } else {
            // Prorated: only pay interest for months actually used
            $monthsRemaining  = $termMonths - $monthsUsed;
            $interestDiscount = round($totalInterest * ($monthsRemaining / $termMonths), 2);
        }

        $settlementAmount = round($principalOutstanding - $interestDiscount, 2);

        return [
            'settlement_amount' => max($settlementAmount, $principalOutstanding * 0.1), // floor at 10%
            'interest_discount' => $interestDiscount,
        ];
    }

    /**
     * Calculate Loan-to-Value ratio.
     */
    public function ltv(float $loanAmount, float $collateralValue): float
    {
        if ($collateralValue <= 0) return 0;
        return round(($loanAmount / $collateralValue) * 100, 2);
    }

    // ── Private helpers ───────────────────────────────────────────────────

    /**
     * Standard PMT formula: periodic payment for an annuity.
     *
     * @param  float $rate     Periodic (monthly) interest rate
     * @param  int   $nper     Number of periods
     * @param  float $pv       Present value (loan amount)
     * @return float
     */
    private function pmt(float $rate, int $nper, float $pv): float
    {
        if ($rate === 0.0) {
            return round($pv / $nper, 2);
        }

        $factor = pow(1 + $rate, $nper);

        return round($pv * ($rate * $factor) / ($factor - 1), 2);
    }
}
