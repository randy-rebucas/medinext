import React from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
  Calendar,
  FileText,
  Pill,
  CheckCircle,
  Activity,
  Heart,
  Zap,
  Download,
  User
} from 'lucide-react';
import { Link } from '@inertiajs/react';
import { useAuth, hasPermission } from '@/utils/permissions';

interface DashboardStats {
  upcomingAppointments?: number;
  completedAppointments?: number;
  activePrescriptions?: number;
  pendingLabResults?: number;
  newMessages?: number;
  totalVisits?: number;
}

interface PatientDashboardProps {
  stats: DashboardStats;
}

export default function PatientDashboard({ stats }: PatientDashboardProps) {
  const user = useAuth();

  const quickActions = [
    {
      title: 'Book Appointment',
      description: 'Schedule a new appointment with your doctor',
      icon: Calendar,
      href: '/appointments/book',
      gradient: 'from-blue-500 to-blue-600',
      hoverGradient: 'from-blue-600 to-blue-700',
      permission: 'appointments.create'
    },
    {
      title: 'View Prescriptions',
      description: 'Check your current medications and prescriptions',
      icon: Pill,
      href: '/prescriptions',
      gradient: 'from-emerald-500 to-emerald-600',
      hoverGradient: 'from-emerald-600 to-emerald-700',
      permission: 'prescriptions.view'
    },
    {
      title: 'Medical Records',
      description: 'Access your medical history and records',
      icon: FileText,
      href: '/records',
      gradient: 'from-purple-500 to-purple-600',
      hoverGradient: 'from-purple-600 to-purple-700',
      permission: 'medical_records.view'
    },
    {
      title: 'Lab Results',
      description: 'View your latest lab test results',
      icon: Activity,
      href: '/lab-results',
      gradient: 'from-orange-500 to-orange-600',
      hoverGradient: 'from-orange-600 to-orange-700',
      permission: 'lab_results.view'
    },
  ];

    const filteredQuickActions = quickActions.filter(action =>
    hasPermission(user, action.permission)
  );

  return (
    <>
      {/* Welcome Section */}
      <div className="mb-8">
        <Card className="border-0 shadow-xl bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20">
          <CardContent className="p-8">
            <div className="flex items-center space-x-4">
              <div className="p-4 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-2xl">
                <Heart className="h-8 w-8 text-white" />
              </div>
              <div>
                <h1 className="text-3xl font-bold text-slate-900 dark:text-white mb-2">Welcome to Your Health Portal</h1>
                <p className="text-slate-600 dark:text-slate-400 text-lg">
                  Manage your appointments, view your medical records, and stay connected with your healthcare team.
                </p>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      {/* Stats Overview */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-6 mb-8">
        <Card className="relative overflow-hidden border-0 shadow-lg hover:shadow-xl transition-all duration-300 bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20">
          <div className="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-blue-500/10 to-blue-600/10 rounded-full -translate-y-10 translate-x-10"></div>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2 relative z-10">
            <CardTitle className="text-sm font-medium text-slate-700 dark:text-slate-300">Upcoming Appointments</CardTitle>
            <div className="p-2 bg-blue-500 rounded-lg shadow-md">
              <Calendar className="h-4 w-4 text-white" />
            </div>
          </CardHeader>
          <CardContent className="relative z-10">
            <div className="text-3xl font-bold text-blue-600 dark:text-blue-400">{stats.upcomingAppointments || 0}</div>
            <p className="text-xs text-slate-600 dark:text-slate-400 mt-1">
              Scheduled visits
            </p>
          </CardContent>
        </Card>

        <Card className="relative overflow-hidden border-0 shadow-lg hover:shadow-xl transition-all duration-300 bg-gradient-to-br from-emerald-50 to-emerald-100 dark:from-emerald-900/20 dark:to-emerald-800/20">
          <div className="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-emerald-500/10 to-emerald-600/10 rounded-full -translate-y-10 translate-x-10"></div>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2 relative z-10">
            <CardTitle className="text-sm font-medium text-slate-700 dark:text-slate-300">Completed Visits</CardTitle>
            <div className="p-2 bg-emerald-500 rounded-lg shadow-md">
              <CheckCircle className="h-4 w-4 text-white" />
            </div>
          </CardHeader>
          <CardContent className="relative z-10">
            <div className="text-3xl font-bold text-emerald-600 dark:text-emerald-400">{stats.completedAppointments || 0}</div>
            <p className="text-xs text-slate-600 dark:text-slate-400 mt-1">
              This month
            </p>
          </CardContent>
        </Card>

        <Card className="relative overflow-hidden border-0 shadow-lg hover:shadow-xl transition-all duration-300 bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20">
          <div className="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-purple-500/10 to-purple-600/10 rounded-full -translate-y-10 translate-x-10"></div>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2 relative z-10">
            <CardTitle className="text-sm font-medium text-slate-700 dark:text-slate-300">Active Prescriptions</CardTitle>
            <div className="p-2 bg-purple-500 rounded-lg shadow-md">
              <Pill className="h-4 w-4 text-white" />
            </div>
          </CardHeader>
          <CardContent className="relative z-10">
            <div className="text-3xl font-bold text-purple-600 dark:text-purple-400">{stats.activePrescriptions || 0}</div>
            <p className="text-xs text-slate-600 dark:text-slate-400 mt-1">
              Current medications
            </p>
          </CardContent>
        </Card>

        <Card className="relative overflow-hidden border-0 shadow-lg hover:shadow-xl transition-all duration-300 bg-gradient-to-br from-orange-50 to-orange-100 dark:from-orange-900/20 dark:to-orange-800/20">
          <div className="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-orange-500/10 to-orange-600/10 rounded-full -translate-y-10 translate-x-10"></div>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2 relative z-10">
            <CardTitle className="text-sm font-medium text-slate-700 dark:text-slate-300">Lab Results</CardTitle>
            <div className="p-2 bg-orange-500 rounded-lg shadow-md">
              <Activity className="h-4 w-4 text-white" />
            </div>
          </CardHeader>
          <CardContent className="relative z-10">
            <div className="text-3xl font-bold text-orange-600 dark:text-orange-400">{stats.pendingLabResults || 0}</div>
            <p className="text-xs text-slate-600 dark:text-slate-400 mt-1">
              Available now
            </p>
          </CardContent>
        </Card>

        <Card className="relative overflow-hidden border-0 shadow-lg hover:shadow-xl transition-all duration-300 bg-gradient-to-br from-cyan-50 to-cyan-100 dark:from-cyan-900/20 dark:to-cyan-800/20">
          <div className="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-cyan-500/10 to-cyan-600/10 rounded-full -translate-y-10 translate-x-10"></div>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2 relative z-10">
            <CardTitle className="text-sm font-medium text-slate-700 dark:text-slate-300">New Messages</CardTitle>
            <div className="p-2 bg-cyan-500 rounded-lg shadow-md">
              <FileText className="h-4 w-4 text-white" />
            </div>
          </CardHeader>
          <CardContent className="relative z-10">
            <div className="text-3xl font-bold text-cyan-600 dark:text-cyan-400">{stats.newMessages || 0}</div>
            <p className="text-xs text-slate-600 dark:text-slate-400 mt-1">
              From your doctor
            </p>
          </CardContent>
        </Card>

        <Card className="relative overflow-hidden border-0 shadow-lg hover:shadow-xl transition-all duration-300 bg-gradient-to-br from-indigo-50 to-indigo-100 dark:from-indigo-900/20 dark:to-indigo-800/20">
          <div className="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-indigo-500/10 to-indigo-600/10 rounded-full -translate-y-10 translate-x-10"></div>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2 relative z-10">
            <CardTitle className="text-sm font-medium text-slate-700 dark:text-slate-300">Total Visits</CardTitle>
            <div className="p-2 bg-indigo-500 rounded-lg shadow-md">
              <User className="h-4 w-4 text-white" />
            </div>
          </CardHeader>
          <CardContent className="relative z-10">
            <div className="text-3xl font-bold text-indigo-600 dark:text-indigo-400">{stats.totalVisits || 0}</div>
            <p className="text-xs text-slate-600 dark:text-slate-400 mt-1">
              All time
            </p>
          </CardContent>
        </Card>
      </div>

      {/* Quick Actions */}
      <div className="mb-8">
        <div className="flex items-center space-x-3 mb-6">
          <div className="p-2 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-lg">
            <Zap className="h-5 w-5 text-white" />
          </div>
          <h2 className="text-2xl font-bold bg-gradient-to-r from-slate-900 to-slate-700 dark:from-white dark:to-slate-300 bg-clip-text text-transparent">
            Quick Actions
          </h2>
        </div>
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          {filteredQuickActions.map((action, index) => (
            <Link key={index} href={action.href}>
              <Card className="group cursor-pointer border-0 shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1 bg-white dark:bg-slate-800">
                <CardHeader className="pb-4">
                  <div className="flex items-center space-x-4">
                    <div className={`p-3 rounded-xl bg-gradient-to-r ${action.gradient} group-hover:bg-gradient-to-r ${action.hoverGradient} transition-all duration-300 shadow-lg group-hover:shadow-xl`}>
                      <action.icon className="h-6 w-6 text-white" />
                    </div>
                    <div className="flex-1">
                      <CardTitle className="text-base font-semibold text-slate-900 dark:text-white group-hover:text-slate-700 dark:group-hover:text-slate-200 transition-colors">
                        {action.title}
                      </CardTitle>
                      <CardDescription className="text-sm text-slate-600 dark:text-slate-400 mt-1">
                        {action.description}
                      </CardDescription>
                    </div>
                  </div>
                </CardHeader>
                <CardContent className="pt-0">
                  <Button
                    variant="outline"
                    size="sm"
                    className="w-full border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 group-hover:border-slate-300 dark:group-hover:border-slate-600 transition-all duration-300"
                  >
                    <span className="group-hover:translate-x-1 transition-transform duration-300">Open</span>
                  </Button>
                </CardContent>
              </Card>
            </Link>
          ))}
        </div>
      </div>

      {/* Recent Activity & Upcoming Appointments */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <Card className="border-0 shadow-xl bg-white dark:bg-slate-800">
          <CardHeader className="pb-4">
            <div className="flex items-center space-x-3">
              <div className="p-2 bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg">
                <Calendar className="h-5 w-5 text-white" />
              </div>
              <div>
                <CardTitle className="text-lg font-semibold text-slate-900 dark:text-white">Upcoming Appointments</CardTitle>
                <CardDescription className="text-slate-600 dark:text-slate-400">
                  Your scheduled visits and appointments
                </CardDescription>
              </div>
            </div>
          </CardHeader>
          <CardContent>
            <div className="space-y-6">
              <div className="flex items-center space-x-4 p-4 rounded-xl bg-gradient-to-r from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 border border-blue-200 dark:border-blue-800">
                <div className="p-3 bg-blue-500 rounded-xl shadow-md">
                  <Calendar className="h-5 w-5 text-white" />
                </div>
                <div className="flex-1">
                  <p className="text-sm font-semibold text-slate-900 dark:text-white">Dr. Smith - General Checkup</p>
                  <p className="text-xs text-slate-600 dark:text-slate-400 mt-1">Tomorrow at 2:00 PM</p>
                </div>
                <Badge className="bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 border-0">Confirmed</Badge>
              </div>

              <div className="flex items-center space-x-4 p-4 rounded-xl bg-gradient-to-r from-emerald-50 to-emerald-100 dark:from-emerald-900/20 dark:to-emerald-800/20 border border-emerald-200 dark:border-emerald-800">
                <div className="p-3 bg-emerald-500 rounded-xl shadow-md">
                  <Activity className="h-5 w-5 text-white" />
                </div>
                <div className="flex-1">
                  <p className="text-sm font-semibold text-slate-900 dark:text-white">Lab Results Review</p>
                  <p className="text-xs text-slate-600 dark:text-slate-400 mt-1">Friday at 10:00 AM</p>
                </div>
                <Badge className="bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-200 border-0">Scheduled</Badge>
              </div>

              <div className="flex items-center space-x-4 p-4 rounded-xl bg-gradient-to-r from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20 border border-purple-200 dark:border-purple-800">
                <div className="p-3 bg-purple-500 rounded-xl shadow-md">
                  <Pill className="h-5 w-5 text-white" />
                </div>
                <div className="flex-1">
                  <p className="text-sm font-semibold text-slate-900 dark:text-white">Prescription Refill</p>
                  <p className="text-xs text-slate-600 dark:text-slate-400 mt-1">Next week</p>
                </div>
                <Badge className="bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200 border-0">Reminder</Badge>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card className="border-0 shadow-xl bg-white dark:bg-slate-800">
          <CardHeader className="pb-4">
            <div className="flex items-center space-x-3">
              <div className="p-2 bg-gradient-to-r from-emerald-500 to-emerald-600 rounded-lg">
                <Activity className="h-5 w-5 text-white" />
              </div>
              <div>
                <CardTitle className="text-lg font-semibold text-slate-900 dark:text-white">Health Summary</CardTitle>
                <CardDescription className="text-slate-600 dark:text-slate-400">
                  Your recent health activities and updates
                </CardDescription>
              </div>
            </div>
          </CardHeader>
          <CardContent>
            <div className="space-y-6">
              <div className="flex items-center space-x-4 p-4 rounded-xl bg-gradient-to-r from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20 border border-green-200 dark:border-green-800">
                <div className="p-3 bg-green-500 rounded-xl shadow-md">
                  <CheckCircle className="h-5 w-5 text-white" />
                </div>
                <div className="flex-1">
                  <p className="text-sm font-semibold text-slate-900 dark:text-white">Annual checkup completed</p>
                  <p className="text-xs text-slate-600 dark:text-slate-400 mt-1">Last week - All results normal</p>
                </div>
                <Badge className="bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 border-0">Complete</Badge>
              </div>

              <div className="flex items-center space-x-4 p-4 rounded-xl bg-gradient-to-r from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 border border-blue-200 dark:border-blue-800">
                <div className="p-3 bg-blue-500 rounded-xl shadow-md">
                  <Download className="h-5 w-5 text-white" />
                </div>
                <div className="flex-1">
                  <p className="text-sm font-semibold text-slate-900 dark:text-white">New lab results available</p>
                  <p className="text-xs text-slate-600 dark:text-slate-400 mt-1">Blood work from last visit</p>
                </div>
                <Badge className="bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 border-0">New</Badge>
              </div>

              <div className="flex items-center space-x-4 p-4 rounded-xl bg-gradient-to-r from-orange-50 to-orange-100 dark:from-orange-900/20 dark:to-orange-800/20 border border-orange-200 dark:border-orange-800">
                <div className="p-3 bg-orange-500 rounded-xl shadow-md">
                  <Pill className="h-5 w-5 text-white" />
                </div>
                <div className="flex-1">
                  <p className="text-sm font-semibold text-slate-900 dark:text-white">Prescription updated</p>
                  <p className="text-xs text-slate-600 dark:text-slate-400 mt-1">Dosage adjusted by Dr. Smith</p>
                </div>
                <Badge className="bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200 border-0">Updated</Badge>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </>
  );
}
