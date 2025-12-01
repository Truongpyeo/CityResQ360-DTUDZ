import React from 'react';
import { Link, router } from '@inertiajs/react';
import AdminLayout from '../layouts/AdminLayout';
import { CheckCircle, Clock, XCircle, Eye, Mail } from 'lucide-react';

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
    status: 'pending' | 'approved' | 'rejected';
    created_at: string;
    reviewed_at: string | null;
    admin_notes: string | null;
    user: User;
}

interface Props {
    module: Module;
    requests: Request[];
}

export default function Requests({ module, requests }: Props) {
    const [selectedRequest, setSelectedRequest] = React.useState<Request | null>(null);
    const [adminNotes, setAdminNotes] = React.useState('');
    const [rejectReason, setRejectReason] = React.useState('');

    const handleApprove = (requestId: number) => {
        if (confirm('Duyệt request này? Credentials sẽ được gửi qua email.')) {
            router.post(`/admin/modules/requests/${requestId}/approve`, {
                admin_notes: adminNotes
            });
        }
    };

    const handleReject = (requestId: number) => {
        if (rejectReason && confirm('Từ chối request này?')) {
            router.post(`/admin/modules/requests/${requestId}/reject`, {
                reason: rejectReason
            });
        }
    };

    const getStatusBadge = (status: string) => {
        switch (status) {
            case 'pending':
                return <span className="inline-flex items-center gap-1 px-2 py-1 bg-orange-100 text-orange-700 rounded-full text-xs">
                    <Clock size={12} /> Pending
                </span>;
            case 'approved':
                return <span className="inline-flex items-center gap-1 px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs">
                    <CheckCircle size={12} /> Approved
                </span>;
            case 'rejected':
                return <span className="inline-flex items-center gap-1 px-2 py-1 bg-red-100 text-red-700 rounded-full text-xs">
                    <XCircle size={12} /> Rejected
                </span>;
        }
    };

    return (
        <AdminLayout title={`${module.module_name} - Requests`}>
            <div className="space-y-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold text-gray-900">{module.module_name} - Requests</h1>
                        <p className="text-sm text-gray-600 mt-1">Quản lý requests đăng ký module</p>
                    </div>
                    <Link href="/admin/modules/dashboard" className="text-blue-600 hover:text-blue-700">
                        ← Back to Dashboard
                    </Link>
                </div>

                {/* Requests List */}
                <div className="bg-white rounded-lg shadow">
                    <div className="divide-y">
                        {requests.length === 0 ? (
                            <div className="p-8 text-center text-gray-500">
                                Chưa có requests nào
                            </div>
                        ) : (
                            requests.map((request) => (
                                <div key={request.id} className="p-6">
                                    <div className="flex items-start justify-between">
                                        <div className="flex-1">
                                            <div className="flex items-center gap-3 mb-2">
                                                <h3 className="font-semibold text-gray-900">Request #{request.id}</h3>
                                                {getStatusBadge(request.status)}
                                            </div>

                                            <div className="text-sm space-y-1 mb-3">
                                                <div><span className="text-gray-600">User:</span> {request.user.ho_ten} ({request.user.email})</div>
                                                <div><span className="text-gray-600">App:</span> {request.app_name || 'N/A'} ({request.app_domain})</div>
                                                <div className="text-gray-600">Đăng ký: {new Date(request.created_at).toLocaleString('vi-VN')}</div>
                                            </div>

                                            <div className="bg-gray-50 p-3 rounded text-sm">
                                                <div className="font-medium text-gray-700 mb-1">Mục đích:</div>
                                                <div className="text-gray-900">{request.purpose}</div>
                                            </div>

                                            {request.admin_notes && (
                                                <div className="mt-3 bg-blue-50 p-3 rounded text-sm">
                                                    <div className="font-medium text-blue-700 mb-1">Admin Notes:</div>
                                                    <div className="text-gray-900">{request.admin_notes}</div>
                                                </div>
                                            )}
                                        </div>

                                        {request.status === 'pending' && (
                                            <div className="ml-4 space-y-2">
                                                <button
                                                    onClick={() => setSelectedRequest(request)}
                                                    className="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 flex items-center gap-2 text-sm"
                                                >
                                                    <CheckCircle size={16} />
                                                    Duyệt
                                                </button>
                                                <button
                                                    onClick={() => {
                                                        const reason = prompt('Lý do từ chối:');
                                                        if (reason) {
                                                            setRejectReason(reason);
                                                            handleReject(request.id);
                                                        }
                                                    }}
                                                    className="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 flex items-center gap-2 text-sm w-full"
                                                >
                                                    <XCircle size={16} />
                                                    Từ Chối
                                                </button>
                                            </div>
                                        )}
                                    </div>
                                </div>
                            ))
                        )}
                    </div>
                </div>

                {/* Approve Modal */}
                {selectedRequest && (
                    <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
                        <div className="bg-white rounded-lg p-6 max-w-lg w-full">
                            <h3 className="text-lg font-semibold mb-4">Duyệt Request</h3>

                            <div className="space-y-3 mb-4 text-sm">
                                <div><strong>User:</strong> {selectedRequest.user.ho_ten}</div>
                                <div><strong>Email:</strong> {selectedRequest.user.email}</div>
                                <div><strong>App:</strong> {selectedRequest.app_domain}</div>
                            </div>

                            <div className="mb-4">
                                <label className="block text-sm font-medium text-gray-700 mb-2">
                                    Admin Notes (Optional)
                                </label>
                                <textarea
                                    value={adminNotes}
                                    onChange={(e) => setAdminNotes(e.target.value)}
                                    className="w-full border rounded px-3 py-2 text-sm"
                                    rows={3}
                                    placeholder="Ghi chú khi duyệt..."
                                />
                            </div>

                            <div className="bg-yellow-50 border border-yellow-200 rounded p-3 mb-4 text-sm">
                                <strong>⚠️ Lưu ý:</strong> Sau khi duyệt, credentials sẽ được tự động gửi qua email {selectedRequest.user.email}
                            </div>

                            <div className="flex gap-2">
                                <button
                                    onClick={() => {
                                        handleApprove(selectedRequest.id);
                                        setSelectedRequest(null);
                                        setAdminNotes('');
                                    }}
                                    className="flex-1 px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700"
                                >
                                    Xác Nhận Duyệt
                                </button>
                                <button
                                    onClick={() => {
                                        setSelectedRequest(null);
                                        setAdminNotes('');
                                    }}
                                    className="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300"
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
