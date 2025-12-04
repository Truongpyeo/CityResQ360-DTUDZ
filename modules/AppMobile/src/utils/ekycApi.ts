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

import api from './Api';

export const ekycApi = {
  // Request OTP
  requestOtp: async (identifier: string, type: 'phone' | 'email') => {
    try {
      const response = await api.post('/auth/request-otp', {
        identifier,
        type
      });
      return response.data;
    } catch (error) {
      console.error('Error requesting OTP:', error);
      throw error;
    }
  },

  // Verify OTP
  verifyOtp: async (identifier: string, type: 'phone' | 'email', otp: string) => {
    try {
      const response = await api.post('/auth/verify-otp', {
        identifier,
        type,
        otp
      });
      return response.data;
    } catch (error) {
      console.error('Error verifying OTP:', error);
      throw error;
    }
  },

  // Upload ID Card Images
  uploadIdCard: async (type: 'front' | 'back', file: any) => {
    try {
      const formData = new FormData();
      formData.append('type', type);
      formData.append('file', file);

      const response = await api.post('/ekyc/upload-id-card', formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      });
      return response.data;
    } catch (error) {
      console.error('Error uploading ID card:', error);
      throw error;
    }
  },

  // Upload Selfie
  uploadSelfie: async (file: any) => {
    try {
      const formData = new FormData();
      formData.append('file', file);

      const response = await api.post('/ekyc/upload-selfie', formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      });
      return response.data;
    } catch (error) {
      console.error('Error uploading selfie:', error);
      throw error;
    }
  },

  // Submit Personal Information
  submitInformation: async (data: {
    fullName: string;
    idNumber: string;
    dateOfBirth: string;
    address: string;
    nationality: string;
  }) => {
    try {
      const response = await api.post('/ekyc/submit-information', data);
      return response.data;
    } catch (error) {
      console.error('Error submitting information:', error);
      throw error;
    }
  },

  // Get eKYC Status
  getStatus: async () => {
    try {
      const response = await api.get('/ekyc/status');
      return response.data;
    } catch (error) {
      console.error('Error getting eKYC status:', error);
      throw error;
    }
  },
};

export default ekycApi;
