<?php

namespace App\Http\Resources;

class LoanResource extends GracimorResource
{
    public function toArray($request): array
    {
        return [
            // ── Identity ──────────────────────────────────────────────────────
            'id'          => $this->id,
            'loan_number' => $this->loan_number,

            // ── Status ────────────────────────────────────────────────────────
            'status'       => $this->statusBadge($this->status),
            'is_overdue'   => in_array($this->status, ['overdue', 'legal']),
            'days_overdue' => $this->whenLoaded('loanBalance',
                fn () => $this->loanBalance?->days_overdue ?? 0
            ),

            // ── Borrower (lightweight inline when loaded) ─────────────────────
            'borrower' => $this->whenLoaded('borrower', fn () => [
                'id'              => $this->borrower->id,
                'borrower_number' => $this->borrower->borrower_number,
                'full_name'       => $this->borrower->full_name,
                'phone_primary'   => $this->borrower->phone_primary,
                'nrc_number'      => $this->borrower->nrc_number,
                'kyc_status'      => $this->borrower->kyc_status,
            ]),
            'borrower_id' => $this->borrower_id,

            // ── Product ───────────────────────────────────────────────────────
            'product' => $this->whenLoaded('loanProduct', fn () => [
                'id'              => $this->loanProduct->id,
                'name'            => $this->loanProduct->name,
                'code'            => $this->loanProduct->code,
                'interest_method' => $this->loanProduct->interest_method,
                'penalty_basis'   => $this->loanProduct->penalty_basis,
                'grace_period_days' => $this->loanProduct->grace_period_days,
            ]),
            'loan_product_id' => $this->loan_product_id,

            // ── Collateral ────────────────────────────────────────────────────
            'collateral' => $this->whenLoaded('collateralAsset', fn () => [
                'id'                  => $this->collateralAsset->id,
                'collateral_type'     => $this->collateralAsset->collateral_type,
                'asset_description'   => $this->collateralAsset->asset_description,
                'registration_number' => $this->collateralAsset->registration_number,
                'estimated_value'     => $this->money($this->collateralAsset->estimated_value),
                'valuation_date'      => $this->d($this->collateralAsset->valuation_date),
            ]),

            // ── Financials ────────────────────────────────────────────────────
            'financials' => [
                'principal_amount'    => $this->money($this->principal_amount),
                'interest_rate'       => $this->percent($this->interest_rate),
                'interest_method'     => $this->interest_method,
                'term_months'         => (int) $this->term_months,
                'total_interest'      => $this->money($this->total_interest),
                'total_repayable'     => $this->money($this->total_repayable),
                'monthly_instalment'  => $this->money($this->monthly_instalment),
                'processing_fee'      => $this->money($this->processing_fee),
                'ltv_at_origination'  => $this->percent($this->ltv_at_origination),
            ],

            // ── Live Balance (from loan_balances table) ───────────────────────
            'balance' => $this->whenLoaded('loanBalance', fn () =>
                LoanBalanceResource::make($this->loanBalance)
            ),

            // ── Dates ─────────────────────────────────────────────────────────
            'dates' => [
                'applied_at'           => $this->dt($this->created_at),
                'approved_at'          => $this->dt($this->approved_at),
                'disbursed_at'         => $this->dt($this->disbursed_at),
                'first_repayment_date' => $this->d($this->first_repayment_date),
                'maturity_date'        => $this->d($this->maturity_date),
                'early_settled_at'     => $this->dt($this->early_settled_at),
            ],

            // ── Disbursement ──────────────────────────────────────────────────
            'disbursement' => [
                'method'    => $this->disbursement_method,
                'reference' => $this->disbursement_reference,
                'notes'     => $this->when($this->isSenior(), $this->disburse_notes),
            ],

            // ── Early Settlement ──────────────────────────────────────────────
            'early_settlement' => $this->when($this->is_early_settled, [
                'settled_at'   => $this->dt($this->early_settled_at),
                'amount'       => $this->money($this->early_settlement_amount),
                'discount'     => $this->money($this->early_settlement_discount),
            ]),

            // ── Rejection (if applicable) ─────────────────────────────────────
            'rejection' => $this->when($this->status === 'rejected', [
                'reason'      => $this->rejection_reason,
                'rejected_at' => $this->dt($this->rejected_at),
                'rejected_by' => $this->whenLoaded('rejectedBy', fn () => [
                    'id'   => $this->rejectedBy?->id,
                    'name' => $this->rejectedBy?->name,
                ]),
            ]),

            // ── Notes (senior staff only) ─────────────────────────────────────
            'notes' => $this->when($this->isSenior(), [
                'loan_purpose'   => $this->loan_purpose,
                'approval_notes' => $this->approval_notes,
            ]),

            // ── Schedule (only when loaded — detail view) ─────────────────────
            'schedule' => LoanScheduleResource::collection(
                $this->whenLoaded('loanSchedule')
            ),

            // ── Payments (only when loaded) ───────────────────────────────────
            'payments' => PaymentResource::collection(
                $this->whenLoaded('payments')
            ),

            // ── Penalties (only when loaded) ──────────────────────────────────
            'penalties' => PenaltyResource::collection(
                $this->whenLoaded('penalties')
            ),

            // ── Penalty summary (always computed from loaded penalties) ────────
            'penalty_summary' => $this->mergeWhen(
                $this->relationLoaded('penalties'),
                fn () => [
                    'total_outstanding' => $this->money(
                        $this->penalties->where('status', 'outstanding')->sum('amount')
                    ),
                    'total_waived' => $this->money(
                        $this->penalties->where('status', 'waived')->sum('amount')
                    ),
                    'count_outstanding' => $this->penalties->where('status', 'outstanding')->count(),
                ]
            ),

            // ── Guarantors ────────────────────────────────────────────────────
            'guarantors' => GuarantorResource::collection(
                $this->whenLoaded('guarantors')
            ),

            // ── Status history ────────────────────────────────────────────────
            'status_history' => LoanStatusHistoryResource::collection(
                $this->whenLoaded('statusHistory')
            ),

            // ── Officers ─────────────────────────────────────────────────────
            'officers' => [
                'applied_by'   => $this->whenLoaded('appliedBy', fn () => [
                    'id'   => $this->appliedBy->id,
                    'name' => $this->appliedBy->name,
                ]),
                'approved_by'  => $this->whenLoaded('approvedBy', fn () => [
                    'id'   => $this->approvedBy?->id,
                    'name' => $this->approvedBy?->name,
                ]),
                'disbursed_by' => $this->whenLoaded('disbursedBy', fn () => [
                    'id'   => $this->disbursedBy?->id,
                    'name' => $this->disbursedBy?->name,
                ]),
            ],

            // ── Links ─────────────────────────────────────────────────────────
            'links' => [
                'self'      => "/api/loans/{$this->id}",
                'schedule'  => "/api/loans/{$this->id}/schedule",
                'activity'  => "/api/loans/{$this->id}/activity",
                'statement' => "/api/reports/statement/{$this->id}",
                'borrower'  => "/api/borrowers/{$this->borrower_id}",
            ],
        ];
    }
}


// ═══════════════════════════════════════════════════════════════════════════════
// LoanCollection
// Appends portfolio-level aggregate totals to paginated loan list responses
// ═══════════════════════════════════════════════════════════════════════════════
