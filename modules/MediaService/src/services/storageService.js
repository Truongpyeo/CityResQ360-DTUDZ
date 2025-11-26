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
      // Use CDN URL if available, otherwise use MinIO endpoint
      const baseUrl = process.env.CDN_URL || `http://${process.env.MINIO_ENDPOINT}:${process.env.MINIO_PORT}`;
      const url = `${baseUrl}/${bucketName}/${objectName}`;
      
      return url;
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
