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

import { NativeStackScreenProps } from '@react-navigation/native-stack';
import { NavigatorScreenParams } from '@react-navigation/native';

export type MainTabParamList = {
  Home: undefined;
  Map: undefined;
  Wallet: undefined;
  Profile: undefined;
  CreateReport: undefined;
};

export type RootStackParamList = {
  Loading: undefined;
  Onboarding: undefined;
  Login: undefined;
  Register: undefined;
  Reports: undefined;
  ForgotPassword: undefined;
  OTPVerification: {
    identifier: string;
    type: 'phone' | 'email';
    flow?: 'register' | 'login' | 'forgot';
  };
  MainTabs: NavigatorScreenParams<MainTabParamList>;
  ChangePassword: undefined;
  UpdatePassword: {
    token: string;
  };
  EmailVerification: undefined;
  PhoneVerification: undefined;
  // Reports Module
  ReportList: undefined;
  ReportDetail: {
    id: number;
    reportId?: number; // Alternative parameter name
  };
  CreateReport: undefined;
  EditReport: {
    id: number;
  };
  MyReports: undefined;
  NearbyReports: undefined;
  TrendingReports: undefined;
  // Comments Module
  ReportComments: {
    reportId: number;
  };
  // Map Module
  MapReports: undefined;
  MapHeatmap: undefined;
  MapClusters: undefined;
  MapRoutes: undefined;
  // Wallet Module
  WalletDetail: undefined;
  WalletTransactions: undefined;
  WalletRewards: undefined;
  RedeemReward: {
    rewardId: number;
  };
  // Notifications Module
  Notifications: undefined;
  NotificationSettings: undefined;
  // Dashboard/Stats Module
  Dashboard: undefined;
  StatsOverview: undefined;
  StatsCategories: undefined;
  StatsTimeline: undefined;
  Leaderboard: undefined;
  CityStats: undefined;
  // Agencies Module
  AgencyList: undefined;
  AgencyDetail: {
    id: number;
  };
  AgencyReports: {
    agencyId: number;
  };
  AgencyStats: {
    agencyId: number;
  };
  // User Profile Module
  UserProfile: {
    userId: number;
  };
  UserReports: {
    userId: number;
  };
  UserStats: {
    userId: number;
  };
  ChangePasswordLoggedIn: undefined;
  // Settings Module
  LanguageSettings: undefined;
  HelpCenter: undefined;
  About: undefined;
};

export type StackScreen<T extends keyof RootStackParamList> = React.FC<NativeStackScreenProps<RootStackParamList, T>>;

declare global {
  namespace ReactNavigation {
    interface RootParamList extends RootStackParamList { }
  }
}
