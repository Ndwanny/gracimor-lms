<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && !$user->is_active) {
            // Revoke the token so the client is forced to re-authenticate
            // once the account is re-enabled, rather than retrying indefinitely.
            $request->user()->currentAccessToken()?->delete();

            return response()->json([
                'message' => 'Your account is inactive. Please contact your administrator.',
            ], 403);
        }

        return $next($request);
    }
}
