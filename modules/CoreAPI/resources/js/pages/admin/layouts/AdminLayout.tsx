import React, { useState, useEffect } from 'react';
import { Link, router } from '@inertiajs/react';
import {
  LayoutDashboard,
  FileText,
  Users,
  Building2,
  Shield,
  UserCog,
  Settings,
  LogOut,
  Menu,
  X,
  ChevronDown,
  BarChart3,
  Cpu,
  Bell
} from 'lucide-react';
import Pusher from 'pusher-js';
import Swal from 'sweetalert2';

interface AdminLayoutProps {
  children: React.ReactNode;
}

export default function AdminLayout({ children }: AdminLayoutProps) {
  const [sidebarOpen, setSidebarOpen] = useState(false);
  const [userMenuOpen, setUserMenuOpen] = useState(false);
  const [notificationCount, setNotificationCount] = useState(0);
  const [wsConnected, setWsConnected] = useState(false);

  // WebSocket connection for realtime notifications (global for all admin pages)
  useEffect(() => {
    console.log('🔌 Initializing global WebSocket connection for Admin...');

    // Request notification permission
    if ('Notification' in window && Notification.permission === 'default') {
      Notification.requestPermission().then(permission => {
        console.log('Notification permission:', permission);
      });
    }

    // Initialize Pusher
    const pusher = new Pusher(import.meta.env.VITE_REVERB_APP_KEY || 'lwf6joghdvbowg9hb7p4', {
      wsHost: window.location.hostname,
      wsPort: 443,
      wssPort: 443,
      forceTLS: true,
      enabledTransports: ['ws', 'wss'],
      disableStats: true,
      cluster: 'mt1',
      authEndpoint: '/broadcasting/auth',
    });

    pusher.connection.bind('connected', () => {
      console.log('✅ WebSocket connected');
      setWsConnected(true);
    });

    pusher.connection.bind('disconnected', () => {
      console.log('❌ WebSocket disconnected');
      setWsConnected(false);
    });

    // Subscribe to admin-reports channel
    const channel = pusher.subscribe('admin-reports');

    channel.bind('pusher:subscription_succeeded', () => {
      console.log('✅ Subscribed to admin-reports');
    });

    channel.bind('new.report', (data: any) => {
      console.log('📢 New report:', data);

      setNotificationCount(prev => prev + 1);

      // Show SweetAlert2 popup
      Swal.fire({
        title: '📢 Phản ánh mới!',
        html: `
          <div style="text-align: left;">
            <p><strong>Từ:</strong> ${data.user?.ho_ten || 'N/A'}</p>
            <p><strong>Tiêu đề:</strong> ${data.report?.tieu_de || 'N/A'}</p>
            <p><strong>Danh mục:</strong> ${data.report?.danh_muc?.ten || 'N/A'}</p>
            <p><strong>Mô tả:</strong> ${(data.report?.mo_ta || '').substring(0, 100)}${(data.report?.mo_ta || '').length > 100 ? '...' : ''}</p>
          </div>
        `,
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: '👁️ Xem chi tiết',
        cancelButtonText: 'Đóng',
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
      }).then((result) => {
        if (result.isConfirmed) {
          router.visit(`/admin/reports/${data.report.id}`);
        }
      });

      // Play notification sound
      try {
        const audioContext = new (window.AudioContext || (window as any).webkitAudioContext)();
        const playTone = (frequency: number, startTime: number, duration: number) => {
          const oscillator = audioContext.createOscillator();
          const gainNode = audioContext.createGain();
          oscillator.connect(gainNode);
          gainNode.connect(audioContext.destination);
          oscillator.frequency.value = frequency;
          oscillator.type = 'sine';
          gainNode.gain.setValueAtTime(0.15, startTime);
          gainNode.gain.exponentialRampToValueAtTime(0.01, startTime + duration);
          oscillator.start(startTime);
          oscillator.stop(startTime + duration);
        };
        playTone(800, audioContext.currentTime, 0.15);
        playTone(1000, audioContext.currentTime + 0.2, 0.15);
      } catch (e) {
        console.log('Could not play sound');
      }

      // Browser notification
      if ('Notification' in window && Notification.permission === 'granted') {
        new Notification('Phản ánh mới!', {
          body: `${data.user?.ho_ten || 'Người dùng'}: ${data.report?.tieu_de || ''}`,
          icon: '/favicon.ico',
          tag: `report-${data.report.id}`,
        });
      }

      // Reload data if on dashboard or reports page
      if (window.location.pathname === '/admin/dashboard' || window.location.pathname === '/admin/reports') {
        setTimeout(() => router.reload({ only: ['stats', 'reports', 'recentReports'] }), 1000);
      }
    });

    return () => {
      console.log('🔌 Cleaning up WebSocket...');
      channel.unbind_all();
      pusher.unsubscribe('admin-reports');
      pusher.disconnect();
    };
  }, []);

  const navigation = [
    { name: 'Dashboard', href: '/admin/dashboard', icon: LayoutDashboard },
    { name: 'Quản lý phản ánh', href: '/admin/reports', icon: FileText },
    { name: 'Người dùng', href: '/admin/users', icon: Users },
    { name: 'Cơ quan xử lý', href: '/admin/agencies', icon: Building2 },
    { name: 'API Modules', href: '/admin/modules/all-requests', icon: Cpu },
    { name: 'Phân tích & Thống kê', href: '/admin/analytics', icon: BarChart3 },
    { name: 'Quản lý Admin', href: '/admin/admins', icon: UserCog },
    { name: 'Phân quyền', href: '/admin/permissions/roles', icon: Shield },
    { name: 'Cài đặt', href: '/admin/settings', icon: Settings },
  ];

  const handleLogout = () => {
    router.post('/admin/logout');
  };

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Sidebar for desktop */}
      <div className="hidden lg:fixed lg:inset-y-0 lg:z-50 lg:flex lg:w-72 lg:flex-col">
        <div className="flex grow flex-col gap-y-5 overflow-y-auto border-r border-gray-200 bg-white px-6 pb-4">
          {/* Logo */}
          <div className="flex h-16 shrink-0 items-center">
            <h1 className="text-2xl font-bold text-blue-600">CityResQ360</h1>
            <span className="ml-2 rounded bg-blue-100 px-2 py-1 text-xs font-semibold text-blue-800">
              Admin
            </span>
          </div>

          {/* Navigation */}
          <nav className="flex flex-1 flex-col">
            <ul role="list" className="flex flex-1 flex-col gap-y-7">
              <li>
                <ul role="list" className="-mx-2 space-y-1">
                  {navigation.map((item) => {
                    const isActive = window.location.pathname.startsWith(item.href);
                    return (
                      <li key={item.name}>
                        <Link
                          href={item.href}
                          className={`group flex gap-x-3 rounded-md p-2 text-sm font-semibold leading-6 ${isActive
                            ? 'bg-blue-50 text-blue-600'
                            : 'text-gray-700 hover:bg-gray-50 hover:text-blue-600'
                            }`}
                        >
                          <item.icon className="h-6 w-6 shrink-0" />
                          {item.name}
                        </Link>
                      </li>
                    );
                  })}
                </ul>
              </li>

              {/* Logout at bottom */}
              <li className="mt-auto">
                <button
                  onClick={handleLogout}
                  className="group -mx-2 flex w-full gap-x-3 rounded-md p-2 text-sm font-semibold leading-6 text-gray-700 hover:bg-gray-50 hover:text-red-600"
                >
                  <LogOut className="h-6 w-6 shrink-0" />
                  Đăng xuất
                </button>
              </li>
            </ul>
          </nav>
        </div>
      </div>

      {/* Mobile sidebar */}
      {sidebarOpen && (
        <div className="relative z-50 lg:hidden">
          <div className="fixed inset-0 bg-gray-900/80" onClick={() => setSidebarOpen(false)} />
          <div className="fixed inset-0 flex">
            <div className="relative mr-16 flex w-full max-w-xs flex-1">
              <div className="absolute left-full top-0 flex w-16 justify-center pt-5">
                <button onClick={() => setSidebarOpen(false)}>
                  <X className="h-6 w-6 text-white" />
                </button>
              </div>
              <div className="flex grow flex-col gap-y-5 overflow-y-auto bg-white px-6 pb-4">
                <div className="flex h-16 shrink-0 items-center">
                  <h1 className="text-2xl font-bold text-blue-600">CityResQ360</h1>
                </div>
                <nav className="flex flex-1 flex-col">
                  <ul role="list" className="flex flex-1 flex-col gap-y-7">
                    <li>
                      <ul role="list" className="-mx-2 space-y-1">
                        {navigation.map((item) => (
                          <li key={item.name}>
                            <Link
                              href={item.href}
                              className="group flex gap-x-3 rounded-md p-2 text-sm font-semibold leading-6 text-gray-700 hover:bg-gray-50 hover:text-blue-600"
                            >
                              <item.icon className="h-6 w-6 shrink-0" />
                              {item.name}
                            </Link>
                          </li>
                        ))}
                      </ul>
                    </li>
                  </ul>
                </nav>
              </div>
            </div>
          </div>
        </div>
      )}

      {/* Main content */}
      <div className="lg:pl-72">
        {/* Top bar */}
        <div className="sticky top-0 z-40 flex h-16 shrink-0 items-center gap-x-4 border-b border-gray-200 bg-white px-4 shadow-sm sm:gap-x-6 sm:px-6 lg:px-8">
          <button
            onClick={() => setSidebarOpen(true)}
            className="-m-2.5 p-2.5 text-gray-700 lg:hidden"
          >
            <Menu className="h-6 w-6" />
          </button>

          <div className="flex flex-1 gap-x-4 self-stretch lg:gap-x-6">
            <div className="flex flex-1"></div>
            <div className="flex items-center gap-x-4 lg:gap-x-6">
              {/* WebSocket status & Notification indicator */}
              <div className="flex items-center gap-x-2">
                <div className={`h-2 w-2 rounded-full ${wsConnected ? 'bg-green-500' : 'bg-red-500'}`}
                  title={wsConnected ? 'Connected' : 'Disconnected'} />
                <Link href="/admin/reports" className="relative">
                  <Bell className="h-5 w-5 text-gray-600 hover:text-gray-900" />
                  {notificationCount > 0 && (
                    <span className="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-xs text-white">
                      {notificationCount > 9 ? '9+' : notificationCount}
                    </span>
                  )}
                </Link>
              </div>

              {/* User menu */}
              <div className="relative">
                <button
                  onClick={() => setUserMenuOpen(!userMenuOpen)}
                  className="flex items-center gap-x-2 text-sm font-semibold text-gray-900"
                >
                  <div className="h-8 w-8 rounded-full bg-blue-600 flex items-center justify-center text-white">
                    A
                  </div>
                  <ChevronDown className="h-4 w-4" />
                </button>

                {userMenuOpen && (
                  <div className="absolute right-0 mt-2 w-48 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5">
                    <button
                      onClick={handleLogout}
                      className="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100"
                    >
                      Đăng xuất
                    </button>
                  </div>
                )}
              </div>
            </div>
          </div>
        </div>

        {/* Page content */}
        <main className="py-10">
          <div className="px-4 sm:px-6 lg:px-8">
            {children}
          </div>
        </main>
      </div>
    </div>
  );
}
