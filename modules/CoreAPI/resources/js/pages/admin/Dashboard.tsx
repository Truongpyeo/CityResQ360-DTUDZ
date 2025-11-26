import React from 'react';
import { Head, Link, usePage } from '@inertiajs/react';
import {
  LayoutDashboard,
  FileText,
  Users,
  Building2,
  Settings,
  TrendingUp,
  AlertCircle,
  CheckCircle,
  Clock
} from 'lucide-react';
import AdminLayout from './layouts/AdminLayout';

interface DashboardProps {
  admin: {
    id: number;
    ten_quan_tri: string;
    email: string;
    vai_tro: number;
    vai_tro_text: string;
    anh_dai_dien: string | null;
  };
  stats: {
    total_reports: number;
    pending_reports: number;
    in_progress_reports: number;
    resolved_reports: number;
    total_users: number;
    verified_users: number;
    total_agencies: number;
    active_agencies: number;
  };
  reportsByCategory: Array<{ category: string; total: number }>;
  reportsByStatus: Array<{ status: string; total: number }>;
  recentReports: Array<{
    id: number;
    tieu_de: string;
    danh_muc: string;
    trang_thai: string;
    nguoi_dung: string;
    co_quan: string | null;
    created_at: string;
  }>;
  reportsTrend: Array<{ date: string; total: number }>;
}

