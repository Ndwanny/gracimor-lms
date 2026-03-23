<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Loan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'loan_number',
        'borrower_id',
        'loan_product_id',
        'collateral_asset_id',
        'principal_amount',
        'interest_rate',
        'interest_method',
        'term_months',
        'first_repayment_date',
        'total_interest',
        'total_repayable',
        'monthly_instalment',
        'processing_fee',
        'ltv_at_origination',
        'disbursement_method',
        'disbursement_reference',
        'disbursed_at',
        'maturity_date',
        'status',
        'is_early_settled',
        'early_settled_at',
        'early_settlement_amount',
        'early_settlement_discount',
        'rejection_reason',
        'rejected_by',
        'rejected_at',
        'loan_purpose',
        'approval_notes',
        'disburse_notes',
        'applied_by',
        'approved_by',
        'approved_at',
        'disbursed_by',
        'borrower_signature',
        'officer_signature',
        'borrower_signed_at',
        'officer_signed_at',
    ];

    protected function casts(): array
    {
        return [
            'principal_amount'          => 'decimal:2',
            'interest_rate'             => 'decimal:2',
            'total_interest'            => 'decimal:2',
            'total_repayable'           => 'decimal:2',
            'monthly_instalment'        => 'decimal:2',
            'processing_fee'            => 'decimal:2',
            'ltv_at_origination'        => 'decimal:2',
            'early_settlement_amount'   => 'decimal:2',
            'early_settlement_discount' => 'decimal:2',
            'first_repayment_date'      => 'date',
            'disbursed_at'              => 'date',
            'maturity_date'             => 'date',
            'early_settled_at'          => 'date',
            'approved_at'               => 'datetime',
            'rejected_at'               => 'datetime',
            'is_early_settled'          => 'boolean',
        ];
    }

    // ── Status helpers ────────────────────────────────────────────────────

    public function isDraft(): bool      { return $this->status === 'draft'; }
    public function isPending(): bool    { return in_array($this->status, ['pending', 'pending_approval']); }
    public function isApproved(): bool   { return $this->status === 'approved'; }
    public function isActive(): bool     { return $this->status === 'active'; }
    public function isClosed(): bool     { return $this->status === 'closed'; }
    public function isDefaulted(): bool  { return $this->status === 'defaulted'; }
    public function isRejected(): bool   { return $this->status === 'rejected'; }

    public function canBeApproved(): bool  { return $this->isPending(); }
    public function canBeDisbursed(): bool { return $this->isApproved(); }
    public function canBeRejected(): bool  { return $this->isPending(); }

    public function canRecordPayment(): bool
    {
        return in_array($this->status, ['active', 'overdue']);
    }

    public function canEarlySettle(): bool
    {
        return in_array($this->status, ['active', 'overdue'])
            && ! $this->is_early_settled
            && $this->product->allow_early_settlement;
    }

    // ── Computed attributes ───────────────────────────────────────────────

    protected function outstandingBalance(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->balance?->total_outstanding ?? $this->total_repayable,
        );
    }

    protected function repaymentProgressPercent(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (! $this->balance || $this->total_repayable <= 0) {
                    return 0;
                }
                $paid = $this->total_repayable - $this->balance->total_outstanding;
                return min(100, round(($paid / $this->total_repayable) * 100, 1));
            }
        );
    }

    // ── Scopes ────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', ['pending', 'pending_approval']);
    }

    public function scopeOverdue($query)
    {
        return $query->where('loans.status', 'overdue');
    }

    public function scopeForBorrower($query, int $borrowerId)
    {
        return $query->where('borrower_id', $borrowerId);
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('loan_number', 'like', "%{$term}%")
              ->orWhereHas('borrower', fn ($bq) => $bq->search($term));
        });
    }

    // ── Relationships ─────────────────────────────────────────────────────

    public function borrower(): BelongsTo
    {
        return $this->belongsTo(Borrower::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(LoanProduct::class, 'loan_product_id');
    }

    public function loanProduct(): BelongsTo
    {
        return $this->product();
    }

    public function collateral(): BelongsTo
    {
        return $this->belongsTo(CollateralAsset::class, 'collateral_asset_id');
    }

    public function collateralAsset(): BelongsTo
    {
        return $this->collateral();
    }

    public function appliedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'applied_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function disbursedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disbursed_by');
    }

    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function schedule(): HasMany
    {
        return $this->hasMany(LoanSchedule::class)->orderBy('instalment_number');
    }

    public function pendingSchedule(): HasMany
    {
        return $this->hasMany(LoanSchedule::class)
            ->whereNotIn('status', ['paid', 'waived'])
            ->orderBy('instalment_number');
    }

    public function overdueSchedule(): HasMany
    {
        return $this->hasMany(LoanSchedule::class)
            ->where('status', 'overdue')
            ->orderBy('due_date');
    }

    public function balance(): HasOne
    {
        return $this->hasOne(LoanBalance::class);
    }

    public function loanBalance(): HasOne
    {
        return $this->balance();
    }

    public function loanSchedule(): HasMany
    {
        return $this->schedule();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->orderByDesc('payment_date');
    }

    public function penalties(): HasMany
    {
        return $this->hasMany(Penalty::class);
    }

    public function outstandingPenalties(): HasMany
    {
        return $this->hasMany(Penalty::class)->where('status', 'outstanding');
    }

    public function guarantors(): HasMany
    {
        return $this->hasMany(Guarantor::class);
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(LoanStatusHistory::class)->orderBy('created_at');
    }

    public function documents(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    public function reminders(): HasMany
    {
        return $this->hasMany(Reminder::class);
    }

    public function contactAttempts(): HasMany
    {
        return $this->hasMany(Reminder::class);
    }
}
