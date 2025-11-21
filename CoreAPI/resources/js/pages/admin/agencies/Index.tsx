import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AdminLayout from '../layouts/AdminLayout';
import {
    Building2,
    Search,
    Plus,
    Download,
    RefreshCw,
    Eye,
    Edit,
    Trash2,
    MapPin,
    Mail,
    Phone,
    ChevronLeft,
    ChevronRight,
    CheckCircle2,
    XCircle,
} from 'lucide-react';
import { showSuccess, showError, showDeleteConfirm } from '@/utils/notifications';

interface Agency {
    id: number;
    ten_co_quan: string;
    email_lien_he: string;
    so_dien_thoai: string;
    dia_chi: string;
    cap_do: number;
    cap_do_text: string;
    trang_thai: number;
    trang_thai_text: string;
    so_phan_anh: number;
    created_at: string;
}

interface Props {
    agencies: {
        data: Agency[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number;
        to: number;
    };
    filters: {
        trang_thai?: string;
        cap_do?: string;
        search?: string;
    };
}

export default function AgenciesIndex({ agencies, filters }: Props) {
    const [searchTerm, setSearchTerm] = useState(filters.search || '');
    const [statusFilter, setStatusFilter] = useState(filters.trang_thai || '');
    const [levelFilter, setLevelFilter] = useState(filters.cap_do || '');

    const handleFilter = () => {
        router.get('/admin/agencies', {
            search: searchTerm,
            trang_thai: statusFilter,
            cap_do: levelFilter,
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

    const handleDelete = async (agencyId: number, name: string) => {
        const confirmed = await showDeleteConfirm(`cơ quan "${name}"`);

        if (confirmed) {
            router.delete(`/admin/agencies/${agencyId}`, {
                onSuccess: () => {
                    showSuccess('Xóa cơ quan thành công!');
                },
                onError: (errors) => {
                    const firstError = Object.values(errors)[0] as string;
                    showError(firstError || 'Không thể xóa cơ quan!');
                },
            });
        }
    };

    const getLevelBadge = (level: number) => {
        const levelMap = {
            0: { text: 'Phường/Xã', style: 'bg-green-100 text-green-800' },
            1: { text: 'Quận/Huyện', style: 'bg-blue-100 text-blue-800' },
            2: { text: 'Thành phố', style: 'bg-purple-100 text-purple-800' },
        };

        const levelInfo = levelMap[level as keyof typeof levelMap] || levelMap[0];

        return (
            <span className={`inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium ${levelInfo.style}`}>
                {levelInfo.text}
            </span>
        );
    };

    const totalActive = agencies.data.filter(a => a.trang_thai === 1).length;
    const totalInactive = agencies.data.filter(a => a.trang_thai === 0).length;

    return (
        <AdminLayout>
            <Head title="Quản lý cơ quan xử lý - Admin" />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h1 className="text-3xl font-bold text-gray-900">Quản lý cơ quan xử lý</h1>
                        <p className="mt-1 text-sm text-gray-500">
                            Quản lý các cơ quan phụ trách xử lý phản ánh từ người dân
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
                                router.get('/admin/agencies/export', {
                                    search: searchTerm,
                                });
                            }}
                            className="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                        >
                            <Download className="h-4 w-4" />
                            Xuất Excel
                        </button>
                        <Link
                            href="/admin/agencies/create"
                            className="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
                        >
                            <Plus className="h-4 w-4" />
                            Thêm cơ quan
                        </Link>
                    </div>
                </div>

                {/* Stats Cards */}
                <div className="grid gap-4 grid-cols-1 sm:grid-cols-3">
                    <div className="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                        <div className="flex items-center justify-between">
                            <div>
                                <p className="text-sm font-medium text-gray-600">Tổng cơ quan</p>
                                <p className="mt-1 text-2xl font-bold text-gray-900">{agencies.total}</p>
                            </div>
                            <div className="rounded-full bg-blue-100 p-3">
                                <Building2 className="h-6 w-6 text-blue-600" />
                            </div>
                        </div>
                    </div>
                    <div className="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                        <div className="flex items-center justify-between">
                            <div>
                                <p className="text-sm font-medium text-gray-600">Đang hoạt động</p>
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
                                <p className="text-sm font-medium text-gray-600">Không hoạt động</p>
                                <p className="mt-1 text-2xl font-bold text-red-600">{totalInactive}</p>
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
                                    placeholder="Tìm kiếm theo tên, email, địa chỉ..."
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
                                <option value="0">Không hoạt động</option>
                            </select>
                        </div>
                        <div className="flex-1 min-w-[150px]">
                            <label className="block text-sm font-medium text-gray-700 mb-2">Cấp độ</label>
                            <select
                                value={levelFilter}
                                onChange={(e) => setLevelFilter(e.target.value)}
                                className="block w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-20"
                            >
                                <option value="">Tất cả</option>
                                <option value="0">Phường/Xã</option>
                                <option value="1">Quận/Huyện</option>
                                <option value="2">Thành phố</option>
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

                {/* Agencies Table */}
                <div className="rounded-lg border border-gray-200 bg-white shadow-sm overflow-hidden">
                    <div className="border-b border-gray-200 bg-gray-50 px-6 py-4">
                        <div className="flex items-center justify-between">
                            <h3 className="text-lg font-semibold text-gray-900">Danh sách cơ quan</h3>
                            <span className="text-sm text-gray-500">{agencies.total} cơ quan</span>
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
                                        Thông tin cơ quan
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                                        Liên hệ
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                                        Cấp độ
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                                        Phản ánh
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
                                {agencies.data.map((agency, index) => (
                                    <tr key={agency.id} className="hover:bg-gray-50 transition-colors">
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            <span className="text-sm font-medium text-gray-900">{agencies.from + index}</span>
                                        </td>
                                        <td className="px-6 py-4">
                                            <div className="max-w-md">
                                                <Link
                                                    href={`/admin/agencies/${agency.id}`}
                                                    className="text-sm font-medium text-gray-900 hover:text-blue-600 line-clamp-1"
                                                >
                                                    {agency.ten_co_quan}
                                                </Link>
                                                {agency.dia_chi && (
                                                    <div className="mt-1 flex items-center gap-1 text-xs text-gray-500">
                                                        <MapPin className="h-3.5 w-3.5 flex-shrink-0" />
                                                        <span className="line-clamp-1">{agency.dia_chi}</span>
                                                    </div>
                                                )}
                                            </div>
                                        </td>
                                        <td className="px-6 py-4">
                                            <div className="space-y-1 text-xs">
                                                <div className="flex items-center gap-1.5 text-gray-700">
                                                    <Mail className="h-3.5 w-3.5 text-gray-400" />
                                                    <span>{agency.email_lien_he}</span>
                                                </div>
                                                {agency.so_dien_thoai && (
                                                    <div className="flex items-center gap-1.5 text-gray-700">
                                                        <Phone className="h-3.5 w-3.5 text-gray-400" />
                                                        <span>{agency.so_dien_thoai}</span>
                                                    </div>
                                                )}
                                            </div>
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            {getLevelBadge(agency.cap_do)}
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            <span className="inline-flex items-center gap-1.5 text-sm font-medium text-gray-900">
                                                {agency.so_phan_anh}
                                            </span>
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            {agency.trang_thai === 1 ? (
                                                <span className="inline-flex items-center gap-1.5 rounded-full bg-green-100 px-2.5 py-1 text-xs font-medium text-green-800">
                                                    <CheckCircle2 className="h-3.5 w-3.5" />
                                                    Hoạt động
                                                </span>
                                            ) : (
                                                <span className="inline-flex items-center gap-1.5 rounded-full bg-red-100 px-2.5 py-1 text-xs font-medium text-red-800">
                                                    <XCircle className="h-3.5 w-3.5" />
                                                    Không hoạt động
                                                </span>
                                            )}
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap text-right">
                                            <div className="flex items-center justify-end gap-2">
                                                <Link
                                                    href={`/admin/agencies/${agency.id}`}
                                                    className="inline-flex items-center gap-1.5 rounded-lg bg-blue-50 px-3 py-1.5 text-xs font-medium text-blue-700 hover:bg-blue-100 transition-colors"
                                                >
                                                    <Eye className="h-4 w-4" />
                                                    Chi tiết
                                                </Link>
                                                <Link
                                                    href={`/admin/agencies/${agency.id}/edit`}
                                                    className="inline-flex items-center gap-1.5 rounded-lg bg-gray-50 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-100 transition-colors"
                                                >
                                                    <Edit className="h-4 w-4" />
                                                    Sửa
                                                </Link>
                                                <button
                                                    onClick={() => handleDelete(agency.id, agency.ten_co_quan)}
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
                    {agencies.last_page > 1 && (
                        <div className="border-t border-gray-200 bg-gray-50 px-6 py-4">
                            <div className="flex items-center justify-between">
                                <div className="text-sm text-gray-500">
                                    Hiển thị <span className="font-medium">{agencies.from || 0}</span> đến{' '}
                                    <span className="font-medium">{agencies.to || 0}</span> trong tổng số{' '}
                                    <span className="font-medium">{agencies.total}</span> kết quả
                                </div>
                                <div className="flex items-center gap-2">
                                    <Link
                                        href={agencies.current_page > 1 ? `/admin/agencies?page=${agencies.current_page - 1}` : '#'}
                                        className={`inline-flex items-center gap-1 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 ${
                                            agencies.current_page <= 1 ? 'opacity-50 cursor-not-allowed' : ''
                                        }`}
                                        preserveState
                                        preserveScroll
                                    >
                                        <ChevronLeft className="h-4 w-4" />
                                        Trước
                                    </Link>

                                    {[...Array(Math.min(5, agencies.last_page))].map((_, i) => {
                                        const page = i + 1;
                                        return (
                                            <Link
                                                key={page}
                                                href={`/admin/agencies?page=${page}`}
                                                className={`inline-flex items-center justify-center rounded-lg px-3 py-2 text-sm font-medium ${
                                                    page === agencies.current_page
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
                                            agencies.current_page < agencies.last_page
                                                ? `/admin/agencies?page=${agencies.current_page + 1}`
                                                : '#'
                                        }
                                        className={`inline-flex items-center gap-1 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 ${
                                            agencies.current_page >= agencies.last_page ? 'opacity-50 cursor-not-allowed' : ''
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

