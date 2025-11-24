const express = require('express');
const router = express.Router();
const mediaController = require('../controllers/mediaController');
const authMiddleware = require('../middlewares/auth');
const uploadMiddleware = require('../middlewares/upload');

// All routes require authentication
router.use(authMiddleware);

router.post('/upload', uploadMiddleware.single('file'), mediaController.upload);
router.get('/my', mediaController.myMedia);
router.get('/:id', mediaController.getById);
router.delete('/:id', mediaController.delete);

module.exports = router;
