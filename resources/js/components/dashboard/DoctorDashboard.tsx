import React from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
  Calendar,
  Users,
  FileText,
  Pill,
  Clock,
  AlertCircle,
  CheckCircle,
  Activity,
  Zap
} from 'lucide-react';
import { Link } from '@inertiajs/react';
import { useAuth, hasPermission } from '@/utils/permissions';

interface DashboardStats {
  todayAppointments?: number;
  upcomingAppointments?: number;
  activePatients?: number;
  pendingPrescriptions?: number;
  recentMedsSamples?: number;
  urgentTasks?: number;
}

interface DoctorDashboardProps {
  stats: DashboardStats;
}

export default function DoctorDashboard({ stats }: DoctorDashboardProps) {
  const user = useAuth();

  const quickActions = [
    {
      title: 'View Schedule',
      description: 'Check your appointments and availability',
      icon: Calendar,
      href: '/appointments',
      gradient: 'from-blue-500 to-blue-600',
      hoverGradient: 'from-blue-600 to-blue-700',
      permission: 'schedule.view'
    },
    {
      title: 'Manage Patients',
      description: 'Access patient EMR and medical records',
      icon: Users,
      href: '/patients',
      gradient: 'from-emerald-500 to-emerald-600',
      hoverGradient: 'from-emerald-600 to-emerald-700',
      permission: 'patient.read'
    },
    {
      title: 'Issue Prescriptions',
      description: 'Create and manage prescriptions',
      icon: FileText,
      href: '/prescriptions',
      gradient: 'from-purple-500 to-purple-600',
      hoverGradient: 'from-purple-600 to-purple-700',
      permission: 'rx.issue'
    },
    {
      title: 'Med Samples',
      description: 'View medication samples from MedReps',
      icon: Pill,
      href: '/med-samples',
      gradient: 'from-orange-500 to-orange-600',
      hoverGradient: 'from-orange-600 to-orange-700',
      permission: 'medrep.view'
    },
  ];

    const filteredQuickActions = quickActions.filter(action =>
    hasPermission(user, action.permission)
  );

  return (
    <>
      {/* Stats Overview */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-6 mb-8">
        <Card className="relative overflow-hidden border-0 shadow-lg hover:shadow-xl transition-all duration-300 bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20">
          <div className="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-blue-500/10 to-blue-600/10 rounded-full -translate-y-10 translate-x-10"></div>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2 relative z-10">
            <CardTitle className="text-sm font-medium text-slate-700 dark:text-slate-300">Today's Appointments</CardTitle>
            <div className="p-2 bg-blue-500 rounded-lg shadow-md">
              <Calendar className="h-4 w-4 text-white" />
            </div>
          </CardHeader>
          <CardContent className="relative z-10">
            <div className="text-3xl font-bold text-blue-600 dark:text-blue-400">{stats.todayAppointments || 0}</div>
            <p className="text-xs text-slate-600 dark:text-slate-400 mt-1">
              Scheduled for today
            </p>
          </CardContent>
        </Card>

        <Card className="relative overflow-hidden border-0 shadow-lg hover:shadow-xl transition-all duration-300 bg-gradient-to-br from-emerald-50 to-emerald-100 dark:from-emerald-900/20 dark:to-emerald-800/20">
          <div className="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-emerald-500/10 to-emerald-600/10 rounded-full -translate-y-10 translate-x-10"></div>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2 relative z-10">
            <CardTitle className="text-sm font-medium text-slate-700 dark:text-slate-300">Upcoming</CardTitle>
            <div className="p-2 bg-emerald-500 rounded-lg shadow-md">
              <Clock className="h-4 w-4 text-white" />
            </div>
          </CardHeader>
          <CardContent className="relative z-10">
            <div className="text-3xl font-bold text-emerald-600 dark:text-emerald-400">{stats.upcomingAppointments || 0}</div>
            <p className="text-xs text-slate-600 dark:text-slate-400 mt-1">
              Next 7 days
            </p>
          </CardContent>
        </Card>

        <Card className="relative overflow-hidden border-0 shadow-lg hover:shadow-xl transition-all duration-300 bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20">
          <div className="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-purple-500/10 to-purple-600/10 rounded-full -translate-y-10 translate-x-10"></div>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2 relative z-10">
            <CardTitle className="text-sm font-medium text-slate-700 dark:text-slate-300">Active Patients</CardTitle>
            <div className="p-2 bg-purple-500 rounded-lg shadow-md">
              <Users className="h-4 w-4 text-white" />
            </div>
          </CardHeader>
          <CardContent className="relative z-10">
            <div className="text-3xl font-bold text-purple-600 dark:text-purple-400">{stats.activePatients || 0}</div>
            <p className="text-xs text-slate-600 dark:text-slate-400 mt-1">
              Under your care
            </p>
          </CardContent>
        </Card>

        <Card className="relative overflow-hidden border-0 shadow-lg hover:shadow-xl transition-all duration-300 bg-gradient-to-br from-orange-50 to-orange-100 dark:from-orange-900/20 dark:to-orange-800/20">
          <div className="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-orange-500/10 to-orange-600/10 rounded-full -translate-y-10 translate-x-10"></div>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2 relative z-10">
            <CardTitle className="text-sm font-medium text-slate-700 dark:text-slate-300">Pending Prescriptions</CardTitle>
            <div className="p-2 bg-orange-500 rounded-lg shadow-md">
              <FileText className="h-4 w-4 text-white" />
            </div>
          </CardHeader>
          <CardContent className="relative z-10">
            <div className="text-3xl font-bold text-orange-600 dark:text-orange-400">{stats.pendingPrescriptions || 0}</div>
            <p className="text-xs text-slate-600 dark:text-slate-400 mt-1">
              Awaiting verification
            </p>
          </CardContent>
        </Card>

        <Card className="relative overflow-hidden border-0 shadow-lg hover:shadow-xl transition-all duration-300 bg-gradient-to-br from-cyan-50 to-cyan-100 dark:from-cyan-900/20 dark:to-cyan-800/20">
          <div className="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-cyan-500/10 to-cyan-600/10 rounded-full -translate-y-10 translate-x-10"></div>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2 relative z-10">
            <CardTitle className="text-sm font-medium text-slate-700 dark:text-slate-300">Recent Med Samples</CardTitle>
            <div className="p-2 bg-cyan-500 rounded-lg shadow-md">
              <Pill className="h-4 w-4 text-white" />
            </div>
          </CardHeader>
          <CardContent className="relative z-10">
            <div className="text-3xl font-bold text-cyan-600 dark:text-cyan-400">{stats.recentMedsSamples || 0}</div>
            <p className="text-xs text-slate-600 dark:text-slate-400 mt-1">
              This week
            </p>
          </CardContent>
        </Card>

        <Card className="relative overflow-hidden border-0 shadow-lg hover:shadow-xl transition-all duration-300 bg-gradient-to-br from-red-50 to-red-100 dark:from-red-900/20 dark:to-red-800/20">
          <div className="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-red-500/10 to-red-600/10 rounded-full -translate-y-10 translate-x-10"></div>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2 relative z-10">
            <CardTitle className="text-sm font-medium text-slate-700 dark:text-slate-300">Urgent Tasks</CardTitle>
            <div className="p-2 bg-red-500 rounded-lg shadow-md">
              <AlertCircle className="h-4 w-4 text-white" />
            </div>
          </CardHeader>
          <CardContent className="relative z-10">
            <div className="text-3xl font-bold text-red-600 dark:text-red-400">{stats.urgentTasks || 0}</div>
            <p className="text-xs text-slate-600 dark:text-slate-400 mt-1">
              Requires attention
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

      {/* Recent Activity */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <Card className="border-0 shadow-xl bg-white dark:bg-slate-800">
          <CardHeader className="pb-4">
            <div className="flex items-center space-x-3">
              <div className="p-2 bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg">
                <Activity className="h-5 w-5 text-white" />
              </div>
              <div>
                <CardTitle className="text-lg font-semibold text-slate-900 dark:text-white">Recent Clinical Activity</CardTitle>
                <CardDescription className="text-slate-600 dark:text-slate-400">
                  Your latest patient consultations and clinical interactions
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
                  <p className="text-sm font-semibold text-slate-900 dark:text-white">Appointment completed</p>
                  <p className="text-xs text-slate-600 dark:text-slate-400 mt-1">John Doe - 2 hours ago</p>
                </div>
                <Badge className="bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 border-0">Completed</Badge>
              </div>

              <div className="flex items-center space-x-4 p-4 rounded-xl bg-gradient-to-r from-emerald-50 to-emerald-100 dark:from-emerald-900/20 dark:to-emerald-800/20 border border-emerald-200 dark:border-emerald-800">
                <div className="p-3 bg-emerald-500 rounded-xl shadow-md">
                  <FileText className="h-5 w-5 text-white" />
                </div>
                <div className="flex-1">
                  <p className="text-sm font-semibold text-slate-900 dark:text-white">Prescription issued</p>
                  <p className="text-xs text-slate-600 dark:text-slate-400 mt-1">Jane Smith - 4 hours ago</p>
                </div>
                <Badge className="bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-200 border-0">Active</Badge>
              </div>

              <div className="flex items-center space-x-4 p-4 rounded-xl bg-gradient-to-r from-orange-50 to-orange-100 dark:from-orange-900/20 dark:to-orange-800/20 border border-orange-200 dark:border-orange-800">
                <div className="p-3 bg-orange-500 rounded-xl shadow-md">
                  <Pill className="h-5 w-5 text-white" />
                </div>
                <div className="flex-1">
                  <p className="text-sm font-semibold text-slate-900 dark:text-white">New med samples received</p>
                  <p className="text-xs text-slate-600 dark:text-slate-400 mt-1">PharmaCorp - 1 day ago</p>
                </div>
                <Badge className="bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200 border-0">New</Badge>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card className="border-0 shadow-xl bg-white dark:bg-slate-800">
          <CardHeader className="pb-4">
            <div className="flex items-center space-x-3">
              <div className="p-2 bg-gradient-to-r from-red-500 to-red-600 rounded-lg">
                <AlertCircle className="h-5 w-5 text-white" />
              </div>
              <div>
                <CardTitle className="text-lg font-semibold text-slate-900 dark:text-white">Clinical Tasks & Reminders</CardTitle>
                <CardDescription className="text-slate-600 dark:text-slate-400">
                  Important clinical tasks, follow-ups, and patient care reminders
                </CardDescription>
              </div>
            </div>
          </CardHeader>
          <CardContent>
            <div className="space-y-6">
              <div className="flex items-center space-x-4 p-4 rounded-xl bg-gradient-to-r from-red-50 to-red-100 dark:from-red-900/20 dark:to-red-800/20 border border-red-200 dark:border-red-800">
                <div className="p-3 bg-red-500 rounded-xl shadow-md">
                  <AlertCircle className="h-5 w-5 text-white" />
                </div>
                <div className="flex-1">
                  <p className="text-sm font-semibold text-slate-900 dark:text-white">Follow-up appointment</p>
                  <p className="text-xs text-slate-600 dark:text-slate-400 mt-1">Tomorrow at 10:00 AM</p>
                </div>
                <Badge className="bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 border-0">Urgent</Badge>
              </div>

              <div className="flex items-center space-x-4 p-4 rounded-xl bg-gradient-to-r from-yellow-50 to-yellow-100 dark:from-yellow-900/20 dark:to-yellow-800/20 border border-yellow-200 dark:border-yellow-800">
                <div className="p-3 bg-yellow-500 rounded-xl shadow-md">
                  <Clock className="h-5 w-5 text-white" />
                </div>
                <div className="flex-1">
                  <p className="text-sm font-semibold text-slate-900 dark:text-white">Prescription review</p>
                  <p className="text-xs text-slate-600 dark:text-slate-400 mt-1">Due in 2 days</p>
                </div>
                <Badge className="bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200 border-0">Pending</Badge>
              </div>

              <div className="flex items-center space-x-4 p-4 rounded-xl bg-gradient-to-r from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20 border border-green-200 dark:border-green-800">
                <div className="p-3 bg-green-500 rounded-xl shadow-md">
                  <CheckCircle className="h-5 w-5 text-white" />
                </div>
                <div className="flex-1">
                  <p className="text-sm font-semibold text-slate-900 dark:text-white">Lab results review</p>
                  <p className="text-xs text-slate-600 dark:text-slate-400 mt-1">Available now</p>
                </div>
                <Badge className="bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 border-0">Ready</Badge>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </>
  );
}
