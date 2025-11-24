const Media = require('../models/Media');
const storageService = require('../services/storageService');
const imageProcessor = require('../services/imageProcessor');
const path = require('path');
const fs = require('fs').promises;
const { v4: uuidv4 } = require('uuid');

class MediaController {
  async upload(req, res) {
    try {
      const file = req.file;
      const userId = req.user.id;
      const { type, lien_ket_den, mo_ta, id_lien_ket } = req.body;
      
      // Auto-detect type if not provided
      const mediaType = type || (req.file.mimetype.startsWith('image/') ? 'image' : 'video');

      if (!file) {
        return res.status(400).json({
          success: false,
          message: 'Không có file được upload'
        });
      }

      // Generate unique filename
      const date = new Date();
      const dateFolder = `${date.getFullYear()}/${String(date.getMonth() + 1).padStart(2, '0')}/${String(date.getDate()).padStart(2, '0')}`;
      const uniqueFilename = `${uuidv4()}${path.extname(file.originalname)}`;
      const storagePath = `${dateFolder}/${uniqueFilename}`;

      let processedData;
      let thumbnailUrl = null;

      // Process based on file type
      if (mediaType === 'image' || file.mimetype.startsWith('image/')) {
        // Process image
        const outputDir = path.join(process.env.UPLOAD_DIR || './uploads', dateFolder);
        await fs.mkdir(outputDir, { recursive: true });

        processedData = await imageProcessor.processImage(file.path, outputDir);

        // Upload original
        const originalUrl = await storageService.uploadFile(
          processedData.original,
          storagePath,
          file.mimetype
        );

        // Upload thumbnail
        const thumbnailPath = `${dateFolder}/thumb_${uniqueFilename}`;
        thumbnailUrl = await storageService.uploadFile(
          processedData.thumbnail,
          thumbnailPath,
          'image/jpeg'
        );

        // Cleanup temp files
        await imageProcessor.cleanup([
          file.path,
          processedData.original,
          processedData.thumbnail
        ]);

        processedData.url = originalUrl;
      } else {
        // Video upload (simple version)
        const videoUrl = await storageService.uploadFile(
          file.path,
          storagePath,
          file.mimetype
        );
        
        // Cleanup temp file
        await fs.unlink(file.path);
        
        processedData = { 
          url: videoUrl,
          metadata: {}
        };
      }

      // Save to database
      const media = new Media({
        nguoi_dung_id: userId,
        loai_media: mediaType,
        ten_file_goc: file.originalname,
        ten_file_luu_tru: storagePath,
        duong_dan: processedData.url,
        duong_dan_thumbnail: thumbnailUrl,
        kich_thuoc: file.size,
        dinh_dang: file.mimetype,
        width: processedData.metadata?.width,
        height: processedData.metadata?.height,
        lien_ket_den: lien_ket_den || 'phan_anh',
        id_lien_ket: id_lien_ket || null,
        mo_ta: mo_ta || null
      });

      await media.save();

      return res.status(201).json({
        success: true,
        message: 'Upload thành công',
        data: {
          id: media._id,
          url: media.duong_dan,
          thumbnail_url: media.duong_dan_thumbnail,
          type: media.loai_media,
          kich_thuoc: media.kich_thuoc,
          dinh_dang: media.dinh_dang,
          width: media.width,
          height: media.height,
          created_at: media.ngay_tao
        }
      });
    } catch (error) {
      console.error('Upload error:', error);
      return res.status(500).json({
        success: false,
        message: 'Upload thất bại',
        error: error.message
      });
    }
  }

  async getById(req, res) {
    try {
      const media = await Media.findById(req.params.id);

      if (!media || media.trang_thai === 0) {
        return res.status(404).json({
          success: false,
          message: 'Không tìm thấy file'
        });
      }

      return res.json({
        success: true,
        data: media
      });
    } catch (error) {
      return res.status(500).json({
        success: false,
        message: error.message
      });
    }
  }

  async delete(req, res) {
    try {
      const media = await Media.findById(req.params.id);

      if (!media) {
        return res.status(404).json({
          success: false,
          message: 'Không tìm thấy file'
        });
      }

      // Check permission (only owner can delete)
      if (media.nguoi_dung_id !== req.user.id) {
        return res.status(403).json({
          success: false,
          message: 'Không có quyền xóa file này'
        });
      }

      // Delete from storage
      await storageService.deleteFile(media.ten_file_luu_tru);
      if (media.duong_dan_thumbnail) {
        const thumbnailPath = media.ten_file_luu_tru.replace(
          path.basename(media.ten_file_luu_tru), 
          `thumb_${path.basename(media.ten_file_luu_tru)}`
        );
        await storageService.deleteFile(thumbnailPath);
      }

      // Soft delete from database
      media.trang_thai = 0;
      await media.save();

      return res.json({
        success: true,
        message: 'Xóa file thành công'
      });
    } catch (error) {
      return res.status(500).json({
        success: false,
        message: error.message
      });
    }
  }

  async myMedia(req, res) {
    try {
      const userId = req.user.id;
      const { page = 1, limit = 20, type } = req.query;

      const query = {
        nguoi_dung_id: userId,
        trang_thai: 1
      };

      if (type) {
        query.loai_media = type;
      }

      const media = await Media.find(query)
        .sort({ ngay_tao: -1 })
        .limit(limit * 1)
        .skip((page - 1) * limit);

      const count = await Media.countDocuments(query);

      return res.json({
        success: true,
        data: media,
        meta: {
          current_page: parseInt(page),
          total: count,
          per_page: parseInt(limit)
        }
      });
    } catch (error) {
      return res.status(500).json({
        success: false,
        message: error.message
      });
    }
  }
}

module.exports = new MediaController();
