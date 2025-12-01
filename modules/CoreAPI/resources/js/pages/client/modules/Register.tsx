import React, { FormEvent, useState } from 'react';
import { router } from '@inertiajs/react';
import { ArrowLeft, CheckCircle, Info } from 'lucide-react';

interface Module {
    id: number;
    module_key: string;
    module_name: string;
    description: string;
    base_url: string;
    documentation_url: string;
    default_max_storage_mb: number;
    default_max_requests_per_day: number;
}

interface Props {
    module: Module;
}

export default function Register({ module }: Props) {
    const [formData, setFormData] = useState({
        app_name: '',
        app_domain: '',
        purpose: '',
        requested_max_storage_mb: module.default_max_storage_mb || 1000,
        requested_max_requests_per_day: module.default_max_requests_per_day || 10000,
    });

    const [errors, setErrors] = useState<any>({});
    const [submitting, setSubmitting] = useState(false);

    const handleSubmit = (e: FormEvent) => {
        e.preventDefault();
        setSubmitting(true);

        router.post(`/client/modules/${module.module_key}/register`, formData, {
            onError: (errors) => {
                setErrors(errors);
                setSubmitting(false);
            },
            onSuccess: () => {
                setSubmitting(false);
            }
        });
    };

    const handleChange = (field: string, value: string | number) => {
        setFormData(prev => ({ ...prev, [field]: value }));
        if (errors[field]) {
            setErrors((prev: any) => ({ ...prev, [field]: null }));
        }
    };

    return (
        <div className="min-h-screen bg-gray-50">
            {/* Header */}
            <header className="bg-white shadow">
                <div className="max-w-4xl mx-auto px-4 py-6">
                    <div className="flex items-center gap-4">
                        <a href="/client/modules" className="text-gray-600 hover:text-gray-900">
                            <ArrowLeft size={24} />
                        </a>
                        <div>
                            <h1 className="text-3xl font-bold text-gray-900">Đăng Ký Module</h1>
                            <p className="text-gray-600 mt-1">{module.module_name}</p>
                        </div>
                    </div>
                </div>
            </header>

            <main className="max-w-4xl mx-auto px-4 py-8">
                <div className="grid lg:grid-cols-3 gap-8">
                    {/* Form */}
                    <div className="lg:col-span-2">
                        <div className="bg-white rounded-lg shadow-sm border p-6">
                            <h2 className="text-xl font-semibold text-gray-900 mb-6">Thông Tin Đăng Ký</h2>

                            <form onSubmit={handleSubmit} className="space-y-5">
                                {/* App Name */}
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-2">
                                        Tên Ứng Dụng <span className="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        value={formData.app_name}
                                        onChange={(e) => handleChange('app_name', e.target.value)}
                                        className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        placeholder="Ví dụ: My App"
                                        required
                                    />
                                    {errors.app_name && <p className="mt-1 text-sm text-red-600">{errors.app_name}</p>}
                                </div>

                                {/* App Domain */}
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-2">
                                        Domain <span className="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        value={formData.app_domain}
                                        onChange={(e) => handleChange('app_domain', e.target.value)}
                                        className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        placeholder="Ví dụ: myapp.com hoặc localhost:3000"
                                        required
                                    />
                                    {errors.app_domain && <p className="mt-1 text-sm text-red-600">{errors.app_domain}</p>}
                                    <p className="mt-1 text-sm text-gray-500">Domain của ứng dụng cần tích hợp module này</p>
                                </div>

                                {/* Purpose */}
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-2">
                                        Mục Đích Sử Dụng <span className="text-red-500">*</span>
                                    </label>
                                    <textarea
                                        value={formData.purpose}
                                        onChange={(e) => handleChange('purpose', e.target.value)}
                                        rows={4}
                                        className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        placeholder="Mô tả chi tiết mục đích sử dụng module..."
                                        required
                                    />
                                    {errors.purpose && <p className="mt-1 text-sm text-red-600">{errors.purpose}</p>}
                                </div>

                                {/* Storage Quota */}
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-2">
                                        Dung Lượng Lưu Trữ (MB)
                                    </label>
                                    <input
                                        type="number"
                                        value={formData.requested_max_storage_mb}
                                        onChange={(e) => handleChange('requested_max_storage_mb', parseInt(e.target.value))}
                                        className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        min="100"
                                    />
                                    <p className="mt-1 text-sm text-gray-500">Mặc định: {module.default_max_storage_mb} MB</p>
                                </div>

                                {/* Requests Quota */}
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-2">
                                        Số Request Tối Đa / Ngày
                                    </label>
                                    <input
                                        type="number"
                                        value={formData.requested_max_requests_per_day}
                                        onChange={(e) => handleChange('requested_max_requests_per_day', parseInt(e.target.value))}
                                        className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        min="1000"
                                    />
                                    <p className="mt-1 text-sm text-gray-500">Mặc định: {module.default_max_requests_per_day.toLocaleString()} requests/day</p>
                                </div>

                                {/* Submit */}
                                <div className="flex gap-4 pt-4">
                                    <button
                                        type="submit"
                                        disabled={submitting}
                                        className="flex-1 bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed font-medium"
                                    >
                                        {submitting ? 'Đang gửi...' : 'Gửi Yêu Cầu'}
                                    </button>
                                    <a
                                        href="/client/modules"
                                        className="px-6 py-3 border border-gray-300 rounded-lg hover:bg-gray-50 text-gray-700"
                                    >
                                        Hủy
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>

                    {/* Info Sidebar */}
                    <div className="space-y-6">
                        {/* Module Info */}
                        <div className="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <div className="flex items-start gap-3">
                                <Info className="text-blue-600 flex-shrink-0 mt-0.5" size={20} />
                                <div>
                                    <h3 className="font-semibold text-blue-900 mb-2">Quy Trình Duyệt</h3>
                                    <ol className="text-sm text-blue-800 space-y-2">
                                        <li className="flex items-start gap-2">
                                            <span className="font-medium">1.</span>
                                            <span>Gửi form đăng ký</span>
                                        </li>
                                        <li className="flex items-start gap-2">
                                            <span className="font-medium">2.</span>
                                            <span>Admin xem xét (24-48h)</span>
                                        </li>
                                        <li className="flex items-start gap-2">
                                            <span className="font-medium">3.</span>
                                            <span>Nhận credentials qua email</span>
                                        </li>
                                        <li className="flex items-start gap-2">
                                            <span className="font-medium">4.</span>
                                            <span>Tích hợp vào app</span>
                                        </li>
                                    </ol>
                                </div>
                            </div>
                        </div>

                        {/* Benefits */}
                        <div className="bg-white rounded-lg shadow-sm border p-4">
                            <h3 className="font-semibold text-gray-900 mb-3">Lợi Ích</h3>
                            <ul className="space-y-2 text-sm text-gray-600">
                                <li className="flex items-start gap-2">
                                    <CheckCircle className="text-green-600 flex-shrink-0 mt-0.5" size={16} />
                                    <span>API endpoint riêng</span>
                                </li>
                                <li className="flex items-start gap-2">
                                    <CheckCircle className="text-green-600 flex-shrink-0 mt-0.5" size={16} />
                                    <span>JWT authentication</span>
                                </li>
                                <li className="flex items-start gap-2">
                                    <CheckCircle className="text-green-600 flex-shrink-0 mt-0.5" size={16} />
                                    <span>Quota management</span>
                                </li>
                                <li className="flex items-start gap-2">
                                    <CheckCircle className="text-green-600 flex-shrink-0 mt-0.5" size={16} />
                                    <span>Usage analytics</span>
                                </li>
                            </ul>
                        </div>

                        {/* Documentation Link */}
                        {module.documentation_url && (
                            <a
                                href={module.documentation_url}
                                target="_blank"
                                rel="noopener noreferrer"
                                className="block bg-gray-100 rounded-lg p-4 hover:bg-gray-200 transition-colors"
                            >
                                <p className="font-medium text-gray-900">📚 Xem Tài Liệu</p>
                                <p className="text-sm text-gray-600 mt-1">Hướng dẫn tích hợp chi tiết</p>
                            </a>
                        )}
                    </div>
                </div>
            </main>
        </div>
    );
}
