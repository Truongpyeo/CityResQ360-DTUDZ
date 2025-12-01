import React from 'react';
import { Link } from '@inertiajs/react';
import AdminLayout from '../layouts/AdminLayout';
import { Image, Bell, Wallet, Cpu, AlertTriangle, BarChart, Search } from 'lucide-react';

interface ModuleStats {
    total_modules: number;
    pending_requests: number;
    active_clients: number;
    total_requests_today: number;
}

interface Module {
    id: number;
    module_key: string;
    module_name: string;
    description: string;
    icon: string;
    is_active: boolean;
    credentials_count: number;
    requests_count: number;
}

interface Props {
    stats: ModuleStats;
    modules: Module[];
}

const iconMap: Record<string, any> = {
    Image, Bell, Wallet, Cpu, AlertTriangle, BarChart, Search
};

export default function Dashboard({ stats, modules }: Props) {
    return (
        <AdminLayout title="Module Management">
            <div className="space-y-6">
                {/* Header */}
                <div>
                    <h1 className="text-2xl font-bold text-gray-900">Module Management</h1>
                    <p className="text-sm text-gray-600 mt-1">Quản lý modules và client requests</p>
                </div>

                {/* Stats Cards */}
                <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div className="bg-white rounded-lg shadow p-6">
                        <div className="text-sm text-gray-600">Total Modules</div>
                        <div className="text-3xl font-bold text-gray-900 mt-2">{stats.total_modules}</div>
                    </div>
                    <div className="bg-white rounded-lg shadow p-6">
                        <div className="text-sm text-gray-600">Pending Requests</div>
                        <div className="text-3xl font-bold text-orange-600 mt-2">{stats.pending_requests}</div>
                    </div>
                    <div className="bg-white rounded-lg shadow p-6">
                        <div className="text-sm text-gray-600">Active Clients</div>
                        <div className="text-3xl font-bold text-green-600 mt-2">{stats.active_clients}</div>
                    </div>
                    <div className="bg-white rounded-lg shadow p-6">
                        <div className="text-sm text-gray-600">Total Requests</div>
                        <div className="text-3xl font-bold text-blue-600 mt-2">{stats.total_requests_today.toLocaleString()}</div>
                    </div>
                </div>

                {/* Modules Grid */}
                <div className="bg-white rounded-lg shadow">
                    <div className="p-6 border-b">
                        <h2 className="text-lg font-semibold">Modules Overview</h2>
                    </div>
                    <div className="p-6">
                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            {modules.map((module) => {
                                const Icon = iconMap[module.icon] || Image;
                                return (
                                    <Link
                                        key={module.id}
                                        href={`/admin/modules/${module.module_key}`}
                                        className="block p-6 border rounded-lg hover:shadow-lg transition-shadow"
                                    >
                                        <div className="flex items-start justify-between">
                                            <div className="flex items-center gap-3">
                                                <div className={`w-12 h-12 rounded-lg flex items-center justify-center ${module.is_active ? 'bg-blue-100' : 'bg-gray-100'
                                                    }`}>
                                                    <Icon className={module.is_active ? 'text-blue-600' : 'text-gray-400'} size={24} />
                                                </div>
                                                <div>
                                                    <h3 className="font-semibold text-gray-900">{module.module_name}</h3>
                                                    <span className={`inline-block px-2 py-0.5 text-xs rounded-full mt-1 ${module.is_active
                                                        ? 'bg-green-100 text-green-700'
                                                        : 'bg-gray-100 text-gray-600'
                                                        }`}>
                                                        {module.is_active ? '✓ Active' : 'Coming Soon'}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <p className="text-sm text-gray-600 mt-3">{module.description}</p>
                                        <div className="flex gap-4 mt-4 text-sm">
                                            <div>
                                                <span className="text-gray-600">Clients:</span>{' '}
                                                <span className="font-semibold">{module.credentials_count}</span>
                                            </div>
                                            {module.requests_count > 0 && (
                                                <div>
                                                    <span className="text-gray-600">Pending:</span>{' '}
                                                    <span className="font-semibold text-orange-600">{module.requests_count}</span>
                                                </div>
                                            )}
                                        </div>
                                    </Link>
                                );
                            })}
                        </div>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
