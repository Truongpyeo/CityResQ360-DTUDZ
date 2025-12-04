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

/**
 * Cấu hình môi trường ứng dụng - FILE MẪU
 * Copy file này thành env.ts và điền thông tin thích hợp
 */

const env = {
  // API URL
  API_URL: 'https://api.example.com',

  // EKYC Configuration
  EKYC: {
    TOKEN_KEY: 'YOUR_EKYC_TOKEN_KEY',
    TOKEN_ID: 'YOUR_EKYC_TOKEN_ID',
    ACCESS_TOKEN: 'bearer YOUR_EKYC_JWT_TOKEN', // Đảm bảo JWT token có đủ 3 phần header.payload.signature
  },

  // MapTiler Configuration (Open Source Map Provider)
  MAPTILER_API_KEY: 'YOUR_MAPTILER_API_KEY', // Get free key from https://cloud.maptiler.com

  // Các cấu hình khác
  TIMEOUT: 15000,
  DEBUG: true,
};

export default env;
