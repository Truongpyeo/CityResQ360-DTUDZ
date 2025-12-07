/**
 * Notification Service - Send notifications via RabbitMQ
 */
const amqp = require('amqplib');
const logger = require('../utils/logger');

const RABBITMQ_URL = process.env.RABBITMQ_URL || 'amqp://cityresq:cityresq_rabbitmq@rabbitmq:5672';
const EXCHANGE_NAME = 'cityresq.notifications';

let connection = null;
let channel = null;

/**
 * Initialize RabbitMQ connection
 */
async function initializeRabbitMQ() {
  try {
    connection = await amqp.connect(RABBITMQ_URL);
    channel = await connection.createChannel();
    
    // Declare exchange for notifications
    await channel.assertExchange(EXCHANGE_NAME, 'topic', { durable: true });
    
    logger.info('RabbitMQ notification service initialized', {
      exchange: EXCHANGE_NAME,
    });
    
    return true;
  } catch (error) {
    logger.error('Failed to initialize RabbitMQ', { error: error.message });
    return false;
  }
}

/**
 * Send notification to agency about new incident assignment
 * @param {Object} incident - Incident object
 * @param {Object} agency - Agency object
 * @param {string} type - Notification type (ASSIGNED, OVERDUE, etc.)
 */
async function sendAgencyNotification(incident, agency, type = 'ASSIGNED') {
  try {
    if (!channel) {
      logger.warn('RabbitMQ channel not initialized, attempting reconnect...');
      await initializeRabbitMQ();
    }

    const notification = {
      type,
      incident_id: incident.id,
      report_id: incident.report_id,
      priority: incident.priority,
      status: incident.status,
      due_date: incident.due_date,
      agency: {
        id: agency.id,
        name: agency.ten_co_quan,
        email: agency.email_lien_he,
        phone: agency.so_dien_thoai,
      },
      timestamp: new Date().toISOString(),
      message: getNotificationMessage(type, incident, agency),
    };

    const routingKey = `notification.agency.${type.toLowerCase()}`;
    
    await channel.publish(
      EXCHANGE_NAME,
      routingKey,
      Buffer.from(JSON.stringify(notification)),
      { persistent: true }
    );

    logger.info('Agency notification sent', {
      type,
      incident_id: incident.id,
      agency_id: agency.id,
      routing_key: routingKey,
    });

    return true;
  } catch (error) {
    logger.error('Failed to send agency notification', {
      type,
      incident_id: incident?.id,
      agency_id: agency?.id,
      error: error.message,
    });
    return false;
  }
}

/**
 * Send SLA violation notification
 * @param {Object} incident - Incident object
 */
async function sendSLANotification(incident) {
  try {
    if (!channel) {
      await initializeRabbitMQ();
    }

    const notification = {
      type: 'SLA_VIOLATION',
      incident_id: incident.id,
      report_id: incident.report_id,
      priority: incident.priority,
      status: incident.status,
      due_date: incident.due_date,
      overdue_minutes: Math.floor((Date.now() - new Date(incident.due_date)) / 60000),
      agency_id: incident.assigned_agency_id,
      timestamp: new Date().toISOString(),
      message: `⚠️ SLA VIOLATION: Incident #${incident.id} (Priority: ${incident.priority}) is overdue!`,
    };

    const routingKey = 'notification.sla.violation';
    
    await channel.publish(
      EXCHANGE_NAME,
      routingKey,
      Buffer.from(JSON.stringify(notification)),
      { persistent: true }
    );

    logger.info('SLA violation notification sent', {
      incident_id: incident.id,
      overdue_minutes: notification.overdue_minutes,
    });

    return true;
  } catch (error) {
    logger.error('Failed to send SLA notification', {
      incident_id: incident?.id,
      error: error.message,
    });
    return false;
  }
}

/**
 * Send SLA approaching deadline warning
 * @param {Object} incident - Incident object
 * @param {number} minutesRemaining - Minutes until deadline
 */
async function sendSLAWarning(incident, minutesRemaining) {
  try {
    if (!channel) {
      await initializeRabbitMQ();
    }

    const notification = {
      type: 'SLA_WARNING',
      incident_id: incident.id,
      report_id: incident.report_id,
      priority: incident.priority,
      status: incident.status,
      due_date: incident.due_date,
      minutes_remaining: minutesRemaining,
      agency_id: incident.assigned_agency_id,
      timestamp: new Date().toISOString(),
      message: `⏰ SLA WARNING: Incident #${incident.id} deadline in ${minutesRemaining} minutes!`,
    };

    const routingKey = 'notification.sla.warning';
    
    await channel.publish(
      EXCHANGE_NAME,
      routingKey,
      Buffer.from(JSON.stringify(notification)),
      { persistent: true }
    );

    logger.info('SLA warning notification sent', {
      incident_id: incident.id,
      minutes_remaining: minutesRemaining,
    });

    return true;
  } catch (error) {
    logger.error('Failed to send SLA warning', {
      incident_id: incident?.id,
      error: error.message,
    });
    return false;
  }
}

/**
 * Generate notification message based on type
 * @param {string} type 
 * @param {Object} incident 
 * @param {Object} agency 
 * @returns {string}
 */
function getNotificationMessage(type, incident, agency) {
  switch (type) {
    case 'ASSIGNED':
      return `🚨 New incident #${incident.id} assigned to ${agency.ten_co_quan}. Priority: ${incident.priority}, Due: ${new Date(incident.due_date).toLocaleString('vi-VN')}`;
    
    case 'AUTO_DISPATCHED':
      return `🤖 Incident #${incident.id} auto-dispatched to ${agency.ten_co_quan} (nearest available agency)`;
    
    case 'STATUS_UPDATED':
      return `📝 Incident #${incident.id} status updated to: ${incident.status}`;
    
    case 'RESOLVED':
      return `✅ Incident #${incident.id} has been resolved by ${agency.ten_co_quan}`;
    
    default:
      return `Incident #${incident.id} - ${type}`;
  }
}

/**
 * Close RabbitMQ connection
 */
async function closeConnection() {
  try {
    if (channel) {
      await channel.close();
    }
    if (connection) {
      await connection.close();
    }
    logger.info('RabbitMQ connection closed');
  } catch (error) {
    logger.error('Error closing RabbitMQ connection', { error: error.message });
  }
}

// Graceful shutdown
process.on('SIGINT', async () => {
  await closeConnection();
  process.exit(0);
});

module.exports = {
  initializeRabbitMQ,
  sendAgencyNotification,
  sendSLANotification,
  sendSLAWarning,
  closeConnection,
};
