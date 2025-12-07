import React, { useState } from 'react';
import { Head } from '@inertiajs/react';
import {
    Code,
    Copy,
    Check,
    ChevronDown,
    ChevronUp,
} from 'lucide-react';
import DocsLayout from './layouts/DocsLayout';

// Interfaces
interface Service {
    id: string;
    name: string;
    description: string;
    icon: string;
    status: string;
    version: string;
    baseUrls: {
        direct: string;
        viaCoreAPI: string;
    };
    integrationMethods: IntegrationMethod[];
    endpoints: Endpoint[];
    codeExamples: Record<string, Record<string, string>>;
}

interface IntegrationMethod {
    id: string;
    name: string;
    recommended: boolean;
    description: string;
    benefits: string[];
    url: string;
}

interface Endpoint {
    method: string;
    path: string;
    description: string;
    auth: string;
    requestBody?: RequestParam[];
    pathParams?: RequestParam[];
    response: any;
}

interface RequestParam {
    name: string;
    type: string;
    required: boolean;
    description: string;
}

interface ServiceProps {
    service: Service;
    services: Service[];
}

export default function Service({ service, services }: ServiceProps) {
    return (
        <DocsLayout services={services}>
            <Head title={`${service.name} - API Documentation`} />

            <div className="space-y-8">
                {/* Header */}
                <div>
                    <div className="flex items-center space-x-2">
                        <h1 className="text-4xl font-bold text-gray-900">{service.name}</h1>
                        <span className="rounded-full bg-green-100 px-3 py-1 text-sm font-medium text-green-700">
                            {service.version}
                        </span>
                    </div>
                    <p className="mt-4 text-lg text-gray-600">{service.description}</p>
                </div>

                {/* Base URLs */}
                <div className="rounded-lg border bg-blue-50 p-6">
                    <h3 className="text-sm font-semibold uppercase text-blue-900">Base URLs</h3>
                    <div className="mt-3 space-y-2">
                        <div>
                            <span className="text-sm font-medium text-blue-900">Trực tiếp:</span>
                            <code className="ml-2 rounded bg-blue-100 px-2 py-1 text-sm text-blue-800">
                                {service.baseUrls.direct}
                            </code>
                        </div>
                        <div>
                            <span className="text-sm font-medium text-blue-900">Qua CoreAPI:</span>
                            <code className="ml-2 rounded bg-blue-100 px-2 py-1 text-sm text-blue-800">
                                {service.baseUrls.viaCoreAPI}
                            </code>
                        </div>
                    </div>
                </div>

                {/* Integration Methods */}
                <div>
                    <h2 className="text-2xl font-bold text-gray-900">Phương Thức Tích Hợp</h2>
                    <div className="mt-4 grid gap-4 md:grid-cols-2">
                        {service.integrationMethods.map((method) => (
                            <div
                                key={method.id}
                                className={`rounded-lg border p-6 ${method.recommended
                                        ? 'border-blue-300 bg-blue-50'
                                        : 'bg-white'
                                    }`}
                            >
                                <div className="flex items-center gap-2">
                                    <h3 className="text-lg font-semibold text-gray-900">
                                        {method.name}
                                    </h3>
                                    {method.recommended && (
                                        <span className="rounded-full bg-blue-600 px-3 py-1 text-xs font-medium text-white whitespace-nowrap">
                                            Khuyến Nghị
                                        </span>
                                    )}
                                </div>
                                <p className="mt-2 text-sm text-gray-600">{method.description}</p>
                                <ul className="mt-4 space-y-2">
                                    {method.benefits.map((benefit, idx) => (
                                        <li key={idx} className="flex items-start text-sm text-gray-700">
                                            <Check className="mr-2 mt-0.5 h-4 w-4 shrink-0 text-green-600" />
                                            <span>{benefit}</span>
                                        </li>
                                    ))}
                                </ul>
                                <div className="mt-4">
                                    <code className="block rounded bg-gray-100 p-2 text-sm">
                                        {method.url}
                                    </code>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>

                {/* Endpoints */}
                <div>
                    <h2 className="text-2xl font-bold text-gray-900">API Endpoints</h2>
                    <div className="mt-4 space-y-4">
                        {service.endpoints.map((endpoint, idx) => (
                            <ApiEndpoint
                                key={idx}
                                endpoint={endpoint}
                                codeExamples={service.codeExamples}
                            />
                        ))}
                    </div>
                </div>
            </div>
        </DocsLayout>
    );
}

// API Endpoint Component
function ApiEndpoint({ endpoint, codeExamples }: { endpoint: Endpoint; codeExamples: any }) {
    const [isExpanded, setIsExpanded] = useState(false);
    const [selectedLang, setSelectedLang] = useState('laravel');

    const methodColors: Record<string, string> = {
        GET: 'bg-green-100 text-green-800',
        POST: 'bg-blue-100 text-blue-800',
        PUT: 'bg-yellow-100 text-yellow-800',
        DELETE: 'bg-red-100 text-red-800',
    };

    return (
        <div className="rounded-lg border bg-white overflow-hidden">
            {/* Header */}
            <div
                className="flex items-center justify-between p-4 cursor-pointer hover:bg-gray-50"
                onClick={() => setIsExpanded(!isExpanded)}
            >
                <div className="flex items-center space-x-3">
                    <span className={`rounded px-2 py-1 text-xs font-bold ${methodColors[endpoint.method]}`}>
                        {endpoint.method}
                    </span>
                    <code className="text-sm font-mono text-gray-900">{endpoint.path}</code>
                </div>
                {isExpanded ? (
                    <ChevronUp className="h-5 w-5 text-gray-400" />
                ) : (
                    <ChevronDown className="h-5 w-5 text-gray-400" />
                )}
            </div>

            <p className="px-4 pb-4 text-sm text-gray-600">{endpoint.description}</p>

            {/* Expanded Content */}
            {isExpanded && (
                <div className="border-t bg-gray-50 p-4 space-y-4">
                    {/* Authentication */}
                    <div>
                        <h4 className="text-sm font-semibold text-gray-900">Authentication</h4>
                        <code className="mt-1 block rounded bg-white p-2 text-sm">{endpoint.auth}</code>
                    </div>

                    {/* Parameters */}
                    {endpoint.requestBody && endpoint.requestBody.length > 0 && (
                        <div>
                            <h4 className="text-sm font-semibold text-gray-900">Request Body</h4>
                            <div className="mt-2 overflow-x-auto">
                                <table className="min-w-full text-sm">
                                    <thead>
                                        <tr className="border-b">
                                            <th className="px-2 py-1 text-left">Field</th>
                                            <th className="px-2 py-1 text-left">Type</th>
                                            <th className="px-2 py-1 text-left">Required</th>
                                            <th className="px-2 py-1 text-left">Description</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {endpoint.requestBody.map((param, idx) => (
                                            <tr key={idx} className="border-b">
                                                <td className="px-2 py-1 font-mono">{param.name}</td>
                                                <td className="px-2 py-1 text-gray-600">{param.type}</td>
                                                <td className="px-2 py-1">
                                                    {param.required ? (
                                                        <span className="text-red-600">Yes</span>
                                                    ) : (
                                                        <span className="text-gray-400">No</span>
                                                    )}
                                                </td>
                                                <td className="px-2 py-1 text-gray-600">{param.description}</td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    )}

                    {/* Response */}
                    <div>
                        <h4 className="text-sm font-semibold text-gray-900">Response Example</h4>
                        <CodeExample code={JSON.stringify(endpoint.response, null, 2)} language="json" />
                    </div>

                    {/* Code Examples */}
                    <div>
                        <div className="flex items-center justify-between">
                            <h4 className="text-sm font-semibold text-gray-900">Code Examples</h4>
                            <div className="flex space-x-2">
                                {Object.keys(codeExamples).map((lang) => (
                                    <button
                                        key={lang}
                                        onClick={() => setSelectedLang(lang)}
                                        className={`px-3 py-1 text-xs font-medium rounded ${selectedLang === lang
                                                ? 'bg-blue-600 text-white'
                                                : 'bg-white text-gray-700 hover:bg-gray-100'
                                            }`}
                                    >
                                        {lang === 'laravel' ? 'Laravel (PHP)' : lang === 'python' ? 'Python' : 'JavaScript'}
                                    </button>
                                ))}
                            </div>
                        </div>
                        <div className="mt-2">
                            <CodeExample
                                code={codeExamples[selectedLang]?.upload || ''}
                                language={selectedLang === 'laravel' ? 'php' : selectedLang === 'python' ? 'python' : 'javascript'}
                            />
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}

// Code Example Component with Copy
function CodeExample({ code, language }: { code: string; language: string }) {
    const [copied, setCopied] = useState(false);

    const handleCopy = () => {
        navigator.clipboard.writeText(code);
        setCopied(true);
        setTimeout(() => setCopied(false), 2000);
    };

    return (
        <div className="relative">
            <button
                onClick={handleCopy}
                className="absolute right-2 top-2 rounded bg-gray-800 p-2 text-white hover:bg-gray-700"
            >
                {copied ? <Check className="h-4 w-4" /> : <Copy className="h-4 w-4" />}
            </button>
            <pre className="rounded bg-gray-900 p-4 text-sm text-white overflow-x-auto">
                <code>{code}</code>
            </pre>
        </div>
    );
}
