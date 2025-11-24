const jwt = require('jsonwebtoken');

const authMiddleware = (req, res, next) => {
  try {
    const authHeader = req.headers.authorization;
    
    if (!authHeader || !authHeader.startsWith('Bearer ')) {
      return res.status(401).json({
        success: false,
        message: 'Unauthorized - No token provided'
      });
    }

    const token = authHeader.substring(7); // Remove 'Bearer '
    
    // Check if X-User-Id header is provided (from Laravel Sanctum)
    // This is the primary method for authentication from CoreAPI
    const userId = req.headers['x-user-id'];
    if (userId) {
      req.user = { id: parseInt(userId) };
      return next();
    }
    
    // Fallback to JWT if JWT_SECRET is set and X-User-Id is not provided
    if (!process.env.JWT_SECRET || process.env.JWT_SECRET.trim() === '' || process.env.JWT_SECRET === 'your-secret-key-here') {
      console.warn('⚠️ JWT_SECRET not properly configured, and no X-User-Id header');
      return res.status(401).json({
        success: false,
        message: 'Unauthorized - No user ID provided'
      });
    }
    
    try {
      const decoded = jwt.verify(token, process.env.JWT_SECRET);
      req.user = decoded;
    } catch (error) {
      return res.status(401).json({
        success: false,
        message: 'Unauthorized - Invalid token'
      });
    }
    
    next();
  } catch (error) {
    return res.status(401).json({
      success: false,
      message: 'Unauthorized - Invalid token'
    });
  }
};

module.exports = authMiddleware;
