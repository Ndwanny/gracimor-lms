<?php

namespace App\Http\Resources;

class BorrowerResource extends GracimorResource
{
    public function toArray($request): array
    {
        return [
            // ── Identity ──────────────────────────────────────────────────────
            'id'              => $this->id,
            'borrower_number' => $this->borrower_number,
            'full_name'       => $this->full_name,       // accessor on model
            'first_name'      => $this->first_name,
            'last_name'       => $this->last_name,
            'nrc_number'      => $this->nrc_number,
            'date_of_birth'   => $this->d($this->date_of_birth),
            'age'             => $this->date_of_birth
                ? now()->diffInYears($this->date_of_birth)
                : null,
            'gender'          => $this->gender,

            // ── Contact ───────────────────────────────────────────────────────
            'phone_primary'   => $this->phone_primary,
            'phone_secondary' => $this->phone_secondary,
            'email'           => $this->email,
            'residential_address' => $this->residential_address,
            'city_town'       => $this->city_town,

            // ── Employment ────────────────────────────────────────────────────
            'employment_status' => $this->employment_status,
            'employer_name'   => $this->employer_name,
            'job_title'       => $this->job_title,
            'monthly_income'  => $this->money($this->monthly_income),
            'work_phone'      => $this->work_phone,
            'work_address'    => $this->work_address,

            // ── KYC ───────────────────────────────────────────────────────────
            'kyc' => [
                'status'      => $this->kyc_status,
                'badge'       => $this->statusBadge($this->kyc_status ?? 'pending'),
                'verified_at' => $this->dt($this->kyc_verified_at),
                'verified_by' => $this->whenLoaded('kycVerifiedBy', fn () =>
                    UserResource::make($this->kycVerifiedBy)->only(['id', 'name'])
                ),
            ],

            // ── Officer ───────────────────────────────────────────────────────
            'assigned_officer' => $this->whenLoaded('assignedOfficer', fn () => [
                'id'   => $this->assignedOfficer->id,
                'name' => $this->assignedOfficer->name,
            ]),

            // ── Loan summary counts ───────────────────────────────────────────
            'loan_counts' => [
                'total'  => $this->whenCounted('loans', fn () => $this->loans_count),
                'active' => $this->whenCounted('loans', fn () => $this->active_loans_count),
            ],

            // ── Active loans (lightweight, only when loaded) ──────────────────
            'loans' => LoanResource::collection(
                $this->whenLoaded('loans')
            ),

            // ── Documents ─────────────────────────────────────────────────────
            'documents' => $this->whenLoaded('documents', fn () =>
                $this->documents->map(fn ($doc) => [
                    'id'            => $doc->id,
                    'document_type' => $doc->document_type,
                    'file_name'     => $doc->file_name,
                    'mime_type'     => $doc->mime_type,
                    'file_size_kb'  => $doc->file_size ? round($doc->file_size / 1024, 1) : null,
                    'expiry_date'   => $this->d($doc->expiry_date),
                    'uploaded_at'   => $this->dt($doc->created_at),
                    'download_url'  => "/api/borrowers/{$this->id}/documents/{$doc->id}/download",
                ])
            ),

            // ── Photo ─────────────────────────────────────────────────────────
            'photo_url' => $this->photo_path
                ? "/storage/{$this->photo_path}"
                : null,

            // ── Internal notes (managers and above only) ──────────────────────
            'internal_notes' => $this->when($this->isSenior(), $this->internal_notes),

            // ── Timestamps ───────────────────────────────────────────────────
            'registered_at'  => $this->dt($this->created_at),
            'updated_at'     => $this->dt($this->updated_at),

            // ── Links ─────────────────────────────────────────────────────────
            'links' => [
                'self'      => "/api/borrowers/{$this->id}",
                'loans'     => "/api/loans?borrower_id={$this->id}",
                'statement' => "/api/borrowers/{$this->id}/statement",
            ],
        ];
    }
}


// ═══════════════════════════════════════════════════════════════════════════════
// BorrowerCollection
// Wraps paginated borrower lists with portfolio-level metadata
// ═══════════════════════════════════════════════════════════════════════════════
