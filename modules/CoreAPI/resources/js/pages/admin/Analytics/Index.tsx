import React, { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import AdminLayout from '../layouts/AdminLayout';
import {
    BarChart3,
    TrendingUp,
    TrendingDown,
    Calendar,
    Download,
    RefreshCw,
    FileText,
    Users,
    Building2,
    CheckCircle2,
    Clock,
    AlertCircle,
} from 'lucide-react';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    BarElement,
    ArcElement,
    Title,
    Tooltip,
    Legend,
    Filler,
} from 'chart.js';
import { Line, Bar, Pie } from 'react-chartjs-2';

// Register ChartJS components
ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    BarElement,
    ArcElement,
    Title,
    Tooltip,
    Legend,
    Filler
);

interface Stats {
    total_reports: number;
    total_users: number;
    total_agencies: number;
    resolved_reports: number;
    pending_reports: number;
    in_progress_reports: number;
    resolution_rate: number;
}

interface DailyTrend {
    date: string;
    count: number;
    resolved: number;
}

interface CategoryData {
    name: string;
    count: number;
}

interface AgencyPerformance {
    ten_co_quan: string;
    total_reports: number;
    resolved_reports: number;
    resolution_rate: number;
}

interface TopUser {
    ho_ten: string;
    email: string;
    total_reports: number;
    resolved_reports: number;
}

interface Props {
    stats: Stats;
    dailyTrends: DailyTrend[];
    reportsByCategory: CategoryData[];
    reportsByPriority: CategoryData[];
    agencyPerformance: AgencyPerformance[];
    topUsers: TopUser[];
    filters: {
        tu_ngay: string;
        den_ngay: string;
    };
}

