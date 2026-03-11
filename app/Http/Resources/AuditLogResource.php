<?php

namespace App\Http\Resources;

class AuditLogResource extends GracimorResource
{
    public function toArray($request): array
    {
        // Parse the action into type and verb for colour-coding
        $parts      = explode('.', $this->action ?? '');
        $entityType = $parts[0] ?? 'system';
        $verb       = $parts[1] ?? $this->action;

        $typeColours = [
            'loan'     => 'blue',
            'payment'  => 'green',
            'borrower' => 'copper',
            'penalty'  => 'amber',
            'user'     => 'purple',
            'system'   => 'slate',
        ];

        return [
            'id'             => $this->id,
            'action'         => $this->action,
            'action_type'    => $entityType,
            'action_verb'    => $verb,
            'action_colour'  => $typeColours[$entityType] ?? 'slate',
            'description'    => $this->description,

            // ── Actor ─────────────────────────────────────────────────────────
            'actor' => $this->whenLoaded('user', fn () => $this->user
                ? [
                    'id'    => $this->user->id,
                    'name'  => $this->user->name,
                    'role'  => $this->user->role,
                    'initials' => collect(explode(' ', $this->user->name))
                        ->map(fn ($w) => strtoupper(substr($w, 0, 1)))
                        ->take(2)
                        ->implode(''),
                ]
                : ['id' => null, 'name' => 'System', 'role' => 'system', 'initials' => 'SY']
            ),
            'user_id' => $this->user_id,

            // ── Entity reference ──────────────────────────────────────────────
            'entity' => [
                'type' => $this->auditable_type
                    ? class_basename($this->auditable_type)
                    : null,
                'id'   => $this->auditable_id,
                'ref'  => $this->auditable_type && $this->auditable_id
                    ? class_basename($this->auditable_type) . '-' .
                      str_pad($this->auditable_id, 5, '0', STR_PAD_LEFT)
                    : null,
            ],

            // ── Metadata (senior staff only) ───────────────────────────────────
            'metadata'   => $this->when($this->isSenior(), $this->metadata),
            'ip_address' => $this->when($this->isSenior(), $this->ip_address),
            'user_agent' => $this->when($this->hasRole('superadmin'), $this->user_agent),

            'logged_at'  => $this->dt($this->created_at),
            'logged_at_human' => $this->created_at
                ? $this->created_at->diffForHumans()
                : null,
        ];
    }
}


// ═══════════════════════════════════════════════════════════════════════════════
// ReminderResource
// Used by: GET /api/overdue/log-contact results, embedded in loan activity
// ═══════════════════════════════════════════════════════════════════════════════
