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

// Try to load Sharp, but allow service to work without it
let sharp;
try {
  sharp = require('sharp');
} catch (e) {
  console.warn('⚠️  Sharp not installed - image processing disabled');
  sharp = null;
}

const path = require('path');
const fs = require('fs').promises;

class ImageProcessor {
  async processImage(inputPath, outputDir) {
    // If Sharp not available, return null (skip processing)
    if (!sharp) {
      console.log('📸 Skipping image processing (Sharp not installed)');
      return null;
    }

    try {
      const filename = path.basename(inputPath, path.extname(inputPath));

      // Get image metadata
      const metadata = await sharp(inputPath).metadata();

      // Generate thumbnail
      const thumbnailPath = path.join(outputDir, `thumb_${filename}.jpg`);
      await sharp(inputPath)
        .resize(parseInt(process.env.THUMBNAIL_WIDTH), parseInt(process.env.THUMBNAIL_HEIGHT), {
          fit: 'cover',
          position: 'center'
        })
        .jpeg({ quality: 80 })
        .toFile(thumbnailPath);

      // Optimize original (if too large)
      const optimizedPath = path.join(outputDir, `${filename}.jpg`);
      const maxWidth = parseInt(process.env.OPTIMIZED_WIDTH);
      const maxHeight = parseInt(process.env.OPTIMIZED_HEIGHT);

      if (metadata.width > maxWidth || metadata.height > maxHeight) {
        await sharp(inputPath)
          .resize(maxWidth, maxHeight, {
            fit: 'inside',
            withoutEnlargement: true
          })
          .jpeg({ quality: 85 })
          .toFile(optimizedPath);
      } else {
        await sharp(inputPath)
          .jpeg({ quality: 85 })
          .toFile(optimizedPath);
      }

      return {
        original: optimizedPath,
        thumbnail: thumbnailPath,
        metadata: {
          width: metadata.width,
          height: metadata.height,
          format: metadata.format
        }
      };
    } catch (error) {
      throw new Error(`Image processing failed: ${error.message}`);
    }
  }

  async cleanup(files) {
    for (const file of files) {
      try {
        await fs.unlink(file);
      } catch (error) {
        console.error(`Cleanup error for ${file}:`, error);
      }
    }
  }
}

module.exports = new ImageProcessor();
