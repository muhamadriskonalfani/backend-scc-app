<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminStatusMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Belum login
        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated'
            ], 401);
        }

        // Bukan admin
        if (!in_array($user->role, ['admin', 'super_admin'])) {
            return response()->json([
                'message' => 'Akses khusus admin'
            ], 403);
        }

        // Status tidak aktif
        if ($user->status !== 'active') {
            return response()->json([
                'message' => 'Akun admin tidak aktif'
            ], 403);
        }

        return $next($request);
    }
}
