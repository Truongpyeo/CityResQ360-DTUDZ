import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AdminLayout from '../layouts/AdminLayout';
import { ArrowLeft, Building2 } from 'lucide-react';
import { showSuccess, showError } from '@/utils/notifications';

export default function AgencyCreate() {
    const [form, setForm] = useState({
        ten_co_quan: '',
        email_lien_he: '',
        so_dien_thoai: '',
        dia_chi: '',
        cap_do: '0',
        mo_ta: '',
        trang_thai: '1',
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();

        if (!form.ten_co_quan || !form.email_lien_he) {
            showError('Vui lòng nhập đầy đủ thông tin bắt buộc!');
            return;
        }

        router.post('/admin/agencies', form, {
            onSuccess: () => {
                showSuccess('Tạo cơ quan thành công!');
            },
            onError: (errors) => {
                const firstError = Object.values(errors)[0] as string;
                showError(firstError || 'Không thể tạo cơ quan!');
            },
        });
    };

    return (
        <AdminLayout>
            <Head title="Thêm cơ quan xử lý - Admin" />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex items-center gap-4">
                    <Link
                        href="/admin/agencies"
                        className="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                    >
                        <ArrowLeft className="h-4 w-4" />
                        Quay lại
                    </Link>
                    <div>
                        <h1 className="text-2xl font-bold text-gray-900">Thêm cơ quan xử lý</h1>
                        <p className="mt-1 text-sm text-gray-500">Tạo mới cơ quan phụ trách xử lý phản ánh</p>
                    </div>
                </div>

                {/* Form */}
                <div className="rounded-lg border border-gray-200 bg-white shadow-sm">
                    <div className="border-b border-gray-200 px-6 py-4">
                        <div className="flex items-center gap-2">
                            <Building2 className="h-5 w-5 text-gray-600" />
                            <h3 className="text-lg font-semibold text-gray-900">Thông tin cơ quan</h3>
                        </div>
                    </div>

                    <form onSubmit={handleSubmit} className="px-6 py-6">
                        <div className="grid gap-6 md:grid-cols-2">
                            {/* Tên cơ quan */}
                            <div className="md:col-span-2">
                                <label className="block text-sm font-medium text-gray-700 mb-2">
                                    Tên cơ quan <span className="text-red-600">*</span>
                                </label>
                                <input
                                    type="text"
                                    value={form.ten_co_quan}
                                    onChange={(e) => setForm({ ...form, ten_co_quan: e.target.value })}
                                    placeholder="Nhập tên cơ quan..."
                                    maxLength={200}
                                    className="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-20"
                                    required
                                />
                            </div>

                            {/* Email */}
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-2">
                                    Email liên hệ <span className="text-red-600">*</span>
                                </label>
                                <input
                                    type="email"
                                    value={form.email_lien_he}
                                    onChange={(e) => setForm({ ...form, email_lien_he: e.target.value })}
                                    placeholder="email@example.com"
                                    maxLength={100}
                                    className="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-20"
                                    required
                                />
                            </div>

                            {/* Số điện thoại */}
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-2">Số điện thoại</label>
                                <input
                                    type="tel"
                                    value={form.so_dien_thoai}
                                    onChange={(e) => setForm({ ...form, so_dien_thoai: e.target.value })}
                                    placeholder="0123456789"
                                    maxLength={15}
                                    className="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-20"
                                />
                            </div>

                            {/* Cấp độ */}
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-2">
                                    Cấp độ <span className="text-red-600">*</span>
                                </label>
                                <select
                                    value={form.cap_do}
                                    onChange={(e) => setForm({ ...form, cap_do: e.target.value })}
                                    className="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-20"
                                    required
                                >
                                    <option value="0">Phường/Xã</option>
                                    <option value="1">Quận/Huyện</option>
                                    <option value="2">Thành phố</option>
                                </select>
                            </div>

                            {/* Trạng thái */}
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-2">
                                    Trạng thái <span className="text-red-600">*</span>
                                </label>
                                <select
                                    value={form.trang_thai}
                                    onChange={(e) => setForm({ ...form, trang_thai: e.target.value })}
                                    className="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-20"
                                    required
                                >
                                    <option value="1">Hoạt động</option>
                                    <option value="0">Không hoạt động</option>
                                </select>
                            </div>

                            {/* Địa chỉ */}
                            <div className="md:col-span-2">
                                <label className="block text-sm font-medium text-gray-700 mb-2">Địa chỉ</label>
                                <input
                                    type="text"
                                    value={form.dia_chi}
                                    onChange={(e) => setForm({ ...form, dia_chi: e.target.value })}
                                    placeholder="Nhập địa chỉ cơ quan..."
                                    maxLength={300}
                                    className="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-20"
                                />
                            </div>

                            {/* Mô tả */}
                            <div className="md:col-span-2">
                                <label className="block text-sm font-medium text-gray-700 mb-2">Mô tả</label>
                                <textarea
                                    value={form.mo_ta}
                                    onChange={(e) => setForm({ ...form, mo_ta: e.target.value })}
                                    placeholder="Nhập mô tả về cơ quan..."
                                    rows={4}
                                    maxLength={500}
                                    className="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-20"
                                />
                                <p className="mt-1 text-xs text-gray-500">{form.mo_ta.length}/500 ký tự</p>
                            </div>
                        </div>

                        {/* Actions */}
                        <div className="mt-6 flex justify-end gap-3 border-t border-gray-200 pt-6">
                            <Link
                                href="/admin/agencies"
                                className="rounded-lg border border-gray-300 bg-white px-6 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50"
                            >
                                Hủy
                            </Link>
                            <button
                                type="submit"
                                className="rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-medium text-white hover:bg-blue-700"
                            >
                                Tạo cơ quan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </AdminLayout>
    );
}
