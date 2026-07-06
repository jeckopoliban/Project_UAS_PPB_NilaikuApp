<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class EnsureSuperAdmin
{
    public function handle(Request $request, Closure $next): JsonResponse
    {
        $user = $request->user();

        if (! $user || $user->role !== 'super_admin') {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak, khusus Super Admin',
            ], 403);
        }

        return $next($request);
    }
}
