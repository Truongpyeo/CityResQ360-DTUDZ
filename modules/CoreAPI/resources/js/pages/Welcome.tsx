import React from 'react';
import { Link } from '@inertiajs/react';
import { Shield, Zap, Users, ArrowRight, CheckCircle } from 'lucide-react';

export default function Welcome() {
    return (
        <div className="min-h-screen bg-gradient-to-br from-blue-50 via-white to-indigo-50">
            {/* Header */}
            <header className="bg-white/80 backdrop-blur-sm border-b border-gray-200 sticky top-0 z-50">
                <div className="max-w-7xl mx-auto px-4 py-4">
                    <div className="flex items-center justify-between">
                        <div className="flex items-center gap-2">
                            <Shield className="text-blue-600" size={32} />
                            <span className="text-2xl font-bold text-gray-900">CityResQ360</span>
                        </div>
                        <div className="flex items-center gap-3">
                            <a href="/login" className="px-4 py-2 text-gray-700 hover:text-gray-900">
                                Đăng Nhập
                            </a>
                            <a href="/register" className="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                Đăng Ký
                            </a>
                        </div>
                    </div>
                </div>
            </header>

            {/* Hero Section */}
            <section className="max-w-7xl mx-auto px-4 py-20">
                <div className="text-center">
                    <h1 className="text-5xl md:text-6xl font-bold text-gray-900 mb-6">
                        Smart City Emergency
                        <span className="block text-blue-600 mt-2">Response System</span>
                    </h1>
                    <p className="text-xl text-gray-600 mb-8 max-w-3xl mx-auto">
                        Hệ thống API Platform tích hợp các dịch vụ thông minh cho ứng dụng của bạn.
                        Kết nối, quản lý và mở rộng với các module mạnh mẽ.
                    </p>
                    <div className="flex items-center justify-center gap-4">
                        <a
                            href="/register"
                            className="inline-flex items-center gap-2 px-8 py-4 bg-blue-600 text-white text-lg rounded-lg hover:bg-blue-700 transition-colors shadow-lg hover:shadow-xl"
                        >
                            Bắt Đầu Miễn Phí
                            <ArrowRight size={20} />
                        </a>
                        <a
                            href="/documents"
                            className="inline-flex items-center gap-2 px-8 py-4 bg-white text-gray-700 text-lg rounded-lg hover:bg-gray-50 transition-colors border border-gray-300"
                        >
                            Xem API Docs
                        </a>
                    </div>
                </div>
            </section>

            {/* Features */}
            <section className="max-w-7xl mx-auto px-4 py-16">
                <div className="grid md:grid-cols-3 gap-8">
                    <div className="bg-white rounded-2xl p-8 shadow-sm hover:shadow-md transition-shadow">
                        <div className="w-14 h-14 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                            <Zap className="text-blue-600" size={28} />
                        </div>
                        <h3 className="text-xl font-semibold text-gray-900 mb-3">Tích Hợp Nhanh</h3>
                        <p className="text-gray-600">
                            API đơn giản, tài liệu đầy đủ. Bắt đầu trong vài phút với SDK và code examples sẵn có.
                        </p>
                    </div>

                    <div className="bg-white rounded-2xl p-8 shadow-sm hover:shadow-md transition-shadow">
                        <div className="w-14 h-14 bg-green-100 rounded-lg flex items-center justify-center mb-4">
                            <Shield className="text-green-600" size={28} />
                        </div>
                        <h3 className="text-xl font-semibold text-gray-900 mb-3">Bảo Mật Cao</h3>
                        <p className="text-gray-600">
                            Mã hóa end-to-end, JWT authentication, quota management và access control chặt chẽ.
                        </p>
                    </div>

                    <div className="bg-white rounded-2xl p-8 shadow-sm hover:shadow-md transition-shadow">
                        <div className="w-14 h-14 bg-purple-100 rounded-lg flex items-center justify-center mb-4">
                            <Users className="text-purple-600" size={28} />
                        </div>
                        <h3 className="text-xl font-semibold text-gray-900 mb-3">Hỗ Trợ 24/7</h3>
                        <p className="text-gray-600">
                            Đội ngũ support nhiệt tình, documentation chi tiết và community active sẵn sàng hỗ trợ.
                        </p>
                    </div>
                </div>
            </section>

            {/* Available Modules */}
            <section className="max-w-7xl mx-auto px-4 py-16">
                <div className="text-center mb-12">
                    <h2 className="text-3xl font-bold text-gray-900 mb-4">Available API Modules</h2>
                    <p className="text-gray-600">Các dịch vụ mạnh mẽ sẵn sàng cho ứng dụng của bạn</p>
                </div>

                <div className="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                    {[
                        { name: 'Media Service', desc: 'Upload, resize & optimize images', status: 'Active' },
                        { name: 'Notification', desc: 'Push, Email & SMS notifications', status: 'Coming Soon' },
                        { name: 'Wallet Service', desc: 'Payment & transaction management', status: 'Coming Soon' },
                        { name: 'Analytics', desc: 'Real-time data & insights', status: 'Coming Soon' },
                    ].map((module, idx) => (
                        <div key={idx} className="bg-white rounded-xl p-6 border border-gray-200 hover:border-blue-300 transition-colors">
                            <div className="flex items-center justify-between mb-3">
                                <h3 className="font-semibold text-gray-900">{module.name}</h3>
                                {module.status === 'Active' && (
                                    <span className="px-2 py-1 bg-green-100 text-green-700 text-xs rounded-full flex items-center gap-1">
                                        <CheckCircle size={12} />
                                        Active
                                    </span>
                                )}
                            </div>
                            <p className="text-sm text-gray-600">{module.desc}</p>
                        </div>
                    ))}
                </div>
            </section>

            {/* CTA */}
            <section className="max-w-7xl mx-auto px-4 py-16">
                <div className="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-3xl p-12 text-center text-white">
                    <h2 className="text-3xl font-bold mb-4">Sẵn sàng bắt đầu?</h2>
                    <p className="text-xl mb-8 text-blue-100">Đăng ký miễn phí và tích hợp API trong vài phút</p>
                    <a
                        href="/register"
                        className="inline-flex items-center gap-2 px-8 py-4 bg-white text-blue-600 text-lg rounded-lg hover:bg-gray-100 transition-colors font-semibold"
                    >
                        Tạo Tài Khoản Ngay
                        <ArrowRight size={20} />
                    </a>
                </div>
            </section>

            {/* Footer */}
            <footer className="bg-gray-900 text-gray-400 py-12">
                <div className="max-w-7xl mx-auto px-4">
                    <div className="flex items-center justify-between">
                        <div className="flex items-center gap-2">
                            <Shield className="text-blue-500" size={24} />
                            <span className="text-white font-semibold">CityResQ360</span>
                        </div>
                        <div className="flex gap-6">
                            <a href="/documents" className="hover:text-white transition-colors">Documentation</a>
                            <a href="/admin/login" className="hover:text-white transition-colors text-sm opacity-50">Admin</a>
                        </div>
                    </div>
                    <div className="mt-8 pt-8 border-t border-gray-800 text-center text-sm">
                        <p>© 2025 CityResQ360. Smart City Emergency Response System.</p>
                    </div>
                </div>
            </footer>
        </div>
    );
}
