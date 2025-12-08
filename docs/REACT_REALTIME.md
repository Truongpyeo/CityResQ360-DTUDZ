# Tích hợp Realtime Notifications vào React Native Mobile App

## 📋 Tổng quan

Hướng dẫn này giúp tích hợp Laravel Reverb WebSocket vào ứng dụng React Native để nhận thông báo realtime khi:
- Admin duyệt/từ chối phản ánh của user
- Có phản ánh mới từ user khác trong cùng khu vực
- Điểm uy tín/wallet của user thay đổi

## 🔧 Cài đặt

### 1. Cài đặt dependencies

```bash
cd modules/AppMobile
npm install pusher-js @react-native-async-storage/async-storage
```

**Lưu ý:** Pusher-js hoạt động tốt trên React Native và tương thích với Laravel Reverb.

### 2. Cấu hình môi trường

Cập nhật file `.env`:

```env
# Laravel Reverb Configuration (Development)
REVERB_APP_ID=808212
REVERB_APP_KEY=lwf6joghdvbowg9hb7p4
REVERB_APP_SECRET=yh8dts6nhxqzn2i77yim
REVERB_HOST=192.168.1.100  # IP của máy chạy Docker (local network)
REVERB_PORT=6001           # Internal Reverb port
REVERB_SCHEME=http

# For Mobile App (React Native)
VITE_REVERB_APP_KEY=lwf6joghdvbowg9hb7p4
VITE_REVERB_HOST=192.168.1.100  # or api.yourdomai n.com for production
VITE_REVERB_PORT=8080              # External port (dev) or 443 (production SSL)
VITE_REVERB_SCHEME=http            # http (dev) or https (production)
```

**Quan trọng:** Trên thiết bị thật hoặc emulator, không thể dùng `localhost`. Phải dùng:
- **IP LAN** (192.168.x.x) nếu test trên mạng nội bộ
- **Domain/IP công khai** nếu deploy production

## 📁 Cấu trúc file

```
src/
├── services/
│   └── websocket.ts          # WebSocket service
├── contexts/
│   └── WebSocketContext.tsx  # Global WebSocket context
├── hooks/
│   └── useNotifications.ts   # Custom hook cho notifications
├── components/
│   └── NotificationBanner.tsx # Component hiển thị notification
└── screens/
    └── HomeScreen.tsx         # Screen sử dụng realtime
```

## 💻 Implementation

### 1. WebSocket Service (`src/services/websocket.ts`)

```typescript
import Pusher from 'pusher-js/react-native';
import AsyncStorage from '@react-native-async-storage/async-storage';

// Pusher configuration
// For development: use IP + port 8080 (if mobile can't access 6001)
// For production: use domain + port 443 (SSL)
const PUSHER_CONFIG = {
  key: process.env.VITE_REVERB_APP_KEY || 'lwf6joghdvbowg9hb7p4',
  cluster: 'mt1',
  wsHost: process.env.VITE_REVERB_HOST || '192.168.1.100',
  wsPort: parseInt(process.env.VITE_REVERB_PORT || '8080'),   // External port
  wssPort: parseInt(process.env.VITE_REVERB_PORT || '8080'),  // Same for wss
  forceTLS: process.env.VITE_REVERB_SCHEME === 'https',       // Enable for production
  enabledTransports: ['ws', 'wss'],
  disableStats: true,
  authEndpoint: `${process.env.VITE_REVERB_SCHEME}://${process.env.VITE_REVERB_HOST}/broadcasting/auth`,
  auth: {
    headers: {},
  },
};

class WebSocketService {
  private pusher: Pusher | null = null;
  private channels: Map<string, any> = new Map();
  private isConnected: boolean = false;

