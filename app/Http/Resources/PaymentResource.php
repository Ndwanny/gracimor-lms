<?php

namespace App\Http\Resources;

class PaymentResource extends GracimorResource
{
    public function toArray($request): array
    {
        // Build allocation summary from loaded allocations (if present)
        $allocationSummary = null;
        if ($this->relationLoaded('paymentAllocations') && $this->paymentAllocations->count()) {
            $allocationSummary = [
                'principal' => $this->money(
                    $this->paymentAllocations->where('allocation_type', 'principal')->sum('amount')
                ),
                'interest'  => $this->money(
                    $this->paymentAllocations->where('allocation_type', 'interest')->sum('amount')
                ),
                'penalty'   => $this->money(
                    $this->paymentAllocations->where('allocation_type', 'penalty')->sum('amount')
                ),
            ];
        }

        return [
            'id'             => $this->id,
            'receipt_number' => $this->receipt_number,

            // ── Loan context ──────────────────────────────────────────────────
            'loan' => $this->whenLoaded('loan', fn () => [
                'id'          => $this->loan->id,
                'loan_number' => $this->loan->loan_number,
                'borrower'    => $this->loan->relationLoaded('borrower')
                    ? [
                        'id'       => $this->loan->borrower->id,
                        'full_name'=> $this->loan->borrower->full_name,
                        'phone'    => $this->loan->borrower->phone_primary,
                    ]
                    : ['id' => $this->loan->borrower_id],
            ]),
            'loan_id' => $this->loan_id,

            // ── Payment details ───────────────────────────────────────────────
            'amount'          => $this->money($this->amount),
            'payment_method'  => $this->payment_method,
            'payment_method_label' => match ($this->payment_method) {
                'cash'           => 'Cash',
                'bank_transfer'  => 'Bank Transfer',
                'mobile_money'   => 'Mobile Money',
                'cheque'         => 'Cheque',
                default          => ucfirst($this->payment_method ?? ''),
            },
            'payment_date'    => $this->d($this->payment_date),
            'payment_date_iso'=> $this->dt($this->payment_date),
            'reference'       => $this->reference,

            // ── Allocation breakdown ──────────────────────────────────────────
            'allocation_summary' => $allocationSummary,
            'allocations' => $this->whenLoaded('paymentAllocations', fn () =>
                $this->paymentAllocations->map(fn ($a) => [
                    'id'              => $a->id,
                    'allocation_type' => $a->allocation_type,
                    'amount'          => $this->money($a->amount),
                    'instalment_ref'  => $a->loan_schedule_id,
                ])
            ),

            // ── Status ────────────────────────────────────────────────────────
            'status'    => $this->statusBadge($this->status ?? 'paid'),
            'is_reversal'  => (bool) ($this->is_reversal ?? false),
            'reversed_at'  => $this->dt($this->reversed_at ?? null),
            'reversal_reason' => $this->when(
                $this->isSenior() && !empty($this->reversal_reason),
                $this->reversal_reason
            ),

            // ── Staff ─────────────────────────────────────────────────────────
            'recorded_by' => $this->whenLoaded('recordedBy', fn () => [
                'id'   => $this->recordedBy->id,
                'name' => $this->recordedBy->name,
            ]),

            'notes'      => $this->when($this->isSenior(), $this->notes),
            'recorded_at'=> $this->dt($this->created_at),

            // ── Links ─────────────────────────────────────────────────────────
            'links' => [
                'self'    => "/api/payments/{$this->id}",
                'receipt' => "/api/payments/{$this->id}/receipt",
                'loan'    => "/api/loans/{$this->loan_id}",
            ],
        ];
    }
}
