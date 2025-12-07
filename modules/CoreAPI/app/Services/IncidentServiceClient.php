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

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Firebase\JWT\JWT;

/**
 * Client for IncidentService API
 */
class IncidentServiceClient
{
    private string $baseUrl;
    private string $jwtToken;

    public function __construct()
    {
        $this->baseUrl = config('services.incident_service.url', 'http://incident-service:8005');
        $this->jwtToken = $this->generateJWT();
    }

    /**
     * Generate JWT token for service-to-service authentication
     */
    private function generateJWT(): string
    {
        $payload = [
            'sub' => 'core_api_service',
            'user_id' => 0, // System user
            'email' => 'system@cityresq.vn',
            'role' => 'ADMIN',
            'iat' => time(),
            'exp' => time() + 3600, // 1 hour
        ];

        $secret = config('services.incident_service.jwt_secret', 'your-jwt-secret-key-here');

        return JWT::encode($payload, $secret, 'HS256');
    }

    /**
     * Create a new incident in IncidentService
     *
     * @param array $data
     * @return array|null
     */
    public function createIncident(array $data): ?array
    {
        try {
            Log::info('IncidentServiceClient: Creating incident', ['data' => $data]);

            $response = Http::timeout(10)
                ->withHeaders([
                    'Authorization' => "Bearer {$this->jwtToken}",
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->post("{$this->baseUrl}/api/v1/incidents", $data);

            if ($response->successful()) {
                $result = $response->json();
                Log::info('IncidentServiceClient: Incident created successfully', [
                    'incident_id' => $result['data']['id'] ?? null,
                ]);
                return $result['data'] ?? null;
            }

            Log::error('IncidentServiceClient: Failed to create incident', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('IncidentServiceClient: Exception while creating incident', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    /**
     * Get incident details by ID
     *
     * @param int $incidentId
     * @return array|null
     */
    public function getIncident(int $incidentId): ?array
    {
        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'Authorization' => "Bearer {$this->jwtToken}",
                    'Accept' => 'application/json',
                ])
                ->get("{$this->baseUrl}/api/v1/incidents/{$incidentId}");

            if ($response->successful()) {
                return $response->json('data');
            }

            return null;
        } catch (\Exception $e) {
            Log::error('IncidentServiceClient: Failed to get incident', [
                'incident_id' => $incidentId,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Update incident status
     *
     * @param int $incidentId
     * @param array $data
     * @return array|null
     */
    public function updateIncident(int $incidentId, array $data): ?array
    {
        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'Authorization' => "Bearer {$this->jwtToken}",
                    'Content-Type' => 'application/json',
                ])
                ->patch("{$this->baseUrl}/api/v1/incidents/{$incidentId}", $data);

            if ($response->successful()) {
                return $response->json('data');
            }

            return null;
        } catch (\Exception $e) {
            Log::error('IncidentServiceClient: Failed to update incident', [
                'incident_id' => $incidentId,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Check if IncidentService is healthy
     *
     * @return bool
     */
    public function healthCheck(): bool
    {
        try {
            $response = Http::timeout(5)->get("{$this->baseUrl}/health");
            return $response->successful() && $response->json('status') === 'healthy';
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Map priority from CoreAPI to IncidentService format
     *
     * @param int|null $uuTienId
     * @return string
     */
    public static function mapPriority(?int $uuTienId): string
    {
        return match ($uuTienId) {
            4 => 'CRITICAL', // Urgent/Emergency
            3 => 'HIGH',
            2 => 'MEDIUM',
            1 => 'LOW',
            default => 'MEDIUM',
        };
    }
}
