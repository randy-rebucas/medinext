import React from 'react';
import { Link } from '@inertiajs/react';
import { CheckCircleIcon, XCircleIcon, ExclamationTriangleIcon, InformationCircleIcon } from '@heroicons/react/24/outline';
import InstallationLayout from '@/components/InstallationLayout';

interface SystemRequirements {
  php_version: {
    required: string;
    current: string;
    status: boolean;
  };
  extensions: Record<string, boolean>;
  permissions: Record<string, boolean>;
}

interface DatabaseConnection {
  status: boolean;
  message: string;
}

interface Props {
  system_requirements: SystemRequirements;
  database_connection: DatabaseConnection;
}

export default function Welcome({ system_requirements, database_connection }: Props) {
  const allRequirementsMet = system_requirements.php_version.status &&
    Object.values(system_requirements.extensions).every(Boolean) &&
    Object.values(system_requirements.permissions).every(Boolean);

  const canProceed = allRequirementsMet && database_connection.status;

  const getRequirementStatus = () => {
    const failedRequirements = [];
    
    if (!system_requirements.php_version.status) {
      failedRequirements.push('PHP Version');
    }
    
    const failedExtensions = Object.entries(system_requirements.extensions)
      .filter(([_name, status]) => !status)
      .map(([name]) => name);
    
    const failedPermissions = Object.entries(system_requirements.permissions)
      .filter(([_name, status]) => !status)
      .map(([name]) => name.replace('_', ' '));
    
    return {
      failed: failedRequirements.concat(failedExtensions, failedPermissions),
      total: 1 + Object.keys(system_requirements.extensions).length + Object.keys(system_requirements.permissions).length
    };
  };

  const requirementStatus = getRequirementStatus();

  return (
    <InstallationLayout 
      title="Welcome" 
      subtitle="Electronic Medical Records System"
      step={1}
      totalSteps={4}
    >
      <div className="text-center mb-8">
        <h2 className="text-2xl font-bold text-gray-900 mb-2">Welcome to MediNext</h2>
        <p className="text-gray-600">
          Let's get your EMR system set up. This installation wizard will guide you through the process.
        </p>
      </div>

      {/* System Requirements Overview */}
      <div className="mb-8">
        <div className="flex items-center justify-between mb-4">
          <h3 className="text-lg font-medium text-gray-900">System Requirements</h3>
          <div className="flex items-center">
            <span className="text-sm text-gray-500 mr-2">
              {requirementStatus.total - requirementStatus.failed.length} of {requirementStatus.total} passed
            </span>
            <div className="w-16 bg-gray-200 rounded-full h-2">
              <div 
                className="bg-green-500 h-2 rounded-full transition-all duration-300"
                style={{ width: `${((requirementStatus.total - requirementStatus.failed.length) / requirementStatus.total) * 100}%` }}
              ></div>
            </div>
          </div>
        </div>
        
        {/* PHP Version */}
        <div className="mb-4">
          <div className={`flex items-center justify-between p-4 rounded-lg border ${
            system_requirements.php_version.status 
              ? 'bg-green-50 border-green-200' 
              : 'bg-red-50 border-red-200'
          }`}>
            <div className="flex items-center">
              {system_requirements.php_version.status ? (
                <CheckCircleIcon className="h-5 w-5 text-green-500 mr-3" />
              ) : (
                <XCircleIcon className="h-5 w-5 text-red-500 mr-3" />
              )}
              <div>
                <span className="font-medium text-gray-900">PHP Version</span>
                <p className="text-sm text-gray-600">Required for Laravel framework</p>
              </div>
            </div>
            <div className="text-right">
              <span className={`text-sm font-medium ${
                system_requirements.php_version.status ? 'text-green-600' : 'text-red-600'
              }`}>
                {system_requirements.php_version.current}
              </span>
              {!system_requirements.php_version.status && (
                <p className="text-xs text-red-500">Required: {system_requirements.php_version.required}+</p>
              )}
            </div>
          </div>
        </div>

        {/* PHP Extensions */}
        <div className="mb-4">
          <h4 className="text-sm font-medium text-gray-700 mb-3">PHP Extensions</h4>
          <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
            {Object.entries(system_requirements.extensions).map(([extension, status]) => (
              <div key={extension} className={`flex items-center p-3 rounded-lg border ${
                status ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200'
              }`}>
                {status ? (
                  <CheckCircleIcon className="h-4 w-4 text-green-500 mr-3 flex-shrink-0" />
                ) : (
                  <XCircleIcon className="h-4 w-4 text-red-500 mr-3 flex-shrink-0" />
                )}
                <span className="text-sm font-medium">{extension}</span>
              </div>
            ))}
          </div>
        </div>

        {/* File Permissions */}
        <div className="mb-6">
          <h4 className="text-sm font-medium text-gray-700 mb-3">File Permissions</h4>
          <div className="space-y-2">
            {Object.entries(system_requirements.permissions).map(([permission, status]) => (
              <div key={permission} className={`flex items-center p-3 rounded-lg border ${
                status ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200'
              }`}>
                {status ? (
                  <CheckCircleIcon className="h-4 w-4 text-green-500 mr-3 flex-shrink-0" />
                ) : (
                  <XCircleIcon className="h-4 w-4 text-red-500 mr-3 flex-shrink-0" />
                )}
                <div>
                  <span className="text-sm font-medium">{permission.replace('_', ' ')}</span>
                  <p className="text-xs text-gray-600">Directory write permissions</p>
                </div>
              </div>
            ))}
          </div>
        </div>
      </div>

      {/* Database Connection */}
      <div className="mb-8">
        <h3 className="text-lg font-medium text-gray-900 mb-4">Database Connection</h3>
        <div className={`flex items-center justify-between p-4 rounded-lg border ${
          database_connection.status 
            ? 'bg-green-50 border-green-200' 
            : 'bg-red-50 border-red-200'
        }`}>
          <div className="flex items-center">
            {database_connection.status ? (
              <CheckCircleIcon className="h-5 w-5 text-green-500 mr-3" />
            ) : (
              <XCircleIcon className="h-5 w-5 text-red-500 mr-3" />
            )}
            <div>
              <span className="font-medium text-gray-900">Database Connection</span>
              <p className="text-sm text-gray-600">Required for data storage</p>
            </div>
          </div>
          <div className="text-right">
            <span className={`text-sm font-medium ${
              database_connection.status ? 'text-green-600' : 'text-red-600'
            }`}>
              {database_connection.status ? 'Connected' : 'Failed'}
            </span>
          </div>
        </div>
        {!database_connection.status && (
          <div className="mt-3 p-3 bg-red-50 border border-red-200 rounded-lg">
            <div className="flex items-start">
              <ExclamationTriangleIcon className="h-5 w-5 text-red-500 mr-2 mt-0.5 flex-shrink-0" />
              <div>
                <p className="text-sm text-red-800 font-medium">Connection Failed</p>
                <p className="text-sm text-red-700 mt-1">{database_connection.message}</p>
              </div>
            </div>
          </div>
        )}
      </div>

      {/* Status Summary */}
      <div className="mb-8">
        <div className={`p-6 rounded-lg border ${
          canProceed 
            ? 'bg-green-50 border-green-200' 
            : 'bg-red-50 border-red-200'
        }`}>
          <div className="flex items-start">
            {canProceed ? (
              <CheckCircleIcon className="h-6 w-6 text-green-500 mr-3 mt-0.5 flex-shrink-0" />
            ) : (
              <XCircleIcon className="h-6 w-6 text-red-500 mr-3 mt-0.5 flex-shrink-0" />
            )}
            <div className="flex-1">
              <h4 className={`text-lg font-semibold ${
                canProceed ? 'text-green-800' : 'text-red-800'
              }`}>
                {canProceed ? 'All requirements met!' : 'Requirements not met'}
              </h4>
              <p className={`text-sm mt-2 ${
                canProceed ? 'text-green-700' : 'text-red-700'
              }`}>
                {canProceed 
                  ? 'Your system meets all requirements. You can proceed with the installation.'
                  : 'Please fix the issues above before proceeding with the installation.'
                }
              </p>
              {!canProceed && requirementStatus.failed.length > 0 && (
                <div className="mt-3">
                  <p className="text-sm font-medium text-red-800 mb-2">Failed requirements:</p>
                  <ul className="text-sm text-red-700 list-disc list-inside space-y-1">
                    {requirementStatus.failed.map((req, index) => (
                      <li key={index}>{req}</li>
                    ))}
                    {!database_connection.status && <li>Database Connection</li>}
                  </ul>
                </div>
              )}
            </div>
          </div>
        </div>
      </div>

      {/* Action Buttons */}
      <div className="flex flex-col sm:flex-row justify-between gap-4">
        <button
          onClick={() => window.location.reload()}
          className="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
        >
          <svg className="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
          Refresh Check
        </button>
        
        <Link
          href="/install/database"
          className={`inline-flex items-center justify-center px-6 py-2 text-sm font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors ${
            canProceed
              ? 'text-white bg-blue-600 hover:bg-blue-700 focus:ring-blue-500'
              : 'text-gray-400 bg-gray-200 cursor-not-allowed'
          }`}
          disabled={!canProceed}
        >
          {canProceed ? (
            <>
              Continue Installation
              <svg className="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
              </svg>
            </>
          ) : (
            'Fix Requirements First'
          )}
        </Link>
      </div>
    </InstallationLayout>
  );
}