  /**
   * Initialize Pusher connection
   */
  async connect() {
    if (this.pusher) {
      console.log('⚠️ WebSocket already connected');
      return this.pusher;
    }

    try {
      // Get auth token from storage
      const token = await AsyncStorage.getItem('auth_token');
      
      if (token) {
        PUSHER_CONFIG.auth.headers = {
          Authorization: `Bearer ${token}`,
          Accept: 'application/json',
        };
      }

      // Initialize Pusher
      this.pusher = new Pusher(PUSHER_CONFIG.key, PUSHER_CONFIG);

      // Connection events
      this.pusher.connection.bind('connected', () => {
        console.log('✅ WebSocket connected');
        this.isConnected = true;
      });

      this.pusher.connection.bind('disconnected', () => {
        console.log('❌ WebSocket disconnected');
        this.isConnected = false;
      });

      this.pusher.connection.bind('error', (err: any) => {
        console.error('❌ WebSocket error:', err);
        this.isConnected = false;
      });

      return this.pusher;
    } catch (error) {
      console.error('Failed to connect WebSocket:', error);
      throw error;
    }
  }

  /**
   * Disconnect WebSocket
   */
  disconnect() {
    if (this.pusher) {
      this.channels.forEach((channel) => {
        channel.unbind_all();
      });
      this.pusher.disconnect();
      this.pusher = null;
      this.channels.clear();
      this.isConnected = false;
      console.log('🔌 WebSocket disconnected');
    }
  }

  /**
   * Subscribe to a channel
   */
  subscribe(channelName: string) {
    if (!this.pusher) {
      throw new Error('WebSocket not connected. Call connect() first.');
    }

    if (this.channels.has(channelName)) {
      return this.channels.get(channelName);
    }

    const channel = this.pusher.subscribe(channelName);
    this.channels.set(channelName, channel);

    channel.bind('pusher:subscription_succeeded', () => {
      console.log(`✅ Subscribed to ${channelName}`);
    });

    channel.bind('pusher:subscription_error', (err: any) => {
      console.error(`❌ Failed to subscribe to ${channelName}:`, err);
    });

    return channel;
  }

  /**
   * Unsubscribe from a channel
   */
  unsubscribe(channelName: string) {
    if (this.channels.has(channelName)) {
      const channel = this.channels.get(channelName);
      channel.unbind_all();
      this.pusher?.unsubscribe(channelName);
      this.channels.delete(channelName);
      console.log(`🔌 Unsubscribed from ${channelName}`);
    }
  }

  /**
   * Listen to event on a channel
   */
  listen(channelName: string, eventName: string, callback: (data: any) => void) {
    const channel = this.channels.get(channelName);
    if (!channel) {
      throw new Error(`Channel ${channelName} not subscribed`);
    }

    channel.bind(eventName, callback);
    console.log(`👂 Listening to ${eventName} on ${channelName}`);
  }

  /**
   * Stop listening to event
   */
  stopListening(channelName: string, eventName: string) {
    const channel = this.channels.get(channelName);
    if (channel) {
      channel.unbind(eventName);
    }
  }

  /**
   * Check connection status
   */
  getConnectionStatus(): boolean {
    return this.isConnected;
  }
}

export default new WebSocketService();
```

### 2. WebSocket Context (`src/contexts/WebSocketContext.tsx`)

```typescript
import React, { createContext, useContext, useEffect, useState, ReactNode } from 'react';
import WebSocketService from '../services/websocket';
import AsyncStorage from '@react-native-async-storage/async-storage';

interface WebSocketContextType {
  isConnected: boolean;
  subscribe: (channel: string) => void;
  unsubscribe: (channel: string) => void;
  listen: (channel: string, event: string, callback: (data: any) => void) => void;
}

const WebSocketContext = createContext<WebSocketContextType | undefined>(undefined);

