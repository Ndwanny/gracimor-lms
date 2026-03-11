<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanBalance extends Model
{
    protected $fillable = [
        'loan_id',
        'principal_disbursed',
        'principal_paid',
        'principal_outstanding',
        'interest_charged',
        'interest_paid',
        'interest_outstanding',
        'penalty_charged',
        'penalty_paid',
        'penalty_outstanding',
        'total_outstanding',
        'instalments_total',
        'instalments_paid',
        'instalments_overdue',
        'last_payment_at',
        'last_payment_amount',
    ];

    protected function casts(): array
    {
        return [
            'principal_disbursed'   => 'decimal:2',
            'principal_paid'        => 'decimal:2',
            'principal_outstanding' => 'decimal:2',
            'interest_charged'      => 'decimal:2',
            'interest_paid'         => 'decimal:2',
            'interest_outstanding'  => 'decimal:2',
            'penalty_charged'       => 'decimal:2',
            'penalty_paid'          => 'decimal:2',
            'penalty_outstanding'   => 'decimal:2',
            'total_outstanding'     => 'decimal:2',
            'last_payment_at'       => 'datetime',
            'last_payment_amount'   => 'decimal:2',
        ];
    }

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    /**
     * Recalculate all derived fields from their component parts.
     * Called by PaymentService after each payment.
     */
    public function recalculate(): void
    {
        $this->principal_outstanding = max(0, $this->principal_disbursed - $this->principal_paid);
        $this->interest_outstanding  = max(0, $this->interest_charged  - $this->interest_paid);
        $this->penalty_outstanding   = max(0, $this->penalty_charged   - $this->penalty_paid);
        $this->total_outstanding     = round(
            $this->principal_outstanding + $this->interest_outstanding + $this->penalty_outstanding,
            2
        );
    }
}
