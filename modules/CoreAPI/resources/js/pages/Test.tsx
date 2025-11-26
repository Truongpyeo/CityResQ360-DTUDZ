import { Head } from '@inertiajs/react';

export default function TestPage() {
    return (
        <>
            <Head title="Test" />
            <div className="min-h-screen bg-gray-100 flex items-center justify-center">
                <div className="bg-white p-8 rounded-lg shadow-lg">
                    <h1 className="text-2xl font-bold text-gray-800">
                        React is Working! 🎉
                    </h1>
                    <p className="mt-4 text-gray-600">
                        If you see this, React + Inertia is configured correctly.
                    </p>
                </div>
            </div>
        </>
    );
}
