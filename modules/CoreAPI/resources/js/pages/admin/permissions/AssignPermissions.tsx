import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AdminLayout from '../layouts/AdminLayout';
import { ArrowLeft, Shield, Check, Save } from 'lucide-react';
import { showSuccess, showError } from '@/utils/notifications';

interface Permission {
    id: number;
    ten_chuc_nang: string;
    route_name: string;
    mo_ta: string;
    has_permission: boolean;
}

interface Props {
    role: {
        id: number;
        ten_vai_tro: string;
        slug: string;
    };
    functions: Record<string, Permission[]>;
}

export default function AssignPermissions({ role, functions }: Props) {
    const [selectedPermissions, setSelectedPermissions] = useState<number[]>(() => {
        const initial: number[] = [];
        Object.values(functions).forEach((group) => {
            group.forEach((func) => {
                if (func.has_permission) {
                    initial.push(func.id);
                }
            });
        });
        return initial;
    });

    const handleTogglePermission = (functionId: number) => {
        setSelectedPermissions((prev) =>
            prev.includes(functionId)
                ? prev.filter((id) => id !== functionId)
                : [...prev, functionId]
        );
    };

    const handleToggleGroup = (group: Permission[]) => {
        const groupIds = group.map((f) => f.id);
        const allSelected = groupIds.every((id) => selectedPermissions.includes(id));

        if (allSelected) {
            setSelectedPermissions((prev) => prev.filter((id) => !groupIds.includes(id)));
        } else {
            setSelectedPermissions((prev) => {
                const newIds = groupIds.filter((id) => !prev.includes(id));
                return [...prev, ...newIds];
            });
        }
    };

    const handleSubmit = () => {
        router.post(`/admin/permissions/roles/assign/${role.id}`, {
            permissions: selectedPermissions,
        }, {
            onSuccess: () => {
                showSuccess('Cập nhật phân quyền thành công!');
            },
            onError: (errors) => {
                const firstError = Object.values(errors)[0] as string;
                showError(firstError || 'Không thể cập nhật phân quyền!');
            },
        });
    };

    const groupNames: Record<string, string> = {
        dashboard: 'Dashboard',
        reports: 'Quản lý phản ánh',
        users: 'Quản lý người dùng',
        agencies: 'Quản lý cơ quan',
        analytics: 'Phân tích & Báo cáo',
        settings: 'Cài đặt hệ thống',
        permissions: 'Quản lý phân quyền',
        system: 'Hệ thống',
    };

    const groupColors: Record<string, string> = {
        dashboard: 'bg-blue-50 border-blue-200',
        reports: 'bg-green-50 border-green-200',
        users: 'bg-purple-50 border-purple-200',
        agencies: 'bg-orange-50 border-orange-200',
        analytics: 'bg-pink-50 border-pink-200',
        settings: 'bg-yellow-50 border-yellow-200',
        permissions: 'bg-red-50 border-red-200',
        system: 'bg-gray-50 border-gray-200',
    };

    return (
        <AdminLayout>
            <Head title={`Phân quyền - ${role.ten_vai_tro}`} />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <Link
                            href="/admin/permissions/roles"
                            className="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                        >
                            <ArrowLeft className="h-4 w-4" />
                            Quay lại
                        </Link>
                        <div>
                            <h1 className="text-2xl font-bold text-gray-900">Phân quyền cho vai trò</h1>
                            <div className="mt-1 flex items-center gap-2">
                                <Shield className="h-4 w-4 text-gray-500" />
                                <span className="text-sm text-gray-500">{role.ten_vai_tro}</span>
                                <span className="inline-flex items-center gap-1 rounded-md bg-gray-100 px-2 py-0.5 text-xs font-mono text-gray-600">
                                    {role.slug}
                                </span>
                            </div>
                        </div>
                    </div>
                    <button
                        onClick={handleSubmit}
                        className="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
                    >
                        <Save className="h-4 w-4" />
                        Lưu phân quyền
                    </button>
                </div>

                {/* Summary */}
                <div className="rounded-lg border border-blue-200 bg-blue-50 p-4">
                    <div className="flex items-center justify-between">
                        <div className="flex items-center gap-3">
                            <div className="rounded-full bg-blue-600 p-2">
                                <Check className="h-5 w-5 text-white" />
                            </div>
                            <div>
                                <div className="text-sm font-medium text-blue-900">
                                    Đã chọn {selectedPermissions.length} quyền
                                </div>
                                <div className="text-xs text-blue-700">
                                    Tổng cộng {Object.values(functions).reduce((acc, group) => acc + group.length, 0)} quyền khả dụng
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Permissions by Group */}
                <div className="space-y-4">
                    {Object.entries(functions).map(([groupKey, groupFunctions]) => {
                        const allSelected = groupFunctions.every((f) => selectedPermissions.includes(f.id));
                        const someSelected = groupFunctions.some((f) => selectedPermissions.includes(f.id));

                        return (
                            <div key={groupKey} className={`rounded-lg border ${groupColors[groupKey] || 'bg-gray-50 border-gray-200'} overflow-hidden`}>
                                {/* Group Header */}
                                <div className="border-b border-gray-200 bg-white px-6 py-4">
                                    <div className="flex items-center justify-between">
                                        <div className="flex items-center gap-3">
                                            <h3 className="text-lg font-semibold text-gray-900">
                                                {groupNames[groupKey] || groupKey}
                                            </h3>
                                            <span className="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-700">
                                                {groupFunctions.length} quyền
                                            </span>
                                        </div>
                                        <button
                                            onClick={() => handleToggleGroup(groupFunctions)}
                                            className={`inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium transition-colors ${
                                                allSelected
                                                    ? 'bg-red-100 text-red-700 hover:bg-red-200'
                                                    : 'bg-blue-100 text-blue-700 hover:bg-blue-200'
                                            }`}
                                        >
                                            {allSelected ? 'Bỏ chọn tất cả' : someSelected ? 'Chọn tất cả' : 'Chọn tất cả'}
                                        </button>
                                    </div>
                                </div>

                                {/* Permissions List */}
                                <div className="divide-y divide-gray-200 bg-white">
                                    {groupFunctions.map((func) => {
                                        const isSelected = selectedPermissions.includes(func.id);

                                        return (
                                            <div
                                                key={func.id}
                                                className={`px-6 py-4 transition-colors ${
                                                    isSelected ? 'bg-blue-50' : 'hover:bg-gray-50'
                                                }`}
                                            >
                                                <label className="flex items-start gap-4 cursor-pointer">
                                                    <div className="flex h-5 items-center">
                                                        <input
                                                            type="checkbox"
                                                            checked={isSelected}
                                                            onChange={() => handleTogglePermission(func.id)}
                                                            className="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-2 focus:ring-blue-500 focus:ring-offset-0"
                                                        />
                                                    </div>
                                                    <div className="flex-1">
                                                        <div className="flex items-center gap-2">
                                                            <span className="text-sm font-medium text-gray-900">
                                                                {func.ten_chuc_nang}
                                                            </span>
                                                            {isSelected && (
                                                                <span className="inline-flex items-center gap-1 rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-800">
                                                                    <Check className="h-3 w-3" />
                                                                    Đã chọn
                                                                </span>
                                                            )}
                                                        </div>
                                                        <div className="mt-1 text-xs font-mono text-gray-500">
                                                            {func.route_name}
                                                        </div>
                                                        {func.mo_ta && (
                                                            <div className="mt-1 text-xs text-gray-600">
                                                                {func.mo_ta}
                                                            </div>
                                                        )}
                                                    </div>
                                                </label>
                                            </div>
                                        );
                                    })}
                                </div>
                            </div>
                        );
                    })}
                </div>

                {/* Bottom Actions */}
                <div className="flex items-center justify-between rounded-lg border border-gray-200 bg-white p-6">
                    <div className="text-sm text-gray-600">
                        Đã chọn <span className="font-semibold text-gray-900">{selectedPermissions.length}</span> quyền
                    </div>
                    <div className="flex items-center gap-3">
                        <Link
                            href="/admin/permissions/roles"
                            className="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                        >
                            Hủy
                        </Link>
                        <button
                            onClick={handleSubmit}
                            className="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
                        >
                            <Save className="h-4 w-4" />
                            Lưu phân quyền ({selectedPermissions.length})
                        </button>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
