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

const axios = require('axios');
const logger = require('../utils/logger');
const { Sequelize } = require('sequelize');

// Connect to CoreAPI MySQL to get agencies and reports data
const coreDB = new Sequelize({
  host: process.env.CORE_DB_HOST || 'mysql',
  port: process.env.CORE_DB_PORT || 3306,
  database: process.env.CORE_DB_NAME || 'cityresq_db',
  username: process.env.CORE_DB_USER || 'cityresq',
  password: process.env.CORE_DB_PASSWORD || 'cityresq_password',
  dialect: 'mysql',
  logging: false,
});

/**
 * Calculate distance between two points using Haversine formula
 * @param {number} lat1 - Latitude of point 1
 * @param {number} lon1 - Longitude of point 1
 * @param {number} lat2 - Latitude of point 2
 * @param {number} lon2 - Longitude of point 2
 * @returns {number} Distance in kilometers
 */
function calculateDistance(lat1, lon1, lat2, lon2) {
  const R = 6371; // Earth's radius in km
  const dLat = toRad(lat2 - lat1);
  const dLon = toRad(lon2 - lon1);
  
  const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
            Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) *
            Math.sin(dLon / 2) * Math.sin(dLon / 2);
  
  const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
  return R * c;
}

function toRad(degrees) {
  return degrees * (Math.PI / 180);
}

/**
 * Get report location from CoreAPI database
 * @param {number} reportId
 * @returns {Object} {vi_do, kinh_do, dia_chi}
 */
async function getReportLocation(reportId) {
  try {
    const [rows] = await coreDB.query(
      'SELECT vi_do, kinh_do, dia_chi FROM phan_anhs WHERE id = ?',
      {
        replacements: [reportId],
        type: Sequelize.QueryTypes.SELECT,
      }
    );
    return rows;
  } catch (error) {
    logger.error('Failed to get report location', { reportId, error: error.message });
    return null;
  }
}

/**
 * Get active agencies with their locations and workload
 * @returns {Array} List of agencies
 */
async function getActiveAgencies() {
  try {
    const query = `
      SELECT 
        cq.id,
        cq.ten_co_quan,
        cq.vi_do,
        cq.kinh_do,
        cq.dia_chi,
        cq.cap_do,
        cq.email_lien_he,
        cq.so_dien_thoai
      FROM co_quan_xu_lys cq
      WHERE cq.trang_thai = 1
        AND cq.vi_do IS NOT NULL
        AND cq.kinh_do IS NOT NULL
    `;
    
    const agencies = await coreDB.query(query, {
      type: Sequelize.QueryTypes.SELECT,
    });
    
    return agencies;
  } catch (error) {
    logger.error('Failed to get active agencies', { error: error.message });
    return [];
  }
}

/**
 * Get current workload for an agency from IncidentService
 * @param {number} agencyId
 * @returns {number} Number of active incidents
 */
async function getAgencyWorkload(agencyId) {
  try {
    const { Incident } = require('../models/Incident');
    const count = await Incident.count({
      where: {
        assigned_agency_id: agencyId,
        status: ['PENDING', 'IN_PROGRESS'],
      },
    });
    return count;
  } catch (error) {
    logger.error('Failed to get agency workload', { agencyId, error: error.message });
    return 999; // Return high number to deprioritize on error
  }
}

/**
 * Auto-dispatch incident to nearest suitable agency
 * @param {Object} incident - The incident to dispatch
 * @returns {Object|null} Selected agency or null
 */