export default function AnalyticsIndex({
    stats,
    dailyTrends,
    reportsByCategory,
    reportsByPriority,
    agencyPerformance,
    topUsers,
    filters,
}: Props) {
    const [startDate, setStartDate] = useState(filters.tu_ngay);
    const [endDate, setEndDate] = useState(filters.den_ngay);

    const handleFilter = () => {
        router.get('/admin/analytics', {
            tu_ngay: startDate,
            den_ngay: endDate,
        }, {
            preserveState: true,
        });
    };

    const handleRefresh = () => {
        router.reload();
    };

    // Daily trends line chart
    const dailyTrendsChart = {
        labels: dailyTrends.map(item => new Date(item.date).toLocaleDateString('vi-VN', { month: 'short', day: 'numeric' })),
        datasets: [
            {
                label: 'Tổng phản ánh',
                data: dailyTrends.map(item => item.count),
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                fill: true,
            },
            {
                label: 'Đã giải quyết',
                data: dailyTrends.map(item => item.resolved),
                borderColor: 'rgb(34, 197, 94)',
                backgroundColor: 'rgba(34, 197, 94, 0.1)',
                tension: 0.4,
                fill: true,
            },
        ],
    };

    // Category pie chart
    const categoryChart = {
        labels: reportsByCategory.map(item => item.name),
        datasets: [
            {
                data: reportsByCategory.map(item => item.count),
                backgroundColor: [
                    'rgba(59, 130, 246, 0.8)',
                    'rgba(34, 197, 94, 0.8)',
                    'rgba(251, 146, 60, 0.8)',
                    'rgba(168, 85, 247, 0.8)',
                    'rgba(236, 72, 153, 0.8)',
                    'rgba(14, 165, 233, 0.8)',
                ],
                borderWidth: 2,
                borderColor: '#fff',
            },
        ],
    };

    // Priority bar chart
    const priorityChart = {
        labels: reportsByPriority.map(item => item.name),
        datasets: [
            {
                label: 'Số lượng',
                data: reportsByPriority.map(item => item.count),
                backgroundColor: [
                    'rgba(239, 68, 68, 0.8)',
                    'rgba(251, 146, 60, 0.8)',
                    'rgba(59, 130, 246, 0.8)',
                    'rgba(156, 163, 175, 0.8)',
                ],
                borderWidth: 0,
            },
        ],
    };

    const chartOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom' as const,
            },
        },
    };

    return (
        <AdminLayout>
            <Head title="Phân tích & Thống kê - Admin" />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h1 className="text-3xl font-bold text-gray-900">Phân tích & Thống kê</h1>
                        <p className="mt-1 text-sm text-gray-500">
                            Báo cáo chi tiết về hiệu suất và xu hướng phản ánh
                        </p>
                    </div>
                    <div className="flex gap-3">
                        <button
                            onClick={handleRefresh}
                            className="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                        >
                            <RefreshCw className="h-4 w-4" />
                            Làm mới
                        </button>
                    </div>
                </div>

                {/* Date Filter */}
                <div className="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <div className="flex flex-wrap items-end gap-4">
                        <div className="flex-1 min-w-[200px]">
                            <label className="mb-1 block text-sm font-medium text-gray-700">
                                Từ ngày
                            </label>
                            <input
                                type="date"
                                value={startDate}
                                onChange={(e) => setStartDate(e.target.value)}
                                className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                            />
                        </div>
                        <div className="flex-1 min-w-[200px]">
                            <label className="mb-1 block text-sm font-medium text-gray-700">
                                Đến ngày
                            </label>
                            <input
                                type="date"
                                value={endDate}
                                onChange={(e) => setEndDate(e.target.value)}
                                className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                            />
                        </div>
                        <button
                            onClick={handleFilter}
                            className="rounded-lg bg-blue-600 px-6 py-2 text-sm font-medium text-white hover:bg-blue-700"
                        >
                            Áp dụng
                        </button>
                    </div>
                </div>

                {/* Stats Overview */}
                <div className="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-4">
                    <div className="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                        <div className="flex items-center justify-between">
                            <div className="flex-1">
                                <p className="text-sm font-medium text-gray-600">Tổng phản ánh</p>
                                <p className="mt-2 text-3xl font-bold text-gray-900">{stats.total_reports}</p>
                            </div>
                            <div className="rounded-full bg-blue-100 p-3">
                                <FileText className="h-6 w-6 text-blue-600" />
                            </div>
                        </div>
                    </div>

                    <div className="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                        <div className="flex items-center justify-between">
                            <div className="flex-1">
                                <p className="text-sm font-medium text-gray-600">Đã giải quyết</p>
                                <p className="mt-2 text-3xl font-bold text-green-600">{stats.resolved_reports}</p>
                                <p className="mt-1 text-xs text-green-600">{stats.resolution_rate}% tỷ lệ</p>
                            </div>
                            <div className="rounded-full bg-green-100 p-3">
                                <CheckCircle2 className="h-6 w-6 text-green-600" />
                            </div>
                        </div>
                    </div>

                    <div className="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                        <div className="flex items-center justify-between">
                            <div className="flex-1">
                                <p className="text-sm font-medium text-gray-600">Đang xử lý</p>
                                <p className="mt-2 text-3xl font-bold text-orange-600">{stats.in_progress_reports}</p>
                            </div>
                            <div className="rounded-full bg-orange-100 p-3">
                                <AlertCircle className="h-6 w-6 text-orange-600" />
                            </div>
                        </div>
                    </div>

                    <div className="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                        <div className="flex items-center justify-between">
                            <div className="flex-1">
                                <p className="text-sm font-medium text-gray-600">Chờ xử lý</p>
                                <p className="mt-2 text-3xl font-bold text-yellow-600">{stats.pending_reports}</p>
                            </div>
                            <div className="rounded-full bg-yellow-100 p-3">
                                <Clock className="h-6 w-6 text-yellow-600" />
                            </div>
                        </div>
                    </div>
                </div>

                {/* Charts Row 1 */}
                <div className="grid gap-6 lg:grid-cols-2">
                    {/* Daily Trends */}
                    <div className="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                        <div className="mb-4 flex items-center justify-between">
                            <h2 className="text-lg font-semibold text-gray-900">Xu hướng theo ngày</h2>
                            <BarChart3 className="h-5 w-5 text-gray-400" />
                        </div>
                        <div className="h-[300px]">
                            <Line data={dailyTrendsChart} options={chartOptions} />
                        </div>
                    </div>

                    {/* Category Distribution */}
                    <div className="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                        <div className="mb-4 flex items-center justify-between">
                            <h2 className="text-lg font-semibold text-gray-900">Phân bố theo danh mục</h2>
                        </div>
                        <div className="h-[300px]">
                            <Pie data={categoryChart} options={chartOptions} />
                        </div>
                    </div>
                </div>

                {/* Charts Row 2 */}
                <div className="grid gap-6 lg:grid-cols-2">
                    {/* Priority Distribution */}
                    <div className="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                        <div className="mb-4 flex items-center justify-between">
                            <h2 className="text-lg font-semibold text-gray-900">Phân bố theo mức ưu tiên</h2>
                        </div>
                        <div className="h-[300px]">
                            <Bar data={priorityChart} options={chartOptions} />
                        </div>
                    </div>

                    {/* Agency Performance */}
                    <div className="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                        <div className="mb-4 flex items-center justify-between">
                            <h2 className="text-lg font-semibold text-gray-900">Top 10 cơ quan hiệu suất cao</h2>
                            <Building2 className="h-5 w-5 text-gray-400" />
                        </div>
                        <div className="space-y-3 max-h-[300px] overflow-y-auto">
                            {agencyPerformance.map((agency, index) => (
                                <div key={index} className="flex items-center justify-between rounded-lg bg-gray-50 p-3">
                                    <div className="flex-1">
                                        <p className="font-medium text-gray-900">{agency.ten_co_quan}</p>
                                        <p className="text-xs text-gray-600">
                                            {agency.resolved_reports}/{agency.total_reports} đã giải quyết
                                        </p>
                                    </div>
                                    <div className="flex items-center gap-2">
                                        <span className="font-semibold text-green-600">{agency.resolution_rate}%</span>
                                        {agency.resolution_rate >= 80 ? (
                                            <TrendingUp className="h-4 w-4 text-green-600" />
                                        ) : agency.resolution_rate >= 50 ? (
                                            <TrendingDown className="h-4 w-4 text-yellow-600" />
                                        ) : (
                                            <TrendingDown className="h-4 w-4 text-red-600" />
                                        )}
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>
                </div>

                {/* Top Users Table */}
                <div className="rounded-lg border border-gray-200 bg-white shadow-sm">
                    <div className="border-b border-gray-200 p-6">
                        <div className="flex items-center justify-between">
                            <h2 className="text-lg font-semibold text-gray-900">Top 10 người dùng tích cực</h2>
                            <Users className="h-5 w-5 text-gray-400" />
                        </div>
                    </div>
                    <div className="overflow-x-auto">
                        <table className="w-full">
                            <thead className="bg-gray-50">
                                <tr>
                                    <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        #
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Người dùng
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Email
                                    </th>
                                    <th className="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Tổng phản ánh
                                    </th>
                                    <th className="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Đã giải quyết
                                    </th>
                                    <th className="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Tỷ lệ
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-200 bg-white">
                                {topUsers.map((user, index) => (
                                    <tr key={index} className="hover:bg-gray-50">
                                        <td className="px-6 py-4 text-sm text-gray-900">
                                            {index + 1}
                                        </td>
                                        <td className="px-6 py-4">
                                            <div className="font-medium text-gray-900">{user.ho_ten}</div>
                                        </td>
                                        <td className="px-6 py-4 text-sm text-gray-600">
                                            {user.email}
                                        </td>
                                        <td className="px-6 py-4 text-right text-sm font-medium text-gray-900">
                                            {user.total_reports}
                                        </td>
                                        <td className="px-6 py-4 text-right text-sm font-medium text-green-600">
                                            {user.resolved_reports}
                                        </td>
                                        <td className="px-6 py-4 text-right text-sm font-semibold text-gray-900">
                                            {user.total_reports > 0
                                                ? Math.round((user.resolved_reports / user.total_reports) * 100)
                                                : 0}%
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
