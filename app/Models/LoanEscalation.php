<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanEscalation extends Model
{
    use HasFactory;

    protected $table = 'loan_escalations';

    protected $fillable = [
        'loan_id',
        'escalation_type',
        'assigned_to',
        'notes',
        'status',
        'days_overdue_at_escalation',
        'outstanding_at_escalation',
        'escalated_by',
        'resolved_at',
        'resolved_by',
        'resolution_notes',
    ];

    protected $casts = [
        'resolved_at'              => 'datetime',
        'outstanding_at_escalation'=> 'decimal:2',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    public function escalatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'escalated_by');
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function resolve(int $resolvedBy, string $notes = ''): void
    {
        $this->update([
            'status'           => 'resolved',
            'resolved_at'      => now(),
            'resolved_by'      => $resolvedBy,
            'resolution_notes' => $notes,
        ]);
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }
}
