const { minioClient, bucketName } = require('../config/storage');

class StorageService {
  async uploadFile(filePath, objectName, contentType) {
    try {
      const metaData = {
        'Content-Type': contentType
      };

      await minioClient.fPutObject(
        bucketName,
        objectName,
        filePath,
        metaData
      );

      // Generate public URL
      // Use PUBLIC_MEDIA_URL for client-facing URLs (e.g., https://media.cityresq360.io.vn)
      // Falls back to MinIO internal URL for development
      const publicUrl = process.env.PUBLIC_MEDIA_URL;

      if (publicUrl) {
        // Production: use public domain
        return `${publicUrl}/${bucketName}/${objectName}`;
      } else {
        // Development: use MinIO endpoint
        const baseUrl = `http://${process.env.MINIO_ENDPOINT}:${process.env.MINIO_PORT}`;
        return `${baseUrl}/${bucketName}/${objectName}`;
      }
    } catch (error) {
      throw new Error(`Upload failed: ${error.message}`);
    }
  }

  async deleteFile(objectName) {
    try {
      await minioClient.removeObject(bucketName, objectName);
      return true;
    } catch (error) {
      throw new Error(`Delete failed: ${error.message}`);
    }
  }

  async getFileUrl(objectName, expiry = 7 * 24 * 60 * 60) {
    try {
      const url = await minioClient.presignedGetObject(
        bucketName,
        objectName,
        expiry
      );
      return url;
    } catch (error) {
      throw new Error(`Get URL failed: ${error.message}`);
    }
  }
}

module.exports = new StorageService();
