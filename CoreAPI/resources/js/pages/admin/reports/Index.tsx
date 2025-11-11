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

interface Report {
    id: number;
    tieu_de: string;
    danh_muc: string;
    uu_tien: string;
    trang_thai: string;
    nguoi_dung: string;
    dia_chi: string;
    luot_ung_ho: number;
    luot_xem: number;
    created_at: string;
}

export default function ReportsIndex() {
    const [searchTerm, setSearchTerm] = useState('');
    const [statusFilter, setStatusFilter] = useState('');
    const [categoryFilter, setCategoryFilter] = useState('');
    const [priorityFilter, setPriorityFilter] = useState('');

    // Mock data - sẽ thay bằng props từ backend
    const mockReports: Report[] = [
        {
            id: 1,
            tieu_de: 'Đường Nguyễn Huệ bị hư hỏng nghiêm trọng',
            danh_muc: 'Giao thông',
            uu_tien: 'Cao',
            trang_thai: 'Chờ xử lý',
            nguoi_dung: 'Nguyễn Văn A',
            dia_chi: 'Đường Nguyễn Huệ, Quận 1, TP.HCM',
            luot_ung_ho: 45,
            luot_xem: 230,
            created_at: '10/11/2025 14:30',
        },
        {
            id: 2,
            tieu_de: 'Rác thải tràn lan trên vỉa hè',
            danh_muc: 'Rác thải',
            uu_tien: 'Trung bình',
            trang_thai: 'Đang xử lý',
            nguoi_dung: 'Trần Thị B',
            dia_chi: 'Đường Lê Lợi, Quận 3, TP.HCM',
            luot_ung_ho: 32,
            luot_xem: 156,
            created_at: '10/11/2025 10:15',
        },
        {
            id: 3,
            tieu_de: 'Cây xanh bị đổ chắn ngang đường',
            danh_muc: 'Môi trường',
            uu_tien: 'Khẩn cấp',
            trang_thai: 'Đã xác minh',
            nguoi_dung: 'Lê Văn C',
            dia_chi: 'Đường Điện Biên Phủ, Bình Thạnh, TP.HCM',
            luot_ung_ho: 78,
            luot_xem: 345,
            created_at: '09/11/2025 16:45',
        },
        {
            id: 4,
            tieu_de: 'Ngập nước kéo dài sau mưa',
            danh_muc: 'Ngập lụt',
            uu_tien: 'Cao',
            trang_thai: 'Đã giải quyết',
            nguoi_dung: 'Phạm Thị D',
            dia_chi: 'Đường Xô Viết Nghệ Tĩnh, Bình Thạnh, TP.HCM',
            luot_ung_ho: 56,
            luot_xem: 289,
            created_at: '09/11/2025 08:20',
        },
        {
            id: 5,
            tieu_de: 'Báo cháy nhà dân - cần hỗ trợ khẩn',
            danh_muc: 'Cháy nổ',
            uu_tien: 'Khẩn cấp',
            trang_thai: 'Từ chối',
            nguoi_dung: 'Hoàng Văn E',
            dia_chi: 'Đường Cách Mạng Tháng 8, Quận 10, TP.HCM',
            luot_ung_ho: 12,
            luot_xem: 98,
            created_at: '08/11/2025 22:10',
        },
    ];

    const getStatusBadge = (status: string) => {
        const styles = {
            'Chờ xử lý': 'bg-yellow-100 text-yellow-800 border-yellow-200',
            'Đã xác minh': 'bg-blue-100 text-blue-800 border-blue-200',
            'Đang xử lý': 'bg-orange-100 text-orange-800 border-orange-200',
            'Đã giải quyết': 'bg-green-100 text-green-800 border-green-200',
            'Từ chối': 'bg-red-100 text-red-800 border-red-200',
        };

        const icons = {
            'Chờ xử lý': <Clock className="w-3.5 h-3.5" />,
            'Đã xác minh': <CheckCircle2 className="w-3.5 h-3.5" />,
            'Đang xử lý': <AlertCircle className="w-3.5 h-3.5" />,
            'Đã giải quyết': <CheckCircle2 className="w-3.5 h-3.5" />,
            'Từ chối': <XCircle className="w-3.5 h-3.5" />,
        };

        return (
            <span className={`inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium border ${styles[status as keyof typeof styles] || 'bg-gray-100 text-gray-800 border-gray-200'}`}>
                {icons[status as keyof typeof icons]}
                {status}
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
        // Bạn có thể thêm icon cho từng category
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
                        <button className="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                            <RefreshCw className="h-4 w-4" />
                            Làm mới
                        </button>
                        <button className="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
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
                                <p className="mt-1 text-2xl font-bold text-gray-900">156</p>
                                <p className="mt-1 text-xs text-green-600">↑ 12% so với tháng trước</p>
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
                                <p className="mt-1 text-2xl font-bold text-yellow-600">23</p>
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
                                <p className="mt-1 text-2xl font-bold text-orange-600">48</p>
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
                                <p className="mt-1 text-2xl font-bold text-green-600">85</p>
                                <p className="mt-1 text-xs text-green-600">↑ 8% hiệu suất</p>
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
                                <option value="1">Giao thông</option>
                                <option value="2">Môi trường</option>
                                <option value="3">Cháy nổ</option>
                                <option value="4">Rác thải</option>
                                <option value="5">Ngập lụt</option>
                                <option value="6">Khác</option>
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
                                <option value="0">Thấp</option>
                                <option value="1">Trung bình</option>
                                <option value="2">Cao</option>
                                <option value="3">Khẩn cấp</option>
                            </select>
                        </div>
                        <div className="flex items-end">
                            <button className="whitespace-nowrap rounded-lg bg-blue-600 px-6 py-3 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
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
                            <span className="text-sm text-gray-500">{mockReports.length} phản ánh</span>
                        </div>
                    </div>

                    <div className="overflow-x-auto">
                        <table className="w-full">
                            <thead className="border-b border-gray-200 bg-gray-50">
                                <tr>
                                    <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                                        ID
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
                                {mockReports.map((report) => (
                                    <tr key={report.id} className="hover:bg-gray-50 transition-colors">
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            <span className="text-sm font-medium text-blue-600">#{report.id}</span>
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
                                Hiển thị <span className="font-medium">1</span> đến <span className="font-medium">5</span> trong tổng số{' '}
                                <span className="font-medium">156</span> kết quả
                            </div>
                            <div className="flex items-center gap-2">
                                <button className="inline-flex items-center gap-1 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50">
                                    <ChevronLeft className="h-4 w-4" />
                                    Trước
                                </button>
                                <button className="inline-flex items-center justify-center rounded-lg bg-blue-600 px-3 py-2 text-sm font-medium text-white">
                                    1
                                </button>
                                <button className="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                                    2
                                </button>
                                <button className="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                                    3
                                </button>
                                <span className="px-2 text-gray-500">...</span>
                                <button className="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                                    32
                                </button>
                                <button className="inline-flex items-center gap-1 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                                    Sau
                                    <ChevronRight className="h-4 w-4" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
