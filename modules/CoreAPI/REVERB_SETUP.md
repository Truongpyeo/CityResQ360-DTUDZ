# Laravel Reverb - WebSocket Broadcasting

## 📋 Tổng quan

Laravel Reverb đã được tích hợp vào CityResQ360 để cung cấp real-time notifications qua WebSocket.

## 🔥 Các chức năng Realtime

### 1. **Notifications (Thông báo cá nhân)**
- Event: `NotificationSent`
- Channel: `user.{userId}` (Private)
- Kích hoạt: Khi có thông báo mới
- Dành cho: User cụ thể

### 2. **New Reports for Admins (Phản ánh mới cho Admin)**
- Event: `NewReportForAdmins`
- Channel: `admin-reports` (Public with auth)
- Kích hoạt: Khi user tạo phản ánh mới
- Dành cho: Tất cả admin đang online
- Use case: Admin panel realtime monitoring

### 3. **Report Status for Users (Cập nhật trạng thái cho User)**
- Event: `ReportStatusUpdatedForUsers`
- Channel: `user-reports` (Public)
- Kích hoạt: Khi admin duyệt/đổi trạng thái phản ánh
- Dành cho: Tất cả users đang mở app
- Use case: Tự động refresh bản đồ

### 4. **Report Status (Trạng thái phản ánh - Legacy)**
- Event: `ReportStatusChanged`
- Channels: 
  - `user.{userId}` (Private) - User của phản ánh
  - `reports` (Public) - Admin monitoring
- Kích hoạt: Khi admin đổi trạng thái phản ánh

### 5. **Points Updates (Cập nhật điểm)**
- Event: `PointsUpdated`
- Channel: `user.{userId}` (Private)
- Kích hoạt: Khi user nhận CityPoints

## 🚀 Start Reverb Server

### Local Development:
```bash
cd modules/CoreAPI
php artisan reverb:start
```

### Docker:
```bash
docker-compose up -d
# Reverb tự động start trong container coreapi
```

Port: `8080` (WebSocket)

## 🔐 Channel Authorization

Channels và quyền truy cập:

### Private Channels (Require Auth):
- **`user.{userId}`** - Chỉ user với userId tương ứng mới subscribe được
  - Dùng cho: Notifications cá nhân, Points updates

### Public Channels (Require Auth but allow all):
- **`admin-reports`** - Chỉ admin (vai_tro = 1) mới subscribe được
  - Dùng cho: Admin nhận phản ánh mới realtime
  
- **`user-reports`** - Tất cả users đã login đều subscribe được
  - Dùng cho: Mobile app tự động refresh bản đồ khi có cập nhật
  
- **`reports`** - Chỉ admin (legacy channel)

Public channels không cần auth.

## 📱 Mobile App Integration (Flutter)

### Install package:
```yaml
dependencies:
  pusher_channels_flutter: ^2.2.1
```

### Connect to Reverb:
```dart
import 'package:pusher_channels_flutter/pusher_channels_flutter.dart';

final pusher = PusherChannelsFlutter.getInstance();
await pusher.init(
  apiKey: 'local-key',
  cluster: 'mt1',
  onConnectionStateChange: onConnectionStateChange,
  onError: onError,
  onSubscriptionSucceeded: onSubscriptionSucceeded,
  onEvent: onEvent,
  onSubscriptionError: onSubscriptionError,
  onDecryptionFailure: onDecryptionFailure,
  authEndpoint: 'http://localhost:8000/broadcasting/auth',
  authParams: {
    'headers': {
      'Authorization': 'Bearer $token',
    },
  },
  wsHost: 'localhost',
  wsPort: 8080,
  encrypted: false,
);

await pusher.subscribe(channelName: 'private-user.1');
await pusher.connect();
```

### Listen to events:
```dart
void onEvent(PusherEvent event) {
  // Personal notifications
  if (event.eventName == 'notification.sent') {
    final data = jsonDecode(event.data);
    print('New notification: ${data['title']}');
  } 
  
  // Points updated
  else if (event.eventName == 'points.updated') {
    final data = jsonDecode(event.data);
    print('Points: +${data['points']}');
  }
  
  // Report status updated (for map refresh)
  else if (event.eventName == 'report.status.updated') {
    final data = jsonDecode(event.data);
    print('Report ${data['report_id']} status: ${data['status_text']}');
    // Refresh map data
    refreshMapMarkers();
  }
  
  // New report for admins
  else if (event.eventName == 'new.report') {
    final data = jsonDecode(event.data);
    print('New report from ${data['user']['ho_ten']}');
    // Update admin dashboard
    refreshAdminDashboard();
  }
}

// Subscribe to channels
await pusher.subscribe(channelName: 'private-user.1'); // Personal notifications
await pusher.subscribe(channelName: 'user-reports');   // Map updates (all users)
await pusher.subscribe(channelName: 'admin-reports');  // Admin monitoring (admin only)
```

