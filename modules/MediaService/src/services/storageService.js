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
