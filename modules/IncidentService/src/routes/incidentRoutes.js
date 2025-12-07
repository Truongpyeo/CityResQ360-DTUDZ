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

const express = require('express');
const router = express.Router();
const IncidentController = require('../controllers/IncidentController');
const { authenticate, authorize } = require('../middleware/authMiddleware');
const {
  validateCreateIncident,
  validateAssignIncident,
  validateUpdateStatus,
  validateGetIncident,
  validateListIncidents,
} = require('../validators/incidentValidators');

// All routes require authentication
router.use(authenticate);

// Create incident - All authenticated users can create
router.post('/', validateCreateIncident, IncidentController.createIncident);

// List incidents - All authenticated users can list
router.get('/', validateListIncidents, IncidentController.listIncidents);

// Get incident details - All authenticated users can view
router.get('/:id', validateGetIncident, IncidentController.getIncident);

// Assign incident - Only OFFICER and ADMIN can assign
router.put(
  '/:id/assign',
  authorize(['OFFICER', 'ADMIN']),
  validateAssignIncident,
  IncidentController.assignIncident
);

// Update incident status - Only OFFICER and ADMIN can update status
router.put(
  '/:id/status',
  authorize(['OFFICER', 'ADMIN']),
  validateUpdateStatus,
  IncidentController.updateStatus
);

module.exports = router;
