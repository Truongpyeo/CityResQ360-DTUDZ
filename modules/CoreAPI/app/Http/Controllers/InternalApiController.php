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

namespace App\Http\Controllers;

use App\Models\ClientModuleCredential;
use Illuminate\Http\Request;

class InternalApiController extends Controller
{
    /**
     * Verify credential for service authentication
     * Called by microservices to validate JWT tokens from external clients
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyCredential(Request $request)
    {
        $clientId = $request->query('client_id');
        $moduleKey = $request->query('module_key');

        if (!$clientId || !$moduleKey) {
            return response()->json([
                'success' => false,
                'message' => 'Missing client_id or module_key'
            ], 400);
        }

        $credential = ClientModuleCredential::where('client_id', $clientId)
            ->whereHas('module', function ($q) use ($moduleKey) {
                $q->where('module_key', $moduleKey);
            })
            ->with('module')
            ->first();

        if (!$credential) {
            return response()->json([
                'success' => false,
                'message' => 'Credential not found'
            ], 404);
        }

        // Make jwt_secret visible for verification
        return response()->json([
            'success' => true,
            'data' => $credential->makeVisible('jwt_secret')
        ]);
    }

    /**
     * Log API usage from microservices (optional)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logUsage(Request $request)
    {
        // TODO: Implement usage logging if needed
        return response()->json([
            'success' => true,
            'message' => 'Usage logged'
        ]);
    }
}
