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

import Swal from 'sweetalert2';

/**
 * Show success notification
 */
export const showSuccess = (message: string, title: string = 'Thành công!') => {
    return Swal.fire({
        icon: 'success',
        title: title,
        text: message,
        confirmButtonText: 'OK',
        confirmButtonColor: '#2563eb',
        timer: 3000,
        timerProgressBar: true,
    });
};

/**
 * Show error notification
 */
export const showError = (message: string, title: string = 'Lỗi!') => {
    return Swal.fire({
        icon: 'error',
        title: title,
        text: message,
        confirmButtonText: 'OK',
        confirmButtonColor: '#dc2626',
    });
};

/**
 * Show warning notification
 */
export const showWarning = (message: string, title: string = 'Cảnh báo!') => {
    return Swal.fire({
        icon: 'warning',
        title: title,
        text: message,
        confirmButtonText: 'OK',
        confirmButtonColor: '#ea580c',
    });
};

/**
 * Show info notification
 */
export const showInfo = (message: string, title: string = 'Thông tin') => {
    return Swal.fire({
        icon: 'info',
        title: title,
        text: message,
        confirmButtonText: 'OK',
        confirmButtonColor: '#0891b2',
    });
};

/**
 * Show confirmation dialog
 */
export const showConfirm = async (
    message: string,
    title: string = 'Bạn có chắc chắn?',
    confirmButtonText: string = 'Xác nhận',
    cancelButtonText: string = 'Hủy'
): Promise<boolean> => {
    const result = await Swal.fire({
        icon: 'question',
        title: title,
        text: message,
        showCancelButton: true,
        confirmButtonText: confirmButtonText,
        cancelButtonText: cancelButtonText,
        confirmButtonColor: '#2563eb',
        cancelButtonColor: '#6b7280',
        reverseButtons: true,
    });

    return result.isConfirmed;
};

/**
 * Show delete confirmation
 */
export const showDeleteConfirm = async (
    itemName: string = 'mục này'
): Promise<boolean> => {
    const result = await Swal.fire({
        icon: 'warning',
        title: 'Xác nhận xóa',
        html: `Bạn có chắc chắn muốn xóa <strong>${itemName}</strong>?<br><span class="text-sm text-gray-500">Hành động này không thể hoàn tác!</span>`,
        showCancelButton: true,
        confirmButtonText: 'Xóa',
        cancelButtonText: 'Hủy',
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        reverseButtons: true,
    });

    return result.isConfirmed;
};

/**
 * Show status update confirmation
 */
export const showStatusConfirm = async (
    currentStatus: string,
    newStatus: string,
    itemName: string = 'mục này'
): Promise<boolean> => {
    const result = await Swal.fire({
        icon: 'question',
        title: 'Xác nhận thay đổi trạng thái',
        html: `Bạn có muốn thay đổi trạng thái của <strong>${itemName}</strong><br>từ <span class="text-orange-600 font-semibold">${currentStatus}</span> sang <span class="text-blue-600 font-semibold">${newStatus}</span>?`,
        showCancelButton: true,
        confirmButtonText: 'Xác nhận',
        cancelButtonText: 'Hủy',
        confirmButtonColor: '#2563eb',
        cancelButtonColor: '#6b7280',
        reverseButtons: true,
    });

    return result.isConfirmed;
};

/**
 * Show loading spinner
 */
export const showLoading = (message: string = 'Đang xử lý...') => {
    Swal.fire({
        title: message,
        allowOutsideClick: false,
        allowEscapeKey: false,
        allowEnterKey: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        },
    });
};

/**
 * Close loading spinner
 */
export const closeLoading = () => {
    Swal.close();
};

/**
 * Show toast notification (non-blocking)
 */
export const showToast = (message: string, icon: 'success' | 'error' | 'warning' | 'info' = 'success') => {
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer);
            toast.addEventListener('mouseleave', Swal.resumeTimer);
        },
    });

    return Toast.fire({
        icon: icon,
        title: message,
    });
};

/**
 * Show input dialog
 */
