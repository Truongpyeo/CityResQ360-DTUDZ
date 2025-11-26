import type { ReactNode } from "react";
import Link from "next/link";

const adminLinks = [
  { label: "Dashboard", href: "/admin/dashboard" },
  { label: "Người dùng", href: "/admin/users" },
  { label: "Cơ quan", href: "/admin/agencies" },
  { label: "Danh mục", href: "/admin/categories" },
  { label: "Khu vực", href: "/admin/areas" },
  { label: "Alert Rules", href: "/admin/alert-rules" },
  { label: "Nhật ký", href: "/admin/logs" },
];

export default function AdminLayout({ children }: { children: ReactNode }) {
  return (
    <div className="min-h-screen bg-slate-50 text-slate-900">
      <div className="flex min-h-screen">
        <aside className="w-64 border-r border-slate-200 bg-white">
          <div className="border-b border-slate-200 px-6 py-6">
            <p className="text-sm font-semibold text-emerald-600">
              CityResQ360
            </p>
            <h2 className="text-xl font-bold">Admin Control</h2>
            <p className="mt-2 text-sm text-slate-500">
              Thiết lập hệ thống và phân quyền.
            </p>
          </div>
          <nav className="space-y-1 px-4 py-6">
            {adminLinks.map((item) => (
              <Link
                key={item.href}
                href={item.href}
                className="block rounded-lg px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-100 hover:text-slate-900"
              >
                {item.label}
              </Link>
            ))}
          </nav>
        </aside>

        <div className="flex min-h-screen flex-1 flex-col">
          <header className="border-b border-slate-200 bg-white px-6 py-4">
            <p className="text-sm font-semibold text-slate-500">
              Trung tâm cấu hình
            </p>
            <h1 className="text-2xl font-bold text-slate-900">
              Quản trị toàn hệ thống
            </h1>
            <p className="text-sm text-slate-500">
              Theo dõi quyền truy cập, nhật ký và cấu hình vùng.
            </p>
          </header>
          <main className="flex-1 px-6 py-6">{children}</main>
        </div>
      </div>
    </div>
  );
}
