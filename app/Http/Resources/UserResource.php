<?php

namespace App\Http\Resources;

class UserResource extends GracimorResource
{
    public function toArray($request): array
    {
        $isSelf   = \Illuminate\Support\Facades\Auth::id() === $this->id;
        $canSeeAll = $this->isSenior() || $isSelf;

        return [
            'id'        => $this->id,
            'name'      => $this->name,
            'role'      => $this->role,
            'role_label'=> match ($this->role) {
                'superadmin' => 'Super Admin',
                'ceo'        => 'CEO',
                'manager'    => 'Manager',
                'officer'    => 'Loan Officer',
                'accountant' => 'Accountant',
                default      => ucfirst($this->role ?? ''),
            },
            'is_active' => (bool) $this->is_active,

            // ── Contact — visible to self or senior staff ──────────────────────
            'email' => $this->when($canSeeAll, $this->email),
            'phone' => $this->when($canSeeAll, $this->phone),

            // ── Performance stats (when eager-loaded) ─────────────────────────
            'stats' => $this->mergeWhen(
                $this->relationLoaded('loans') || isset($this->loans_count),
                fn () => [
                    'total_loans'  => $this->whenCounted('loans',
                        fn () => $this->loans_count
                    ),
                    'active_loans' => $this->whenCounted('loans',
                        fn () => $this->active_loans_count
                    ),
                ]
            ),

            // ── Last login (managers and self only) ────────────────────────────
            'last_login_at' => $this->when(
                $canSeeAll,
                fn () => $this->dt($this->last_login_at)
            ),

            'created_at' => $this->when($this->isSenior(), $this->dt($this->created_at)),

            'links' => [
                'self' => "/api/users/{$this->id}",
            ],
        ];
    }
}


// ═══════════════════════════════════════════════════════════════════════════════
// AuditLogResource
// Used by: GET /api/audit-log
// ═══════════════════════════════════════════════════════════════════════════════
