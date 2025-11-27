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
use App\Models\PhanAnh;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $type = $request->query('type', 'Alert');
        $limit = min($request->query('limit', 20), 1000);
        $offset = $request->query('offset', 0);
        
        // Query incidents (PhanAnh model)
        $query = PhanAnh::query()
            ->where('trang_thai', '!=', 'draft')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->offset($offset);
        
        // Apply filters if provided
        if ($request->has('q')) {
            // Simple query support (can be extended)
            $q = $request->query('q');
            if (strpos($q, 'category==') !== false) {
                $category = str_replace(['category==', '"', "'"], '', $q);
                $query->where('danh_muc_id', $category);
            }
        }
        
        $incidents = $query->get();
        
        // Transform to NGSI-LD format
        $entities = $incidents->map(function ($incident) {
            return $this->transformToNgsiLd($incident);
        });
        
        return response()->json($entities, 200, [
            'Content-Type' => 'application/ld+json'
        ]);
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
        // Extract numeric ID from URN format
        // urn:ngsi-ld:Alert:123 -> 123
        $numericId = $this->extractIdFromUrn($id);
        
        $incident = PhanAnh::find($numericId);
        
        if (!$incident) {
            return response()->json([
                'type' => 'https://uri.etsi.org/ngsi-ld/errors/ResourceNotFound',
                'title' => 'Entity not found',
                'detail' => "Entity with id $id not found"
            ], 404);
        }
        
        $entity = $this->transformToNgsiLd($incident);
        
        return response()->json($entity, 200, [
            'Content-Type' => 'application/ld+json'
        ]);
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
            'tieu_de' => $entity['description']['value'] ?? 'Alert',
            'mo_ta' => $entity['description']['value'] ?? '',
            'danh_muc_id' => $this->mapCategoryReverse($entity['category']['value'] ?? 'other'),
            'muc_uu_tien_id' => $this->mapSeverityReverse($entity['severity']['value'] ?? 'medium'),
            'vi_do' => $entity['location']['value']['coordinates'][1] ?? 0,
            'kinh_do' => $entity['location']['value']['coordinates'][0] ?? 0,
            'dia_chi' => $entity['address']['value']['streetAddress'] ?? '',
            'trang_thai' => $entity['status']['value'] ?? 'pending',
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
     * Map severity string to priority ID
     */
    private function mapSeverity($severity)
    {
        return $this->mapSeverityReverse($severity);
    }
}
