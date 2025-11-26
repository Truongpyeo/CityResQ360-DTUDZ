import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AdminLayout from '../layouts/AdminLayout';
import {
    FileText,
    Search,
    Filter,
    Download,
    RefreshCw,
    Eye,
    MapPin,
    Clock,
    AlertCircle,
    CheckCircle2,
    XCircle,
    MoreVertical,
    ChevronLeft,
    ChevronRight,
} from 'lucide-react';
import { showSuccess, showError, showDeleteConfirm } from '@/utils/notifications';

interface Report {
    id: number;
    tieu_de: string;
    danh_muc: string;
    uu_tien: string;
    trang_thai: number;
    trang_thai_text: string;
    nguoi_dung: string;
    dia_chi: string;
    luot_ung_ho: number;
    luot_xem: number;
    created_at: string;
}

interface Category {
    id: number;
    ten_danh_muc: string;
    ma_danh_muc: string;
}

interface Priority {
    id: number;
    ten_muc: string;
    ma_muc: string;
}

interface Agency {
    id: number;
    ten_co_quan: string;
}

interface Stats {
    total: number;
    pending: number;
    in_progress: number;
    resolved: number;
}

interface Props {
    reports: {
        data: Report[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number;
        to: number;
    };
    stats: Stats;
    categories: Category[];
    priorities: Priority[];
    agencies: Agency[];
    filters: {
        trang_thai?: string;
        danh_muc_id?: string;
        uu_tien_id?: string;
        co_quan_phu_trach_id?: string;
        search?: string;
    };
}

export default function ReportsIndex({ reports, stats, categories, priorities, agencies, filters }: Props) {
    const [searchTerm, setSearchTerm] = useState(filters.search || '');
    const [statusFilter, setStatusFilter] = useState(filters.trang_thai || '');
    const [categoryFilter, setCategoryFilter] = useState(filters.danh_muc_id || '');
    const [priorityFilter, setPriorityFilter] = useState(filters.uu_tien_id || '');

    const handleFilter = () => {
        router.get('/admin/reports', {
            search: searchTerm,
            trang_thai: statusFilter,
            danh_muc_id: categoryFilter,
            uu_tien_id: priorityFilter,
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

    const handleDelete = async (reportId: number, title: string) => {
        const confirmed = await showDeleteConfirm(`phản ánh "${title}"`);

        if (confirmed) {
            router.delete(`/admin/reports/${reportId}`, {
                onSuccess: () => {
                    showSuccess('Xóa phản ánh thành công!');
                },
                onError: () => {
                    showError('Không thể xóa phản ánh!');
                },
            });
        }
    };

    const getStatusBadge = (status: number) => {
        const statusMap = {
            0: { text: 'Chờ xử lý', style: 'bg-yellow-100 text-yellow-800 border-yellow-200', icon: <Clock className="w-3.5 h-3.5" /> },
            1: { text: 'Đã xác minh', style: 'bg-blue-100 text-blue-800 border-blue-200', icon: <CheckCircle2 className="w-3.5 h-3.5" /> },
            2: { text: 'Đang xử lý', style: 'bg-orange-100 text-orange-800 border-orange-200', icon: <AlertCircle className="w-3.5 h-3.5" /> },
            3: { text: 'Đã giải quyết', style: 'bg-green-100 text-green-800 border-green-200', icon: <CheckCircle2 className="w-3.5 h-3.5" /> },
            4: { text: 'Từ chối', style: 'bg-red-100 text-red-800 border-red-200', icon: <XCircle className="w-3.5 h-3.5" /> },
        };

        const statusInfo = statusMap[status as keyof typeof statusMap] || statusMap[0];

        return (
            <span className={`inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium border ${statusInfo.style}`}>
                {statusInfo.icon}
                {statusInfo.text}
            </span>
        );
    };

    const getPriorityBadge = (priority: string) => {
        const styles = {
            'Thấp': 'bg-gray-100 text-gray-700',
            'Trung bình': 'bg-blue-100 text-blue-700',
            'Cao': 'bg-orange-100 text-orange-700',
            'Khẩn cấp': 'bg-red-100 text-red-700',
        };

        return (
            <span className={`inline-flex items-center px-2.5 py-1 rounded text-xs font-semibold ${styles[priority as keyof typeof styles] || 'bg-gray-100 text-gray-700'}`}>
                {priority}
            </span>
        );
    };

    const getCategoryIcon = (category: string) => {
        return (
            <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-medium bg-gray-100 text-gray-700">
                {category}
            </span>
        );
    };

    return (
        <AdminLayout>
            <Head title="Quản lý phản ánh - Admin" />

            <div className="space-y-6">
                {/* Header with Actions */}
                <div className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h1 className="text-3xl font-bold text-gray-900">Quản lý phản ánh</h1>
                        <p className="mt-1 text-sm text-gray-500">
                            Quản lý và xử lý các phản ánh từ người dân
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
                                router.get('/admin/reports/export', {
                                    search: searchTerm,
                                    trang_thai: statusFilter,
                                    danh_muc_id: categoryFilter,
                                    uu_tien_id: priorityFilter,
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
                <div className="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-4">
                    <div className="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                        <div className="flex items-center justify-between">
                            <div>
                                <p className="text-sm font-medium text-gray-600">Tổng phản ánh</p>
                                <p className="mt-1 text-2xl font-bold text-gray-900">{stats.total}</p>
                                <p className="mt-1 text-xs text-gray-500">Tất cả các trạng thái</p>
                            </div>
                            <div className="rounded-full bg-blue-100 p-3">
                                <FileText className="h-6 w-6 text-blue-600" />
                            </div>
                        </div>
                    </div>
                    <div className="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                        <div className="flex items-center justify-between">
                            <div>
                                <p className="text-sm font-medium text-gray-600">Chờ xử lý</p>
                                <p className="mt-1 text-2xl font-bold text-yellow-600">{stats.pending}</p>
                                <p className="mt-1 text-xs text-gray-500">Cần xử lý ngay</p>
                            </div>
                            <div className="rounded-full bg-yellow-100 p-3">
                                <Clock className="h-6 w-6 text-yellow-600" />
                            </div>
                        </div>
                    </div>
                    <div className="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                        <div className="flex items-center justify-between">
                            <div>
                                <p className="text-sm font-medium text-gray-600">Đang xử lý</p>
                                <p className="mt-1 text-2xl font-bold text-orange-600">{stats.in_progress}</p>
                                <p className="mt-1 text-xs text-gray-500">Đang theo dõi</p>
                            </div>
                            <div className="rounded-full bg-orange-100 p-3">
                                <AlertCircle className="h-6 w-6 text-orange-600" />
                            </div>
                        </div>
                    </div>
                    <div className="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                        <div className="flex items-center justify-between">
                            <div>
                                <p className="text-sm font-medium text-gray-600">Đã giải quyết</p>
                                <p className="mt-1 text-2xl font-bold text-green-600">{stats.resolved}</p>
                                <p className="mt-1 text-xs text-green-600">{stats.total > 0 ? Math.round((stats.resolved / stats.total) * 100) : 0}% tỷ lệ</p>
                            </div>
                            <div className="rounded-full bg-green-100 p-3">
                                <CheckCircle2 className="h-6 w-6 text-green-600" />
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
                                    placeholder="Tìm kiếm theo tiêu đề, địa chỉ..."
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
                                <option value="0">Chờ xử lý</option>
                                <option value="1">Đã xác minh</option>
                                <option value="2">Đang xử lý</option>
                                <option value="3">Đã giải quyết</option>
                                <option value="4">Từ chối</option>
                            </select>
                        </div>
                        <div className="flex-1 min-w-[150px]">
                            <label className="block text-sm font-medium text-gray-700 mb-2">Danh mục</label>
                            <select
                                value={categoryFilter}
                                onChange={(e) => setCategoryFilter(e.target.value)}
                                className="block w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-20"
                            >
                                <option value="">Tất cả</option>
                                {categories.map((cat) => (
                                    <option key={cat.id} value={cat.id}>{cat.ten_danh_muc}</option>
                                ))}
                            </select>
                        </div>
                        <div className="flex-1 min-w-[150px]">
                            <label className="block text-sm font-medium text-gray-700 mb-2">Ưu tiên</label>
                            <select
                                value={priorityFilter}
                                onChange={(e) => setPriorityFilter(e.target.value)}
                                className="block w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-20"
                            >
                                <option value="">Tất cả</option>
                                {priorities.map((priority) => (
                                    <option key={priority.id} value={priority.id}>{priority.ten_muc}</option>
                                ))}
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

                {/* Reports Table */}
                <div className="rounded-lg border border-gray-200 bg-white shadow-sm overflow-hidden">
                    <div className="border-b border-gray-200 bg-gray-50 px-6 py-4">
                        <div className="flex items-center justify-between">
                            <h3 className="text-lg font-semibold text-gray-900">Danh sách phản ánh</h3>
                            <span className="text-sm text-gray-500">{reports.total} phản ánh</span>
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
                                        Thông tin phản ánh
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                                        Người gửi
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                                        Danh mục
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                                        Ưu tiên
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                                        Trạng thái
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                                        Tương tác
                                    </th>
                                    <th className="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600">
                                        Thao tác
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-200 bg-white">
                                {reports.data.map((report, index) => (
                                    <tr key={report.id} className="hover:bg-gray-50 transition-colors">
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            <span className="text-sm font-medium text-gray-900">{reports.from + index}</span>
                                        </td>
                                        <td className="px-6 py-4">
                                            <div className="max-w-md">
                                                <Link
                                                    href={`/admin/reports/${report.id}`}
                                                    className="text-sm font-medium text-gray-900 hover:text-blue-600 line-clamp-1"
                                                >
                                                    {report.tieu_de}
                                                </Link>
                                                <div className="mt-1 flex items-center gap-1 text-xs text-gray-500">
                                                    <MapPin className="h-3.5 w-3.5 flex-shrink-0" />
                                                    <span className="line-clamp-1">{report.dia_chi}</span>
                                                </div>
                                                <div className="mt-1 flex items-center gap-1 text-xs text-gray-400">
                                                    <Clock className="h-3.5 w-3.5 flex-shrink-0" />
                                                    <span>{report.created_at}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            <div className="flex items-center gap-2">
                                                <div className="h-8 w-8 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white text-xs font-semibold">
                                                    {report.nguoi_dung.charAt(0)}
                                                </div>
                                                <span className="text-sm text-gray-900">{report.nguoi_dung}</span>
                                            </div>
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            {getCategoryIcon(report.danh_muc)}
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            {getPriorityBadge(report.uu_tien)}
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            {getStatusBadge(report.trang_thai)}
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            <div className="flex items-center gap-3 text-xs text-gray-500">
                                                <div className="flex items-center gap-1">
                                                    <Eye className="h-4 w-4 flex-shrink-0" />
                                                    <span>{report.luot_xem}</span>
                                                </div>
                                                <div className="flex items-center gap-1 text-green-600">
                                                    <svg className="h-4 w-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M2 10.5a1.5 1.5 0 113 0v6a1.5 1.5 0 01-3 0v-6zM6 10.333v5.43a2 2 0 001.106 1.79l.05.025A4 4 0 008.943 18h5.416a2 2 0 001.962-1.608l1.2-6A2 2 0 0015.56 8H12V4a2 2 0 00-2-2 1 1 0 00-1 1v.667a4 4 0 01-.8 2.4L6.8 7.933a4 4 0 00-.8 2.4z" />
                                                    </svg>
                                                    <span>{report.luot_ung_ho}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap text-right">
                                            <div className="flex items-center justify-end gap-2">
                                                <Link
                                                    href={`/admin/reports/${report.id}`}
                                                    className="inline-flex items-center gap-1.5 rounded-lg bg-blue-50 px-3 py-1.5 text-xs font-medium text-blue-700 hover:bg-blue-100 transition-colors"
                                                >
                                                    <Eye className="h-4 w-4" />
                                                    Chi tiết
                                                </Link>
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
                                Hiển thị <span className="font-medium">{reports.from || 0}</span> đến <span className="font-medium">{reports.to || 0}</span> trong tổng số{' '}
                                <span className="font-medium">{reports.total}</span> kết quả
                            </div>
                            <div className="flex items-center gap-2">
                                <Link
                                    href={reports.current_page > 1 ? `/admin/reports?page=${reports.current_page - 1}` : '#'}
                                    className={`inline-flex items-center gap-1 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 ${reports.current_page <= 1 ? 'opacity-50 cursor-not-allowed' : ''}`}
                                    preserveState
                                    preserveScroll
                                >
                                    <ChevronLeft className="h-4 w-4" />
                                    Trước
                                </Link>

                                {[...Array(Math.min(5, reports.last_page))].map((_, i) => {
                                    const page = i + 1;
                                    return (
                                        <Link
                                            key={page}
                                            href={`/admin/reports?page=${page}`}
                                            className={`inline-flex items-center justify-center rounded-lg px-3 py-2 text-sm font-medium ${
                                                page === reports.current_page
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

                                {reports.last_page > 5 && (
                                    <>
                                        <span className="px-2 text-gray-500">...</span>
                                        <Link
                                            href={`/admin/reports?page=${reports.last_page}`}
                                            className="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                                            preserveState
                                            preserveScroll
                                        >
                                            {reports.last_page}
                                        </Link>
                                    </>
                                )}

                                <Link
                                    href={reports.current_page < reports.last_page ? `/admin/reports?page=${reports.current_page + 1}` : '#'}
                                    className={`inline-flex items-center gap-1 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 ${reports.current_page >= reports.last_page ? 'opacity-50 cursor-not-allowed' : ''}`}
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
