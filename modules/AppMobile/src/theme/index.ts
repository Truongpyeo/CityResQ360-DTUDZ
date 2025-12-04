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
 * Theme System - WISE English LMS
 * 
 * Centralized theme exports for cross-platform consistency
 * Usage:
 * import { theme, COLORS, textStyles, cardStyles, wp, hp } from '../../theme';
 */

export { theme, COLORS } from './colors';
export { typography, textStyles as typographyStyles, createTextStyle } from './typography';
export { 
  responsive,
  FONT_SIZE,
  SPACING,
  BORDER_RADIUS,
  ICON_SIZE,
  BUTTON_HEIGHT,
  INPUT_HEIGHT,
  AVATAR_SIZE,
  CARD,
  TAB_BAR,
  BOTTOM_SHEET,
  SCREEN_PADDING,
  LIST_ITEM,
  normalize,
  BREAKPOINTS,
} from './responsive';
export {
  containerStyles,
  cardStyles,
  buttonStyles,
  textStyles,
  inputStyles,
  badgeStyles,
  iconContainerStyles,
  listItemStyles,
  avatarStyles,
  dividerStyle,
} from './components';

// Re-export wp & hp for convenience
export { widthPercentageToDP as wp, heightPercentageToDP as hp } from 'react-native-responsive-screen';

