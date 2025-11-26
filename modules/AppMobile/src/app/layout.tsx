import type { Metadata } from "next";
import { Inter } from "next/font/google";
import "./globals.css";
import ClientProviders from "@/providers/ClientProviders";

const inter = Inter({ subsets: ["latin"] });

export const metadata: Metadata = {
  title: "CityResQ360 - Bản đồ sống xanh & Giáo dục môi trường",
  description: "Nền tảng giáo dục môi trường tích hợp bản đồ 3D hiển thị AQI, năng lượng xanh, và các hành động xanh được đề xuất bởi AI",
  icons: {
    icon: '/favicon.ico',
  },
};

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html lang="vi">
      <body className={inter.className}>
        <ClientProviders>{children}</ClientProviders>
      </body>
    </html>
  );
}
