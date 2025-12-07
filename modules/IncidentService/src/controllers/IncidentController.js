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

const CORE_API_URL = process.env.CORE_API_URL || 'http://core-api:8000/api/v1';

// Create incident from report
const createIncident = async (req, res, next) => {
    try {
        const { report_id, priority, assigned_agency_id, assigned_user_id, notes } = req.body;

        logger.info(`Creating incident for report_id: ${report_id}`, { user: req.user?.email });

        const incident = await Incident.create({
            report_id,
            priority: priority || 'MEDIUM',
            status: 'PENDING',
            assigned_agency_id,
            assigned_user_id,
        });

        await WorkflowLog.create({
            incident_id: incident.id,
            action: 'CREATED',
            to_status: 'PENDING',
            notes: notes || 'Incident created from report',
            performed_by: req.user?.id,
        });

        logger.info(`Incident created successfully: ${incident.id}`);

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
