import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AdminLayout from '../layouts/AdminLayout';
import {
    Users,
    Search,
    Plus,
    RefreshCw,
    Edit,
    Trash2,
    Shield,
    Lock,
    Unlock,
    CheckCircle2,
    XCircle,
    Crown,
    ChevronLeft,
    ChevronRight,
    Eye,
} from 'lucide-react';
import { showSuccess, showError, showDeleteConfirm, showStatusConfirm } from '@/utils/notifications';

interface VaiTro {
    id: number;
    ten_vai_tro: string;
    slug: string;
}

interface Admin {
    id: number;
    ho_ten: string;
    email: string;
    ten_dang_nhap: string;
    so_dien_thoai: string;
    trang_thai: number;
    is_master: boolean;
    vai_tro: VaiTro | null;
    lan_dang_nhap_cuoi: string | null;
    created_at: string;
}

interface Props {
    admins: {
        data: Admin[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number;
        to: number;
    };
    roles: VaiTro[];
    stats: {
        total: number;
        active: number;
        locked: number;
        master: number;
    };
    filters: {
        search?: string;
        id_vai_tro?: string;
        trang_thai?: string;
    };
}

export default function Index({ admins, roles, stats, filters }: Props) {
    const [searchTerm, setSearchTerm] = useState(filters.search || '');
    const [roleFilter, setRoleFilter] = useState(filters.id_vai_tro || '');
    const [statusFilter, setStatusFilter] = useState(filters.trang_thai || '');

    const handleFilter = () => {
        router.get('/admin/admins', {
            search: searchTerm,
            id_vai_tro: roleFilter,
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

    const handleStatusChange = async (adminId: number, name: string, currentStatus: number) => {
        const newStatus = currentStatus === 1 ? 0 : 1;
        const action = newStatus === 1 ? 'mở khóa' : 'khóa';

        const confirmed = await showStatusConfirm(name, action);

        if (confirmed) {
            router.patch(`/admin/admins/status/${adminId}`, {
                trang_thai: newStatus,
            }, {
                onSuccess: () => {
                    showSuccess(`${newStatus === 1 ? 'Mở khóa' : 'Khóa'} quản trị viên thành công!`);
                },
                onError: (errors) => {
                    const firstError = Object.values(errors)[0] as string;
                    showError(firstError || 'Có lỗi xảy ra!');
                },
            });
        }
    };

    const handleDelete = async (adminId: number, name: string) => {
        const confirmed = await showDeleteConfirm(`quản trị viên "${name}"`);

        if (confirmed) {
            router.delete(`/admin/admins/delete/${adminId}`, {
                onSuccess: () => {
                    showSuccess('Xóa quản trị viên thành công!');
                },
                onError: (errors) => {
                    const firstError = Object.values(errors)[0] as string;
                    showError(firstError || 'Không thể xóa quản trị viên!');
                },
            });
        }
    };

    return (
        <AdminLayout>
            <Head title="Quản lý quản trị viên - Admin" />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h1 className="text-3xl font-bold text-gray-900">Quản lý quản trị viên</h1>
                        <p className="mt-1 text-sm text-gray-500">
                            Quản lý tài khoản quản trị viên hệ thống
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
                            href="/admin/admins/create"
                            className="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
                        >
                            <Plus className="h-4 w-4" />
                            Thêm quản trị viên
                        </Link>
                    </div>
                </div>

                {/* Stats Cards */}
                <div className="grid gap-4 grid-cols-1 sm:grid-cols-4">
                    <div className="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                        <div className="flex items-center justify-between">
                            <div>
                                <p className="text-sm font-medium text-gray-600">Tổng số</p>
                                <p className="mt-1 text-2xl font-bold text-gray-900">{stats.total}</p>
                            </div>
                            <div className="rounded-full bg-blue-100 p-3">
                                <Users className="h-6 w-6 text-blue-600" />
                            </div>
                        </div>
                    </div>
                    <div className="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                        <div className="flex items-center justify-between">
                            <div>
                                <p className="text-sm font-medium text-gray-600">Hoạt động</p>
                                <p className="mt-1 text-2xl font-bold text-green-600">{stats.active}</p>
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
                                <p className="mt-1 text-2xl font-bold text-red-600">{stats.locked}</p>
                            </div>
                            <div className="rounded-full bg-red-100 p-3">
                                <XCircle className="h-6 w-6 text-red-600" />
                            </div>
                        </div>
                    </div>
                    <div className="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                        <div className="flex items-center justify-between">
                            <div>
                                <p className="text-sm font-medium text-gray-600">Master Admin</p>
                                <p className="mt-1 text-2xl font-bold text-purple-600">{stats.master}</p>
                            </div>
                            <div className="rounded-full bg-purple-100 p-3">
                                <Crown className="h-6 w-6 text-purple-600" />
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
                                    placeholder="Tìm kiếm theo tên, email, username..."
                                    className="block w-full rounded-lg border border-gray-300 pl-10 pr-4 py-3 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-20"
                                />
                            </div>
                        </div>
                        <div className="flex-1 min-w-[150px]">
                            <label className="block text-sm font-medium text-gray-700 mb-2">Vai trò</label>
                            <select
                                value={roleFilter}
                                onChange={(e) => setRoleFilter(e.target.value)}
                                className="block w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-20"
                            >
                                <option value="">Tất cả vai trò</option>
                                {roles.map((role) => (
                                    <option key={role.id} value={role.id}>
                                        {role.ten_vai_tro}
                                    </option>
                                ))}
                            </select>
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

                {/* Admins Table */}
                <div className="rounded-lg border border-gray-200 bg-white shadow-sm overflow-hidden">
                    <div className="border-b border-gray-200 bg-gray-50 px-6 py-4">
                        <div className="flex items-center justify-between">
                            <h3 className="text-lg font-semibold text-gray-900">Danh sách quản trị viên</h3>
                            <span className="text-sm text-gray-500">{admins.total} quản trị viên</span>
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
                                        Thông tin
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                                        Vai trò
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                                        Đăng nhập cuối
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
                                {admins.data.map((admin, index) => (
                                    <tr key={admin.id} className="hover:bg-gray-50 transition-colors">
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            <span className="text-sm font-medium text-gray-900">{admins.from + index}</span>
                                        </td>
                                        <td className="px-6 py-4">
                                            <div className="flex items-start gap-3">
                                                {admin.is_master && (
                                                    <Crown className="h-5 w-5 text-purple-600 flex-shrink-0 mt-0.5" />
                                                )}
                                                <div>
                                                    <div className="text-sm font-medium text-gray-900">{admin.ho_ten}</div>
                                                    <div className="text-sm text-gray-500">{admin.email}</div>
                                                    <div className="mt-1 inline-flex items-center gap-1 rounded-md bg-gray-100 px-2 py-0.5 text-xs font-mono text-gray-600">
                                                        {admin.ten_dang_nhap}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            {admin.vai_tro ? (
                                                <span className="inline-flex items-center gap-1.5 rounded-full bg-blue-100 px-2.5 py-1 text-xs font-medium text-blue-800">
                                                    <Shield className="h-3.5 w-3.5" />
                                                    {admin.vai_tro.ten_vai_tro}
                                                </span>
                                            ) : (
                                                <span className="text-sm text-gray-400">Chưa có</span>
                                            )}
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            <span className="text-sm text-gray-500">
                                                {admin.lan_dang_nhap_cuoi || 'Chưa đăng nhập'}
                                            </span>
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            {admin.trang_thai === 1 ? (
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
                                                    href={`/admin/admins/${admin.id}`}
                                                    className="inline-flex items-center gap-1.5 rounded-lg bg-blue-50 px-3 py-1.5 text-xs font-medium text-blue-700 hover:bg-blue-100 transition-colors"
                                                >
                                                    <Eye className="h-4 w-4" />
                                                    Xem
                                                </Link>
                                                <Link
                                                    href={`/admin/admins/edit/${admin.id}`}
                                                    className="inline-flex items-center gap-1.5 rounded-lg bg-gray-50 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-100 transition-colors"
                                                >
                                                    <Edit className="h-4 w-4" />
                                                    Sửa
                                                </Link>
                                                {!admin.is_master && (
                                                    <>
                                                        <button
                                                            onClick={() => handleStatusChange(admin.id, admin.ho_ten, admin.trang_thai)}
                                                            className={`inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-medium transition-colors ${
                                                                admin.trang_thai === 1
                                                                    ? 'bg-orange-50 text-orange-700 hover:bg-orange-100'
                                                                    : 'bg-green-50 text-green-700 hover:bg-green-100'
                                                            }`}
                                                        >
                                                            {admin.trang_thai === 1 ? (
                                                                <>
                                                                    <Lock className="h-4 w-4" />
                                                                    Khóa
                                                                </>
                                                            ) : (
                                                                <>
                                                                    <Unlock className="h-4 w-4" />
                                                                    Mở
                                                                </>
                                                            )}
                                                        </button>
                                                        <button
                                                            onClick={() => handleDelete(admin.id, admin.ho_ten)}
                                                            className="inline-flex items-center gap-1.5 rounded-lg bg-red-50 px-3 py-1.5 text-xs font-medium text-red-700 hover:bg-red-100 transition-colors"
                                                        >
                                                            <Trash2 className="h-4 w-4" />
                                                            Xóa
                                                        </button>
                                                    </>
                                                )}
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>

                    {/* Pagination */}
                    {admins.last_page > 1 && (
                        <div className="border-t border-gray-200 bg-gray-50 px-6 py-4">
                            <div className="flex items-center justify-between">
                                <div className="text-sm text-gray-500">
                                    Hiển thị <span className="font-medium">{admins.from || 0}</span> đến{' '}
                                    <span className="font-medium">{admins.to || 0}</span> trong tổng số{' '}
                                    <span className="font-medium">{admins.total}</span> kết quả
                                </div>
                                <div className="flex items-center gap-2">
                                    <Link
                                        href={admins.current_page > 1 ? `/admin/admins?page=${admins.current_page - 1}` : '#'}
                                        className={`inline-flex items-center gap-1 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 ${
                                            admins.current_page <= 1 ? 'opacity-50 cursor-not-allowed' : ''
                                        }`}
                                        preserveState
                                        preserveScroll
                                    >
                                        <ChevronLeft className="h-4 w-4" />
                                        Trước
                                    </Link>

                                    {[...Array(Math.min(5, admins.last_page))].map((_, i) => {
                                        const page = i + 1;
                                        return (
                                            <Link
                                                key={page}
                                                href={`/admin/admins?page=${page}`}
                                                className={`inline-flex items-center justify-center rounded-lg px-3 py-2 text-sm font-medium ${
                                                    page === admins.current_page
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
                                            admins.current_page < admins.last_page
                                                ? `/admin/admins?page=${admins.current_page + 1}`
                                                : '#'
                                        }
                                        className={`inline-flex items-center gap-1 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 ${
                                            admins.current_page >= admins.last_page ? 'opacity-50 cursor-not-allowed' : ''
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
