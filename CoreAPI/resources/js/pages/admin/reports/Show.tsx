import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AdminLayout from '../layouts/AdminLayout';
import {
    ArrowLeft,
    MapPin,
    Calendar,
    User,
    Building2,
    AlertCircle,
    CheckCircle2,
    Clock,
    XCircle,
    Eye,
    ThumbsUp,
    ThumbsDown,
    MessageSquare,
    Tag,
    Shield,
    FileText,
    Edit,
    Trash2,
} from 'lucide-react';
import {
    showSuccess,
    showError,
    showDeleteConfirm,
    showStatusConfirm,
} from '@/utils/notifications';

interface Report {
    id: number;
    tieu_de: string;
    mo_ta: string;
    danh_muc: {
        id: number;
        ten_danh_muc: string;
    };
    trang_thai: number;
    trang_thai_text: string;
    uu_tien: {
        id: number;
        ten_muc: string;
    };
    dia_chi: string;
    vi_do: number;
    kinh_do: number;
    nhan_ai: string[];
    do_tin_cay: number;
    la_cong_khai: boolean;
    luot_ung_ho: number;
    luot_khong_ung_ho: number;
    luot_xem: number;
    the_tags: string[];
    du_lieu_mo_rong: any;
    nguoi_dung: {
        id: number;
        ho_ten: string;
        email: string;
        so_dien_thoai: string;
        diem_uy_tin: number;
        xac_thuc_cong_dan: boolean;
    };
    co_quan: {
        id: number;
        ten_co_quan: string;
        email_lien_he: string;
        so_dien_thoai: string;
    } | null;
    binh_luans: Array<{
        id: number;
        noi_dung: string;
        la_chinh_thuc: boolean;
        nguoi_dung: {
            ho_ten: string;
        };
        created_at: string;
    }>;
    created_at: string;
    updated_at: string;
}

interface Agency {
    id: number;
    ten_co_quan: string;
}

interface Props {
    report: Report;
    agencies: Agency[];
}

