import React from 'react';
import { Head, Link } from '@inertiajs/react';
import {
    ArrowRight,
    CheckCircle,
    Image,
    Bell,
    Wallet,
    Cpu,
    AlertTriangle,
    BarChart,
    Search
} from 'lucide-react';
import DocsLayout from './layouts/DocsLayout';

interface Service {
    id: string;
    name: string;
    description: string;
    icon: string;
    status: string;
    version: string;
}

interface IndexProps {
    services: Service[];
}

const iconMap: Record<string, React.ComponentType<any>> = {
    Image,
    Bell,
    Wallet,
    Cpu,
    AlertTriangle,
    BarChart,
    Search,
};

export default function Index({ services }: IndexProps) {
    return (
        <DocsLayout services={services}>
            <Head title="API Documentation - CityResQ360" />

            <div className="space-y-12">
                {/* Hero Section */}
                <div className="text-center">
                    <h1 className="text-4xl font-bold tracking-tight text-gray-900 sm:text-5xl">
                        Tài Liệu API CityResQ360
                    </h1>
                    <p className="mt-4 text-lg text-gray-600">
                        Hướng dẫn tích hợp và tài liệu API đầy đủ cho tất cả dịch vụ microservices
                    </p>
                    <div className="mt-6 flex justify-center gap-4">
                        <Link
                            href="/documents/media-service"
                            className="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
                        >
                            Bắt Đầu
                            <ArrowRight className="ml-2 h-4 w-4" />
                        </Link>
                        <a
                            href="https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ"
                            target="_blank"
                            rel="noopener noreferrer"
                            className="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                        >
                            Xem GitHub
                        </a>
                    </div>
                </div>

                {/* Features */}
                <div className="grid gap-6 md:grid-cols-3">
                    <div className="rounded-lg border bg-white p-6">
                        <CheckCircle className="h-8 w-8 text-green-600" />
                        <h3 className="mt-4 text-lg font-semibold">RESTful APIs</h3>
                        <p className="mt-2 text-sm text-gray-600">
                            Tất cả APIs tuân theo chuẩn REST, dễ tích hợp với mọi ngôn ngữ lập trình
                        </p>
                    </div>
                    <div className="rounded-lg border bg-white p-6">
                        <CheckCircle className="h-8 w-8 text-green-600" />
                        <h3 className="mt-4 text-lg font-semibold">Code Examples</h3>
                        <p className="mt-2 text-sm text-gray-600">
                            Ví dụ code chi tiết cho Laravel, Python, Node.js và nhiều ngôn ngữ khác
                        </p>
                    </div>
                    <div className="rounded-lg border bg-white p-6">
                        <CheckCircle className="h-8 w-8 text-green-600" />
                        <h3 className="mt-4 text-lg font-semibold">2 Integration Methods</h3>
                        <p className="mt-2 text-sm text-gray-600">
                            Lựa chọn truy cập qua CoreAPI hoặc trực tiếp microservice
                        </p>
                    </div>
                </div>

                {/* Services Grid */}
                <div>
                    <h2 className="text-2xl font-bold text-gray-900">Dịch Vụ Có Sẵn</h2>
                    <p className="mt-2 text-gray-600">
                        Chọn service để xem tài liệu API chi tiết
                    </p>

                    <div className="mt-6 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                        {services.map((service) => (
                            <ServiceCard key={service.id} service={service} />
                        ))}
                    </div>
                </div>
            </div>
        </DocsLayout>
    );
}

// Service Card Component
function ServiceCard({ service }: { service: Service }) {
    const Icon = iconMap[service.icon] || Image;
    const isAvailable = service.status === 'stable';

    return (
        <Link
            href={isAvailable ? `/documents/${service.id}` : '#'}
            className={`
                group relative rounded-lg border bg-white p-6 transition-all
                ${isAvailable
                    ? 'hover:border-blue-300 hover:shadow-md'
                    : 'cursor-not-allowed opacity-60'
                }
            `}
        >
            <div className="flex items-start justify-between">
                <div className="rounded-lg bg-blue-50 p-3 group-hover:bg-blue-100">
                    <Icon className="h-6 w-6 text-blue-600" />
                </div>
                {service.status === 'stable' && (
                    <span className="rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-700">
                        Stable
                    </span>
                )}
                {service.status === 'coming-soon' && (
                    <span className="rounded-full bg-gray-100 px-2 py-1 text-xs font-medium text-gray-600">
                        Coming Soon
                    </span>
                )}
            </div>

            <h3 className="mt-4 text-lg font-semibold text-gray-900">
                {service.name}
            </h3>
            <p className="mt-2 text-sm text-gray-600">
                {service.description}
            </p>

            {isAvailable && (
                <div className="mt-4 flex items-center text-sm font-medium text-blue-600 group-hover:text-blue-700">
                    Xem tài liệu
                    <ArrowRight className="ml-1 h-4 w-4" />
                </div>
            )}
        </Link>
    );
}
