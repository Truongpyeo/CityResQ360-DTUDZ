<?php
/*
 * CityResQ360-DTUDZ - Smart City Emergency Response System
 * Copyright (C) 2025 DTU-DZ Team
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Http\Middleware;

use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use App\Models\NguoiDung;

class JwtAuthenticate
{
    /**
     * Handle an incoming request.
     * Supports both Sanctum and JWT authentication.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $authHeader = $request->header('Authorization');

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            // If no JWT, fall back to Sanctum
            if (auth('sanctum')->check()) {
                return $next($request);
            }

            return response()->json([
                'success' => false,
                'message' => 'Unauthorized - Missing or invalid authorization header'
            ], 401);
        }

        $token = substr($authHeader, 7); // Remove "Bearer " prefix

        // Check if token is Sanctum format (contains |)
        if (str_contains($token, '|')) {
            // Let Sanctum handle it
            if (auth('sanctum')->check()) {
                return $next($request);
            }

            return response()->json([
                'success' => false,
                'message' => 'Unauthorized - Invalid Sanctum token'
            ], 401);
        }

        // Verify JWT token
        try {
            $decoded = JWT::decode(
                $token,
                new Key(config('services.jwt_secret'), 'HS256')
            );

            // Check if user exists and is active
            $userId = $decoded->user_id ?? $decoded->sub ?? null;

            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid token - Missing user_id'
                ], 401);
            }

            $user = NguoiDung::where('id', $userId)
                ->where('trang_thai', 1)
                ->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found or account disabled'
                ], 403);
            }

            // Attach user to request
            auth('sanctum')->setUser($user);

            Log::info('JWT authentication successful', [
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => $user->vai_tro
            ]);

            return $next($request);

        } catch (\Firebase\JWT\ExpiredException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token expired'
            ], 401);
        } catch (\Firebase\JWT\SignatureInvalidException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid token signature'
            ], 401);
        } catch (\Exception $e) {
            Log::error('JWT authentication failed', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'JWT authentication failed'
            ], 500);
        }
    }
}