export const WebSocketProvider = ({ children }: { children: ReactNode }) => {
  const [isConnected, setIsConnected] = useState(false);

  useEffect(() => {
    let mounted = true;

    const initWebSocket = async () => {
      try {
        // Check if user is logged in
        const token = await AsyncStorage.getItem('auth_token');
        if (!token) {
          console.log('⚠️ No auth token, skipping WebSocket connection');
          return;
        }

        // Connect WebSocket
        await WebSocketService.connect();
        
        if (mounted) {
          setIsConnected(true);
        }
      } catch (error) {
        console.error('Failed to initialize WebSocket:', error);
      }
    };

    initWebSocket();

    // Cleanup on unmount
    return () => {
      mounted = false;
      WebSocketService.disconnect();
    };
  }, []);

  const subscribe = (channel: string) => {
    WebSocketService.subscribe(channel);
  };

  const unsubscribe = (channel: string) => {
    WebSocketService.unsubscribe(channel);
  };

  const listen = (channel: string, event: string, callback: (data: any) => void) => {
    WebSocketService.listen(channel, event, callback);
  };

  return (
    <WebSocketContext.Provider value={{ isConnected, subscribe, unsubscribe, listen }}>
      {children}
    </WebSocketContext.Provider>
  );
};

export const useWebSocket = () => {
  const context = useContext(WebSocketContext);
  if (!context) {
    throw new Error('useWebSocket must be used within WebSocketProvider');
  }
  return context;
};
```

### 3. Notifications Hook (`src/hooks/useNotifications.ts`)

```typescript
import { useEffect, useState } from 'react';
import { useWebSocket } from '../contexts/WebSocketContext';
import { useAuth } from '../contexts/AuthContext'; // Your auth context
import notifee from '@notifee/react-native'; // Optional: for local notifications

interface Notification {
  id: string;
  type: 'report_status' | 'points_updated' | 'new_nearby_report';
  title: string;
  message: string;
  data?: any;
  timestamp: Date;
}

export const useNotifications = () => {
  const { isConnected, subscribe, unsubscribe, listen } = useWebSocket();
  const { user } = useAuth(); // Get current user
  const [notifications, setNotifications] = useState<Notification[]>([]);
  const [unreadCount, setUnreadCount] = useState(0);

  useEffect(() => {
    if (!isConnected || !user?.id) return;

    // Subscribe to user's private channel
    const userChannel = `private-user.${user.id}`;
    subscribe(userChannel);

    // Listen to report status updates
    listen(userChannel, 'report.status.updated', (data) => {
      console.log('📢 Report status updated:', data);
      
      const notification: Notification = {
        id: `report-${Date.now()}`,
        type: 'report_status',
        title: 'Cập nhật phản ánh',
        message: `Phản ánh "${data.report.tieu_de}" đã được ${getStatusText(data.report.trang_thai)}`,
        data: data.report,
        timestamp: new Date(),
      };

      setNotifications(prev => [notification, ...prev]);
      setUnreadCount(prev => prev + 1);

      // Show local notification
      showLocalNotification(notification);
    });

    // Listen to points updates
    listen(userChannel, 'points.updated', (data) => {
      console.log('💰 Points updated:', data);
      
      const notification: Notification = {
        id: `points-${Date.now()}`,
        type: 'points_updated',
        title: 'Điểm thay đổi',
        message: `${data.change > 0 ? '+' : ''}${data.change} điểm. Tổng: ${data.new_balance}`,
        data,
        timestamp: new Date(),
      };

      setNotifications(prev => [notification, ...prev]);
      setUnreadCount(prev => prev + 1);
      showLocalNotification(notification);
    });

    // Cleanup
    return () => {
      unsubscribe(userChannel);
    };
  }, [isConnected, user?.id]);

  const markAsRead = (notificationId: string) => {
    setNotifications(prev =>
      prev.map(n => n.id === notificationId ? { ...n, read: true } : n)
    );
    setUnreadCount(prev => Math.max(0, prev - 1));
  };

  const clearAll = () => {
    setNotifications([]);
    setUnreadCount(0);
  };

  return {
    notifications,
    unreadCount,
    markAsRead,
    clearAll,
  };
};

// Helper functions
function getStatusText(status: number): string {
  switch (status) {
    case 1: return 'đang xử lý';
    case 2: return 'đã giải quyết';
    case 3: return 'bị từ chối';
    default: return 'chờ xử lý';
  }
}

