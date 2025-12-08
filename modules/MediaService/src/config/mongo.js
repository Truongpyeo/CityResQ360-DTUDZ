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

const mongoose = require('mongoose');

const connectMongoDB = async function connectMongo() {
    try {
        // MongoDB is OPTIONAL for MediaService (legacy code)
        // Core functionality uses MySQL + MinIO only
        if (!process.env.MONGODB_URI) {
            console.log('⚠️  MongoDB URI not configured - skipping MongoDB connection');
            return null;
        }

        const conn = await mongoose.connect(process.env.MONGODB_URI || 'mongodb://localhost:27017/media_db', {
            serverSelectionTimeoutMS: 5000, // Fail fast if MongoDB unavailable
        });
        console.log(`✅ MongoDB connected: ${conn.connection.host}`);
        return conn;
    } catch (error) {
        console.warn('⚠️  MongoDB connection failed (non-critical):', error.message);
        console.log('ℹ️  MediaService will continue with MySQL + MinIO only');
        return null; // Return null instead of crashing
    }
};

module.exports = connectMongoDB;
