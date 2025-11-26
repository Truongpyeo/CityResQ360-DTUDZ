import type { ReactNode } from "react";
import Link from "next/link";

const navigation = [
  { label: "Dashboard", href: "/officer/dashboard" },
  { label: "Bản đồ", href: "/officer/map" },
  { label: "Sự cố", href: "/officer/incidents" },
  { label: "Báo cáo", href: "/officer/reports" },
  { label: "Phân công", href: "/officer/assignments" },
  { label: "IoT Sensors", href: "/officer/iot/sensors" },
  { label: "Thông báo", href: "/officer/notifications" },
  { label: "Thống kê", href: "/officer/statistics" },
];

export default function OfficerLayout({ children }: { children: ReactNode }) {
  return (
    <div className="min-h-screen bg-slate-50 text-slate-900">
      <div className="flex min-h-screen">
        <aside className="w-64 border-r border-slate-200 bg-white">
          <div className="border-b border-slate-200 px-6 py-6">
            <p className="text-sm font-semibold text-sky-600">CityResQ360</p>
            <h2 className="text-xl font-bold">Officer Console</h2>
            <p className="mt-2 text-sm text-slate-500">
              Điều phối lực lượng hiện trường.
            </p>
          </div>
          <nav className="space-y-1 px-4 py-6">
            {navigation.map((item) => (
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
              Trung tâm điều hành
            </p>
            <h1 className="text-2xl font-bold text-slate-900">
              Giám sát hiện trường
            </h1>
            <p className="text-sm text-slate-500">
              Theo dõi timeline sự cố, phân công và cảnh báo IoT.
            </p>
          </header>
          <main className="flex-1 px-6 py-6">{children}</main>
        </div>
      </div>
    </div>
  );
}
