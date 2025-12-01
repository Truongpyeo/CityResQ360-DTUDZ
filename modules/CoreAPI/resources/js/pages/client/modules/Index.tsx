import React from 'react';
import { Link, router } from '@inertiajs/react';
import { Image, Bell, Wallet, Cpu, AlertTriangle, BarChart, Search, CheckCircle, Clock, XCircle } from 'lucide-react';

interface Module {
    id: number;
    module_key: string;
    module_name: string;
    description: string;
    icon: string;
    is_active: boolean;
    user_request?: {
        status: string;
        created_at: string;
    } | null;
    user_credential?: {
        client_id: string;
    } | null;
}

interface Props {
    modules: Module[];
}

const iconMap: Record<string, any> = {
    Image, Bell, Wallet, Cpu, AlertTriangle, BarChart, Search
};

export default function Index({ modules }: Props) {
    return (
        <div className="min-h-screen bg-gray-50">
            {/* Header */}
            <header className="bg-white shadow">
                <div className="max-w-7xl mx-auto px-4 py-6">
                    <div className="flex items-center justify-between">
                        <div>
                            <h1 className="text-3xl font-bold text-gray-900">Developer Portal</h1>
                            <p className="text-gray-600 mt-1">Quản lý API modules của bạn</p>
                        </div>
                        <div className="flex gap-3">
                            <Link href="/client/api-keys" className="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                                API Keys
                            </Link>
                            <Link href="/client/usage" className="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">
                                Usage Stats
                            </Link>
                        </div>
                    </div>
                </div>
            </header>

            <main className="max-w-7xl mx-auto px-4 py-8">
                {/* Modules Grid */}
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    {modules.map((module) => {
                        const Icon = iconMap[module.icon] || Image;
                        const hasCredential = !!module.user_credential;
                        const hasPendingRequest = module.user_request?.status === 'pending';
                        const isRejected = module.user_request?.status === 'rejected';

                        return (
                            <div key={module.id} className="bg-white rounded-lg shadow-sm border p-6">
                                <div className="flex items-start gap-4 mb-4">
                                    <div className={`w-14 h-14 rounded-lg flex items-center justify-center ${module.is_active ? 'bg-blue-50' : 'bg-gray-100'
                                        }`}>
                                        <Icon className={module.is_active ? 'text-blue-600' : 'text-gray-400'} size={28} />
                                    </div>
                                    <div className="flex-1">
                                        <h3 className="text-lg font-semibold text-gray-900">{module.module_name}</h3>
                                        <span className={`inline-block px-2 py-0.5 text-xs rounded-full mt-1 ${module.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'
                                            }`}>
                                            {module.is_active ? '✓ Stable' : 'Coming Soon'}
                                        </span>
                                    </div>
                                </div>

                                <p className="text-sm text-gray-600 mb-4">{module.description}</p>

                                {/* Status / Actions */}
                                <div className="border-t pt-4">
                                    {!module.is_active ? (
                                        <div className="text-sm text-gray-500 italic">Module chưa available</div>
                                    ) : hasCredential ? (
                                        <div className="flex items-center gap-2 text-green-600 text-sm">
                                            <CheckCircle size={16} />
                                            <span className="font-medium">Đã kích hoạt</span>
                                        </div>
                                    ) : hasPendingRequest ? (
                                        <div className="flex items-center gap-2 text-orange-600 text-sm">
                                            <Clock size={16} />
                                            <span>Request đang chờ duyệt...</span>
                                        </div>
                                    ) : isRejected ? (
                                        <div>
                                            <div className="flex items-center gap-2 text-red-600 text-sm mb-2">
                                                <XCircle size={16} />
                                                <span>Request bị từ chối</span>
                                            </div>
                                            <Link
                                                href={`/client/modules/${module.module_key}/register`}
                                                className="block w-full text-center px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm"
                                            >
                                                Đăng Ký Lại
                                            </Link>
                                        </div>
                                    ) : (
                                        <Link
                                            href={`/client/modules/${module.module_key}/register`}
                                            className="block w-full text-center px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm"
                                        >
                                            Đăng Ký Ngay
                                        </Link>
                                    )}
                                </div>
                            </div>
                        );
                    })}
                </div>
            </main>
        </div>
    );
}
