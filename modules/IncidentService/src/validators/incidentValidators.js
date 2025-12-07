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

const { body, param, query, validationResult } = require('express-validator');
const { BadRequestError } = require('../middleware/errorHandler');

/**
 * Middleware to handle validation results
 */
const handleValidationErrors = (req, res, next) => {
  const errors = validationResult(req);
  if (!errors.isEmpty()) {
    throw new BadRequestError('Validation failed', errors.array());
  }
  next();
};

/**
 * Validation rules for creating an incident
 */
const validateCreateIncident = [
  body('report_id')
    .isInt({ min: 1 })
    .withMessage('report_id must be a positive integer'),
  
  body('priority')
    .optional()
    .isIn(['LOW', 'MEDIUM', 'HIGH', 'CRITICAL'])
    .withMessage('priority must be one of: LOW, MEDIUM, HIGH, CRITICAL'),

  body('assigned_agency_id')
    .optional()
    .isInt({ min: 1 })
    .withMessage('assigned_agency_id must be a positive integer'),

  body('assigned_user_id')
    .optional()
    .isInt({ min: 1 })
    .withMessage('assigned_user_id must be a positive integer'),

  body('notes')
    .optional()
    .isString()
    .trim()
    .isLength({ max: 1000 })
    .withMessage('notes must be a string with max 1000 characters'),

  handleValidationErrors,
];

/**
 * Validation rules for assigning an incident
 */
const validateAssignIncident = [
  param('id')
    .isInt({ min: 1 })
    .withMessage('Incident ID must be a positive integer'),

  body('assigned_agency_id')
    .optional()
    .isInt({ min: 1 })
    .withMessage('assigned_agency_id must be a positive integer'),

  body('assigned_user_id')
    .optional()
    .isInt({ min: 1 })
    .withMessage('assigned_user_id must be a positive integer'),

  body('notes')
    .optional()
    .isString()
    .trim()
    .isLength({ max: 1000 })
    .withMessage('notes must be a string with max 1000 characters'),

  // At least one of assigned_agency_id or assigned_user_id must be present
  body()
    .custom((value) => {
      if (!value.assigned_agency_id && !value.assigned_user_id) {
        throw new Error('Either assigned_agency_id or assigned_user_id must be provided');
      }
      return true;
    }),

  handleValidationErrors,
];

/**
 * Validation rules for updating incident status
 */
const validateUpdateStatus = [
  param('id')
    .isInt({ min: 1 })
    .withMessage('Incident ID must be a positive integer'),

  body('status')
    .isIn(['PENDING', 'IN_PROGRESS', 'RESOLVED', 'CLOSED'])
    .withMessage('status must be one of: PENDING, IN_PROGRESS, RESOLVED, CLOSED'),

  body('notes')
    .optional()
    .isString()
    .trim()
    .isLength({ max: 1000 })
    .withMessage('notes must be a string with max 1000 characters'),

  handleValidationErrors,
];

/**
 * Validation rules for getting a single incident
 */
const validateGetIncident = [
  param('id')
    .isInt({ min: 1 })
    .withMessage('Incident ID must be a positive integer'),

  handleValidationErrors,
];

/**
 * Validation rules for listing incidents
 */
const validateListIncidents = [
  query('status')
    .optional()
    .isIn(['PENDING', 'IN_PROGRESS', 'RESOLVED', 'CLOSED'])
    .withMessage('status must be one of: PENDING, IN_PROGRESS, RESOLVED, CLOSED'),

  query('priority')
    .optional()
    .isIn(['LOW', 'MEDIUM', 'HIGH', 'CRITICAL'])
    .withMessage('priority must be one of: LOW, MEDIUM, HIGH, CRITICAL'),

  query('assigned_agency_id')
    .optional()
    .isInt({ min: 1 })
    .withMessage('assigned_agency_id must be a positive integer'),

  query('assigned_user_id')
    .optional()
    .isInt({ min: 1 })
    .withMessage('assigned_user_id must be a positive integer'),

  query('page')
    .optional()
    .isInt({ min: 1 })
    .withMessage('page must be a positive integer'),

  query('limit')
    .optional()
    .isInt({ min: 1, max: 100 })
    .withMessage('limit must be between 1 and 100'),

  handleValidationErrors,
];

module.exports = {
  validateCreateIncident,
  validateAssignIncident,
  validateUpdateStatus,
  validateGetIncident,
  validateListIncidents,
};