async function showLocalNotification(notification: Notification) {
  try {
    // Request permission first time
    await notifee.requestPermission();

    // Create notification channel (Android)
    const channelId = await notifee.createChannel({
      id: 'default',
      name: 'Default Channel',
    });

    // Display notification
    await notifee.displayNotification({
      title: notification.title,
      body: notification.message,
      android: {
        channelId,
        smallIcon: 'ic_launcher',
        pressAction: {
          id: 'default',
        },
      },
      ios: {
        sound: 'default',
      },
    });
  } catch (error) {
    console.error('Failed to show notification:', error);
  }
}
```

### 4. Notification Banner Component (`src/components/NotificationBanner.tsx`)

```typescript
import React from 'react';
import { View, Text, TouchableOpacity, StyleSheet } from 'react-native';
import { useNotifications } from '../hooks/useNotifications';

export const NotificationBanner = () => {
  const { notifications, markAsRead } = useNotifications();
  const latestNotification = notifications[0];

  if (!latestNotification) return null;

  return (
    <TouchableOpacity
      style={styles.banner}
      onPress={() => markAsRead(latestNotification.id)}
    >
      <View style={styles.content}>
        <Text style={styles.title}>{latestNotification.title}</Text>
        <Text style={styles.message}>{latestNotification.message}</Text>
      </View>
      <Text style={styles.close}>✕</Text>
    </TouchableOpacity>
  );
};

const styles = StyleSheet.create({
  banner: {
    backgroundColor: '#3B82F6',
    padding: 16,
    flexDirection: 'row',
    alignItems: 'center',
    marginHorizontal: 16,
    marginTop: 8,
    borderRadius: 8,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.25,
    shadowRadius: 3.84,
    elevation: 5,
  },
  content: {
    flex: 1,
  },
  title: {
    color: '#fff',
    fontSize: 16,
    fontWeight: 'bold',
    marginBottom: 4,
  },
  message: {
    color: '#fff',
    fontSize: 14,
  },
  close: {
    color: '#fff',
    fontSize: 20,
    paddingLeft: 16,
  },
});
```

### 5. App.tsx - Setup Provider

```typescript
import React from 'react';
import { WebSocketProvider } from './src/contexts/WebSocketContext';
import { AuthProvider } from './src/contexts/AuthContext';
import AppNavigator from './src/navigation/AppNavigator';

export default function App() {
  return (
    <AuthProvider>
      <WebSocketProvider>
        <AppNavigator />
      </WebSocketProvider>
    </AuthProvider>
  );
}
```

### 6. Sử dụng trong Screen

```typescript
import React from 'react';
import { View, Text, FlatList } from 'react-native';
import { useWebSocket } from '../contexts/WebSocketContext';
import { useNotifications } from '../hooks/useNotifications';
import { NotificationBanner } from '../components/NotificationBanner';

export default function HomeScreen() {
  const { isConnected } = useWebSocket();
  const { notifications, unreadCount } = useNotifications();

  return (
    <View style={{ flex: 1 }}>
      {/* Connection Status */}
      <View style={{ flexDirection: 'row', padding: 8, backgroundColor: isConnected ? '#10B981' : '#EF4444' }}>
        <Text style={{ color: '#fff' }}>
          {isConnected ? '🟢 Connected' : '🔴 Disconnected'}
        </Text>
        {unreadCount > 0 && (
          <Text style={{ color: '#fff', marginLeft: 8 }}>
            ({unreadCount} new)
          </Text>
        )}
      </View>

      {/* Latest Notification Banner */}
      <NotificationBanner />

      {/* Your content here */}
      <FlatList
        data={notifications}
        keyExtractor={(item) => item.id}
        renderItem={({ item }) => (
          <View style={{ padding: 16, borderBottomWidth: 1, borderColor: '#E5E7EB' }}>
            <Text style={{ fontWeight: 'bold' }}>{item.title}</Text>
            <Text style={{ color: '#6B7280' }}>{item.message}</Text>
          </View>
        )}
      />
    </View>
  );
}
```

## 📡 Events và Channels

### 1. Public Channel: `user-reports` (✅ **Dành cho Client/Mobile App**)

**Mục đích:** Thông báo cho TẤT CẢ users khi có report được cập nhật trạng thái bởi admin.

**Use case:** 
- Mobile app tự động refresh map khi report thay đổi trạng thái
- Hiển thị realtime updates cho users
- Auto-reload danh sách reports

**Event:** `report.status.updated`

**Payload example:**
```json
{
  "report_id": 123,
  "old_status": 0,
  "new_status": 1,
  "status_text": "Đã xác nhận",
  "report": {
    "id": 123,
    "tieu_de": "Đường hư hỏng",
    "trang_thai": 1,
    "dia_chi": "123 Nguyen Hue, Q1",
    "vi_do": "10.7769",
    "kinh_do": "106.7009",
    "updated_at": "2025-12-08T04:30:00.000000Z"
  }
}
```

**Status codes:**
- `0` = Chờ xử lý
- `1` = Đã xác nhận  
- `2` = Đang xử lý
- `3` = Đã giải quyết
- `4` = Từ chối

**Implementation:**

```typescript
import { useEffect } from 'react';
import { useWebSocket } from '../contexts/WebSocketContext';

