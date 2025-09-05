import React, { useState } from 'react';
import { Head, Link } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import {
  MessageSquare,
  Search,
  Plus,
  Filter,
  Send,
  Reply,
  Archive,
  Star,
  StarOff,
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
  Mail,
  Phone,
  Video,
  Paperclip,
  MoreVertical
} from 'lucide-react';

interface Message {
  id: number;
  patientName: string;
  patientId: number;
  subject: string;
  content: string;
  type: 'email' | 'sms' | 'video' | 'phone' | 'in-app';
  status: 'unread' | 'read' | 'replied' | 'archived';
  priority: 'low' | 'normal' | 'high' | 'urgent';
  date: string;
  time: string;
  isStarred: boolean;
  attachments: number;
}

interface MessagesPageProps {
  auth: {
    user: {
      id: number;
      name: string;
      email: string;
      role: string;
    };
  };
}

export default function MessagesPage({ auth }: MessagesPageProps) {
  const [sidebarOpen, setSidebarOpen] = useState(false);
  const [searchTerm, setSearchTerm] = useState('');
  const [selectedType, setSelectedType] = useState('all');
  const [selectedStatus, setSelectedStatus] = useState('all');
  const [selectedMessage, setSelectedMessage] = useState<Message | null>(null);

  // Mock messages data
  const messages: Message[] = [
    {
      id: 1,
      patientName: 'John Doe',
      patientId: 1001,
      subject: 'Medication Question',
      content: 'Dr. Smith, I have a question about my blood pressure medication. I\'ve been experiencing some dizziness in the morning. Should I be concerned?',
      type: 'email',
      status: 'unread',
      priority: 'normal',
      date: '2024-01-20',
      time: '09:30',
      isStarred: false,
      attachments: 0
    },
    {
      id: 2,
      patientName: 'Jane Smith',
      patientId: 1002,
      subject: 'Appointment Reschedule Request',
      content: 'Hi Dr. Johnson, I need to reschedule my appointment for next week. Would it be possible to move it to Friday afternoon instead of Tuesday morning?',
      type: 'sms',
      status: 'read',
      priority: 'low',
      date: '2024-01-19',
      time: '14:15',
      isStarred: true,
      attachments: 0
    },
    {
      id: 3,
      patientName: 'Michael Johnson',
      patientId: 1003,
      subject: 'Urgent: Lab Results',
      content: 'Dr. Williams, I received my lab results and I\'m concerned about the cholesterol levels. The numbers seem quite high. Should I schedule an appointment to discuss this?',
      type: 'in-app',
      status: 'unread',
      priority: 'high',
      date: '2024-01-19',
      time: '16:45',
      isStarred: false,
      attachments: 2
    },
    {
      id: 4,
      patientName: 'Sarah Wilson',
      patientId: 1004,
      subject: 'Follow-up on Treatment',
      content: 'Thank you for the migraine treatment plan. I\'ve been following the medication schedule and the frequency has reduced significantly. Just wanted to update you on my progress.',
      type: 'email',
      status: 'replied',
      priority: 'normal',
      date: '2024-01-18',
      time: '11:20',
      isStarred: false,
      attachments: 1
    },
    {
      id: 5,
      patientName: 'Robert Brown',
      patientId: 1005,
      subject: 'Emergency: Severe Chest Pain',
      content: 'Dr. Smith, I\'m experiencing severe chest pain and shortness of breath. I\'ve taken my emergency medication but the pain persists. Should I go to the emergency room?',
      type: 'phone',
      status: 'unread',
      priority: 'urgent',
      date: '2024-01-20',
      time: '08:15',
      isStarred: false,
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
      current: true,
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

  const filteredMessages = messages.filter(message => {
    const matchesSearch = message.subject.toLowerCase().includes(searchTerm.toLowerCase()) ||
                         message.patientName.toLowerCase().includes(searchTerm.toLowerCase()) ||
                         message.content.toLowerCase().includes(searchTerm.toLowerCase());
    const matchesType = selectedType === 'all' || message.type === selectedType;
    const matchesStatus = selectedStatus === 'all' || message.status === selectedStatus;
    return matchesSearch && matchesType && matchesStatus;
  });

  const getStatusBadge = (status: string) => {
    switch (status) {
      case 'unread':
        return <Badge className="bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">Unread</Badge>;
      case 'read':
        return <Badge className="bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200">Read</Badge>;
      case 'replied':
        return <Badge className="bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Replied</Badge>;
      case 'archived':
        return <Badge className="bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">Archived</Badge>;
      default:
        return <Badge>Unknown</Badge>;
    }
  };

  const getPriorityBadge = (priority: string) => {
    switch (priority) {
      case 'urgent':
        return <Badge className="bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">Urgent</Badge>;
      case 'high':
        return <Badge className="bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200">High</Badge>;
      case 'normal':
        return <Badge className="bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">Normal</Badge>;
      case 'low':
        return <Badge className="bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200">Low</Badge>;
      default:
        return <Badge>Unknown</Badge>;
    }
  };

  const getTypeIcon = (type: string) => {
    switch (type) {
      case 'email':
        return <Mail className="h-4 w-4 text-blue-600" />;
      case 'sms':
        return <MessageSquare className="h-4 w-4 text-green-600" />;
      case 'video':
        return <Video className="h-4 w-4 text-purple-600" />;
      case 'phone':
        return <Phone className="h-4 w-4 text-orange-600" />;
      case 'in-app':
        return <MessageSquare className="h-4 w-4 text-indigo-600" />;
      default:
        return <MessageSquare className="h-4 w-4 text-gray-600" />;
    }
  };

  return (
    <>
      <Head title="Messages - MediNext" />

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
                  <h1 className="text-2xl font-bold text-slate-900 dark:text-white">Messages</h1>
                  <p className="text-sm text-slate-600 dark:text-slate-400">Communicate with your patients</p>
                </div>
              </div>

              <div className="flex items-center space-x-4">
                <Button className="px-4">
                  <Plus className="h-4 w-4 mr-2" />
                  New Message
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
              <Card className="border-0 shadow-lg bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20">
                <CardContent className="p-6">
                  <div className="flex items-center justify-between">
                    <div>
                      <p className="text-sm font-medium text-slate-600 dark:text-slate-400">Total Messages</p>
                      <p className="text-3xl font-bold text-green-600 dark:text-green-400">{messages.length}</p>
                    </div>
                    <MessageSquare className="h-8 w-8 text-green-600 dark:text-green-400" />
                  </div>
                </CardContent>
              </Card>

              <Card className="border-0 shadow-lg bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20">
                <CardContent className="p-6">
                  <div className="flex items-center justify-between">
                    <div>
                      <p className="text-sm font-medium text-slate-600 dark:text-slate-400">Unread</p>
                      <p className="text-3xl font-bold text-blue-600 dark:text-blue-400">
                        {messages.filter(m => m.status === 'unread').length}
                      </p>
                    </div>
                    <AlertCircle className="h-8 w-8 text-blue-600 dark:text-blue-400" />
                  </div>
                </CardContent>
              </Card>

              <Card className="border-0 shadow-lg bg-gradient-to-br from-red-50 to-red-100 dark:from-red-900/20 dark:to-red-800/20">
                <CardContent className="p-6">
                  <div className="flex items-center justify-between">
                    <div>
                      <p className="text-sm font-medium text-slate-600 dark:text-slate-400">Urgent</p>
                      <p className="text-3xl font-bold text-red-600 dark:text-red-400">
                        {messages.filter(m => m.priority === 'urgent').length}
                      </p>
                    </div>
                    <AlertTriangle className="h-8 w-8 text-red-600 dark:text-red-400" />
                  </div>
                </CardContent>
              </Card>

              <Card className="border-0 shadow-lg bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20">
                <CardContent className="p-6">
                  <div className="flex items-center justify-between">
                    <div>
                      <p className="text-sm font-medium text-slate-600 dark:text-slate-400">This Week</p>
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
                <div className="flex flex-col lg:flex-row gap-4">
                  <div className="flex-1 relative">
                    <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-slate-400" />
                    <Input
                      placeholder="Search messages by subject, patient, or content..."
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
                      <option value="email">Email</option>
                      <option value="sms">SMS</option>
                      <option value="video">Video</option>
                      <option value="phone">Phone</option>
                      <option value="in-app">In-App</option>
                    </select>
                    <select
                      value={selectedStatus}
                      onChange={(e) => setSelectedStatus(e.target.value)}
                      className="px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white"
                    >
                      <option value="all">All Status</option>
                      <option value="unread">Unread</option>
                      <option value="read">Read</option>
                      <option value="replied">Replied</option>
                      <option value="archived">Archived</option>
                    </select>
                  </div>
                </div>
              </CardContent>
            </Card>

            {/* Messages List */}
            <div className="space-y-4">
              {filteredMessages.map((message) => (
                <Card key={message.id} className={`hover:shadow-xl transition-all duration-300 border-0 shadow-lg ${
                  message.status === 'unread' ? 'ring-2 ring-blue-200 dark:ring-blue-800' : ''
                }`}>
                  <CardContent className="p-6">
                    <div className="flex items-start justify-between mb-4">
                      <div className="flex items-start space-x-4">
                        <div className="p-3 bg-gradient-to-r from-green-500 to-green-600 rounded-xl">
                          {getTypeIcon(message.type)}
                        </div>
                        <div className="flex-1">
                          <div className="flex items-center space-x-2 mb-2">
                            <h3 className="text-lg font-semibold text-slate-900 dark:text-white">{message.subject}</h3>
                            {message.isStarred && <Star className="h-4 w-4 text-yellow-500 fill-current" />}
                            {getPriorityBadge(message.priority)}
                            {getStatusBadge(message.status)}
                          </div>
                          <div className="flex items-center space-x-4 text-sm text-slate-600 dark:text-slate-400 mb-3">
                            <span className="flex items-center space-x-1">
                              <User className="h-4 w-4" />
                              <span>{message.patientName} (ID: {message.patientId})</span>
                            </span>
                            <span className="flex items-center space-x-1">
                              <Calendar className="h-4 w-4" />
                              <span>{message.date} at {message.time}</span>
                            </span>
                            {message.attachments > 0 && (
                              <span className="flex items-center space-x-1">
                                <Paperclip className="h-4 w-4" />
                                <span>{message.attachments} attachment{message.attachments > 1 ? 's' : ''}</span>
                              </span>
                            )}
                          </div>
                          <p className="text-slate-700 dark:text-slate-300 text-sm mb-3 line-clamp-2">
                            {message.content}
                          </p>
                        </div>
                      </div>
                    </div>

                    <div className="flex gap-2">
                      <Button variant="outline" size="sm">
                        <Eye className="h-4 w-4 mr-2" />
                        View
                      </Button>
                      <Button variant="outline" size="sm">
                        <Reply className="h-4 w-4 mr-2" />
                        Reply
                      </Button>
                      <Button variant="outline" size="sm">
                        {message.isStarred ? <StarOff className="h-4 w-4 mr-2" /> : <Star className="h-4 w-4 mr-2" />}
                        {message.isStarred ? 'Unstar' : 'Star'}
                      </Button>
                      <Button variant="outline" size="sm">
                        <Archive className="h-4 w-4 mr-2" />
                        Archive
                      </Button>
                      {message.priority === 'urgent' && (
                        <Button size="sm" className="bg-red-600 hover:bg-red-700">
                          <AlertTriangle className="h-4 w-4 mr-2" />
                          Urgent Response
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
