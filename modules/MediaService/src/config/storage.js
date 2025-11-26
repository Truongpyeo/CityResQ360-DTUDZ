const Minio = require('minio');

const minioClient = new Minio.Client({
  endPoint: process.env.MINIO_ENDPOINT,
  port: parseInt(process.env.MINIO_PORT),
  useSSL: process.env.MINIO_USE_SSL === 'true',
  accessKey: process.env.MINIO_ACCESS_KEY,
  secretKey: process.env.MINIO_SECRET_KEY
});

const bucketName = process.env.MINIO_BUCKET;

// Ensure bucket exists
const ensureBucket = async () => {
  try {
    const exists = await minioClient.bucketExists(bucketName);
    if (!exists) {
      await minioClient.makeBucket(bucketName, 'us-east-1');
      // Set bucket policy to public read
      const policy = {
        Version: '2012-10-17',
        Statement: [{
          Effect: 'Allow',
          Principal: { AWS: ['*'] },
          Action: ['s3:GetObject'],
          Resource: [`arn:aws:s3:::${bucketName}/*`]
        }]
      };
      await minioClient.setBucketPolicy(bucketName, JSON.stringify(policy));
      console.log(`✅ Bucket ${bucketName} created and configured`);
    } else {
      console.log(`✅ Bucket ${bucketName} already exists`);
    }
  } catch (error) {
    console.error('❌ MinIO bucket error:', error);
    // Don't exit, service can still work
  }
};

// Run async, don't block startup
setTimeout(() => {
  ensureBucket().catch(err => {
    console.error('Failed to ensure bucket:', err);
  });
}, 2000);

module.exports = {
  minioClient,
  bucketName
};
