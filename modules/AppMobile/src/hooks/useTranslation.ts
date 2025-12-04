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

import { useTranslation as useI18nTranslation } from 'react-i18next';
import i18n from '../i18n';

export const useTranslation = () => {
  const { t, i18n: i18nInstance } = useI18nTranslation();

  const changeLanguage = async (languageCode: string) => {
    try {
      await i18nInstance.changeLanguage(languageCode);
    } catch (error) {
      console.log('Error changing language:', error);
    }
  };

  const getCurrentLanguage = () => {
    return i18nInstance.language;
  };

  const isRTL = () => {
    return i18nInstance.dir() === 'rtl';
  };

  return {
    t,
    changeLanguage,
    getCurrentLanguage,
    isRTL,
    currentLanguage: getCurrentLanguage(),
  };
};

export default useTranslation;
