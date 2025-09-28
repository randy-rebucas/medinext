import React, { useEffect, useState } from 'react';
import { router } from '@inertiajs/react';
import { CheckCircleIcon, ClockIcon, ExclamationTriangleIcon } from '@heroicons/react/24/outline';
import InstallationLayout from '@/components/InstallationLayout';

interface SeedingStep {
  id: string;
  name: string;
  status: 'pending' | 'running' | 'completed' | 'error';
  message?: string;
  progress?: number;
}

interface Props {
  seeding_steps: SeedingStep[];
  current_step?: string;
  is_complete?: boolean;
  error?: string;
}

export default function Seeding({ seeding_steps, current_step, is_complete, error }: Props) {
  const [steps, setSteps] = useState<SeedingStep[]>(seeding_steps || []);
  const [isPolling, setIsPolling] = useState(!is_complete && !error);

  // Poll for progress updates
  useEffect(() => {
    // Start seeding process if not already started
    if (!seeding_steps.some(step => step.status === 'completed' || step.status === 'running') && !error) {
      router.post('/install/seeding/start');
    }
  }, []);

  useEffect(() => {
    if (!isPolling) return;

    const interval = setInterval(() => {
      router.reload({
        only: ['seeding_steps', 'current_step', 'is_complete', 'error'],
      });
    }, 1000); // Poll every second

    return () => clearInterval(interval);
  }, [isPolling]);

  // Stop polling when complete or error
  useEffect(() => {
    if (is_complete || error) {
      setIsPolling(false);
    }
  }, [is_complete, error]);

  // Update steps when props change
  useEffect(() => {
    if (seeding_steps) {
      setSteps(seeding_steps);
    }
  }, [seeding_steps]);

  const getStepIcon = (step: SeedingStep) => {
    switch (step.status) {
      case 'completed':
        return <CheckCircleIcon className="h-5 w-5 text-green-500" />;
      case 'running':
        return (
          <div className="relative">
            <ClockIcon className="h-5 w-5 text-blue-500" />
            <div className="absolute inset-0 animate-spin">
              <ClockIcon className="h-5 w-5 text-blue-500" />
            </div>
          </div>
        );
      case 'error':
        return <ExclamationTriangleIcon className="h-5 w-5 text-red-500" />;
      default:
        return <div className="h-5 w-5 rounded-full border-2 border-gray-300" />;
    }
  };

  const getStepStatusColor = (step: SeedingStep) => {
    switch (step.status) {
      case 'completed':
        return 'bg-green-50 border-green-200';
      case 'running':
        return 'bg-blue-50 border-blue-200';
      case 'error':
        return 'bg-red-50 border-red-200';
      default:
        return 'bg-gray-50 border-gray-200';
    }
  };

  const completedSteps = steps.filter(step => step.status === 'completed').length;
  const totalSteps = steps.length;
  const progressPercentage = totalSteps > 0 ? (completedSteps / totalSteps) * 100 : 0;

  return (
    <InstallationLayout 
      title="Setting Up System" 
      subtitle="Initializing core system data"
      step={2.5}
      totalSteps={4}
    >
      <div className="text-center mb-8">
        <h2 className="text-2xl font-bold text-gray-900 mb-2">Setting Up Your System</h2>
        <p className="text-gray-600">
          We're setting up the core system data. This may take a few moments...
        </p>
      </div>

      {/* Overall Progress */}
      <div className="mb-8">
        <div className="flex items-center justify-between mb-4">
          <h3 className="text-lg font-medium text-gray-900">Installation Progress</h3>
          <span className="text-sm text-gray-500">
            {completedSteps} of {totalSteps} steps completed
          </span>
        </div>
        <div className="w-full bg-gray-200 rounded-full h-3">
          <div 
            className="bg-blue-600 h-3 rounded-full transition-all duration-500 ease-out"
            style={{ width: `${progressPercentage}%` }}
          ></div>
        </div>
        <p className="text-sm text-gray-600 mt-2">
          {Math.round(progressPercentage)}% Complete
        </p>
      </div>

      {/* Error Display */}
      {error && (
        <div className="mb-8 p-4 bg-red-50 border border-red-200 rounded-lg">
          <div className="flex items-start">
            <ExclamationTriangleIcon className="h-5 w-5 text-red-500 mr-3 mt-0.5 flex-shrink-0" />
            <div>
              <h3 className="text-sm font-medium text-red-800">Installation Error</h3>
              <p className="text-sm text-red-700 mt-1">{error}</p>
            </div>
          </div>
        </div>
      )}

      {/* Seeding Steps */}
      <div className="space-y-3">
        {steps.map((step, index) => (
          <div 
            key={step.id} 
            className={`flex items-center p-4 rounded-lg border transition-all duration-300 ${getStepStatusColor(step)}`}
          >
            <div className="flex-shrink-0 mr-4">
              {getStepIcon(step)}
            </div>
            <div className="flex-1">
              <div className="flex items-center justify-between">
                <h4 className="text-sm font-medium text-gray-900">{step.name}</h4>
                <span className="text-xs text-gray-500">
                  {step.status === 'running' && step.progress ? `${step.progress}%` : ''}
                </span>
              </div>
              {step.message && (
                <p className="text-xs text-gray-600 mt-1">{step.message}</p>
              )}
              {step.status === 'running' && step.progress && (
                <div className="mt-2 w-full bg-gray-200 rounded-full h-1">
                  <div 
                    className="bg-blue-600 h-1 rounded-full transition-all duration-300"
                    style={{ width: `${step.progress}%` }}
                  ></div>
                </div>
              )}
            </div>
          </div>
        ))}
      </div>

      {/* Current Step Indicator */}
      {current_step && !is_complete && !error && (
        <div className="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
          <div className="flex items-center">
            <div className="animate-spin rounded-full h-4 w-4 border-b-2 border-blue-600 mr-3"></div>
            <span className="text-sm text-blue-800">
              Currently: {steps.find(s => s.id === current_step)?.name || 'Processing...'}
            </span>
          </div>
        </div>
      )}

      {/* Completion Message */}
      {is_complete && (
        <div className="mt-8 p-6 bg-green-50 border border-green-200 rounded-lg">
          <div className="flex items-center">
            <CheckCircleIcon className="h-6 w-6 text-green-500 mr-3" />
            <div>
              <h3 className="text-lg font-medium text-green-800">System Setup Complete!</h3>
              <p className="text-sm text-green-700 mt-1">
                All core system data has been successfully initialized.
              </p>
            </div>
          </div>
        </div>
      )}

      {/* Action Buttons */}
      <div className="mt-8 flex justify-center">
        {is_complete ? (
          <button
            onClick={() => router.post('/install/seeding/complete')}
            className="inline-flex items-center px-6 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
          >
            Continue to Admin Setup
            <svg className="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
            </svg>
          </button>
        ) : error ? (
          <button
            onClick={() => router.post('/install/seeding/retry')}
            className="inline-flex items-center px-6 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors"
          >
            Retry Installation
            <svg className="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
          </button>
        ) : !seeding_steps.some(step => step.status === 'completed' || step.status === 'running') ? (
          <button
            onClick={() => router.post('/install/seeding/start')}
            className="inline-flex items-center px-6 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
          >
            Start Installation
            <svg className="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1m4 0h1m-6 4h1m4 0h1m6-7a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </button>
        ) : (
          <div className="text-center">
            <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
            <p className="text-sm text-gray-600 mt-2">Please wait while we set up your system...</p>
          </div>
        )}
      </div>
    </InstallationLayout>
  );
}
