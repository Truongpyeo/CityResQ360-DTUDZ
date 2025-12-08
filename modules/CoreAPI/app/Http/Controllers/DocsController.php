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

use Inertia\Inertia;
use Inertia\Response;

class DocsController extends Controller
{
    /**
     * Hiển thị trang chủ tài liệu API
     */
    public function index(): Response
    {
        return Inertia::render('docs/Index', [
            'services' => $this->getServices(),
        ]);
    }

    /**
     * Hiển thị tài liệu chi tiết của một service
     */
    public function show(string $service): Response
    {
        $serviceData = $this->getServiceData($service);

        if (!$serviceData) {
            abort(404, 'Service not found');
        }

        return Inertia::render('docs/Service', [
            'service' => $serviceData,
            'services' => $this->getServices(),
        ]);
    }

    /**
     * Lấy danh sách tất cả services
     */
    private function getServices(): array
    {
        return [
            [
                'id' => 'media-service',
                'name' => 'MediaService',
                'description' => 'Dịch vụ upload và lưu trữ file (ảnh, video)',
                'icon' => 'Image',
                'status' => 'stable',
                'version' => 'v1.0',
            ],
            [
                'id' => 'wallet-service',
                'name' => 'WalletService',
                'description' => 'Dịch vụ quản lý ví điện tử và giao dịch',
                'icon' => 'Wallet',
                'status' => 'coming-soon',
                'version' => 'v1.0',
            ],
            [
                'id' => 'iot-service',
                'name' => 'IoTService',
                'description' => 'Dịch vụ quản lý thiết bị IoT và telemetry',
                'icon' => 'Cpu',
                'status' => 'coming-soon',
                'version' => 'v1.0',
            ],
            [
                'id' => 'incident-service',
                'name' => 'IncidentService',
                'description' => 'Dịch vụ quản lý sự cố và phân công xử lý',
                'icon' => 'AlertTriangle',
                'status' => 'stable',
                'version' => 'v1.0',
            ],
            [
                'id' => 'analytics-service',
                'name' => 'AnalyticsService',
                'description' => 'Dịch vụ phân tích dữ liệu và báo cáo',
                'icon' => 'BarChart',
                'status' => 'coming-soon',
                'version' => 'v1.0',
            ],
            [
                'id' => 'aiml-service',
                'name' => 'AIMLService',
                'description' => 'Dịch vụ AI/ML phân tích ảnh và phát hiện sự cố tự động',
                'icon' => 'Brain',
                'status' => 'stable',
                'version' => 'v1.0',
            ],
            [
                'id' => 'search-service',
                'name' => 'SearchService',
                'description' => 'Dịch vụ tìm kiếm toàn văn (OpenSearch)',
                'icon' => 'Search',
                'status' => 'coming-soon',
                'version' => 'v1.0',
            ],
        ];
    }

    /**
     * Lấy dữ liệu chi tiết của một service
     */
    private function getServiceData(string $serviceId): ?array
    {
        $services = [
            'media-service' => $this->getMediaServiceData(),
            'incident-service' => $this->getIncidentServiceData(),
            'aiml-service' => $this->getAIMLServiceData(),
            // Thêm các services khác sau
        ];

        return $services[$serviceId] ?? null;
    }

