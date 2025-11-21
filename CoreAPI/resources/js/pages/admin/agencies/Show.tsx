import React from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AdminLayout from '../layouts/AdminLayout';
import {
    ArrowLeft,
    Building2,
    Mail,
    Phone,
    MapPin,
    Calendar,
    Edit,
    Trash2,
    FileText,
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
    mo_ta: string;
    trang_thai: number;
    created_at: string;
    updated_at: string;
    phan_anhs: Array<{
        id: number;
        tieu_de: string;
        trang_thai: string;
        created_at: string;
    }>;
}

interface Props {
    agency: Agency;
}

export default function AgencyShow({ agency }: Props) {
    const handleDelete = async () => {
        const confirmed = await showDeleteConfirm(`cơ quan "${agency.ten_co_quan}"`);

        if (confirmed) {
            router.delete(`/admin/agencies/${agency.id}`, {
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
            <span className={`inline-flex items-center rounded-full px-3 py-1 text-sm font-medium ${levelInfo.style}`}>
                {levelInfo.text}
            </span>
        );
    };

    return (
        <AdminLayout>
            <Head title={`${agency.ten_co_quan} - Chi tiết cơ quan`} />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <Link
                            href="/admin/agencies"
                            className="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                        >
                            <ArrowLeft className="h-4 w-4" />
                            Quay lại
                        </Link>
                        <div>
                            <h1 className="text-2xl font-bold text-gray-900">Chi tiết cơ quan</h1>
                            <p className="mt-1 text-sm text-gray-500">Xem thông tin chi tiết cơ quan xử lý</p>
                        </div>
                    </div>
                    <div className="flex items-center gap-3">
                        <Link
                            href={`/admin/agencies/${agency.id}/edit`}
                            className="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
                        >
                            <Edit className="h-4 w-4" />
                            Chỉnh sửa
                        </Link>
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
                        {/* Agency Info */}
                        <div className="rounded-lg border border-gray-200 bg-white shadow-sm">
                            <div className="border-b border-gray-200 px-6 py-4">
                                <div className="flex items-start justify-between">
                                    <div className="flex-1">
                                        <h2 className="text-xl font-bold text-gray-900">{agency.ten_co_quan}</h2>
                                        <div className="mt-2 flex items-center gap-3">
                                            {getLevelBadge(agency.cap_do)}
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
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div className="px-6 py-6">
                                <div className="space-y-4">
                                    <div>
                                        <label className="text-sm font-semibold text-gray-900">Liên hệ</label>
                                        <div className="mt-2 space-y-2">
                                            <div className="flex items-center gap-2 text-sm text-gray-700">
                                                <Mail className="h-4 w-4 text-gray-400" />
                                                <span>{agency.email_lien_he}</span>
                                            </div>
                                            {agency.so_dien_thoai && (
                                                <div className="flex items-center gap-2 text-sm text-gray-700">
                                                    <Phone className="h-4 w-4 text-gray-400" />
                                                    <span>{agency.so_dien_thoai}</span>
                                                </div>
                                            )}
                                        </div>
                                    </div>

                                    {agency.dia_chi && (
                                        <div>
                                            <label className="text-sm font-semibold text-gray-900">Địa chỉ</label>
                                            <div className="mt-2 flex items-start gap-2 text-sm text-gray-700">
                                                <MapPin className="h-4 w-4 flex-shrink-0 text-gray-400 mt-0.5" />
                                                <span>{agency.dia_chi}</span>
                                            </div>
                                        </div>
                                    )}

                                    {agency.mo_ta && (
                                        <div>
                                            <label className="text-sm font-semibold text-gray-900">Mô tả</label>
                                            <p className="mt-2 text-sm text-gray-700 whitespace-pre-wrap">{agency.mo_ta}</p>
                                        </div>
                                    )}
                                </div>
                            </div>
                        </div>

                        {/* Reports List */}
                        <div className="rounded-lg border border-gray-200 bg-white shadow-sm">
                            <div className="border-b border-gray-200 px-6 py-4">
                                <h3 className="flex items-center gap-2 text-lg font-semibold text-gray-900">
                                    <FileText className="h-5 w-5" />
                                    Phản ánh được phân ({agency.phan_anhs.length})
                                </h3>
                            </div>
                            {agency.phan_anhs.length > 0 ? (
                                <div className="overflow-x-auto">
                                    <table className="w-full">
                                        <thead className="border-b border-gray-200 bg-gray-50">
                                            <tr>
                                                <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                                                    STT
                                                </th>
                                                <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                                                    Tiêu đề
                                                </th>
                                                <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                                                    Trạng thái
                                                </th>
                                                <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                                                    Ngày tạo
                                                </th>
                                                <th className="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600">
                                                    Thao tác
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody className="divide-y divide-gray-200">
                                            {agency.phan_anhs.map((report, index) => (
                                                <tr key={report.id} className="hover:bg-gray-50">
                                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        {index + 1}
                                                    </td>
                                                    <td className="px-6 py-4">
                                                        <Link
                                                            href={`/admin/reports/${report.id}`}
                                                            className="text-sm font-medium text-gray-900 hover:text-blue-600 line-clamp-1"
                                                        >
                                                            {report.tieu_de}
                                                        </Link>
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap">
                                                        <span className="text-sm text-gray-700">{report.trang_thai}</span>
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        {report.created_at}
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap text-right">
                                                        <Link
                                                            href={`/admin/reports/${report.id}`}
                                                            className="text-sm font-medium text-blue-600 hover:text-blue-700"
                                                        >
                                                            Xem chi tiết
                                                        </Link>
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            ) : (
                                <div className="px-6 py-12 text-center">
                                    <FileText className="mx-auto h-12 w-12 text-gray-400" />
                                    <p className="mt-2 text-sm font-medium text-gray-900">Chưa có phản ánh nào</p>
                                    <p className="mt-1 text-sm text-gray-500">
                                        Cơ quan này chưa được phân công xử lý phản ánh nào
                                    </p>
                                </div>
                            )}
                        </div>
                    </div>

                    {/* Sidebar */}
                    <div className="space-y-6">
                        {/* Stats */}
                        <div className="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                            <h3 className="mb-4 text-lg font-semibold text-gray-900">Thống kê</h3>
                            <div className="space-y-4">
                                <div className="rounded-lg bg-blue-50 p-4">
                                    <div className="flex items-center justify-between">
                                        <span className="text-sm font-medium text-blue-900">Phản ánh được phân</span>
                                        <span className="text-2xl font-bold text-blue-700">{agency.phan_anhs.length}</span>
                                    </div>
                                </div>
                            </div>
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
                                        <p className="font-medium text-gray-900">Tạo cơ quan</p>
                                        <p className="text-xs text-gray-500">{agency.created_at}</p>
                                    </div>
                                </div>
                                <div className="flex items-start gap-3">
                                    <div className="mt-1 h-2 w-2 flex-shrink-0 rounded-full bg-blue-500"></div>
                                    <div>
                                        <p className="font-medium text-gray-900">Cập nhật gần nhất</p>
                                        <p className="text-xs text-gray-500">{agency.updated_at}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
