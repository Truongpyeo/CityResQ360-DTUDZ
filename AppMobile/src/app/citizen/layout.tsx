import type { ReactNode } from "react";
import PublicHeader from "@/components/common/PublicHeader";

export default function CitizenLayout({ children }: { children: ReactNode }) {
  return (
    <div className="min-h-screen bg-gray-50 text-gray-900">
      <PublicHeader />
      <main className="mx-auto max-w-5xl px-4 pt-24 pb-12 sm:px-6 lg:px-8">
        {children}
      </main>
      <footer className="border-t border-gray-200 bg-white px-4 py-6 text-center text-sm text-gray-500">
        Cổng công dân CityResQ360
      </footer>
    </div>
  );
}
