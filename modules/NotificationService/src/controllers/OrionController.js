import Notification from '../models/Notification.js';

export const handleOrionNotification = async (req, res) => {
    try {
        console.log('📩 [ORION WEBHOOK] Received notification:', JSON.stringify(req.body, null, 2));

        const { data, subscriptionId } = req.body;

        if (!data || data.length === 0) {
            return res.status(200).json({ message: 'No data received' });
        }

        // Process each entity in the notification
        for (const entity of data) {
            console.log(`🔍 Processing entity: ${entity.id} (${entity.type})`);

            // Example: Check for high precipitation
            if (entity.type === 'WeatherObserved' && entity.precipitation) {
                const precipitation = entity.precipitation.value;
                console.log(`   🌧️ Precipitation: ${precipitation} mm`);

                if (precipitation > 50) {
                    console.log('   ⚠️ HIGH PRECIPITATION ALERT! Sending notification...');

                    // Create a notification record (mock)
                    // In real app, this would trigger FCM/Email
                    /*
                    await Notification.create({
                      title: 'High Rainfall Alert',
                      body: `Heavy rain detected at ${entity.address?.value?.addressLocality || 'unknown location'}: ${precipitation}mm`,
                      type: 'alert',
                      recipient: 'admin' // or broadcast
                    });
                    */
                }
            }
        }

        res.status(200).json({ message: 'Notification processed successfully' });
    } catch (error) {
        console.error('❌ Error processing Orion notification:', error);
        res.status(500).json({ message: 'Internal server error' });
    }
};