    /**
     * Dữ liệu tài liệu MediaService
     */
    private function getMediaServiceData(): array
    {
        return [
            'id' => 'media-service',
            'name' => 'MediaService',
            'description' => 'Dịch vụ upload, lưu trữ và quản lý file media (ảnh, video) sử dụng MinIO (S3-compatible storage).',
            'icon' => 'Image',
            'status' => 'stable',
            'version' => 'v1.0',

            'baseUrls' => [
                'production' => 'https://api.cityresq360.io.vn/api/v1/media',
                'local' => 'http://localhost:8000/api/v1/media',
            ],

            'integrationMethods' => [
                [
                    'id' => 'recommended',
                    'name' => 'Qua CoreAPI (Only Method)',
                    'recommended' => true,
                    'description' => 'Tất cả requests đều phải qua CoreAPI - Không có direct access',
                    'benefits' => [
                        'Tích hợp business logic và validation',
                        'Authentication và authorization thống nhất',
                        'User tracking và audit log đầy đủ',
                        'Rate limiting và security centralized',
                    ],
                    'url' => 'https://api.cityresq360.io.vn/api/v1/media',
                ],
            ],

            'endpoints' => [
                [
                    'method' => 'POST',
                    'path' => '/api/v1/media/upload',
                    'description' => 'Upload một file (ảnh hoặc video)',
                    'auth' => 'Bearer Token (JWT)',
                    'requestBody' => [
                        [
                            'name' => 'file',
                            'type' => 'File',
                            'required' => true,
                            'description' => 'File cần upload',
                        ],
                        [
                            'name' => 'type',
                            'type' => 'string',
                            'required' => true,
                            'description' => 'Loại file: "image" hoặc "video"',
                        ],
                    ],
                    'response' => [
                        'id' => 'abc123def456',
                        'url' => 'https://minio.cityresq360.io.vn/cityresq-media/2025/11/30/abc123.jpg',
                        'size' => 2048576,
                        'type' => 'image/jpeg',
                        'created_at' => '2025-11-30T15:30:00Z',
                    ],
                ],
                [
                    'method' => 'GET',
                    'path' => '/api/v1/media/:id',
                    'description' => 'Lấy thông tin metadata của một file',
                    'auth' => 'Bearer Token (JWT)',
                    'pathParams' => [
                        [
                            'name' => 'id',
                            'type' => 'string',
                            'required' => true,
                            'description' => 'ID của file',
                        ],
                    ],
                    'response' => [
                        'id' => 'abc123def456',
                        'url' => 'https://minio.cityresq360.io.vn/cityresq-media/2025/11/30/abc123.jpg',
                        'size' => 2048576,
                        'type' => 'image/jpeg',
                        'created_at' => '2025-11-30T15:30:00Z',
                    ],
                ],
                [
                    'method' => 'DELETE',
                    'path' => '/api/v1/media/:id',
                    'description' => 'Xóa một file',
                    'auth' => 'Bearer Token (JWT)',
                    'pathParams' => [
                        [
                            'name' => 'id',
                            'type' => 'string',
                            'required' => true,
                            'description' => 'ID của file',
                        ],
                    ],
                    'response' => [
                        'message' => 'File deleted successfully',
                    ],
                ],
            ],

            'codeExamples' => [
                'laravel' => [
                    'upload' => "use Illuminate\\Support\\Facades\\Http;\n\n\$response = Http::withToken(\$sanctumToken)\n    ->attach(\n        'file', file_get_contents(\$filePath), 'photo.jpg'\n    )->post('https://api.cityresq360.io.vn/api/v1/media/upload', [\n        'type' => 'image'\n    ]);\n\n\$data = \$response->json();",
                ],
                'python' => [
                    'upload' => "import requests\n\nheaders = {\n    'Authorization': 'Bearer YOUR_SANCTUM_TOKEN'\n}\nfiles = {'file': open('photo.jpg', 'rb')}\ndata = {'type': 'image'}\n\nresponse = requests.post(\n    'https://api.cityresq360.io.vn/api/v1/media/upload',\n    headers=headers,\n    files=files,\n    data=data\n)\n\nresult = response.json()",
                ],
                'javascript' => [
                    'upload' => "const formData = new FormData();\nformData.append('file', fileInput.files[0]);\nformData.append('type', 'image');\n\nconst response = await fetch(\n    'https://api.cityresq360.io.vn/api/v1/media/upload',\n    {\n        method: 'POST',\n        headers: {\n            'Authorization': `Bearer \${sanctumToken}`\n        },\n        body: formData\n    }\n);\n\nconst data = await response.json();",
                ],
            ],
        ];
    }

