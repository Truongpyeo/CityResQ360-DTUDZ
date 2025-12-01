import React, { FormEvent, useState } from 'react';
import { router } from '@inertiajs/react';

export default function Register() {
    const [formData, setFormData] = useState({
        ho_ten: '',
        email: '',
        so_dien_thoai: '',
        password: '',
        password_confirmation: '',
    });
    const [errors, setErrors] = useState<any>({});

    const handleSubmit = (e: FormEvent) => {
        e.preventDefault();

        router.post('/register', formData, {
            onError: (errors) => {
                setErrors(errors);
            }
        });
    };

    const handleChange = (field: string, value: string) => {
        setFormData(prev => ({ ...prev, [field]: value }));
    };

    return (
        <div className="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center px-4">
            <div className="max-w-md w-full">
                <div className="bg-white rounded-2xl shadow-xl p-8">
                    {/* Header */}
                    <div className="text-center mb-8">
                        <h1 className="text-3xl font-bold text-gray-900">CityResQ360</h1>
                        <p className="text-gray-600 mt-2">Create New Account</p>
                    </div>

                    {/* Register Form */}
                    <form onSubmit={handleSubmit} className="space-y-4">
                        <div>
                            <label htmlFor="ho_ten" className="block text-sm font-medium text-gray-700 mb-2">
                                Họ và Tên
                            </label>
                            <input
                                id="ho_ten"
                                type="text"
                                value={formData.ho_ten}
                                onChange={(e) => handleChange('ho_ten', e.target.value)}
                                className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="Nguyễn Văn A"
                                required
                            />
                            {errors.ho_ten && <p className="mt-1 text-sm text-red-600">{errors.ho_ten}</p>}
                        </div>

                        <div>
                            <label htmlFor="email" className="block text-sm font-medium text-gray-700 mb-2">
                                Email
                            </label>
                            <input
                                id="email"
                                type="email"
                                value={formData.email}
                                onChange={(e) => handleChange('email', e.target.value)}
                                className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="your@email.com"
                                required
                            />
                            {errors.email && <p className="mt-1 text-sm text-red-600">{errors.email}</p>}
                        </div>

                        <div>
                            <label htmlFor="so_dien_thoai" className="block text-sm font-medium text-gray-700 mb-2">
                                Số Điện Thoại
                            </label>
                            <input
                                id="so_dien_thoai"
                                type="tel"
                                value={formData.so_dien_thoai}
                                onChange={(e) => handleChange('so_dien_thoai', e.target.value)}
                                className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="0901234567"
                                required
                            />
                            {errors.so_dien_thoai && <p className="mt-1 text-sm text-red-600">{errors.so_dien_thoai}</p>}
                        </div>

                        <div>
                            <label htmlFor="password" className="block text-sm font-medium text-gray-700 mb-2">
                                Mật Khẩu
                            </label>
                            <input
                                id="password"
                                type="password"
                                value={formData.password}
                                onChange={(e) => handleChange('password', e.target.value)}
                                className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="••••••••"
                                required
                            />
                            {errors.password && <p className="mt-1 text-sm text-red-600">{errors.password}</p>}
                        </div>

                        <div>
                            <label htmlFor="password_confirmation" className="block text-sm font-medium text-gray-700 mb-2">
                                Xác Nhận Mật Khẩu
                            </label>
                            <input
                                id="password_confirmation"
                                type="password"
                                value={formData.password_confirmation}
                                onChange={(e) => handleChange('password_confirmation', e.target.value)}
                                className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="••••••••"
                                required
                            />
                        </div>

                        <button
                            type="submit"
                            className="w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 transition-colors font-medium"
                        >
                            Đăng Ký
                        </button>
                    </form>

                    {/* Login Link */}
                    <div className="mt-6 text-center">
                        <p className="text-gray-600">
                            Already have an account?{' '}
                            <a href="/login" className="text-blue-600 hover:text-blue-700 font-medium">
                                Login here
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    );
}
