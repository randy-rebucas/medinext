import React, { useState } from 'react';
import { Head, Link } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
  BarChart3,
  TrendingUp,
  TrendingDown,
  Users,
  Calendar,
  FileText,
  Download,
  Filter,
  Calendar as CalendarIcon,
  User,
  Stethoscope,
  Menu,
  X,
  Home,
  Settings,
  LogOut,
  Bell,
  ClipboardList,
  MessageSquare,
  Microscope,
  Pill,
  Activity,
  Heart,
  Zap,
  AlertTriangle,
  CheckCircle,
  Clock,
  FileCheck,
  AlertCircle,
  PieChart,
  LineChart
} from 'lucide-react';

interface ReportData {
  id: number;
  title: string;
  type: 'appointments' | 'patients' | 'prescriptions' | 'revenue' | 'performance' | 'custom';
  period: string;
  generatedDate: string;
  status: 'ready' | 'generating' | 'error';
  metrics: {
    label: string;
    value: string;
    change: number;
    trend: 'up' | 'down' | 'stable';
  }[];
}

interface ReportsPageProps {
  auth: {
    user: {
      id: number;
      name: string;
      email: string;
      role: string;
    };
  };
}

export default function ReportsPage({ auth }: ReportsPageProps) {
  const [sidebarOpen, setSidebarOpen] = useState(false);
  const [selectedPeriod, setSelectedPeriod] = useState('30days');

  // Mock reports data
  const reports: ReportData[] = [
    {
      id: 1,
      title: 'Monthly Patient Summary',
      type: 'patients',
      period: 'January 2024',
      generatedDate: '2024-01-31',
      status: 'ready',
      metrics: [
        { label: 'New Patients', value: '45', change: 12, trend: 'up' },
        { label: 'Total Visits', value: '128', change: 8, trend: 'up' },
        { label: 'Follow-ups', value: '83', change: -3, trend: 'down' },
        { label: 'No-shows', value: '12', change: -15, trend: 'down' }
      ]
    },
    {
      id: 2,
      title: 'Prescription Analytics',
      type: 'prescriptions',
      period: 'Last 30 Days',
      generatedDate: '2024-01-30',
      status: 'ready',
      metrics: [
        { label: 'Total Prescriptions', value: '156', change: 18, trend: 'up' },
        { label: 'Refills', value: '89', change: 5, trend: 'up' },
        { label: 'New Medications', value: '67', change: 22, trend: 'up' },
        { label: 'Expired', value: '3', change: -50, trend: 'down' }
      ]
    },
    {
      id: 3,
      title: 'Appointment Performance',
      type: 'appointments',
      period: 'Q1 2024',
      generatedDate: '2024-01-29',
      status: 'ready',
      metrics: [
        { label: 'Scheduled', value: '245', change: 15, trend: 'up' },
        { label: 'Completed', value: '198', change: 8, trend: 'up' },
        { label: 'Cancelled', value: '32', change: -12, trend: 'down' },
        { label: 'No-shows', value: '15', change: -25, trend: 'down' }
      ]
    },
    {
      id: 4,
      title: 'Revenue Report',
      type: 'revenue',
      period: 'January 2024',
      generatedDate: '2024-01-28',
      status: 'generating',
      metrics: []
    }
  ];

  const navigationItems = [
    {
      name: 'Dashboard',
      href: '/doctor/dashboard',
      icon: Home,
      current: false,
      color: 'text-blue-600',
      bgColor: 'bg-blue-50 dark:bg-blue-900/20'
    },
    {
      name: 'Patient Management',
      href: '/doctor/patients',
      icon: Users,
      current: false,
      color: 'text-emerald-600',
      bgColor: 'bg-emerald-50 dark:bg-emerald-900/20'
    },
    {
      name: 'Appointments',
      href: '/doctor/appointments',
      icon: Calendar,
      current: false,
      color: 'text-purple-600',
      bgColor: 'bg-purple-50 dark:bg-purple-900/20'
    },
    {
      name: 'Medical Records',
      href: '/doctor/records',
      icon: FileText,
      current: false,
      color: 'text-orange-600',
      bgColor: 'bg-orange-50 dark:bg-orange-900/20'
    },
    {
      name: 'Prescriptions',
      href: '/doctor/prescriptions',
      icon: Pill,
      current: false,
      color: 'text-cyan-600',
      bgColor: 'bg-cyan-50 dark:bg-cyan-900/20'
    },
    {
      name: 'Lab Results',
      href: '/doctor/lab-results',
      icon: Microscope,
      current: false,
      color: 'text-indigo-600',
      bgColor: 'bg-indigo-50 dark:bg-indigo-900/20'
    },
    {
      name: 'Reports & Analytics',
      href: '/doctor/reports',
      icon: BarChart3,
      current: true,
      color: 'text-pink-600',
      bgColor: 'bg-pink-50 dark:bg-pink-900/20'
    },
    {
      name: 'Med Samples',
      href: '/doctor/med-samples',
      icon: ClipboardList,
      current: false,
      color: 'text-yellow-600',
      bgColor: 'bg-yellow-50 dark:bg-yellow-900/20'
    },
    {
      name: 'Messages',
      href: '/doctor/messages',
      icon: MessageSquare,
      current: false,
      color: 'text-green-600',
      bgColor: 'bg-green-50 dark:bg-green-900/20'
    },
    {
      name: 'Settings',
      href: '/doctor/settings',
      icon: Settings,
      current: false,
      color: 'text-gray-600',
      bgColor: 'bg-gray-50 dark:bg-gray-900/20'
    }
  ];

  const getStatusBadge = (status: string) => {
    switch (status) {
      case 'ready':
        return <Badge className="bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Ready</Badge>;
      case 'generating':
        return <Badge className="bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">Generating</Badge>;
      case 'error':
        return <Badge className="bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">Error</Badge>;
      default:
        return <Badge>Unknown</Badge>;
    }
  };

  const getTypeIcon = (type: string) => {
    switch (type) {
      case 'appointments':
        return <Calendar className="h-5 w-5 text-purple-600" />;
      case 'patients':
        return <Users className="h-5 w-5 text-emerald-600" />;
      case 'prescriptions':
        return <Pill className="h-5 w-5 text-cyan-600" />;
      case 'revenue':
        return <TrendingUp className="h-5 w-5 text-green-600" />;
      case 'performance':
        return <Activity className="h-5 w-5 text-blue-600" />;
      case 'custom':
        return <FileText className="h-5 w-5 text-gray-600" />;
      default:
        return <BarChart3 className="h-5 w-5 text-gray-600" />;
    }
  };

  const getTrendIcon = (trend: string) => {
    switch (trend) {
      case 'up':
        return <TrendingUp className="h-4 w-4 text-green-600" />;
      case 'down':
        return <TrendingDown className="h-4 w-4 text-red-600" />;
      case 'stable':
        return <Activity className="h-4 w-4 text-gray-600" />;
      default:
        return <Activity className="h-4 w-4 text-gray-600" />;
    }
  };

  const getTrendColor = (trend: string) => {
    switch (trend) {
      case 'up':
        return 'text-green-600';
      case 'down':
        return 'text-red-600';
      case 'stable':
        return 'text-gray-600';
      default:
        return 'text-gray-600';
    }
  };

  return (
    <>
      <Head title="Reports & Analytics - MediNext" />

      <div className="min-h-screen bg-gradient-to-br from-slate-50 via-white to-blue-50 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900">
        {/* Mobile sidebar overlay */}
        {sidebarOpen && (
          <div
            className="fixed inset-0 z-40 bg-black bg-opacity-50 lg:hidden"
            onClick={() => setSidebarOpen(false)}
          />
        )}

        {/* Sidebar */}
        <div className={`fixed inset-y-0 left-0 z-50 w-64 bg-white dark:bg-slate-900 shadow-2xl transform transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0 ${
          sidebarOpen ? 'translate-x-0' : '-translate-x-full'
        }`}>
          <div className="flex flex-col h-full">
            {/* Logo and Header */}
            <div className="flex items-center justify-between p-6 border-b border-slate-200 dark:border-slate-700">
              <div className="flex items-center space-x-3">
                <div className="p-2 bg-gradient-to-r from-blue-600 to-purple-600 rounded-xl">
                  <Stethoscope className="h-6 w-6 text-white" />
                </div>
                <div>
                  <h1 className="text-lg font-bold text-slate-900 dark:text-white">MediNext</h1>
                  <p className="text-xs text-slate-600 dark:text-slate-400">Doctor Portal</p>
                </div>
              </div>
              <button
                onClick={() => setSidebarOpen(false)}
                className="lg:hidden p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800"
              >
                <X className="h-5 w-5 text-slate-600 dark:text-slate-400" />
              </button>
            </div>

            {/* User Profile */}
            <div className="p-6 border-b border-slate-200 dark:border-slate-700">
              <div className="flex items-center space-x-3">
                <div className="p-2 bg-gradient-to-r from-emerald-500 to-emerald-600 rounded-full">
                  <User className="h-5 w-5 text-white" />
                </div>
                <div>
                  <p className="text-sm font-semibold text-slate-900 dark:text-white">Dr. {auth.user.name}</p>
                  <p className="text-xs text-slate-600 dark:text-slate-400">{auth.user.email}</p>
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
                <button
                  onClick={() => setSidebarOpen(true)}
                  className="lg:hidden p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800"
                >
                  <Menu className="h-5 w-5 text-slate-600 dark:text-slate-400" />
                </button>
                <div>
                  <h1 className="text-2xl font-bold text-slate-900 dark:text-white">Reports & Analytics</h1>
                  <p className="text-sm text-slate-600 dark:text-slate-400">View insights and generate reports for your practice</p>
                </div>
              </div>

              <div className="flex items-center space-x-4">
                <Button className="px-4">
                  <BarChart3 className="h-4 w-4 mr-2" />
                  Generate Report
                </Button>
                <button className="relative p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800">
                  <Bell className="h-5 w-5 text-slate-600 dark:text-slate-400" />
                  <span className="absolute -top-1 -right-1 h-4 w-4 bg-red-500 text-white text-xs rounded-full flex items-center justify-center">3</span>
                </button>
              </div>
            </div>
          </div>

          {/* Page Content */}
          <div className="p-4 sm:p-6 lg:p-8">
            {/* Quick Stats */}
            <div className="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
              <Card className="border-0 shadow-lg bg-gradient-to-br from-pink-50 to-pink-100 dark:from-pink-900/20 dark:to-pink-800/20">
                <CardContent className="p-6">
                  <div className="flex items-center justify-between">
                    <div>
                      <p className="text-sm font-medium text-slate-600 dark:text-slate-400">Total Reports</p>
                      <p className="text-3xl font-bold text-pink-600 dark:text-pink-400">{reports.length}</p>
                    </div>
                    <BarChart3 className="h-8 w-8 text-pink-600 dark:text-pink-400" />
                  </div>
                </CardContent>
              </Card>

              <Card className="border-0 shadow-lg bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20">
                <CardContent className="p-6">
                  <div className="flex items-center justify-between">
                    <div>
                      <p className="text-sm font-medium text-slate-600 dark:text-slate-400">Ready</p>
                      <p className="text-3xl font-bold text-green-600 dark:text-green-400">
                        {reports.filter(r => r.status === 'ready').length}
                      </p>
                    </div>
                    <CheckCircle className="h-8 w-8 text-green-600 dark:text-green-400" />
                  </div>
                </CardContent>
              </Card>

              <Card className="border-0 shadow-lg bg-gradient-to-br from-yellow-50 to-yellow-100 dark:from-yellow-900/20 dark:to-yellow-800/20">
                <CardContent className="p-6">
                  <div className="flex items-center justify-between">
                    <div>
                      <p className="text-sm font-medium text-slate-600 dark:text-slate-400">Generating</p>
                      <p className="text-3xl font-bold text-yellow-600 dark:text-yellow-400">
                        {reports.filter(r => r.status === 'generating').length}
                      </p>
                    </div>
                    <Clock className="h-8 w-8 text-yellow-600 dark:text-yellow-400" />
                  </div>
                </CardContent>
              </Card>

              <Card className="border-0 shadow-lg bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20">
                <CardContent className="p-6">
                  <div className="flex items-center justify-between">
                    <div>
                      <p className="text-sm font-medium text-slate-600 dark:text-slate-400">This Month</p>
                      <p className="text-3xl font-bold text-blue-600 dark:text-blue-400">8</p>
                    </div>
                    <Activity className="h-8 w-8 text-blue-600 dark:text-blue-400" />
                  </div>
                </CardContent>
              </Card>
            </div>

            {/* Report Generation Tools */}
            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
              <Card className="lg:col-span-2">
                <CardHeader>
                  <CardTitle className="flex items-center space-x-2">
                    <PieChart className="h-5 w-5 text-pink-600" />
                    <span>Quick Analytics</span>
                  </CardTitle>
                  <CardDescription>Real-time insights into your practice performance</CardDescription>
                </CardHeader>
                <CardContent>
                  <div className="grid grid-cols-2 gap-4">
                    <div className="p-4 bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 rounded-lg">
                      <div className="flex items-center justify-between mb-2">
                        <span className="text-sm font-medium text-slate-600 dark:text-slate-400">Patient Satisfaction</span>
                        <TrendingUp className="h-4 w-4 text-green-600" />
                      </div>
                      <p className="text-2xl font-bold text-blue-600 dark:text-blue-400">94%</p>
                      <p className="text-xs text-slate-600 dark:text-slate-400">+2% from last month</p>
                    </div>
                    <div className="p-4 bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20 rounded-lg">
                      <div className="flex items-center justify-between mb-2">
                        <span className="text-sm font-medium text-slate-600 dark:text-slate-400">Appointment Efficiency</span>
                        <TrendingUp className="h-4 w-4 text-green-600" />
                      </div>
                      <p className="text-2xl font-bold text-green-600 dark:text-green-400">87%</p>
                      <p className="text-xs text-slate-600 dark:text-slate-400">+5% from last month</p>
                    </div>
                    <div className="p-4 bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20 rounded-lg">
                      <div className="flex items-center justify-between mb-2">
                        <span className="text-sm font-medium text-slate-600 dark:text-slate-400">Revenue Growth</span>
                        <TrendingUp className="h-4 w-4 text-green-600" />
                      </div>
                      <p className="text-2xl font-bold text-purple-600 dark:text-purple-400">12%</p>
                      <p className="text-xs text-slate-600 dark:text-slate-400">+3% from last month</p>
                    </div>
                    <div className="p-4 bg-gradient-to-br from-orange-50 to-orange-100 dark:from-orange-900/20 dark:to-orange-800/20 rounded-lg">
                      <div className="flex items-center justify-between mb-2">
                        <span className="text-sm font-medium text-slate-600 dark:text-slate-400">No-Show Rate</span>
                        <TrendingDown className="h-4 w-4 text-red-600" />
                      </div>
                      <p className="text-2xl font-bold text-orange-600 dark:text-orange-400">6%</p>
                      <p className="text-xs text-slate-600 dark:text-slate-400">-2% from last month</p>
                    </div>
                  </div>
                </CardContent>
              </Card>

              <Card>
                <CardHeader>
                  <CardTitle className="flex items-center space-x-2">
                    <LineChart className="h-5 w-5 text-blue-600" />
                    <span>Generate Report</span>
                  </CardTitle>
                  <CardDescription>Create custom reports for your practice</CardDescription>
                </CardHeader>
                <CardContent className="space-y-4">
                  <div>
                    <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                      Report Type
                    </label>
                    <select className="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                      <option>Patient Summary</option>
                      <option>Appointment Analytics</option>
                      <option>Prescription Report</option>
                      <option>Revenue Analysis</option>
                      <option>Custom Report</option>
                    </select>
                  </div>
                  <div>
                    <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                      Time Period
                    </label>
                    <select
                      value={selectedPeriod}
                      onChange={(e) => setSelectedPeriod(e.target.value)}
                      className="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white"
                    >
                      <option value="7days">Last 7 Days</option>
                      <option value="30days">Last 30 Days</option>
                      <option value="90days">Last 90 Days</option>
                      <option value="1year">Last Year</option>
                      <option value="custom">Custom Range</option>
                    </select>
                  </div>
                  <Button className="w-full">
                    <BarChart3 className="h-4 w-4 mr-2" />
                    Generate Report
                  </Button>
                </CardContent>
              </Card>
            </div>

            {/* Reports List */}
            <div className="space-y-4">
              <h2 className="text-xl font-semibold text-slate-900 dark:text-white mb-4">Recent Reports</h2>
              {reports.map((report) => (
                <Card key={report.id} className="hover:shadow-xl transition-all duration-300 border-0 shadow-lg">
                  <CardContent className="p-6">
                    <div className="flex items-start justify-between mb-4">
                      <div className="flex items-start space-x-4">
                        <div className="p-3 bg-gradient-to-r from-slate-100 to-slate-200 dark:from-slate-800 dark:to-slate-700 rounded-xl">
                          {getTypeIcon(report.type)}
                        </div>
                        <div className="flex-1">
                          <div className="flex items-center space-x-2 mb-2">
                            <h3 className="text-lg font-semibold text-slate-900 dark:text-white">{report.title}</h3>
                            {getStatusBadge(report.status)}
                          </div>
                          <div className="flex items-center space-x-4 text-sm text-slate-600 dark:text-slate-400 mb-3">
                            <span className="flex items-center space-x-1">
                              <CalendarIcon className="h-4 w-4" />
                              <span>{report.period}</span>
                            </span>
                            <span className="flex items-center space-x-1">
                              <Clock className="h-4 w-4" />
                              <span>Generated: {report.generatedDate}</span>
                            </span>
                          </div>
                        </div>
                      </div>
                      <div className="flex items-center space-x-2">
                        {report.status === 'ready' && (
                          <Button variant="outline" size="sm">
                            <Download className="h-4 w-4 mr-2" />
                            Download
                          </Button>
                        )}
                        {report.status === 'generating' && (
                          <Button variant="outline" size="sm" disabled>
                            <Clock className="h-4 w-4 mr-2" />
                            Generating...
                          </Button>
                        )}
                      </div>
                    </div>

                    {/* Metrics */}
                    {report.metrics.length > 0 && (
                      <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                        {report.metrics.map((metric, index) => (
                          <div key={index} className="p-3 bg-slate-50 dark:bg-slate-800 rounded-lg">
                            <div className="flex items-center justify-between mb-1">
                              <span className="text-sm font-medium text-slate-900 dark:text-white">{metric.label}</span>
                              {getTrendIcon(metric.trend)}
                            </div>
                            <div className="text-lg font-bold text-slate-900 dark:text-white">{metric.value}</div>
                            <div className={`text-xs flex items-center space-x-1 ${getTrendColor(metric.trend)}`}>
                              {metric.change > 0 ? '+' : ''}{metric.change}%
                              <span>from last period</span>
                            </div>
                          </div>
                        ))}
                      </div>
                    )}
                  </CardContent>
                </Card>
              ))}
            </div>
          </div>
        </div>
      </div>
    </>
  );
}
