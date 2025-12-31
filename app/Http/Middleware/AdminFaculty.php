<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminFaculty
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // 1. Harus login
        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated'
            ], 401);
        }

        // 2. Hanya role admin
        if ($user->role !== 'admin') {
            return response()->json([
                'message' => 'Unauthorized role'
            ], 403);
        }

        // 3. Admin wajib punya admin_profile
        if (!$user->adminProfile) {
            return response()->json([
                'message' => 'Admin profile tidak ditemukan'
            ], 403);
        }

        // 4. Inject faculty_id ke request
        $request->merge([
            'admin_faculty_id' => $user->adminProfile->faculty_id
        ]);

        return $next($request);
    }
}
