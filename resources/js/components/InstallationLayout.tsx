import React from 'react';
import { Head } from '@inertiajs/react';

interface InstallationLayoutProps {
  title: string;
  subtitle: string;
  children: React.ReactNode;
  step?: number;
  totalSteps?: number;
}

export default function InstallationLayout({ 
  title, 
  subtitle, 
  children, 
  step, 
  totalSteps 
}: InstallationLayoutProps) {
  return (
    <>
      <Head title={`${title} - MediNext Installation`} />
      <style>{`
        .installation-page {
          font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        .installation-page * {
          color: inherit !important;
        }
        .installation-page h1,
        .installation-page h2,
        .installation-page h3,
        .installation-page h4,
        .installation-page h5,
        .installation-page h6 {
          color: #111827 !important;
          font-weight: 600;
        }
        .installation-page p,
        .installation-page span,
        .installation-page div {
          color: #374151 !important;
        }
        .installation-page .text-gray-900 {
          color: #111827 !important;
        }
        .installation-page .text-gray-600 {
          color: #4b5563 !important;
        }
        .installation-page .text-gray-700 {
          color: #374151 !important;
        }
        .installation-page .text-gray-500 {
          color: #6b7280 !important;
        }
        .installation-page .bg-white {
          background-color: #ffffff !important;
        }
        .installation-page .bg-gray-50 {
          background-color: #f9fafb !important;
        }
        .installation-page .bg-gray-100 {
          background-color: #f3f4f6 !important;
        }
        .installation-page .border-gray-300 {
          border-color: #d1d5db !important;
        }
        .installation-page .bg-blue-600 {
          background-color: #2563eb !important;
        }
        .installation-page .bg-blue-700 {
          background-color: #1d4ed8 !important;
        }
        .installation-page .text-white {
          color: #ffffff !important;
        }
        .installation-page .text-green-600 {
          color: #059669 !important;
        }
        .installation-page .text-red-600 {
          color: #dc2626 !important;
        }
        .installation-page .text-blue-600 {
          color: #2563eb !important;
        }
        .installation-page .border-blue-200 {
          border-color: #bfdbfe !important;
        }
        .installation-page .bg-blue-50 {
          background-color: #eff6ff !important;
        }
        .installation-page .bg-green-50 {
          background-color: #f0fdf4 !important;
        }
        .installation-page .bg-red-50 {
          background-color: #fef2f2 !important;
        }
        .installation-page .bg-yellow-50 {
          background-color: #fffbeb !important;
        }
        .installation-page .border-green-200 {
          border-color: #bbf7d0 !important;
        }
        .installation-page .border-red-200 {
          border-color: #fecaca !important;
        }
        .installation-page .border-yellow-200 {
          border-color: #fde68a !important;
        }
        .installation-page .text-green-800 {
          color: #166534 !important;
        }
        .installation-page .text-red-800 {
          color: #991b1b !important;
        }
        .installation-page .text-blue-800 {
          color: #1e40af !important;
        }
        .installation-page .text-blue-700 {
          color: #1d4ed8 !important;
        }
        .installation-page .text-blue-900 {
          color: #1e3a8a !important;
        }
        .installation-page .text-yellow-800 {
          color: #92400e !important;
        }
        .installation-page .text-yellow-700 {
          color: #b45309 !important;
        }
        .installation-page .text-green-500 {
          color: #10b981 !important;
        }
        .installation-page .text-red-500 {
          color: #ef4444 !important;
        }
        .installation-page .text-yellow-400 {
          color: #fbbf24 !important;
        }
        .installation-page .text-red-400 {
          color: #f87171 !important;
        }
        .installation-page .text-blue-100 {
          color: #dbeafe !important;
        }
        .installation-page .bg-blue-100 {
          background-color: #dbeafe !important;
        }
        .installation-page .bg-green-100 {
          background-color: #dcfce7 !important;
        }
        .installation-page .bg-yellow-100 {
          background-color: #fef3c7 !important;
        }
        .installation-page .bg-red-100 {
          background-color: #fee2e2 !important;
        }
        .installation-page input:focus,
        .installation-page textarea:focus,
        .installation-page select:focus {
          outline: none !important;
          ring: 2px !important;
          ring-color: #3b82f6 !important;
          border-color: #3b82f6 !important;
        }
        .installation-page .focus\\:ring-blue-500:focus {
          --tw-ring-color: #3b82f6 !important;
        }
        .installation-page .focus\\:border-blue-500:focus {
          border-color: #3b82f6 !important;
        }
        .installation-page .hover\\:bg-blue-700:hover {
          background-color: #1d4ed8 !important;
        }
        .installation-page .hover\\:bg-gray-50:hover {
          background-color: #f9fafb !important;
        }
        .installation-page .hover\\:text-blue-500:hover {
          color: #3b82f6 !important;
        }
        .installation-page .disabled\\:opacity-50:disabled {
          opacity: 0.5 !important;
        }
        .installation-page .disabled\\:cursor-not-allowed:disabled {
          cursor: not-allowed !important;
        }
        .installation-page .shadow {
          box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06) !important;
        }
        .installation-page .sm\\:rounded-lg {
          border-radius: 0.5rem !important;
        }
        .installation-page .rounded-lg {
          border-radius: 0.5rem !important;
        }
        .installation-page .rounded-md {
          border-radius: 0.375rem !important;
        }
        .installation-page .rounded-full {
          border-radius: 9999px !important;
        }
        .installation-page .transition-colors {
          transition-property: color, background-color, border-color, text-decoration-color, fill, stroke !important;
          transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1) !important;
          transition-duration: 150ms !important;
        }
        .installation-page .animate-pulse {
          animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite !important;
        }
        @keyframes pulse {
          0%, 100% {
            opacity: 1;
          }
          50% {
            opacity: .5;
          }
        }
        .installation-page .animate-spin {
          animation: spin 1s linear infinite !important;
        }
        @keyframes spin {
          from {
            transform: rotate(0deg);
          }
          to {
            transform: rotate(360deg);
          }
        }
      `}</style>
      
      <div className="installation-page min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
        {/* Progress Bar */}
        {step && totalSteps && (
          <div className="fixed top-0 left-0 right-0 bg-white shadow-sm z-50" role="progressbar" aria-valuenow={Math.floor(step)} aria-valuemin={1} aria-valuemax={totalSteps} aria-label={`Installation progress: step ${Math.floor(step)} of ${totalSteps}`}>
            <div className="max-w-4xl mx-auto px-4 py-3">
              <div className="flex items-center justify-between mb-2">
                <span className="text-sm font-medium text-gray-700">
                  Step {Math.floor(step)} of {totalSteps}
                </span>
                <span className="text-sm text-gray-500">
                  {Math.round((step / totalSteps) * 100)}% Complete
                </span>
              </div>
              <div className="w-full bg-gray-200 rounded-full h-2">
                <div 
                  className="bg-blue-600 h-2 rounded-full transition-all duration-500 ease-out"
                  style={{ width: `${(step / totalSteps) * 100}%` }}
                  aria-hidden="true"
                ></div>
              </div>
            </div>
          </div>
        )}

        {/* Header */}
        <div className="sm:mx-auto sm:w-full sm:max-w-md">
          <div className="text-center">
            <div className="flex items-center justify-center mb-4">
              <div className="w-12 h-12 bg-blue-600 rounded-lg flex items-center justify-center">
                <svg className="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
              </div>
            </div>
            <h1 className="text-4xl font-bold text-gray-900 mb-2">MediNext EMR</h1>
            <p className="text-lg text-gray-600">{subtitle}</p>
          </div>
        </div>

        {/* Main Content */}
        <div className="mt-8 sm:mx-auto sm:w-full sm:max-w-2xl px-4">
          <div className="bg-white py-6 px-4 shadow-xl sm:rounded-lg sm:px-8 lg:px-10">
            {children}
          </div>
        </div>

        {/* Footer */}
        <div className="mt-8 text-center">
          <p className="text-sm text-gray-500">
            Need help? Check our{' '}
            <a href="#" className="text-blue-600 hover:text-blue-500 transition-colors">
              installation guide
            </a>
          </p>
        </div>
      </div>
    </>
  );
}
