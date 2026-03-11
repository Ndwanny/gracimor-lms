<?php

namespace App\Http\Resources;

class CollateralAssetResource extends GracimorResource
{
    public function toArray($request): array
    {
        return [
            'id'               => $this->id,
            'collateral_type'  => $this->collateral_type,
            'collateral_type_label' => ucfirst($this->collateral_type ?? ''),

            // ── Asset description ─────────────────────────────────────────────
            'asset_description'    => $this->asset_description,
            'registration_number'  => $this->registration_number,

            // ── Vehicle-specific (only shown for vehicle collateral) ───────────
            'vehicle' => $this->when($this->collateral_type === 'vehicle', [
                'make'   => $this->make,
                'model'  => $this->model,
                'year'   => $this->year,
                'colour' => $this->colour,
            ]),

            // ── Land-specific ─────────────────────────────────────────────────
            'land' => $this->when($this->collateral_type === 'land', [
                'plot_number'          => $this->plot_number,
                'location_description' => $this->location_description,
                'title_deed_number'    => $this->title_deed_number,
            ]),

            // ── Ownership ────────────────────────────────────────────────────
            'owner' => [
                'name'       => $this->owner_name,
                'nrc_number' => $this->owner_nrc,
            ],

            // ── Valuation ────────────────────────────────────────────────────
            'valuation' => [
                'estimated_value' => $this->money($this->estimated_value),
                'valuation_date'  => $this->d($this->valuation_date),
                'valuation_source'=> $this->valuation_source,
                'months_old'      => $this->valuation_date
                    ? now()->diffInMonths($this->valuation_date)
                    : null,
                'is_stale'        => $this->valuation_date
                    ? now()->diffInMonths($this->valuation_date) > 6
                    : true,
            ],

            // ── Status ────────────────────────────────────────────────────────
            'status'     => $this->statusBadge($this->status ?? 'available'),

            // ── Loan count (when eager loaded) ────────────────────────────────
            'loan_count' => $this->whenCounted('loans', fn () => $this->loan_count),
            'loans' => $this->whenLoaded('loans', fn () =>
                $this->loans->map(fn ($l) => [
                    'id'          => $l->id,
                    'loan_number' => $l->loan_number,
                    'status'      => $l->status,
                    'borrower'    => $l->relationLoaded('borrower')
                        ? $l->borrower->full_name
                        : null,
                ])
            ),

            'notes'         => $this->notes,
            'registered_at' => $this->dt($this->created_at),

            'registered_by' => $this->whenLoaded('registeredBy', fn () => [
                'id'   => $this->registeredBy->id,
                'name' => $this->registeredBy->name,
            ]),

            // ── Links ─────────────────────────────────────────────────────────
            'links' => [
                'self' => "/api/collateral/{$this->id}",
            ],
        ];
    }
}


// ═══════════════════════════════════════════════════════════════════════════════
// GuarantorResource
// Used by: GuarantorController::index
//          LoanResource (whenLoaded)
// ═══════════════════════════════════════════════════════════════════════════════
