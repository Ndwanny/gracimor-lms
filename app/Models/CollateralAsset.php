<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CollateralAsset extends Model
{
    use HasFactory, SoftDeletes;

    protected $appends = ['display_label'];

    protected $fillable = [
        'borrower_id',
        'asset_type',
        'vehicle_registration',
        'vehicle_make',
        'vehicle_model',
        'vehicle_year',
        'vehicle_color',
        'engine_number',
        'chassis_vin',
        'insurance_expiry',
        'insurance_company',
        'plot_number',
        'title_deed_number',
        'land_address',
        'land_size_sqm',
        'land_ownership_type',
        'land_use',
        'gps_latitude',
        'gps_longitude',
        'estimated_value',
        'valuation_date',
        'valuer_name',
        'valuation_firm',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'insurance_expiry' => 'date',
            'valuation_date'   => 'date',
            'estimated_value'  => 'decimal:2',
            'land_size_sqm'    => 'decimal:2',
            'gps_latitude'     => 'decimal:7',
            'gps_longitude'    => 'decimal:7',
        ];
    }

    // ── Computed attributes ───────────────────────────────────────────────

    protected function displayLabel(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->asset_type === 'vehicle') {
                    return "{$this->vehicle_make} {$this->vehicle_model} — {$this->vehicle_registration}";
                }

                $type = $this->land_use ? " — {$this->land_use}" : '';
                return "Plot {$this->plot_number}, {$this->land_address}{$type}";
            }
        );
    }

    protected function isPledged(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->status === 'pledged',
        );
    }

    protected function isAvailable(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->status === 'available',
        );
    }

    /**
     * Insurance expiry within 30 days.
     */
    public function insuranceExpiringSoon(): bool
    {
        if (! $this->insurance_expiry || $this->asset_type !== 'vehicle') {
            return false;
        }

        return $this->insurance_expiry->diffInDays(now()) <= 30
            && $this->insurance_expiry->isFuture();
    }

    /**
     * Calculate Loan-to-Value ratio for a given loan amount.
     */
    public function ltv(float $loanAmount): float
    {
        if (! $this->estimated_value || $this->estimated_value <= 0) {
            return 0;
        }

        return round(($loanAmount / $this->estimated_value) * 100, 2);
    }

    // ── Scopes ────────────────────────────────────────────────────────────

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    public function scopeVehicles($query)
    {
        return $query->where('asset_type', 'vehicle');
    }

    public function scopeLand($query)
    {
        return $query->where('asset_type', 'land');
    }

    // ── Relationships ─────────────────────────────────────────────────────

    public function borrower(): BelongsTo
    {
        return $this->belongsTo(Borrower::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    public function activeLoan(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Loan::class)->whereIn('status', ['active', 'approved']);
    }

    public function documents(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }
}
