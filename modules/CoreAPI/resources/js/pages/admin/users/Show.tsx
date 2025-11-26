import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AdminLayout from '../layouts/AdminLayout';
import {
    ArrowLeft,
    User,
    Mail,
    Phone,
    Shield,
    Award,
    TrendingUp,
    Calendar,
    MapPin,
    CheckCircle2,
    XCircle,
    Clock,
    FileText,
    MessageSquare,
    ThumbsUp,
    Lock,
    Unlock,
    Plus,
    Minus,
} from 'lucide-react';
import {
    showSuccess,
    showError,
    showLockUnlockConfirm,
    showVerifyConfirm,
    showPointsDialog,
} from '@/utils/notifications';

interface Report {
    id: number;
    tieu_de: string;
    trang_thai: string;
    created_at: string;
}

interface User {
    id: number;
    ho_ten: string;
    email: string;
    so_dien_thoai: string;
    vai_tro: number;
    anh_dai_dien: string | null;
    trang_thai: number;
    diem_thanh_pho: number;
    xac_thuc_cong_dan: boolean;
    diem_uy_tin: number;
    tong_so_phan_anh: number;
    so_phan_anh_chinh_xac: number;
    ty_le_chinh_xac: number;
    cap_huy_hieu: number;
    push_token: string | null;
    tuy_chon_thong_bao: {
        email: boolean;
        push: boolean;
        sms: boolean;
    };
    created_at: string;
    updated_at: string;
    phan_anhs: Report[];
}

interface Props {
    user: User;
}

