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
