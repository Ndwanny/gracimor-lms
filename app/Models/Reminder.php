<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reminder extends Model
{
    protected $fillable = [
        'loan_id', 'loan_schedule_id', 'borrower_id', 'channel',
        'trigger_type', 'message_body', 'recipient_number',
        'status', 'provider_message_id', 'provider_response',
        'sent_at', 'delivered_at', 'is_automated', 'triggered_by',
    ];

    protected function casts(): array
    {
        return [
            'sent_at'      => 'datetime',
            'delivered_at' => 'datetime',
            'is_automated' => 'boolean',
        ];
    }

    public function scopeSent($query)     { return $query->where('status', 'sent'); }
    public function scopeFailed($query)   { return $query->where('status', 'failed'); }

    public function loan(): BelongsTo         { return $this->belongsTo(Loan::class); }
    public function scheduleRow(): BelongsTo  { return $this->belongsTo(LoanSchedule::class, 'loan_schedule_id'); }
    public function borrower(): BelongsTo     { return $this->belongsTo(Borrower::class); }
    public function triggeredBy(): BelongsTo  { return $this->belongsTo(User::class, 'triggered_by'); }
}
