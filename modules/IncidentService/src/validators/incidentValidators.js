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
 * Supports both CoreAPI flow (report_id) and direct creation (title + description + location)
 */
const validateCreateIncident = [
  // Option 1: CoreAPI flow with report_id
  body('report_id')
    .optional()
    .isInt({ min: 1 })
    .withMessage('report_id must be a positive integer'),
  
  // Option 2: Direct creation fields
  body('title')
    .optional()
    .isString()
    .trim()
    .isLength({ min: 5, max: 255 })
    .withMessage('title must be between 5 and 255 characters'),
  
  body('description')
    .optional()
    .isString()
    .trim()
    .isLength({ min: 10, max: 2000 })
    .withMessage('description must be between 10 and 2000 characters'),
  
  body('location_latitude')
    .optional()
    .isFloat({ min: -90, max: 90 })
    .withMessage('location_latitude must be between -90 and 90'),
  
  body('location_longitude')
    .optional()
    .isFloat({ min: -180, max: 180 })
    .withMessage('location_longitude must be between -180 and 180'),
  
  body('address')
    .optional()
    .isString()
    .trim()
    .isLength({ max: 500 })
    .withMessage('address must not exceed 500 characters'),
  
  body('category')
    .optional()
    .isString()
    .trim()
    .isLength({ max: 100 })
    .withMessage('category must not exceed 100 characters'),
  
  body('external_id')
    .optional()
    .isString()
    .trim()
    .isLength({ max: 100 })
    .withMessage('external_id must not exceed 100 characters'),
  
  body('external_system')
    .optional()
    .isString()
    .trim()
    .isLength({ max: 50 })
    .withMessage('external_system must not exceed 50 characters'),
  
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
