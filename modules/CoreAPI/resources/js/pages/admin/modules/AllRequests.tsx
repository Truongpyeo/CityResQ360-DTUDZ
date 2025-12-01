import React, { useState } from 'react';
import { router } from '@inertiajs/react';
import AdminLayout from '../layouts/AdminLayout';
import { CheckCircle, Clock, XCircle, Eye, Filter } from 'lucide-react';

interface User {
    id: number;
    ho_ten: string;
    email: string;
}

interface Module {
    id: number;
    module_key: string;
    module_name: string;
}

interface Request {
    id: number;
    app_domain: string;
    app_name: string;
    purpose: string;
    requested_max_storage_mb: number;
    requested_max_requests_per_day: number;
    status: 'pending' | 'approved' | 'rejected';
    created_at: string;
    reviewed_at: string | null;
    admin_notes: string | null;
    user: User;
    module: Module;
}

interface Props {
    requests: Request[];
    stats: {
        total: number;
        pending: number;
        approved: number;
        rejected: number;
    };
}

export default function AllRequests({ requests, stats }: Props) {
    const [selectedRequest, setSelectedRequest] = useState<Request | null>(null);
    const [adminNotes, setAdminNotes] = useState('');
    const [filterStatus, setFilterStatus] = useState<string>('all');

    const filteredRequests = filterStatus === 'all'
        ? requests
        : requests.filter(r => r.status === filterStatus);

    const handleApprove = (requestId: number) => {
        router.post(`/admin/modules/requests/${requestId}/approve`, {
            admin_notes: adminNotes
        }, {
            onSuccess: () => {
                setSelectedRequest(null);
                setAdminNotes('');
            }
        });
    };

    const handleReject = (requestId: number) => {
        const reason = prompt('Lý do từ chối:');
        if (reason) {
            router.post(`/admin/modules/requests/${requestId}/reject`, {
                reason: reason
            });
        }
    };

    const getStatusBadge = (status: string) => {
        switch (status) {
            case 'pending':
                return <span className="inline-flex items-center gap-1 px-3 py-1 bg-orange-100 text-orange-700 rounded-full text-xs font-medium">
                    <Clock size={14} /> Chờ Duyệt
                </span>;
            case 'approved':
                return <span className="inline-flex items-center gap-1 px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-medium">
                    <CheckCircle size={14} /> Đã Duyệt
                </span>;
            case 'rejected':
                return <span className="inline-flex items-center gap-1 px-3 py-1 bg-red-100 text-red-700 rounded-full text-xs font-medium">
                    <XCircle size={14} /> Từ Chối
                </span>;
        }
    };

    return (
        <AdminLayout>
            <div className="space-y-6">
                {/* Header */}
                <div>
                    <h1 className="text-3xl font-bold text-gray-900">Module Integration Requests</h1>
                    <p className="text-gray-600 mt-1">Quản lý tất cả requests đăng ký module từ clients</p>
                </div>

                {/* Stats Cards */}
                <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div className="bg-white rounded-lg shadow p-6">
                        <div className="text-sm text-gray-600 mb-1">Tổng Requests</div>
                        <div className="text-3xl font-bold text-gray-900">{stats.total}</div>
                    </div>
                    <div className="bg-orange-50 rounded-lg shadow p-6">
                        <div className="text-sm text-orange-600 mb-1">Chờ Duyệt</div>
                        <div className="text-3xl font-bold text-orange-600">{stats.pending}</div>
                    </div>
                    <div className="bg-green-50 rounded-lg shadow p-6">
                        <div className="text-sm text-green-600 mb-1">Đã Duyệt</div>
                        <div className="text-3xl font-bold text-green-600">{stats.approved}</div>
                    </div>
                    <div className="bg-red-50 rounded-lg shadow p-6">
                        <div className="text-sm text-red-600 mb-1">Từ Chối</div>
                        <div className="text-3xl font-bold text-red-600">{stats.rejected}</div>
                    </div>
                </div>

                {/* Filter */}
                <div className="flex items-center gap-3">
                    <Filter size={20} className="text-gray-400" />
                    <select
                        value={filterStatus}
                        onChange={(e) => setFilterStatus(e.target.value)}
                        className="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                        <option value="all">Tất Cả ({stats.total})</option>
                        <option value="pending">Chờ Duyệt ({stats.pending})</option>
                        <option value="approved">Đã Duyệt ({stats.approved})</option>
                        <option value="rejected">Từ Chối ({stats.rejected})</option>
                    </select>
                </div>

                {/* Requests Table */}
                <div className="bg-white rounded-lg shadow overflow-hidden">
                    <table className="min-w-full divide-y divide-gray-200">
                        <thead className="bg-gray-50">
                            <tr>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    User / Module
                                </th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    App Info
                                </th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Quotas
                                </th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Date
                                </th>
                                <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody className="bg-white divide-y divide-gray-200">
                            {filteredRequests.length === 0 ? (
                                <tr>
                                    <td colSpan={6} className="px-6 py-8 text-center text-gray-500">
                                        Không có requests nào
                                    </td>
                                </tr>
                            ) : (
                                filteredRequests.map((request) => (
                                    <tr key={request.id} className="hover:bg-gray-50">
                                        <td className="px-6 py-4">
                                            <div className="text-sm font-medium text-gray-900">{request.user.ho_ten}</div>
                                            <div className="text-sm text-gray-500">{request.user.email}</div>
                                            <div className="text-xs text-blue-600 mt-1">{request.module.module_name}</div>
                                        </td>
                                        <td className="px-6 py-4">
                                            <div className="text-sm text-gray-900">{request.app_name || 'N/A'}</div>
                                            <div className="text-sm text-gray-500">{request.app_domain}</div>
                                        </td>
                                        <td className="px-6 py-4 text-sm text-gray-500">
                                            <div>{request.requested_max_storage_mb} MB</div>
                                            <div>{request.requested_max_requests_per_day.toLocaleString()}/day</div>
                                        </td>
                                        <td className="px-6 py-4">
                                            {getStatusBadge(request.status)}
                                        </td>
                                        <td className="px-6 py-4 text-sm text-gray-500">
                                            {new Date(request.created_at).toLocaleDateString('vi-VN')}
                                        </td>
                                        <td className="px-6 py-4 text-right text-sm font-medium">
                                            {request.status === 'pending' ? (
                                                <div className="flex items-center justify-end gap-2">
                                                    <button
                                                        onClick={() => setSelectedRequest(request)}
                                                        className="inline-flex items-center gap-1.5 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors shadow-sm"
                                                    >
                                                        <CheckCircle size={16} />
                                                        <span>Duyệt</span>
                                                    </button>
                                                    <button
                                                        onClick={() => handleReject(request.id)}
                                                        className="inline-flex items-center gap-1.5 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors shadow-sm"
                                                    >
                                                        <XCircle size={16} />
                                                        <span>Từ Chối</span>
                                                    </button>
                                                </div>
                                            ) : (
                                                <span className="text-gray-400">—</span>
                                            )}
                                        </td>
                                    </tr>
                                ))
                            )}
                        </tbody>
                    </table>
                </div>

                {/* Approve Modal */}
                {selectedRequest && (
                    <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50" onClick={() => setSelectedRequest(null)}>
                        <div className="bg-white rounded-lg p-6 max-w-2xl w-full mx-4" onClick={(e) => e.stopPropagation()}>
                            <h3 className="text-xl font-semibold mb-4">Duyệt Request #{selectedRequest.id}</h3>

                            <div className="grid grid-cols-2 gap-4 mb-4 text-sm">
                                <div>
                                    <div className="text-gray-600">User</div>
                                    <div className="font-medium">{selectedRequest.user.ho_ten}</div>
                                    <div className="text-gray-500">{selectedRequest.user.email}</div>
                                </div>
                                <div>
                                    <div className="text-gray-600">Module</div>
                                    <div className="font-medium">{selectedRequest.module.module_name}</div>
                                </div>
                                <div>
                                    <div className="text-gray-600">App / Domain</div>
                                    <div className="font-medium">{selectedRequest.app_name || 'N/A'}</div>
                                    <div className="text-gray-500">{selectedRequest.app_domain}</div>
                                </div>
                                <div>
                                    <div className="text-gray-600">Quotas</div>
                                    <div className="font-medium">{selectedRequest.requested_max_storage_mb} MB</div>
                                    <div className="text-gray-500">{selectedRequest.requested_max_requests_per_day.toLocaleString()} req/day</div>
                                </div>
                            </div>

                            <div className="bg-gray-50 p-4 rounded mb-4">
                                <div className="text-sm font-medium text-gray-700 mb-1">Mục đích sử dụng:</div>
                                <div className="text-sm text-gray-900">{selectedRequest.purpose}</div>
                            </div>

                            <div className="mb-4">
                                <label className="block text-sm font-medium text-gray-700 mb-2">
                                    Admin Notes (Optional)
                                </label>
                                <textarea
                                    value={adminNotes}
                                    onChange={(e) => setAdminNotes(e.target.value)}
                                    className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    rows={3}
                                    placeholder="Ghi chú khi duyệt request..."
                                />
                            </div>

                            <div className="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4 text-sm">
                                <div className="font-medium text-blue-900 mb-1">📧 Sau khi duyệt:</div>
                                <ul className="text-blue-800 space-y-1">
                                    <li>• Credentials sẽ được tự động tạo</li>
                                    <li>• Email gửi tới: {selectedRequest.user.email}</li>
                                    <li>• Client ID + JWT Secret</li>
                                </ul>
                            </div>

                            <div className="flex gap-3">
                                <button
                                    onClick={() => handleApprove(selectedRequest.id)}
                                    className="flex-1 px-4 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium"
                                >
                                    ✓ Xác Nhận Duyệt
                                </button>
                                <button
                                    onClick={() => {
                                        setSelectedRequest(null);
                                        setAdminNotes('');
                                    }}
                                    className="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300"
                                >
                                    Hủy
                                </button>
                            </div>
                        </div>
                    </div>
                )}
            </div>
        </AdminLayout>
    );
}
