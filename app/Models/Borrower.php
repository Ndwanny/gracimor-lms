<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Borrower extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'borrower_number',
        'first_name',
        'last_name',
        'nrc_number',
        'date_of_birth',
        'gender',
        'phone_primary',
        'phone_secondary',
        'email',
        'residential_address',
        'city_town',
        'employment_status',
        'employer_name',
        'job_title',
        'monthly_income',
        'work_phone',
        'work_address',
        'kyc_status',
        'kyc_verified_at',
        'kyc_verified_by',
        'internal_notes',
        'photo_path',
        'assigned_officer_id',
        'registered_by',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth'   => 'date',
            'kyc_verified_at' => 'datetime',
            'monthly_income'  => 'decimal:2',
        ];
    }

    // ── Computed attributes ───────────────────────────────────────────────

    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn () => trim("{$this->first_name} {$this->last_name}"),
        );
    }

    protected function isKycVerified(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->kyc_status === 'verified',
        );
    }

    // ── Scopes ────────────────────────────────────────────────────────────

    public function scopeVerified($query)
    {
        return $query->where('kyc_status', 'verified');
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('first_name', 'like', "%{$term}%")
              ->orWhere('last_name', 'like', "%{$term}%")
              ->orWhere('borrower_number', 'like', "%{$term}%")
              ->orWhere('nrc_number', 'like', "%{$term}%")
              ->orWhere('phone_primary', 'like', "%{$term}%");
        });
    }

    // ── Relationships ─────────────────────────────────────────────────────

    public function registeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by');
    }

    public function assignedOfficer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_officer_id');
    }

    public function kycVerifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'kyc_verified_by');
    }

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    public function activeLoans(): HasMany
    {
        return $this->hasMany(Loan::class)->where('status', 'active');
    }

    public function collateralAssets(): HasMany
    {
        return $this->hasMany(CollateralAsset::class);
    }

    public function availableCollateral(): HasMany
    {
        return $this->hasMany(CollateralAsset::class)->where('status', 'available');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function documents(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    public function guarantors(): HasMany
    {
        return $this->hasMany(Guarantor::class, 'borrower_id');
    }
}
