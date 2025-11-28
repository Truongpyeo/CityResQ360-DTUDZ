const mqtt = require('mqtt');
require('dotenv').config();

const brokerUrl = process.env.MQTT_BROKER || 'mqtt://localhost:1883';
const topicPrefix = process.env.MQTT_TOPIC_PREFIX || 'cityresq/iot';

const client = mqtt.connect(brokerUrl, {
    clientId: 'iot_service_' + Math.random().toString(16).substr(2, 8),
    reconnectPeriod: 5000,
});

client.on('connect', () => {
    console.log('Connected to MQTT Broker');

    // Subscribe to all sensor topics
    const topic = `${topicPrefix}/+/+`; // cityresq/iot/{device_id}/{sensor_type}
    client.subscribe(topic, (err) => {
        if (!err) {
            console.log(`Subscribed to ${topic}`);
        } else {
            console.error('Subscription error:', err);
        }
    });
});

client.on('error', (err) => {
    console.error('MQTT Error:', err);
});

module.exports = {
    client,
    topicPrefix,
};
