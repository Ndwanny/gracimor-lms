<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Role hierarchy — a user with a higher-index role passes any check
     * for a lower-index role automatically.
     */
    private const HIERARCHY = [
        'officer'    => 1,
        'accountant' => 2,
        'manager'    => 3,
        'ceo'        => 4,
        'superadmin' => 5,
    ];

    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        // Should never reach here without auth:sanctum, but guard defensively
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $userLevel = self::HIERARCHY[$user->role] ?? 0;

        // Resolve the minimum level required from the allowed roles list
        // e.g. middleware('role:manager,ceo') → minimum level = 3 (manager)
        // Any user at level 3+ passes.
        $requiredLevel = collect($roles)
            ->map(fn ($r) => self::HIERARCHY[$r] ?? 999)
            ->min();

        if ($userLevel >= $requiredLevel) {
            return $next($request);
        }

        return response()->json([
            'message' => 'This action is unauthorized.',
            'required_roles' => $roles,
            'your_role'      => $user->role,
        ], 403);
    }
}
