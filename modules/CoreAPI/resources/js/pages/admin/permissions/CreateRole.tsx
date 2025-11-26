import React, { useState, FormEvent } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AdminLayout from '../layouts/AdminLayout';
import { ArrowLeft, Shield, Save } from 'lucide-react';
import { showSuccess, showError } from '@/utils/notifications';

interface FormData {
    ten_vai_tro: string;
    slug: string;
    mo_ta: string;
    trang_thai: number;
}

interface Errors {
    ten_vai_tro?: string;
    slug?: string;
    mo_ta?: string;
    trang_thai?: string;
}

export default function CreateRole() {
    const [formData, setFormData] = useState<FormData>({
        ten_vai_tro: '',
        slug: '',
        mo_ta: '',
        trang_thai: 1,
    });

    const [errors, setErrors] = useState<Errors>({});
    const [processing, setProcessing] = useState(false);

    const generateSlug = (text: string): string => {
        return text
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/đ/g, 'd')
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .replace(/^-+|-+$/g, '');
    };

    const handleInputChange = (field: keyof FormData, value: string | number) => {
        setFormData(prev => ({
            ...prev,
            [field]: value,
        }));

        // Auto-generate slug from name
        if (field === 'ten_vai_tro' && typeof value === 'string') {
            const newSlug = generateSlug(value);
            setFormData(prev => ({
                ...prev,
                slug: newSlug,
            }));
        }

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

        router.post('/admin/permissions/roles', {
            ten_vai_tro: formData.ten_vai_tro,
            slug: formData.slug,
            mo_ta: formData.mo_ta,
            trang_thai: formData.trang_thai,
        }, {
            onSuccess: () => {
                showSuccess('Tạo vai trò thành công!');
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
            <Head title="Thêm vai trò mới - Admin" />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex items-center gap-4">
                    <Link
                        href="/admin/permissions/roles"
                        className="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                    >
                        <ArrowLeft className="h-4 w-4" />
                        Quay lại
                    </Link>
                    <div>
                        <h1 className="text-3xl font-bold text-gray-900">Thêm vai trò mới</h1>
                        <p className="mt-1 text-sm text-gray-500">
                            Tạo vai trò mới cho hệ thống
                        </p>
                    </div>
                </div>

                {/* Form */}
                <div className="rounded-lg border border-gray-200 bg-white shadow-sm">
                    <div className="border-b border-gray-200 bg-gray-50 px-6 py-4">
                        <div className="flex items-center gap-2">
                            <Shield className="h-5 w-5 text-blue-600" />
                            <h2 className="text-lg font-semibold text-gray-900">Thông tin vai trò</h2>
                        </div>
                    </div>

                    <form onSubmit={handleSubmit} className="p-6 space-y-6">
                        {/* Tên vai trò */}
                        <div>
                            <label htmlFor="ten_vai_tro" className="block text-sm font-medium text-gray-700 mb-2">
                                Tên vai trò <span className="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                id="ten_vai_tro"
                                value={formData.ten_vai_tro}
                                onChange={(e) => handleInputChange('ten_vai_tro', e.target.value)}
                                placeholder="Ví dụ: Quản trị viên hệ thống"
                                className={`block w-full rounded-lg border ${
                                    errors.ten_vai_tro ? 'border-red-300' : 'border-gray-300'
                                } px-4 py-3 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-20`}
                                required
                            />
                            {errors.ten_vai_tro && (
                                <p className="mt-1 text-sm text-red-600">{errors.ten_vai_tro}</p>
                            )}
                        </div>

                        {/* Slug */}
                        <div>
                            <label htmlFor="slug" className="block text-sm font-medium text-gray-700 mb-2">
                                Slug <span className="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                id="slug"
                                value={formData.slug}
                                placeholder="quan-tri-vien-he-thong"
                                className="block w-full rounded-lg border border-gray-300 bg-gray-100 px-4 py-3 text-sm font-mono text-gray-600 cursor-not-allowed"
                                disabled
                                readOnly
                            />
                            <p className="mt-1 text-xs text-gray-500">
                                Slug được tự động tạo từ tên vai trò
                            </p>
                            {errors.slug && (
                                <p className="mt-1 text-sm text-red-600">{errors.slug}</p>
                            )}
                        </div>

                        {/* Mô tả */}
                        <div>
                            <label htmlFor="mo_ta" className="block text-sm font-medium text-gray-700 mb-2">
                                Mô tả
                            </label>
                            <textarea
                                id="mo_ta"
                                value={formData.mo_ta}
                                onChange={(e) => handleInputChange('mo_ta', e.target.value)}
                                placeholder="Mô tả vai trò này..."
                                rows={4}
                                className={`block w-full rounded-lg border ${
                                    errors.mo_ta ? 'border-red-300' : 'border-gray-300'
                                } px-4 py-3 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-20`}
                            />
                            {errors.mo_ta && (
                                <p className="mt-1 text-sm text-red-600">{errors.mo_ta}</p>
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

                        {/* Action Buttons */}
                        <div className="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                            <Link
                                href="/admin/permissions/roles"
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
                                {processing ? 'Đang lưu...' : 'Lưu vai trò'}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </AdminLayout>
    );
}
