📝 Hướng dẫn Test FCM ở Local

## ✅ FCM đã sẵn sàng!

Firebase/FCM đã được cấu hình và hoạt động. Kiểm tra:
```bash
./scripts/test-fcm.sh
```

---

## 🧪 Test FCM Local (3 cách)

### **Cách 1: Test với User thật (Recommended)**

```bash
cd modules/CoreAPI
php artisan tinker
```

```php
// 1. Kiểm tra user có trong DB
$user = \App\Models\NguoiDung::first();
echo "User ID: " . $user->id . "\n";
echo "Push Token: " . ($user->push_token ?? 'null') . "\n";

// 2. Gửi notification
$service = app(\App\Services\NotificationService::class);
$notification = $service->send(
    userId: $user->id,
    title: '🎉 Test FCM từ Local',
    content: 'Notification đang hoạt động!',
    type: 'system',
    data: ['test' => true, 'timestamp' => now()]
);

echo "✅ Notification created (ID: " . $notification->id . ")\n";

// 3. Check trong database
\App\Models\ThongBao::latest()->first();
```

**Kết quả:**
- ✅ Notification được lưu vào DB
- ⚠️ FCM push **KHÔNG gửi** nếu user chưa có `push_token`
- ✅ Mobile app sẽ nhận notification khi call API `/api/v1/notifications`

---

### **Cách 2: Test với Fake Push Token**

```bash
php artisan tinker
```

```php
// 1. Tạo/Update user với fake token
$user = \App\Models\NguoiDung::first();
$user->update(['push_token' => 'fake-token-for-testing-only']);

// 2. Gửi notification
$service = app(\App\Services\NotificationService::class);
$service->send($user->id, 'Test Push', 'Testing FCM', 'system');

// 3. Check logs
// storage/logs/laravel.log sẽ có:
// - "Push notification sent to user X" (nếu thành công)
// - Hoặc "UNREGISTERED" error (token không hợp lệ)
```

**Check logs:**
```bash
tail -f storage/logs/laravel.log | grep -i "fcm\|push\|firebase"
```

---

### **Cách 3: Test qua Admin Panel**

1. **Start local server:**
```bash
cd modules/CoreAPI
php artisan serve
```

2. **Login Admin:** http://localhost:8000/admin/login

3. **Đổi trạng thái Report:**
   - Vào **Reports** → Chọn 1 report
   - Đổi trạng thái từ "Chờ xử lý" → "Đã xác nhận"
   - User sẽ tự động nhận notification

4. **Check notification:**
```bash
php artisan tinker
```
```php
// Xem notification mới nhất
\App\Models\ThongBao::latest()->first();
```

---

## 📱 Test với Mobile App thật

### **1. Mobile app gửi FCM token:**

```dart
// Flutter example
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:http/http.dart' as http;

// Get FCM token
final fcmToken = await FirebaseMessaging.instance.getToken();

// Send to API
await http.post(
  Uri.parse('http://localhost:8000/api/v1/user/update-push-token'),
  headers: {
    'Authorization': 'Bearer YOUR_TOKEN',
    'Content-Type': 'application/json',
  },
  body: jsonEncode({'push_token': fcmToken}),
);
```

### **2. Tạo API endpoint (nếu chưa có):**

```php
// routes/api.php
Route::middleware('auth:sanctum')->post('/user/update-push-token', function (Request $request) {
    $request->validate(['push_token' => 'required|string|max:500']);
    
    auth()->user()->update(['push_token' => $request->push_token]);
    
    return response()->json(['success' => true]);
});
```

### **3. Test push thật:**

```bash
php artisan tinker
```

```php
// User đã có push_token từ mobile
$user = \App\Models\NguoiDung::where('push_token', '!=', null)->first();

if ($user) {
    $service = app(\App\Services\NotificationService::class);
    $service->send(
        $user->id,
        '🔔 Real Push Test',
        'Bạn nhận được notification từ CoreAPI!',
        'system'
    );
    
    echo "✅ Push notification sent!\n";
    echo "Check your mobile device!\n";
} else {
    echo "⚠️  No user with push_token found\n";
}
```

---

## 🔍 Troubleshooting

### ❌ "Firebase credentials not found"
```bash
# Check file
ls -la modules/CoreAPI/storage/app/firebase-credentials.json

# Check .env
grep FIREBASE modules/CoreAPI/.env
```

### ❌ "UNREGISTERED" error
- Token không hợp lệ hoặc đã expired
- App đã uninstall
- Token bị revoke

**Fix:** Mobile app cần gửi token mới

### ❌ No push received
```bash
# Check logs
tail -f modules/CoreAPI/storage/logs/laravel.log

# Check user có token không
php artisan tinker
>>> $user = \App\Models\NguoiDung::find(1);
>>> $user->push_token

# Test Firebase manually
php artisan tinker
>>> $factory = (new \Kreait\Firebase\Factory)->withServiceAccount(config('firebase.credentials'));
>>> $messaging = $factory->createMessaging();
>>> echo "Firebase OK\n";
```

---

## ✅ Checklist

- [x] Firebase credentials file tồn tại
- [x] .env có FIREBASE_CREDENTIALS và FIREBASE_PROJECT_ID
- [x] NotificationService khởi tạo thành công
- [ ] User có push_token trong database
- [ ] Mobile app đã setup Firebase
- [ ] Mobile app gửi token lên API
- [ ] Test push notification nhận được trên device

---

## 📚 Tham khảo

- **Test script:** `./scripts/test-fcm.sh`
- **Config:** `modules/CoreAPI/config/firebase.php`
- **Service:** `modules/CoreAPI/app/Services/NotificationService.php`
- **Logs:** `modules/CoreAPI/storage/logs/laravel.log`

---

**🎉 FCM đã hoạt động! Chỉ cần mobile app gửi token là có thể nhận push notification!**
