import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AdminLayout from '../layouts/AdminLayout';
import { Building2, Search, Plus } from 'lucide-react';

export default function AgenciesIndex() {
  return (
    <AdminLayout>
      <Head title="Quản lý cơ quan - Admin" />

      <div className="space-y-6">
        {/* Header */}
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold text-gray-900">Quản lý cơ quan xử lý</h1>
            <p className="mt-1 text-sm text-gray-500">
              Quản lý các cơ quan phụ trách xử lý phản ánh
            </p>
          </div>
          <Link
            href="/admin/agencies/create"
            className="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700"
          >
            <Plus className="mr-2 h-5 w-5" />
            Thêm cơ quan
          </Link>
        </div>

        {/* Search */}
        <div className="rounded-lg border bg-white p-6">
          <div className="relative">
            <Search className="absolute left-3 top-2.5 h-5 w-5 text-gray-400" />
            <input
              type="text"
              placeholder="Tìm kiếm cơ quan..."
              className="block w-full rounded-md border border-gray-300 pl-10 pr-3 py-2 focus:border-blue-500 focus:ring-blue-500"
            />
          </div>
        </div>

        {/* Agencies Table */}
        <div className="rounded-lg border bg-white">
          <div className="border-b px-6 py-4">
            <h3 className="text-lg font-semibold text-gray-900">Danh sách cơ quan</h3>
          </div>
          <div className="p-6">
            <div className="text-center py-12">
              <Building2 className="mx-auto h-12 w-12 text-gray-400" />
              <h3 className="mt-2 text-sm font-medium text-gray-900">Đang phát triển</h3>
              <p className="mt-1 text-sm text-gray-500">
                Trang này đang được phát triển. Vui lòng quay lại sau.
              </p>
            </div>
          </div>
        </div>
      </div>
    </AdminLayout>
  );
}
