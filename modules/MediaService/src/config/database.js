const mysql = require('mysql2/promise');

// Create connection pool to CoreAPI database
const pool = mysql.createPool({
  host: process.env.COREAPI_DB_HOST || 'cityresq-mysql',
  user: process.env.COREAPI_DB_USER || 'root',
  password: process.env.COREAPI_DB_PASSWORD || '',
  database: process.env.COREAPI_DB_NAME || 'cityresq360',
  waitForConnections: true,
  connectionLimit: 10,
  queueLimit: 0,
  enableKeepAlive: true,
  keepAliveInitialDelay: 0
});

// Test connection
pool.getConnection()
  .then(connection => {
    console.log('✅ Connected to CoreAPI database');
    connection.release();
  })
  .catch(error => {
    console.error('❌ CoreAPI database connection failed:', error.message);
  });

module.exports = pool;
