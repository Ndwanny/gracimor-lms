<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Penalty extends Model
{
    protected $fillable = [
        'loan_id',
        'loan_schedule_id',
        'borrower_id',
        'penalty_amount',
        'penalty_rate_used',
        'applied_date',
        'days_overdue_at_application',
        'status',
        'cleared_by_payment_id',
        'cleared_at',
        'waived_by',
        'waiver_reason',
        'waived_at',
        'is_system_generated',
        'applied_by',
    ];

    protected function casts(): array
    {
        return [
            'penalty_amount'     => 'decimal:2',
            'penalty_rate_used'  => 'decimal:2',
            'applied_date'       => 'date',
            'cleared_at'         => 'datetime',
            'waived_at'          => 'datetime',
            'is_system_generated'=> 'boolean',
        ];
    }

    public function scopeOutstanding($query)
    {
        return $query->where('status', 'outstanding');
    }

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    public function scheduleRow(): BelongsTo
    {
        return $this->belongsTo(LoanSchedule::class, 'loan_schedule_id');
    }

    public function borrower(): BelongsTo
    {
        return $this->belongsTo(Borrower::class);
    }

    public function clearedByPayment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'cleared_by_payment_id');
    }

    public function waivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'waived_by');
    }

    public function appliedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'applied_by');
    }
}
