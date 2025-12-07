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

const { Incident, WorkflowLog } = require('../models/Incident');
const axios = require('axios');
const logger = require('../utils/logger');
const { NotFoundError, BadRequestError } = require('../middleware/errorHandler');
const { autoDispatch, calculateDueDate } = require('../services/autoDispatch');
const { sendAgencyNotification } = require('../services/notificationService');

const CORE_API_URL = process.env.CORE_API_URL || 'http://core-api:8000/api/v1';

// Create incident from report
const createIncident = async (req, res, next) => {
    try {
        const { 
            report_id, 
            title,
            description,
            location_latitude,
            location_longitude,
            address,
            category,
            priority, 
            assigned_agency_id, 
            assigned_user_id, 
            notes,
            external_id,
            external_system 
        } = req.body;

        logger.info('Creating incident', { 
            report_id, 
            title,
            external_id,
            user: req.user?.email 
        });

        // Validate: Either report_id OR (title + description + location) required
        if (!report_id && (!title || !description)) {
            return res.status(400).json({
                success: false,
                message: 'Either report_id OR (title + description + location) is required',
            });
        }

        const priorityValue = priority || 'MEDIUM';
        
        // Calculate SLA due date
        const dueDate = calculateDueDate(priorityValue);

        const incident = await Incident.create({
            report_id: report_id || null,
            title: title || `Incident for Report #${report_id}`,
            description: description || notes || '',
            location_latitude: location_latitude || null,
            location_longitude: location_longitude || null,
            address: address || null,
            category: category || null,
            priority: priorityValue,
            status: 'PENDING',
            assigned_agency_id,
            assigned_user_id,
            due_date: dueDate,
            external_id: external_id || null,
            external_system: external_system || null,
        });

        await WorkflowLog.create({
            incident_id: incident.id,
            action: 'CREATED',
            to_status: 'PENDING',
            notes: notes || (report_id ? `Incident created from report ${report_id}` : 'Direct incident creation'),
            performed_by: typeof req.user?.id === 'number' ? req.user.id : 0,
        });

        logger.info(`Incident created successfully: ${incident.id}, due_date: ${dueDate}`);

        // 🔥 Auto-Dispatch if not manually assigned
        if (!assigned_agency_id && !assigned_user_id) {
            logger.info('Attempting auto-dispatch...', { incident_id: incident.id });
            
            const selectedAgency = await autoDispatch(incident);
            
            if (selectedAgency) {
                incident.assigned_agency_id = selectedAgency.id;
                incident.assigned_at = new Date();
                await incident.save();

                await WorkflowLog.create({
                    incident_id: incident.id,
                    action: 'AUTO_DISPATCHED',
                    to_status: 'PENDING',
                    notes: `Auto-dispatched to ${selectedAgency.ten_co_quan} (${selectedAgency.distance.toFixed(2)} km away)`,
                    performed_by: 0,
                });

                logger.info('Auto-dispatch successful', {
                    incident_id: incident.id,
                    agency_id: selectedAgency.id,
                    agency_name: selectedAgency.ten_co_quan,
                });

                // Send notification to agency
                await sendAgencyNotification(incident, selectedAgency, 'AUTO_DISPATCHED');
            } else {
                logger.warn('Auto-dispatch failed, incident remains unassigned', {
                    incident_id: incident.id,
                });
            }
        }

        // Reload to get updated data
        await incident.reload();

        res.status(201).json({
            success: true,
            message: 'Incident created successfully',
            data: incident,
        });

    } catch (error) {
        logger.error('Error creating incident:', error);
        next(error);
    }
};

