import React from 'react';
import { Link, router } from '@inertiajs/react';
import AdminLayout from '../layouts/AdminLayout';
import { RefreshCw, Ban, CheckCircle, Trash2, BarChart3 } from 'lucide-react';

interface User {
    ho_ten: string;
    email: string;
}

interface Module {
    id: number;
    module_key: string;
    module_name: string;
    description: string;
    base_url: string;
}

interface Credential {
    id: number;
    client_id: string;
    max_storage_mb: number;
    max_requests_per_day: number;
    current_storage_mb: number;
    total_requests: number;
    last_used_at: string | null;
    is_active: boolean;
    revoked_at: string | null;
    created_at: string;
    user: User;
}

interface Props {
    module: Module;
}

export default function Show({ module }: Props) {
    const credentials = (module as any).credentials || [];

    const handleRegenerateSecret = (id: number) => {
        if (confirm('Regenerate secret? Token cũ sẽ invalid ngay lập tức!')) {
            router.post(`/admin/modules/credentials/${id}/regenerate`);
        }
    };

    const handleRevoke = (id: number) => {
        const reason = prompt('Lý do revoke:');
        if (reason) {
            router.post(`/admin/modules/credentials/${id}/revoke`, { reason });
        }
    };

    const handleRestore = (id: number) => {
        if (confirm('Restore credential này?')) {
            router.post(`/admin/modules/credentials/${id}/restore`);
        }
    };

    const getStoragePercentage = (current: number, max: number) => {
        return max > 0 ? (current / max) * 100 : 0;
    };

    return (
        <AdminLayout title={`${module.module_name} - Details`}>
            <div className="space-y-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold text-gray-900">{module.module_name}</h1>
                        <p className="text-sm text-gray-600 mt-1">{module.description}</p>
                    </div>
                    <div className="flex gap-2">
                        <Link
                            href={`/admin/modules/${module.module_key}/requests`}
                            className="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm"
                        >
                            View Requests
                        </Link>
                        <Link href="/admin/modules/dashboard" className="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300 text-sm">
                            ← Back
                        </Link>
                    </div>
                </div>

                {/* Module Info */}
                <div className="bg-white rounded-lg shadow p-6">
                    <h2 className="font-semibold mb-4">Module Information</h2>
                    <div className="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span className="text-gray-600">Base URL:</span>
                            <div className="font-mono text-sm mt-1">{module.base_url}</div>
                        </div>
                        <div>
                            <span className="text-gray-600">Active Clients:</span>
                            <div className="text-2xl font-bold mt-1">{credentials.filter((c: Credential) => c.is_active).length}</div>
                        </div>
                    </div>
                </div>

                {/* Clients List */}
                <div className="bg-white rounded-lg shadow">
                    <div className="p-6 border-b">
                        <h2 className="font-semibold">Active Clients</h2>
                    </div>
                    <div className="divide-y">
                        {credentials.length === 0 ? (
                            <div className="p-8 text-center text-gray-500">
                                Chưa có clients nào
                            </div>
                        ) : (
                            credentials.map((cred: Credential) => (
                                <div key={cred.id} className="p-6">
                                    <div className="flex items-start justify-between">
                                        <div className="flex-1">
                                            <div className="flex items-center gap-3 mb-3">
                                                <h3 className="font-semibold text-gray-900">{cred.user.ho_ten}</h3>
                                                {cred.is_active ? (
                                                    <span className="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs flex items-center gap-1">
                                                        <CheckCircle size={12} /> Active
                                                    </span>
                                                ) : (
                                                    <span className="px-2 py-1 bg-red-100 text-red-700 rounded-full text-xs flex items-center gap-1">
                                                        <Ban size={12} /> Revoked
                                                    </span>
                                                )}
                                            </div>

                                            <div className="grid grid-cols-2 gap-4 text-sm mb-4">
                                                <div>
                                                    <div className="text-gray-600">Email</div>
                                                    <div className="font-medium">{cred.user.email}</div>
                                                </div>
                                                <div>
                                                    <div className="text-gray-600">Client ID</div>
                                                    <div className="font-mono text-xs">{cred.client_id}</div>
                                                </div>
                                                <div>
                                                    <div className="text-gray-600">Created</div>
                                                    <div>{new Date(cred.created_at).toLocaleDateString('vi-VN')}</div>
                                                </div>
                                                <div>
                                                    <div className="text-gray-600">Last Used</div>
                                                    <div>{cred.last_used_at ? new Date(cred.last_used_at).toLocaleString('vi-VN') : 'Never'}</div>
                                                </div>
                                            </div>

                                            {/* Usage Stats */}
                                            <div className="grid grid-cols-2 gap-4">
                                                <div>
                                                    <div className="flex justify-between text-sm mb-1">
                                                        <span className="text-gray-600">Storage</span>
                                                        <span className="font-medium">{cred.current_storage_mb}/{cred.max_storage_mb} MB</span>
                                                    </div>
                                                    <div className="w-full bg-gray-200 rounded-full h-2">
                                                        <div
                                                            className="bg-blue-600 h-2 rounded-full transition-all"
                                                            style={{ width: `${getStoragePercentage(cred.current_storage_mb, cred.max_storage_mb)}%` }}
                                                        />
                                                    </div>
                                                </div>
                                                <div>
                                                    <div className="text-sm mb-1">
                                                        <span className="text-gray-600">Total Requests: </span>
                                                        <span className="font-bold">{cred.total_requests.toLocaleString()}</span>
                                                    </div>
                                                    <div className="text-xs text-gray-500">Limit: {cred.max_requests_per_day.toLocaleString()}/day</div>
                                                </div>
                                            </div>
                                        </div>

                                        {/* Actions */}
                                        <div className="ml-4 space-y-2">
                                            {cred.is_active ? (
                                                <>
                                                    <button
                                                        onClick={() => handleRegenerateSecret(cred.id)}
                                                        className="px-3 py-2 bg-orange-600 text-white rounded hover:bg-orange-700 flex items-center gap-2 text-sm w-full"
                                                    >
                                                        <RefreshCw size={14} />
                                                        Regenerate
                                                    </button>
                                                    <button
                                                        onClick={() => handleRevoke(cred.id)}
                                                        className="px-3 py-2 bg-red-600 text-white rounded hover:bg-red-700 flex items-center gap-2 text-sm w-full"
                                                    >
                                                        <Ban size={14} />
                                                        Revoke
                                                    </button>
                                                </>
                                            ) : (
                                                <button
                                                    onClick={() => handleRestore(cred.id)}
                                                    className="px-3 py-2 bg-green-600 text-white rounded hover:bg-green-700 flex items-center gap-2 text-sm w-full"
                                                >
                                                    <CheckCircle size={14} />
                                                    Restore
                                                </button>
                                            )}
                                        </div>
                                    </div>
                                </div>
                            ))
                        )}
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
