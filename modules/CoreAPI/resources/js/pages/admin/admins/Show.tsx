import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AdminLayout from '../layouts/AdminLayout';
import {
    ArrowLeft,
    Shield,
    Crown,
    Mail,
    Phone,
    User,
    Calendar,
    Clock,
    CheckCircle2,
    XCircle,
    Edit,
    Key,
    Activity,
} from 'lucide-react';
import { showSuccess, showError } from '@/utils/notifications';
import Swal from 'sweetalert2';

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
    updated_at: string;
}

interface Log {
    id: number;
    hanh_dong: string;
    doi_tuong: string;
    noi_dung: string;
    dia_chi_ip: string;
    created_at: string;
}

interface Props {
    admin: Admin;
    recentLogs: Log[];
}

export default function Show({ admin, recentLogs }: Props) {
    const [processing, setProcessing] = useState(false);

    const handleChangePassword = async () => {
        const { value: password } = await Swal.fire({
            title: 'Đổi mật khẩu',
            html: `
                <p class="text-sm text-gray-600 mb-4">Đổi mật khẩu cho: <strong>${admin.ho_ten}</strong></p>
                <input id="new-password" type="password" class="swal2-input" placeholder="Nhập mật khẩu mới">
                <p class="text-xs text-gray-500 mt-2">Tối thiểu 8 ký tự, bao gồm chữ hoa, chữ thường và số</p>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Đổi mật khẩu',
            cancelButtonText: 'Hủy',
            confirmButtonColor: '#3B82F6',
            cancelButtonColor: '#6B7280',
            preConfirm: () => {
                const password = (document.getElementById('new-password') as HTMLInputElement).value;
                if (!password) {
                    Swal.showValidationMessage('Vui lòng nhập mật khẩu mới');
                    return false;
                }
                if (password.length < 8) {
                    Swal.showValidationMessage('Mật khẩu phải có ít nhất 8 ký tự');
                    return false;
                }
                return password;
            },
        });

        if (password) {
            setProcessing(true);
            router.post(`/admin/admins/password/${admin.id}`, {
                mat_khau_moi: password,
            }, {
                onSuccess: () => {
                    showSuccess('Đổi mật khẩu thành công!');
                },
                onError: (errors) => {
                    const firstError = Object.values(errors)[0] as string;
                    showError(firstError || 'Có lỗi xảy ra!');
                },
                onFinish: () => {
                    setProcessing(false);
                },
            });
        }
    };

    const getActionBadge = (action: string) => {
        const badges: Record<string, { text: string; color: string }> = {
            create: { text: 'Tạo mới', color: 'bg-green-100 text-green-800' },
            update: { text: 'Cập nhật', color: 'bg-blue-100 text-blue-800' },
            delete: { text: 'Xóa', color: 'bg-red-100 text-red-800' },
            login: { text: 'Đăng nhập', color: 'bg-purple-100 text-purple-800' },
            logout: { text: 'Đăng xuất', color: 'bg-gray-100 text-gray-800' },
        };

        const badge = badges[action] || { text: action, color: 'bg-gray-100 text-gray-800' };
        return <span className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ${badge.color}`}>{badge.text}</span>;
    };

    return (
        <AdminLayout>
            <Head title={`${admin.ho_ten} - Admin`} />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex items-center gap-4">
                    <Link
                        href="/admin/admins"
                        className="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                    >
                        <ArrowLeft className="h-4 w-4" />
                        Quay lại
                    </Link>
                    <div className="flex-1">
                        <div className="flex items-center gap-3">
                            <h1 className="text-3xl font-bold text-gray-900">{admin.ho_ten}</h1>
                            {admin.is_master && (
                                <span className="inline-flex items-center gap-1.5 rounded-full bg-purple-100 px-3 py-1 text-sm font-medium text-purple-800">
                                    <Crown className="h-4 w-4" />
                                    Master Admin
                                </span>
                            )}
                            {admin.trang_thai === 1 ? (
                                <span className="inline-flex items-center gap-1.5 rounded-full bg-green-100 px-3 py-1 text-sm font-medium text-green-800">
                                    <CheckCircle2 className="h-4 w-4" />
                                    Hoạt động
                                </span>
                            ) : (
                                <span className="inline-flex items-center gap-1.5 rounded-full bg-red-100 px-3 py-1 text-sm font-medium text-red-800">
                                    <XCircle className="h-4 w-4" />
                                    Đã khóa
                                </span>
                            )}
                        </div>
                        <p className="mt-1 text-sm text-gray-500">
                            Chi tiết thông tin quản trị viên
                        </p>
                    </div>
                    <div className="flex gap-3">
                        <button
                            onClick={handleChangePassword}
                            disabled={processing}
                            className="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                        >
                            <Key className="h-4 w-4" />
                            Đổi mật khẩu
                        </button>
                        <Link
                            href={`/admin/admins/edit/${admin.id}`}
                            className="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
                        >
                            <Edit className="h-4 w-4" />
                            Chỉnh sửa
                        </Link>
                    </div>
                </div>

                {/* Admin Info */}
                <div className="grid gap-6 grid-cols-1 lg:grid-cols-3">
                    {/* Main Info */}
                    <div className="lg:col-span-2 space-y-6">
                        <div className="rounded-lg border border-gray-200 bg-white shadow-sm">
                            <div className="border-b border-gray-200 bg-gray-50 px-6 py-4">
                                <div className="flex items-center gap-2">
                                    <User className="h-5 w-5 text-blue-600" />
                                    <h2 className="text-lg font-semibold text-gray-900">Thông tin cơ bản</h2>
                                </div>
                            </div>
                            <div className="p-6 space-y-4">
                                <div className="flex items-start gap-3">
                                    <Mail className="h-5 w-5 text-gray-400 mt-0.5" />
                                    <div>
                                        <p className="text-sm font-medium text-gray-500">Email</p>
                                        <p className="mt-1 text-sm text-gray-900">{admin.email}</p>
                                    </div>
                                </div>
                                <div className="flex items-start gap-3">
                                    <User className="h-5 w-5 text-gray-400 mt-0.5" />
                                    <div>
                                        <p className="text-sm font-medium text-gray-500">Tên đăng nhập</p>
                                        <p className="mt-1 text-sm font-mono text-gray-900">{admin.ten_dang_nhap}</p>
                                    </div>
                                </div>
                                {admin.so_dien_thoai && (
                                    <div className="flex items-start gap-3">
                                        <Phone className="h-5 w-5 text-gray-400 mt-0.5" />
                                        <div>
                                            <p className="text-sm font-medium text-gray-500">Số điện thoại</p>
                                            <p className="mt-1 text-sm text-gray-900">{admin.so_dien_thoai}</p>
                                        </div>
                                    </div>
                                )}
                                <div className="flex items-start gap-3">
                                    <Shield className="h-5 w-5 text-gray-400 mt-0.5" />
                                    <div>
                                        <p className="text-sm font-medium text-gray-500">Vai trò</p>
                                        {admin.vai_tro ? (
                                            <div className="mt-1">
                                                <p className="text-sm font-medium text-gray-900">{admin.vai_tro.ten_vai_tro}</p>
                                                <p className="text-xs font-mono text-gray-500">{admin.vai_tro.slug}</p>
                                            </div>
                                        ) : (
                                            <p className="mt-1 text-sm text-gray-400">Chưa có vai trò</p>
                                        )}
                                    </div>
                                </div>
                                <div className="flex items-start gap-3">
                                    <Clock className="h-5 w-5 text-gray-400 mt-0.5" />
                                    <div>
                                        <p className="text-sm font-medium text-gray-500">Đăng nhập cuối</p>
                                        <p className="mt-1 text-sm text-gray-900">
                                            {admin.lan_dang_nhap_cuoi || 'Chưa đăng nhập'}
                                        </p>
                                    </div>
                                </div>
                                <div className="flex items-start gap-3">
                                    <Calendar className="h-5 w-5 text-gray-400 mt-0.5" />
                                    <div>
                                        <p className="text-sm font-medium text-gray-500">Ngày tạo</p>
                                        <p className="mt-1 text-sm text-gray-900">{admin.created_at}</p>
                                    </div>
                                </div>
                                <div className="flex items-start gap-3">
                                    <Calendar className="h-5 w-5 text-gray-400 mt-0.5" />
                                    <div>
                                        <p className="text-sm font-medium text-gray-500">Cập nhật cuối</p>
                                        <p className="mt-1 text-sm text-gray-900">{admin.updated_at}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Recent Activity */}
                    <div className="lg:col-span-1">
                        <div className="rounded-lg border border-gray-200 bg-white shadow-sm">
                            <div className="border-b border-gray-200 bg-gray-50 px-6 py-4">
                                <div className="flex items-center gap-2">
                                    <Activity className="h-5 w-5 text-blue-600" />
                                    <h2 className="text-lg font-semibold text-gray-900">Hoạt động gần đây</h2>
                                </div>
                            </div>
                            <div className="p-6">
                                {recentLogs.length > 0 ? (
                                    <div className="space-y-4">
                                        {recentLogs.slice(0, 10).map((log) => (
                                            <div key={log.id} className="border-l-2 border-blue-200 pl-4">
                                                <div className="flex items-start justify-between gap-2">
                                                    {getActionBadge(log.hanh_dong)}
                                                    <span className="text-xs text-gray-500 whitespace-nowrap">{log.created_at}</span>
                                                </div>
                                                <p className="mt-1 text-sm text-gray-700">{log.doi_tuong}</p>
                                                <p className="mt-0.5 text-xs text-gray-500">{log.dia_chi_ip}</p>
                                            </div>
                                        ))}
                                    </div>
                                ) : (
                                    <p className="text-sm text-gray-500 text-center py-4">Chưa có hoạt động nào</p>
                                )}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
