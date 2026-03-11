<?php

namespace App\Http\Resources;

use App\Http\Resources\BorrowerCollection;
use App\Http\Resources\BorrowerResource;
use App\Http\Resources\LoanResource;
use App\Http\Resources\LoanProductResource;
use App\Http\Resources\LoanCollection;
use App\Http\Resources\PaymentResource;
use App\Http\Resources\PenaltyResource;
use App\Http\Resources\CollateralAssetResource;
use App\Http\Resources\GuarantorResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\AuditLogResource;
use App\Http\Resources\ReminderResource;
use Illuminate\Http\JsonResponse;

class ReminderResource extends GracimorResource
{
    public function toArray($request): array
    {
        $isContactLog = $this->reminder_type === 'contact_log';

        return [
            'id'           => $this->id,
            'loan_id'      => $this->loan_id,
            'reminder_type'=> $this->reminder_type,
            'channel'      => $this->channel,
            'status'       => $this->statusBadge($this->status ?? 'pending'),

            // ── Contact log fields (only relevant for manual logs) ─────────────
            'contact' => $this->when($isContactLog, [
                'method'           => $this->contact_method,
                'outcome'          => $this->outcome,
                'outcome_label'    => match ($this->outcome) {
                    'connected_committed'   => 'Connected — committed to pay',
                    'connected_no_commitment' => 'Connected — no commitment',
                    'no_answer'             => 'No answer',
                    'not_reachable'         => 'Not reachable',
                    'wrong_number'          => 'Wrong number',
                    'visited_in'            => 'Field visit — attended',
                    default => ucfirst(str_replace('_', ' ', $this->outcome ?? '')),
                },
                'promise_date'     => $this->d($this->promise_date),
            ]),

            // ── SMS fields (only for automated messages) ──────────────────────
            'sms' => $this->when(!$isContactLog, [
                'recipient_phone' => $this->recipient_phone,
                'message_body'    => $this->when($this->isSenior(), $this->message_body),
                'provider_ref'    => $this->provider_ref,
            ]),

            'notes'      => $this->notes,
            'sent_by'    => $this->when($this->isSenior() && $this->sent_by, fn () => [
                'id'   => $this->sent_by,
            ]),
            'sent_at'    => $this->dt($this->sent_at),
        ];
    }
}
