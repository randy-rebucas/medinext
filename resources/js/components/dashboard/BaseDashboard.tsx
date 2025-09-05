import React from 'react';
import { Badge } from '@/components/ui/badge';
import {
  useAuth,
  getUserDisplayName,
  getRoleColorScheme,
  isDoctor,
  isPatient,
  isReceptionist,
  isMedRep,
  isAdmin,
  isSuperAdmin,
  hasPermission
} from '@/utils/permissions';
import {
  Calendar,
  Users,
  FileText,
  Pill,
  Stethoscope,
  Bell,
  Search,
  BarChart3,
  ClipboardList,
  MessageSquare,
  Microscope,
  Home,
  User,
  Settings,
  LogOut,
  UserCheck,
  DollarSign
} from 'lucide-react';
import { Link } from '@inertiajs/react';

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
}

interface BaseDashboardProps {
  stats: DashboardStats;
  children?: React.ReactNode;
}

export default function BaseDashboard({ stats, children }: BaseDashboardProps) {
  const user = useAuth();
  const colorScheme = getRoleColorScheme(user?.primary_role);
  const displayName = getUserDisplayName(user);

  const getNavigationItems = () => {
    const baseItems = [
      {
        name: 'Dashboard',
        href: '/dashboard',
        icon: Home,
        current: true,
        color: 'text-blue-600',
        bgColor: 'bg-blue-50 dark:bg-blue-900/20',
        permission: null
      }
    ];

    if (isDoctor(user) || isAdmin(user) || isSuperAdmin(user)) {
      return [
        ...baseItems,
        {
          name: 'Patients',
          href: '/doctor/patients',
          icon: Users,
          current: false,
          color: 'text-emerald-600',
          bgColor: 'bg-emerald-50 dark:bg-emerald-900/20',
          permission: 'patient.read'
        },
        {
          name: 'Appointments',
          href: '/doctor/appointments',
          icon: Calendar,
          current: false,
          color: 'text-purple-600',
          bgColor: 'bg-purple-50 dark:bg-purple-900/20',
          permission: 'schedule.view'
        },
        {
          name: 'Medical Records',
          href: '/doctor/records',
          icon: FileText,
          current: false,
          color: 'text-orange-600',
          bgColor: 'bg-orange-50 dark:bg-orange-900/20',
          permission: 'emr.read'
        },
        {
          name: 'Prescriptions',
          href: '/doctor/prescriptions',
          icon: Pill,
          current: false,
          color: 'text-cyan-600',
          bgColor: 'bg-cyan-50 dark:bg-cyan-900/20',
          permission: 'rx.view'
        },
        {
          name: 'Lab Results',
          href: '/doctor/lab-results',
          icon: Microscope,
          current: false,
          color: 'text-indigo-600',
          bgColor: 'bg-indigo-50 dark:bg-indigo-900/20',
          permission: 'lab_results.view'
        },
        {
          name: 'Reports',
          href: '/doctor/reports',
          icon: BarChart3,
          current: false,
          color: 'text-pink-600',
          bgColor: 'bg-pink-50 dark:bg-pink-900/20',
          permission: 'reports.view'
        },
        {
          name: 'Med Samples',
          href: '/doctor/med-samples',
          icon: ClipboardList,
          current: false,
          color: 'text-yellow-600',
          bgColor: 'bg-yellow-50 dark:bg-yellow-900/20',
          permission: 'medrep.view'
        },
        {
          name: 'Messages',
          href: '/doctor/messages',
          icon: MessageSquare,
          current: false,
          color: 'text-green-600',
          bgColor: 'bg-green-50 dark:bg-green-900/20',
          permission: 'messages.view'
        },
        {
          name: 'Settings',
          href: '/doctor/settings',
          icon: Settings,
          current: false,
          color: 'text-gray-600',
          bgColor: 'bg-gray-50 dark:bg-gray-900/20',
          permission: 'settings.view'
        }
      ];
    }

    if (isPatient(user)) {
      return [
        ...baseItems,
        {
          name: 'Appointments',
          href: '/appointments',
          icon: Calendar,
          current: false,
          color: 'text-purple-600',
          bgColor: 'bg-purple-50 dark:bg-purple-900/20',
          permission: 'appointments.view'
        },
        {
          name: 'Prescriptions',
          href: '/prescriptions',
          icon: Pill,
          current: false,
          color: 'text-cyan-600',
          bgColor: 'bg-cyan-50 dark:bg-cyan-900/20',
          permission: 'prescriptions.view'
        },
        {
          name: 'Medical Records',
          href: '/records',
          icon: FileText,
          current: false,
          color: 'text-orange-600',
          bgColor: 'bg-orange-50 dark:bg-orange-900/20',
          permission: 'medical_records.view'
        },
        {
          name: 'Lab Results',
          href: '/lab-results',
          icon: Microscope,
          current: false,
          color: 'text-indigo-600',
          bgColor: 'bg-indigo-50 dark:bg-indigo-900/20',
          permission: 'lab_results.view'
        },
        {
          name: 'Messages',
          href: '/messages',
          icon: MessageSquare,
          current: false,
          color: 'text-green-600',
          bgColor: 'bg-green-50 dark:bg-green-900/20',
          permission: 'messages.view'
        },
        {
          name: 'Settings',
          href: '/settings',
          icon: Settings,
          current: false,
          color: 'text-gray-600',
          bgColor: 'bg-gray-50 dark:bg-gray-900/20',
          permission: 'profile.edit'
        }
      ];
    }

    if (isReceptionist(user)) {
      return [
        ...baseItems,
        {
          name: 'Check-in',
          href: '/checkin',
          icon: UserCheck,
          current: false,
          color: 'text-emerald-600',
          bgColor: 'bg-emerald-50 dark:bg-emerald-900/20',
          permission: 'appointments.checkin'
        },
        {
          name: 'Appointments',
          href: '/appointments/create',
          icon: Calendar,
          current: false,
          color: 'text-purple-600',
          bgColor: 'bg-purple-50 dark:bg-purple-900/20',
          permission: 'appointments.create'
        },
        {
          name: 'Patients',
          href: '/patients/create',
          icon: Users,
          current: false,
          color: 'text-orange-600',
          bgColor: 'bg-orange-50 dark:bg-orange-900/20',
          permission: 'patients.create'
        },
        {
          name: 'Billing',
          href: '/billing',
          icon: DollarSign,
          current: false,
          color: 'text-cyan-600',
          bgColor: 'bg-cyan-50 dark:bg-cyan-900/20',
          permission: 'billing.view'
        },
        {
          name: 'Reports',
          href: '/reports',
          icon: BarChart3,
          current: false,
          color: 'text-pink-600',
          bgColor: 'bg-pink-50 dark:bg-pink-900/20',
          permission: 'reports.view'
        },
        {
          name: 'Settings',
          href: '/settings',
          icon: Settings,
          current: false,
          color: 'text-gray-600',
          bgColor: 'bg-gray-50 dark:bg-gray-900/20',
          permission: 'settings.view'
        }
      ];
    }

    if (isMedRep(user)) {
      return [
        ...baseItems,
        {
          name: 'Schedule',
          href: '/medrep/schedule',
          icon: Calendar,
          current: false,
          color: 'text-purple-600',
          bgColor: 'bg-purple-50 dark:bg-purple-900/20',
          permission: 'medrep.schedule'
        },
        {
          name: 'Upload',
          href: '/medrep/upload',
          icon: FileText,
          current: false,
          color: 'text-orange-600',
          bgColor: 'bg-orange-50 dark:bg-orange-900/20',
          permission: 'medrep.upload'
        },
        {
          name: 'Reports',
          href: '/reports',
          icon: BarChart3,
          current: false,
          color: 'text-pink-600',
          bgColor: 'bg-pink-50 dark:bg-pink-900/20',
          permission: 'reports.view'
        },
        {
          name: 'Settings',
          href: '/settings',
          icon: Settings,
          current: false,
          color: 'text-gray-600',
          bgColor: 'bg-gray-50 dark:bg-gray-900/20',
          permission: 'settings.view'
        }
      ];
    }

    if (isAdmin(user) || isSuperAdmin(user)) {
      return [
        ...baseItems,
        {
          name: 'Users',
          href: '/admin/users',
          icon: Users,
          current: false,
          color: 'text-emerald-600',
          bgColor: 'bg-emerald-50 dark:bg-emerald-900/20',
          permission: 'users.manage'
        },
        {
          name: 'Settings',
          href: '/admin/settings',
          icon: Settings,
          current: false,
          color: 'text-gray-600',
          bgColor: 'bg-gray-50 dark:bg-gray-900/20',
          permission: 'settings.manage'
        }
      ];
    }

    return baseItems;
  };

  const navigationItems = getNavigationItems().filter(item =>
    !item.permission || hasPermission(user, item.permission)
  );

  return (
    <div className="min-h-screen bg-gradient-to-br from-slate-50 via-white to-blue-50 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900">
      {/* Sidebar */}
      <div className="fixed inset-y-0 left-0 z-50 w-64 bg-white dark:bg-slate-900 shadow-2xl">
        <div className="flex flex-col h-full">
          {/* Logo and Header */}
          <div className="flex items-center justify-between p-6 border-b border-slate-200 dark:border-slate-700">
            <div className="flex items-center space-x-3">
              <div className={`p-2 bg-gradient-to-r ${colorScheme.primary} rounded-xl`}>
                <Stethoscope className="h-6 w-6 text-white" />
              </div>
              <div>
                <h1 className="text-lg font-bold text-slate-900 dark:text-white">MediNext</h1>
                <p className="text-xs text-slate-600 dark:text-slate-400 capitalize">
                  {user?.primary_role || 'User'} Portal
                </p>
              </div>
            </div>
          </div>

          {/* User Profile */}
          <div className="p-6 border-b border-slate-200 dark:border-slate-700">
            <div className="flex items-center space-x-3">
              <div className={`p-2 bg-gradient-to-r ${colorScheme.secondary} rounded-full`}>
                <User className="h-5 w-5 text-white" />
              </div>
              <div>
                <p className="text-sm font-semibold text-slate-900 dark:text-white">{displayName}</p>
                <p className="text-xs text-slate-600 dark:text-slate-400">{user?.email}</p>
                <Badge className={`mt-1 bg-${colorScheme.accent}-100 text-${colorScheme.accent}-800 dark:bg-${colorScheme.accent}-900 dark:text-${colorScheme.accent}-200 border-0 text-xs`}>
                  {user?.primary_role || 'User'}
                </Badge>
              </div>
            </div>
          </div>

          {/* Navigation */}
          <nav className="flex-1 p-4 space-y-2 overflow-y-auto">
            {navigationItems.map((item) => (
              <Link
                key={item.name}
                href={item.href}
                className={`flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-200 group ${
                  item.current
                    ? `${item.bgColor} ${item.color} shadow-md`
                    : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white'
                }`}
              >
                <item.icon className={`h-5 w-5 ${item.current ? item.color : 'group-hover:text-slate-900 dark:group-hover:text-white'}`} />
                <span className="text-sm font-medium">{item.name}</span>
              </Link>
            ))}
          </nav>

          {/* Footer */}
          <div className="p-4 border-t border-slate-200 dark:border-slate-700">
            <Link
              href="/logout"
              className="flex items-center space-x-3 px-4 py-3 text-slate-600 dark:text-slate-400 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-600 dark:hover:text-red-400 rounded-xl transition-all duration-200"
            >
              <LogOut className="h-5 w-5" />
              <span className="text-sm font-medium">Sign Out</span>
            </Link>
          </div>
        </div>
      </div>

      {/* Main Content */}
      <div className="lg:pl-64">
        {/* Top Navigation */}
        <div className="sticky top-0 z-30 bg-white/80 dark:bg-slate-900/80 backdrop-blur-md border-b border-slate-200 dark:border-slate-700">
          <div className="flex items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
            <div className="flex items-center space-x-4">
              <div>
                <h1 className="text-2xl font-bold text-slate-900 dark:text-white">Dashboard</h1>
                <p className="text-sm text-slate-600 dark:text-slate-400">Welcome back, {displayName}</p>
              </div>
            </div>

            <div className="flex items-center space-x-4">
              {/* Search */}
              <div className="hidden md:block relative">
                <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-slate-400" />
                <input
                  type="text"
                  placeholder="Search..."
                  className="pl-10 pr-4 py-2 border border-slate-200 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-500 dark:placeholder-slate-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                />
              </div>

              {/* Notifications */}
              <div className="relative group">
                <button className="relative p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800">
                  <Bell className="h-5 w-5 text-slate-600 dark:text-slate-400" />
                  {stats.newMessages && stats.newMessages > 0 && (
                    <span className="absolute -top-1 -right-1 h-4 w-4 bg-red-500 text-white text-xs rounded-full flex items-center justify-center">
                      {stats.newMessages}
                    </span>
                  )}
                </button>
              </div>

              {/* User Menu */}
              <div className="relative group">
                <button className="flex items-center space-x-2 p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800">
                  <div className={`w-8 h-8 bg-gradient-to-r ${colorScheme.secondary} rounded-full flex items-center justify-center`}>
                    <User className="h-4 w-4 text-white" />
                  </div>
                  <span className="hidden sm:block text-sm font-medium text-slate-700 dark:text-slate-300">{displayName}</span>
                </button>
              </div>
            </div>
          </div>
        </div>

        {/* Dashboard Content */}
        <div className="p-4 sm:p-6 lg:p-8">
          {children}
        </div>
      </div>
    </div>
  );
}
