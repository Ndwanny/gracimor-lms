<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoanSchedule extends Model
{
    use HasFactory;

    protected $table = 'loan_schedule';

    protected $fillable = [
        'loan_id',
        'instalment_number',
        'due_date',
        'principal_portion',
        'interest_portion',
        'total_due',
        'opening_balance',
        'closing_balance',
        'penalty_amount',
        'penalty_applied_at',
        'days_overdue',
        'amount_paid',
        'penalty_paid',
        'paid_at',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'due_date'           => 'date',
            'penalty_applied_at' => 'date',
            'paid_at'            => 'date',
            'principal_portion'  => 'decimal:2',
            'interest_portion'   => 'decimal:2',
            'total_due'          => 'decimal:2',
            'opening_balance'    => 'decimal:2',
            'closing_balance'    => 'decimal:2',
            'penalty_amount'     => 'decimal:2',
            'amount_paid'        => 'decimal:2',
            'penalty_paid'       => 'decimal:2',
        ];
    }

    // ── Computed attributes ───────────────────────────────────────────────

    /**
     * Total amount owed including any outstanding penalty.
     */
    protected function totalWithPenalty(): Attribute
    {
        return Attribute::make(
            get: fn () => round(
                ($this->total_due - $this->amount_paid) + ($this->penalty_amount - $this->penalty_paid),
                2
            ),
        );
    }

    protected function remainingPrincipal(): Attribute
    {
        return Attribute::make(
            get: fn () => max(0, round($this->principal_portion - $this->amount_paid, 2)),
        );
    }

    protected function outstandingPenalty(): Attribute
    {
        return Attribute::make(
            get: fn () => max(0, round($this->penalty_amount - $this->penalty_paid, 2)),
        );
    }

    protected function isFullyPaid(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->status === 'paid',
        );
    }

    protected function isOverdue(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->status === 'overdue',
        );
    }

    // ── Scopes ────────────────────────────────────────────────────────────

    public function scopeOverdue($query)
    {
        return $query->where('status', 'overdue');
    }

    public function scopeDue($query)
    {
        return $query->where('status', 'due');
    }

    public function scopeUnpaid($query)
    {
        return $query->whereNotIn('status', ['paid', 'waived']);
    }

    public function scopeDueBy($query, string $date)
    {
        return $query->where('due_date', '<=', $date)->unpaid();
    }

    // ── Relationships ─────────────────────────────────────────────────────

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    public function penalties(): HasMany
    {
        return $this->hasMany(Penalty::class);
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(PaymentAllocation::class);
    }

    public function reminders(): HasMany
    {
        return $this->hasMany(Reminder::class);
    }
}
