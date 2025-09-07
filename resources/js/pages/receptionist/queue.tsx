import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    Clock,
    User,
    Stethoscope,
    CheckCircle,
    XCircle,
    AlertCircle,
    Play,
    Pause,
    TrendingUp,
    Activity,
    Building2,
    Shield
} from 'lucide-react';
import { receptionistQueue } from '@/routes';
import { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Receptionist Dashboard',
        href: '/receptionist/dashboard',
    },
    {
        title: 'Queue Management',
        href: receptionistQueue(),
    },
];

interface QueueManagementProps {
    user?: {
        id: number;
        name: string;
        email: string;
        role: string;
        clinic_id?: number;
        clinic?: {
            id: number;
            name: string;
        };
    };
    permissions?: string[];
}

export default function QueueManagement({
    user,
    permissions = []
}: QueueManagementProps) {
    const hasPermission = (permission: string) => permissions.includes(permission);
    const queue = [
        {
            id: 1,
            patient: 'John Doe',
            doctor: 'Dr. Sarah Johnson',
            appointmentTime: '09:00 AM',
            checkInTime: '08:45 AM',
            waitTime: '15 min',
            status: 'Waiting',
            priority: 'Normal',
            room: 'Room 101'
        },
        {
            id: 2,
            patient: 'Jane Smith',
            doctor: 'Dr. Michael Brown',
            appointmentTime: '10:30 AM',
            checkInTime: '10:25 AM',
            waitTime: '5 min',
            status: 'In Progress',
            priority: 'Normal',
            room: 'Room 102'
        },
        {
            id: 3,
            patient: 'Bob Johnson',
            doctor: 'Dr. Emily Davis',
            appointmentTime: '11:15 AM',
            checkInTime: '11:10 AM',
            waitTime: '5 min',
            status: 'Waiting',
            priority: 'High',
            room: 'Room 103'
        },
        {
            id: 4,
            patient: 'Alice Brown',
            doctor: 'Dr. James Wilson',
            appointmentTime: '02:00 PM',
            checkInTime: '01:55 PM',
            waitTime: '5 min',
            status: 'Completed',
            priority: 'Normal',
            room: 'Room 104'
        }
    ];

    const getStatusColor = (status: string) => {
        switch (status) {
            case 'Waiting': return 'default';
            case 'In Progress': return 'secondary';
            case 'Completed': return 'default';
            case 'Cancelled': return 'destructive';
            default: return 'secondary';
        }
    };

    const getPriorityColor = (priority: string) => {
        switch (priority) {
            case 'High': return 'destructive';
            case 'Normal': return 'default';
            case 'Low': return 'secondary';
            default: return 'secondary';
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Queue Management - Medinext">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&family=instrument-sans:400,500,600" rel="stylesheet" />
            </Head>
            <div className="min-h-screen bg-gradient-to-br from-slate-50 via-white to-blue-50 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900">
                <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
                    {/* Modern Header */}
                    <div className="relative overflow-hidden rounded-2xl bg-gradient-to-r from-orange-600 to-red-600 p-8 text-white shadow-xl">
                        <div className="absolute inset-0 bg-black/10"></div>
                        <div className="relative flex items-center justify-between">
                            <div>
                                <h1 className="text-3xl font-bold tracking-tight">Queue Management</h1>
                                <p className="mt-2 text-orange-100">
                                    {user?.clinic?.name || 'No Clinic'} • Manage patient queue and waiting times
                                </p>
                            </div>
                            <div className="flex items-center gap-3">
                                <Badge variant="secondary" className="flex items-center gap-1 bg-white/20 text-white border-white/30 hover:bg-white/30">
                                    <Shield className="h-3 w-3" />
                                    Receptionist
                                </Badge>
                                {user?.clinic && (
                                    <Badge variant="secondary" className="flex items-center gap-1 bg-white/20 text-white border-white/30 hover:bg-white/30">
                                        <Building2 className="h-3 w-3" />
                                        {user.clinic.name}
                                    </Badge>
                                )}
                                {hasPermission('manage_queue') && (
                                    <div className="flex space-x-2">
                                        <Button variant="outline" className="bg-white/20 hover:bg-white/30 text-white border-white/30 hover:border-white/40">
                                            <Pause className="mr-2 h-4 w-4" />
                                            Pause Queue
                                        </Button>
                                        <Button className="bg-white/20 hover:bg-white/30 text-white border-white/30 hover:border-white/40">
                                            <Play className="mr-2 h-4 w-4" />
                                            Start Queue
                                        </Button>
                                    </div>
                                )}
                            </div>
                        </div>
                        {/* Decorative elements */}
                        <div className="absolute -top-4 -right-4 w-24 h-24 bg-white/10 rounded-full"></div>
                        <div className="absolute -bottom-2 -left-2 w-16 h-16 bg-white/5 rounded-full"></div>
                    </div>

                    {/* Queue Statistics */}
                    <div className="grid gap-6 md:grid-cols-4">
                        <Card className="relative overflow-hidden border-0 shadow-lg hover:shadow-xl transition-all duration-300 bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20">
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium text-slate-700 dark:text-slate-300">Total in Queue</CardTitle>
                                <div className="p-2 bg-blue-500 rounded-lg">
                                    <Clock className="h-4 w-4 text-white" />
                                </div>
                            </CardHeader>
                            <CardContent>
                                <div className="text-3xl font-bold text-slate-900 dark:text-white">12</div>
                                <div className="flex items-center mt-2">
                                    <TrendingUp className="h-3 w-3 text-blue-500 mr-1" />
                                    <p className="text-xs text-slate-600 dark:text-slate-400">
                                        Active patients
                                    </p>
                                </div>
                            </CardContent>
                        </Card>
                        <Card className="relative overflow-hidden border-0 shadow-lg hover:shadow-xl transition-all duration-300 bg-gradient-to-br from-yellow-50 to-yellow-100 dark:from-yellow-900/20 dark:to-yellow-800/20">
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium text-slate-700 dark:text-slate-300">Waiting</CardTitle>
                                <div className="p-2 bg-yellow-500 rounded-lg">
                                    <User className="h-4 w-4 text-white" />
                                </div>
                            </CardHeader>
                            <CardContent>
                                <div className="text-3xl font-bold text-slate-900 dark:text-white">8</div>
                                <div className="flex items-center mt-2">
                                    <Activity className="h-3 w-3 text-yellow-500 mr-1" />
                                    <p className="text-xs text-slate-600 dark:text-slate-400">
                                        Awaiting doctor
                                    </p>
                                </div>
                            </CardContent>
                        </Card>
                        <Card className="relative overflow-hidden border-0 shadow-lg hover:shadow-xl transition-all duration-300 bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20">
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium text-slate-700 dark:text-slate-300">In Progress</CardTitle>
                                <div className="p-2 bg-purple-500 rounded-lg">
                                    <Stethoscope className="h-4 w-4 text-white" />
                                </div>
                            </CardHeader>
                            <CardContent>
                                <div className="text-3xl font-bold text-slate-900 dark:text-white">3</div>
                                <div className="flex items-center mt-2">
                                    <Activity className="h-3 w-3 text-purple-500 mr-1" />
                                    <p className="text-xs text-slate-600 dark:text-slate-400">
                                        Being seen
                                    </p>
                                </div>
                            </CardContent>
                        </Card>
                        <Card className="relative overflow-hidden border-0 shadow-lg hover:shadow-xl transition-all duration-300 bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20">
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium text-slate-700 dark:text-slate-300">Completed</CardTitle>
                                <div className="p-2 bg-green-500 rounded-lg">
                                    <CheckCircle className="h-4 w-4 text-white" />
                                </div>
                            </CardHeader>
                            <CardContent>
                                <div className="text-3xl font-bold text-slate-900 dark:text-white">1</div>
                                <div className="flex items-center mt-2">
                                    <CheckCircle className="h-3 w-3 text-green-500 mr-1" />
                                    <p className="text-xs text-slate-600 dark:text-slate-400">
                                        Finished today
                                    </p>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                        <CardHeader>
                            <CardTitle className="text-lg font-semibold text-slate-900 dark:text-white">Patient Queue</CardTitle>
                            <CardDescription className="text-slate-600 dark:text-slate-300">
                                Current patient queue status and management
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                {queue.map((patient) => (
                                    <div key={patient.id} className="flex items-center justify-between p-4 border border-slate-200 dark:border-slate-700 rounded-lg hover:shadow-md transition-all duration-200 bg-slate-50 dark:bg-slate-700/50">
                                        <div className="flex items-center space-x-4">
                                            <div className="flex-shrink-0">
                                                <div className="w-10 h-10 bg-gradient-to-br from-orange-500 to-red-500 text-white rounded-full flex items-center justify-center text-sm font-medium shadow-lg">
                                                    {patient.id}
                                                </div>
                                            </div>
                                            <div>
                                                <h3 className="font-medium text-slate-900 dark:text-white">{patient.patient}</h3>
                                                <p className="text-sm text-slate-600 dark:text-slate-400">{patient.doctor}</p>
                                                <div className="flex items-center space-x-4 mt-1">
                                                    <span className="text-xs text-slate-500 dark:text-slate-500">
                                                        Appt: {patient.appointmentTime}
                                                    </span>
                                                    <span className="text-xs text-slate-500 dark:text-slate-500">
                                                        Check-in: {patient.checkInTime}
                                                    </span>
                                                    <span className="text-xs text-slate-500 dark:text-slate-500">
                                                        Wait: {patient.waitTime}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div className="flex items-center space-x-4">
                                            <div className="text-right">
                                                <div className="text-sm font-medium text-slate-900 dark:text-white">{patient.room}</div>
                                                <div className="flex items-center space-x-2 mt-1">
                                                    <Badge variant={getStatusColor(patient.status)} className="border-0">
                                                        {patient.status}
                                                    </Badge>
                                                    <Badge variant={getPriorityColor(patient.priority)} className="border-0">
                                                        {patient.priority}
                                                    </Badge>
                                                </div>
                                            </div>
                                            {hasPermission('manage_queue') && (
                                                <div className="flex items-center space-x-2">
                                                    {patient.status === 'Waiting' && (
                                                        <Button variant="outline" size="sm" className="border-green-300 dark:border-green-700 hover:bg-green-100 dark:hover:bg-green-900/20 hover:border-green-400 dark:hover:border-green-600 transition-all duration-200">
                                                            <CheckCircle className="h-4 w-4 text-green-600 dark:text-green-400" />
                                                        </Button>
                                                    )}
                                                    {patient.status === 'In Progress' && (
                                                        <Button variant="outline" size="sm" className="border-red-300 dark:border-red-700 hover:bg-red-100 dark:hover:bg-red-900/20 hover:border-red-400 dark:hover:border-red-600 transition-all duration-200">
                                                            <XCircle className="h-4 w-4 text-red-600 dark:text-red-400" />
                                                        </Button>
                                                    )}
                                                    <Button variant="outline" size="sm" className="border-orange-300 dark:border-orange-700 hover:bg-orange-100 dark:hover:bg-orange-900/20 hover:border-orange-400 dark:hover:border-orange-600 transition-all duration-200">
                                                        <AlertCircle className="h-4 w-4 text-orange-600 dark:text-orange-400" />
                                                    </Button>
                                                </div>
                                            )}
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AppLayout>
    );
}
