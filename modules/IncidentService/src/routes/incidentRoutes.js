const express = require('express');
const router = express.Router();
const IncidentController = require('../controllers/IncidentController');

router.post('/', IncidentController.createIncident);
router.get('/', IncidentController.listIncidents);
router.get('/:id', IncidentController.getIncident);
router.put('/:id/assign', IncidentController.assignIncident);
router.put('/:id/status', IncidentController.updateStatus);

module.exports = router;