export default function Dashboard() {
  const { admin, stats, reportsByCategory, reportsByStatus, recentReports, reportsTrend } = usePage<DashboardProps>().props;

  return (
    <AdminLayout>
      <Head title="Admin Dashboard - CityResQ360" />

      <div className="space-y-6">
        {/* Header */}
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold text-gray-900">Dashboard</h1>
            <p className="mt-1 text-sm text-gray-500">
              Xin chào, {admin.ten_quan_tri} ({admin.vai_tro_text})
            </p>
          </div>
        </div>

        {/* Stats Cards */}
        <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
          <StatsCard
            title="Tổng phản ánh"
            value={stats.total_reports}
            icon={<FileText className="h-6 w-6" />}
            color="blue"
          />
          <StatsCard
            title="Chờ xử lý"
            value={stats.pending_reports}
            icon={<Clock className="h-6 w-6" />}
            color="yellow"
          />
          <StatsCard
            title="Đang xử lý"
            value={stats.in_progress_reports}
            icon={<AlertCircle className="h-6 w-6" />}
            color="orange"
          />
          <StatsCard
            title="Đã giải quyết"
            value={stats.resolved_reports}
            icon={<CheckCircle className="h-6 w-6" />}
            color="green"
          />
        </div>

        {/* Secondary Stats */}
        <div className="grid gap-6 md:grid-cols-3">
          <div className="rounded-lg border bg-white p-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm font-medium text-gray-600">Người dùng</p>
                <p className="mt-2 text-3xl font-bold text-gray-900">{stats.total_users}</p>
                <p className="mt-1 text-sm text-green-600">
                  {stats.verified_users} đã xác thực
                </p>
              </div>
              <Users className="h-10 w-10 text-blue-500" />
            </div>
          </div>

          <div className="rounded-lg border bg-white p-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm font-medium text-gray-600">Cơ quan xử lý</p>
                <p className="mt-2 text-3xl font-bold text-gray-900">{stats.total_agencies}</p>
                <p className="mt-1 text-sm text-green-600">
                  {stats.active_agencies} đang hoạt động
                </p>
              </div>
              <Building2 className="h-10 w-10 text-purple-500" />
            </div>
          </div>

          <div className="rounded-lg border bg-white p-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm font-medium text-gray-600">Tỷ lệ giải quyết</p>
                <p className="mt-2 text-3xl font-bold text-gray-900">
                  {stats.total_reports > 0
                    ? Math.round((stats.resolved_reports / stats.total_reports) * 100)
                    : 0}%
                </p>
                <p className="mt-1 text-sm text-gray-500">
                  {stats.resolved_reports}/{stats.total_reports} phản ánh
                </p>
              </div>
              <TrendingUp className="h-10 w-10 text-green-500" />
            </div>
          </div>
        </div>

        {/* Charts */}
        <div className="grid gap-6 lg:grid-cols-2">
          {/* Reports by Category */}
          <div className="rounded-lg border bg-white p-6">
            <h3 className="text-lg font-semibold text-gray-900">Phản ánh theo danh mục</h3>
            <div className="mt-4 space-y-3">
              {reportsByCategory.map((item) => (
                <div key={item.category}>
                  <div className="flex items-center justify-between text-sm">
                    <span className="text-gray-600">{item.category}</span>
                    <span className="font-semibold text-gray-900">{item.total}</span>
                  </div>
                  <div className="mt-1 h-2 w-full rounded-full bg-gray-200">
                    <div
                      className="h-2 rounded-full bg-blue-500"
                      style={{
                        width: `${(item.total / stats.total_reports) * 100}%`,
                      }}
                    />
                  </div>
                </div>
              ))}
            </div>
          </div>

          {/* Reports by Status */}
          <div className="rounded-lg border bg-white p-6">
            <h3 className="text-lg font-semibold text-gray-900">Phản ánh theo trạng thái</h3>
            <div className="mt-4 space-y-3">
              {reportsByStatus.map((item) => (
                <div key={item.status}>
                  <div className="flex items-center justify-between text-sm">
                    <span className="text-gray-600">{item.status}</span>
                    <span className="font-semibold text-gray-900">{item.total}</span>
                  </div>
                  <div className="mt-1 h-2 w-full rounded-full bg-gray-200">
                    <div
                      className="h-2 rounded-full bg-green-500"
                      style={{
                        width: `${(item.total / stats.total_reports) * 100}%`,
                      }}
                    />
                  </div>
                </div>
              ))}
            </div>
          </div>
        </div>

        {/* Recent Reports Table */}
        <div className="rounded-lg border bg-white">
          <div className="border-b px-6 py-4">
            <h3 className="text-lg font-semibold text-gray-900">Phản ánh gần đây</h3>
          </div>
          <div className="overflow-x-auto">
            <table className="w-full">
              <thead className="border-b bg-gray-50">
                <tr>
                  <th className="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">STT</th>
                  <th className="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Tiêu đề</th>
                  <th className="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Danh mục</th>
                  <th className="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Trạng thái</th>
                  <th className="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Người dùng</th>
                  <th className="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Thời gian</th>
                  <th className="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Thao tác</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-200">
                {recentReports.map((report, index) => (
                  <tr key={report.id} className="hover:bg-gray-50">
                    <td className="px-6 py-4 text-sm text-gray-900">{index + 1}</td>
                    <td className="px-6 py-4 text-sm text-gray-900">{report.tieu_de}</td>
                    <td className="px-6 py-4 text-sm">
                      <span className="inline-flex rounded-full bg-blue-100 px-2 py-1 text-xs font-medium text-blue-800">
                        {report.danh_muc}
                      </span>
                    </td>
                    <td className="px-6 py-4 text-sm">
                      <StatusBadge status={report.trang_thai} />
                    </td>
                    <td className="px-6 py-4 text-sm text-gray-600">{report.nguoi_dung}</td>
                    <td className="px-6 py-4 text-sm text-gray-500">{report.created_at}</td>
                    <td className="px-6 py-4 text-sm">
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
          </div>
        </div>
      </div>
    </AdminLayout>
  );
}

// Stats Card Component
function StatsCard({ title, value, icon, color }: {
  title: string;
  value: number;
  icon: React.ReactNode;
  color: 'blue' | 'yellow' | 'orange' | 'green';
}) {
  const colorClasses = {
    blue: 'bg-blue-50 text-blue-600',
    yellow: 'bg-yellow-50 text-yellow-600',
    orange: 'bg-orange-50 text-orange-600',
    green: 'bg-green-50 text-green-600',
  };

  return (
    <div className="rounded-lg border bg-white p-6">
      <div className="flex items-center justify-between">
        <div>
          <p className="text-sm font-medium text-gray-600">{title}</p>
          <p className="mt-2 text-3xl font-bold text-gray-900">{value.toLocaleString()}</p>
        </div>
        <div className={`rounded-full p-3 ${colorClasses[color]}`}>
          {icon}
        </div>
      </div>
    </div>
  );
}

// Status Badge Component
function StatusBadge({ status }: { status: string }) {
  const statusColors: Record<string, string> = {
    'Chờ xử lý': 'bg-yellow-100 text-yellow-800',
    'Đã xác minh': 'bg-blue-100 text-blue-800',
    'Đang xử lý': 'bg-orange-100 text-orange-800',
    'Đã giải quyết': 'bg-green-100 text-green-800',
    'Từ chối': 'bg-red-100 text-red-800',
  };

  return (
    <span className={`inline-flex rounded-full px-2 py-1 text-xs font-medium ${statusColors[status] || 'bg-gray-100 text-gray-800'}`}>
      {status}
    </span>
  );
}
