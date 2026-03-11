<?php

namespace App\Http\Resources;

class _UserControllerIntegration
{
    // index
    public function index($request): JsonResponse
    {
        $users = $query->paginate($request->per_page ?? 20);

        return \App\Http\Resources\UserResource::collection($users)->response();
    }

    // show / me
    public function show($user): JsonResponse
    {
        $user->loadCount(['loans as loans_count', 'loans as active_loans_count' => fn ($q) => $q->active()]);

        return \App\Http\Resources\UserResource::make($user)->response();
    }

    // store / update — wrap in resource with message
    public function store($request): JsonResponse
    {
        $user = \App\Models\User::create([...]);

        return response()->json([
            'message' => 'User account created.',
            'user'    => \App\Http\Resources\UserResource::make($user),
        ], 201);
    }

    // me
    public function me(): JsonResponse
    {
        return \App\Http\Resources\UserResource::make(auth()->user())->response();
    }
}


// ─────────────────────────────────────────────────────────────────────────────
// Audit Log (inline route closure in api.php)
// ─────────────────────────────────────────────────────────────────────────────

/*
 * In routes/api.php, update the audit log closure:
 *
 * Route::get('/', function (Request $request) {
 *     $query = \App\Models\AuditLog::with('user:id,name,role')
 *         ->latest()
 *         ->when($request->user_id, ...)
 *         ...;
 *
 *     // BEFORE: return response()->json($query->paginate(...));
 *     return \App\Http\Resources\AuditLogResource::collection(
 *         $query->paginate($request->per_page ?? 50)
 *     )->response();
 * });
 */


// ─────────────────────────────────────────────────────────────────────────────
// GuarantorController
// ─────────────────────────────────────────────────────────────────────────────
