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
use App\Http\Resources\OrganizationNgsiResource;
use App\Http\Resources\PersonNgsiResource;
use App\Http\Resources\CategoryNgsiResource;
use App\Models\PhanAnh;
use App\Models\CoQuanXuLy;
use App\Models\NguoiDung;
use App\Models\DanhMucPhanAnh;
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
     * @OA\Get(
     *     path="/ngsi-ld/v1/entities",
     *     operationId="getEntities",
     *     tags={"NGSI-LD OpenData"},
     *     summary="Truy vấn NGSI-LD entities",
     *     description="Truy vấn các entities với bộ lọc linh hoạt. Hỗ trợ 5 loại entity: Alert (Phản ánh), Organization (Tổ chức), Person (Người dùng), Category (Danh mục), WeatherObserved (Thời tiết).

VÍ DỤ: Alerts về giao thông (?type=Alert&q=category==traffic), Tổ chức cấp thành phố (?type=Organization&level=0), Phân trang (?limit=10&offset=20), Tìm gần điểm (?georel=near;maxDistance==5000&geometry=Point&coordinates=[106.7,10.8])",
     *
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Loại entity cần truy vấn",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={"Alert", "Organization", "Person", "Category", "WeatherObserved"},
     *             default="Alert"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Số lượng kết quả tối đa (1-1000)",
     *         required=false,
     *         @OA\Schema(type="integer", default=20, minimum=1, maximum=1000),
     *         example=20
     *     ),
     *     @OA\Parameter(
     *         name="offset",
     *         in="query",
     *         description="Vị trí bắt đầu cho phân trang",
     *         required=false,
     *         @OA\Schema(type="integer", default=0, minimum=0),
     *         example=0
     *     ),
     *     @OA\Parameter(
     *         name="q",
     *         in="query",
     *         description="Bộ lọc thuộc tính. Ví dụ: category==traffic (lọc giao thông), severity==high (mức độ cao), status==pending (đang chờ)",
     *         required=false,
     *         @OA\Schema(type="string"),
     *         example="category==traffic"
     *     ),
     *     @OA\Parameter(
     *         name="georel",
     *         in="query",
     *         description="Quan hệ địa lý (near, within)",
     *         required=false,
     *         @OA\Schema(type="string"),
     *         example="near;maxDistance==5000"
     *     ),
     *     @OA\Parameter(
     *         name="geometry",
     *         in="query",
     *         description="Loại hình học (Point, Polygon)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"Point", "Polygon"}),
     *         example="Point"
     *     ),
     *     @OA\Parameter(
     *         name="coordinates",
     *         in="query",
     *         description="Tọa độ dạng JSON [longitude, latitude]",
     *         required=false,
     *         @OA\Schema(type="string"),
     *         example="[106.7,10.8]"
     *     ),
     *     @OA\Parameter(
     *         name="level",
     *         in="query",
     *         description="Cấp tổ chức (chỉ dùng cho Organization): 0=Thành phố, 1=Quận, 2=Phường",
     *         required=false,
     *         @OA\Schema(type="integer", enum={0, 1, 2}),
     *         example=0
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Thành công - Trả về danh sách entities dạng JSON-LD",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="string", example="urn:ngsi-ld:Alert:1"),
     *                 @OA\Property(property="type", type="string", example="Alert"),
     *                 @OA\Property(property="@context", type="array", @OA\Items(type="string"))
     *             )
     *         ),
     *         @OA\Header(header="Content-Type", description="application/ld+json", @OA\Schema(type="string")),
     *         @OA\Header(header="X-Total-Count", description="Tổng số entities", @OA\Schema(type="integer"))
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Lỗi request không hợp lệ",
     *         @OA\JsonContent(
     *             @OA\Property(property="type", type="string", example="https://uri.etsi.org/ngsi-ld/errors/BadRequestData"),
     *             @OA\Property(property="title", type="string", example="Unsupported entity type"),
     *             @OA\Property(property="detail", type="string")
     *         )
     *     )
     * )
     */
    public function getEntities(Request $request)
    {
        try {
            $type = $request->query('type', 'Alert');
            $limit = min($request->query('limit', 20), 1000);
            $offset = $request->query('offset', 0);

            // Supported entity types
            $supportedTypes = ['Alert', 'WeatherObserved', 'Organization', 'Person', 'Category'];

            // Apply type filter
            if ($type && !in_array($type, $supportedTypes)) {
                return response()->json([
                    'type' => 'https://uri.etsi.org/ngsi-ld/errors/BadRequestData',
                    'title' => 'Unsupported entity type',
                    'detail' => "Entity type '$type' not supported. Supported types: " . implode(', ', $supportedTypes)
                ], 400);
            }

            // Route to appropriate handler based on type
            switch ($type) {
                case 'Organization':
                    return $this->getOrganizations($request, $limit, $offset);
                case 'Person':
                    return $this->getPersons($request, $limit, $offset);
                case 'Category':
                    return $this->getCategories($request, $limit, $offset);
                case 'WeatherObserved':
                    return $this->getWeatherObserved($request, $limit, $offset);
                default:
                    // Alert (default)
                    return $this->getAlerts($request, $limit, $offset);
            }
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
                        1 => 'traffic',
                        2 => 'environment',
                        3 => 'fire',
                        4 => 'waste',
                        5 => 'flood',
                        6 => 'other'
                    ]);
                    if (isset($categoryMap[$value])) {
                        $query->where('danh_muc_id', $categoryMap[$value]); // Use danh_muc_id
                    }
                    break;

                case 'status':
                    $statusMap = array_flip([
                        0 => 'pending',
                        1 => 'verified',
                        2 => 'in_progress',
                        3 => 'resolved',
                        4 => 'rejected'
                    ]);
                    if (isset($statusMap[$value])) {
                        $query->where('trang_thai', $statusMap[$value]);
                    }
                    break;

                case 'severity':
                    $severityMap = array_flip([
                        1 => 'low',
                        2 => 'medium',
                        3 => 'high',
                        4 => 'critical'
                    ]);
                    if (isset($severityMap[$value])) {
                        $query->where('uu_tien_id', $severityMap[$value]); // Use muc_uu_tien_id
                    }
                    break;
            }
        }
    }

    /**
     * @OA\Get(
     *     path="/ngsi-ld/v1/entities/{id}",
     *     operationId="getEntityById",
     *     tags={"NGSI-LD OpenData"},
     *     summary="Lấy entity theo ID",
     *     description="Truy vấn một entity cụ thể bằng URN. Format ID: urn:ngsi-ld:EntityType:ID (ví dụ: urn:ngsi-ld:Alert:1, urn:ngsi-ld:Organization:5)",
     *     
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Entity URN (Uniform Resource Name)",
     *         @OA\Schema(type="string"),
     *         example="urn:ngsi-ld:Alert:1"
     *     ),
     *     
     *     @OA\Response(
     *         response=200,
     *         description="Thành công - Trả về entity dạng JSON-LD",
     *         @OA\JsonContent(
     *             type="object",
             @OA\Property(property="id", type="string", example="urn:ngsi-ld:Alert:1"),
             @OA\Property(property="type", type="string", example="Alert"),
             @OA\Property(property="@context", type="array", @OA\Items(type="string"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy entity",
     *         @OA\JsonContent(
     *             @OA\Property(property="type", type="string", example="https://uri.etsi.org/ngsi-ld/errors/ResourceNotFound"),
     *             @OA\Property(property="title", type="string", example="Entity not found"),
     *             @OA\Property(property="detail", type="string")
     *         )
     *     )
     * )
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

    /**
     * Get Organizations (CoQuanXuLy)
     */
    private function getOrganizations(Request $request, $limit, $offset)
    {
        $query = CoQuanXuLy::query()
            ->where('trang_thai', 1) // Active only
            ->orderBy('created_at', 'desc');

        // Filter by level if provided
        if ($request->has('level')) {
            $query->where('cap_do', $request->query('level'));
        }

        $totalCount = $query->count();
        $organizations = $query->limit($limit)->offset($offset)->get();

        $entities = OrganizationNgsiResource::collection($organizations);

        return response()->json($entities, 200, [
            'Content-Type' => 'application/ld+json',
            'X-Total-Count' => $totalCount
        ]);
    }

    /**
     * Get Persons (NguoiDung)
     */
    private function getPersons(Request $request, $limit, $offset)
    {
        $query = NguoiDung::query()
            ->where('trang_thai', 1) // Active only
            ->orderBy('created_at', 'desc');

        // Note: email_verified_at column doesn't exist in nguoi_dungs table
        // Filter by email verified (handle column name properly)
        // if ($request->query('emailVerified') === 'true') {
        //     $query->whereNotNull('email_verified_at');
        // }

        $totalCount = $query->count();
        $persons = $query->limit($limit)->offset($offset)->get();

        $entities = PersonNgsiResource::collection($persons);

        return response()->json($entities, 200, [
            'Content-Type' => 'application/ld+json',
            'X-Total-Count' => $totalCount
        ]);
    }

    /**
     * Get Categories (DanhMucPhanAnh)
     */
    private function getCategories(Request $request, $limit, $offset)
    {
        $query = DanhMucPhanAnh::query()
            ->where('trang_thai', 1) // Active only
            ->orderBy('created_at', 'desc'); // Order by created_at instead

        $totalCount = $query->count();
        $categories = $query->limit($limit)->offset($offset)->get();

        $entities = CategoryNgsiResource::collection($categories);

        return response()->json($entities, 200, [
            'Content-Type' => 'application/ld+json',
            'X-Total-Count' => $totalCount
        ]);
    }

    /**
     * Get WeatherObserved (extracted from original getEntities)
     */
    private function getWeatherObserved(Request $request, $limit, $offset)
    {
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

    /**
     * Get Alerts (extracted from original getEntities)
     */
    private function getAlerts(Request $request, $limit, $offset)
    {
        $query = PhanAnh::query()
            ->where('trang_thai', '!=', 4) // Exclude rejected
            ->with(['nguoiDung', 'coQuanXuLy'])
            ->orderBy('created_at', 'desc');

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
    }

    /**
     * Apply geo-query for weather data
     */
    private function applyGeoQueryWeather($query, $request)
    {
        // Similar to applyGeoQuery but for weather table structure
        // Implementation depends on weather table schema
        // Placeholder for now
    }

    /**
     * @OA\Get(
     *     path="/ngsi-ld/v1/types",
     *     operationId="getTypes",
     *     tags={"NGSI-LD OpenData"},
     *     summary="Liệt kê các loại entity có sẵn",
     *     description="Trả về danh sách các loại entity (types) được hỗ trợ trong hệ thống. Mỗi type đại diện cho một loại dữ liệu khác nhau như Alert, Organization, Person, Category, WeatherObserved.",
     *
     *     @OA\Response(
     *         response=200,
     *         description="Thành công - Danh sách entity types",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="types",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="string", example="Alert"),
     *                     @OA\Property(property="typeName", type="string", example="Alert"),
     *                     @OA\Property(property="description", type="string", example="Phản ánh sự cố từ người dân"),
     *                     @OA\Property(property="attributeNames", type="array", @OA\Items(type="string"))
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function getTypes()
    {
        $types = [
            [
                'id' => 'Alert',
                'typeName' => 'Alert',
                'description' => 'Phản ánh sự cố từ người dân (Incident Reports)',
                'attributeNames' => ['category', 'description', 'severity', 'location', 'dateObserved', 'validFrom', 'validTo', 'data', 'alertSource']
            ],
            [
                'id' => 'Organization',
                'typeName' => 'Organization',
                'description' => 'Tổ chức/Cơ quan xử lý (Government Organizations)',
                'attributeNames' => ['name', 'email', 'address', 'level', 'status']
            ],
            [
                'id' => 'Person',
                'typeName' => 'Person',
                'description' => 'Người dùng hệ thống (System Users)',
                'attributeNames' => ['name', 'email', 'address', 'telephone', 'reputation', 'status']
            ],
            [
                'id' => 'Category',
                'typeName' => 'Category',
                'description' => 'Danh mục phân loại (Incident Categories)',
                'attributeNames' => ['name', 'description', 'icon', 'color', 'status']
            ],
            [
                'id' => 'WeatherObserved',
                'typeName' => 'WeatherObserved',
                'description' => 'Quan trắc thời tiết (Weather Observations)',
                'attributeNames' => ['temperature', 'relativeHumidity', 'precipitation', 'windSpeed', 'atmosphericPressure', 'dateObserved', 'location']
            ]
        ];

        return response()->json([
            'types' => $types,
            '@context' => config('app.url') . '/@context.jsonld'
        ], 200, [
            'Content-Type' => 'application/ld+json'
        ]);
    }

    /**
     * @OA\Get(
     *     path="/ngsi-ld/v1/types/{type}",
     *     operationId="getTypeSchema",
     *     tags={"NGSI-LD OpenData"},
     *     summary="Lấy schema của một entity type",
     *     description="Trả về JSON Schema chi tiết của một entity type cụ thể, bao gồm các thuộc tính, kiểu dữ liệu, và mô tả của từng field.",
     *
     *     @OA\Parameter(
     *         name="type",
     *         in="path",
     *         required=true,
     *         description="Tên entity type",
     *         @OA\Schema(
     *             type="string",
     *             enum={"Alert", "Organization", "Person", "Category", "WeatherObserved"}
     *         ),
     *         example="Alert"
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Thành công - Schema của entity type",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="string", example="Alert"),
     *             @OA\Property(property="typeName", type="string", example="Alert"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(
     *                 property="attributeDetails",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="attributeName", type="string"),
     *                     @OA\Property(property="attributeType", type="string"),
     *                     @OA\Property(property="description", type="string")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Entity type không tồn tại",
     *         @OA\JsonContent(
     *             @OA\Property(property="type", type="string", example="https://uri.etsi.org/ngsi-ld/errors/ResourceNotFound"),
     *             @OA\Property(property="title", type="string", example="Entity type not found"),
     *             @OA\Property(property="detail", type="string")
     *         )
     *     )
     * )
     */
    public function getTypeSchema($type)
    {
        $schemas = [
            'Alert' => [
                'id' => 'Alert',
                'typeName' => 'Alert',
                'description' => 'Phản ánh sự cố từ người dân theo chuẩn FIWARE Alert Data Model',
                'attributeDetails' => [
                    ['attributeName' => 'category', 'attributeType' => 'Property', 'description' => 'Loại sự cố: traffic, environment, infrastructure, security, health'],
                    ['attributeName' => 'description', 'attributeType' => 'Property', 'description' => 'Mô tả chi tiết sự cố'],
                    ['attributeName' => 'severity', 'attributeType' => 'Property', 'description' => 'Mức độ nghiêm trọng: informational, low, medium, high, critical'],
                    ['attributeName' => 'location', 'attributeType' => 'GeoProperty', 'description' => 'Vị trí địa lý (GeoJSON Point)'],
                    ['attributeName' => 'dateObserved', 'attributeType' => 'Property', 'description' => 'Thời điểm phát hiện sự cố'],
                    ['attributeName' => 'validFrom', 'attributeType' => 'Property', 'description' => 'Thời gian bắt đầu hiệu lực'],
                    ['attributeName' => 'validTo', 'attributeType' => 'Property', 'description' => 'Thời gian kết thúc hiệu lực'],
                    ['attributeName' => 'alertSource', 'attributeType' => 'Relationship', 'description' => 'Nguồn phản ánh (Person)'],
                    ['attributeName' => 'data', 'attributeType' => 'Property', 'description' => 'Dữ liệu bổ sung (images, videos)']
                ]
            ],
            'Organization' => [
                'id' => 'Organization',
                'typeName' => 'Organization',
                'description' => 'Tổ chức/Cơ quan xử lý theo chuẩn Schema.org Organization',
                'attributeDetails' => [
                    ['attributeName' => 'name', 'attributeType' => 'Property', 'description' => 'Tên tổ chức'],
                    ['attributeName' => 'email', 'attributeType' => 'Property', 'description' => 'Email liên hệ'],
                    ['attributeName' => 'address', 'attributeType' => 'Property', 'description' => 'Địa chỉ tổ chức'],
                    ['attributeName' => 'level', 'attributeType' => 'Property', 'description' => 'Cấp tổ chức: 0=Thành phố, 1=Quận, 2=Phường'],
                    ['attributeName' => 'status', 'attributeType' => 'Property', 'description' => 'Trạng thái hoạt động']
                ]
            ],
            'Person' => [
                'id' => 'Person',
                'typeName' => 'Person',
                'description' => 'Người dùng hệ thống theo chuẩn Schema.org Person',
                'attributeDetails' => [
                    ['attributeName' => 'name', 'attributeType' => 'Property', 'description' => 'Họ tên'],
                    ['attributeName' => 'email', 'attributeType' => 'Property', 'description' => 'Email'],
                    ['attributeName' => 'address', 'attributeType' => 'Property', 'description' => 'Địa chỉ'],
                    ['attributeName' => 'telephone', 'attributeType' => 'Property', 'description' => 'Số điện thoại'],
                    ['attributeName' => 'reputation', 'attributeType' => 'Property', 'description' => 'Điểm uy tín'],
                    ['attributeName' => 'status', 'attributeType' => 'Property', 'description' => 'Trạng thái tài khoản']
                ]
            ],
            'Category' => [
                'id' => 'Category',
                'typeName' => 'Category',
                'description' => 'Danh mục phân loại sự cố',
                'attributeDetails' => [
                    ['attributeName' => 'name', 'attributeType' => 'Property', 'description' => 'Tên danh mục'],
                    ['attributeName' => 'description', 'attributeType' => 'Property', 'description' => 'Mô tả chi tiết'],
                    ['attributeName' => 'icon', 'attributeType' => 'Property', 'description' => 'Icon hiển thị'],
                    ['attributeName' => 'color', 'attributeType' => 'Property', 'description' => 'Màu sắc'],
                    ['attributeName' => 'status', 'attributeType' => 'Property', 'description' => 'Trạng thái sử dụng']
                ]
            ],
            'WeatherObserved' => [
                'id' => 'WeatherObserved',
                'typeName' => 'WeatherObserved',
                'description' => 'Quan trắc thời tiết theo chuẩn FIWARE WeatherObserved',
                'attributeDetails' => [
                    ['attributeName' => 'temperature', 'attributeType' => 'Property', 'description' => 'Nhiệt độ (°C)'],
                    ['attributeName' => 'relativeHumidity', 'attributeType' => 'Property', 'description' => 'Độ ẩm tương đối (%)'],
                    ['attributeName' => 'precipitation', 'attributeType' => 'Property', 'description' => 'Lượng mưa (mm)'],
                    ['attributeName' => 'windSpeed', 'attributeType' => 'Property', 'description' => 'Tốc độ gió (m/s)'],
                    ['attributeName' => 'atmosphericPressure', 'attributeType' => 'Property', 'description' => 'Áp suất khí quyển (hPa)'],
                    ['attributeName' => 'dateObserved', 'attributeType' => 'Property', 'description' => 'Thời điểm quan trắc'],
                    ['attributeName' => 'location', 'attributeType' => 'GeoProperty', 'description' => 'Vị trí trạm quan trắc']
                ]
            ]
        ];

        if (!isset($schemas[$type])) {
            return response()->json([
                'type' => 'https://uri.etsi.org/ngsi-ld/errors/ResourceNotFound',
                'title' => 'Entity type not found',
                'detail' => "The entity type '{$type}' does not exist. Available types: " . implode(', ', array_keys($schemas))
            ], 404);
        }

        $schema = $schemas[$type];
        $schema['@context'] = config('app.url') . '/@context.jsonld';

        return response()->json($schema, 200, [
            'Content-Type' => 'application/ld+json'
        ]);
    }

    /**
     * @OA\Get(
     *     path="/ngsi-ld/v1/attributes",
     *     operationId="getAttributes",
     *     tags={"NGSI-LD OpenData"},
     *     summary="Danh sách các attributes có thể query",
     *     description="Trả về danh sách tất cả các attributes có thể sử dụng trong query filter (tham số q). Giúp developer biết được các field nào có thể dùng để lọc dữ liệu.",
     *
     *     @OA\Response(
     *         response=200,
     *         description="Thành công - Danh sách queryable attributes",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="attributes",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="attributeName", type="string", example="category"),
     *                     @OA\Property(property="attributeType", type="string", example="Property"),
     *                     @OA\Property(property="entityTypes", type="array", @OA\Items(type="string")),
     *                     @OA\Property(property="description", type="string"),
     *                     @OA\Property(property="queryExample", type="string", example="category==traffic")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function getAttributes()
    {
        $attributes = [
            [
                'attributeName' => 'category',
                'attributeType' => 'Property',
                'entityTypes' => ['Alert'],
                'description' => 'Loại sự cố: traffic, environment, infrastructure, security, health',
                'queryExample' => 'category==traffic'
            ],
            [
                'attributeName' => 'severity',
                'attributeType' => 'Property',
                'entityTypes' => ['Alert'],
                'description' => 'Mức độ nghiêm trọng: informational, low, medium, high, critical',
                'queryExample' => 'severity==high'
            ],
            [
                'attributeName' => 'status',
                'attributeType' => 'Property',
                'entityTypes' => ['Alert', 'Organization', 'Person', 'Category'],
                'description' => 'Trạng thái entity',
                'queryExample' => 'status==pending'
            ],
            [
                'attributeName' => 'level',
                'attributeType' => 'Property',
                'entityTypes' => ['Organization'],
                'description' => 'Cấp tổ chức: 0=Thành phố, 1=Quận, 2=Phường',
                'queryExample' => 'level==0'
            ],
            [
                'attributeName' => 'name',
                'attributeType' => 'Property',
                'entityTypes' => ['Organization', 'Person', 'Category'],
                'description' => 'Tên entity',
                'queryExample' => 'name~="Quận 1"'
            ],
            [
                'attributeName' => 'temperature',
                'attributeType' => 'Property',
                'entityTypes' => ['WeatherObserved'],
                'description' => 'Nhiệt độ (°C)',
                'queryExample' => 'temperature>30'
            ],
            [
                'attributeName' => 'location',
                'attributeType' => 'GeoProperty',
                'entityTypes' => ['Alert', 'WeatherObserved'],
                'description' => 'Vị trí địa lý (sử dụng georel, geometry, coordinates để query)',
                'queryExample' => 'georel=near;maxDistance==5000&geometry=Point&coordinates=[106.7,10.8]'
            ]
        ];

        return response()->json([
            'attributes' => $attributes,
            'totalCount' => count($attributes),
            '@context' => config('app.url') . '/@context.jsonld'
        ], 200, [
            'Content-Type' => 'application/ld+json'
        ]);
    }
}
