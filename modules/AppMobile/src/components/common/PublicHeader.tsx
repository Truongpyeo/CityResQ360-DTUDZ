"use client";

import Link from "next/link";
import { useState, useEffect } from "react";
import { motion } from "framer-motion";
import { usePathname } from "next/navigation";

export default function PublicHeader() {
  const [scrolled, setScrolled] = useState(false);
  const pathname = usePathname();

  useEffect(() => {
    const handleScroll = () => {
      setScrolled(window.scrollY > 20);
    };
    
    window.addEventListener("scroll", handleScroll);
    return () => {
      window.removeEventListener("scroll", handleScroll);
    };
  }, []);

  const navItems = [
    { name: "Bản đồ", href: "/map" },
    { name: "Chiến dịch", href: "/actions" },
    { name: "Demo AI", href: "/ai-demo" },
  ];

  return (
    <motion.nav
      initial={{ y: -100 }}
      animate={{ y: 0 }}
      className={`fixed top-0 left-0 right-0 z-50 transition-all duration-500 ${
        scrolled || pathname !== "/"
          ? "bg-white/80 dark:bg-gray-950/80 backdrop-blur-xl shadow-theme-lg border-b border-gray-200/50 dark:border-gray-800/50"
          : "bg-transparent"
      }`}
    >
      <div className="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div className="flex items-center justify-between h-16 sm:h-20">
          <Link href="/" className="flex items-center gap-2">
            <motion.div 
              whileHover={{ scale: 1.05 }}
            >
              <img src="/images/logo/logo.svg" alt="CityResQ360 Logo" className="h-8 w-auto" />
            </motion.div>
          </Link>
          <div className="hidden gap-8 md:flex">
            {navItems.map((item, index) => (
              <motion.a
                key={item.name}
                href={item.href}
                className={`text-sm font-medium transition-colors relative group ${
                  pathname === item.href
                    ? "text-brand-600 dark:text-brand-400"
                    : "text-gray-700 hover:text-brand-600 dark:text-gray-300 dark:hover:text-brand-400"
                }`}
                initial={{ opacity: 0, y: -20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ delay: index * 0.1 }}
              >
                {item.name}
                <span className={`absolute bottom-0 left-0 h-0.5 bg-gradient-to-r from-brand-500 to-brand-600 transition-all ${
                  pathname === item.href ? "w-full" : "w-0 group-hover:w-full"
                }`} />
              </motion.a>
            ))}
          </div>
          <motion.div 
            className="flex items-center gap-3"
            initial={{ opacity: 0, x: 20 }}
            animate={{ opacity: 1, x: 0 }}
            transition={{ delay: 0.3 }}
          >
            <Link
              href="/login"
              className="px-4 py-2 text-sm font-medium text-gray-700 transition-all rounded-lg hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800/50"
            >
              Đăng nhập
            </Link>
            <Link
              href="/register"
              className="px-4 py-2 text-sm font-medium text-white transition-all rounded-lg bg-gradient-to-r from-brand-500 to-brand-600 hover:shadow-lg hover:shadow-brand-500/50 hover:scale-105"
            >
              Đăng ký
            </Link>
          </motion.div>
        </div>
      </div>
    </motion.nav>
  );
}