export const showInput = async (
    title: string,
    placeholder: string,
    inputType: 'text' | 'number' | 'textarea' = 'text',
    defaultValue: string = ''
): Promise<string | null> => {
    const result = await Swal.fire({
        title: title,
        input: inputType,
        inputValue: defaultValue,
        inputPlaceholder: placeholder,
        showCancelButton: true,
        confirmButtonText: 'Xác nhận',
        cancelButtonText: 'Hủy',
        confirmButtonColor: '#2563eb',
        cancelButtonColor: '#6b7280',
        inputValidator: (value) => {
            if (!value) {
                return 'Vui lòng nhập thông tin!';
            }
            return null;
        },
    });

    return result.isConfirmed ? result.value : null;
};

/**
 * Show verification confirmation (for user verification)
 */
export const showVerifyConfirm = async (userName: string): Promise<boolean> => {
    const result = await Swal.fire({
        icon: 'info',
        title: 'Xác thực công dân',
        html: `Bạn có muốn xác thực công dân cho <strong>${userName}</strong>?<br><span class="text-sm text-gray-500">Sau khi xác thực, người dùng sẽ có thêm các quyền lợi đặc biệt.</span>`,
        showCancelButton: true,
        confirmButtonText: 'Xác thực',
        cancelButtonText: 'Hủy',
        confirmButtonColor: '#2563eb',
        cancelButtonColor: '#6b7280',
        reverseButtons: true,
    });

    return result.isConfirmed;
};

/**
 * Show lock/unlock account confirmation
 */
export const showLockUnlockConfirm = async (
    isCurrentlyActive: boolean,
    userName: string
): Promise<boolean> => {
    const action = isCurrentlyActive ? 'khóa' : 'mở khóa';
    const color = isCurrentlyActive ? '#dc2626' : '#16a34a';

    const result = await Swal.fire({
        icon: 'warning',
        title: `Xác nhận ${action} tài khoản`,
        html: `Bạn có chắc chắn muốn <strong>${action}</strong> tài khoản của <strong>${userName}</strong>?`,
        showCancelButton: true,
        confirmButtonText: action.charAt(0).toUpperCase() + action.slice(1),
        cancelButtonText: 'Hủy',
        confirmButtonColor: color,
        cancelButtonColor: '#6b7280',
        reverseButtons: true,
    });

    return result.isConfirmed;
};

/**
 * Show points dialog (for adding/removing points)
 */
export const showPointsDialog = async (): Promise<{ points: number; reason: string } | null> => {
    const { value: formValues } = await Swal.fire({
        title: 'Thêm CityPoints',
        html: `
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2 text-left">Số điểm</label>
                    <input id="points" type="number" min="1" max="1000" placeholder="Nhập số điểm (1-1000)"
                        class="swal2-input w-full" style="width: 100%; margin: 0;">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2 text-left">Lý do</label>
                    <textarea id="reason" placeholder="Nhập lý do thêm điểm..." rows="3" maxlength="200"
                        class="swal2-textarea w-full" style="width: 100%; margin: 0;"></textarea>
                    <p class="text-xs text-gray-500 mt-1 text-right"><span id="char-count">0</span>/200 ký tự</p>
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Thêm điểm',
        cancelButtonText: 'Hủy',
        confirmButtonColor: '#2563eb',
        cancelButtonColor: '#6b7280',
        focusConfirm: false,
        didOpen: () => {
            const reasonInput = document.getElementById('reason') as HTMLTextAreaElement;
            const charCount = document.getElementById('char-count');

            reasonInput?.addEventListener('input', () => {
                if (charCount) {
                    charCount.textContent = reasonInput.value.length.toString();
                }
            });
        },
        preConfirm: () => {
            const points = (document.getElementById('points') as HTMLInputElement).value;
            const reason = (document.getElementById('reason') as HTMLTextAreaElement).value;

            if (!points || !reason) {
                Swal.showValidationMessage('Vui lòng nhập đầy đủ thông tin!');
                return null;
            }

            const pointsNum = parseInt(points);
            if (pointsNum < 1 || pointsNum > 1000) {
                Swal.showValidationMessage('Số điểm phải từ 1 đến 1000!');
                return null;
            }

            return { points: pointsNum, reason };
        },
    });

    return formValues || null;
};
