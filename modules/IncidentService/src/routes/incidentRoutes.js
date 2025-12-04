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

router.post('/', IncidentController.createIncident);
router.get('/', IncidentController.listIncidents);
router.get('/:id', IncidentController.getIncident);
router.put('/:id/assign', IncidentController.assignIncident);
router.put('/:id/status', IncidentController.updateStatus);

module.exports = router;
