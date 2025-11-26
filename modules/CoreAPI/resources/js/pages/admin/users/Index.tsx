import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AdminLayout from '../layouts/AdminLayout';
import {
    Users,
    Search,
    Filter,
    Download,
    RefreshCw,
    Eye,
    UserCheck,
    UserX,
    Shield,
    Award,
    CheckCircle2,
    XCircle,
    Clock,
    ChevronLeft,
    ChevronRight,
    MoreVertical,
} from 'lucide-react';

interface User {
    id: number;
    ho_ten: string;
    email: string;
    so_dien_thoai: string;
    vai_tro: number;
    vai_tro_text: string;
    trang_thai: number;
    trang_thai_text: string;
    diem_thanh_pho: number;
    xac_thuc_cong_dan: boolean;
    diem_uy_tin: number;
    tong_so_phan_anh: number;
    ty_le_chinh_xac: number;
    cap_huy_hieu: string;
    created_at: string;
}

interface Props {
    users: {
        data: User[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number;
        to: number;
    };
    filters: {
        trang_thai?: string;
        xac_thuc_cong_dan?: string;
        search?: string;
    };
}

export default function UsersIndex({ users, filters }: Props) {
    const [searchTerm, setSearchTerm] = useState(filters.search || '');
    const [statusFilter, setStatusFilter] = useState(filters.trang_thai || '');
    const [verifiedFilter, setVerifiedFilter] = useState(filters.xac_thuc_cong_dan || '');

    const handleFilter = () => {
        router.get('/admin/users', {
            search: searchTerm,
            trang_thai: statusFilter,
            xac_thuc_cong_dan: verifiedFilter,
        }, {
            preserveState: true,
        });
    };

    const handleRefresh = () => {
        router.reload();
    };

    const getBadgeBadge = (badge: string) => {
        const styles = {
            'bronze': 'bg-orange-100 text-orange-700',
            'silver': 'bg-gray-100 text-gray-700',
            'gold': 'bg-yellow-100 text-yellow-700',
            'platinum': 'bg-blue-100 text-blue-700',
        };

        const names = {
            'bronze': 'Đồng',
            'silver': 'Bạc',
            'gold': 'Vàng',
            'platinum': 'Bạch kim',
        };

        return (
            <span className={`inline-flex items-center gap-1 px-2.5 py-1 rounded text-xs font-semibold ${styles[badge as keyof typeof styles] || 'bg-gray-100 text-gray-700'}`}>
                <Award className="w-3.5 h-3.5" />
                {names[badge as keyof typeof names] || badge}
            </span>
        );
    };

    const getStatusBadge = (status: number) => {
        if (status === 1) {
            return (
                <span className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium border bg-green-100 text-green-800 border-green-200">
                    <CheckCircle2 className="w-3.5 h-3.5" />
                    Hoạt động
                </span>
            );
        }

        return (
            <span className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium border bg-red-100 text-red-800 border-red-200">
                <XCircle className="w-3.5 h-3.5" />
                Đã khóa
            </span>
        );
    };

    const stats = {
        total: users.total,
        active: users.data.filter(u => u.trang_thai === 1).length,
        verified: users.data.filter(u => u.xac_thuc_cong_dan).length,
    };

    return (
        <AdminLayout>
            <Head title="Quản lý người dùng - Admin" />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h1 className="text-3xl font-bold text-gray-900">Quản lý người dùng</h1>
                        <p className="mt-1 text-sm text-gray-500">
                            Quản lý thông tin và hoạt động của người dùng
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
                        <button
                            onClick={() => {
                                router.get('/admin/users/export', {
                                    search: searchTerm,
                                    trang_thai: statusFilter,
                                    xac_thuc_danh_tinh: verifiedFilter,
                                });
                            }}
                            className="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                        >
                            <Download className="h-4 w-4" />
                            Xuất Excel
                        </button>
                    </div>
                </div>

                {/* Stats Cards */}
                <div className="grid gap-4 grid-cols-1 sm:grid-cols-3">
                    <div className="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                        <div className="flex items-center justify-between">
                            <div>
                                <p className="text-sm font-medium text-gray-600">Tổng người dùng</p>
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
                                <p className="text-sm font-medium text-gray-600">Đang hoạt động</p>
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
                                <p className="text-sm font-medium text-gray-600">Đã xác thực</p>
                                <p className="mt-1 text-2xl font-bold text-blue-600">{stats.verified}</p>
                            </div>
                            <div className="rounded-full bg-blue-100 p-3">
                                <Shield className="h-6 w-6 text-blue-600" />
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
                                <input
                                    type="text"
                                    value={searchTerm}
                                    onChange={(e) => setSearchTerm(e.target.value)}
                                    placeholder="Tìm kiếm theo tên, email, số điện thoại..."
                                    className="block w-full rounded-lg border border-gray-300 pl-10 pr-4 py-3 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-20"
                                />
                                <Search className="absolute left-3 top-3.5 h-4 w-4 text-gray-400" />
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
                        <div className="flex-1 min-w-[150px]">
                            <label className="block text-sm font-medium text-gray-700 mb-2">Xác thực</label>
                            <select
                                value={verifiedFilter}
                                onChange={(e) => setVerifiedFilter(e.target.value)}
                                className="block w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-20"
                            >
                                <option value="">Tất cả</option>
                                <option value="1">Đã xác thực</option>
                                <option value="0">Chưa xác thực</option>
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

                {/* Users Table */}
                <div className="rounded-lg border border-gray-200 bg-white shadow-sm overflow-hidden">
                    <div className="border-b border-gray-200 bg-gray-50 px-6 py-4">
                        <div className="flex items-center justify-between">
                            <h3 className="text-lg font-semibold text-gray-900">Danh sách người dùng</h3>
                            <span className="text-sm text-gray-500">{users.total} người dùng</span>
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
                                        Thông tin người dùng
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                                        Vai trò
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                                        Xác thực
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                                        CityPoints
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                                        Uy tín
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                                        Huy hiệu
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
                                {users.data.map((user, index) => (
                                    <tr key={user.id} className="hover:bg-gray-50 transition-colors">
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            <span className="text-sm font-medium text-gray-900">{users.from + index}</span>
                                        </td>
                                        <td className="px-6 py-4">
                                            <div className="flex items-center gap-3">
                                                <div className="h-10 w-10 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white text-sm font-semibold">
                                                    {user.ho_ten.charAt(0)}
                                                </div>
                                                <div>
                                                    <Link
                                                        href={`/admin/users/${user.id}`}
                                                        className="text-sm font-medium text-gray-900 hover:text-blue-600"
                                                    >
                                                        {user.ho_ten}
                                                    </Link>
                                                    <div className="text-xs text-gray-500">{user.email}</div>
                                                    <div className="text-xs text-gray-400">{user.so_dien_thoai}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            <span className="inline-flex items-center px-2.5 py-1 rounded text-xs font-medium bg-gray-100 text-gray-700">
                                                {user.vai_tro_text}
                                            </span>
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            {user.xac_thuc_cong_dan ? (
                                                <span className="inline-flex items-center gap-1 text-xs text-green-600">
                                                    <CheckCircle2 className="w-4 h-4" />
                                                    Đã xác thực
                                                </span>
                                            ) : (
                                                <span className="inline-flex items-center gap-1 text-xs text-gray-500">
                                                    <Clock className="w-4 h-4" />
                                                    Chưa xác thực
                                                </span>
                                            )}
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            <div className="text-sm font-semibold text-blue-600">{user.diem_thanh_pho}</div>
                                            <div className="text-xs text-gray-500">{user.tong_so_phan_anh} phản ánh</div>
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            <div className="text-sm font-semibold text-gray-900">{user.diem_uy_tin}/100</div>
                                            <div className="text-xs text-gray-500">{user.ty_le_chinh_xac.toFixed(1)}% chính xác</div>
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            {getBadgeBadge(user.cap_huy_hieu)}
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            {getStatusBadge(user.trang_thai)}
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap text-right">
                                            <div className="flex items-center justify-end gap-2">
                                                <Link
                                                    href={`/admin/users/${user.id}`}
                                                    className="inline-flex items-center gap-1.5 rounded-lg bg-blue-50 px-3 py-1.5 text-xs font-medium text-blue-700 hover:bg-blue-100 transition-colors"
                                                >
                                                    <Eye className="h-4 w-4" />
                                                    Chi tiết
                                                </Link>
                                                <button className="inline-flex items-center justify-center rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors">
                                                    <MoreVertical className="h-4 w-4" />
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>

                    {/* Pagination */}
                    <div className="border-t border-gray-200 bg-gray-50 px-6 py-4">
                        <div className="flex items-center justify-between">
                            <div className="text-sm text-gray-500">
                                Hiển thị <span className="font-medium">{users.from || 0}</span> đến <span className="font-medium">{users.to || 0}</span> trong tổng số{' '}
                                <span className="font-medium">{users.total}</span> kết quả
                            </div>
                            <div className="flex items-center gap-2">
                                <Link
                                    href={users.current_page > 1 ? `/admin/users?page=${users.current_page - 1}` : '#'}
                                    className={`inline-flex items-center gap-1 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 ${users.current_page <= 1 ? 'opacity-50 cursor-not-allowed' : ''}`}
                                    preserveState
                                    preserveScroll
                                >
                                    <ChevronLeft className="h-4 w-4" />
                                    Trước
                                </Link>

                                {[...Array(Math.min(5, users.last_page))].map((_, i) => {
                                    const page = i + 1;
                                    return (
                                        <Link
                                            key={page}
                                            href={`/admin/users?page=${page}`}
                                            className={`inline-flex items-center justify-center rounded-lg px-3 py-2 text-sm font-medium ${
                                                page === users.current_page
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

                                {users.last_page > 5 && (
                                    <>
                                        <span className="px-2 text-gray-500">...</span>
                                        <Link
                                            href={`/admin/users?page=${users.last_page}`}
                                            className="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                                            preserveState
                                            preserveScroll
                                        >
                                            {users.last_page}
                                        </Link>
                                    </>
                                )}

                                <Link
                                    href={users.current_page < users.last_page ? `/admin/users?page=${users.current_page + 1}` : '#'}
                                    className={`inline-flex items-center gap-1 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 ${users.current_page >= users.last_page ? 'opacity-50 cursor-not-allowed' : ''}`}
                                    preserveState
                                    preserveScroll
                                >
                                    Sau
                                    <ChevronRight className="h-4 w-4" />
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
