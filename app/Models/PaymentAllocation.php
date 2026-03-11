<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentAllocation extends Model
{
    protected $fillable = [
        'payment_id',
        'loan_schedule_id',
        'loan_id',
        'allocated_principal',
        'allocated_interest',
        'allocated_penalty',
        'allocated_total',
        'instalment_fully_paid',
    ];

    protected function casts(): array
    {
        return [
            'allocated_principal'   => 'decimal:2',
            'allocated_interest'    => 'decimal:2',
            'allocated_penalty'     => 'decimal:2',
            'allocated_total'       => 'decimal:2',
            'instalment_fully_paid' => 'boolean',
        ];
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function scheduleRow(): BelongsTo
    {
        return $this->belongsTo(LoanSchedule::class, 'loan_schedule_id');
    }

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }
}
