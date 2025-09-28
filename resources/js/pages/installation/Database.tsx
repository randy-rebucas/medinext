import React, { useState } from 'react';
import { Link, useForm } from '@inertiajs/react';
import { ArrowLeftIcon, ArrowRightIcon, EyeIcon, EyeSlashIcon, InformationCircleIcon } from '@heroicons/react/24/outline';
import InstallationLayout from '@/components/InstallationLayout';

export default function Database() {
  const [showPassword, setShowPassword] = useState(false);
  
  const { data, setData, post, processing, errors } = useForm({
    db_host: 'localhost',
    db_port: 3306,
    db_name: '',
    db_username: '',
    db_password: '',
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    post('/install/database');
  };

  const isFormValid = data.db_name.trim() && data.db_username.trim() && data.db_host.trim();
  
  const getConnectionStatus = () => {
    if (Object.keys(errors).length > 0) {
      return { status: 'error', message: 'Connection failed' };
    }
    if (isFormValid) {
      return { status: 'ready', message: 'Ready to test connection' };
    }
    return { status: 'incomplete', message: 'Please fill required fields' };
  };
  
  const connectionStatus = getConnectionStatus();

  return (
    <InstallationLayout 
      title="Database Configuration" 
      subtitle="Configure your database connection"
      step={2}
      totalSteps={4}
    >
      <div className="text-center mb-8">
        <h2 className="text-2xl font-bold text-gray-900 mb-2">Database Setup</h2>
        <p className="text-gray-600">
          Configure your database connection. Make sure your database server is running and accessible.
        </p>
      </div>

      {/* Database Info */}
      <div className="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
        <div className="flex items-start">
          <InformationCircleIcon className="h-5 w-5 text-blue-500 mr-3 mt-0.5 flex-shrink-0" />
          <div>
            <h3 className="text-sm font-medium text-blue-800">Database Requirements</h3>
            <div className="mt-2 text-sm text-blue-700">
              <p>MediNext supports MySQL, PostgreSQL, and SQLite databases. Make sure:</p>
              <ul className="mt-2 list-disc list-inside space-y-1">
                <li>Your database server is running</li>
                <li>The database exists (or you have permission to create it)</li>
                <li>The user has appropriate permissions</li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      {/* Connection Status */}
      <div className={`mb-6 p-4 rounded-lg border ${
        connectionStatus.status === 'error' 
          ? 'bg-red-50 border-red-200' 
          : connectionStatus.status === 'ready' 
            ? 'bg-green-50 border-green-200' 
            : 'bg-yellow-50 border-yellow-200'
      }`}>
        <div className="flex items-center">
          {connectionStatus.status === 'error' && (
            <svg className="h-5 w-5 text-red-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          )}
          {connectionStatus.status === 'ready' && (
            <svg className="h-5 w-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          )}
          {connectionStatus.status === 'incomplete' && (
            <svg className="h-5 w-5 text-yellow-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 19.5c-.77.833.192 2.5 1.732 2.5z" />
            </svg>
          )}
          <div>
            <p className={`text-sm font-medium ${
              connectionStatus.status === 'error' 
                ? 'text-red-800' 
                : connectionStatus.status === 'ready' 
                  ? 'text-green-800' 
                  : 'text-yellow-800'
            }`}>
              {connectionStatus.message}
            </p>
            {connectionStatus.status === 'incomplete' && (
              <p className="text-xs text-yellow-700 mt-1">Fill in the required fields to test your database connection</p>
            )}
          </div>
        </div>
      </div>

      <form onSubmit={handleSubmit} className="space-y-6">
        {/* Connection Details */}
        <div className="grid grid-cols-1 sm:grid-cols-2 gap-6">
          {/* Database Host */}
          <div>
            <label htmlFor="db_host" className="block text-sm font-medium text-gray-700 mb-2">
              Database Host
            </label>
            <div className="relative">
              <input
                id="db_host"
                name="db_host"
                type="text"
                value={data.db_host}
                onChange={(e) => setData('db_host', e.target.value)}
                className={`block w-full px-3 py-2 border rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-colors ${
                  errors.db_host ? 'border-red-300' : 'border-gray-300'
                }`}
                placeholder="localhost"
              />
            </div>
            {errors.db_host && (
              <p className="mt-1 text-sm text-red-600">{errors.db_host}</p>
            )}
          </div>

          {/* Database Port */}
          <div>
            <label htmlFor="db_port" className="block text-sm font-medium text-gray-700 mb-2">
              Database Port
            </label>
            <div className="relative">
              <input
                id="db_port"
                name="db_port"
                type="number"
                value={data.db_port}
                onChange={(e) => setData('db_port', parseInt(e.target.value) || 3306)}
                className={`block w-full px-3 py-2 border rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-colors ${
                  errors.db_port ? 'border-red-300' : 'border-gray-300'
                }`}
                placeholder="3306"
                min="1"
                max="65535"
              />
            </div>
            {errors.db_port && (
              <p className="mt-1 text-sm text-red-600">{errors.db_port}</p>
            )}
          </div>
        </div>

        {/* Database Name */}
        <div>
          <label htmlFor="db_name" className="block text-sm font-medium text-gray-700 mb-2">
            Database Name
          </label>
          <div className="relative">
            <input
              id="db_name"
              name="db_name"
              type="text"
              value={data.db_name}
              onChange={(e) => setData('db_name', e.target.value)}
              className={`block w-full px-3 py-2 border rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-colors ${
                errors.db_name ? 'border-red-300' : 'border-gray-300'
              }`}
              placeholder="medinext_db"
              required
            />
          </div>
          {errors.db_name && (
            <p className="mt-1 text-sm text-red-600">{errors.db_name}</p>
          )}
          <p className="mt-1 text-xs text-gray-500">The name of the database to use</p>
        </div>

        {/* Database Credentials */}
        <div className="grid grid-cols-1 sm:grid-cols-2 gap-6">
          {/* Database Username */}
          <div>
            <label htmlFor="db_username" className="block text-sm font-medium text-gray-700 mb-2">
              Database Username
            </label>
            <div className="relative">
              <input
                id="db_username"
                name="db_username"
                type="text"
                value={data.db_username}
                onChange={(e) => setData('db_username', e.target.value)}
                className={`block w-full px-3 py-2 border rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-colors ${
                  errors.db_username ? 'border-red-300' : 'border-gray-300'
                }`}
                placeholder="root"
                required
              />
            </div>
            {errors.db_username && (
              <p className="mt-1 text-sm text-red-600">{errors.db_username}</p>
            )}
          </div>

          {/* Database Password */}
          <div>
            <label htmlFor="db_password" className="block text-sm font-medium text-gray-700 mb-2">
              Database Password
            </label>
            <div className="relative">
              <input
                id="db_password"
                name="db_password"
                type={showPassword ? "text" : "password"}
                value={data.db_password}
                onChange={(e) => setData('db_password', e.target.value)}
                className={`block w-full px-3 py-2 pr-10 border rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-colors ${
                  errors.db_password ? 'border-red-300' : 'border-gray-300'
                }`}
                placeholder="Enter database password"
              />
              <button
                type="button"
                className="absolute inset-y-0 right-0 pr-3 flex items-center"
                onClick={() => setShowPassword(!showPassword)}
              >
                {showPassword ? (
                  <EyeSlashIcon className="h-4 w-4 text-gray-400" />
                ) : (
                  <EyeIcon className="h-4 w-4 text-gray-400" />
                )}
              </button>
            </div>
            {errors.db_password && (
              <p className="mt-1 text-sm text-red-600">{errors.db_password}</p>
            )}
          </div>
        </div>

        {/* Database Error */}
        {Object.keys(errors).length > 0 && (
          <div className="bg-red-50 border border-red-200 rounded-lg p-4">
            <div className="flex items-start">
              <div className="flex-shrink-0">
                <svg className="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                  <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clipRule="evenodd" />
                </svg>
              </div>
              <div className="ml-3">
                <h3 className="text-sm font-medium text-red-800">Connection Failed</h3>
                <div className="text-sm text-red-700 mt-1">
                  {Object.entries(errors).map(([field, message]) => (
                    <p key={field}>{message}</p>
                  ))}
                </div>
              </div>
            </div>
          </div>
        )}

        {/* Action Buttons */}
        <div className="flex flex-col sm:flex-row justify-between gap-4 pt-6 border-t border-gray-200">
          <Link
            href="/install"
            className="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
          >
            <ArrowLeftIcon className="h-4 w-4 mr-2" />
            Back
          </Link>
          
          <button
            type="submit"
            disabled={processing || !isFormValid}
            className={`inline-flex items-center justify-center px-6 py-2 text-sm font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors ${
              processing || !isFormValid
                ? 'text-gray-400 bg-gray-200 cursor-not-allowed'
                : 'text-white bg-blue-600 hover:bg-blue-700 focus:ring-blue-500'
            }`}
            title={!isFormValid ? 'Please fill in database name and username' : 'Test database connection and continue'}
          >
            {processing ? (
              <>
                <svg className="animate-spin -ml-1 mr-2 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24">
                  <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                  <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Testing Connection...
              </>
            ) : (
              <>
                Test & Continue
                <ArrowRightIcon className="h-4 w-4 ml-2" />
              </>
            )}
          </button>
        </div>
      </form>
    </InstallationLayout>
  );
}
