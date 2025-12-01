import React, { useState } from 'react';
import { Link, router } from '@inertiajs/react';
import { Eye, EyeOff, Copy, CheckCheck, RefreshCw } from 'lucide-react';

interface Module {
    module_name: string;
    base_url: string;
    docs_url: string;
}

interface Credential {
    id: number;
    client_id: string;
    jwt_secret: string;
    max_storage_mb: number;
    current_storage_mb: number;
    total_requests: number;
    created_at: string;
    module: Module;
}

interface Props {
    credentials: Credential[];
}

export default function ApiKeys({ credentials }: Props) {
    const [visibleSecrets, setVisibleSecrets] = useState<Record<number, boolean>>({});
    const [copied, setCopied] = useState<string | null>(null);

    const toggleSecretVisibility = (id: number) => {
        setVisibleSecrets(prev => ({ ...prev, [id]: !prev[id] }));
    };

    const copyToClipboard = (text: string, label: string) => {
        navigator.clipboard.writeText(text);
        setCopied(label);
        setTimeout(() => setCopied(null), 2000);
    };

    return (
        <div className="min-h-screen bg-gray-50">
            {/* Header */}
            <header className="bg-white shadow">
                <div className="max-w-7xl mx-auto px-4 py-6">
                    <div className="flex items-center justify-between">
                        <div>
                            <h1 className="text-3xl font-bold text-gray-900">API Keys</h1>
                            <p className="text-gray-600 mt-1">Quản lý credentials cho các modules</p>
                        </div>
                        <Link href="/client/modules" className="text-blue-600 hover:text-blue-700">
                            ← Back to Modules
                        </Link>
                    </div>
                </div>
            </header>

            <main className="max-w-7xl mx-auto px-4 py-8">
                {credentials.length === 0 ? (
                    <div className="bg-white rounded-lg shadow p-12 text-center">
                        <p className="text-gray-500 mb-4">Bạn chưa có API keys nào</p>
                        <Link href="/client/modules" className="text-blue-600 hover:text-blue-700">
                            Đăng ký module →
                        </Link>
                    </div>
                ) : (
                    <div className="space-y-6">
                        {credentials.map((cred) => (
                            <div key={cred.id} className="bg-white rounded-lg shadow p-6">
                                <div className="mb-4">
                                    <h2 className="text-xl font-semibold text-gray-900">{cred.module.module_name}</h2>
                                    <p className="text-sm text-gray-500">
                                        Activated: {new Date(cred.created_at).toLocaleDateString('vi-VN')}
                                    </p>
                                </div>

                                {/* Credentials */}
                                <div className="space-y-4">
                                    {/* Client ID */}
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700 mb-1">Client ID</label>
                                        <div className="flex items-center gap-2">
                                            <input
                                                type="text"
                                                value={cred.client_id}
                                                readOnly
                                                className="flex-1 px-3 py-2 border rounded bg-gray-50 font-mono text-sm"
                                            />
                                            <button
                                                onClick={() => copyToClipboard(cred.client_id, `client_id_${cred.id}`)}
                                                className="px-3 py-2 bg-gray-200 rounded hover:bg-gray-300"
                                            >
                                                {copied === `client_id_${cred.id}` ? <CheckCheck size={16} /> : <Copy size={16} />}
                                            </button>
                                        </div>
                                    </div>

                                    {/* JWT Secret */}
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700 mb-1">JWT Secret</label>
                                        <div className="flex items-center gap-2">
                                            <input
                                                type={visibleSecrets[cred.id] ? 'text' : 'password'}
                                                value={cred.jwt_secret}
                                                readOnly
                                                className="flex-1 px-3 py-2 border rounded bg-gray-50 font-mono text-sm"
                                            />
                                            <button
                                                onClick={() => toggleSecretVisibility(cred.id)}
                                                className="px-3 py-2 bg-gray-200 rounded hover:bg-gray-300"
                                            >
                                                {visibleSecrets[cred.id] ? <EyeOff size={16} /> : <Eye size={16} />}
                                            </button>
                                            <button
                                                onClick={() => copyToClipboard(cred.jwt_secret, `secret_${cred.id}`)}
                                                className="px-3 py-2 bg-gray-200 rounded hover:bg-gray-300"
                                                title="Copy Secret"
                                            >
                                                {copied === `secret_${cred.id}` ? <CheckCheck size={16} /> : <Copy size={16} />}
                                            </button>
                                            <button
                                                onClick={() => {
                                                    if (confirm('CẢNH BÁO: Việc làm mới Secret sẽ khiến các kết nối hiện tại bị ngắt ngay lập tức. Bạn có chắc chắn muốn tiếp tục?')) {
                                                        router.post(`/client/credentials/${cred.id}/refresh-secret`);
                                                    }
                                                }}
                                                className="px-3 py-2 bg-red-100 text-red-600 rounded hover:bg-red-200 flex items-center gap-1"
                                                title="Làm mới Secret"
                                            >
                                                <RefreshCw size={16} />
                                                <span className="text-xs font-medium">Refresh</span>
                                            </button>
                                        </div>
                                    </div>

                                    {/* Base URL */}
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700 mb-1">Base URL</label>
                                        <div className="flex items-center gap-2">
                                            <input
                                                type="text"
                                                value={cred.module.base_url}
                                                readOnly
                                                className="flex-1 px-3 py-2 border rounded bg-gray-50 font-mono text-sm"
                                            />
                                            <button
                                                onClick={() => copyToClipboard(cred.module.base_url, `url_${cred.id}`)}
                                                className="px-3 py-2 bg-gray-200 rounded hover:bg-gray-300"
                                            >
                                                {copied === `url_${cred.id}` ? <CheckCheck size={16} /> : <Copy size={16} />}
                                            </button>
                                        </div>
                                    </div>

                                    {/* Quick Start */}
                                    <div className="bg-gray-50 p-4 rounded">
                                        <div className="flex items-center justify-between mb-2">
                                            <span className="text-sm font-medium text-gray-700">Quick Start (.env)</span>
                                            <div className="flex items-center gap-2">
                                                <button
                                                    onClick={() => toggleSecretVisibility(cred.id)}
                                                    className="text-gray-500 hover:text-gray-700 text-sm flex items-center gap-1"
                                                >
                                                    {visibleSecrets[cred.id] ? <EyeOff size={14} /> : <Eye size={14} />}
                                                    {visibleSecrets[cred.id] ? 'Hide' : 'Show'}
                                                </button>
                                                <button
                                                    onClick={() => copyToClipboard(
                                                        `MEDIASERVICE_CLIENT_ID=${cred.client_id}\nMEDIASERVICE_JWT_SECRET=${cred.jwt_secret}\nMEDIASERVICE_URL=${cred.module.base_url}`,
                                                        `env_${cred.id}`
                                                    )}
                                                    className="text-blue-600 hover:text-blue-700 text-sm flex items-center gap-1"
                                                >
                                                    {copied === `env_${cred.id}` ? <CheckCheck size={14} /> : <Copy size={14} />}
                                                    {copied === `env_${cred.id}` ? 'Copied!' : 'Copy All'}
                                                </button>
                                            </div>
                                        </div>
                                        <pre className="text-xs font-mono text-gray-800 overflow-x-auto whitespace-pre-wrap break-all">
                                            {`MEDIASERVICE_CLIENT_ID=${cred.client_id}
MEDIASERVICE_JWT_SECRET=${visibleSecrets[cred.id] ? cred.jwt_secret : '****************************************'}
MEDIASERVICE_URL=${cred.module.base_url}`}
                                        </pre>
                                    </div>

                                    {/* Stats */}
                                    <div className="grid grid-cols-2 gap-4 pt-4 border-t">
                                        <div>
                                            <div className="text-sm text-gray-600">Storage Used</div>
                                            <div className="text-lg font-semibold">{cred.current_storage_mb}/{cred.max_storage_mb} MB</div>
                                        </div>
                                        <div>
                                            <div className="text-sm text-gray-600">Total Requests</div>
                                            <div className="text-lg font-semibold">{cred.total_requests.toLocaleString()}</div>
                                        </div>
                                    </div>

                                    {/* Documentation Link */}
                                    <div className="pt-4 border-t">
                                        <a
                                            href={cred.module.docs_url}
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            className="text-blue-600 hover:text-blue-700 text-sm"
                                        >
                                            📚 Xem tài liệu đầy đủ →
                                        </a>
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                )}
            </main>
        </div>
    );
}