export const useReportUpdates = (onReportUpdated: (data: any) => void) => {
  const { isConnected, subscribe, listen } = useWebSocket();
  
  useEffect(() => {
    if (!isConnected) return;
    
    // Subscribe to public channel
    subscribe('user-reports');
    
    // Listen to report status updates
    listen('user-reports', 'report.status.updated', (data) => {
      console.log('📢 Report updated:', data);
      
      // Option 1: Update specific report in state
      onReportUpdated(data.report);
      
      // Option 2: Refetch all reports
      // refetchReports();
      
      // Option 3: Update map markers
      // updateMapMarker(data.report.id, data.report);
    });
    
    return () => {
      // Cleanup handled by WebSocketProvider
    };
  }, [isConnected]);
};
```

**Example: Map Screen với Auto-Refresh**

```typescript
import React, { useState, useEffect } from 'react';
import { View } from 'react-native';
import MapView, { Marker } from 'react-native-maps';
import { useReportUpdates } from '../hooks/useReportUpdates';

export default function MapScreen() {
  const [reports, setReports] = useState([]);
  const [loading, setLoading] = useState(false);
  
  // Fetch initial reports
  useEffect(() => {
    fetchReports();
  }, []);
  
  // ✅ Auto-update when admin changes status
  useReportUpdates((updatedReport) => {
    console.log('🔄 Auto-updating report:', updatedReport.id);
    
    setReports(prev => 
      prev.map(r => 
        r.id === updatedReport.id ? { ...r, ...updatedReport } : r
      )
    );
    
    // Show toast notification
    Toast.show({
      text: `Phản ánh "${updatedReport.tieu_de}" đã được cập nhật`,
      type: 'info',
    });
  });
  
  async function fetchReports() {
    setLoading(true);
    try {
      const response = await api.get('/api/v1/reports');
      setReports(response.data.data);
    } finally {
      setLoading(false);
    }
  }
  
  return (
    <View style={{ flex: 1 }}>
      <MapView
        style={{ flex: 1 }}
        initialRegion={{
          latitude: 10.7769,
          longitude: 106.7009,
          latitudeDelta: 0.05,
          longitudeDelta: 0.05,
        }}
      >
        {reports.map(report => (
          <Marker
            key={report.id}
            coordinate={{
              latitude: parseFloat(report.vi_do),
              longitude: parseFloat(report.kinh_do),
            }}
            pinColor={getMarkerColor(report.trang_thai)}
            title={report.tieu_de}
            description={report.status_text}
          />
        ))}
      </MapView>
    </View>
  );
}

