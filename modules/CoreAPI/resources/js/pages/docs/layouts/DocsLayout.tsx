import React, { PropsWithChildren } from 'react';
import { Head, Link } from '@inertiajs/react';
import { Book, Github, ExternalLink } from 'lucide-react';

interface Service {
    id: string;
    name: string;
    description: string;
    icon: string;
    status: string;
}

interface DocsLayoutProps extends PropsWithChildren {
    services?: Service[];
}

export default function DocsLayout({ children, services = [] }: DocsLayoutProps) {
    return (
        <>
            <Head>
                <title>API Documentation - CityResQ360</title>
                <meta name="description" content="Tài liệu API đầy đủ cho các dịch vụ CityResQ360" />
            </Head>

            <div className="min-h-screen bg-gray-50">
                {/* Header */}
                <header className="sticky top-0 z-50 border-b bg-white shadow-sm">
                    <div className="container mx-auto px-4">
                        <div className="flex h-16 items-center justify-between">
                            {/* Logo */}
                            <Link href="/documents" className="flex items-center space-x-2">
                                <Book className="h-6 w-6 text-blue-600" />
                                <span className="text-xl font-bold text-gray-900">
                                    CityResQ360 <span className="text-blue-600">API Docs</span>
                                </span>
                            </Link>

                            {/* Navigation */}
                            <nav className="hidden md:flex items-center space-x-6">
                                <Link
                                    href="/"
                                    className="text-sm text-gray-600 hover:text-gray-900"
                                >
                                    Trang Chủ
                                </Link>
                                <a
                                    href="https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    className="flex items-center space-x-1 text-sm text-gray-600 hover:text-gray-900"
                                >
                                    <Github className="h-4 w-4" />
                                    <span>GitHub</span>
                                    <ExternalLink className="h-3  w-3" />
                                </a>
                            </nav>
                        </div>
                    </div>
                </header>

                <div className="container mx-auto">
                    <div className="flex">
                        {/* Sidebar */}
                        {services.length > 0 && (
                            <aside className="hidden lg:block w-64 shrink-0">
                                <div className="sticky top-20 h-[calc(100vh-5rem)] overflow-y-auto p-6">
                                    <ServiceNav services={services} />
                                </div>
                            </aside>
                        )}

                        {/* Main Content */}
                        <main className="flex-1 p-6 lg:p-8">
                            <div className="mx-auto max-w-4xl">
                                {children}
                            </div>
                        </main>
                    </div>
                </div>

                {/* Footer */}
                <footer className="border-t bg-white mt-12">
                    <div className="container mx-auto px-4 py-6">
                        <p className="text-center text-sm text-gray-600">
                            © 2025 CityResQ360 - DTU-DZ Team. Licensed under GPL-3.0
                        </p>
                    </div>
                </footer>
            </div>
        </>
    );
}

// Service Navigation Component
function ServiceNav({ services }: { services: Service[] }) {
    const currentPath = window.location.pathname;

    return (
        <nav className="space-y-1">
            <h3 className="mb-3 text-xs font-semibold uppercase text-gray-500">
                Services
            </h3>
            {services.map((service) => {
                const href = `/documents/${service.id}`;
                const isActive = currentPath === href;

                return (
                    <Link
                        key={service.id}
                        href={href}
                        className={`
                            flex items-center space-x-2 rounded-md px-3 py-2 text-sm font-medium
                            ${isActive
                                ? 'bg-blue-50 text-blue-700'
                                : 'text-gray-700 hover:bg-gray-100'
                            }
                        `}
                    >
                        <span>{service.name}</span>
                        {service.status === 'stable' && (
                            <span className="ml-auto rounded-full bg-green-100 px-2 py-0.5 text-xs text-green-700">
                                Stable
                            </span>
                        )}
                        {service.status === 'coming-soon' && (
                            <span className="ml-auto rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-600">
                                Soon
                            </span>
                        )}
                    </Link>
                );
            })}
        </nav>
    );
}
