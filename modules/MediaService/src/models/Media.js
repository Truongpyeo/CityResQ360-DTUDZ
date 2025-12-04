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

const mongoose = require('mongoose');

const mediaSchema = new mongoose.Schema({
  nguoi_dung_id: {
    type: Number,
    required: true,
    index: true
  },
  loai_media: {
    type: String,
    enum: ['image', 'video'],
    required: true
  },
  ten_file_goc: {
    type: String,
    required: true
  },
  ten_file_luu_tru: {
    type: String,
    required: true,
    unique: true
  },
  duong_dan: {
    type: String,
    required: true
  },
  duong_dan_thumbnail: {
    type: String
  },
  kich_thuoc: {
    type: Number,
    required: true
  },
  dinh_dang: {
    type: String,
    required: true
  },
  width: Number,
  height: Number,
  thoi_luong: Number,  // for videos
  lien_ket_den: {
    type: String,
    enum: ['phan_anh', 'binh_luan', 'avatar'],
    required: true
  },
  id_lien_ket: Number,
  mo_ta: String,
  trang_thai: {
    type: Number,
    default: 1  // 1: active, 0: deleted
  }
}, {
  timestamps: {
    createdAt: 'ngay_tao',
    updatedAt: 'ngay_cap_nhat'
  }
});

// Indexes
mediaSchema.index({ nguoi_dung_id: 1, trang_thai: 1 });
mediaSchema.index({ lien_ket_den: 1, id_lien_ket: 1 });

module.exports = mongoose.model('Media', mediaSchema);
