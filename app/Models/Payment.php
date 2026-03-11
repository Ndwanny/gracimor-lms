<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'receipt_number',
        'loan_id',
        'borrower_id',
        'amount_received',
        'towards_principal',
        'towards_interest',
        'towards_penalty',
        'overpayment',
        'balance_before',
        'balance_after',
        'payment_type',
        'payment_method',
        'payment_reference',
        'payment_provider',
        'payment_date',
        'notes',
        'is_reversed',
        'reversed_at',
        'reversed_by',
        'reversal_reason',
        'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'amount_received'    => 'decimal:2',
            'towards_principal'  => 'decimal:2',
            'towards_interest'   => 'decimal:2',
            'towards_penalty'    => 'decimal:2',
            'overpayment'        => 'decimal:2',
            'balance_before'     => 'decimal:2',
            'balance_after'      => 'decimal:2',
            'payment_date'       => 'date',
            'reversed_at'        => 'datetime',
            'is_reversed'        => 'boolean',
        ];
    }

    // ── Scopes ────────────────────────────────────────────────────────────

    public function scopeNotReversed($query)
    {
        return $query->where('is_reversed', false);
    }

    public function scopeForDate($query, string $date)
    {
        return $query->where('payment_date', $date);
    }

    public function scopeForMonth($query, int $year, int $month)
    {
        return $query->whereYear('payment_date', $year)
                     ->whereMonth('payment_date', $month);
    }

    // ── Relationships ─────────────────────────────────────────────────────

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    public function borrower(): BelongsTo
    {
        return $this->belongsTo(Borrower::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function reversedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reversed_by');
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(PaymentAllocation::class);
    }

    public function paymentAllocations(): HasMany
    {
        return $this->hasMany(PaymentAllocation::class);
    }
}