export default function UserShow({ user }: Props) {
    const [showEditModal, setShowEditModal] = useState(false);
    const [editForm, setEditForm] = useState({
        ho_ten: user.ho_ten,
        email: user.email,
        so_dien_thoai: user.so_dien_thoai,
        vai_tro: user.vai_tro,
    });

    const handleToggleStatus = async () => {
        const confirmed = await showLockUnlockConfirm(user.trang_thai === 1, user.ho_ten);

        if (confirmed) {
            router.patch(`/admin/users/status/${user.id}`, {
                trang_thai: user.trang_thai === 1 ? 0 : 1,
            }, {
                onSuccess: () => {
                    const action = user.trang_thai === 1 ? 'khóa' : 'mở khóa';
                    showSuccess(`${action.charAt(0).toUpperCase() + action.slice(1)} tài khoản thành công!`);
                },
                onError: () => {
                    showError('Không thể thay đổi trạng thái tài khoản!');
                },
            });
        }
    };

    const handleVerify = async () => {
        const confirmed = await showVerifyConfirm(user.ho_ten);

        if (confirmed) {
            router.post(`/admin/users/verify/${user.id}`, {}, {
                onSuccess: () => {
                    showSuccess('Xác thực công dân thành công!');
                },
                onError: () => {
                    showError('Không thể xác thực công dân!');
                },
            });
        }
    };

    const handleAddPoints = async () => {
        const result = await showPointsDialog();

        if (result) {
            router.post(`/admin/users/points/${user.id}`, {
                diem: result.points,
                ly_do: result.reason,
            }, {
                onSuccess: () => {
                    showSuccess(`Thêm ${result.points} điểm thành công!`);
                },
                onError: () => {
                    showError('Không thể thêm điểm!');
                },
            });
        }
    };

    const handleUpdateUser = () => {
        if (!editForm.ho_ten || !editForm.email || !editForm.so_dien_thoai) {
            showError('Vui lòng nhập đầy đủ thông tin!');
            return;
        }

        router.post(`/admin/users/update/${user.id}`, editForm, {
            onSuccess: () => {
                setShowEditModal(false);
                showSuccess('Cập nhật thông tin thành công!');
            },
            onError: (errors) => {
                const firstError = Object.values(errors)[0] as string;
                showError(firstError || 'Không thể cập nhật thông tin!');
            },
        });
    };

    const getBadgeName = (badge: number) => {
        const badges = {
            0: { name: 'Đồng', color: 'orange' },
            1: { name: 'Bạc', color: 'gray' },
            2: { name: 'Vàng', color: 'yellow' },
            3: { name: 'Bạch kim', color: 'blue' },
        };
        return badges[badge as keyof typeof badges] || badges[0];
    };

    const badgeInfo = getBadgeName(user.cap_huy_hieu);

    return (
        <AdminLayout>
            <Head title={`${user.ho_ten} - Quản lý người dùng`} />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <Link
                            href="/admin/users"
                            className="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white p-2 hover:bg-gray-50"
                        >
                            <ArrowLeft className="h-5 w-5" />
                        </Link>
                        <div>
                            <h1 className="text-3xl font-bold text-gray-900">Chi tiết người dùng</h1>
                            <p className="mt-1 text-sm text-gray-500">
                                Thông tin chi tiết và lịch sử hoạt động
                            </p>
                        </div>
                    </div>
                    <div className="flex gap-3">
                        <button
                            onClick={() => setShowEditModal(true)}
                            className="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
                        >
                            <User className="h-4 w-4" />
                            Cập nhật thông tin
                        </button>
                        {!user.xac_thuc_cong_dan && (
                            <button
                                onClick={handleVerify}
                                className="inline-flex items-center gap-2 rounded-lg border border-blue-300 bg-white px-4 py-2 text-sm font-medium text-blue-700 hover:bg-blue-50"
                            >
                                <Shield className="h-4 w-4" />
                                Xác thực
                            </button>
                        )}
                        <button
                            onClick={handleAddPoints}
                            className="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                        >
                            <Plus className="h-4 w-4" />
                            Thêm điểm
                        </button>
                        <button
                            onClick={handleToggleStatus}
                            className={`inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium ${
                                user.trang_thai === 1
                                    ? 'border border-red-300 bg-white text-red-700 hover:bg-red-50'
                                    : 'bg-green-600 text-white hover:bg-green-700'
                            }`}
                        >
                            {user.trang_thai === 1 ? (
                                <>
                                    <Lock className="h-4 w-4" />
                                    Khóa tài khoản
                                </>
                            ) : (
                                <>
                                    <Unlock className="h-4 w-4" />
                                    Mở khóa
                                </>
                            )}
                        </button>
                    </div>
                </div>

                {/* User Profile Card */}
                <div className="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                    <div className="flex items-start gap-6">
                        <div className="h-24 w-24 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white text-3xl font-bold">
                            {user.ho_ten.charAt(0)}
                        </div>
                        <div className="flex-1">
                            <div className="flex items-start justify-between">
                                <div>
                                    <h2 className="text-2xl font-bold text-gray-900">{user.ho_ten}</h2>
                                    <div className="mt-2 flex items-center gap-4">
                                        <span className="inline-flex items-center gap-1.5 text-sm text-gray-600">
                                            <Mail className="h-4 w-4" />
                                            {user.email}
                                        </span>
                                        <span className="inline-flex items-center gap-1.5 text-sm text-gray-600">
                                            <Phone className="h-4 w-4" />
                                            {user.so_dien_thoai}
                                        </span>
                                    </div>
                                </div>
                                <div className="flex items-center gap-2">
                                    {user.trang_thai === 1 ? (
                                        <span className="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm font-medium border bg-green-100 text-green-800 border-green-200">
                                            <CheckCircle2 className="w-4 h-4" />
                                            Hoạt động
                                        </span>
                                    ) : (
                                        <span className="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm font-medium border bg-red-100 text-red-800 border-red-200">
                                            <XCircle className="w-4 h-4" />
                                            Đã khóa
                                        </span>
                                    )}
                                    {user.xac_thuc_cong_dan && (
                                        <span className="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                            <Shield className="w-4 h-4" />
                                            Đã xác thực
                                        </span>
                                    )}
                                </div>
                            </div>
                            <div className="mt-4 flex items-center gap-6">
                                <div>
                                    <span className="text-sm text-gray-500">Vai trò</span>
                                    <p className="mt-1 text-base font-semibold text-gray-900">
                                        {user.vai_tro === 0 ? 'Công dân' : 'Cán bộ'}
                                    </p>
                                </div>
                                <div className="h-10 w-px bg-gray-200" />
                                <div>
                                    <span className="text-sm text-gray-500">Ngày tham gia</span>
                                    <p className="mt-1 text-base font-semibold text-gray-900">
                                        {user.created_at}
                                    </p>
                                </div>
                                <div className="h-10 w-px bg-gray-200" />
                                <div>
                                    <span className="text-sm text-gray-500">Cập nhật cuối</span>
                                    <p className="mt-1 text-base font-semibold text-gray-900">
                                        {user.updated_at}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Stats Grid */}
                <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                    <div className="rounded-lg border border-gray-200 bg-white p-6">
                        <div className="flex items-center justify-between">
                            <div>
                                <p className="text-sm font-medium text-gray-600">CityPoints</p>
                                <p className="mt-2 text-3xl font-bold text-blue-600">{user.diem_thanh_pho}</p>
                            </div>
                            <div className="rounded-full bg-blue-100 p-3">
                                <TrendingUp className="h-6 w-6 text-blue-600" />
                            </div>
                        </div>
                    </div>

                    <div className="rounded-lg border border-gray-200 bg-white p-6">
                        <div className="flex items-center justify-between">
                            <div>
                                <p className="text-sm font-medium text-gray-600">Điểm uy tín</p>
                                <p className="mt-2 text-3xl font-bold text-gray-900">{user.diem_uy_tin}/100</p>
                            </div>
                            <div className="rounded-full bg-purple-100 p-3">
                                <Shield className="h-6 w-6 text-purple-600" />
                            </div>
                        </div>
                    </div>

                    <div className="rounded-lg border border-gray-200 bg-white p-6">
                        <div className="flex items-center justify-between">
                            <div>
                                <p className="text-sm font-medium text-gray-600">Phản ánh</p>
                                <p className="mt-2 text-3xl font-bold text-gray-900">{user.tong_so_phan_anh}</p>
                                <p className="mt-1 text-xs text-gray-500">
                                    {user.so_phan_anh_chinh_xac} chính xác
                                </p>
                            </div>
                            <div className="rounded-full bg-green-100 p-3">
                                <FileText className="h-6 w-6 text-green-600" />
                            </div>
                        </div>
                    </div>

                    <div className="rounded-lg border border-gray-200 bg-white p-6">
                        <div className="flex items-center justify-between">
                            <div>
                                <p className="text-sm font-medium text-gray-600">Huy hiệu</p>
                                <p className="mt-2 text-2xl font-bold text-gray-900">{badgeInfo.name}</p>
                                <p className="mt-1 text-xs text-gray-500">
                                    {user.ty_le_chinh_xac.toFixed(1)}% chính xác
                                </p>
                            </div>
                            <div className={`rounded-full bg-${badgeInfo.color}-100 p-3`}>
                                <Award className={`h-6 w-6 text-${badgeInfo.color}-600`} />
                            </div>
                        </div>
                    </div>
                </div>

                {/* Reports History */}
                <div className="rounded-lg border border-gray-200 bg-white shadow-sm overflow-hidden">
                    <div className="border-b border-gray-200 bg-gray-50 px-6 py-4">
                        <h3 className="text-lg font-semibold text-gray-900">Lịch sử phản ánh</h3>
                    </div>
                    <div className="overflow-x-auto">
                        {user.phan_anhs && user.phan_anhs.length > 0 ? (
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
                                <tbody className="divide-y divide-gray-200 bg-white">
                                    {user.phan_anhs.map((report, index) => (
                                        <tr key={report.id} className="hover:bg-gray-50">
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {index + 1}
                                            </td>
                                            <td className="px-6 py-4 text-sm text-gray-900">
                                                {report.tieu_de}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm">
                                                <span className="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    {report.trang_thai}
                                                </span>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {report.created_at}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-right text-sm">
                                                <Link
                                                    href={`/admin/reports/${report.id}`}
                                                    className="text-blue-600 hover:text-blue-800"
                                                >
                                                    Chi tiết
                                                </Link>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        ) : (
                            <div className="text-center py-12">
                                <FileText className="mx-auto h-12 w-12 text-gray-400" />
                                <h3 className="mt-2 text-sm font-medium text-gray-900">Chưa có phản ánh</h3>
                                <p className="mt-1 text-sm text-gray-500">
                                    Người dùng này chưa gửi phản ánh nào.
                                </p>
                            </div>
                        )}
                    </div>
                </div>
            </div>

            {/* Edit User Modal */}
            {showEditModal && (
                <div className="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
                    <div className="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4">
                        <div className="px-6 py-4 border-b border-gray-200">
                            <h3 className="text-lg font-semibold text-gray-900">Cập nhật thông tin người dùng</h3>
                        </div>
                        <div className="px-6 py-4 space-y-4">
                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-2">
                                        Họ tên <span className="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        value={editForm.ho_ten}
                                        onChange={(e) => setEditForm({ ...editForm, ho_ten: e.target.value })}
                                        placeholder="Nhập họ tên"
                                        className="block w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-20"
                                    />
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-2">
                                        Email <span className="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="email"
                                        value={editForm.email}
                                        onChange={(e) => setEditForm({ ...editForm, email: e.target.value })}
                                        placeholder="Nhập email"
                                        className="block w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-20"
                                    />
                                </div>
                            </div>
                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-2">
                                        Số điện thoại <span className="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="tel"
                                        value={editForm.so_dien_thoai}
                                        onChange={(e) => setEditForm({ ...editForm, so_dien_thoai: e.target.value })}
                                        placeholder="Nhập số điện thoại"
                                        className="block w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-20"
                                    />
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-2">
                                        Vai trò
                                    </label>
                                    <select
                                        value={editForm.vai_tro}
                                        onChange={(e) => setEditForm({ ...editForm, vai_tro: parseInt(e.target.value) })}
                                        className="block w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-20"
                                    >
                                        <option value="0">Công dân</option>
                                        <option value="1">Cán bộ</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div className="px-6 py-4 border-t border-gray-200 flex justify-end gap-3">
                            <button
                                onClick={() => {
                                    setShowEditModal(false);
                                    setEditForm({
                                        ho_ten: user.ho_ten,
                                        email: user.email,
                                        so_dien_thoai: user.so_dien_thoai,
                                        vai_tro: user.vai_tro,
                                    });
                                }}
                                className="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
                            >
                                Hủy
                            </button>
                            <button
                                onClick={handleUpdateUser}
                                className="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700"
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
