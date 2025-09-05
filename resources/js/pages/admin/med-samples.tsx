import React, { useState } from 'react';
import { Head, Link } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import {
  ClipboardList,
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
  MessageSquare,
  Microscope,
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
  Package,
  Star,
  StarOff,
  Archive,
  Trash2
} from 'lucide-react';

interface MedSample {
  id: number;
  medicationName: string;
  manufacturer: string;
  representative: string;
  representativeEmail: string;
  representativePhone: string;
  sampleType: 'tablet' | 'capsule' | 'injection' | 'cream' | 'syrup' | 'other';
  quantity: number;
  expiryDate: string;
  receivedDate: string;
  status: 'available' | 'distributed' | 'expired' | 'returned';
  description: string;
  indications: string[];
  dosage: string;
  sideEffects: string[];
  isStarred: boolean;
  notes: string;
}

interface MedSamplesPageProps {
  auth: {
    user: {
      id: number;
      name: string;
      email: string;
      role: string;
    };
  };
}

export default function MedSamplesPage({ auth }: MedSamplesPageProps) {
  const [sidebarOpen, setSidebarOpen] = useState(false);
  const [searchTerm, setSearchTerm] = useState('');
  const [selectedType, setSelectedType] = useState('all');
  const [selectedStatus, setSelectedStatus] = useState('all');

  // Mock medication samples data
  const medSamples: MedSample[] = [
    {
      id: 1,
      medicationName: 'Lisinopril 10mg',
      manufacturer: 'PharmaCorp Inc.',
      representative: 'John Smith',
      representativeEmail: 'john.smith@pharmacorp.com',
      representativePhone: '+1 (555) 123-4567',
      sampleType: 'tablet',
      quantity: 30,
      expiryDate: '2024-12-31',
      receivedDate: '2024-01-15',
      status: 'available',
      description: 'ACE inhibitor for hypertension management',
      indications: ['Hypertension', 'Heart Failure'],
      dosage: '10mg once daily',
      sideEffects: ['Dry cough', 'Dizziness', 'Hyperkalemia'],
      isStarred: false,
      notes: 'High-quality sample, good for patient education'
    },
    {
      id: 2,
      medicationName: 'Metformin 500mg',
      manufacturer: 'MediLife Pharmaceuticals',
      representative: 'Sarah Johnson',
      representativeEmail: 'sarah.j@medilife.com',
      representativePhone: '+1 (555) 234-5678',
      sampleType: 'tablet',
      quantity: 60,
      expiryDate: '2025-06-30',
      receivedDate: '2024-01-12',
      status: 'available',
      description: 'First-line treatment for Type 2 Diabetes',
      indications: ['Type 2 Diabetes', 'PCOS'],
      dosage: '500mg twice daily with meals',
      sideEffects: ['Nausea', 'Diarrhea', 'Metallic taste'],
      isStarred: true,
      notes: 'Excellent for new diabetes patients'
    },
    {
      id: 3,
      medicationName: 'Atorvastatin 20mg',
      manufacturer: 'CardioMed Solutions',
      representative: 'Michael Brown',
      representativeEmail: 'michael.b@cardiomed.com',
      representativePhone: '+1 (555) 345-6789',
      sampleType: 'tablet',
      quantity: 28,
      expiryDate: '2024-09-15',
      receivedDate: '2024-01-10',
      status: 'distributed',
      description: 'HMG-CoA reductase inhibitor for cholesterol management',
      indications: ['Hypercholesterolemia', 'Cardiovascular Disease Prevention'],
      dosage: '20mg once daily in the evening',
      sideEffects: ['Muscle pain', 'Liver enzyme elevation', 'Digestive issues'],
      isStarred: false,
      notes: 'Distributed to 5 patients for trial'
    },
    {
      id: 4,
      medicationName: 'Sumatriptan 50mg',
      manufacturer: 'NeuroPharm Ltd.',
      representative: 'Emily Davis',
      representativeEmail: 'emily.d@neuropharm.com',
      representativePhone: '+1 (555) 456-7890',
      sampleType: 'tablet',
      quantity: 9,
      expiryDate: '2024-03-20',
      receivedDate: '2024-01-08',
      status: 'expired',
      description: 'Triptan for acute migraine treatment',
      indications: ['Migraine', 'Cluster Headache'],
      dosage: '50mg at onset of migraine, max 2 doses per day',
      sideEffects: ['Chest tightness', 'Dizziness', 'Nausea'],
      isStarred: false,
      notes: 'Expired - needs to be returned'
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
      current: false,
      color: 'text-pink-600',
      bgColor: 'bg-pink-50 dark:bg-pink-900/20'
    },
    {
      name: 'Med Samples',
      href: '/doctor/med-samples',
      icon: ClipboardList,
      current: true,
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

  const filteredSamples = medSamples.filter(sample => {
    const matchesSearch = sample.medicationName.toLowerCase().includes(searchTerm.toLowerCase()) ||
                         sample.manufacturer.toLowerCase().includes(searchTerm.toLowerCase()) ||
                         sample.representative.toLowerCase().includes(searchTerm.toLowerCase());
    const matchesType = selectedType === 'all' || sample.sampleType === selectedType;
    const matchesStatus = selectedStatus === 'all' || sample.status === selectedStatus;
    return matchesSearch && matchesType && matchesStatus;
  });

  const getStatusBadge = (status: string) => {
    switch (status) {
      case 'available':
        return <Badge className="bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Available</Badge>;
      case 'distributed':
        return <Badge className="bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">Distributed</Badge>;
      case 'expired':
        return <Badge className="bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">Expired</Badge>;
      case 'returned':
        return <Badge className="bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200">Returned</Badge>;
      default:
        return <Badge>Unknown</Badge>;
    }
  };

  const getTypeBadge = (type: string) => {
    switch (type) {
      case 'tablet':
        return <Badge variant="outline" className="text-blue-600 border-blue-600">Tablet</Badge>;
      case 'capsule':
        return <Badge variant="outline" className="text-green-600 border-green-600">Capsule</Badge>;
      case 'injection':
        return <Badge variant="outline" className="text-red-600 border-red-600">Injection</Badge>;
      case 'cream':
        return <Badge variant="outline" className="text-purple-600 border-purple-600">Cream</Badge>;
      case 'syrup':
        return <Badge variant="outline" className="text-orange-600 border-orange-600">Syrup</Badge>;
      case 'other':
        return <Badge variant="outline" className="text-gray-600 border-gray-600">Other</Badge>;
      default:
        return <Badge variant="outline">Unknown</Badge>;
    }
  };

  const isExpiringSoon = (expiryDate: string) => {
    const expiry = new Date(expiryDate);
    const today = new Date();
    const diffTime = expiry.getTime() - today.getTime();
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    return diffDays <= 30 && diffDays > 0;
  };

  const isExpired = (expiryDate: string) => {
    const expiry = new Date(expiryDate);
    const today = new Date();
    return expiry < today;
  };

  return (
    <>
      <Head title="Medication Samples - MediNext" />

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
                  <h1 className="text-2xl font-bold text-slate-900 dark:text-white">Medication Samples</h1>
                  <p className="text-sm text-slate-600 dark:text-slate-400">Manage pharmaceutical samples from representatives</p>
                </div>
              </div>

              <div className="flex items-center space-x-4">
                <Button className="px-4">
                  <Plus className="h-4 w-4 mr-2" />
                  Add Sample
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
              <Card className="border-0 shadow-lg bg-gradient-to-br from-yellow-50 to-yellow-100 dark:from-yellow-900/20 dark:to-yellow-800/20">
                <CardContent className="p-6">
                  <div className="flex items-center justify-between">
                    <div>
                      <p className="text-sm font-medium text-slate-600 dark:text-slate-400">Total Samples</p>
                      <p className="text-3xl font-bold text-yellow-600 dark:text-yellow-400">{medSamples.length}</p>
                    </div>
                    <Package className="h-8 w-8 text-yellow-600 dark:text-yellow-400" />
                  </div>
                </CardContent>
              </Card>

              <Card className="border-0 shadow-lg bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20">
                <CardContent className="p-6">
                  <div className="flex items-center justify-between">
                    <div>
                      <p className="text-sm font-medium text-slate-600 dark:text-slate-400">Available</p>
                      <p className="text-3xl font-bold text-green-600 dark:text-green-400">
                        {medSamples.filter(s => s.status === 'available').length}
                      </p>
                    </div>
                    <CheckCircle className="h-8 w-8 text-green-600 dark:text-green-400" />
                  </div>
                </CardContent>
              </Card>

              <Card className="border-0 shadow-lg bg-gradient-to-br from-orange-50 to-orange-100 dark:from-orange-900/20 dark:to-orange-800/20">
                <CardContent className="p-6">
                  <div className="flex items-center justify-between">
                    <div>
                      <p className="text-sm font-medium text-slate-600 dark:text-slate-400">Expiring Soon</p>
                      <p className="text-3xl font-bold text-orange-600 dark:text-orange-400">
                        {medSamples.filter(s => isExpiringSoon(s.expiryDate)).length}
                      </p>
                    </div>
                    <AlertTriangle className="h-8 w-8 text-orange-600 dark:text-orange-400" />
                  </div>
                </CardContent>
              </Card>

              <Card className="border-0 shadow-lg bg-gradient-to-br from-red-50 to-red-100 dark:from-red-900/20 dark:to-red-800/20">
                <CardContent className="p-6">
                  <div className="flex items-center justify-between">
                    <div>
                      <p className="text-sm font-medium text-slate-600 dark:text-slate-400">Expired</p>
                      <p className="text-3xl font-bold text-red-600 dark:text-red-400">
                        {medSamples.filter(s => isExpired(s.expiryDate)).length}
                      </p>
                    </div>
                    <AlertCircle className="h-8 w-8 text-red-600 dark:text-red-400" />
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
                      placeholder="Search samples by medication, manufacturer, or representative..."
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
                      <option value="tablet">Tablet</option>
                      <option value="capsule">Capsule</option>
                      <option value="injection">Injection</option>
                      <option value="cream">Cream</option>
                      <option value="syrup">Syrup</option>
                      <option value="other">Other</option>
                    </select>
                    <select
                      value={selectedStatus}
                      onChange={(e) => setSelectedStatus(e.target.value)}
                      className="px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white"
                    >
                      <option value="all">All Status</option>
                      <option value="available">Available</option>
                      <option value="distributed">Distributed</option>
                      <option value="expired">Expired</option>
                      <option value="returned">Returned</option>
                    </select>
                  </div>
                </div>
              </CardContent>
            </Card>

            {/* Samples List */}
            <div className="space-y-4">
              {filteredSamples.map((sample) => (
                <Card key={sample.id} className={`hover:shadow-xl transition-all duration-300 border-0 shadow-lg ${
                  isExpired(sample.expiryDate) ? 'ring-2 ring-red-200 dark:ring-red-800' :
                  isExpiringSoon(sample.expiryDate) ? 'ring-2 ring-orange-200 dark:ring-orange-800' : ''
                }`}>
                  <CardContent className="p-6">
                    <div className="flex items-start justify-between mb-4">
                      <div className="flex items-start space-x-4">
                        <div className="p-3 bg-gradient-to-r from-yellow-500 to-yellow-600 rounded-xl">
                          <Pill className="h-6 w-6 text-white" />
                        </div>
                        <div className="flex-1">
                          <div className="flex items-center space-x-2 mb-2">
                            <h3 className="text-lg font-semibold text-slate-900 dark:text-white">{sample.medicationName}</h3>
                            {sample.isStarred && <Star className="h-4 w-4 text-yellow-500 fill-current" />}
                            {getTypeBadge(sample.sampleType)}
                            {getStatusBadge(sample.status)}
                            {isExpired(sample.expiryDate) && (
                              <Badge className="bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">Expired</Badge>
                            )}
                            {isExpiringSoon(sample.expiryDate) && !isExpired(sample.expiryDate) && (
                              <Badge className="bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200">Expiring Soon</Badge>
                            )}
                          </div>
                          <div className="flex items-center space-x-4 text-sm text-slate-600 dark:text-slate-400 mb-3">
                            <span className="flex items-center space-x-1">
                              <Package className="h-4 w-4" />
                              <span>{sample.manufacturer}</span>
                            </span>
                            <span className="flex items-center space-x-1">
                              <User className="h-4 w-4" />
                              <span>{sample.representative}</span>
                            </span>
                            <span className="flex items-center space-x-1">
                              <Calendar className="h-4 w-4" />
                              <span>Expires: {sample.expiryDate}</span>
                            </span>
                            <span className="flex items-center space-x-1">
                              <Package className="h-4 w-4" />
                              <span>Qty: {sample.quantity}</span>
                            </span>
                          </div>
                          <p className="text-slate-700 dark:text-slate-300 text-sm mb-3">{sample.description}</p>

                          <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mb-3">
                            <div>
                              <p className="text-sm font-medium text-slate-900 dark:text-white mb-1">Indications:</p>
                              <div className="flex flex-wrap gap-1">
                                {sample.indications.map((indication, index) => (
                                  <Badge key={index} variant="outline" className="text-xs">
                                    {indication}
                                  </Badge>
                                ))}
                              </div>
                            </div>
                            <div>
                              <p className="text-sm font-medium text-slate-900 dark:text-white mb-1">Side Effects:</p>
                              <div className="flex flex-wrap gap-1">
                                {sample.sideEffects.slice(0, 3).map((effect, index) => (
                                  <Badge key={index} variant="outline" className="text-xs text-orange-600 border-orange-600">
                                    {effect}
                                  </Badge>
                                ))}
                                {sample.sideEffects.length > 3 && (
                                  <Badge variant="outline" className="text-xs">
                                    +{sample.sideEffects.length - 3} more
                                  </Badge>
                                )}
                              </div>
                            </div>
                          </div>

                          <div className="text-sm text-slate-600 dark:text-slate-400">
                            <p><strong>Dosage:</strong> {sample.dosage}</p>
                            {sample.notes && <p><strong>Notes:</strong> {sample.notes}</p>}
                          </div>
                        </div>
                      </div>
                    </div>

                    <div className="flex gap-2">
                      <Button variant="outline" size="sm">
                        <Eye className="h-4 w-4 mr-2" />
                        View Details
                      </Button>
                      <Button variant="outline" size="sm">
                        <Edit className="h-4 w-4 mr-2" />
                        Edit
                      </Button>
                      <Button variant="outline" size="sm">
                        {sample.isStarred ? <StarOff className="h-4 w-4 mr-2" /> : <Star className="h-4 w-4 mr-2" />}
                        {sample.isStarred ? 'Unstar' : 'Star'}
                      </Button>
                      {sample.status === 'available' && (
                        <Button size="sm" className="bg-green-600 hover:bg-green-700">
                          <Package className="h-4 w-4 mr-2" />
                          Distribute
                        </Button>
                      )}
                      {isExpired(sample.expiryDate) && (
                        <Button size="sm" className="bg-red-600 hover:bg-red-700">
                          <Trash2 className="h-4 w-4 mr-2" />
                          Return
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