function getMarkerColor(status: number): string {
  switch (status) {
    case 0: return 'orange';  // Chờ xử lý
    case 1: return 'blue';    // Đã xác nhận
    case 2: return 'yellow';  // Đang xử lý
    case 3: return 'green';   // Đã giải quyết
    case 4: return 'red';     // Từ chối
    default: return 'gray';
  }
}
```

---

### 2. Private User Channel: `private-user.{userId}` (✅ **Dành cho User cá nhân**)

**Mục đích:** Thông báo riêng cho user khi REPORTS CỦA HỌ được admin cập nhật.

**Events:**
- `report.status.updated` - Admin duyệt/từ chối phản ánh của user
- `points.updated` - Điểm uy tín thay đổi
- `notification.sent` - Notification chung

**Payload example:**
```json
{
  "report": {
    "id": 123,
    "tieu_de": "Đường hư hỏng",
    "trang_thai": 2,
    "ghi_chu_admin": "Đã xử lý xong"
  }
}
```

### 3. Backend Implementation (Already done ✅)

**Event broadcast in `AdminController::updateStatus()`:**

```php
use App\Events\ReportStatusUpdatedForUsers;

public function updateStatus(Request $request, $id) {
    $report = PhanAnh::findOrFail($id);
    $oldStatus = $report->trang_thai;
    
    $report->update(['trang_thai' => $request->trang_thai]);
    
    // 🔥 Broadcast to ALL users for map refresh
    broadcast(new ReportStatusUpdatedForUsers(
        $report, 
        $oldStatus, 
        $request->trang_thai
    ))->toOthers();
    
    return response()->json(['success' => true]);
}
```

**Channel authorization in `routes/channels.php`:**

```php
// Public channel - all authenticated users can listen
Broadcast::channel('user-reports', function () {
    return true;
});
```

## 🔒 Authentication

Backend Laravel đã cấu hình authentication cho WebSocket trong `routes/channels.php`:

```php
Broadcast::channel('user.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});
```

Token Bearer sẽ được gửi kèm trong header khi subscribe private channel.

## 🐛 Debugging

### Check WebSocket connection

```typescript
import WebSocketService from './src/services/websocket';

// In any component
useEffect(() => {
  const status = WebSocketService.getConnectionStatus();
  console.log('WebSocket status:', status);
}, []);
```

### Test trên DevTools

1. Mở Chrome DevTools → Network tab
2. Filter: WS (WebSocket)
3. Xem messages được gửi/nhận

### Common Issues

**1. Connection refused:**
- Kiểm tra IP/domain trong `.env`
- Đảm bảo port 8080 không bị firewall chặn
- Test: `telnet <REVERB_HOST> 8080`

**2. Authentication failed:**
- Kiểm tra token trong AsyncStorage
- Verify token còn hạn trong backend
- Check `authEndpoint` URL

**3. Không nhận được events:**
- Verify channel name chính xác
- Check event name match với backend
- Xem logs trong Laravel: `docker logs -f cityresq-coreapi`

## 📱 Testing

### 1. Test WebSocket connection

```bash
# Create test report
./scripts/test-create-report.sh

# Update report status (trigger notification)
curl -X PATCH http://localhost:8000/api/admin/reports/123/status \
  -H "Authorization: Bearer <ADMIN_TOKEN>" \
  -d '{"trang_thai": 2, "ghi_chu_admin": "Done"}'
```

### 2. Monitor Reverb server

```bash
docker exec cityresq-coreapi tail -f /var/log/supervisor/reverb.log
```

## 🚀 Production Deployment

### 1. Cấu hình SSL/TLS

**Environment variables cho production:**

```env
VITE_REVERB_APP_KEY=lwf6joghdvbowg9hb7p4
VITE_REVERB_HOST=api.cityresq360.io.vn
VITE_REVERB_PORT=443
VITE_REVERB_SCHEME=https
```

**Pusher config sẽ tự động dùng SSL:**

```typescript
const PUSHER_CONFIG = {
  key: 'lwf6joghdvbowg9hb7p4',
  wsHost: 'api.cityresq360.io.vn',
  wsPort: 443,
  wssPort: 443,
  forceTLS: true,  // ✅ Enable SSL
  enabledTransports: ['ws', 'wss'],
};
```

### 2. Nginx Reverse Proxy

**Nginx config cho WebSocket (on VPS):**

```nginx
server {
    server_name api.cityresq360.io.vn;
    
    # WebSocket location
    location /app {
        proxy_pass http://127.0.0.1:6001;  # Internal Reverb port
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "Upgrade";
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto https;
        proxy_read_timeout 86400;
    }
    
    listen 443 ssl;
    ssl_certificate /etc/letsencrypt/live/api.cityresq360.io.vn/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/api.cityresq360.io.vn/privkey.pem;
}
```

**Supervisor config (trong Docker container):**

```ini
[program:reverb]
command=php /var/www/html/artisan reverb:start --host=0.0.0.0 --port=6001
autostart=true
autorestart=true
stdout_logfile=/var/log/supervisor/reverb.log
stderr_logfile=/var/log/supervisor/reverb_error.log
```

### 3. React Admin Panel Example

**AdminLayout.tsx (Real implementation):**

```typescript
import Pusher from 'pusher-js';
import Swal from 'sweetalert2';

export default function AdminLayout({ children }) {
  const [wsConnected, setWsConnected] = useState(false);
  
  useEffect(() => {
    // Initialize Pusher for Admin
    const pusher = new Pusher(
      import.meta.env.VITE_REVERB_APP_KEY || 'lwf6joghdvbowg9hb7p4',
      {
        wsHost: window.location.hostname,  // Auto-detect domain
        wsPort: 443,                        // SSL port
        wssPort: 443,
        forceTLS: true,                     // Enable SSL
        enabledTransports: ['ws', 'wss'],
        disableStats: true,
        cluster: 'mt1',
        authEndpoint: '/broadcasting/auth',
      }
    );
    
    // Connection events
    pusher.connection.bind('connected', () => {
      console.log('✅ WebSocket connected');
      setWsConnected(true);
    });
    
    // Subscribe to admin channel
    const channel = pusher.subscribe('admin-reports');
    
    channel.bind('new.report', (data) => {
      // Show popup notification
      Swal.fire({
        title: '📢 Phản ánh mới!',
        html: `
          <p><strong>Từ:</strong> ${data.user?.ho_ten}</p>
          <p><strong>Tiêu đề:</strong> ${data.report?.tieu_de}</p>
        `,
        icon: 'info',
        confirmButtonText: '👁️ Xem chi tiết',
      }).then((result) => {
        if (result.isConfirmed) {
          router.visit(`/admin/reports/${data.report.id}`);
        }
      });
    });
    
    return () => {
      pusher.disconnect();
    };
  }, []);
  
  return (
    <div>
      {/* WebSocket status indicator */}
      <div className={wsConnected ? 'bg-green-500' : 'bg-red-500'}>
        {wsConnected ? '🟢 Connected' : '🔴 Disconnected'}
      </div>
      {children}
    </div>
  );
}
```

### 4. Environment Management

**Using `react-native-config`:**

```bash
npm install react-native-config
```

**.env.development:**
```env
VITE_REVERB_HOST=192.168.1.100
VITE_REVERB_PORT=8080
VITE_REVERB_SCHEME=http
```

**.env.production:**
```env
VITE_REVERB_HOST=api.cityresq360.io.vn
VITE_REVERB_PORT=443
VITE_REVERB_SCHEME=https
```

## 📚 Tài liệu tham khảo

- [Laravel Broadcasting](https://laravel.com/docs/11.x/broadcasting)
- [Laravel Reverb](https://reverb.laravel.com)
- [Pusher-js](https://github.com/pusher/pusher-js)
- [Notifee (Local Notifications)](https://notifee.app)

## ✅ Checklist

- [ ] Cài đặt dependencies: `pusher-js`, `@react-native-async-storage/async-storage`
- [ ] Tạo WebSocketService
- [ ] Tạo WebSocketContext và Provider
- [ ] Tạo useNotifications hook
- [ ] Thêm NotificationBanner component
- [ ] Wrap App với WebSocketProvider
- [ ] Cấu hình `.env` với IP/domain đúng
- [ ] Test connection trên emulator/device thật
- [ ] Test nhận notification khi admin cập nhật report
- [ ] Test local notification (optional)
- [ ] Setup SSL cho production

---

**🎉 Happy Coding!** Nếu có vấn đề, check logs và verify network connection trước.
