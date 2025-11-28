const express = require('express');
const router = express.Router();
const SensorController = require('../controllers/SensorController');

// GET /api/v1/sensors/:deviceId/:sensorType/latest
router.get('/:deviceId/:sensorType/latest', SensorController.getLatest);

// GET /api/v1/sensors/:deviceId/:sensorType/history
router.get('/:deviceId/:sensorType/history', SensorController.getHistory);

// POST /api/v1/sensors/command
router.post('/command', SensorController.publishCommand);

module.exports = router;