    /**
     * Dữ liệu tài liệu IncidentService
     */
    private function getIncidentServiceData(): array
    {
        return [
            'id' => 'incident-service',
            'name' => 'IncidentService',
            'description' => 'Dịch vụ quản lý sự cố, auto-dispatch, SLA monitoring và phân công xử lý cho agencies. Hỗ trợ tạo incident trực tiếp qua JWT authentication.',
            'icon' => 'AlertTriangle',
            'status' => 'stable',
            'version' => 'v1.0',

            'baseUrls' => [
                'production' => 'https://api.cityresq360.io.vn/api/v1/incidents',
                'local' => 'http://localhost:8000/api/v1/incidents',
            ],

            'integrationMethods' => [
                [
                    'id' => 'recommended',
                    'name' => 'Qua CoreAPI (Only Method)',
                    'recommended' => true,
                    'description' => 'Tất cả requests đều phải qua CoreAPI - Không có direct access',
                    'benefits' => [
                        'Tích hợp business logic và validation',
                        'Authentication và authorization thống nhất',
                        'Auto-dispatch tự động',
                        'User tracking và audit log đầy đủ',
                        'Rate limiting và security centralized',
                    ],
                    'url' => 'https://api.cityresq360.io.vn/api/v1/incidents',
                ],
            ],

            'endpoints' => [
                [
                    'method' => 'POST',
                    'path' => '/api/v1/incidents',
                    'description' => 'Tạo incident mới với đầy đủ thông tin (Direct Creation Flow)',
                    'auth' => 'Bearer Token (JWT)',
                    'requestBody' => [
                        [
                            'name' => 'title',
                            'type' => 'string',
                            'required' => true,
                            'description' => 'Tiêu đề incident (5-255 ký tự)',
                        ],
                        [
                            'name' => 'description',
                            'type' => 'string',
                            'required' => true,
                            'description' => 'Mô tả chi tiết (10-2000 ký tự)',
                        ],
                        [
                            'name' => 'priority',
                            'type' => 'string',
                            'required' => false,
                            'description' => 'Mức độ ưu tiên: LOW, MEDIUM, HIGH, CRITICAL (mặc định: MEDIUM)',
                        ],
                        [
                            'name' => 'location_latitude',
                            'type' => 'number',
                            'required' => false,
                            'description' => 'Vĩ độ (-90 đến 90)',
                        ],
                        [
                            'name' => 'location_longitude',
                            'type' => 'number',
                            'required' => false,
                            'description' => 'Kinh độ (-180 đến 180)',
                        ],
                        [
                            'name' => 'address',
                            'type' => 'string',
                            'required' => false,
                            'description' => 'Địa chỉ đầy đủ',
                        ],
                        [
                            'name' => 'category',
                            'type' => 'string',
                            'required' => false,
                            'description' => 'Loại incident',
                        ],
                        [
                            'name' => 'external_id',
                            'type' => 'string',
                            'required' => false,
                            'description' => 'ID tracking từ hệ thống bên ngoài',
                        ],
                        [
                            'name' => 'external_system',
                            'type' => 'string',
                            'required' => false,
                            'description' => 'Tên hệ thống bên ngoài (ví dụ: "iot_sensor", "mobile_v2")',
                        ],
                    ],
                    'response' => [
                        'success' => true,
                        'message' => 'Incident created successfully',
                        'data' => [
                            'id' => 11,
                            'report_id' => null,
                            'title' => 'Emergency: Road Flooding Detected',
                            'description' => 'IoT sensor detected flooding...',
                            'status' => 'PENDING',
                            'priority' => 'HIGH',
                            'assigned_agency_id' => 18,
                            'location_latitude' => '10.77580000',
                            'location_longitude' => '106.70190000',
                            'address' => 'Le Loi Street, District 1',
                            'category' => 'Traffic - Flooding',
                            'external_id' => 'IOT-SENSOR-001',
                            'external_system' => 'iot_water_sensor',
                            'due_date' => '2025-12-07T15:34:29.732Z',
                            'assigned_at' => '2025-12-07T03:34:29.815Z',
                            'createdAt' => '2025-12-07T03:34:29.733Z',
                        ],
                    ],
                ],
                [
                    'method' => 'GET',
                    'path' => '/api/v1/incidents/:id',
                    'description' => 'Lấy thông tin chi tiết của một incident',
                    'auth' => 'Bearer Token (JWT)',
                    'pathParams' => [
                        [
                            'name' => 'id',
                            'type' => 'integer',
                            'required' => true,
                            'description' => 'ID của incident',
                        ],
                    ],
                    'response' => [
                        'success' => true,
                        'data' => [
                            'id' => 11,
                            'title' => 'Emergency: Road Flooding Detected',
                            'status' => 'PENDING',
                            'priority' => 'HIGH',
                            'assigned_agency_id' => 18,
                            'due_date' => '2025-12-07T15:34:29.732Z',
                        ],
                    ],
                ],
                [
                    'method' => 'GET',
                    'path' => '/api/v1/incidents',
                    'description' => 'Lấy danh sách incidents với filter',
                    'auth' => 'Bearer Token (JWT)',
                    'queryParams' => [
                        [
                            'name' => 'status',
                            'type' => 'string',
                            'required' => false,
                            'description' => 'Filter theo status: PENDING, IN_PROGRESS, RESOLVED, CLOSED',
                        ],
                        [
                            'name' => 'priority',
                            'type' => 'string',
                            'required' => false,
                            'description' => 'Filter theo priority: LOW, MEDIUM, HIGH, CRITICAL',
                        ],
                        [
                            'name' => 'page',
                            'type' => 'integer',
                            'required' => false,
                            'description' => 'Số trang (mặc định: 1)',
                        ],
                        [
                            'name' => 'limit',
                            'type' => 'integer',
                            'required' => false,
                            'description' => 'Số lượng mỗi trang (mặc định: 20)',
                        ],
                    ],
                    'response' => [
                        'success' => true,
                        'data' => [
                            'total' => 50,
                            'page' => 1,
                            'limit' => 20,
                            'incidents' => [
                                ['id' => 11, 'title' => '...', 'status' => 'PENDING'],
                                ['id' => 10, 'title' => '...', 'status' => 'IN_PROGRESS'],
                            ],
                        ],
                    ],
                ],
            ],

            'codeExamples' => [
                'nodejs' => [
                    'create_incident' => "const axios = require('axios');\n\n// Sử dụng Sanctum token từ CoreAPI login\nconst sanctumToken = 'YOUR_SANCTUM_TOKEN';\n\n// Create incident qua CoreAPI\nconst response = await axios.post(\n  'https://api.cityresq360.io.vn/api/v1/incidents',\n  {\n    title: 'Emergency: Flooding Detected',\n    description: 'Water level 45cm, traffic blocked',\n    priority: 'HIGH',\n    location_latitude: 10.7758,\n    location_longitude: 106.7019,\n    address: 'Le Loi Street, District 1',\n    category: 'Traffic - Flooding'\n  },\n  {\n    headers: { 'Authorization': `Bearer \${sanctumToken}` }\n  }\n);\n\nconsole.log(response.data);",
                ],
                'python' => [
                    'create_incident' => "import requests\n\n# Sử dụng Sanctum token từ CoreAPI login\nsanctum_token = 'YOUR_SANCTUM_TOKEN'\n\n# Create incident qua CoreAPI\nresponse = requests.post(\n    'https://api.cityresq360.io.vn/api/v1/incidents',\n    json={\n        'title': 'Emergency: Flooding Detected',\n        'description': 'Water level 45cm, traffic blocked',\n        'priority': 'HIGH',\n        'location_latitude': 10.7758,\n        'location_longitude': 106.7019,\n        'address': 'Le Loi Street, District 1',\n        'category': 'Traffic - Flooding'\n    },\n    headers={'Authorization': f'Bearer {sanctum_token}'}\n)\n\nprint(response.json())",
                ],
                'curl' => [
                    'create_incident' => "curl -X POST https://api.cityresq360.io.vn/api/v1/incidents \\\n  -H \"Authorization: Bearer YOUR_SANCTUM_TOKEN\" \\\n  -H \"Content-Type: application/json\" \\\n  -d '{\n    \"title\": \"Emergency: Flooding Detected\",\n    \"description\": \"Water level 45cm, traffic blocked\",\n    \"priority\": \"HIGH\",\n    \"location_latitude\": 10.7758,\n    \"location_longitude\": 106.7019,\n    \"address\": \"Le Loi Street, District 1\",\n    \"category\": \"Traffic - Flooding\"\n  }'",
                ],
            ],

            'authentication' => [
                'type' => 'Sanctum',
                'description' => 'Tất cả requests phải có Laravel Sanctum token. Token được lấy sau khi login thành công vào CoreAPI.',
                'steps' => [
                    'Đăng nhập vào CoreAPI (POST /api/v1/auth/login)',
                    'Lấy Sanctum token từ response',
                    'Thêm token vào header: Authorization: Bearer {token}',
                    'Gọi Incident endpoints với token này',
                ],
            ],

            'features' => [
                [
                    'name' => 'Direct Incident Creation',
                    'description' => 'Tạo incident trực tiếp không cần report_id từ CoreAPI',
                ],
                [
                    'name' => 'Auto-Dispatch Algorithm',
                    'description' => 'Tự động phân công cho agency gần nhất trong bán kính 10km',
                ],
                [
                    'name' => 'SLA Monitoring',
                    'description' => 'Theo dõi SLA và gửi cảnh báo khi sắp quá hạn',
                ],
                [
                    'name' => 'RabbitMQ Notifications',
                    'description' => 'Gửi thông báo realtime cho agencies qua message queue',
                ],
                [
                    'name' => 'External System Tracking',
                    'description' => 'Hỗ trợ external_id và external_system để tracking từ hệ thống bên ngoài',
                ],
            ],

            'useCases' => [
                [
                    'title' => 'IoT Sensors',
                    'description' => 'Cảm biến IoT phát hiện sự cố (flooding, fire, air quality) và tự động tạo incident',
                    'icon' => 'Wifi',
                ],
                [
                    'title' => 'Third-Party Apps',
                    'description' => 'Ứng dụng mobile/web của bên thứ 3 tích hợp để báo cáo sự cố',
                    'icon' => 'Smartphone',
                ],
                [
                    'title' => 'Emergency Services',
                    'description' => 'Hệ thống cứu hỏa, cứu thương tạo incident trực tiếp',
                    'icon' => 'Siren',
                ],
                [
                    'title' => 'Smart City Integration',
                    'description' => 'Tích hợp với các hệ thống smart city khác (traffic, environment monitoring)',
                    'icon' => 'Building',
                ],
            ],
        ];
    }

