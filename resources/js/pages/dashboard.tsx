import React from 'react';
import { Head } from '@inertiajs/react';
import { useAuth, isSuperAdmin, isAdmin, isDoctor, isPatient, isReceptionist, isMedRep } from '@/utils/permissions';
import BaseDashboard from '@/components/dashboard/BaseDashboard';
import DoctorDashboard from '@/components/dashboard/DoctorDashboard';
import PatientDashboard from '@/components/dashboard/PatientDashboard';
import ReceptionistDashboard from '@/components/dashboard/ReceptionistDashboard';

interface DashboardStats {
  todayAppointments?: number;
  upcomingAppointments?: number;
  activePatients?: number;
  pendingPrescriptions?: number;
  recentMedsSamples?: number;
  urgentTasks?: number;
  totalPatients?: number;
  completedAppointments?: number;
  pendingLabResults?: number;
  newMessages?: number;
  checkedInPatients?: number;
  pendingCheckIns?: number;
  billingTasks?: number;
  newRegistrations?: number;
  activePrescriptions?: number;
  totalVisits?: number;
}

interface DashboardProps {
  stats?: DashboardStats;
}

export default function Dashboard({ stats = {} }: DashboardProps) {
  const user = useAuth();

  // Handle case where user is not authenticated
  if (!user) {
    return (
      <div className="min-h-screen bg-gradient-to-br from-slate-50 via-white to-blue-50 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900 flex items-center justify-center">
        <div className="text-center">
          <h2 className="text-2xl font-bold text-slate-900 dark:text-white mb-4">
            Please log in to access your dashboard
          </h2>
          <p className="text-slate-600 dark:text-slate-400">
            You need to be authenticated to view this page.
          </p>
        </div>
      </div>
    );
  }

  // Default stats for different roles
  const getDefaultStats = (): DashboardStats => {
    if (isDoctor(user)) {
      return {
        todayAppointments: 8,
        upcomingAppointments: 24,
        activePatients: 156,
        pendingPrescriptions: 12,
        recentMedsSamples: 5,
        urgentTasks: 3,
        ...stats
      };
    }

    if (isPatient(user)) {
      return {
        upcomingAppointments: 2,
        completedAppointments: 8,
        activePrescriptions: 3,
        pendingLabResults: 1,
        newMessages: 2,
        totalVisits: 24,
        ...stats
      };
    }

    if (isReceptionist(user)) {
      return {
        todayAppointments: 15,
        checkedInPatients: 8,
        pendingCheckIns: 7,
        totalPatients: 1200,
        billingTasks: 5,
        newRegistrations: 12,
        ...stats
      };
    }

    if (isAdmin(user) || isSuperAdmin(user)) {
      return {
        todayAppointments: 25,
        upcomingAppointments: 45,
        activePatients: 500,
        pendingPrescriptions: 18,
        recentMedsSamples: 8,
        urgentTasks: 5,
        totalPatients: 1200,
        ...stats
      };
    }

    return stats;
  };

  const currentStats = getDefaultStats();

  const renderRoleSpecificDashboard = () => {
    if (isDoctor(user)) {
      return <DoctorDashboard stats={currentStats} />;
    }

    if (isPatient(user)) {
      return <PatientDashboard stats={currentStats} />;
    }

    if (isReceptionist(user)) {
      return <ReceptionistDashboard stats={currentStats} />;
    }

    if (isAdmin(user) || isSuperAdmin(user)) {
      return <DoctorDashboard stats={currentStats} />; // Admin can use doctor dashboard
    }

    if (isMedRep(user)) {
      // MedRep dashboard would go here
      return (
        <div className="text-center py-12">
          <h2 className="text-2xl font-bold text-slate-900 dark:text-white mb-4">
            Medical Representative Dashboard
          </h2>
          <p className="text-slate-600 dark:text-slate-400">
            MedRep dashboard coming soon...
          </p>
        </div>
      );
    }

    // Default fallback for users without roles or unrecognized roles
    return (
      <div className="text-center py-12">
        <div className="max-w-md mx-auto">
          <div className="p-6 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-2xl border border-blue-200 dark:border-blue-800">
            <div className="p-4 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-2xl mb-4 inline-block">
              <svg className="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
              </svg>
            </div>
            <h2 className="text-2xl font-bold text-slate-900 dark:text-white mb-2">
              Welcome to MediNext
            </h2>
            <p className="text-slate-600 dark:text-slate-400 mb-4">
              Your personalized dashboard is being prepared. Please contact your administrator to assign your role.
            </p>
            <div className="text-sm text-slate-500 dark:text-slate-400">
              Current user: {user?.name || 'Unknown'}
            </div>
          </div>
        </div>
      </div>
    );
  };

  return (
    <>
      <Head title="Dashboard" />
      <BaseDashboard stats={currentStats}>
        {renderRoleSpecificDashboard()}
      </BaseDashboard>
    </>
  );
}
