import React, { useState } from 'react';
import { Link, useForm } from '@inertiajs/react';
import { ArrowLeftIcon, ArrowRightIcon, EyeIcon, EyeSlashIcon, InformationCircleIcon } from '@heroicons/react/24/outline';
import InstallationLayout from '@/components/InstallationLayout';

export default function Admin() {
  const [showPassword, setShowPassword] = useState(false);
  const [showConfirmPassword, setShowConfirmPassword] = useState(false);
  
  const { data, setData, post, processing, errors } = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    clinic_name: '',
    clinic_phone: '',
    clinic_email: '',
    clinic_address: '',
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    post('/install/admin');
  };

  const isFormValid = data.name && data.email && data.password && data.password_confirmation && 
                     data.clinic_name && data.clinic_phone && data.clinic_email && data.clinic_address;
  
  const getPasswordStrength = (password: string) => {
    if (!password) return { strength: 0, label: '', color: '' };
    
    let strength = 0;
    if (password.length >= 8) strength++;
    if (/[a-z]/.test(password)) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^A-Za-z0-9]/.test(password)) strength++;
    
    const labels = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong'];
    const colors = ['bg-red-500', 'bg-orange-500', 'bg-yellow-500', 'bg-blue-500', 'bg-green-500'];
    
    return {
      strength,
      label: labels[strength - 1] || '',
      color: colors[strength - 1] || 'bg-gray-300'
    };
  };
  
  const passwordStrength = getPasswordStrength(data.password);

  return (
      <InstallationLayout
        title="Admin Account Setup"
        subtitle="Create your administrator account and clinic information"
        step={4}
        totalSteps={4}
      >
      <div className="text-center mb-8">
        <h2 className="text-2xl font-bold text-gray-900 mb-2">Create Admin Account</h2>
        <p className="text-gray-600">
          Set up your administrator account and clinic information. This will be your main access to the system.
        </p>
      </div>

      {/* Security Notice */}
      <div className="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
        <div className="flex items-start">
          <InformationCircleIcon className="h-5 w-5 text-yellow-500 mr-3 mt-0.5 flex-shrink-0" />
          <div>
            <h3 className="text-sm font-medium text-yellow-800">Security Information</h3>
            <div className="mt-2 text-sm text-yellow-700">
              <p>This account will have full administrative access to your MediNext system. Please choose a strong password and keep your credentials secure.</p>
            </div>
          </div>
        </div>
      </div>

      <form onSubmit={handleSubmit} className="space-y-8">
        {/* Admin Information */}
        <div className="bg-gray-50 rounded-lg p-6">
          <h3 className="text-lg font-medium text-gray-900 mb-6">Administrator Information</h3>
          
          <div className="grid grid-cols-1 gap-6 sm:grid-cols-2">
            {/* Admin Name */}
            <div>
              <label htmlFor="name" className="block text-sm font-medium text-gray-700 mb-2">
                Full Name
              </label>
              <input
                id="name"
                name="name"
                type="text"
                value={data.name}
                onChange={(e) => setData('name', e.target.value)}
                className={`block w-full px-3 py-2 border rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-colors ${
                  errors.name ? 'border-red-300' : 'border-gray-300'
                }`}
                placeholder="John Doe"
                required
              />
              {errors.name && (
                <p className="mt-1 text-sm text-red-600">{errors.name}</p>
              )}
            </div>

            {/* Admin Email */}
            <div>
              <label htmlFor="email" className="block text-sm font-medium text-gray-700 mb-2">
                Email Address
              </label>
              <input
                id="email"
                name="email"
                type="email"
                value={data.email}
                onChange={(e) => setData('email', e.target.value)}
                className={`block w-full px-3 py-2 border rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-colors ${
                  errors.email ? 'border-red-300' : 'border-gray-300'
                }`}
                placeholder="admin@clinic.com"
                required
              />
              {errors.email && (
                <p className="mt-1 text-sm text-red-600">{errors.email}</p>
              )}
            </div>
          </div>

          <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 mt-6">
            {/* Password */}
            <div>
              <label htmlFor="password" className="block text-sm font-medium text-gray-700 mb-2">
                Password
              </label>
              <div className="relative">
                <input
                  id="password"
                  name="password"
                  type={showPassword ? "text" : "password"}
                  value={data.password}
                  onChange={(e) => setData('password', e.target.value)}
                  className={`block w-full px-3 py-2 pr-10 border rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-colors ${
                    errors.password ? 'border-red-300' : 'border-gray-300'
                  }`}
                  placeholder="Enter a strong password"
                  required
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
              {errors.password && (
                <p className="mt-1 text-sm text-red-600">{errors.password}</p>
              )}
              {data.password && (
                <div className="mt-2">
                  <div className="flex items-center justify-between mb-1">
                    <span className="text-xs text-gray-600">Password strength:</span>
                    <span className={`text-xs font-medium ${
                      passwordStrength.strength <= 2 ? 'text-red-600' :
                      passwordStrength.strength === 3 ? 'text-yellow-600' :
                      'text-green-600'
                    }`}>
                      {passwordStrength.label}
                    </span>
                  </div>
                  <div className="w-full bg-gray-200 rounded-full h-2">
                    <div 
                      className={`h-2 rounded-full transition-all duration-300 ${passwordStrength.color}`}
                      style={{ width: `${(passwordStrength.strength / 5) * 100}%` }}
                    ></div>
                  </div>
                </div>
              )}
              <p className="mt-1 text-xs text-gray-500">Minimum 8 characters with letters and numbers</p>
            </div>

            {/* Password Confirmation */}
            <div>
              <label htmlFor="password_confirmation" className="block text-sm font-medium text-gray-700 mb-2">
                Confirm Password
              </label>
              <div className="relative">
                <input
                  id="password_confirmation"
                  name="password_confirmation"
                  type={showConfirmPassword ? "text" : "password"}
                  value={data.password_confirmation}
                  onChange={(e) => setData('password_confirmation', e.target.value)}
                  className={`block w-full px-3 py-2 pr-10 border rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-colors ${
                    errors.password_confirmation ? 'border-red-300' : 'border-gray-300'
                  }`}
                  placeholder="Confirm your password"
                  required
                />
                <button
                  type="button"
                  className="absolute inset-y-0 right-0 pr-3 flex items-center"
                  onClick={() => setShowConfirmPassword(!showConfirmPassword)}
                >
                  {showConfirmPassword ? (
                    <EyeSlashIcon className="h-4 w-4 text-gray-400" />
                  ) : (
                    <EyeIcon className="h-4 w-4 text-gray-400" />
                  )}
                </button>
              </div>
              {errors.password_confirmation && (
                <p className="mt-1 text-sm text-red-600">{errors.password_confirmation}</p>
              )}
              {data.password_confirmation && (
                <div className="mt-1 flex items-center">
                  {data.password === data.password_confirmation ? (
                    <>
                      <svg className="h-4 w-4 text-green-500 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                      </svg>
                      <span className="text-xs text-green-600">Passwords match</span>
                    </>
                  ) : (
                    <>
                      <svg className="h-4 w-4 text-red-500 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                      </svg>
                      <span className="text-xs text-red-600">Passwords do not match</span>
                    </>
                  )}
                </div>
              )}
            </div>
          </div>
        </div>

        {/* Clinic Information */}
        <div className="bg-gray-50 rounded-lg p-6">
          <h3 className="text-lg font-medium text-gray-900 mb-6">Clinic Information</h3>
          
          {/* Clinic Name */}
          <div className="mb-6">
            <label htmlFor="clinic_name" className="block text-sm font-medium text-gray-700 mb-2">
              Clinic Name
            </label>
            <input
              id="clinic_name"
              name="clinic_name"
              type="text"
              value={data.clinic_name}
              onChange={(e) => setData('clinic_name', e.target.value)}
              className={`block w-full px-3 py-2 border rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-colors ${
                errors.clinic_name ? 'border-red-300' : 'border-gray-300'
              }`}
              placeholder="ABC Medical Center"
              required
            />
            {errors.clinic_name && (
              <p className="mt-1 text-sm text-red-600">{errors.clinic_name}</p>
            )}
          </div>

          <div className="grid grid-cols-1 gap-6 sm:grid-cols-2">
            {/* Clinic Phone */}
            <div>
              <label htmlFor="clinic_phone" className="block text-sm font-medium text-gray-700 mb-2">
                Phone Number
              </label>
              <input
                id="clinic_phone"
                name="clinic_phone"
                type="tel"
                value={data.clinic_phone}
                onChange={(e) => setData('clinic_phone', e.target.value)}
                className={`block w-full px-3 py-2 border rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-colors ${
                  errors.clinic_phone ? 'border-red-300' : 'border-gray-300'
                }`}
                placeholder="+1 (555) 123-4567"
                required
              />
              {errors.clinic_phone && (
                <p className="mt-1 text-sm text-red-600">{errors.clinic_phone}</p>
              )}
            </div>

            {/* Clinic Email */}
            <div>
              <label htmlFor="clinic_email" className="block text-sm font-medium text-gray-700 mb-2">
                Email Address
              </label>
              <input
                id="clinic_email"
                name="clinic_email"
                type="email"
                value={data.clinic_email}
                onChange={(e) => setData('clinic_email', e.target.value)}
                className={`block w-full px-3 py-2 border rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-colors ${
                  errors.clinic_email ? 'border-red-300' : 'border-gray-300'
                }`}
                placeholder="info@clinic.com"
                required
              />
              {errors.clinic_email && (
                <p className="mt-1 text-sm text-red-600">{errors.clinic_email}</p>
              )}
            </div>
          </div>

          {/* Clinic Address */}
          <div className="mt-6">
            <label htmlFor="clinic_address" className="block text-sm font-medium text-gray-700 mb-2">
              Address
            </label>
            <textarea
              id="clinic_address"
              name="clinic_address"
              rows={3}
              value={data.clinic_address}
              onChange={(e) => setData('clinic_address', e.target.value)}
              className={`block w-full px-3 py-2 border rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-colors ${
                errors.clinic_address ? 'border-red-300' : 'border-gray-300'
              }`}
              placeholder="123 Main Street, City, State, ZIP Code"
              required
            />
            {errors.clinic_address && (
              <p className="mt-1 text-sm text-red-600">{errors.clinic_address}</p>
            )}
          </div>
        </div>

        {/* General Error */}
        {Object.keys(errors).length > 0 && (
          <div className="bg-red-50 border border-red-200 rounded-lg p-4">
            <div className="flex items-start">
              <div className="flex-shrink-0">
                <svg className="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                  <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clipRule="evenodd" />
                </svg>
              </div>
              <div className="ml-3">
                <h3 className="text-sm font-medium text-red-800">Setup Error</h3>
                <p className="text-sm text-red-700 mt-1">Please check the form for errors and try again.</p>
              </div>
            </div>
          </div>
        )}

        {/* Action Buttons */}
        <div className="flex flex-col sm:flex-row justify-between gap-4 pt-6 border-t border-gray-200">
          <Link
            href="/install/database"
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
          >
            {processing ? (
              <>
                <svg className="animate-spin -ml-1 mr-2 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24">
                  <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                  <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Installing...
              </>
            ) : (
              <>
                Complete Installation
                <ArrowRightIcon className="h-4 w-4 ml-2" />
              </>
            )}
          </button>
        </div>
      </form>
    </InstallationLayout>
  );
}
