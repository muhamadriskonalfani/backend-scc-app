<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Kreait\Firebase\Factory;
use Symfony\Component\HttpFoundation\Response;

class VerifyAppCheck
{
    public function handle(
        Request $request,
        Closure $next
    ): Response {
        $token = $request->header('X-Firebase-AppCheck');

        if (!$token) {
            return response()->json([
                'message' => 'Firebase App Check token tidak ditemukan.',
            ], 401);
        }

        try {
            $factory = (new Factory())
                ->withServiceAccount(
                    config('firebase.credentials')
                );

            $appCheck = $factory->createAppCheck();

            $appCheck->verifyToken($token);

            return $next($request);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Firebase App Check token tidak valid.',
            ], 401);
        }
    }
}
