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

import { createNavigationContainerRef, StackActions } from '@react-navigation/native';
import type { RootStackParamList } from './types';

// Global navigation ref to allow navigation outside React components (e.g., in interceptors)
export const navigationRef = createNavigationContainerRef<RootStackParamList>();

export function isReady() {
  return navigationRef.isReady();
}

export function navigate<Name extends keyof RootStackParamList>(
  name: Name,
  params?: RootStackParamList[Name]
) {
  if (navigationRef.isReady()) {
    // @ts-expect-error params are validated by RootStackParamList
    navigationRef.navigate(name as never, params as never);
  }
}

export function resetTo<Name extends keyof RootStackParamList>(
  name: Name,
  params?: RootStackParamList[Name]
) {
  if (navigationRef.isReady()) {
    navigationRef.dispatch(
      StackActions.replace(name as string, params as object | undefined)
    );
  }
}

export function goBack() {
  if (navigationRef.isReady() && navigationRef.canGoBack()) {
    navigationRef.goBack();
  }
}


