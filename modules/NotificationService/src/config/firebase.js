import admin from 'firebase-admin';
import dotenv from 'dotenv';

dotenv.config();

// Initialize Firebase Admin SDK
// In production, use service account file or environment variables
const initializeFirebase = () => {
    try {
        if (process.env.FCM_PRIVATE_KEY) {
            admin.initializeApp({
                credential: admin.credential.cert({
                    projectId: process.env.FCM_PROJECT_ID,
                    clientEmail: process.env.FCM_CLIENT_EMAIL,
                    privateKey: process.env.FCM_PRIVATE_KEY.replace(/\\n/g, '\n'),
                }),
            });
            console.log('Firebase Admin Initialized');
        } else {
            console.warn('Firebase Admin NOT Initialized: Missing credentials');
        }
    } catch (error) {
        console.error('Firebase Initialization Error:', error);
    }
};

export default initializeFirebase;
export const messaging = admin.messaging;
