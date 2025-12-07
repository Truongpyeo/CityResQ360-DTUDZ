# Admin Dashboard Realtime Integration Guide

## Tổng quan

Admin Dashboard đã được tích hợp WebSocket realtime để hiển thị thông báo ngay lập tức khi có phản ánh mới từ người dùng.

## Kiến trúc

```
User App → API Create Report → Broadcast Event → Reverb Server → Admin Dashboard (WebSocket)
```

### Flow:

1. **User creates report** → `POST /api/v1/reports`
2. **API broadcasts event** → `NewReportForAdmins` to `admin-reports` channel
3. **Reverb server** → Pushes to all connected admin clients
4. **Admin Dashboard** → Receives notification realtime, displays alert + auto-refresh stats

## Files Modified

### 1. **Dashboard.tsx** - Admin Dashboard Component

**Location**: `modules/CoreAPI/resources/js/pages/admin/Dashboard.tsx`

**Changes**:
- ✅ Added `pusher-js` import for WebSocket client
- ✅ Added realtime notifications state management
- ✅ Added WebSocket connection lifecycle (connect, disconnect, error handling)
- ✅ Added subscription to `admin-reports` channel
- ✅ Added event listener for `new.report` events
- ✅ Added notification UI with dismiss functionality
- ✅ Added connection status indicator
- ✅ Added auto-refresh stats after new report

**Key Features**:
```tsx
// WebSocket connection
const pusher = new Pusher(VITE_REVERB_APP_KEY, {
  wsHost: VITE_REVERB_HOST,
  wsPort: VITE_REVERB_PORT,
  // ...
});

// Subscribe to admin channel
const channel = pusher.subscribe('admin-reports');

// Listen for new reports
channel.bind('new.report', (data) => {
  // Display notification
  // Refresh stats
  // Play sound (optional)
});
```

### 2. **test-create-report.sh** - Testing Script

**Location**: `scripts/test-create-report.sh`

**Purpose**: Automated script to test realtime functionality

**Usage**:
```bash
./scripts/test-create-report.sh
```

**What it does**:
1. Login as test user (`nguyenvanan@gmail.com`)
2. Create a test report
3. Verify broadcast was sent
4. Show admin dashboard URL for testing

## Environment Variables

Make sure these are set in `.env`:

```env
# Reverb Server
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=your-app-id
REVERB_APP_KEY=your-app-key
REVERB_APP_SECRET=your-app-secret
REVERB_HOST=0.0.0.0
REVERB_PORT=8080
REVERB_SCHEME=http

# Vite (for frontend)
VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

## NPM Package

**Installed**: `pusher-js` v8.4.0-rc2

```bash
npm install pusher-js
```

## Testing Guide

### Step 1: Login as Admin

1. Open browser: `http://localhost:8000/admin/login`
2. Login with admin credentials:
   - Email: `admin@master.com`
   - Password: `123456`

### Step 2: Open Dashboard

1. Navigate to: `http://localhost:8000/admin/dashboard`
2. Check connection status indicator (should show 🟢 green dot)
3. Open browser console (F12) to see WebSocket logs:
   ```
   🔌 Initializing WebSocket connection for Admin Dashboard...
   ✅ WebSocket connected successfully!
   ✅ Successfully subscribed to admin-reports channel
   ```

### Step 3: Create Test Report

**Option A: Using Script**
```bash
cd /Volumes/MyVolume/Laravel/CityResQ360-DTUDZ
./scripts/test-create-report.sh
```

**Option B: Using API Manually**
```bash
# 1. Login
TOKEN=$(curl -s -X POST "http://localhost:8000/api/v1/auth/login" \
  -H "Content-Type: application/json" \
  -d '{"email":"nguyenvanan@gmail.com","mat_khau":"password123"}' \
  | jq -r '.data.token')

# 2. Create report
curl -X POST "http://localhost:8000/api/v1/reports" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{
    "tieu_de": "Test Realtime",
    "mo_ta": "Testing realtime notifications",
    "dia_chi": "Test Address",
    "vi_do": 10.7769,
    "kinh_do": 106.7009,
    "danh_muc_id": 1
  }'
```

**Option C: Using Mobile App**
- Just create a normal report from the app
- Admin dashboard will receive it realtime

### Step 4: Verify Realtime Notification

You should see:

1. **Console logs**:
   ```
   📢 New report received: {report: {...}}
   ```

2. **Notification banner** at top of dashboard:
   - Blue alert box
   - Report title and user name
   - "Xem chi tiết" button
   - Dismiss (X) button

3. **Stats auto-refresh**: Numbers update after 1 second

4. **Recent reports table**: New report appears at top

## Notification Features

### UI Components

```tsx
<div className="notification">
  <Bell icon />
  <div className="content">
    <p>Phản ánh mới từ {user.name}</p>
    <p><strong>{report.title}</strong></p>
    <p>{category} • {timestamp}</p>
  </div>
  <button onClick={dismiss}>X</button>
  <Link href={`/admin/reports/${id}`}>Xem chi tiết</Link>
</div>
```

### Features:
- ✅ Slide-in animation
- ✅ Blue color scheme (matches admin theme)
- ✅ User name and report title
- ✅ Category and timestamp
- ✅ Dismiss functionality
- ✅ Direct link to report details
- ✅ Auto-refresh stats
- ✅ Sound notification (optional)
- ✅ Connection status indicator

