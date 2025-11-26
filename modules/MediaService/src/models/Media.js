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
