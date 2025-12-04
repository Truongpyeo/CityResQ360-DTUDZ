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
