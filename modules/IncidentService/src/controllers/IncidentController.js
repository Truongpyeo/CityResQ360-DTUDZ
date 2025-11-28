const { Incident, WorkflowLog } = require('../models/Incident');
const axios = require('axios');

const CORE_API_URL = process.env.CORE_API_URL || 'http://coreapi:8000/api/v1';

// Create incident from report
const createIncident = async (req, res) => {
    try {
        const { report_id, priority } = req.body;

        const incident = await Incident.create({
            report_id,
            priority: priority || 'medium',
            status: 'pending',
        });

        await WorkflowLog.create({
            incident_id: incident.id,
            action: 'created',
            to_status: 'pending',
            notes: 'Incident created from report',
        });

        res.status(201).json(incident);
    } catch (error) {
        res.status(500).json({ error: error.message });
    }
};

// Assign incident to agency/user
const assignIncident = async (req, res) => {
    try {
        const { id } = req.params;
        const { agency_id, user_id } = req.body;

        const incident = await Incident.findByPk(id);
        if (!incident) {
            return res.status(404).json({ error: 'Incident not found' });
        }

        const oldStatus = incident.status;
        incident.assigned_agency_id = agency_id;
        incident.assigned_to_user_id = user_id;
        incident.status = 'assigned';
        incident.assigned_at = new Date();
        await incident.save();

        await WorkflowLog.create({
            incident_id: incident.id,
            action: 'assigned',
            from_status: oldStatus,
            to_status: 'assigned',
            notes: `Assigned to agency ${agency_id}${user_id ? `, user ${user_id}` : ''}`,
        });

        res.json(incident);
    } catch (error) {
        res.status(500).json({ error: error.message });
    }
};

// Update incident status
const updateStatus = async (req, res) => {
    try {
        const { id } = req.params;
        const { status, notes } = req.body;

        const incident = await Incident.findByPk(id);
        if (!incident) {
            return res.status(404).json({ error: 'Incident not found' });
        }

        const oldStatus = incident.status;
        incident.status = status;

        if (status === 'resolved') {
            incident.resolved_at = new Date();
        }

        await incident.save();

        await WorkflowLog.create({
            incident_id: incident.id,
            action: 'status_changed',
            from_status: oldStatus,
            to_status: status,
            notes,
        });

        res.json(incident);
    } catch (error) {
        res.status(500).json({ error: error.message });
    }
};

// Get incident with logs
const getIncident = async (req, res) => {
    try {
        const { id } = req.params;

        const incident = await Incident.findByPk(id, {
            include: [{ model: WorkflowLog, as: 'WorkflowLogs' }]
        });

        if (!incident) {
            return res.status(404).json({ error: 'Incident not found' });
        }

        res.json(incident);
    } catch (error) {
        res.status(500).json({ error: error.message });
    }
};

// List incidents with filters
const listIncidents = async (req, res) => {
    try {
        const { status, priority, agency_id, limit = 20, offset = 0 } = req.query;

        const where = {};
        if (status) where.status = status;
        if (priority) where.priority = priority;
        if (agency_id) where.assigned_agency_id = agency_id;

        const incidents = await Incident.findAndCountAll({
            where,
            limit: parseInt(limit),
            offset: parseInt(offset),
            order: [['created_at', 'DESC']],
        });

        res.json(incidents);
    } catch (error) {
        res.status(500).json({ error: error.message });
    }
};

module.exports = {
    createIncident,
    assignIncident,
    updateStatus,
    getIncident,
    listIncidents,
};
