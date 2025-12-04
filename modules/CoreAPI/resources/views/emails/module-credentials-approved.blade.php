<?php
/*
 * CityResQ360-DTUDZ - Smart City Emergency Response System
 * Copyright (C) 2025 DTU-DZ Team
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

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
