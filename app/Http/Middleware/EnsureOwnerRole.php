<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOwnerRole
{
    /**
     * Ensure the authenticated user has the 'owner' role (US-15, S5-T4).
     * Staff role is strictly restricted from issuing refunds or modifying Stripe credentials.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->attributes->get('user');

        if ($user && $user->role === 'staff') {
            return response()->json([
                'error' => 'Forbidden: Staff role is restricted from issuing refunds or modifying Stripe credentials.',
            ], 403);
        }

        return $next($request);
    }
}
