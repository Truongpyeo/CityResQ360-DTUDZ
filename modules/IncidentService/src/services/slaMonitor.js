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

const cron = require('node-cron');
const { Incident } = require('../models/Incident');
const logger = require('../utils/logger');
const { Op } = require('sequelize');
const { sendSLANotification, sendSLAWarning } = require('./notificationService');

/**
 * Check for overdue incidents and send notifications
 */
async function checkOverdueIncidents() {
  try {
    const now = new Date();
    
    // Find incidents that are overdue
    const overdueIncidents = await Incident.findAll({
      where: {
        status: {
          [Op.in]: ['PENDING', 'IN_PROGRESS'],
        },
        due_date: {
          [Op.lt]: now,
        },
      },
      order: [['due_date', 'ASC']],
    });

    if (overdueIncidents.length === 0) {
      logger.info('SLA check: No overdue incidents');
      return;
    }

    logger.warn(`SLA check: Found ${overdueIncidents.length} overdue incidents`, {
      count: overdueIncidents.length,
    });

    for (const incident of overdueIncidents) {
      const overdueMinutes = Math.floor((now - incident.due_date) / 1000 / 60);
      
      logger.warn('Overdue incident detected', {
        incident_id: incident.id,
        report_id: incident.report_id,
        priority: incident.priority,
        status: incident.status,
        due_date: incident.due_date,
        overdue_minutes: overdueMinutes,
        assigned_agency_id: incident.assigned_agency_id,
        assigned_user_id: incident.assigned_user_id,
      });

      // TODO: Send notification to RabbitMQ
      await sendOverdueNotification(incident, overdueMinutes);
    }
  } catch (error) {
    logger.error('SLA check failed', {
      error: error.message,
      stack: error.stack,
    });
  }
}

/**
 * Send overdue notification (placeholder for RabbitMQ integration)
 * @param {Object} incident
 * @param {number} overdueMinutes
 */
async function sendOverdueNotification(incident, overdueMinutes) {
  // TODO: Integrate with NotificationService via RabbitMQ
  logger.info('Sending overdue notification', {
    incident_id: incident.id,
    report_id: incident.report_id,
    overdue_minutes: overdueMinutes,
    notification_type: 'SLA_OVERDUE',
  });

  // Placeholder for actual notification logic
  // Example:
  // await publishToRabbitMQ('incident.overdue', {
  //   incident_id: incident.id,
  //   report_id: incident.report_id,
  //   priority: incident.priority,
  //   overdue_minutes: overdueMinutes,
  //   assigned_user_id: incident.assigned_user_id,
  // });
}

/**
 * Check for incidents approaching SLA deadline (warning)
 */
async function checkApproachingSLA() {
  try {
    const now = new Date();
    const warningThreshold = new Date(now.getTime() + 60 * 60 * 1000); // 1 hour before due

    const approachingIncidents = await Incident.findAll({
      where: {
        status: {
          [Op.in]: ['PENDING', 'IN_PROGRESS'],
        },
        due_date: {
          [Op.between]: [now, warningThreshold],
        },
      },
    });

    if (approachingIncidents.length > 0) {
      logger.warn(`SLA warning: ${approachingIncidents.length} incidents approaching deadline`, {
        count: approachingIncidents.length,
      });

      for (const incident of approachingIncidents) {
        const minutesLeft = Math.floor((incident.due_date - now) / 1000 / 60);
        
        logger.warn('Incident approaching SLA deadline', {
          incident_id: incident.id,
          report_id: incident.report_id,
          priority: incident.priority,
          minutes_left: minutesLeft,
        });

        // Send warning notification
        await sendSLAWarning(incident, minutesLeft);
      }
    }
  } catch (error) {
    logger.error('SLA warning check failed', { error: error.message });
  }
}

/**
 * Get SLA statistics
 * @returns {Object} SLA statistics
 */
async function getSLAStatistics() {
  try {
    const now = new Date();

    const [total, overdue, onTime, approaching] = await Promise.all([
      Incident.count({
        where: {
          status: {
            [Op.in]: ['PENDING', 'IN_PROGRESS'],
          },
        },
      }),
      Incident.count({
        where: {
          status: {
            [Op.in]: ['PENDING', 'IN_PROGRESS'],
          },
          due_date: {
            [Op.lt]: now,
          },
        },
      }),
      Incident.count({
        where: {
          status: {
            [Op.in]: ['PENDING', 'IN_PROGRESS'],
          },
          due_date: {
            [Op.gt]: new Date(now.getTime() + 60 * 60 * 1000),
          },
        },
      }),
      Incident.count({
        where: {
          status: {
            [Op.in]: ['PENDING', 'IN_PROGRESS'],
          },
          due_date: {
            [Op.between]: [now, new Date(now.getTime() + 60 * 60 * 1000)],
          },
        },
      }),
    ]);

    return {
      total_active: total,
      overdue,
      on_time: onTime,
      approaching_deadline: approaching,
      compliance_rate: total > 0 ? ((onTime / total) * 100).toFixed(2) : 100,
    };
  } catch (error) {
    logger.error('Failed to get SLA statistics', { error: error.message });
    return null;
  }
}

/**
 * Initialize SLA monitoring cron jobs
 */
function initSLAMonitoring() {
  const checkInterval = process.env.SLA_CHECK_INTERVAL_MINUTES || '5';
  
  // Run every N minutes (default: 5)
  const cronExpression = `*/${checkInterval} * * * *`;
  
  logger.info(`Initializing SLA monitoring with interval: ${checkInterval} minutes`);

  // Schedule overdue check
  cron.schedule(cronExpression, async () => {
    logger.info('Running scheduled SLA check...');
    await checkOverdueIncidents();
    await checkApproachingSLA();
  });

  logger.info('SLA monitoring initialized successfully');
}

module.exports = {
  initSLAMonitoring,
  checkOverdueIncidents,
  checkApproachingSLA,
  getSLAStatistics,
};
