import { useForm } from '@inertiajs/react';
import { FormEvent } from 'react';
import { AlertCircle, Building2, Lock, Mail, Shield } from 'lucide-react';

export default function Login() {
    const { data, setData, post, processing, errors } = useForm({
        email: '',
        mat_khau: '',
        remember: false,
    });

    const handleSubmit = (e: FormEvent) => {
        e.preventDefault();
        post('/admin/login');
    };

    return (
        <div className="min-h-screen bg-gradient-to-br from-[#0d90d1] via-[#0c7db8] to-[#0a6a9f] flex items-center justify-center p-4 relative overflow-hidden">
            {/* Background Pattern */}
            <div className="absolute inset-0 opacity-10">
                <div className="absolute top-0 left-0 w-full h-full"
                     style={{
                         backgroundImage: `url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 800 800'%3E%3Cg fill='none' stroke='%23ffffff' stroke-width='2'%3E%3Cpath d='M769 229L1037 260.9M927 880L731 737 520 660 309 538 40 599 295 764 126.5 879.5 40 599-197 493 102 382-31 229 126.5 79.5-69-63'/%3E%3Cpath d='M-31 229L237 261 390 382 603 493 308.5 537.5 101.5 381.5M370 905L295 764'/%3E%3Cpath d='M520 660L578 842 731 737 840 599 603 493 520 660 295 764 309 538 390 382 539 269 769 229 577.5 41.5 370 105 295 -36 126.5 79.5 237 261 102 382 40 599 -69 737 127 880'/%3E%3Cpath d='M520-140L578.5 42.5 731-63M603 493L539 269 237 261 370 105M902 382L539 269M390 382L102 382'/%3E%3Cpath d='M-222 42L126.5 79.5 370 105 539 269 577.5 41.5 927 80 769 229 902 382 603 493 731 737M295-36L577.5 41.5M578 842L295 764M40-201L127 80M102 382L-261 269'/%3E%3C/g%3E%3Cg fill='%23ffffff'%3E%3Ccircle cx='769' cy='229' r='5'/%3E%3Ccircle cx='539' cy='269' r='5'/%3E%3Ccircle cx='603' cy='493' r='5'/%3E%3Ccircle cx='731' cy='737' r='5'/%3E%3Ccircle cx='520' cy='660' r='5'/%3E%3Ccircle cx='309' cy='538' r='5'/%3E%3Ccircle cx='295' cy='764' r='5'/%3E%3Ccircle cx='40' cy='599' r='5'/%3E%3Ccircle cx='102' cy='382' r='5'/%3E%3Ccircle cx='127' cy='80' r='5'/%3E%3Ccircle cx='370' cy='105' r='5'/%3E%3Ccircle cx='578' cy='42' r='5'/%3E%3Ccircle cx='237' cy='261' r='5'/%3E%3Ccircle cx='390' cy='382' r='5'/%3E%3C/g%3E%3C/svg%3E")`,
                         backgroundSize: '800px 800px',
                     }}
                />
            </div>

            <div className="max-w-md w-full relative z-10">
                {/* Logo & Title */}
                <div className="text-center mb-8 animate-fade-in">
                    <div className="inline-flex items-center justify-center w-20 h-20 bg-white rounded-2xl shadow-2xl mb-4 transform hover:scale-105 transition-transform duration-300 overflow-hidden">
                        <img
                            src="/logo.png"
                            alt="CityResQ360 Logo"
                            className="w-full h-full object-contain p-2"
                        />
                    </div>
                    <h1 className="text-4xl font-bold text-white mb-2 drop-shadow-lg">
                        CityResQ360
                    </h1>
                    <div className="inline-flex items-center gap-2 text-cyan-50 bg-white/10 backdrop-blur-sm px-4 py-2 rounded-full">
                        <Shield className="w-4 h-4" />
                        <span className="text-sm font-medium">Admin Dashboard</span>
                    </div>
                </div>

                {/* Login Card */}
                <div className="bg-white rounded-2xl shadow-2xl p-8 backdrop-blur-xl border border-white/20">
                    <div className="mb-6 text-center">
                        <h2 className="text-2xl font-bold text-gray-900 mb-1">
                            Đăng nhập
                        </h2>
                        <p className="text-gray-500 text-sm">
                            Hệ thống quản trị phản ánh đô thị
                        </p>
                    </div>

                    <form onSubmit={handleSubmit} className="space-y-5">
                        {/* Email Field */}
                        <div>
                            <label
                                htmlFor="email"
                                className="block text-sm font-semibold text-gray-700 mb-2"
                            >
                                Email hoặc Tên đăng nhập
                            </label>
                            <div className="relative">
                                <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <Mail className="h-5 w-5 text-gray-400" />
                                </div>
                                <input
                                    id="email"
                                    type="text"
                                    value={data.email}
                                    onChange={(e) => setData('email', e.target.value)}
                                    className={`block w-full pl-10 pr-3 py-3 border ${
                                        errors.email ? 'border-red-300 focus:ring-red-500' : 'border-gray-300 focus:ring-[#0d90d1]'
                                    } rounded-xl focus:ring-2 focus:border-transparent transition-all duration-200 text-gray-900 placeholder-gray-400`}
                                    placeholder="admin@cityresq360.com"
                                    required
                                    autoFocus
                                />
                            </div>
                            {errors.email && (
                                <div className="mt-2 flex items-start gap-2 text-sm text-red-600">
                                    <AlertCircle className="w-4 h-4 mt-0.5 flex-shrink-0" />
                                    <span>{errors.email}</span>
                                </div>
                            )}
                        </div>

                        {/* Password Field */}
                        <div>
                            <label
                                htmlFor="mat_khau"
                                className="block text-sm font-semibold text-gray-700 mb-2"
                            >
                                Mật khẩu
                            </label>
                            <div className="relative">
                                <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <Lock className="h-5 w-5 text-gray-400" />
                                </div>
                                <input
                                    id="mat_khau"
                                    type="password"
                                    value={data.mat_khau}
                                    onChange={(e) => setData('mat_khau', e.target.value)}
                                    className={`block w-full pl-10 pr-3 py-3 border ${
                                        errors.mat_khau ? 'border-red-300 focus:ring-red-500' : 'border-gray-300 focus:ring-[#0d90d1]'
                                    } rounded-xl focus:ring-2 focus:border-transparent transition-all duration-200 text-gray-900 placeholder-gray-400`}
                                    placeholder="••••••••••"
                                    required
                                />
                            </div>
                            {errors.mat_khau && (
                                <div className="mt-2 flex items-start gap-2 text-sm text-red-600">
                                    <AlertCircle className="w-4 h-4 mt-0.5 flex-shrink-0" />
                                    <span>{errors.mat_khau}</span>
                                </div>
                            )}
                        </div>

                        {/* Remember Me */}
                        <div className="flex items-center">
                            <input
                                id="remember"
                                type="checkbox"
                                checked={data.remember}
                                onChange={(e) => setData('remember', e.target.checked)}
                                className="w-4 h-4 text-[#0d90d1] border-gray-300 rounded focus:ring-[#0d90d1] focus:ring-2"
                            />
                            <label htmlFor="remember" className="ml-2 block text-sm text-gray-700">
                                Ghi nhớ đăng nhập
                            </label>
                        </div>

                        {/* Submit Button */}
                        <button
                            type="submit"
                            disabled={processing}
                            className="w-full bg-[#0d90d1] hover:bg-[#0c7db8] active:bg-[#0a6a9f] text-white font-semibold py-3.5 px-4 rounded-xl transition-all duration-200 flex items-center justify-center disabled:opacity-50 disabled:cursor-not-allowed shadow-lg shadow-[#0d90d1]/30 hover:shadow-xl hover:shadow-[#0d90d1]/40 transform hover:-translate-y-0.5"
                        >
                            {processing ? (
                                <span className="flex items-center">
                                    <svg
                                        className="animate-spin -ml-1 mr-3 h-5 w-5 text-white"
                                        xmlns="http://www.w3.org/2000/svg"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                    >
                                        <circle
                                            className="opacity-25"
                                            cx="12"
                                            cy="12"
                                            r="10"
                                            stroke="currentColor"
                                            strokeWidth="4"
                                        ></circle>
                                        <path
                                            className="opacity-75"
                                            fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                        ></path>
                                    </svg>
                                    Đang xử lý...
                                </span>
                            ) : (
                                <>
                                    <Shield className="w-5 h-5 mr-2" />
                                    Đăng nhập
                                </>
                            )}
                        </button>
                    </form>
                </div>

                {/* Footer */}
                <p className="text-center text-white/90 text-sm mt-6 drop-shadow-md">
                    © 2025 <span className="font-semibold">CityResQ360</span> - Hệ thống quản trị đô thị thông minh
                </p>
            </div>
        </div>
    );
}
