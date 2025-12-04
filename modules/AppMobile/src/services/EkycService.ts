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

// services/EkycService.ts
import { NativeModules } from 'react-native';
import { LogResult } from '../types/EkycTypes';

const { EkycBridge } = NativeModules;

export class EkycService {
  static async startEkycFull(): Promise<LogResult> {
    console.log('EkycBridge', NativeModules);
    try {
      const result = await EkycBridge.startEkycFull();
      return JSON.parse(result);
    } catch (error) {
      throw new Error(`eKYC Full failed: ${error}`);
    }
  }

  static async startEkycOcr(): Promise<LogResult> {
    try {
      const result = await EkycBridge.startEkycOcr();
      return JSON.parse(result);
    } catch (error) {
      throw new Error(`eKYC OCR failed: ${error}`);
    }
  }

  static async startEkycFace(): Promise<LogResult> {
    try {
      const result = await EkycBridge.startEkycFace();
      return JSON.parse(result);
    } catch (error) {
      throw new Error(`eKYC Face failed: ${error}`);
    }
  }

  // Verify face against a reference image hash stored after eKYC
  static async verifyFace(referenceHash: string): Promise<LogResult> {
    try {
      const result = await EkycBridge.startEkycFaceWithReference(referenceHash);
      return JSON.parse(result);
    } catch (error) {
      throw new Error(`Verify Face failed: ${error}`);
    }
  }

  // VerifyFace flow: input ID then capture and verify
  static async verifyFaceById(verifyId: string): Promise<LogResult> {
    try {
      const result = await EkycBridge.startVerifyFaceWithId(verifyId);
      return JSON.parse(result);
    } catch (error) {
      throw new Error(`Verify Face by ID failed: ${error}`);
    }
  }

  // Language-aware variants (language: 'vi' | 'en')
  static async startEkycFullWithLanguage(language: 'vi' | 'en'): Promise<LogResult> {
    try {
      const result = await EkycBridge.startEkycFullWithLanguage(language);
      return JSON.parse(result);
    } catch (error) {
      throw new Error(`eKYC Full (lang) failed: ${error}`);
    }
  }

  static async startEkycOcrWithLanguage(language: 'vi' | 'en'): Promise<LogResult> {
    try {
      const result = await EkycBridge.startEkycOcrWithLanguage(language);
      return JSON.parse(result);
    } catch (error) {
      throw new Error(`eKYC OCR (lang) failed: ${error}`);
    }
  }

  static async startEkycFaceWithLanguage(language: 'vi' | 'en'): Promise<LogResult> {
    try {
      const result = await EkycBridge.startEkycFaceWithLanguage(language);
      return JSON.parse(result);
    } catch (error) {
      throw new Error(`eKYC Face (lang) failed: ${error}`);
    }
  }
}