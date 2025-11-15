import React from 'react';
import { Head } from '@inertiajs/react';
import AdminLayout from './layouts/AdminLayout';
import { Settings as SettingsIcon } from 'lucide-react';

export default function Settings() {
  return (
    <AdminLayout>
      <Head title="Cài đặt - Admin" />

      <div className="space-y-6">
        {/* Header */}
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold text-gray-900">Cài đặt hệ thống</h1>
            <p className="mt-1 text-sm text-gray-500">
              Cấu hình và thiết lập hệ thống
            </p>
          </div>
        </div>

        {/* Settings Content */}
        <div className="rounded-lg border bg-white">
          <div className="border-b px-6 py-4">
            <h3 className="text-lg font-semibold text-gray-900">Cài đặt chung</h3>
          </div>
          <div className="p-6">
            <div className="text-center py-12">
              <SettingsIcon className="mx-auto h-12 w-12 text-gray-400" />
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
