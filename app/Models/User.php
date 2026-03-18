<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'is_active',
        'avatar_path',
        'last_login_at',
        'last_login_ip',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at'     => 'datetime',
            'is_active'         => 'boolean',
            'password'          => 'hashed',
        ];
    }

    // ── Role helpers ──────────────────────────────────────────────────────

    public function isSuperAdmin(): bool  { return $this->role === 'superadmin'; }
    public function isCeo(): bool         { return $this->role === 'ceo'; }
    public function isManager(): bool     { return $this->role === 'manager'; }
    public function isOfficer(): bool     { return $this->role === 'officer'; }
    public function isAccountant(): bool  { return $this->role === 'accountant'; }

    /**
     * Can approve loans (officer or above).
     */
    public function canApproveLoan(): bool
    {
        return in_array($this->role, ['superadmin', 'ceo', 'manager', 'officer']);
    }

    /**
     * Can disburse funds (officer or above).
     */
    public function canDisburseFunds(): bool
    {
        return in_array($this->role, ['superadmin', 'ceo', 'manager', 'officer']);
    }

    // ── Relationships ─────────────────────────────────────────────────────

    public function registeredBorrowers(): HasMany
    {
        return $this->hasMany(Borrower::class, 'registered_by');
    }

    public function assignedBorrowers(): HasMany
    {
        return $this->hasMany(Borrower::class, 'assigned_officer_id');
    }

    public function appliedLoans(): HasMany
    {
        return $this->hasMany(Loan::class, 'applied_by');
    }

    public function approvedLoans(): HasMany
    {
        return $this->hasMany(Loan::class, 'approved_by');
    }

    public function disbursedLoans(): HasMany
    {
        return $this->hasMany(Loan::class, 'disbursed_by');
    }

    public function recordedPayments(): HasMany
    {
        return $this->hasMany(Payment::class, 'recorded_by');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }
}
