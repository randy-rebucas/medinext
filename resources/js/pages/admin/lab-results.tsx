import React, { useState } from 'react';
import { Head, Link } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import {
  Microscope,
  Search,
  Plus,
  Filter,
  Eye,
  Edit,
  Download,
  Calendar,
  User,
  Stethoscope,
  Menu,
  X,
  Home,
  Settings,
  LogOut,
  Bell,
  BarChart3,
  ClipboardList,
  MessageSquare,
  Users,
  FileText,
  Pill,
  Activity,
  Heart,
  Zap,
  AlertTriangle,
  CheckCircle,
  Clock,
  FileCheck,
  AlertCircle,
  TrendingUp,
  TrendingDown
} from 'lucide-react';

interface LabResult {
  id: number;
  patientName: string;
  patientId: number;
  testName: string;
  testType: 'blood' | 'urine' | 'imaging' | 'biopsy' | 'culture' | 'other';
  orderedBy: string;
  date: string;
  status: 'pending' | 'completed' | 'abnormal' | 'critical';
  results: {
    parameter: string;
    value: string;
    unit: string;
    referenceRange: string;
    status: 'normal' | 'high' | 'low' | 'critical';
  }[];
  notes: string;
  attachments: number;
}

interface LabResultsPageProps {
  auth: {
    user: {
      id: number;
      name: string;
      email: string;
      role: string;
    };
  };
}

