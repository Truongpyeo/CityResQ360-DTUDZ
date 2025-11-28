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

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseController;
use App\Http\Resources\NgsiLdResource;
use App\Models\PhanAnh;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

/**
 * NGSI-LD API Controller
 * 
 * Implements NGSI-LD (Next Generation Service Interfaces - Linked Data)
 * standard for smart city data sharing as required by OLP 2025 competition.
 * 
 * Specification: ETSI GS CIM 009 V1.6.1
 * @see https://www.etsi.org/deliver/etsi_gs/CIM/001_099/009/01.06.01_60/gs_CIM009v010601p.pdf
 */
class NgsiLdController extends BaseController
{
    /**
     * Get entities list (NGSI-LD)
     * 
     * GET /ngsi-ld/v1/entities
     * 
     * Query parameters:
     * - type: Entity type filter (e.g., "Alert", "Incident")
     * - q: Query expression
     * - georel: Geo-relationship (near, within, etc.)
     * - geometry: Geometry type for geo-queries
     * - coordinates: Coordinates for geo-queries
     * - limit: Max results (default: 20, max: 1000)
     * - offset: Pagination offset
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getEntities(Request $request)
    {
        try {
            $type = $request->query('type', 'Alert');
            $limit = min($request->query('limit', 20), 1000);
            $offset = $request->query('offset', 0);
            
            // Base query
            $query = PhanAnh::query()
                ->where('trang_thai', '!=', 4) // Exclude rejected
                ->with(['nguoiDung', 'coQuanXuLy'])
                ->orderBy('created_at', 'desc');
            
            // Apply type filter
            if ($type && !in_array($type, ['Alert', 'WeatherObserved'])) {
                return response()->json([
                    'type' => 'https://uri.etsi.org/ngsi-ld/errors/BadRequestData',
                    'title' => 'Unsupported entity type',
                    'detail' => "Entity type '$type' not supported. Supported types: Alert, WeatherObserved"
                ], 400);
            }
            
            // Query appropriate model based on type
            if ($type === 'WeatherObserved') {
                $weatherQuery = \App\Models\WeatherObservation::query()
                    ->orderBy('observed_at', 'desc');
                
                // Apply geo-query for weather if provided
                if ($request->has('georel') && $request->has('geometry') && $request->has('coordinates')) {
                    $this->applyGeoQueryWeather($weatherQuery, $request);
                }
                
                $totalCount = $weatherQuery->count();
                $observations = $weatherQuery->limit($limit)->offset($offset)->get();
                
                $entities = \App\Http\Resources\WeatherObservedResource::collection($observations);
                
                return response()->json($entities, 200, [
                    'Content-Type' => 'application/ld+json',
                    'X-Total-Count' => $totalCount
                ]);
            }
            
            // Apply geo-query if provided
            if ($request->has('georel') && $request->has('geometry') && $request->has('coordinates')) {
                $this->applyGeoQuery($query, $request);
            }
            
            // Apply attribute filters (q parameter)
            if ($request->has('q')) {
                $this->applyQueryFilter($query, $request->query('q'));
            }
            
            // Get total count (before pagination)
            $totalCount = $query->count();
            
            // Apply pagination
            $incidents = $query->limit($limit)->offset($offset)->get();
            
            // Transform using NgsiLdResource
            $entities = NgsiLdResource::collection($incidents);
            
            return response()->json($entities, 200, [
                'Content-Type' => 'application/ld+json',
                'X-Total-Count' => $totalCount
            ]);
            
        } catch (\Exception $e) {
            Log::error('NGSI-LD Get Entities Error: ' . $e->getMessage());
            return response()->json([
                'type' => 'https://uri.etsi.org/ngsi-ld/errors/InternalError',
                'title' => 'Internal server error',
                'detail' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Apply geo-query filters
     * 
     * Supports:
     * - near: Near a point (with max distance)
     * - within: Within a geometry (bounding box)
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param Request $request
     */
    private function applyGeoQuery($query, $request)
    {
        $georel = $request->query('georel'); // e.g., "near;maxDistance==5000"
        $geometry = $request->query('geometry'); // e.g., "Point" or "Polygon"
        $coordinates = $request->query('coordinates'); // e.g., "[106.7,10.7]"
        
        // Parse coordinates
        $coords = json_decode($coordinates, true);
        
        if ($georel === 'near' || str_starts_with($georel, 'near;')) {
            // Near query (proximity)
            if ($geometry === 'Point' && is_array($coords) && count($coords) >= 2) {
                $longitude = $coords[0];
                $latitude = $coords[1];
                
                // Extract maxDistance if provided
                $maxDistance = 5000; // Default 5km
                if (preg_match('/maxDistance==(\d+)/', $georel, $matches)) {
                    $maxDistance = (int)$matches[1];
                }
                
                // Calculate distance in meters using Haversine formula
                $query->selectRaw("*, 
                    (6371000 * acos(
                        cos(radians(?)) * cos(radians(vi_do)) * 
                        cos(radians(kinh_do) - radians(?)) + 
                        sin(radians(?)) * sin(radians(vi_do))
                    )) AS distance", [$latitude, $longitude, $latitude])
                    ->having('distance', '<=', $maxDistance)
                    ->orderBy('distance');
            }
        } elseif ($georel === 'within') {
            // Within bounding box
            if ($geometry === 'Polygon' && is_array($coords) && isset($coords[0])) {
                // Bounding box: [[minLng, minLat], [maxLng, maxLat]]
                $bounds = $coords[0];
                if (count($bounds) >= 4) {
                    $minLng = min(array_column($bounds, 0));
                    $maxLng = max(array_column($bounds, 0));
                    $minLat = min(array_column($bounds, 1));
                    $maxLat = max(array_column($bounds, 1));
                    
                    $query->whereBetween('kinh_do', [$minLng, $maxLng])
                          ->whereBetween('vi_do', [$minLat, $maxLat]);
                }
            }
        }
    }
    
    /**
     * Apply attribute query filters
     * 
     * Supports simple queries like:
     * - category=="traffic"
     * - severity=="high"
     * - status=="pending"
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $queryString
     */
    private function applyQueryFilter($query, $queryString)
    {
        // Parse simple equality queries
        if (preg_match('/(\w+)==["\']?([^"\']+)["\']?/', $queryString, $matches)) {
            $attribute = $matches[1];
            $value = $matches[2];
            
            switch ($attribute) {
                case 'category':
                    $categoryMap = array_flip([
                        0 => 'traffic', 1 => 'environment', 2 => 'fire',
                        3 => 'waste', 4 => 'flood', 5 => 'other'
                    ]);
                    if (isset($categoryMap[$value])) {
                        $query->where('danh_muc', $categoryMap[$value]);
                    }
                    break;
                    
                case 'status':
                    $statusMap = array_flip([
                        0 => 'pending', 1 => 'verified', 2 => 'in_progress',
                        3 => 'resolved', 4 => 'rejected'
                    ]);
                    if (isset($statusMap[$value])) {
                        $query->where('trang_thai', $statusMap[$value]);
                    }
                    break;
                    
                case 'severity':
                    $severityMap = array_flip([
                        1 => 'low', 2 => 'medium', 3 => 'high', 4 => 'critical'
                    ]);
                    if (isset($severityMap[$value])) {
                        $query->where('muc_uu_tien_id', $severityMap[$value]);
                    }
                    break;
            }
        }
    }
    
    /**
     * Get single entity by ID
     * 
     * GET /ngsi-ld/v1/entities/{entityId}
     * 
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getEntity($id)
    {
        try {
            // Extract numeric ID from URN format
            // urn:ngsi-ld:Alert:123 -> 123
            $numericId = $this->extractIdFromUrn($id);
            
            $incident = PhanAnh::with(['nguoiDung', 'coQuanXuLy'])->find($numericId);
            
            if (!$incident) {
                return response()->json([
                    'type' => 'https://uri.etsi.org/ngsi-ld/errors/ResourceNotFound',
                    'title' => 'Entity not found',
                    'detail' => "Entity with id $id not found"
                ], 404);
            }
            
            // Use NgsiLdResource
            $entity = new NgsiLdResource($incident);
            
            return response()->json($entity, 200, [
                'Content-Type' => 'application/ld+json'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'type' => 'https://uri.etsi.org/ngsi-ld/errors/InternalError',
                'title' => 'Internal server error',
                'detail' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Create new entity
     * 
     * POST /ngsi-ld/v1/entities
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createEntity(Request $request)
    {
        Log::info('NGSI-LD Create Entity Request: ', $request->all());
        try {
            $data = $request->all();
            
            // Validate NGSI-LD format
            if (!isset($data['type']) || !isset($data['@context'])) {
                return response()->json([
                    'type' => 'https://uri.etsi.org/ngsi-ld/errors/BadRequestData',
                    'title' => 'Invalid NGSI-LD entity',
                    'detail' => 'Entity must have type and @context'
                ], 400);
            }
            
            // Transform NGSI-LD to internal format
            $incidentData = $this->transformFromNgsiLd($data);
            
            // Create incident
            $incident = PhanAnh::create($incidentData);
            
            $entityId = $this->generateUrn($incident->id);
            
            return response()->json([
                'id' => $entityId
            ], 201, [
                'Location' => "/ngsi-ld/v1/entities/$entityId"
            ]);
        } catch (\Exception $e) {
            Log::error('NGSI-LD Create Entity Error: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            
            return response()->json([
                'type' => 'https://uri.etsi.org/ngsi-ld/errors/InternalError',
                'title' => 'Internal server error',
                'detail' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Update entity attributes
     * 
     * PATCH /ngsi-ld/v1/entities/{entityId}/attrs
     * 
     * @param Request $request
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateEntityAttrs(Request $request, $id)
    {
        $numericId = $this->extractIdFromUrn($id);
        $incident = PhanAnh::find($numericId);
        
        if (!$incident) {
            return response()->json([
                'type' => 'https://uri.etsi.org/ngsi-ld/errors/ResourceNotFound',
                'title' => 'Entity not found'
            ], 404);
        }
        
        $attrs = $request->all();
        $updateData = [];
        
        // Map NGSI-LD attributes to database fields
        if (isset($attrs['status'])) {
            $updateData['trang_thai'] = $attrs['status']['value'];
        }
        
        if (isset($attrs['severity'])) {
            $updateData['muc_uu_tien_id'] = $this->mapSeverity($attrs['severity']['value']);
        }
        
        $incident->update($updateData);
        
        return response()->json(null, 204);
    }
    
    /**
     * Delete entity
     * 
     * DELETE /ngsi-ld/v1/entities/{entityId}
     * 
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteEntity($id)
    {
        $numericId = $this->extractIdFromUrn($id);
        $incident = PhanAnh::find($numericId);
        
        if (!$incident) {
            return response()->json([
                'type' => 'https://uri.etsi.org/ngsi-ld/errors/ResourceNotFound',
                'title' => 'Entity not found'
            ], 404);
        }
        
        $incident->delete();
        
        return response()->json(null, 204);
    }
    
    /**
     * Transform internal incident model to NGSI-LD format
     * 
     * Maps to FiWARE Smart Data Model: Alert
     * @see https://github.com/smart-data-models/dataModel.Alert/blob/master/Alert/doc/spec.md
     * 
     * @param PhanAnh $incident
     * @return array
     */
    private function transformToNgsiLd($incident)
    {
        return [
            'id' => $this->generateUrn($incident->id),
            'type' => 'Alert',
            '@context' => [
                'https://uri.etsi.org/ngsi-ld/v1/ngsi-ld-core-context.jsonld',
                url('/@context.jsonld')
            ],
            
            // Properties
            'category' => [
                'type' => 'Property',
                'value' => $this->mapCategory($incident->danh_muc_id)
            ],
            
            'severity' => [
                'type' => 'Property',
                'value' => $this->mapSeverityLevel($incident->muc_uu_tien_id)
            ],
            
            'alertSource' => [
                'type' => 'Property',
                'value' => 'citizen-report'
            ],
            
            'description' => [
                'type' => 'Property',
                'value' => $incident->mo_ta ?? ''
            ],
            
            'dateIssued' => [
                'type' => 'Property',
                'value' => [
                    '@type' => 'DateTime',
                    '@value' => $incident->created_at->toIso8601String()
                ]
            ],
            
            'validTo' => $incident->resolved_at ? [
                'type' => 'Property',
                'value' => [
                    '@type' => 'DateTime',
                    '@value' => $incident->resolved_at->toIso8601String()
                ]
            ] : null,
            
            // GeoProperty
            'location' => [
                'type' => 'GeoProperty',
                'value' => [
                    'type' => 'Point',
                    'coordinates' => [
                        (float)$incident->kinh_do,
                        (float)$incident->vi_do
                    ]
                ]
            ],
            
            'address' => [
                'type' => 'Property',
                'value' => [
                    'addressLocality' => 'Ho Chi Minh City',
                    'addressCountry' => 'VN',
                    'streetAddress' => $incident->dia_chi ?? ''
                ]
            ],
            
            // Relationships
            'subCategory' => [
                'type' => 'Property',
                'value' => $incident->danhMuc->ten_danh_muc ?? 'other'
            ],
            
            // Custom properties (using our domain)
            'voteCount' => [
                'type' => 'Property',
                'value' => $incident->binhChons()->count()
            ],
            
            'viewCount' => [
                'type' => 'Property',
                'value' => $incident->luot_xem ?? 0
            ],
            
            'status' => [
                'type' => 'Property',
                'value' => $incident->trang_thai
            ]
        ];
    }
    
    /**
     * Transform NGSI-LD entity to internal format
     * 
     * @param array $entity
     * @return array
     */
    private function transformFromNgsiLd($entity)
    {
        return [
            'tieu_de' => data_get($entity, 'description.value', 'Alert'),
            'mo_ta' => data_get($entity, 'description.value', ''),
            'danh_muc_id' => $this->mapCategoryReverse(data_get($entity, 'category.value', 'other')),
            'uu_tien_id' => $this->mapSeverityReverse(data_get($entity, 'severity.value', 'medium')),
            'vi_do' => data_get($entity, 'location.value.coordinates.1', 0),
            'kinh_do' => data_get($entity, 'location.value.coordinates.0', 0),
            'dia_chi' => data_get($entity, 'address.value.streetAddress', ''),
            'trang_thai' => $this->mapStatusReverse(data_get($entity, 'status.value', 'pending')),
            'nguoi_dung_id' => auth()->id() ?? 1, // Default or from auth
        ];
    }
    
    /**
     * Generate URN for entity ID
     * Format: urn:ngsi-ld:Alert:{id}
     */
    private function generateUrn($id)
    {
        return "urn:ngsi-ld:Alert:$id";
    }
    
    /**
     * Extract numeric ID from URN
     */
    private function extractIdFromUrn($urn)
    {
        if (is_numeric($urn)) {
            return $urn;
        }
        
        // urn:ngsi-ld:Alert:123 -> 123
        $parts = explode(':', $urn);
        return end($parts);
    }
    
    /**
     * Map internal category to NGSI-LD category
     */
    private function mapCategory($categoryId)
    {
        $map = [
            1 => 'traffic',
            2 => 'environment',
            3 => 'infrastructure',
            4 => 'publicService',
            5 => 'safety',
            6 => 'health',
        ];
        
        return $map[$categoryId] ?? 'other';
    }
    
    /**
     * Map NGSI-LD category to internal
     */
    private function mapCategoryReverse($category)
    {
        $map = [
            'traffic' => 1,
            'environment' => 2,
            'infrastructure' => 3,
            'publicService' => 4,
            'safety' => 5,
            'health' => 6,
        ];
        
        return $map[$category] ?? 1;
    }
    
    /**
     * Map internal priority to severity level
     */
    private function mapSeverityLevel($priorityId)
    {
        $map = [
            1 => 'low',
            2 => 'medium',
            3 => 'high',
            4 => 'critical',
        ];
        
        return $map[$priorityId] ?? 'medium';
    }
    
    /**
     * Map severity to internal priority
     */
    private function mapSeverityReverse($severity)
    {
        $map = [
            'low' => 1,
            'medium' => 2,
            'high' => 3,
            'critical' => 4,
        ];
        
        return $map[$severity] ?? 2;
    }
    
    /**
     * Map status string to internal integer
     */
    private function mapStatusReverse($status)
    {
        $map = [
            'pending' => 0,
            'verified' => 1,
            'in_progress' => 2,
            'resolved' => 3,
            'rejected' => 4,
        ];
        
        return $map[$status] ?? 0;
    }

    /**
     * Map severity string to priority ID
     */
    private function mapSeverity($severity)
    {
        return $this->mapSeverityReverse($severity);
    }
}
