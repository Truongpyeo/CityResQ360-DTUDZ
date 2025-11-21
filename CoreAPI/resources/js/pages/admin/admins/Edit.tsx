import React, { useState, FormEvent } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AdminLayout from '../layouts/AdminLayout';
import { ArrowLeft, Save, Shield, Crown } from 'lucide-react';
import { showSuccess, showError } from '@/utils/notifications';

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
    id_vai_tro: number;
    trang_thai: number;
    is_master: boolean;
}

interface Props {
    admin: Admin;
    roles: VaiTro[];
}

interface FormData {
    ho_ten: string;
    email: string;
    so_dien_thoai: string;
    id_vai_tro: number;
    trang_thai: number;
}

interface Errors {
    ho_ten?: string;
    email?: string;
    so_dien_thoai?: string;
    id_vai_tro?: string;
    trang_thai?: string;
}

export default function Edit({ admin, roles }: Props) {
    const [formData, setFormData] = useState<FormData>({
        ho_ten: admin.ho_ten,
        email: admin.email,
        so_dien_thoai: admin.so_dien_thoai || '',
        id_vai_tro: admin.id_vai_tro,
        trang_thai: admin.trang_thai,
    });

    const [errors, setErrors] = useState<Errors>({});
    const [processing, setProcessing] = useState(false);

    const handleInputChange = (field: keyof FormData, value: string | number) => {
        setFormData(prev => ({
            ...prev,
            [field]: value,
        }));

        // Clear error when user types
        if (errors[field as keyof Errors]) {
            setErrors(prev => ({
                ...prev,
                [field]: undefined,
            }));
        }
    };

    const handleSubmit = (e: FormEvent) => {
        e.preventDefault();
        setProcessing(true);

        router.patch(`/admin/admins/update/${admin.id}`, {
            ho_ten: formData.ho_ten,
            email: formData.email,
            so_dien_thoai: formData.so_dien_thoai,
            id_vai_tro: formData.id_vai_tro,
            trang_thai: formData.trang_thai,
        }, {
            onSuccess: () => {
                showSuccess('Cập nhật quản trị viên thành công!');
            },
            onError: (errors) => {
                setErrors(errors as Errors);
                const firstError = Object.values(errors)[0] as string;
                showError(firstError || 'Có lỗi xảy ra!');
            },
            onFinish: () => {
                setProcessing(false);
            },
        });
    };

    return (
        <AdminLayout>
            <Head title={`Chỉnh sửa ${admin.ho_ten} - Admin`} />

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
                            <h1 className="text-3xl font-bold text-gray-900">Chỉnh sửa quản trị viên</h1>
                            {admin.is_master && (
                                <span className="inline-flex items-center gap-1.5 rounded-full bg-purple-100 px-3 py-1 text-sm font-medium text-purple-800">
                                    <Crown className="h-4 w-4" />
                                    Master Admin
                                </span>
                            )}
                        </div>
                        <p className="mt-1 text-sm text-gray-500">
                            Cập nhật thông tin cho {admin.ho_ten}
                        </p>
                    </div>
                </div>

                {/* Form */}
                <div className="rounded-lg border border-gray-200 bg-white shadow-sm">
                    <div className="border-b border-gray-200 bg-gray-50 px-6 py-4">
                        <div className="flex items-center gap-2">
                            <Shield className="h-5 w-5 text-blue-600" />
                            <h2 className="text-lg font-semibold text-gray-900">Thông tin quản trị viên</h2>
                        </div>
                    </div>

                    <form onSubmit={handleSubmit} className="p-6 space-y-6">
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {/* Họ tên */}
                            <div>
                                <label htmlFor="ho_ten" className="block text-sm font-medium text-gray-700 mb-2">
                                    Họ và tên <span className="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    id="ho_ten"
                                    value={formData.ho_ten}
                                    onChange={(e) => handleInputChange('ho_ten', e.target.value)}
                                    className={`block w-full rounded-lg border ${
                                        errors.ho_ten ? 'border-red-300' : 'border-gray-300'
                                    } px-4 py-3 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-20`}
                                    required
                                />
                                {errors.ho_ten && (
                                    <p className="mt-1 text-sm text-red-600">{errors.ho_ten}</p>
                                )}
                            </div>

                            {/* Email */}
                            <div>
                                <label htmlFor="email" className="block text-sm font-medium text-gray-700 mb-2">
                                    Email <span className="text-red-500">*</span>
                                </label>
                                <input
                                    type="email"
                                    id="email"
                                    value={formData.email}
                                    onChange={(e) => handleInputChange('email', e.target.value)}
                                    className={`block w-full rounded-lg border ${
                                        errors.email ? 'border-red-300' : 'border-gray-300'
                                    } px-4 py-3 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-20`}
                                    required
                                />
                                {errors.email && (
                                    <p className="mt-1 text-sm text-red-600">{errors.email}</p>
                                )}
                            </div>

                            {/* Tên đăng nhập (readonly) */}
                            <div>
                                <label htmlFor="ten_dang_nhap" className="block text-sm font-medium text-gray-700 mb-2">
                                    Tên đăng nhập
                                </label>
                                <input
                                    type="text"
                                    id="ten_dang_nhap"
                                    value={admin.ten_dang_nhap}
                                    className="block w-full rounded-lg border border-gray-300 bg-gray-100 px-4 py-3 text-sm font-mono text-gray-600 cursor-not-allowed"
                                    disabled
                                    readOnly
                                />
                                <p className="mt-1 text-xs text-gray-500">
                                    Tên đăng nhập không thể thay đổi
                                </p>
                            </div>

                            {/* Số điện thoại */}
                            <div>
                                <label htmlFor="so_dien_thoai" className="block text-sm font-medium text-gray-700 mb-2">
                                    Số điện thoại
                                </label>
                                <input
                                    type="text"
                                    id="so_dien_thoai"
                                    value={formData.so_dien_thoai}
                                    onChange={(e) => handleInputChange('so_dien_thoai', e.target.value)}
                                    placeholder="0987654321"
                                    className={`block w-full rounded-lg border ${
                                        errors.so_dien_thoai ? 'border-red-300' : 'border-gray-300'
                                    } px-4 py-3 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-20`}
                                />
                                {errors.so_dien_thoai && (
                                    <p className="mt-1 text-sm text-red-600">{errors.so_dien_thoai}</p>
                                )}
                            </div>

                            {/* Vai trò */}
                            <div>
                                <label htmlFor="id_vai_tro" className="block text-sm font-medium text-gray-700 mb-2">
                                    Vai trò <span className="text-red-500">*</span>
                                </label>
                                <select
                                    id="id_vai_tro"
                                    value={formData.id_vai_tro}
                                    onChange={(e) => handleInputChange('id_vai_tro', parseInt(e.target.value))}
                                    disabled={admin.is_master}
                                    className={`block w-full rounded-lg border ${
                                        admin.is_master ? 'bg-gray-100 cursor-not-allowed' : ''
                                    } ${
                                        errors.id_vai_tro ? 'border-red-300' : 'border-gray-300'
                                    } px-4 py-3 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-20`}
                                    required
                                >
                                    {roles.map((role) => (
                                        <option key={role.id} value={role.id}>
                                            {role.ten_vai_tro}
                                        </option>
                                    ))}
                                </select>
                                {admin.is_master && (
                                    <p className="mt-1 text-xs text-gray-500">
                                        Không thể thay đổi vai trò của Master Admin
                                    </p>
                                )}
                                {errors.id_vai_tro && (
                                    <p className="mt-1 text-sm text-red-600">{errors.id_vai_tro}</p>
                                )}
                            </div>

                            {/* Trạng thái */}
                            <div>
                                <label htmlFor="trang_thai" className="block text-sm font-medium text-gray-700 mb-2">
                                    Trạng thái <span className="text-red-500">*</span>
                                </label>
                                <select
                                    id="trang_thai"
                                    value={formData.trang_thai}
                                    onChange={(e) => handleInputChange('trang_thai', parseInt(e.target.value))}
                                    className={`block w-full rounded-lg border ${
                                        errors.trang_thai ? 'border-red-300' : 'border-gray-300'
                                    } px-4 py-3 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-20`}
                                >
                                    <option value={1}>Hoạt động</option>
                                    <option value={0}>Đã khóa</option>
                                </select>
                                {errors.trang_thai && (
                                    <p className="mt-1 text-sm text-red-600">{errors.trang_thai}</p>
                                )}
                            </div>
                        </div>

                        {/* Action Buttons */}
                        <div className="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                            <Link
                                href="/admin/admins"
                                className="rounded-lg border border-gray-300 bg-white px-6 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors"
                            >
                                Hủy bỏ
                            </Link>
                            <button
                                type="submit"
                                disabled={processing}
                                className={`inline-flex items-center gap-2 rounded-lg px-6 py-3 text-sm font-medium text-white transition-colors ${
                                    processing
                                        ? 'bg-blue-400 cursor-not-allowed'
                                        : 'bg-blue-600 hover:bg-blue-700'
                                }`}
                            >
                                <Save className="h-4 w-4" />
                                {processing ? 'Đang lưu...' : 'Lưu thay đổi'}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </AdminLayout>
    );
}