export default function ReportShow({ report, agencies }: Props) {
    const [showStatusModal, setShowStatusModal] = useState(false);
    const [statusForm, setStatusForm] = useState({
        trang_thai: report.trang_thai,
        co_quan_phu_trach_id: report.co_quan?.id || '',
        ghi_chu: '',
    });

    const handleUpdateStatus = async () => {
        if (!statusForm.trang_thai && statusForm.trang_thai !== 0) {
            showError('Vui lòng chọn trạng thái!');
            return;
        }

        const statusNames: Record<number, string> = {
            0: 'Chờ xử lý',
            1: 'Đã xác minh',
            2: 'Đang xử lý',
            3: 'Đã giải quyết',
            4: 'Từ chối',
        };

        const confirmed = await showStatusConfirm(
            report.trang_thai_text,
            statusNames[statusForm.trang_thai],
            'phản ánh này'
        );

        if (confirmed) {
            router.patch(`/admin/reports/status/${report.id}`, statusForm, {
                onSuccess: () => {
                    setShowStatusModal(false);
                    showSuccess('Cập nhật trạng thái thành công!');
                },
                onError: (errors) => {
                    const firstError = Object.values(errors)[0] as string;
                    showError(firstError || 'Không thể cập nhật trạng thái!');
                },
            });
        }
    };

    const handleDelete = async () => {
        const confirmed = await showDeleteConfirm('phản ánh này');

        if (confirmed) {
            router.delete(`/admin/reports/${report.id}`, {
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
            0: { text: 'Chờ xử lý', style: 'bg-yellow-100 text-yellow-800 border-yellow-200', icon: <Clock className="w-4 h-4" /> },
            1: { text: 'Đã xác minh', style: 'bg-blue-100 text-blue-800 border-blue-200', icon: <CheckCircle2 className="w-4 h-4" /> },
            2: { text: 'Đang xử lý', style: 'bg-orange-100 text-orange-800 border-orange-200', icon: <AlertCircle className="w-4 h-4" /> },
            3: { text: 'Đã giải quyết', style: 'bg-green-100 text-green-800 border-green-200', icon: <CheckCircle2 className="w-4 h-4" /> },
            4: { text: 'Từ chối', style: 'bg-red-100 text-red-800 border-red-200', icon: <XCircle className="w-4 h-4" /> },
        };

        const statusInfo = statusMap[status as keyof typeof statusMap] || statusMap[0];

        return (
            <span className={`inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-sm font-medium border ${statusInfo.style}`}>
                {statusInfo.icon}
                {statusInfo.text}
            </span>
        );
    };

    return (
        <AdminLayout>
            <Head title={`Chi tiết phản ánh - ${report.tieu_de}`} />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <Link
                            href="/admin/reports"
                            className="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                        >
                            <ArrowLeft className="h-4 w-4" />
                            Quay lại
                        </Link>
                        <div>
                            <h1 className="text-2xl font-bold text-gray-900">Chi tiết phản ánh #{report.id}</h1>
                            <p className="mt-1 text-sm text-gray-500">Xem và quản lý phản ánh từ người dân</p>
                        </div>
                    </div>
                    <div className="flex items-center gap-3">
                        <button
                            onClick={() => setShowStatusModal(true)}
                            className="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
                        >
                            <Edit className="h-4 w-4" />
                            Cập nhật trạng thái
                        </button>
                        <button
                            onClick={handleDelete}
                            className="inline-flex items-center gap-2 rounded-lg border border-red-300 bg-white px-4 py-2 text-sm font-medium text-red-700 hover:bg-red-50"
                        >
                            <Trash2 className="h-4 w-4" />
                            Xóa
                        </button>
                    </div>
                </div>

                <div className="grid gap-6 lg:grid-cols-3">
                    {/* Main Content */}
                    <div className="space-y-6 lg:col-span-2">
                        {/* Report Details */}
                        <div className="rounded-lg border border-gray-200 bg-white shadow-sm">
                            <div className="border-b border-gray-200 px-6 py-4">
                                <div className="flex items-start justify-between">
                                    <div className="flex-1">
                                        <h2 className="text-xl font-bold text-gray-900">{report.tieu_de}</h2>
                                        <div className="mt-2 flex flex-wrap items-center gap-3">
                                            {getStatusBadge(report.trang_thai)}
                                            <span className="inline-flex items-center gap-1 rounded-md bg-gray-100 px-2.5 py-1 text-sm font-medium text-gray-700">
                                                <Tag className="h-3.5 w-3.5" />
                                                {report.danh_muc.ten_danh_muc}
                                            </span>
                                            <span className="inline-flex items-center gap-1 rounded-md bg-orange-100 px-2.5 py-1 text-sm font-semibold text-orange-700">
                                                <AlertCircle className="h-3.5 w-3.5" />
                                                {report.uu_tien.ten_muc}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div className="px-6 py-6">
                                <h3 className="mb-3 text-sm font-semibold text-gray-900">Mô tả chi tiết</h3>
                                <div className="prose prose-sm max-w-none text-gray-700">
                                    <p className="whitespace-pre-wrap">{report.mo_ta}</p>
                                </div>

                                {report.the_tags && report.the_tags.length > 0 && (
                                    <div className="mt-4 flex flex-wrap gap-2">
                                        {report.the_tags.map((tag, index) => (
                                            <span
                                                key={index}
                                                className="inline-flex items-center gap-1 rounded-full bg-blue-50 px-3 py-1 text-xs font-medium text-blue-700"
                                            >
                                                #{tag}
                                            </span>
                                        ))}
                                    </div>
                                )}
                            </div>

                            <div className="border-t border-gray-200 bg-gray-50 px-6 py-4">
                                <div className="flex items-center justify-between text-sm">
                                    <div className="flex items-center gap-6 text-gray-600">
                                        <div className="flex items-center gap-2">
                                            <Eye className="h-4 w-4" />
                                            <span>{report.luot_xem} lượt xem</span>
                                        </div>
                                        <div className="flex items-center gap-2 text-green-600">
                                            <ThumbsUp className="h-4 w-4" />
                                            <span>{report.luot_ung_ho}</span>
                                        </div>
                                        <div className="flex items-center gap-2 text-red-600">
                                            <ThumbsDown className="h-4 w-4" />
                                            <span>{report.luot_khong_ung_ho}</span>
                                        </div>
                                        <div className="flex items-center gap-2">
                                            <MessageSquare className="h-4 w-4" />
                                            <span>{report.binh_luans.length} bình luận</span>
                                        </div>
                                    </div>
                                    <div className="flex items-center gap-2">
                                        <Shield className="h-4 w-4 text-blue-600" />
                                        <span className="font-medium text-gray-900">
                                            Độ tin cậy: {(report.do_tin_cay * 100).toFixed(0)}%
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {/* Location & Map */}
                        <div className="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                            <h3 className="mb-4 flex items-center gap-2 text-lg font-semibold text-gray-900">
                                <MapPin className="h-5 w-5 text-red-600" />
                                Vị trí
                            </h3>
                            <p className="mb-4 text-sm text-gray-700">{report.dia_chi}</p>
                            <div className="flex items-center gap-4 text-sm text-gray-500">
                                <span>Vĩ độ: {report.vi_do}</span>
                                <span>•</span>
                                <span>Kinh độ: {report.kinh_do}</span>
                            </div>
                            {/* Placeholder for map - can integrate Google Maps later */}
                            <div className="mt-4 flex h-64 items-center justify-center rounded-lg bg-gray-100 text-gray-500">
                                <MapPin className="h-8 w-8" />
                            </div>
                        </div>

                        {/* Comments */}
                        {report.binh_luans.length > 0 && (
                            <div className="rounded-lg border border-gray-200 bg-white shadow-sm">
                                <div className="border-b border-gray-200 px-6 py-4">
                                    <h3 className="flex items-center gap-2 text-lg font-semibold text-gray-900">
                                        <MessageSquare className="h-5 w-5" />
                                        Bình luận ({report.binh_luans.length})
                                    </h3>
                                </div>
                                <div className="divide-y divide-gray-200">
                                    {report.binh_luans.map((comment) => (
                                        <div key={comment.id} className="px-6 py-4">
                                            <div className="flex items-start gap-3">
                                                <div className="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-blue-500 to-purple-600 text-xs font-semibold text-white">
                                                    {comment.nguoi_dung.ho_ten.charAt(0)}
                                                </div>
                                                <div className="flex-1">
                                                    <div className="flex items-center gap-2">
                                                        <span className="font-medium text-gray-900">
                                                            {comment.nguoi_dung.ho_ten}
                                                        </span>
                                                        {comment.la_chinh_thuc && (
                                                            <span className="rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-700">
                                                                Chính thức
                                                            </span>
                                                        )}
                                                        <span className="text-xs text-gray-500">{comment.created_at}</span>
                                                    </div>
                                                    <p className="mt-1 text-sm text-gray-700">{comment.noi_dung}</p>
                                                </div>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        )}
                    </div>

                    {/* Sidebar */}
                    <div className="space-y-6">
                        {/* Reporter Info */}
                        <div className="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                            <h3 className="mb-4 flex items-center gap-2 text-lg font-semibold text-gray-900">
                                <User className="h-5 w-5" />
                                Người gửi
                            </h3>
                            <div className="space-y-3">
                                <div className="flex items-center gap-3">
                                    <div className="flex h-12 w-12 items-center justify-center rounded-full bg-gradient-to-br from-blue-500 to-purple-600 text-lg font-semibold text-white">
                                        {report.nguoi_dung.ho_ten.charAt(0)}
                                    </div>
                                    <div>
                                        <Link
                                            href={`/admin/users/${report.nguoi_dung.id}`}
                                            className="font-medium text-gray-900 hover:text-blue-600"
                                        >
                                            {report.nguoi_dung.ho_ten}
                                        </Link>
                                        {report.nguoi_dung.xac_thuc_cong_dan && (
                                            <div className="mt-1 flex items-center gap-1 text-xs text-green-600">
                                                <CheckCircle2 className="h-3.5 w-3.5" />
                                                <span>Đã xác thực</span>
                                            </div>
                                        )}
                                    </div>
                                </div>
                                <div className="space-y-2 border-t border-gray-200 pt-3 text-sm">
                                    <div className="flex items-center justify-between">
                                        <span className="text-gray-600">Email:</span>
                                        <span className="font-medium text-gray-900">{report.nguoi_dung.email}</span>
                                    </div>
                                    <div className="flex items-center justify-between">
                                        <span className="text-gray-600">SĐT:</span>
                                        <span className="font-medium text-gray-900">{report.nguoi_dung.so_dien_thoai}</span>
                                    </div>
                                    <div className="flex items-center justify-between">
                                        <span className="text-gray-600">Điểm uy tín:</span>
                                        <span className="font-semibold text-blue-600">{report.nguoi_dung.diem_uy_tin}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {/* Agency Info */}
                        <div className="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                            <h3 className="mb-4 flex items-center gap-2 text-lg font-semibold text-gray-900">
                                <Building2 className="h-5 w-5" />
                                Cơ quan phụ trách
                            </h3>
                            {report.co_quan ? (
                                <div className="space-y-3">
                                    <div className="rounded-lg bg-blue-50 p-3">
                                        <p className="font-medium text-gray-900">{report.co_quan.ten_co_quan}</p>
                                    </div>
                                    <div className="space-y-2 text-sm">
                                        <div className="flex items-center justify-between">
                                            <span className="text-gray-600">Email:</span>
                                            <span className="font-medium text-gray-900">{report.co_quan.email_lien_he}</span>
                                        </div>
                                        <div className="flex items-center justify-between">
                                            <span className="text-gray-600">SĐT:</span>
                                            <span className="font-medium text-gray-900">{report.co_quan.so_dien_thoai}</span>
                                        </div>
                                    </div>
                                </div>
                            ) : (
                                <div className="rounded-lg bg-yellow-50 p-4 text-center">
                                    <AlertCircle className="mx-auto h-8 w-8 text-yellow-600" />
                                    <p className="mt-2 text-sm font-medium text-yellow-800">
                                        Chưa phân cơ quan xử lý
                                    </p>
                                    <p className="mt-1 text-xs text-yellow-700">
                                        Vui lòng cập nhật trạng thái để phân công
                                    </p>
                                </div>
                            )}
                        </div>

                        {/* Timeline */}
                        <div className="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                            <h3 className="mb-4 flex items-center gap-2 text-lg font-semibold text-gray-900">
                                <Calendar className="h-5 w-5" />
                                Thời gian
                            </h3>
                            <div className="space-y-3 text-sm">
                                <div className="flex items-start gap-3">
                                    <div className="mt-1 h-2 w-2 flex-shrink-0 rounded-full bg-green-500"></div>
                                    <div>
                                        <p className="font-medium text-gray-900">Tạo phản ánh</p>
                                        <p className="text-xs text-gray-500">{report.created_at}</p>
                                    </div>
                                </div>
                                <div className="flex items-start gap-3">
                                    <div className="mt-1 h-2 w-2 flex-shrink-0 rounded-full bg-blue-500"></div>
                                    <div>
                                        <p className="font-medium text-gray-900">Cập nhật gần nhất</p>
                                        <p className="text-xs text-gray-500">{report.updated_at}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {/* Update Status Modal */}
            {showStatusModal && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-gray-500 bg-opacity-75">
                    <div className="mx-4 w-full max-w-lg rounded-lg bg-white shadow-xl">
                        <div className="border-b border-gray-200 px-6 py-4">
                            <h3 className="text-lg font-semibold text-gray-900">Cập nhật trạng thái xử lý</h3>
                        </div>
                        <div className="space-y-4 px-6 py-4">
                            <div>
                                <label className="mb-2 block text-sm font-medium text-gray-700">
                                    Trạng thái <span className="text-red-600">*</span>
                                </label>
                                <select
                                    value={statusForm.trang_thai}
                                    onChange={(e) =>
                                        setStatusForm({ ...statusForm, trang_thai: parseInt(e.target.value) })
                                    }
                                    className="block w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-20"
                                >
                                    <option value={0}>Chờ xử lý</option>
                                    <option value={1}>Đã xác minh</option>
                                    <option value={2}>Đang xử lý</option>
                                    <option value={3}>Đã giải quyết</option>
                                    <option value={4}>Từ chối</option>
                                </select>
                            </div>

                            <div>
                                <label className="mb-2 block text-sm font-medium text-gray-700">
                                    Cơ quan phụ trách
                                </label>
                                <select
                                    value={statusForm.co_quan_phu_trach_id}
                                    onChange={(e) =>
                                        setStatusForm({ ...statusForm, co_quan_phu_trach_id: e.target.value })
                                    }
                                    className="block w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-20"
                                >
                                    <option value="">-- Chọn cơ quan --</option>
                                    {agencies.map((agency) => (
                                        <option key={agency.id} value={agency.id}>
                                            {agency.ten_co_quan}
                                        </option>
                                    ))}
                                </select>
                            </div>

                            <div>
                                <label className="mb-2 block text-sm font-medium text-gray-700">Ghi chú</label>
                                <textarea
                                    value={statusForm.ghi_chu}
                                    onChange={(e) => setStatusForm({ ...statusForm, ghi_chu: e.target.value })}
                                    placeholder="Nhập ghi chú về việc cập nhật..."
                                    rows={3}
                                    maxLength={500}
                                    className="block w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-20"
                                />
                                <p className="mt-1 text-xs text-gray-500">{statusForm.ghi_chu.length}/500 ký tự</p>
                            </div>
                        </div>
                        <div className="flex justify-end gap-3 border-t border-gray-200 px-6 py-4">
                            <button
                                onClick={() => setShowStatusModal(false)}
                                className="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                            >
                                Hủy
                            </button>
                            <button
                                onClick={handleUpdateStatus}
                                className="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
                            >
                                Cập nhật
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </AdminLayout>
    );
}
