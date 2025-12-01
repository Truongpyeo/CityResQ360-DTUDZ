@component('mail::message')
# {{ $isRegenerated ? 'Secret Đã Được Tạo Lại' : 'Yêu Cầu Đã Được Duyệt' }}

@if($isRegenerated)
Xin chào {{ $credential->user->ho_ten }},

Secret key của bạn cho **{{ $moduleName }}** đã được tạo lại.
@else
Xin chào {{ $credential->user->ho_ten }},

Yêu cầu sử dụng **{{ $moduleName }}** của bạn đã được duyệt!
@endif

## 🔑 API Credentials

**Client ID:**
```
{{ $clientId }}
```

**JWT Secret:**
```
{{ $jwtSecret }}
```

**Base URL:**
```
{{ $baseUrl }}
```

⚠️ **LƯU Ý QUAN TRỌNG:**
- Vui lòng lưu JWT Secret ở nơi an toàn
- Secret này sẽ không hiển thị lại
- Không commit secret vào Git
- Lưu trong file `.env` của project

## 🚀 Quick Start

Thêm vào file `.env` của bạn:

```bash
MEDIASERVICE_CLIENT_ID={{ $clientId }}
MEDIASERVICE_JWT_SECRET={{ $jwtSecret }}
MEDIASERVICE_URL={{ $baseUrl }}
```

@component('mail::button', ['url' => $docsUrl])
Xem Tài Liệu Đầy Đủ
@endcomponent

Nếu có câu hỏi, vui lòng liên hệ support.

Trân trọng,<br>
{{ config('app.name') }} Team
@endcomponent
