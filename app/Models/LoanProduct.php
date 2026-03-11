<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LoanProduct extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'description',
        'collateral_type',
        'default_interest_rate',
        'interest_method',
        'min_interest_rate',
        'max_interest_rate',
        'min_term_months',
        'max_term_months',
        'default_term_months',
        'min_loan_amount',
        'max_loan_amount',
        'max_ltv_percent',
        'processing_fee_fixed',
        'processing_fee_percent',
        'penalty_rate_percent',
        'grace_period_days',
        'allow_early_settlement',
        'early_settlement_method',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'default_interest_rate'   => 'decimal:2',
            'min_interest_rate'       => 'decimal:2',
            'max_interest_rate'       => 'decimal:2',
            'min_loan_amount'         => 'decimal:2',
            'max_loan_amount'         => 'decimal:2',
            'max_ltv_percent'         => 'decimal:2',
            'processing_fee_fixed'    => 'decimal:2',
            'processing_fee_percent'  => 'decimal:2',
            'penalty_rate_percent'    => 'decimal:2',
            'allow_early_settlement'  => 'boolean',
            'is_active'               => 'boolean',
        ];
    }

    // ── Scopes ────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForCollateral($query, string $type)
    {
        return $query->whereIn('collateral_type', [$type, 'both']);
    }

    // ── Computed helpers ──────────────────────────────────────────────────

    /**
     * Calculate the processing fee for a given principal.
     * Uses fixed fee if set, otherwise applies the percentage.
     */
    public function calculateProcessingFee(float $principal): float
    {
        if ($this->processing_fee_fixed > 0) {
            return (float) $this->processing_fee_fixed;
        }

        return round($principal * $this->processing_fee_percent / 100, 2);
    }

    /**
     * Calculate the penalty amount for a given instalment total.
     */
    public function calculatePenalty(float $instalmentTotal): float
    {
        return round($instalmentTotal * $this->penalty_rate_percent / 100, 2);
    }

    /**
     * Check whether a given loan amount respects the product's LTV limit.
     */
    public function ltvWithinLimit(float $loanAmount, float $collateralValue): bool
    {
        if ($collateralValue <= 0) {
            return false;
        }

        $ltv = ($loanAmount / $collateralValue) * 100;

        return $ltv <= $this->max_ltv_percent;
    }

    // ── Relationships ─────────────────────────────────────────────────────

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }
}
