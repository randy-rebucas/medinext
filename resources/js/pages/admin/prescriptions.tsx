import React, { useState } from 'react';
import { Head, Link } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import {
  Pill,
  Search,
  Plus,
  Filter,
  Eye,
  Edit,
  Download,
  Printer,
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
  Microscope,
  Users,
  FileText,
  Activity,
  Heart,
  Zap,
  AlertTriangle,
  CheckCircle,
  Clock,
  FileCheck,
  AlertCircle
} from 'lucide-react';

interface Prescription {
  id: number;
  patientName: string;
  patientId: number;
  medication: string;
  dosage: string;
  frequency: string;
  duration: string;
  instructions: string;
  prescribedBy: string;
  date: string;
  status: 'active' | 'completed' | 'cancelled' | 'expired';
  refills: number;
  remainingRefills: number;
  sideEffects: string[];
  interactions: string[];
}

interface PrescriptionsPageProps {
  auth: {
    user: {
      id: number;
      name: string;
      email: string;
      role: string;
    };
  };
}

export default function PrescriptionsPage({ auth }: PrescriptionsPageProps) {
  const [sidebarOpen, setSidebarOpen] = useState(false);
  const [searchTerm, setSearchTerm] = useState('');
  const [selectedStatus, setSelectedStatus] = useState('all');

  // Mock prescriptions data
  const prescriptions: Prescription[] = [
    {
      id: 1,
      patientName: 'John Doe',
      patientId: 1001,
      medication: 'Lisinopril',
      dosage: '10mg',
      frequency: 'Once daily',
      duration: '30 days',
      instructions: 'Take with food, preferably in the morning',
      prescribedBy: 'Dr. Smith',
      date: '2024-01-15',
      status: 'active',
      refills: 3,
      remainingRefills: 2,
      sideEffects: ['Dry cough', 'Dizziness'],
      interactions: ['Potassium supplements', 'NSAIDs']
    },
    {
      id: 2,
      patientName: 'Jane Smith',
      patientId: 1002,
      medication: 'Metformin',
      dosage: '500mg',
      frequency: 'Twice daily',
      duration: '90 days',
      instructions: 'Take with meals to reduce stomach upset',
      prescribedBy: 'Dr. Johnson',
      date: '2024-01-12',
      status: 'active',
      refills: 2,
      remainingRefills: 1,
      sideEffects: ['Nausea', 'Diarrhea'],
      interactions: ['Alcohol', 'Contrast agents']
    },
    {
      id: 3,
      patientName: 'Michael Johnson',
      patientId: 1003,
      medication: 'Atorvastatin',
      dosage: '20mg',
      frequency: 'Once daily',
      duration: '30 days',
      instructions: 'Take in the evening with or without food',
      prescribedBy: 'Dr. Williams',
      date: '2024-01-10',
      status: 'completed',
      refills: 0,
      remainingRefills: 0,
      sideEffects: ['Muscle pain', 'Liver enzyme elevation'],
      interactions: ['Grapefruit juice', 'Warfarin']
    },
    {
      id: 4,
      patientName: 'Sarah Wilson',
      patientId: 1004,
      medication: 'Sumatriptan',
      dosage: '50mg',
      frequency: 'As needed',
      duration: '10 doses',
      instructions: 'Take at first sign of migraine, max 2 doses per day',
      prescribedBy: 'Dr. Brown',
      date: '2024-01-08',
      status: 'active',
      refills: 1,
      remainingRefills: 1,
      sideEffects: ['Chest tightness', 'Dizziness'],
      interactions: ['SSRIs', 'MAOIs']
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
      current: true,
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

  const filteredPrescriptions = prescriptions.filter(prescription => {
    const matchesSearch = prescription.medication.toLowerCase().includes(searchTerm.toLowerCase()) ||
                         prescription.patientName.toLowerCase().includes(searchTerm.toLowerCase()) ||
                         prescription.prescribedBy.toLowerCase().includes(searchTerm.toLowerCase());
    const matchesStatus = selectedStatus === 'all' || prescription.status === selectedStatus;
    return matchesSearch && matchesStatus;
  });

  const getStatusBadge = (status: string) => {
    switch (status) {
      case 'active':
        return <Badge className="bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Active</Badge>;
      case 'completed':
        return <Badge className="bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">Completed</Badge>;
      case 'cancelled':
        return <Badge className="bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">Cancelled</Badge>;
      case 'expired':
        return <Badge className="bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200">Expired</Badge>;
      default:
        return <Badge>Unknown</Badge>;
    }
  };

  return (
    <>
      <Head title="Prescriptions - MediNext" />

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
                  <h1 className="text-2xl font-bold text-slate-900 dark:text-white">Prescriptions</h1>
                  <p className="text-sm text-slate-600 dark:text-slate-400">Manage patient prescriptions and medication orders</p>
                </div>
              </div>

              <div className="flex items-center space-x-4">
                <Button className="px-4">
                  <Plus className="h-4 w-4 mr-2" />
                  New Prescription
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
              <Card className="border-0 shadow-lg bg-gradient-to-br from-cyan-50 to-cyan-100 dark:from-cyan-900/20 dark:to-cyan-800/20">
                <CardContent className="p-6">
                  <div className="flex items-center justify-between">
                    <div>
                      <p className="text-sm font-medium text-slate-600 dark:text-slate-400">Total Prescriptions</p>
                      <p className="text-3xl font-bold text-cyan-600 dark:text-cyan-400">{prescriptions.length}</p>
                    </div>
                    <Pill className="h-8 w-8 text-cyan-600 dark:text-cyan-400" />
                  </div>
                </CardContent>
              </Card>

              <Card className="border-0 shadow-lg bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20">
                <CardContent className="p-6">
                  <div className="flex items-center justify-between">
                    <div>
                      <p className="text-sm font-medium text-slate-600 dark:text-slate-400">Active</p>
                      <p className="text-3xl font-bold text-green-600 dark:text-green-400">
                        {prescriptions.filter(p => p.status === 'active').length}
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
                      <p className="text-sm font-medium text-slate-600 dark:text-slate-400">Need Refills</p>
                      <p className="text-3xl font-bold text-yellow-600 dark:text-yellow-400">
                        {prescriptions.filter(p => p.remainingRefills <= 1 && p.status === 'active').length}
                      </p>
                    </div>
                    <AlertTriangle className="h-8 w-8 text-yellow-600 dark:text-yellow-400" />
                  </div>
                </CardContent>
              </Card>

              <Card className="border-0 shadow-lg bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20">
                <CardContent className="p-6">
                  <div className="flex items-center justify-between">
                    <div>
                      <p className="text-sm font-medium text-slate-600 dark:text-slate-400">This Month</p>
                      <p className="text-3xl font-bold text-purple-600 dark:text-purple-400">12</p>
                    </div>
                    <Activity className="h-8 w-8 text-purple-600 dark:text-purple-400" />
                  </div>
                </CardContent>
              </Card>
            </div>

            {/* Search and Filters */}
            <Card className="mb-6">
              <CardContent className="p-6">
                <div className="flex flex-col sm:flex-row gap-4">
                  <div className="flex-1 relative">
                    <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-slate-400" />
                    <Input
                      placeholder="Search prescriptions by medication, patient, or doctor..."
                      value={searchTerm}
                      onChange={(e) => setSearchTerm(e.target.value)}
                      className="pl-10"
                    />
                  </div>
                  <div className="flex gap-2">
                    <select
                      value={selectedStatus}
                      onChange={(e) => setSelectedStatus(e.target.value)}
                      className="px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white"
                    >
                      <option value="all">All Status</option>
                      <option value="active">Active</option>
                      <option value="completed">Completed</option>
                      <option value="cancelled">Cancelled</option>
                      <option value="expired">Expired</option>
                    </select>
                  </div>
                </div>
              </CardContent>
            </Card>

            {/* Prescriptions List */}
            <div className="space-y-4">
              {filteredPrescriptions.map((prescription) => (
                <Card key={prescription.id} className="hover:shadow-xl transition-all duration-300 border-0 shadow-lg">
                  <CardContent className="p-6">
                    <div className="flex items-start justify-between mb-4">
                      <div className="flex items-start space-x-4">
                        <div className="p-3 bg-gradient-to-r from-cyan-500 to-cyan-600 rounded-xl">
                          <Pill className="h-6 w-6 text-white" />
                        </div>
                        <div className="flex-1">
                          <div className="flex items-center space-x-2 mb-2">
                            <h3 className="text-lg font-semibold text-slate-900 dark:text-white">
                              {prescription.medication} {prescription.dosage}
                            </h3>
                            {getStatusBadge(prescription.status)}
                          </div>
                          <div className="flex items-center space-x-4 text-sm text-slate-600 dark:text-slate-400 mb-2">
                            <span className="flex items-center space-x-1">
                              <User className="h-4 w-4" />
                              <span>{prescription.patientName} (ID: {prescription.patientId})</span>
                            </span>
                            <span className="flex items-center space-x-1">
                              <Calendar className="h-4 w-4" />
                              <span>{prescription.date}</span>
                            </span>
                            <span className="flex items-center space-x-1">
                              <Stethoscope className="h-4 w-4" />
                              <span>{prescription.prescribedBy}</span>
                            </span>
                          </div>
                          <div className="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm mb-3">
                            <div>
                              <span className="font-medium text-slate-900 dark:text-white">Frequency:</span>
                              <span className="ml-2 text-slate-600 dark:text-slate-400">{prescription.frequency}</span>
                            </div>
                            <div>
                              <span className="font-medium text-slate-900 dark:text-white">Duration:</span>
                              <span className="ml-2 text-slate-600 dark:text-slate-400">{prescription.duration}</span>
                            </div>
                            <div>
                              <span className="font-medium text-slate-900 dark:text-white">Refills:</span>
                              <span className="ml-2 text-slate-600 dark:text-slate-400">
                                {prescription.remainingRefills}/{prescription.refills}
                              </span>
                            </div>
                          </div>
                          <p className="text-slate-700 dark:text-slate-300 text-sm mb-3">
                            <strong>Instructions:</strong> {prescription.instructions}
                          </p>
                        </div>
                      </div>
                    </div>

                    {/* Side Effects and Interactions */}
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                      {prescription.sideEffects.length > 0 && (
                        <div>
                          <p className="text-sm font-medium text-slate-900 dark:text-white mb-2">Potential Side Effects:</p>
                          <div className="flex flex-wrap gap-1">
                            {prescription.sideEffects.map((effect, index) => (
                              <Badge key={index} variant="outline" className="text-xs text-orange-600 border-orange-600">
                                {effect}
                              </Badge>
                            ))}
                          </div>
                        </div>
                      )}
                      {prescription.interactions.length > 0 && (
                        <div>
                          <p className="text-sm font-medium text-slate-900 dark:text-white mb-2">Drug Interactions:</p>
                          <div className="flex flex-wrap gap-1">
                            {prescription.interactions.map((interaction, index) => (
                              <Badge key={index} variant="outline" className="text-xs text-red-600 border-red-600">
                                {interaction}
                              </Badge>
                            ))}
                          </div>
                        </div>
                      )}
                    </div>

                    <div className="flex gap-2">
                      <Button variant="outline" size="sm">
                        <Eye className="h-4 w-4 mr-2" />
                        View
                      </Button>
                      <Button variant="outline" size="sm">
                        <Edit className="h-4 w-4 mr-2" />
                        Edit
                      </Button>
                      <Button variant="outline" size="sm">
                        <Printer className="h-4 w-4 mr-2" />
                        Print
                      </Button>
                      <Button variant="outline" size="sm">
                        <Download className="h-4 w-4 mr-2" />
                        Download
                      </Button>
                      {prescription.status === 'active' && prescription.remainingRefills > 0 && (
                        <Button size="sm" className="bg-green-600 hover:bg-green-700">
                          <Pill className="h-4 w-4 mr-2" />
                          Refill
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