// Assign incident to agency/user
const assignIncident = async (req, res, next) => {
    try {
        const { id } = req.params;
        const { assigned_agency_id, assigned_user_id, notes } = req.body;

        logger.info(`Assigning incident ${id}`, { 
            agency_id: assigned_agency_id, 
            user_id: assigned_user_id,
            performed_by: req.user?.email,
        });

        const incident = await Incident.findByPk(id);
        if (!incident) {
            throw new NotFoundError('Incident');
        }

        const oldStatus = incident.status;
        incident.assigned_agency_id = assigned_agency_id;
        incident.assigned_user_id = assigned_user_id;
        
        // Auto-update status if still pending
        if (incident.status === 'PENDING') {
            incident.status = 'IN_PROGRESS';
        }
        
        incident.assigned_at = new Date();
        await incident.save();

        await WorkflowLog.create({
            incident_id: incident.id,
            action: 'ASSIGNED',
            from_status: oldStatus,
            to_status: incident.status,
            notes: notes || `Assigned to agency ${assigned_agency_id}${assigned_user_id ? `, user ${assigned_user_id}` : ''}`,
            performed_by: req.user?.id,
        });

        logger.info(`Incident ${id} assigned successfully`);

        res.json({
            success: true,
            message: 'Incident assigned successfully',
            data: incident,
        });
    } catch (error) {
        logger.error(`Error assigning incident ${id}:`, error);
        next(error);
    }
};

// Update incident status
const updateStatus = async (req, res, next) => {
    try {
        const { id } = req.params;
        const { status, notes } = req.body;

        logger.info(`Updating incident ${id} status to ${status}`, { performed_by: req.user?.email });

        const incident = await Incident.findByPk(id);
        if (!incident) {
            throw new NotFoundError('Incident');
        }

        const oldStatus = incident.status;
        incident.status = status;

        if (status === 'RESOLVED') {
            incident.resolved_at = new Date();
        } else if (status === 'CLOSED') {
            incident.closed_at = new Date();
        }

        await incident.save();

        await WorkflowLog.create({
            incident_id: incident.id,
            action: 'STATUS_CHANGED',
            from_status: oldStatus,
            to_status: status,
            notes: notes || `Status changed from ${oldStatus} to ${status}`,
            performed_by: req.user?.id,
        });

        logger.info(`Incident ${id} status updated successfully`);

        res.json({
            success: true,
            message: 'Incident status updated successfully',
            data: incident,
        });
    } catch (error) {
        logger.error(`Error updating incident ${id} status:`, error);
        next(error);
    }
};

// Get incident with logs
const getIncident = async (req, res, next) => {
    try {
        const { id } = req.params;

        const incident = await Incident.findByPk(id, {
            include: [{ model: WorkflowLog, as: 'WorkflowLogs' }]
        });

        if (!incident) {
            throw new NotFoundError('Incident');
        }

        res.json({
            success: true,
            data: incident,
        });
    } catch (error) {
        logger.error(`Error fetching incident ${id}:`, error);
        next(error);
    }
};

// List incidents with filters
const listIncidents = async (req, res, next) => {
    try {
        const { 
            status, 
            priority, 
            assigned_agency_id, 
            assigned_user_id,
            page = 1, 
            limit = 20 
        } = req.query;

        const where = {};
        if (status) where.status = status;
        if (priority) where.priority = priority;
        if (assigned_agency_id) where.assigned_agency_id = assigned_agency_id;
        if (assigned_user_id) where.assigned_user_id = assigned_user_id;

        const offset = (parseInt(page) - 1) * parseInt(limit);

        const incidents = await Incident.findAndCountAll({
            where,
            limit: parseInt(limit),
            offset,
            order: [['created_at', 'DESC']],
        });

        const totalPages = Math.ceil(incidents.count / parseInt(limit));

        res.json({
            success: true,
            data: incidents.rows,
            pagination: {
                total: incidents.count,
                page: parseInt(page),
                limit: parseInt(limit),
                totalPages,
            },
        });
    } catch (error) {
        logger.error('Error listing incidents:', error);
        next(error);
    }
};

module.exports = {
    createIncident,
    assignIncident,
    updateStatus,
    getIncident,
    listIncidents,
};
