import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AdminLayout from '../layouts/AdminLayout';
import {
    Shield,
    Search,
    Plus,
    RefreshCw,
    Eye,
    Edit,
    Trash2,
    Users,
    Key,
    ChevronLeft,
    ChevronRight,
    CheckCircle2,
    XCircle,
} from 'lucide-react';
import { showSuccess, showError, showDeleteConfirm } from '@/utils/notifications';

interface Role {
    id: number;
    ten_vai_tro: string;
    slug: string;
    mo_ta: string;
    trang_thai: number;
    admins_count: number;
    permissions_count: number;
    created_at: string;
}

interface Props {
    roles: {
        data: Role[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number;
        to: number;
    };
    filters: {
        trang_thai?: string;
        search?: string;
    };
}

export default function Roles({ roles, filters }: Props) {
    const [searchTerm, setSearchTerm] = useState(filters.search || '');
    const [statusFilter, setStatusFilter] = useState(filters.trang_thai || '');

    const handleFilter = () => {
        router.get('/admin/permissions/roles', {
            search: searchTerm,
            trang_thai: statusFilter,
        }, {
            preserveState: true,
            onSuccess: () => {
                showSuccess('Đã áp dụng bộ lọc!');
            },
        });
    };

    const handleRefresh = () => {
        router.reload({
            onSuccess: () => {
                showSuccess('Đã làm mới dữ liệu!');
            },
        });
    };

    const handleDelete = async (roleId: number, name: string) => {
        const confirmed = await showDeleteConfirm(`vai trò "${name}"`);

        if (confirmed) {
            router.delete(`/admin/permissions/roles/delete/${roleId}`, {
                onSuccess: () => {
                    showSuccess('Xóa vai trò thành công!');
                },
                onError: (errors) => {
                    const firstError = Object.values(errors)[0] as string;
                    showError(firstError || 'Không thể xóa vai trò!');
                },
            });
        }
    };

    const totalActive = roles.data.filter(r => r.trang_thai === 1).length;
    const totalLocked = roles.data.filter(r => r.trang_thai === 0).length;

    return (
        <AdminLayout>
            <Head title="Quản lý vai trò - Admin" />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h1 className="text-3xl font-bold text-gray-900">Quản lý vai trò</h1>
                        <p className="mt-1 text-sm text-gray-500">
                            Quản lý vai trò và phân quyền cho quản trị viên
                        </p>
                    </div>
                    <div className="flex gap-3">
                        <button
                            onClick={handleRefresh}
                            className="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                        >
                            <RefreshCw className="h-4 w-4" />
                            Làm mới
                        </button>
                        <Link
                            href="/admin/permissions/roles/create"
                            className="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
                        >
                            <Plus className="h-4 w-4" />
                            Thêm vai trò
                        </Link>
                    </div>
                </div>

                {/* Stats Cards */}
                <div className="grid gap-4 grid-cols-1 sm:grid-cols-3">
                    <div className="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                        <div className="flex items-center justify-between">
                            <div>
                                <p className="text-sm font-medium text-gray-600">Tổng vai trò</p>
                                <p className="mt-1 text-2xl font-bold text-gray-900">{roles.total}</p>
                            </div>
                            <div className="rounded-full bg-blue-100 p-3">
                                <Shield className="h-6 w-6 text-blue-600" />
                            </div>
                        </div>
                    </div>
                    <div className="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                        <div className="flex items-center justify-between">
                            <div>
                                <p className="text-sm font-medium text-gray-600">Hoạt động</p>
                                <p className="mt-1 text-2xl font-bold text-green-600">{totalActive}</p>
                            </div>
                            <div className="rounded-full bg-green-100 p-3">
                                <CheckCircle2 className="h-6 w-6 text-green-600" />
                            </div>
                        </div>
                    </div>
                    <div className="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                        <div className="flex items-center justify-between">
                            <div>
                                <p className="text-sm font-medium text-gray-600">Đã khóa</p>
                                <p className="mt-1 text-2xl font-bold text-red-600">{totalLocked}</p>
                            </div>
                            <div className="rounded-full bg-red-100 p-3">
                                <XCircle className="h-6 w-6 text-red-600" />
                            </div>
                        </div>
                    </div>
                </div>

                {/* Filters */}
                <div className="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                    <div className="flex flex-wrap gap-4">
                        <div className="flex-1 min-w-[250px]">
                            <label className="block text-sm font-medium text-gray-700 mb-2">Tìm kiếm</label>
                            <div className="relative">
                                <Search className="absolute left-3 top-2.5 h-5 w-5 text-gray-400" />
                                <input
                                    type="text"
                                    value={searchTerm}
                                    onChange={(e) => setSearchTerm(e.target.value)}
                                    placeholder="Tìm kiếm theo tên vai trò, slug..."
                                    className="block w-full rounded-lg border border-gray-300 pl-10 pr-4 py-3 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-20"
                                />
                            </div>
                        </div>
                        <div className="flex-1 min-w-[150px]">
                            <label className="block text-sm font-medium text-gray-700 mb-2">Trạng thái</label>
                            <select
                                value={statusFilter}
                                onChange={(e) => setStatusFilter(e.target.value)}
                                className="block w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-20"
                            >
                                <option value="">Tất cả</option>
                                <option value="1">Hoạt động</option>
                                <option value="0">Đã khóa</option>
                            </select>
                        </div>
                        <div className="flex items-end">
                            <button
                                onClick={handleFilter}
                                className="whitespace-nowrap rounded-lg bg-blue-600 px-6 py-3 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors"
                            >
                                Áp dụng
                            </button>
                        </div>
                    </div>
                </div>

                {/* Roles Table */}
                <div className="rounded-lg border border-gray-200 bg-white shadow-sm overflow-hidden">
                    <div className="border-b border-gray-200 bg-gray-50 px-6 py-4">
                        <div className="flex items-center justify-between">
                            <h3 className="text-lg font-semibold text-gray-900">Danh sách vai trò</h3>
                            <span className="text-sm text-gray-500">{roles.total} vai trò</span>
                        </div>
                    </div>

                    <div className="overflow-x-auto">
                        <table className="w-full">
                            <thead className="border-b border-gray-200 bg-gray-50">
                                <tr>
                                    <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                                        STT
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                                        Thông tin vai trò
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                                        Quản trị viên
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                                        Quyền hạn
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                                        Trạng thái
                                    </th>
                                    <th className="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600">
                                        Thao tác
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-200 bg-white">
                                {roles.data.map((role, index) => (
                                    <tr key={role.id} className="hover:bg-gray-50 transition-colors">
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            <span className="text-sm font-medium text-gray-900">{roles.from + index}</span>
                                        </td>
                                        <td className="px-6 py-4">
                                            <div>
                                                <div className="text-sm font-medium text-gray-900">{role.ten_vai_tro}</div>
                                                <div className="mt-1 inline-flex items-center gap-1 rounded-md bg-gray-100 px-2 py-0.5 text-xs font-mono text-gray-600">
                                                    {role.slug}
                                                </div>
                                                {role.mo_ta && (
                                                    <div className="mt-1 text-xs text-gray-500 line-clamp-1">
                                                        {role.mo_ta}
                                                    </div>
                                                )}
                                            </div>
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            <div className="flex items-center gap-2">
                                                <Users className="h-4 w-4 text-gray-400" />
                                                <span className="text-sm font-medium text-gray-900">
                                                    {role.admins_count}
                                                </span>
                                            </div>
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            <div className="flex items-center gap-2">
                                                <Key className="h-4 w-4 text-gray-400" />
                                                <span className="text-sm font-medium text-gray-900">
                                                    {role.permissions_count}
                                                </span>
                                            </div>
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            {role.trang_thai === 1 ? (
                                                <span className="inline-flex items-center gap-1.5 rounded-full bg-green-100 px-2.5 py-1 text-xs font-medium text-green-800">
                                                    <CheckCircle2 className="h-3.5 w-3.5" />
                                                    Hoạt động
                                                </span>
                                            ) : (
                                                <span className="inline-flex items-center gap-1.5 rounded-full bg-red-100 px-2.5 py-1 text-xs font-medium text-red-800">
                                                    <XCircle className="h-3.5 w-3.5" />
                                                    Đã khóa
                                                </span>
                                            )}
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap text-right">
                                            <div className="flex items-center justify-end gap-2">
                                                <Link
                                                    href={`/admin/permissions/roles/assign/${role.id}`}
                                                    className="inline-flex items-center gap-1.5 rounded-lg bg-purple-50 px-3 py-1.5 text-xs font-medium text-purple-700 hover:bg-purple-100 transition-colors"
                                                >
                                                    <Key className="h-4 w-4" />
                                                    Phân quyền
                                                </Link>
                                                <Link
                                                    href={`/admin/permissions/roles/edit/${role.id}`}
                                                    className="inline-flex items-center gap-1.5 rounded-lg bg-gray-50 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-100 transition-colors"
                                                >
                                                    <Edit className="h-4 w-4" />
                                                    Sửa
                                                </Link>
                                                <button
                                                    onClick={() => handleDelete(role.id, role.ten_vai_tro)}
                                                    className="inline-flex items-center gap-1.5 rounded-lg bg-red-50 px-3 py-1.5 text-xs font-medium text-red-700 hover:bg-red-100 transition-colors"
                                                >
                                                    <Trash2 className="h-4 w-4" />
                                                    Xóa
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>

                    {/* Pagination */}
                    {roles.last_page > 1 && (
                        <div className="border-t border-gray-200 bg-gray-50 px-6 py-4">
                            <div className="flex items-center justify-between">
                                <div className="text-sm text-gray-500">
                                    Hiển thị <span className="font-medium">{roles.from || 0}</span> đến{' '}
                                    <span className="font-medium">{roles.to || 0}</span> trong tổng số{' '}
                                    <span className="font-medium">{roles.total}</span> kết quả
                                </div>
                                <div className="flex items-center gap-2">
                                    <Link
                                        href={roles.current_page > 1 ? `/admin/permissions/roles?page=${roles.current_page - 1}` : '#'}
                                        className={`inline-flex items-center gap-1 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 ${
                                            roles.current_page <= 1 ? 'opacity-50 cursor-not-allowed' : ''
                                        }`}
                                        preserveState
                                        preserveScroll
                                    >
                                        <ChevronLeft className="h-4 w-4" />
                                        Trước
                                    </Link>

                                    {[...Array(Math.min(5, roles.last_page))].map((_, i) => {
                                        const page = i + 1;
                                        return (
                                            <Link
                                                key={page}
                                                href={`/admin/permissions/roles?page=${page}`}
                                                className={`inline-flex items-center justify-center rounded-lg px-3 py-2 text-sm font-medium ${
                                                    page === roles.current_page
                                                        ? 'bg-blue-600 text-white'
                                                        : 'border border-gray-300 bg-white text-gray-700 hover:bg-gray-50'
                                                }`}
                                                preserveState
                                                preserveScroll
                                            >
                                                {page}
                                            </Link>
                                        );
                                    })}

                                    <Link
                                        href={
                                            roles.current_page < roles.last_page
                                                ? `/admin/permissions/roles?page=${roles.current_page + 1}`
                                                : '#'
                                        }
                                        className={`inline-flex items-center gap-1 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 ${
                                            roles.current_page >= roles.last_page ? 'opacity-50 cursor-not-allowed' : ''
                                        }`}
                                        preserveState
                                        preserveScroll
                                    >
                                        Sau
                                        <ChevronRight className="h-4 w-4" />
                                    </Link>
                                </div>
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </AdminLayout>
    );
}