async function autoDispatch(incident) {
  const AUTO_DISPATCH_ENABLED = process.env.AUTO_DISPATCH_ENABLED === 'true';
  const MAX_DISTANCE_KM = parseFloat(process.env.AUTO_DISPATCH_RADIUS_KM || 10);
  const MAX_WORKLOAD = parseInt(process.env.MAX_AGENCY_WORKLOAD || 5);

  if (!AUTO_DISPATCH_ENABLED) {
    logger.info('Auto-dispatch is disabled', { incident_id: incident.id });
    return null;
  }

  try {
    // Get location from incident first (direct creation) or report (CoreAPI flow)
    let reportLat, reportLon;
    
    if (incident.location_latitude && incident.location_longitude) {
      // Direct incident creation - use incident's location
      reportLat = parseFloat(incident.location_latitude);
      reportLon = parseFloat(incident.location_longitude);
      logger.info('Auto-dispatch: Using incident location (direct creation)', {
        incident_id: incident.id,
        lat: reportLat,
        lon: reportLon,
      });
    } else if (incident.report_id) {
      // CoreAPI flow - query report location
      const reportLocation = await getReportLocation(incident.report_id);
      if (!reportLocation || !reportLocation.vi_do || !reportLocation.kinh_do) {
        logger.warn('Report has no location data, cannot auto-dispatch', {
          incident_id: incident.id,
          report_id: incident.report_id,
        });
        return null;
      }
      reportLat = parseFloat(reportLocation.vi_do);
      reportLon = parseFloat(reportLocation.kinh_do);
      logger.info('Auto-dispatch: Using report location (CoreAPI flow)', {
        incident_id: incident.id,
        report_id: incident.report_id,
        lat: reportLat,
        lon: reportLon,
      });
    } else {
      logger.warn('Incident has no location data (no report_id or coordinates)', {
        incident_id: incident.id,
      });
      return null;
    }

    // Get all active agencies
    const agencies = await getActiveAgencies();
    if (agencies.length === 0) {
      logger.warn('No active agencies available for auto-dispatch');
      return null;
    }

    logger.info(`Found ${agencies.length} active agencies`);

    // Calculate distance and workload for each agency
    const agenciesWithScore = await Promise.all(
      agencies.map(async (agency) => {
        const distance = calculateDistance(
          reportLat,
          reportLon,
          agency.vi_do,
          agency.kinh_do
        );
        
        const workload = await getAgencyWorkload(agency.id);
        
        // Calculate score (lower is better)
        // Weight: distance * 2 + workload * 1
        const score = distance * 2 + workload;
        
        return {
          ...agency,
          distance,
          workload,
          score,
        };
      })
    );

    // Filter agencies within radius and below max workload
    const suitableAgencies = agenciesWithScore.filter(
      (agency) => agency.distance <= MAX_DISTANCE_KM && agency.workload < MAX_WORKLOAD
    );

    if (suitableAgencies.length === 0) {
      logger.warn('No suitable agencies found within criteria', {
        incident_id: incident.id,
        max_distance: MAX_DISTANCE_KM,
        max_workload: MAX_WORKLOAD,
      });
      return null;
    }

    // Sort by score (lowest first)
    suitableAgencies.sort((a, b) => a.score - b.score);

    const selectedAgency = suitableAgencies[0];
    
    logger.info('Auto-dispatch: Agency selected', {
      incident_id: incident.id,
      agency_id: selectedAgency.id,
      agency_name: selectedAgency.ten_co_quan,
      distance: selectedAgency.distance.toFixed(2) + ' km',
      workload: selectedAgency.workload,
      score: selectedAgency.score.toFixed(2),
    });

    return selectedAgency;
  } catch (error) {
    logger.error('Auto-dispatch failed', {
      incident_id: incident.id,
      error: error.message,
      stack: error.stack,
    });
    return null;
  }
}

/**
 * Calculate SLA due date based on priority
 * @param {string} priority - CRITICAL, HIGH, MEDIUM, LOW
 * @returns {Date} Due date
 */
function calculateDueDate(priority) {
  const now = new Date();
  let hours;

  switch (priority) {
    case 'CRITICAL':
      hours = parseInt(process.env.SLA_CRITICAL_PRIORITY_HOURS || 6);
      break;
    case 'HIGH':
      hours = parseInt(process.env.SLA_HIGH_PRIORITY_HOURS || 12);
      break;
    case 'MEDIUM':
      hours = parseInt(process.env.SLA_DEFAULT_HOURS || 24);
      break;
    case 'LOW':
      hours = 48;
      break;
    default:
      hours = 24;
  }

  return new Date(now.getTime() + hours * 60 * 60 * 1000);
}

module.exports = {
  autoDispatch,
  calculateDueDate,
  calculateDistance,
  getReportLocation,
  getActiveAgencies,
  getAgencyWorkload,
};