## 🧪 Testing

### Test with Tinker:
```bash
php artisan tinker

# Test notification broadcast
$user = App\Models\NguoiDung::find(1);
$notification = App\Models\ThongBao::create([
    'nguoi_dung_id' => 1,
    'tieu_de' => 'Test',
    'noi_dung' => 'Hello WebSocket!',
    'loai' => 'system',
    'da_doc' => false,
    'du_lieu_mo_rong' => [],
]);
broadcast(new App\Events\NotificationSent($notification))->toOthers();

# Test report status change
$report = App\Models\PhanAnh::find(1);
broadcast(new App\Events\ReportStatusChanged($report, 0, 1))->toOthers();

# Test points update
broadcast(new App\Events\PointsUpdated(1, 10, 100, 'Test points'))->toOthers();
```

### Test with Browser Console:
```javascript
// Connect to Reverb using Pusher protocol
const pusher = new Pusher('local-key', {
  wsHost: 'localhost',
  wsPort: 8080,
  forceTLS: false,
  cluster: 'mt1',
  authEndpoint: 'http://localhost:8000/broadcasting/auth',
  auth: {
    headers: {
      'Authorization': 'Bearer YOUR_TOKEN'
    }
  }
});

// Subscribe to private channel (user notifications)
const userChannel = pusher.subscribe('private-user.1');
userChannel.bind('notification.sent', function(data) {
  console.log('Notification:', data);
});
userChannel.bind('points.updated', function(data) {
  console.log('Points:', data);
});

// Subscribe to public channel (map refresh for all users)
const userReportsChannel = pusher.subscribe('user-reports');
userReportsChannel.bind('report.status.updated', function(data) {
  console.log('Report updated:', data);
});

// Subscribe to admin channel (new reports for admins only)
const adminChannel = pusher.subscribe('admin-reports');
adminChannel.bind('new.report', function(data) {
  console.log('New report:', data);
});
```

## 📊 Event Payload Examples

### NotificationSent:
```json
{
  "id": 18,
  "title": "Test Notification",
  "content": "This is a test",
  "type": "system",
  "data": {"test": true},
  "created_at": "2025-12-07T07:48:25.000000Z"
### ReportStatusUpdatedForUsers:
```json
{
  "report_id": 5,
  "old_status": 0,
  "new_status": 1,
  "status_text": "Đã xác nhận",
  "report": {
    "id": 5,
    "tieu_de": "Đường hư hỏng",
    "trang_thai": 1,
    "dia_chi": "123 Lê Lợi",
    "vi_do": 16.0544,
    "kinh_do": 108.2022,
    "updated_at": "2025-12-07T08:00:00.000000Z"
  }
}
```

### NewReportForAdmins:
```json
{
  "report": {
    "id": 10,
    "tieu_de": "Cống thoát nước bị tắc",
    "mo_ta": "Cống thoát nước bị tắc nghẽm",
    "trang_thai": 0,
    "dia_chi": "456 Hùng Vương",
    "vi_do": 16.0544,
    "kinh_do": 108.2022,
    "danh_muc": {
      "id": 2,
      "ten": "Hạ tầng"
    },
    "created_at": "2025-12-07T08:30:00.000000Z"
  },
  "user": {
    "id": 5,
    "ho_ten": "Nguyễn Văn A"
  }
}
```new_status": 1,
  "updated_at": "2025-12-07T08:00:00.000000Z"
}
```

### PointsUpdated:
```json
{
  "points": 10,
  "new_balance": 100,
  "reason": "Phản ánh được xác nhận"
}
```

## 🔧 Configuration

Environment variables (`.env`):
```dotenv
BROADCAST_CONNECTION=reverb

REVERB_APP_ID=cityresq360
REVERB_APP_KEY=local-key
REVERB_APP_SECRET=local-secret
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

## 🐛 Troubleshooting

### Reverb không start:
```bash
# Check logs
php artisan reverb:start --debug

# Check port conflict
lsof -i :8080
```

### Client không connect được:
- Check CORS settings
- Check authentication token
- Check firewall/port forwarding

### Events không broadcast:
```bash
# Check queue worker running
php artisan queue:work

# Check logs
tail -f storage/logs/laravel.log
```

## 📚 Resources

- [Laravel Reverb Docs](https://laravel.com/docs/11.x/reverb)
- [Pusher Flutter Client](https://pub.dev/packages/pusher_channels_flutter)
- [Broadcasting Events](https://laravel.com/docs/11.x/broadcasting)
