<?php

namespace App\Http\Resources;

class PenaltyResource extends GracimorResource
{
    public function toArray($request): array
    {
        return [
            'id'         => $this->id,

            // ── Loan context (lightweight) ────────────────────────────────────
            'loan' => $this->whenLoaded('loan', fn () => [
                'id'          => $this->loan->id,
                'loan_number' => $this->loan->loan_number,
                'borrower'    => $this->loan->relationLoaded('borrower')
                    ? [
                        'id'       => $this->loan->borrower->id,
                        'full_name'=> $this->loan->borrower->full_name,
                    ]
                    : ['id' => $this->loan->borrower_id],
            ]),
            'loan_id' => $this->loan_id,

            // ── Instalment reference ──────────────────────────────────────────
            'instalment' => $this->whenLoaded('loanSchedule', fn () => $this->loanSchedule
                ? [
                    'id'               => $this->loanSchedule->id,
                    'instalment_number'=> $this->loanSchedule->instalment_number,
                    'due_date'         => $this->d($this->loanSchedule->due_date),
                ]
                : null
            ),
            'loan_schedule_id' => $this->loan_schedule_id,

            // ── Amounts ───────────────────────────────────────────────────────
            'amount'        => $this->money($this->amount),
            'rate_applied'  => $this->percent($this->rate_applied),
            'basis'         => $this->basis,
            'basis_label'   => $this->basis === 'instalment'
                ? 'On overdue instalment amount'
                : 'On outstanding balance',

            // ── Context ───────────────────────────────────────────────────────
            'days_overdue'     => (int) ($this->days_overdue ?? 0),
            'days_after_grace' => (int) ($this->days_after_grace ?? 0),

            // ── Status ────────────────────────────────────────────────────────
            'status' => $this->statusBadge($this->status ?? 'outstanding'),

            // ── Waiver details (only shown when waived) ───────────────────────
            'waiver' => $this->when($this->status === 'waived', [
                'reason'    => $this->waiver_reason,
                'notes'     => $this->waiver_notes,
                'waived_at' => $this->dt($this->waived_at),
                'waived_by' => $this->whenLoaded('waivedBy', fn () => [
                    'id'   => $this->waivedBy?->id,
                    'name' => $this->waivedBy?->name,
                ]),
            ]),

            'notes'      => $this->notes,
            'applied_at' => $this->dt($this->applied_at),
        ];
    }
}


// ═══════════════════════════════════════════════════════════════════════════════
// CollateralAssetResource
// Used by: CollateralController::index, show, store, update
//          LoanResource (whenLoaded as 'collateral')
// ═══════════════════════════════════════════════════════════════════════════════
