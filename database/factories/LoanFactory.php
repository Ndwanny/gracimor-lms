<?php

namespace Database\Factories;

use App\Models\Borrower;
use App\Models\CollateralAsset;
use App\Models\Loan;
use App\Models\LoanProduct;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LoanFactory extends Factory
{
    protected $model = Loan::class;

    private static int $seq = 1;

    public function definition(): array
    {
        $principal  = fake()->randomElement([20000, 30000, 50000, 75000, 100000, 150000, 200000]);
        $rate       = fake()->randomFloat(2, 24, 32);
        $term       = fake()->randomElement([6, 9, 12, 18, 24]);
        $disbursed  = fake()->dateTimeBetween('-18 months', '-3 months');
        $firstDue   = (clone $disbursed)->modify('+1 month');
        $maturity   = (clone $firstDue)->modify("+{$term} months");
        $monthlyInt  = $this->calcInstalment($principal, $rate, $term);
        $totalInt    = round($monthlyInt * $term - $principal, 2);
        $procFee     = round($principal * 0.02, 2);
        $year        = date('Y', $disbursed->getTimestamp());
        $loanNo      = 'GRS-' . $year . '-' . str_pad(self::$seq++, 5, '0', STR_PAD_LEFT);

        return [
            'loan_number'            => $loanNo,
            'borrower_id'            => Borrower::factory(),
            'loan_product_id'        => LoanProduct::factory(),
            'collateral_asset_id'    => CollateralAsset::factory(),
            'principal_amount'       => $principal,
            'interest_rate'          => $rate,
            'interest_method'        => 'reducing_balance',
            'term_months'            => $term,
            'monthly_instalment'     => $monthlyInt,
            'total_interest'         => $totalInt,
            'total_repayable'        => round($principal + $totalInt, 2),
            'processing_fee'         => $procFee,
            'ltv_at_origination'     => null,
            'disbursement_method'    => fake()->randomElement(['cash', 'bank_transfer', 'mobile_money']),
            'disbursement_reference' => fake()->optional(0.6)->numerify('TXN###########'),
            'first_repayment_date'   => $firstDue->format('Y-m-d'),
            'maturity_date'          => $maturity->format('Y-m-d'),
            'disbursed_at'           => $disbursed,
            'status'                 => 'active',
            'applied_by'             => User::factory()->officer(),
            'approved_by'            => User::factory()->manager(),
            'disbursed_by'           => null,
            'approved_at'            => (clone $disbursed)->modify('-2 days'),
            'loan_purpose'           => fake()->optional(0.7)->randomElement([
                'Vehicle purchase', 'Business working capital', 'School fees',
                'Medical expenses', 'Home renovation', 'Agricultural inputs',
                'Personal use',
            ]),
            'approval_notes'         => null,
            'is_early_settled'       => false,
            'early_settled_at'       => null,
            'early_settlement_amount'=> null,
            'early_settlement_discount' => null,
            'rejection_reason'       => null,
            'rejected_at'            => null,
        ];
    }

    // ── Named states ──────────────────────────────────────────────────────────

    public function pending(): static
    {
        return $this->state([
            'status'       => 'pending_approval',
            'disbursed_at' => null,
            'approved_by'  => null,
            'approved_at'  => null,
        ]);
    }

    public function approved(): static
    {
        return $this->state([
            'status'       => 'approved',
            'disbursed_at' => null,
        ]);
    }

    public function active(): static
    {
        return $this->state(['status' => 'active']);
    }

    public function overdue(): static
    {
        return $this->state([
            'status'          => 'overdue',
            'disbursed_at'    => fake()->dateTimeBetween('-24 months', '-6 months'),
        ]);
    }

    public function closed(): static
    {
        return $this->state(['status' => 'closed']);
    }

    public function rejected(): static
    {
        return $this->state([
            'status'           => 'rejected',
            'rejection_reason' => fake()->randomElement([
                'Insufficient collateral value',
                'Existing loan outstanding',
                'Incomplete documentation',
                'Credit history concerns',
                'KYC not verified',
            ]),
            'rejected_at'  => fake()->dateTimeBetween('-3 months'),
            'disbursed_at' => null,
            'approved_by'  => null,
            'approved_at'  => null,
        ]);
    }

    public function earlySettled(): static
    {
        return $this->state(function () {
            $settledAt      = fake()->dateTimeBetween('-6 months', '-1 month');
            $discount       = fake()->numberBetween(1000, 8000);
            return [
                'status'                    => 'closed',
                'is_early_settled'          => true,
                'early_settled_at'          => $settledAt,
                'early_settlement_discount' => $discount,
            ];
        });
    }

    public function forBorrower(Borrower $borrower): static
    {
        return $this->state(['borrower_id' => $borrower->id]);
    }

    public function withProduct(LoanProduct $product): static
    {
        return $this->state([
            'loan_product_id' => $product->id,
            'interest_rate'   => $product->interest_rate,
            'interest_method' => $product->interest_method,
        ]);
    }

    // ── Instalment calculator (reducing balance) ──────────────────────────────

    private function calcInstalment(float $principal, float $annualRate, int $months): float
    {
        $r = ($annualRate / 100) / 12;
        if ($r == 0) {
            return round($principal / $months, 2);
        }
        $instalment = $principal * ($r * pow(1 + $r, $months)) / (pow(1 + $r, $months) - 1);
        return round($instalment, 2);
    }
}


// ═══════════════════════════════════════════════════════════════════════════════
// LoanScheduleFactory
// File: database/factories/LoanScheduleFactory.php
// ═══════════════════════════════════════════════════════════════════════════════
