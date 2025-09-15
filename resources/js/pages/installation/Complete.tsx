import React from 'react';
import { Link } from '@inertiajs/react';
import { CheckCircleIcon, ArrowRightIcon, ShieldCheckIcon, UserGroupIcon, CogIcon } from '@heroicons/react/24/outline';
import InstallationLayout from '@/components/InstallationLayout';

interface Props {
  admin_email: string;
}

export default function Complete({ admin_email }: Props) {
  return (
    <InstallationLayout 
      title="Installation Complete" 
      subtitle="Your MediNext EMR system is ready to use"
      step={4}
      totalSteps={4}
    >
      <div className="text-center mb-8">
        <div className="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-green-100 mb-6">
          <CheckCircleIcon className="h-10 w-10 text-green-600" />
        </div>
        <h2 className="text-3xl font-bold text-gray-900 mb-3">Installation Successful!</h2>
        <p className="text-lg text-gray-600">
          Your MediNext EMR system has been successfully installed and configured.
        </p>
      </div>

      {/* Installation Summary */}
      <div className="bg-gray-50 rounded-lg p-6 mb-8">
        <h3 className="text-lg font-medium text-gray-900 mb-6">Installation Summary</h3>
        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div className="flex items-center p-3 bg-white rounded-lg border border-green-200">
            <CheckCircleIcon className="h-5 w-5 text-green-500 mr-3 flex-shrink-0" />
            <span className="text-sm font-medium text-gray-700">Database configured and migrated</span>
          </div>
          <div className="flex items-center p-3 bg-white rounded-lg border border-green-200">
            <CheckCircleIcon className="h-5 w-5 text-green-500 mr-3 flex-shrink-0" />
            <span className="text-sm font-medium text-gray-700">System permissions and roles created</span>
          </div>
          <div className="flex items-center p-3 bg-white rounded-lg border border-green-200">
            <CheckCircleIcon className="h-5 w-5 text-green-500 mr-3 flex-shrink-0" />
            <span className="text-sm font-medium text-gray-700">Admin account created</span>
          </div>
          <div className="flex items-center p-3 bg-white rounded-lg border border-green-200">
            <CheckCircleIcon className="h-5 w-5 text-green-500 mr-3 flex-shrink-0" />
            <span className="text-sm font-medium text-gray-700">Clinic information configured</span>
          </div>
          <div className="flex items-center p-3 bg-white rounded-lg border border-green-200">
            <CheckCircleIcon className="h-5 w-5 text-green-500 mr-3 flex-shrink-0" />
            <span className="text-sm font-medium text-gray-700">Default settings applied</span>
          </div>
          <div className="flex items-center p-3 bg-white rounded-lg border border-green-200">
            <CheckCircleIcon className="h-5 w-5 text-green-500 mr-3 flex-shrink-0" />
            <span className="text-sm font-medium text-gray-700">System ready for use</span>
          </div>
        </div>
      </div>

      {/* Login Information */}
      <div className="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-8">
        <div className="flex items-center mb-4">
          <ShieldCheckIcon className="h-6 w-6 text-blue-600 mr-3" />
          <h3 className="text-lg font-medium text-blue-900">Your Admin Account</h3>
        </div>
        <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div className="bg-white rounded-lg p-4 border border-blue-200">
            <div className="text-center">
              <div className="text-sm font-medium text-blue-800 mb-1">Email</div>
              <div className="text-sm text-blue-700 font-mono">{admin_email}</div>
            </div>
          </div>
          <div className="bg-white rounded-lg p-4 border border-blue-200">
            <div className="text-center">
              <div className="text-sm font-medium text-blue-800 mb-1">Role</div>
              <div className="text-sm text-blue-700">Super Administrator</div>
            </div>
          </div>
          <div className="bg-white rounded-lg p-4 border border-blue-200">
            <div className="text-center">
              <div className="text-sm font-medium text-blue-800 mb-1">Access</div>
              <div className="text-sm text-blue-700">Full System Access</div>
            </div>
          </div>
        </div>
      </div>

      {/* Next Steps */}
      <div className="mb-8">
        <h3 className="text-lg font-medium text-gray-900 mb-6">Next Steps</h3>
        <div className="space-y-4">
          <div className="flex items-start p-4 bg-white rounded-lg border border-gray-200">
            <div className="flex-shrink-0">
              <div className="flex items-center justify-center h-8 w-8 rounded-full bg-blue-100 text-blue-600 text-sm font-medium">
                1
              </div>
            </div>
            <div className="ml-4">
              <h4 className="text-sm font-medium text-gray-900 mb-1">Log in to your admin account</h4>
              <p className="text-sm text-gray-600">Use the credentials you just created to access the system</p>
            </div>
          </div>
          <div className="flex items-start p-4 bg-white rounded-lg border border-gray-200">
            <div className="flex-shrink-0">
              <div className="flex items-center justify-center h-8 w-8 rounded-full bg-blue-100 text-blue-600 text-sm font-medium">
                2
              </div>
            </div>
            <div className="ml-4">
              <h4 className="text-sm font-medium text-gray-900 mb-1">Configure additional settings</h4>
              <p className="text-sm text-gray-600">Customize system preferences in the admin panel</p>
            </div>
          </div>
          <div className="flex items-start p-4 bg-white rounded-lg border border-gray-200">
            <div className="flex-shrink-0">
              <div className="flex items-center justify-center h-8 w-8 rounded-full bg-blue-100 text-blue-600 text-sm font-medium">
                3
              </div>
            </div>
            <div className="ml-4">
              <h4 className="text-sm font-medium text-gray-900 mb-1">Add staff members</h4>
              <p className="text-sm text-gray-600">Create user accounts and assign appropriate roles</p>
            </div>
          </div>
          <div className="flex items-start p-4 bg-white rounded-lg border border-gray-200">
            <div className="flex-shrink-0">
              <div className="flex items-center justify-center h-8 w-8 rounded-full bg-blue-100 text-blue-600 text-sm font-medium">
                4
              </div>
            </div>
            <div className="ml-4">
              <h4 className="text-sm font-medium text-gray-900 mb-1">Set up clinic operations</h4>
              <p className="text-sm text-gray-600">Configure working hours, appointment types, and services</p>
            </div>
          </div>
          <div className="flex items-start p-4 bg-white rounded-lg border border-gray-200">
            <div className="flex-shrink-0">
              <div className="flex items-center justify-center h-8 w-8 rounded-full bg-blue-100 text-blue-600 text-sm font-medium">
                5
              </div>
            </div>
            <div className="ml-4">
              <h4 className="text-sm font-medium text-gray-900 mb-1">Start managing patients</h4>
              <p className="text-sm text-gray-600">Add patients and begin scheduling appointments</p>
            </div>
          </div>
        </div>
      </div>

      {/* Security Notice */}
      <div className="bg-yellow-50 border border-yellow-200 rounded-lg p-6 mb-8">
        <div className="flex items-start">
          <div className="flex-shrink-0">
            <svg className="h-6 w-6 text-yellow-500" viewBox="0 0 20 20" fill="currentColor">
              <path fillRule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clipRule="evenodd" />
            </svg>
          </div>
          <div className="ml-3">
            <h3 className="text-sm font-medium text-yellow-800 mb-2">Security Notice</h3>
            <div className="text-sm text-yellow-700">
              <p className="mb-3">
                For security reasons, we recommend removing the installation files after completing the setup.
                You can do this by running the following command in your terminal:
              </p>
              <div className="bg-yellow-100 rounded-lg p-3">
                <code className="text-xs font-mono text-yellow-800">
                  php artisan install:cleanup
                </code>
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* Action Button */}
      <div className="text-center">
        <Link
          href="/login"
          className="inline-flex items-center px-8 py-3 text-base font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors shadow-lg"
        >
          Go to Login
          <ArrowRightIcon className="h-5 w-5 ml-2" />
        </Link>
      </div>
    </InstallationLayout>
  );
}
