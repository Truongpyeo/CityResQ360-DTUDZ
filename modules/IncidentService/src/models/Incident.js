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

const { DataTypes } = require('sequelize');
const { sequelize } = require('../config/database');

const Incident = sequelize.define('Incident', {
    id: {
        type: DataTypes.INTEGER,
        primaryKey: true,
        autoIncrement: true,
    },
    report_id: {
        type: DataTypes.INTEGER,
        allowNull: true,  // Now optional - for direct incident creation
        unique: false,     // Allow null values (multiple nulls OK)
    },
    title: {
        type: DataTypes.STRING,
        allowNull: false,  // Required for direct creation
    },
    description: {
        type: DataTypes.TEXT,
        allowNull: false,  // Required for direct creation
    },
    location_latitude: {
        type: DataTypes.DECIMAL(10, 8),
        allowNull: true,
    },
    location_longitude: {
        type: DataTypes.DECIMAL(11, 8),
        allowNull: true,
    },
    address: {
        type: DataTypes.STRING,
        allowNull: true,
    },
    category: {
        type: DataTypes.STRING,
        allowNull: true,
    },
    external_id: {
        type: DataTypes.STRING,
        allowNull: true,
        comment: 'External system tracking ID',
    },
    external_system: {
        type: DataTypes.STRING,
        allowNull: true,
        comment: 'External system name',
    },
    assigned_agency_id: {
        type: DataTypes.INTEGER,
        allowNull: true,
    },
    assigned_user_id: {
        type: DataTypes.INTEGER,
        allowNull: true,
    },
    status: {
        type: DataTypes.ENUM('PENDING', 'IN_PROGRESS', 'RESOLVED', 'CLOSED'),
        defaultValue: 'PENDING',
    },
    priority: {
        type: DataTypes.ENUM('LOW', 'MEDIUM', 'HIGH', 'CRITICAL'),
        defaultValue: 'MEDIUM',
    },
    due_date: {
        type: DataTypes.DATE,
        allowNull: true,
    },
    assigned_at: {
        type: DataTypes.DATE,
        allowNull: true,
    },
    resolved_at: {
        type: DataTypes.DATE,
        allowNull: true,
    },
    closed_at: {
        type: DataTypes.DATE,
        allowNull: true,
    },
    notes: {
        type: DataTypes.TEXT,
        allowNull: true,
    },
}, {
    tableName: 'incidents',
    timestamps: true,
    underscored: true,
});

const WorkflowLog = sequelize.define('WorkflowLog', {
    id: {
        type: DataTypes.INTEGER,
        primaryKey: true,
        autoIncrement: true,
    },
    incident_id: {
        type: DataTypes.INTEGER,
        allowNull: false,
    },
    action: {
        type: DataTypes.STRING,
        allowNull: false,
    },
    from_status: {
        type: DataTypes.STRING,
    },
    to_status: {
        type: DataTypes.STRING,
    },
    performed_by: {
        type: DataTypes.INTEGER,
        allowNull: true,
    },
    user_id: {
        type: DataTypes.INTEGER,
    },
    notes: {
        type: DataTypes.TEXT,
    },
}, {
    tableName: 'workflow_logs',
    timestamps: true,
    underscored: true,
});

Incident.hasMany(WorkflowLog, { foreignKey: 'incident_id' });
WorkflowLog.belongsTo(Incident, { foreignKey: 'incident_id' });

module.exports = { Incident, WorkflowLog };
