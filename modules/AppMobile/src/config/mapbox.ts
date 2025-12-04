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

/**
 * Mapbox Configuration
 * Initialize Mapbox GL for React Native
 */

import Mapbox from '@rnmapbox/maps';
import env from './env';

// Set Mapbox access token
Mapbox.setAccessToken(env.MAPBOX_ACCESS_TOKEN);

// Disable telemetry (optional - để tránh gửi analytics)
Mapbox.setTelemetryEnabled(false);

// Set WellKnownTileServer (optional - để sử dụng Mapbox tiles)
Mapbox.setWellKnownTileServer('Mapbox');

// Configure camera settings (optional)
export const DEFAULT_CAMERA_CONFIG = {
  zoomLevel: 12,
  pitch: 0,
  heading: 0,
  animationMode: 'flyTo' as const,
  animationDuration: 1000,
};

// Da Nang default coordinates
export const DA_NANG_CENTER = {
  longitude: 108.245350,
  latitude: 16.068882,
};

export default Mapbox;

