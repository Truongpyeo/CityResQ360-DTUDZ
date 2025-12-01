<?php

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
                'id' => 'notification-service',
                'name' => 'NotificationService',
                'description' => 'Dịch vụ gửi thông báo (Push, Email, SMS)',
                'icon' => 'Bell',
                'status' => 'coming-soon',
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
                'status' => 'coming-soon',
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
}
