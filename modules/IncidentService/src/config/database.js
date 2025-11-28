const { Sequelize } = require('sequelize');
require('dotenv').config();

const sequelize = new Sequelize({
    host: process.env.POSTGRES_HOST || 'localhost',
    port: process.env.POSTGRES_PORT || 5434,
    database: process.env.POSTGRES_DB || 'incident_db',
    username: process.env.POSTGRES_USER || 'cityresq',
    password: process.env.POSTGRES_PASSWORD || 'cityresq_password',
    dialect: 'postgres',
    logging: false,
});

const testConnection = async () => {
    try {
        await sequelize.authenticate();
        console.log('Database connection established successfully.');
    } catch (error) {
        console.error('Unable to connect to the database:', error);
    }
};

module.exports = { sequelize, testConnection };
