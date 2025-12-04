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

import { Linking, Platform } from 'react-native';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { NavigationService } from '../navigation/NavigationService';

class DeepLinkHandler {
  static isInitialized = false;

  static init() {
    if (this.isInitialized) return;

    // Handle khi app được mở từ deep link
    Linking.getInitialURL().then(url => {
      if (url) this.handleURL(url);
    });

    // Handle khi deep link được click trong lúc app đang chạy
    Linking.addEventListener('url', ({ url }) => this.handleURL(url));

    this.isInitialized = true;
  }

  static async handleURL(url: string) {
    try {
      console.log('Deep Link URL:', url);
      
      // Parse URL để lấy ref code
      const parsedUrl = new URL(url);
      const match = parsedUrl.pathname.match(/\/ref\/([A-Z0-9]+)/i);
      
      if (!match) {
        console.log('No ref code found in URL');
        return;
      }
      
      const refCode = match[1];
      console.log('Extracted ref code:', refCode);

      // Lưu ref code vào AsyncStorage
      await AsyncStorage.setItem('pending_ref_code', refCode);
      
      // Check xem user đã login chưa
      const isLoggedIn = await AsyncStorage.getItem('user_token');
      
      if (isLoggedIn) {
        // Nếu đã login, mở màn Referral
        NavigationService.navigate('Referral');
      } else {
        // Chưa login thì mở màn Register
        NavigationService.navigate('Register');
      }
    } catch (error) {
      console.error('Error handling deep link:', error);
    }
  }

  static async getPendingRefCode() {
    try {
      const code = await AsyncStorage.getItem('pending_ref_code');
      if (code) {
        // Xóa code sau khi đã lấy
        await AsyncStorage.removeItem('pending_ref_code');
      }
      return code;
    } catch (error) {
      console.error('Error getting pending ref code:', error);
      return null;
    }
  }
}

export default DeepLinkHandler;
