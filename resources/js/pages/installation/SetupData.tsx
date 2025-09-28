import React, { useEffect } from 'react';
import { router } from '@inertiajs/react';
import { CheckCircleIcon, XCircleIcon, ClockIcon, CogIcon } from '@heroicons/react/24/outline';
import InstallationLayout from '@/components/InstallationLayout';

interface Props {
  errors?: Record<string, string>;
}

export default function SetupData({ errors }: Props) {
  // Auto-start system data creation when page loads
  useEffect(() => {
    // Small delay to ensure page is fully loaded
    const timer = setTimeout(() => {
      router.post('/install/setup-data');
    }, 1000);

    return () => clearTimeout(timer);
  }, []);

  const hasErrors = errors && Object.keys(errors).length > 0;

  return (
    <InstallationLayout 
      title="System Setup" 
      subtitle="Setting up system data and permissions"
      step={3}
      totalSteps={4}
    >
      <div className="text-center mb-8">
        <h2 className="text-2xl font-bold text-gray-900 mb-2">System Data Setup</h2>
        <p className="text-gray-600">
          We're setting up your system with essential data, permissions, and roles.
        </p>
      </div>

      {/* Setup Progress */}
      <div className="bg-blue-50 border border-blue-200 rounded-lg p-8">
        <div className="text-center">
          <div className="flex justify-center mb-4">
            <div className="relative">
              <CogIcon className="h-16 w-16 text-blue-500" />
              <div className="absolute inset-0 flex items-center justify-center">
                <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
              </div>
            </div>
          </div>
          
          <h3 className="text-xl font-semibold text-blue-800 mb-2">
            Setting Up System Data
          </h3>
          
          <p className="text-lg text-blue-700 mb-6">
            Creating permissions, roles, and system settings...
          </p>

          {/* Progress Steps */}
          <div className="space-y-4">
            <div className="flex items-center justify-center">
              <div className="flex-shrink-0 w-6 h-6 rounded-full bg-blue-500 flex items-center justify-center">
                <div className="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>
              </div>
              <span className="ml-3 text-sm text-blue-700">
                Creating system permissions (80+ permissions)
              </span>
            </div>
            
            <div className="flex items-center justify-center">
              <div className="flex-shrink-0 w-6 h-6 rounded-full bg-gray-300 flex items-center justify-center">
                <div className="w-2 h-2 bg-white rounded-full"></div>
              </div>
              <span className="ml-3 text-sm text-gray-500">
                Creating user roles (6 roles)
              </span>
            </div>
            
            <div className="flex items-center justify-center">
              <div className="flex-shrink-0 w-6 h-6 rounded-full bg-gray-300 flex items-center justify-center">
                <div className="w-2 h-2 bg-white rounded-full"></div>
              </div>
              <span className="ml-3 text-sm text-gray-500">
                Creating system settings (50+ settings)
              </span>
            </div>
            
            <div className="flex items-center justify-center">
              <div className="flex-shrink-0 w-6 h-6 rounded-full bg-gray-300 flex items-center justify-center">
                <div className="w-2 h-2 bg-white rounded-full"></div>
              </div>
              <span className="ml-3 text-sm text-gray-500">
                Setting up activity logging
              </span>
            </div>
          </div>

          {/* Progress Bar */}
          <div className="mt-8">
            <div className="w-full bg-gray-200 rounded-full h-2">
              <div className="bg-blue-600 h-2 rounded-full animate-pulse" style={{ width: '25%' }}></div>
            </div>
            <p className="text-sm text-gray-600 mt-2">Setting up system data...</p>
          </div>
        </div>
      </div>

      {/* Error Display */}
      {hasErrors && (
        <div className="mt-6 bg-red-50 border border-red-200 rounded-lg p-4">
          <div className="flex items-start">
            <div className="flex-shrink-0">
              <XCircleIcon className="h-5 w-5 text-red-400" />
            </div>
            <div className="ml-3">
              <h3 className="text-sm font-medium text-red-800">Setup Failed</h3>
              <div className="text-sm text-red-700 mt-1">
                {Object.entries(errors).map(([field, message]) => (
                  <p key={field}>{message}</p>
                ))}
              </div>
              <div className="mt-4">
                <button
                  onClick={() => router.post('/install/setup-data')}
                  className="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors"
                >
                  Try Again
                </button>
              </div>
            </div>
          </div>
        </div>
      )}

      {/* Help Text */}
      <div className="mt-8 p-4 bg-blue-50 border border-blue-200 rounded-lg">
        <div className="flex items-start">
          <div className="flex-shrink-0">
            <svg className="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
              <path fillRule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clipRule="evenodd" />
            </svg>
          </div>
          <div className="ml-3">
            <h4 className="text-sm font-medium text-blue-800">Please wait</h4>
            <p className="text-sm text-blue-700 mt-1">
              System data setup is in progress. This process creates all the necessary permissions, 
              roles, and settings for your EMR system. The page will automatically redirect when complete.
            </p>
          </div>
        </div>
      </div>
    </InstallationLayout>
  );
}