    /**
     * Dữ liệu tài liệu AIMLService
     */
    private function getAIMLServiceData(): array
    {
        return [
            'id' => 'aiml-service',
            'name' => 'AIMLService',
            'description' => 'Dịch vụ AI/ML phân tích ảnh để phát hiện tự động các loại sự cố: ổ gà, ngập lụt, rác thải, kẹt xe... Sử dụng Google ViT và Facebook DETR models.',
            'icon' => 'Brain',
            'status' => 'stable',
            'version' => 'v1.0',

            'baseUrls' => [
                'production' => 'https://api.cityresq360.io.vn/api/v1/ai',
                'local' => 'http://localhost:8000/api/v1/ai',
            ],

            'integrationMethods' => [
                [
                    'id' => 'recommended',
                    'name' => 'Qua CoreAPI (Only Method)',
                    'recommended' => true,
                    'description' => 'Tất cả requests đều phải qua CoreAPI - Không có direct access',
                    'benefits' => [
                        'Tích hợp business logic và validation',
                        'Authentication và authorization thống nhất',
                        'User tracking và audit log đầy đủ',
                        'Rate limiting và security centralized',
                    ],
                    'url' => 'https://api.cityresq360.io.vn/api/v1/ai',
                ],
            ],

            'endpoints' => [
                [
                    'method' => 'POST',
                    'path' => '/api/v1/ai/analyze',
                    'description' => 'Phân tích ảnh để phát hiện loại sự cố',
                    'auth' => 'Bearer Token (Sanctum)',
                    'requestBody' => [
                        [
                            'name' => 'file',
                            'type' => 'File',
                            'required' => true,
                            'description' => 'File ảnh cần phân tích (JPEG, PNG, WebP)',
                        ],
                    ],
                    'response' => [
                        'success' => true,
                        'analysis' => [
                            'label' => 'pothole',
                            'label_vi' => 'Ổ gà',
                            'label_en' => 'Pothole',
                            'confidence' => 0.87,
                            'severity' => 'medium',
                            'priority' => 'high',
                            'category_id' => 1,
                            'description' => 'Ổ gà được phát hiện với độ tin cậy 87%',
                            'detected_objects' => ['road', 'hole', 'asphalt', 'damage'],
                            'timestamp' => '2025-12-08T02:30:00Z',
                        ],
                    ],
                ],
                [
                    'method' => 'POST',
                    'path' => '/api/v1/ai/analyze-base64',
                    'description' => 'Phân tích ảnh từ base64 string (cho mobile apps)',
                    'auth' => 'Bearer Token (Sanctum)',
                    'requestBody' => [
                        [
                            'name' => 'image_base64',
                            'type' => 'string',
                            'required' => true,
                            'description' => 'Base64 encoded image data',
                        ],
                    ],
                    'response' => [
                        'success' => true,
                        'analysis' => [
                            'label' => 'flooding',
                            'label_vi' => 'Ngập lụt',
                            'confidence' => 0.92,
                            'category_id' => 2,
                        ],
                    ],
                ],
                [
                    'method' => 'POST',
                    'path' => '/api/v1/ai/analyze-for-report',
                    'description' => 'Phân tích và trả về format cho CoreAPI Report',
                    'auth' => 'Bearer Token (Sanctum)',
                    'requestBody' => [
                        [
                            'name' => 'file',
                            'type' => 'File',
                            'required' => true,
                            'description' => 'File ảnh cần phân tích',
                        ],
                    ],
                    'response' => [
                        'success' => true,
                        'data' => [
                            'danh_muc_id' => 1,
                            'tieu_de' => 'Phát hiện Ổ gà',
                            'mo_ta' => 'Ổ gà được phát hiện với độ tin cậy 87%',
                            'muc_do_uu_tien' => 'high',
                            'muc_do_nghiem_trong' => 'medium',
                            'ai_analysis' => [
                                'label' => 'pothole',
                                'confidence' => 0.87,
                            ],
                        ],
                    ],
                ],
                [
                    'method' => 'GET',
                    'path' => '/api/v1/ai/health',
                    'description' => 'Kiểm tra trạng thái AIMLService',
                    'auth' => 'None',
                    'response' => [
                        'service' => 'AIMLService',
                        'status' => 'healthy',
                        'models_loaded' => true,
                        'device' => 'cpu',
                    ],
                ],
            ],

            'codeExamples' => [
                'python' => [
                    'analyze' => "import requests\n\n# Qua CoreAPI - cần authentication\nheaders = {\n    'Authorization': 'Bearer YOUR_SANCTUM_TOKEN'\n}\nfiles = {'file': open('incident_photo.jpg', 'rb')}\n\nresponse = requests.post(\n    'https://api.cityresq360.io.vn/api/v1/ai/analyze',\n    headers=headers,\n    files=files\n)\n\nresult = response.json()\nprint(f\"Detected: {result['analysis']['label_vi']}\")\nprint(f\"Confidence: {result['analysis']['confidence']:.0%}\")",
                    'analyze_base64' => "import requests\nimport base64\n\n# Read and encode image\nheaders = {\n    'Authorization': 'Bearer YOUR_SANCTUM_TOKEN'\n}\nwith open('photo.jpg', 'rb') as f:\n    image_data = base64.b64encode(f.read()).decode()\n\nresponse = requests.post(\n    'https://api.cityresq360.io.vn/api/v1/ai/analyze-base64',\n    headers=headers,\n    json={'image_base64': image_data}\n)\n\nresult = response.json()",
                ],
                'javascript' => [
                    'analyze' => "// Upload và analyze ảnh qua CoreAPI\nconst formData = new FormData();\nformData.append('file', fileInput.files[0]);\n\nconst response = await fetch(\n    'https://api.cityresq360.io.vn/api/v1/ai/analyze',\n    {\n        method: 'POST',\n        headers: {\n            'Authorization': `Bearer \${sanctumToken}`\n        },\n        body: formData\n    }\n);\n\nconst data = await response.json();\nconsole.log(`Detected: \${data.analysis.label_vi}`);\nconsole.log(`Confidence: \${data.analysis.confidence}`);\n",
                ],
                'curl' => [
                    'analyze' => "curl -X POST https://api.cityresq360.io.vn/api/v1/ai/analyze \\\\\n  -H \"Authorization: Bearer YOUR_SANCTUM_TOKEN\" \\\\\n  -F \"file=@photo.jpg\"\n\n# Response:\n{\n  \"success\": true,\n  \"analysis\": {\n    \"label\": \"pothole\",\n    \"label_vi\": \"Ổ gà\",\n    \"confidence\": 0.87,\n    \"category_id\": 1\n  }\n}",
                ],
            ],

            'authentication' => [
                'type' => 'Sanctum',
                'description' => 'Tất cả requests phải có Laravel Sanctum token. Token được lấy sau khi login thành công vào CoreAPI.',
                'steps' => [
                    'Đăng nhập vào CoreAPI (POST /api/v1/auth/login)',
                    'Lấy Sanctum token từ response',
                    'Thêm token vào header: Authorization: Bearer {token}',
                    'Gọi AI endpoints với token này',
                ],
            ],

            'features' => [
                [
                    'name' => '6 Loại Sự Cố Detection',
                    'description' => 'Phát hiện: Ổ gà, Ngập lụt, Đèn giao thông, Rác thải, Kẹt xe, Khác',
                ],
                [
                    'name' => '2 AI Models',
                    'description' => 'Google ViT (image classification) + Facebook DETR (object detection)',
                ],

                [
                    'name' => 'Dual Authentication',
                    'description' => 'Hỗ trợ cả Sanctum và JWT authentication',
                ],
                [
                    'name' => 'CoreAPI Integration',
                    'description' => 'Tích hợp sẵn trong MediaController upload flow',
                ],
            ],

            'useCases' => [
                [
                    'title' => 'Auto Report Category',
                    'description' => 'Người dùng upload ảnh, AI tự động suggest category cho report',
                    'icon' => 'Image',
                ],
                [
                    'title' => 'Mobile App Integration',
                    'description' => 'App mobile analyze ảnh ngay trên thiết bị trước khi upload',
                    'icon' => 'Smartphone',
                ],
                [
                    'title' => 'Quality Control',
                    'description' => 'Kiểm tra ảnh có đúng loại sự cố người dùng report không',
                    'icon' => 'CheckCircle',
                ],
                [
                    'title' => 'Data Analytics',
                    'description' => 'Phân tích hình ảnh để tạo báo cáo thống kê tự động',
                    'icon' => 'BarChart',
                ],
            ],

            'aiModels' => [
                'classification' => [
                    'name' => 'Google ViT',
                    'description' => 'Vision Transformer - image classification',
                    'accuracy' => '55-95% confidence',
                ],
                'detection' => [
                    'name' => 'Facebook DETR',
                    'description' => 'Detection Transformer - object detection',
                    'features' => 'Phát hiện xe, người, đường, nước, rác...',
                ],
            ],
        ];
    }
}
