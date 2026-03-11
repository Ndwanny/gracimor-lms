<?php

namespace App\Http\Resources;

class GuarantorResource extends GracimorResource
{
    public function toArray($request): array
    {
        // If linked to a registered borrower, pull from that model
        $borrowerData = $this->whenLoaded('borrower', fn () => $this->borrower
            ? [
                'id'              => $this->borrower->id,
                'borrower_number' => $this->borrower->borrower_number,
                'full_name'       => $this->borrower->full_name,
                'nrc_number'      => $this->borrower->nrc_number,
                'phone_primary'   => $this->borrower->phone_primary,
                'kyc_status'      => $this->borrower->kyc_status,
            ]
            : null
        );

        return [
            'id'                    => $this->id,
            'loan_id'               => $this->loan_id,
            'is_registered_borrower'=> !is_null($this->guarantor_borrower_id),

            // ── Registered borrower profile link ──────────────────────────────
            'registered_borrower' => $borrowerData,
            'guarantor_borrower_id' => $this->guarantor_borrower_id,

            // ── Manual / override details ─────────────────────────────────────
            'full_name'    => $this->full_name
                ?? ($this->relationLoaded('borrower') ? $this->borrower?->full_name : null),
            'nrc_number'   => $this->nrc_number
                ?? ($this->relationLoaded('borrower') ? $this->borrower?->nrc_number : null),
            'phone'        => $this->phone
                ?? ($this->relationLoaded('borrower') ? $this->borrower?->phone_primary : null),
            'relationship' => $this->relationship,
            'address'      => $this->address,
            'employer'     => $this->employer,
            'monthly_income' => $this->money($this->monthly_income),

            'added_at' => $this->dt($this->created_at),
            'added_by' => $this->when($this->isSenior(), $this->added_by),
        ];
    }
}
