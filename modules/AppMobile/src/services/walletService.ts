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

import api from '../utils/Api';
import { ApiResponse } from '../types/api/common';
import { WalletInfo, Transaction, Reward, RedeemResponse, TransactionFilterParams } from '../types/api/wallet';

export const walletService = {
    getWalletInfo: async (): Promise<ApiResponse<WalletInfo>> => {
        const response = await api.get<ApiResponse<WalletInfo>>('/wallet');
        return response.data;
    },

    getTransactions: async (params?: TransactionFilterParams): Promise<ApiResponse<Transaction[]>> => {
        const response = await api.get<ApiResponse<Transaction[]>>('/wallet/transactions', { params });
        return response.data;
    },

    getRewards: async (page: number = 1): Promise<ApiResponse<Reward[]>> => {
        const response = await api.get<ApiResponse<Reward[]>>('/wallet/rewards', {
            params: { page }
        });
        return response.data;
    },

    redeemReward: async (rewardId: number): Promise<ApiResponse<RedeemResponse>> => {
        const response = await api.post<ApiResponse<RedeemResponse>>('/wallet/redeem', {
            phan_thuong_id: rewardId
        });
        return response.data;
    }
};
