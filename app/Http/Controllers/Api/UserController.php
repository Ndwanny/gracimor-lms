<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // ──────────────────────────────────────────────────────────────────────────
    // GET /api/users
    // Query params: search, role, active, per_page
    // ──────────────────────────────────────────────────────────────────────────
    public function index(Request $request): JsonResponse
    {
        $query = User::withCount([
            'appliedLoans as loans_count',
            'appliedLoans as active_loans_count' => fn ($q) => $q->active(),
        ])->latest();

        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%");
            });
        }

        if ($request->role) {
            $query->where('role', $request->role);
        }

        if ($request->has('active')) {
            $query->where('is_active', $request->boolean('active'));
        }

        return response()->json($query->paginate($request->per_page ?? 20));
    }

    // ──────────────────────────────────────────────────────────────────────────
    // POST /api/users
    // ──────────────────────────────────────────────────────────────────────────
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:150',
            'email'    => 'required|email|unique:users,email',
            'role'     => 'required|in:superadmin,ceo,manager,officer,accountant',
            'phone'    => 'nullable|string|max:20',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'role'      => $validated['role'],
            'phone'     => $validated['phone'] ?? null,
            'password'  => Hash::make($validated['password']),
            'is_active' => true,
        ]);

        return response()->json([
            'message' => 'User account created.',
            'user'    => $user->makeHidden('password'),
        ], 201);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // GET /api/users/{user}
    // ──────────────────────────────────────────────────────────────────────────
    public function show(User $user): JsonResponse
    {
        $user->loadCount([
            'appliedLoans as loans_count',
            'appliedLoans as active_loans_count' => fn ($q) => $q->active(),
        ]);

        return response()->json($user->makeHidden('password'));
    }

    // ──────────────────────────────────────────────────────────────────────────
    // PUT /api/users/{user}
    // ──────────────────────────────────────────────────────────────────────────
    public function update(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'name'      => 'sometimes|required|string|max:150',
            'email'     => "sometimes|required|email|unique:users,email,{$user->id}",
            'role'      => 'sometimes|required|in:superadmin,ceo,manager,officer,accountant',
            'phone'     => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        // Prevent demoting the last superadmin
        if (
            isset($validated['role']) &&
            $validated['role'] !== 'superadmin' &&
            $user->role === 'superadmin' &&
            User::where('role', 'superadmin')->where('is_active', true)->count() === 1
        ) {
            return response()->json(['message' => 'Cannot change the role of the last active superadmin.'], 422);
        }

        $user->update($validated);

        return response()->json([
            'message' => 'User updated.',
            'user'    => $user->fresh()->makeHidden('password'),
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // POST /api/users/{user}/deactivate
    // ──────────────────────────────────────────────────────────────────────────
    public function deactivate(User $user): JsonResponse
    {
        if ($user->id === Auth::id()) {
            return response()->json(['message' => 'You cannot deactivate your own account.'], 422);
        }

        $user->update(['is_active' => false]);

        return response()->json(['message' => "{$user->name} has been deactivated."]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // POST /api/users/{user}/reset-password
    // Body: { password }  — manager/superadmin only
    // ──────────────────────────────────────────────────────────────────────────
    public function resetPassword(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user->update(['password' => Hash::make($request->password)]);

        return response()->json(['message' => 'Password reset successfully.']);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // GET /api/users/me
    // Returns the authenticated user's own profile
    // ──────────────────────────────────────────────────────────────────────────
    public function me(): JsonResponse
    {
        return response()->json(Auth::user()->makeHidden('password'));
    }

    // ──────────────────────────────────────────────────────────────────────────
    // PUT /api/users/me/profile
    // Self-service profile update (name, phone, password)
    // ──────────────────────────────────────────────────────────────────────────
    public function updateProfile(Request $request): JsonResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name'             => 'sometimes|required|string|max:150',
            'phone'            => 'nullable|string|max:20',
            'current_password' => 'required_with:password|string',
            'password'         => 'nullable|string|min:8|confirmed',
        ]);

        if (isset($validated['password'])) {
            if (!Hash::check($validated['current_password'], $user->password)) {
                return response()->json(['message' => 'Current password is incorrect.'], 422);
            }
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update(array_filter($validated, fn ($k) => $k !== 'current_password', ARRAY_FILTER_USE_KEY));

        return response()->json([
            'message' => 'Profile updated.',
            'user'    => $user->fresh()->makeHidden('password'),
        ]);
    }
}
