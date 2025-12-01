import React from 'react';
import { Link } from '@inertiajs/react';
import { Package, Key, BarChart3, Clock, CheckCircle } from 'lucide-react';

interface Module {
    module_name: string;
    base_url: string;
}

interface ActiveModule {
    id: number;
    client_id: string;
    current_storage_mb: number;
    max_storage_mb: number;
    total_requests: number;
    module: Module;
}

interface PendingRequest {
    id: number;
    created_at: string;
    module: Module;
}

interface Props {
    activeModules: ActiveModule[];
    pendingRequests: PendingRequest[];
}

export default function Dashboard({ activeModules, pendingRequests }: Props) {
    return (
        <div className="min-h-screen bg-gray-50">
            {/* Header */}
            <header className="bg-white shadow">
                <div className="max-w-7xl mx-auto px-4 py-6">
                    <div className="flex items-center justify-between">
                        <div>
                            <h1 className="text-3xl font-bold text-gray-900">Client Portal</h1>
                            <p className="text-gray-600 mt-1">Quản lý tài khoản và tích hợp API</p>
                        </div>
                        <div className="flex gap-3">
                            <Link href="/client/modules" className="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 flex items-center gap-2">
                                <Package size={16} />
                                Tích Hợp Module
                            </Link>
                            <Link href="/client/api-keys" className="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300 flex items-center gap-2">
                                <Key size={16} />
                                API Keys
                            </Link>
                        </div>
                    </div>
                </div>
            </header>

            <main className="max-w-7xl mx-auto px-4 py-8 space-y-6">
                {/* Stats Cards */}
                <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div className="bg-white rounded-lg shadow p-6">
                        <div className="flex items-center gap-3">
                            <div className="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                                <CheckCircle className="text-green-600" size={24} />
                            </div>
                            <div>
                                <div className="text-sm text-gray-600">Active Modules</div>
                                <div className="text-2xl font-bold">{activeModules.length}</div>
                            </div>
                        </div>
                    </div>

                    <div className="bg-white rounded-lg shadow p-6">
                        <div className="flex items-center gap-3">
                            <div className="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                                <Clock className="text-orange-600" size={24} />
                            </div>
                            <div>
                                <div className="text-sm text-gray-600">Pending Requests</div>
                                <div className="text-2xl font-bold">{pendingRequests.length}</div>
                            </div>
                        </div>
                    </div>

                    <div className="bg-white rounded-lg shadow p-6">
                        <div className="flex items-center gap-3">
                            <div className="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                <BarChart3 className="text-blue-600" size={24} />
                            </div>
                            <div>
                                <div className="text-sm text-gray-600">Total Requests</div>
                                <div className="text-2xl font-bold">
                                    {activeModules.reduce((sum, m) => sum + m.total_requests, 0).toLocaleString()}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Active Modules */}
                {activeModules.length > 0 && (
                    <div className="bg-white rounded-lg shadow">
                        <div className="p-6 border-b">
                            <h2 className="text-lg font-semibold">Active Modules</h2>
                        </div>
                        <div className="divide-y">
                            {activeModules.map((module) => (
                                <div key={module.id} className="p-6">
                                    <div className="flex items-center justify-between mb-3">
                                        <h3 className="font-semibold text-lg">{module.module.module_name}</h3>
                                        <span className="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm">Active</span>
                                    </div>
                                    <div className="grid grid-cols-2 gap-4 text-sm">
                                        <div>
                                            <div className="text-gray-600">Client ID</div>
                                            <div className="font-mono text-xs">{module.client_id}</div>
                                        </div>
                                        <div>
                                            <div className="text-gray-600">Total Requests</div>
                                            <div className="font-semibold">{module.total_requests.toLocaleString()}</div>
                                        </div>
                                        <div>
                                            <div className="text-gray-600">Storage</div>
                                            <div>{module.current_storage_mb}/{module.max_storage_mb} MB</div>
                                        </div>
                                        <div>
                                            <div className="text-gray-600">Base URL</div>
                                            <div className="font-mono text-xs truncate">{module.module.base_url}</div>
                                        </div>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>
                )}

                {/* Pending Requests */}
                {pendingRequests.length > 0 && (
                    <div className="bg-white rounded-lg shadow">
                        <div className="p-6 border-b">
                            <h2 className="text-lg font-semibold">Pending Requests</h2>
                        </div>
                        <div className="divide-y">
                            {pendingRequests.map((request) => (
                                <div key={request.id} className="p-6 flex items-center justify-between">
                                    <div>
                                        <h3 className="font-semibold">{request.module.module_name}</h3>
                                        <p className="text-sm text-gray-600">
                                            Requested: {new Date(request.created_at).toLocaleDateString('vi-VN')}
                                        </p>
                                    </div>
                                    <span className="px-3 py-1 bg-orange-100 text-orange-700 rounded-full text-sm flex items-center gap-1">
                                        <Clock size={14} />
                                        Pending
                                    </span>
                                </div>
                            ))}
                        </div>
                    </div>
                )}

                {/* Empty State */}
                {activeModules.length === 0 && pendingRequests.length === 0 && (
                    <div className="bg-white rounded-lg shadow p-12 text-center">
                        <Package className="mx-auto text-gray-400 mb-4" size={48} />
                        <h3 className="text-lg font-semibold text-gray-900 mb-2">Chưa có modules nào</h3>
                        <p className="text-gray-600 mb-4">Bắt đầu tích hợp API modules vào ứng dụng của bạn</p>
                        <Link href="/client/modules" className="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            <Package size={16} />
                            Khám Phá Modules
                        </Link>
                    </div>
                )}
            </main>
        </div>
    );
}
