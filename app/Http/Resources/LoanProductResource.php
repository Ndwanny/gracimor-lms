<?php

namespace App\Http\Resources;

class LoanProductResource extends GracimorResource
{
    public function toArray($request): array
    {
        return [
            'id'   => $this->id,
            'name' => $this->name,
            'code' => $this->code,

            'collateral_type'       => $this->collateral_type,
            'collateral_type_label' => match ($this->collateral_type) {
                'vehicle' => 'Vehicle Only',
                'land'    => 'Land Only',
                'both'    => 'Vehicle or Land',
                default   => $this->collateral_type,
            },
            'description' => $this->description,
            'is_active'   => (bool) $this->is_active,

            // ── Rates ─────────────────────────────────────────────────────────
            'rates' => [
                'interest_rate'     => $this->percent($this->interest_rate),
                'min_interest_rate' => $this->percent($this->min_interest_rate),
                'max_interest_rate' => $this->percent($this->max_interest_rate),
                'interest_method'   => $this->interest_method,
                'interest_method_label' => $this->interest_method === 'reducing_balance'
                    ? 'Reducing Balance'
                    : 'Flat Rate',
            ],

            // ── Terms ─────────────────────────────────────────────────────────
            'terms' => [
                'min_term_months' => (int) $this->min_term_months,
                'max_term_months' => (int) $this->max_term_months,
                'min_loan_amount' => $this->money($this->min_loan_amount),
                'max_loan_amount' => $this->money($this->max_loan_amount),
                'max_ltv_percent' => $this->percent($this->max_ltv_percent),
            ],

            // ── Fees ──────────────────────────────────────────────────────────
            'fees' => [
                'processing_fee_flat'    => $this->money($this->processing_fee_flat),
                'processing_fee_percent' => $this->percent($this->processing_fee_percent),
                'fee_mode'               => $this->processing_fee_flat
                    ? 'flat'
                    : ($this->processing_fee_percent ? 'percent' : 'none'),
            ],

            // ── Penalties ─────────────────────────────────────────────────────
            'penalties' => [
                'penalty_rate_percent' => $this->percent($this->penalty_rate_percent),
                'penalty_basis'        => $this->penalty_basis,
                'penalty_basis_label'  => $this->penalty_basis === 'instalment'
                    ? 'On overdue instalment'
                    : 'On outstanding balance',
                'grace_period_days'    => (int) $this->grace_period_days,
            ],

            // ── Features ─────────────────────────────────────────────────────
            'features' => [
                'allow_early_settlement'   => (bool) $this->allow_early_settlement,
                'early_settlement_method'  => $this->early_settlement_method,
                'early_settlement_label'   => match ($this->early_settlement_method) {
                    'prorated'  => 'Prorated interest rebate',
                    'rebate_78' => 'Rule of 78 rebate',
                    'none'      => 'No rebate — full balance due',
                    default     => null,
                },
                'allow_rate_override'  => (bool) $this->allow_rate_override,
                'require_guarantor'    => (bool) $this->require_guarantor,
            ],

            // ── Usage stats (when eager-loaded via withCount / loadSum) ────────
            'stats' => [
                'total_loans'     => $this->whenCounted('loans',
                    fn () => $this->loans_count
                ),
                'active_loans'    => $this->whenCounted('loans',
                    fn () => $this->active_loans_count
                ),
                'total_disbursed' => $this->when(
                    isset($this->total_disbursed),
                    fn () => $this->money($this->total_disbursed)
                ),
            ],

            'created_at' => $this->dt($this->created_at),
            'updated_at' => $this->dt($this->updated_at),

            'links' => [
                'self'  => "/api/loan-products/{$this->id}",
                'loans' => "/api/loans?loan_product_id={$this->id}",
            ],
        ];
    }
}


// ═══════════════════════════════════════════════════════════════════════════════
// UserResource
// Used by: UserController::index, show, store, update, me
//          Embedded in Loan, Payment, Penalty resources as officer refs
// ═══════════════════════════════════════════════════════════════════════════════
