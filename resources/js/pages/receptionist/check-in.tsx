import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import {
    ClipboardList,
    Search,
    CheckCircle,
    XCircle,
    Clock,
    User,
    Calendar,
    Building2,
    Shield
} from 'lucide-react';
import { receptionistCheckIn } from '@/routes';
import { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Receptionist Dashboard',
        href: '/receptionist/dashboard',
    },
    {
        title: 'Check-in/Check-out',
        href: receptionistCheckIn(),
    },
];

interface CheckInProps {
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

export default function CheckIn({
    user,
    permissions = []
}: CheckInProps) {
    const hasPermission = (permission: string) => permissions.includes(permission);
    const appointments = [
        {
            id: 1,
            patient: 'John Doe',
            doctor: 'Dr. Sarah Johnson',
            appointmentTime: '09:00 AM',
            type: 'Consultation',
            status: 'Scheduled',
            phone: '+1 (555) 123-4567',
            insurance: 'Blue Cross'
        },
        {
            id: 2,
            patient: 'Jane Smith',
            doctor: 'Dr. Michael Brown',
            appointmentTime: '10:30 AM',
            type: 'Follow-up',
            status: 'Scheduled',
            phone: '+1 (555) 234-5678',
            insurance: 'Aetna'
        },
        {
            id: 3,
            patient: 'Bob Johnson',
            doctor: 'Dr. Emily Davis',
            appointmentTime: '11:15 AM',
            type: 'Check-up',
            status: 'Checked In',
            phone: '+1 (555) 345-6789',
            insurance: 'Cigna'
        },
        {
            id: 4,
            patient: 'Alice Brown',
            doctor: 'Dr. James Wilson',
            appointmentTime: '02:00 PM',
            type: 'Consultation',
            status: 'Scheduled',
            phone: '+1 (555) 456-7890',
            insurance: 'Medicare'
        }
    ];

    const getStatusColor = (status: string) => {
        switch (status) {
            case 'Scheduled': return 'default';
            case 'Checked In': return 'secondary';
            case 'Completed': return 'default';
            case 'Cancelled': return 'destructive';
            default: return 'secondary';
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Check-in/Check-out - Medinext">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&family=instrument-sans:400,500,600" rel="stylesheet" />
            </Head>
            <div className="min-h-screen bg-gradient-to-br from-slate-50 via-white to-blue-50 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900">
                <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
                    {/* Modern Header */}
                    <div className="relative overflow-hidden rounded-2xl bg-gradient-to-r from-indigo-600 to-purple-600 p-8 text-white shadow-xl">
                        <div className="absolute inset-0 bg-black/10"></div>
                        <div className="relative flex items-center justify-between">
                            <div>
                                <h1 className="text-3xl font-bold tracking-tight">Check-in/Check-out</h1>
                                <p className="mt-2 text-indigo-100">
                                    {user?.clinic?.name || 'No Clinic'} • Manage patient check-in and check-out process
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
                                {hasPermission('manage_checkin') && (
                                    <Button className="bg-white/20 hover:bg-white/30 text-white border-white/30 hover:border-white/40">
                                        <Search className="mr-2 h-4 w-4" />
                                        Search Patient
                                    </Button>
                                )}
                            </div>
                        </div>
                        {/* Decorative elements */}
                        <div className="absolute -top-4 -right-4 w-24 h-24 bg-white/10 rounded-full"></div>
                        <div className="absolute -bottom-2 -left-2 w-16 h-16 bg-white/5 rounded-full"></div>
                    </div>

                    {/* Quick Check-in */}
                    <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                        <CardHeader>
                            <CardTitle className="flex items-center text-lg font-semibold text-slate-900 dark:text-white">
                                <div className="p-1 bg-indigo-100 dark:bg-indigo-900/20 rounded-md mr-2">
                                    <ClipboardList className="h-5 w-5 text-indigo-600 dark:text-indigo-400" />
                                </div>
                                Quick Check-in
                            </CardTitle>
                            <CardDescription className="text-slate-600 dark:text-slate-300">
                                Check in patients for their appointments
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid gap-4 md:grid-cols-2">
                                <div className="space-y-2">
                                    <Label htmlFor="patient-search" className="text-sm font-medium text-slate-700 dark:text-slate-300">Patient Search</Label>
                                    <Input id="patient-search" placeholder="Search by name, phone, or ID" className="border-slate-200 dark:border-slate-700 focus:border-indigo-500 dark:focus:border-indigo-400" />
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="appointment-time" className="text-sm font-medium text-slate-700 dark:text-slate-300">Appointment Time</Label>
                                    <Input id="appointment-time" type="time" className="border-slate-200 dark:border-slate-700 focus:border-indigo-500 dark:focus:border-indigo-400" />
                                </div>
                            </div>
                            <div className="flex space-x-2">
                                <Button className="hover:bg-green-600 hover:border-green-600 transition-all duration-200">
                                    <CheckCircle className="mr-2 h-4 w-4" />
                                    Check In
                                </Button>
                                <Button variant="outline" className="border-slate-200 dark:border-slate-700 hover:bg-red-50 dark:hover:bg-red-900/20 hover:border-red-300 dark:hover:border-red-600 transition-all duration-200">
                                    <XCircle className="mr-2 h-4 w-4" />
                                    Check Out
                                </Button>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Today's Appointments */}
                    <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                        <CardHeader>
                            <CardTitle className="text-lg font-semibold text-slate-900 dark:text-white">Today's Appointments</CardTitle>
                            <CardDescription className="text-slate-600 dark:text-slate-300">
                                Manage check-in status for today's appointments
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                {appointments.map((appointment) => (
                                    <div key={appointment.id} className="flex items-center justify-between p-4 border border-slate-200 dark:border-slate-700 rounded-lg hover:shadow-md transition-all duration-200 bg-slate-50 dark:bg-slate-700/50">
                                        <div className="flex items-center space-x-4">
                                            <div className="flex-shrink-0">
                                                <div className="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-500 text-white rounded-full flex items-center justify-center shadow-lg">
                                                    <User className="h-5 w-5" />
                                                </div>
                                            </div>
                                            <div>
                                                <h3 className="font-medium text-slate-900 dark:text-white">{appointment.patient}</h3>
                                                <p className="text-sm text-slate-600 dark:text-slate-400">{appointment.doctor}</p>
                                                <div className="flex items-center space-x-4 mt-1">
                                                    <span className="text-xs text-slate-500 dark:text-slate-500 flex items-center">
                                                        <Calendar className="mr-1 h-3 w-3" />
                                                        {appointment.appointmentTime}
                                                    </span>
                                                    <span className="text-xs text-slate-500 dark:text-slate-500">
                                                        {appointment.phone}
                                                    </span>
                                                    <span className="text-xs text-slate-500 dark:text-slate-500">
                                                        {appointment.insurance}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div className="flex items-center space-x-4">
                                            <div className="text-right">
                                                <Badge variant="outline" className="mb-1 border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300">{appointment.type}</Badge>
                                                <div>
                                                    <Badge variant={getStatusColor(appointment.status)} className="border-0">
                                                        {appointment.status}
                                                    </Badge>
                                                </div>
                                            </div>
                                            {hasPermission('manage_checkin') && (
                                                <div className="flex items-center space-x-2">
                                                    {appointment.status === 'Scheduled' && (
                                                        <Button variant="outline" size="sm" className="border-green-300 dark:border-green-700 hover:bg-green-100 dark:hover:bg-green-900/20 hover:border-green-400 dark:hover:border-green-600 transition-all duration-200">
                                                            <CheckCircle className="h-4 w-4 text-green-600 dark:text-green-400" />
                                                        </Button>
                                                    )}
                                                    {appointment.status === 'Checked In' && (
                                                        <Button variant="outline" size="sm" className="border-red-300 dark:border-red-700 hover:bg-red-100 dark:hover:bg-red-900/20 hover:border-red-400 dark:hover:border-red-600 transition-all duration-200">
                                                            <XCircle className="h-4 w-4 text-red-600 dark:text-red-400" />
                                                        </Button>
                                                    )}
                                                    <Button variant="outline" size="sm" className="border-orange-300 dark:border-orange-700 hover:bg-orange-100 dark:hover:bg-orange-900/20 hover:border-orange-400 dark:hover:border-orange-600 transition-all duration-200">
                                                        <Clock className="h-4 w-4 text-orange-600 dark:text-orange-400" />
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
