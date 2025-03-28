<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;

class VerifyAdminToken
{
    public function handle(Request $request, Closure $next)
    {
        // Récupérer le token depuis l'en-tête Authorization
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['error' => 'Token manquant'], 401);
        }

        try {
            // Vérifier et décoder le token
            $decoded = JWT::decode($token, new Key(env('JWT_SECRET'), 'HS256'));

            // Vérifier si l'email dans le token correspond à celui de l'admin dans .env
            if ($decoded->email !== env('ADMIN_EMAIL')) {
                return response()->json(['error' => 'Accès refusé'], 403);
            }

            return $next($request); // Continuer vers la route protégée

        } catch (Exception $e) {
            return response()->json(['error' => 'Token invalide ou expiré'], 401);
        }
    }
}