export default function LabResultsPage({ auth }: LabResultsPageProps) {
  const [sidebarOpen, setSidebarOpen] = useState(false);
  const [searchTerm, setSearchTerm] = useState('');
  const [selectedType, setSelectedType] = useState('all');
  const [selectedStatus, setSelectedStatus] = useState('all');

  // Mock lab results data
  const labResults: LabResult[] = [
    {
      id: 1,
      patientName: 'John Doe',
      patientId: 1001,
      testName: 'Complete Blood Count (CBC)',
      testType: 'blood',
      orderedBy: 'Dr. Smith',
      date: '2024-01-15',
      status: 'completed',
      results: [
        { parameter: 'Hemoglobin', value: '14.2', unit: 'g/dL', referenceRange: '13.8-17.2', status: 'normal' },
        { parameter: 'White Blood Cells', value: '8.5', unit: 'K/μL', referenceRange: '4.5-11.0', status: 'normal' },
        { parameter: 'Platelets', value: '350', unit: 'K/μL', referenceRange: '150-450', status: 'normal' }
      ],
      notes: 'All values within normal range. No abnormalities detected.',
      attachments: 2
    },
    {
      id: 2,
      patientName: 'Jane Smith',
      patientId: 1002,
      testName: 'HbA1c (Diabetes Monitoring)',
      testType: 'blood',
      orderedBy: 'Dr. Johnson',
      date: '2024-01-12',
      status: 'abnormal',
      results: [
        { parameter: 'HbA1c', value: '8.2', unit: '%', referenceRange: '<7.0', status: 'high' },
        { parameter: 'Fasting Glucose', value: '145', unit: 'mg/dL', referenceRange: '70-100', status: 'high' }
      ],
      notes: 'Elevated HbA1c indicates poor glycemic control. Recommend medication adjustment.',
      attachments: 1
    },
    {
      id: 3,
      patientName: 'Michael Johnson',
      patientId: 1003,
      testName: 'Lipid Panel',
      testType: 'blood',
      orderedBy: 'Dr. Williams',
      date: '2024-01-10',
      status: 'completed',
      results: [
        { parameter: 'Total Cholesterol', value: '220', unit: 'mg/dL', referenceRange: '<200', status: 'high' },
        { parameter: 'LDL Cholesterol', value: '140', unit: 'mg/dL', referenceRange: '<100', status: 'high' },
        { parameter: 'HDL Cholesterol', value: '45', unit: 'mg/dL', referenceRange: '>40', status: 'normal' },
        { parameter: 'Triglycerides', value: '180', unit: 'mg/dL', referenceRange: '<150', status: 'high' }
      ],
      notes: 'Elevated cholesterol levels. Consider statin therapy.',
      attachments: 3
    },
    {
      id: 4,
      patientName: 'Sarah Wilson',
      patientId: 1004,
      testName: 'Urinalysis',
      testType: 'urine',
      orderedBy: 'Dr. Brown',
      date: '2024-01-08',
      status: 'pending',
      results: [],
      notes: 'Test in progress. Results expected within 24 hours.',
      attachments: 0
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
      current: true,
      color: 'text-indigo-600',
      bgColor: 'bg-indigo-50 dark:bg-indigo-900/20'
    },
    {
      name: 'Reports & Analytics',
      href: '/doctor/reports',
      icon: BarChart3,
      current: false,
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

  const filteredResults = labResults.filter(result => {
    const matchesSearch = result.testName.toLowerCase().includes(searchTerm.toLowerCase()) ||
                         result.patientName.toLowerCase().includes(searchTerm.toLowerCase()) ||
                         result.orderedBy.toLowerCase().includes(searchTerm.toLowerCase());
    const matchesType = selectedType === 'all' || result.testType === selectedType;
    const matchesStatus = selectedStatus === 'all' || result.status === selectedStatus;
    return matchesSearch && matchesType && matchesStatus;
  });

  const getStatusBadge = (status: string) => {
    switch (status) {
      case 'completed':
        return <Badge className="bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Completed</Badge>;
      case 'abnormal':
        return <Badge className="bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">Abnormal</Badge>;
      case 'critical':
        return <Badge className="bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">Critical</Badge>;
      case 'pending':
        return <Badge className="bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">Pending</Badge>;
      default:
        return <Badge>Unknown</Badge>;
    }
  };

  const getTypeBadge = (type: string) => {
    switch (type) {
      case 'blood':
        return <Badge variant="outline" className="text-red-600 border-red-600">Blood Test</Badge>;
      case 'urine':
        return <Badge variant="outline" className="text-yellow-600 border-yellow-600">Urine Test</Badge>;
      case 'imaging':
        return <Badge variant="outline" className="text-blue-600 border-blue-600">Imaging</Badge>;
      case 'biopsy':
        return <Badge variant="outline" className="text-purple-600 border-purple-600">Biopsy</Badge>;
      case 'culture':
        return <Badge variant="outline" className="text-green-600 border-green-600">Culture</Badge>;
      case 'other':
        return <Badge variant="outline" className="text-gray-600 border-gray-600">Other</Badge>;
      default:
        return <Badge variant="outline">Unknown</Badge>;
    }
  };

  const getResultStatusIcon = (status: string) => {
    switch (status) {
      case 'normal':
        return <CheckCircle className="h-4 w-4 text-green-600" />;
      case 'high':
        return <TrendingUp className="h-4 w-4 text-red-600" />;
      case 'low':
        return <TrendingDown className="h-4 w-4 text-blue-600" />;
      case 'critical':
        return <AlertTriangle className="h-4 w-4 text-red-600" />;
      default:
        return <Clock className="h-4 w-4 text-gray-600" />;
    }
  };

  const getResultStatusColor = (status: string) => {
    switch (status) {
      case 'normal':
        return 'text-green-600 bg-green-50 dark:bg-green-900/20';
      case 'high':
        return 'text-red-600 bg-red-50 dark:bg-red-900/20';
      case 'low':
        return 'text-blue-600 bg-blue-50 dark:bg-blue-900/20';
      case 'critical':
        return 'text-red-600 bg-red-50 dark:bg-red-900/20';
      default:
        return 'text-gray-600 bg-gray-50 dark:bg-gray-900/20';
    }
  };

  return (
    <>
      <Head title="Lab Results - MediNext" />

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
                  <h1 className="text-2xl font-bold text-slate-900 dark:text-white">Lab Results</h1>
                  <p className="text-sm text-slate-600 dark:text-slate-400">View and manage laboratory test results</p>
                </div>
              </div>

              <div className="flex items-center space-x-4">
                <Button variant="outline" className="px-4">
                  <Plus className="h-4 w-4 mr-2" />
                  Order Test
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
            {/* Stats Cards */}
            <div className="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
              <Card className="border-0 shadow-lg bg-gradient-to-br from-indigo-50 to-indigo-100 dark:from-indigo-900/20 dark:to-indigo-800/20">
                <CardContent className="p-6">
                  <div className="flex items-center justify-between">
                    <div>
                      <p className="text-sm font-medium text-slate-600 dark:text-slate-400">Total Tests</p>
                      <p className="text-3xl font-bold text-indigo-600 dark:text-indigo-400">{labResults.length}</p>
                    </div>
                    <Microscope className="h-8 w-8 text-indigo-600 dark:text-indigo-400" />
                  </div>
                </CardContent>
              </Card>

              <Card className="border-0 shadow-lg bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20">
                <CardContent className="p-6">
                  <div className="flex items-center justify-between">
                    <div>
                      <p className="text-sm font-medium text-slate-600 dark:text-slate-400">Completed</p>
                      <p className="text-3xl font-bold text-green-600 dark:text-green-400">
                        {labResults.filter(r => r.status === 'completed').length}
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
                      <p className="text-sm font-medium text-slate-600 dark:text-slate-400">Abnormal</p>
                      <p className="text-3xl font-bold text-yellow-600 dark:text-yellow-400">
                        {labResults.filter(r => r.status === 'abnormal').length}
                      </p>
                    </div>
                    <AlertTriangle className="h-8 w-8 text-yellow-600 dark:text-yellow-400" />
                  </div>
                </CardContent>
              </Card>

              <Card className="border-0 shadow-lg bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20">
                <CardContent className="p-6">
                  <div className="flex items-center justify-between">
                    <div>
                      <p className="text-sm font-medium text-slate-600 dark:text-slate-400">Pending</p>
                      <p className="text-3xl font-bold text-blue-600 dark:text-blue-400">
                        {labResults.filter(r => r.status === 'pending').length}
                      </p>
                    </div>
                    <Clock className="h-8 w-8 text-blue-600 dark:text-blue-400" />
                  </div>
                </CardContent>
              </Card>
            </div>

            {/* Search and Filters */}
            <Card className="mb-6">
              <CardContent className="p-6">
                <div className="flex flex-col lg:flex-row gap-4">
                  <div className="flex-1 relative">
                    <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-slate-400" />
                    <Input
                      placeholder="Search lab results by test name, patient, or doctor..."
                      value={searchTerm}
                      onChange={(e) => setSearchTerm(e.target.value)}
                      className="pl-10"
                    />
                  </div>
                  <div className="flex gap-2">
                    <select
                      value={selectedType}
                      onChange={(e) => setSelectedType(e.target.value)}
                      className="px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white"
                    >
                      <option value="all">All Types</option>
                      <option value="blood">Blood Test</option>
                      <option value="urine">Urine Test</option>
                      <option value="imaging">Imaging</option>
                      <option value="biopsy">Biopsy</option>
                      <option value="culture">Culture</option>
                      <option value="other">Other</option>
                    </select>
                    <select
                      value={selectedStatus}
                      onChange={(e) => setSelectedStatus(e.target.value)}
                      className="px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white"
                    >
                      <option value="all">All Status</option>
                      <option value="completed">Completed</option>
                      <option value="abnormal">Abnormal</option>
                      <option value="critical">Critical</option>
                      <option value="pending">Pending</option>
                    </select>
                  </div>
                </div>
              </CardContent>
            </Card>

            {/* Lab Results List */}
            <div className="space-y-4">
              {filteredResults.map((result) => (
                <Card key={result.id} className="hover:shadow-xl transition-all duration-300 border-0 shadow-lg">
                  <CardContent className="p-6">
                    <div className="flex items-start justify-between mb-4">
                      <div className="flex items-start space-x-4">
                        <div className="p-3 bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-xl">
                          <Microscope className="h-6 w-6 text-white" />
                        </div>
                        <div className="flex-1">
                          <div className="flex items-center space-x-2 mb-2">
                            <h3 className="text-lg font-semibold text-slate-900 dark:text-white">{result.testName}</h3>
                            {getTypeBadge(result.testType)}
                            {getStatusBadge(result.status)}
                          </div>
                          <div className="flex items-center space-x-4 text-sm text-slate-600 dark:text-slate-400 mb-3">
                            <span className="flex items-center space-x-1">
                              <User className="h-4 w-4" />
                              <span>{result.patientName} (ID: {result.patientId})</span>
                            </span>
                            <span className="flex items-center space-x-1">
                              <Calendar className="h-4 w-4" />
                              <span>{result.date}</span>
                            </span>
                            <span className="flex items-center space-x-1">
                              <Stethoscope className="h-4 w-4" />
                              <span>{result.orderedBy}</span>
                            </span>
                          </div>
                        </div>
                      </div>
                      <div className="flex items-center space-x-2">
                        {result.attachments > 0 && (
                          <Badge variant="outline" className="text-xs">
                            {result.attachments} attachment{result.attachments > 1 ? 's' : ''}
                          </Badge>
                        )}
                      </div>
                    </div>

                    {/* Test Results */}
                    {result.results.length > 0 && (
                      <div className="mb-4">
                        <h4 className="text-sm font-medium text-slate-900 dark:text-white mb-3">Test Results:</h4>
                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                          {result.results.map((testResult, index) => (
                            <div key={index} className={`p-3 rounded-lg border ${getResultStatusColor(testResult.status)}`}>
                              <div className="flex items-center justify-between mb-1">
                                <span className="text-sm font-medium">{testResult.parameter}</span>
                                {getResultStatusIcon(testResult.status)}
                              </div>
                              <div className="text-lg font-bold">{testResult.value} {testResult.unit}</div>
                              <div className="text-xs opacity-75">Ref: {testResult.referenceRange}</div>
                            </div>
                          ))}
                        </div>
                      </div>
                    )}

                    {/* Notes */}
                    {result.notes && (
                      <div className="mb-4 p-3 bg-slate-50 dark:bg-slate-800 rounded-lg">
                        <p className="text-sm text-slate-700 dark:text-slate-300">
                          <strong>Notes:</strong> {result.notes}
                        </p>
                      </div>
                    )}

                    <div className="flex gap-2">
                      <Button variant="outline" size="sm">
                        <Eye className="h-4 w-4 mr-2" />
                        View Details
                      </Button>
                      <Button variant="outline" size="sm">
                        <Download className="h-4 w-4 mr-2" />
                        Download
                      </Button>
                      {result.status === 'abnormal' && (
                        <Button size="sm" className="bg-yellow-600 hover:bg-yellow-700">
                          <AlertTriangle className="h-4 w-4 mr-2" />
                          Review
                        </Button>
                      )}
                      {result.status === 'critical' && (
                        <Button size="sm" className="bg-red-600 hover:bg-red-700">
                          <AlertCircle className="h-4 w-4 mr-2" />
                          Urgent Review
                        </Button>
                      )}
                    </div>
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
