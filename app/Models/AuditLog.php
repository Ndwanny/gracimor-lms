<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditLog extends Model
{
    // Append-only — no updated_at
    const UPDATED_AT = null;

    protected $fillable = [
        'user_id', 'user_role', 'action', 'description',
        'auditable_type', 'auditable_id',
        'old_values', 'new_values',
        'ip_address', 'user_agent', 'url', 'http_method',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
        ];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }

    public function auditable(): MorphTo { return $this->morphTo(); }

    /**
     * Write a new audit entry. Use this throughout service classes.
     *
     * @param  string       $action    e.g. 'loan.approved'
     * @param  Model|null   $subject   The affected Eloquent model
     * @param  array        $old
     * @param  array        $new
     */
    public static function record(
        string $action,
        ?Model $subject = null,
        array $old = [],
        array $new = [],
        ?string $description = null
    ): self {
        /** @var \App\Models\User|null $user */
        $user = auth()->user();

        return self::create([
            'user_id'        => $user?->id,
            'user_role'      => $user?->role,
            'action'         => $action,
            'description'    => $description,
            'auditable_type' => $subject ? get_class($subject) : null,
            'auditable_id'   => $subject?->getKey(),
            'old_values'     => $old ?: null,
            'new_values'     => $new ?: null,
            'ip_address'     => request()->ip(),
            'user_agent'     => request()->userAgent(),
            'url'            => request()->fullUrl(),
            'http_method'    => request()->method(),
        ]);
    }
}
