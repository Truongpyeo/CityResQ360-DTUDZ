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
                'direct' => 'https://media.cityresq360.io.vn',
                'viaCoreAPI' => 'https://api.cityresq360.io.vn/api/v1/media',
            ],

            'integrationMethods' => [
                [
                    'id' => 'option-a',
                    'name' => 'Phương Án A: Qua CoreAPI',
                    'recommended' => true,
                    'description' => 'Truy cập qua CoreAPI - Khuyên dùng cho các ứng dụng CityResQ360',
                    'benefits' => [
                        'Tích hợp business logic và validation',
                        'Tự động xử lý fallback',
                        'Quản lý quyền truy cập thống nhất',
                    ],
                    'url' => 'https://api.cityresq360.io.vn/api/v1/media',
                ],
                [
                    'id' => 'option-b',
                    'name' => 'Phương Án B: Truy Cập Trực Tiếp',
                    'recommended' => false,
                    'description' => 'Truy cập trực tiếp MediaService - Dành cho dự án bên ngoài',
                    'benefits' => [
                        'Microservice độc lập',
                        'Performance tốt hơn (bỏ qua proxy)',
                        'Linh hoạt cho external integration',
                    ],
                    'url' => 'https://media.cityresq360.io.vn',
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
                    'upload' => "use Illuminate\\Support\\Facades\\Http;\n\n\$response = Http::attach(\n    'file', file_get_contents(\$filePath), 'photo.jpg'\n)->post('https://media.cityresq360.io.vn/api/v1/media/upload', [\n    'type' => 'image'\n]);\n\n\$data = \$response->json();",
                ],
                'python' => [
                    'upload' => "import requests\n\nfiles = {'file': open('photo.jpg', 'rb')}\ndata = {'type': 'image'}\n\nresponse = requests.post(\n    'https://media.cityresq360.io.vn/api/v1/media/upload',\n    files=files,\n    data=data\n)\n\nresult = response.json()",
                ],
                'javascript' => [
                    'upload' => "const formData = new FormData();\nformData.append('file', fileInput.files[0]);\nformData.append('type', 'image');\n\nconst response = await fetch(\n    'https://media.cityresq360.io.vn/api/v1/media/upload',\n    {\n        method: 'POST',\n        body: formData\n    }\n);\n\nconst data = await response.json();",
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
                'direct' => 'https://incident.cityresq360.io.vn',
                'viaCoreAPI' => 'https://api.cityresq360.io.vn/api/v1/incidents',
            ],

            'integrationMethods' => [
                [
                    'id' => 'option-b',
                    'name' => 'Tích Hợp JWT Trực Tiếp (Recommended)',
                    'recommended' => true,
                    'description' => 'Tạo incident trực tiếp qua JWT - Dành cho external systems (IoT sensors, third-party apps)',
                    'benefits' => [
                        'Không cần report_id từ CoreAPI',
                        'Không cần admin verification',
                        'Auto-dispatch tự động',
                        'Microservice hoàn toàn độc lập',
                        'Phù hợp cho IoT, emergency systems',
                    ],
                    'url' => 'https://incident.cityresq360.io.vn',
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
                    'generate_jwt' => "const jwt = require('jsonwebtoken');\n\nconst token = jwt.sign(\n  {\n    user_id: 999,\n    email: 'external_system@example.com',\n    role: 'EXTERNAL_SERVICE',\n    service: 'iot_sensor'\n  },\n  process.env.INCIDENT_JWT_SECRET,\n  { \n    algorithm: 'HS256',\n    expiresIn: '1h'\n  }\n);\n\nconsole.log(token);",
                    'create_incident' => "const axios = require('axios');\nconst jwt = require('jsonwebtoken');\n\n// Generate JWT token\nconst token = jwt.sign(\n  { user_id: 999, role: 'EXTERNAL_SERVICE' },\n  process.env.INCIDENT_JWT_SECRET,\n  { expiresIn: '1h' }\n);\n\n// Create incident\nconst response = await axios.post(\n  'https://incident.cityresq360.io.vn/api/v1/incidents',\n  {\n    title: 'Emergency: Flooding Detected',\n    description: 'Water level 45cm, traffic blocked',\n    priority: 'HIGH',\n    location_latitude: 10.7758,\n    location_longitude: 106.7019,\n    address: 'Le Loi Street, District 1',\n    category: 'Traffic - Flooding',\n    external_id: 'IOT-SENSOR-001',\n    external_system: 'iot_water_sensor'\n  },\n  {\n    headers: { 'Authorization': `Bearer \${token}` }\n  }\n);\n\nconsole.log(response.data);",
                ],
                'python' => [
                    'generate_jwt' => "import jwt\nfrom datetime import datetime, timedelta\nimport os\n\npayload = {\n    'user_id': 999,\n    'email': 'external_system@example.com',\n    'role': 'EXTERNAL_SERVICE',\n    'service': 'iot_sensor',\n    'exp': datetime.utcnow() + timedelta(hours=1)\n}\n\ntoken = jwt.encode(\n    payload,\n    os.getenv('INCIDENT_JWT_SECRET'),\n    algorithm='HS256'\n)\n\nprint(token)",
                    'create_incident' => "import requests\nimport jwt\nfrom datetime import datetime, timedelta\nimport os\n\n# Generate JWT token\npayload = {\n    'user_id': 999,\n    'role': 'EXTERNAL_SERVICE',\n    'exp': datetime.utcnow() + timedelta(hours=1)\n}\n\ntoken = jwt.encode(\n    payload,\n    os.getenv('INCIDENT_JWT_SECRET'),\n    algorithm='HS256'\n)\n\n# Create incident\nresponse = requests.post(\n    'https://incident.cityresq360.io.vn/api/v1/incidents',\n    json={\n        'title': 'Emergency: Flooding Detected',\n        'description': 'Water level 45cm, traffic blocked',\n        'priority': 'HIGH',\n        'location_latitude': 10.7758,\n        'location_longitude': 106.7019,\n        'address': 'Le Loi Street, District 1',\n        'category': 'Traffic - Flooding',\n        'external_id': 'IOT-SENSOR-001',\n        'external_system': 'iot_water_sensor'\n    },\n    headers={'Authorization': f'Bearer {token}'}\n)\n\nprint(response.json())",
                ],
                'curl' => [
                    'create_incident' => "# Generate JWT token first (using Node.js or Python)\n# Then use it in the request:\n\ncurl -X POST https://incident.cityresq360.io.vn/api/v1/incidents \\\n  -H \"Authorization: Bearer YOUR_JWT_TOKEN\" \\\n  -H \"Content-Type: application/json\" \\\n  -d '{\n    \"title\": \"Emergency: Flooding Detected\",\n    \"description\": \"Water level 45cm, traffic blocked\",\n    \"priority\": \"HIGH\",\n    \"location_latitude\": 10.7758,\n    \"location_longitude\": 106.7019,\n    \"address\": \"Le Loi Street, District 1\",\n    \"category\": \"Traffic - Flooding\",\n    \"external_id\": \"IOT-SENSOR-001\",\n    \"external_system\": \"iot_water_sensor\"\n  }'",
                ],
            ],

            'authentication' => [
                'type' => 'JWT',
                'description' => 'IncidentService sử dụng JWT authentication. Bạn cần đăng ký để nhận JWT_SECRET.',
                'steps' => [
                    'Đăng nhập vào trang Admin Panel',
                    'Vào menu "Tích Hợp" → "API Keys"',
                    'Chọn module "IncidentService"',
                    'Nhấn "Đăng Ký Mới" và điền thông tin',
                    'Sau khi được approve, bạn sẽ nhận được JWT_SECRET qua email',
                    'Sử dụng JWT_SECRET để generate JWT token trong code của bạn',
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
}