### Notification Management

- **Max notifications**: 10 (keeps last 10 only)
- **Dismiss**: Click X button to remove individual notification
- **Auto-hide**: Can be set to auto-hide after N seconds (optional)
- **Persistence**: Notifications cleared on page refresh (by design)

## Troubleshooting

### 1. WebSocket not connecting

**Check**:
```bash
# Reverb server running?
docker exec cityresq-coreapi supervisorctl status reverb

# Reverb logs
docker exec cityresq-coreapi tail -f /var/log/supervisor/reverb.log

# Test port
curl http://localhost:8080/
```

**Solution**:
```bash
# Restart Reverb
docker exec cityresq-coreapi supervisorctl restart reverb

# Or restart container
docker-compose restart coreapi
```

### 2. Notifications not appearing

**Check browser console**:
- Are WebSocket logs present?
- Is connection status green?
- Is subscription successful?

**Check broadcast**:
```bash
# Reverb logs should show broadcast activity
docker logs cityresq-coreapi | grep -i broadcast
```

**Check event**:
```php
// In tinker
use App\Events\NewReportForAdmins;
$report = App\Models\BaoCao::first();
$user = $report->nguoiDung;
broadcast(new NewReportForAdmins($report, $user));
```

### 3. CORS issues

If you see CORS errors:

**Check nginx config**: `modules/CoreAPI/nginx/default.conf`
```nginx
location /app {
    proxy_pass http://127.0.0.1:8080;
    proxy_http_version 1.1;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection "Upgrade";
}
```

### 4. Environment variables not working

**Rebuild frontend**:
```bash
cd modules/CoreAPI
npm run build
```

**Check .env**:
```bash
grep VITE_REVERB .env
```

## Production Deployment

### 1. SSL/TLS Configuration

For production, update `.env`:

```env
REVERB_SCHEME=https
REVERB_HOST=your-domain.com
REVERB_PORT=443
```

### 2. Nginx Reverse Proxy

Add to nginx config:

```nginx
location /reverb {
    proxy_pass http://127.0.0.1:8080;
    proxy_http_version 1.1;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection "Upgrade";
    proxy_set_header Host $host;
    proxy_set_header X-Real-IP $remote_addr;
}
```

### 3. Firewall

Open WebSocket port:

```bash
# Allow port 8080
sudo ufw allow 8080/tcp
```

### 4. Monitoring

Monitor Reverb process:

```bash
# Check uptime
docker exec cityresq-coreapi supervisorctl status reverb

# Check connections
docker exec cityresq-coreapi netstat -an | grep 8080

# View logs
docker exec cityresq-coreapi tail -100 /var/log/supervisor/reverb.log
```

## Performance Considerations

### Connection Limits

Default Reverb can handle ~1000 concurrent connections per instance.

For more:
- Scale horizontally with Redis
- Use load balancer
- Monitor memory usage

### Broadcast Optimization

Current implementation broadcasts to ALL admins. If you have many admins, consider:

1. **Role-based channels**: Different channels for different admin types
2. **Selective broadcast**: Only broadcast to online admins
3. **Queue broadcasts**: Use Laravel queues for async processing

### Frontend Optimization

- ✅ Limit notifications to last 10
- ✅ Debounce stats refresh (1 second delay)
- ✅ Lazy load notification sounds
- ✅ Clean up WebSocket on unmount

## Future Enhancements

### Potential features:

1. **Mark as read**: Track which notifications have been read
2. **Notification center**: Persistent notification history
3. **Filter by category**: Show only specific report types
4. **Sound preferences**: Let admins toggle sound on/off
5. **Desktop notifications**: Browser native notifications
6. **Mobile push**: Send to admin mobile apps
7. **Notification badges**: Show count in sidebar
8. **Priority alerts**: Different colors for urgent reports

## API Reference

### Event: NewReportForAdmins

**Channel**: `admin-reports` (public, admin-only authorization)

**Event name**: `new.report`

**Payload**:
```json
{
  "report": {
    "id": 32,
    "tieu_de": "Report title",
    "mo_ta": "Description",
    "danh_muc": {
      "id": 1,
      "ten": "Category name"
    },
    "nguoi_dung": {
      "id": 1,
      "ho_ten": "User name"
    },
    "trang_thai": 0,
    "uu_tien": "urgent",
    "vi_do": "10.7769",
    "kinh_do": "106.7009",
    "created_at": "2025-12-07T08:55:15Z"
  },
  "user": {
    "id": 1,
    "ho_ten": "User full name",
    "email": "user@example.com"
  }
}
```

## Support

For issues or questions:

1. Check Reverb logs: `docker exec cityresq-coreapi tail -f /var/log/supervisor/reverb.log`
2. Check browser console for WebSocket errors
3. Test with `./scripts/test-create-report.sh`
4. Verify .env variables are correct
5. Restart Reverb: `docker exec cityresq-coreapi supervisorctl restart reverb`

---

**Last Updated**: December 7, 2025  
**Laravel Version**: 11.x  
**Reverb Version**: 1.6.3  
**Pusher.js Version**: 8.4.0-rc2
