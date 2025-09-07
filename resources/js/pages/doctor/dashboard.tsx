import AppLayout from '@/layouts/app-layout';
import { doctorDashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Calendar,
    Clock,
    FileText,
    Pill,
    AlertCircle,
    CheckCircle,
    TrendingUp,
    Activity,
    ArrowUpRight,
    BarChart3,
    UserCheck,
    Building2,
    Shield
} from 'lucide-react';
import { Link } from '@inertiajs/react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Doctor Dashboard',
        href: doctorDashboard(),
    },
];

interface DoctorDashboardProps {
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
    stats?: {
        todayAppointments: number;
        upcomingAppointments: number;
        totalPatients: number;
        pendingPrescriptions: number;
        activeQueue: number;
        completedEncounters: number;
        recentAppointments: Array<{
            id: number;
            patient_name: string;
            start_at: string;
            status: string;
            type: string;
        }>;
        recentPrescriptions: Array<{
            id: number;
            patient_name: string;
            prescription_number: string;
            status: string;
            issued_at: string;
        }>;
    };
    permissions?: string[];
}

export default function DoctorDashboard({
    user,
    stats = {
        todayAppointments: 0,
        upcomingAppointments: 0,
        totalPatients: 0,
        pendingPrescriptions: 0,
        activeQueue: 0,
        completedEncounters: 0,
        recentAppointments: [],
        recentPrescriptions: []
    },
    permissions = []
}: DoctorDashboardProps) {
    const hasPermission = (permission: string) => permissions.includes(permission);
    const getStatusColor = (status: string) => {
        switch (status) {
            case 'scheduled':
            case 'confirmed':
                return 'bg-blue-100 text-blue-800';
            case 'in_progress':
            case 'checked_in':
                return 'bg-yellow-100 text-yellow-800';
            case 'completed':
                return 'bg-green-100 text-green-800';
            case 'cancelled':
                return 'bg-red-100 text-red-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    };

    const getPrescriptionStatusColor = (status: string) => {
        switch (status) {
            case 'active':
                return 'bg-green-100 text-green-800';
            case 'draft':
                return 'bg-yellow-100 text-yellow-800';
            case 'dispensed':
                return 'bg-blue-100 text-blue-800';
            case 'expired':
                return 'bg-red-100 text-red-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Doctor Dashboard - Medinext">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&family=instrument-sans:400,500,600" rel="stylesheet" />
            </Head>
            <div className="min-h-screen bg-gradient-to-br from-slate-50 via-white to-blue-50 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900">
                <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
                    {/* Modern Header */}
                    <div className="relative overflow-hidden rounded-2xl bg-gradient-to-r from-blue-600 to-purple-600 p-8 text-white shadow-xl">
                        <div className="absolute inset-0 bg-black/10"></div>
                        <div className="relative flex items-center justify-between">
                            <div>
                                <h1 className="text-3xl font-bold tracking-tight">
                                    Welcome back, Dr. {user?.name || 'Doctor'}
                                </h1>
                                <p className="mt-2 text-blue-100">
                                    {user?.clinic?.name || 'No Clinic'} • Doctor Dashboard
                                </p>
                            </div>
                            <div className="flex items-center gap-3">
                                <Badge variant="secondary" className="flex items-center gap-1 bg-white/20 text-white border-white/30 hover:bg-white/30">
                                    <Shield className="h-3 w-3" />
                                    Doctor
                                </Badge>
                                {user?.clinic && (
                                    <Badge variant="secondary" className="flex items-center gap-1 bg-white/20 text-white border-white/30 hover:bg-white/30">
                                        <Building2 className="h-3 w-3" />
                                        {user.clinic.name}
                                    </Badge>
                                )}
                            </div>
                        </div>
                        {/* Decorative elements */}
                        <div className="absolute -top-4 -right-4 w-24 h-24 bg-white/10 rounded-full"></div>
                        <div className="absolute -bottom-2 -left-2 w-16 h-16 bg-white/5 rounded-full"></div>
                    </div>
                    {/* Doctor Stats */}
                    <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                        <Card className="relative overflow-hidden border-0 shadow-lg hover:shadow-xl transition-all duration-300 bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20">
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium text-slate-700 dark:text-slate-300">Today's Appointments</CardTitle>
                                <div className="p-2 bg-blue-500 rounded-lg">
                                    <Calendar className="h-4 w-4 text-white" />
                                </div>
                            </CardHeader>
                            <CardContent>
                                <div className="text-3xl font-bold text-slate-900 dark:text-white">{stats.todayAppointments}</div>
                                <div className="flex items-center mt-2">
                                    <TrendingUp className="h-3 w-3 text-blue-500 mr-1" />
                                    <p className="text-xs text-slate-600 dark:text-slate-400">
                                        Your appointments
                                    </p>
                                </div>
                            </CardContent>
                        </Card>
                        <Card className="relative overflow-hidden border-0 shadow-lg hover:shadow-xl transition-all duration-300 bg-gradient-to-br from-orange-50 to-orange-100 dark:from-orange-900/20 dark:to-orange-800/20">
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium text-slate-700 dark:text-slate-300">Active Queue</CardTitle>
                                <div className="p-2 bg-orange-500 rounded-lg">
                                    <Clock className="h-4 w-4 text-white" />
                                </div>
                            </CardHeader>
                            <CardContent>
                                <div className="text-3xl font-bold text-slate-900 dark:text-white">{stats.activeQueue}</div>
                                <div className="flex items-center mt-2">
                                    <Activity className="h-3 w-3 text-orange-500 mr-1" />
                                    <p className="text-xs text-slate-600 dark:text-slate-400">
                                        Patients waiting
                                    </p>
                                </div>
                            </CardContent>
                        </Card>
                        <Card className="relative overflow-hidden border-0 shadow-lg hover:shadow-xl transition-all duration-300 bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20">
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium text-slate-700 dark:text-slate-300">Completed Today</CardTitle>
                                <div className="p-2 bg-green-500 rounded-lg">
                                    <CheckCircle className="h-4 w-4 text-white" />
                                </div>
                            </CardHeader>
                            <CardContent>
                                <div className="text-3xl font-bold text-slate-900 dark:text-white">{stats.completedEncounters}</div>
                                <div className="flex items-center mt-2">
                                    <CheckCircle className="h-3 w-3 text-green-500 mr-1" />
                                    <p className="text-xs text-slate-600 dark:text-slate-400">
                                        Encounters completed
                                    </p>
                                </div>
                            </CardContent>
                        </Card>
                        <Card className="relative overflow-hidden border-0 shadow-lg hover:shadow-xl transition-all duration-300 bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20">
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium text-slate-700 dark:text-slate-300">Pending Prescriptions</CardTitle>
                                <div className="p-2 bg-purple-500 rounded-lg">
                                    <Pill className="h-4 w-4 text-white" />
                                </div>
                            </CardHeader>
                            <CardContent>
                                <div className="text-3xl font-bold text-slate-900 dark:text-white">{stats.pendingPrescriptions}</div>
                                <div className="flex items-center mt-2">
                                    <AlertCircle className="h-3 w-3 text-purple-500 mr-1" />
                                    <p className="text-xs text-slate-600 dark:text-slate-400">
                                        Awaiting verification
                                    </p>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    {/* Quick Actions */}
                    <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                        <CardHeader>
                            <CardTitle className="text-xl font-semibold text-slate-900 dark:text-white">Doctor Tools</CardTitle>
                            <CardDescription className="text-slate-600 dark:text-slate-300">Access your medical tools and patient management</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="grid gap-4 md:grid-cols-3">
                                {hasPermission('work_on_queue') && (
                                    <Link href="/doctor/queue">
                                        <Button variant="outline" className="w-full justify-start h-12 border-slate-200 dark:border-slate-700 hover:bg-orange-50 dark:hover:bg-orange-900/20 hover:border-orange-300 dark:hover:border-orange-600 transition-all duration-200 group">
                                            <div className="p-1 bg-orange-100 dark:bg-orange-900 rounded-md mr-3 group-hover:bg-orange-200 dark:group-hover:bg-orange-800 transition-colors">
                                                <Clock className="h-4 w-4 text-orange-600 dark:text-orange-400" />
                                            </div>
                                            <span className="font-medium">Patient Queue</span>
                                            <ArrowUpRight className="h-4 w-4 ml-auto text-slate-400 group-hover:text-orange-500 transition-colors" />
                                        </Button>
                                    </Link>
                                )}
                                {hasPermission('view_appointments') && (
                                    <Link href="/doctor/appointments">
                                        <Button variant="outline" className="w-full justify-start h-12 border-slate-200 dark:border-slate-700 hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:border-blue-300 dark:hover:border-blue-600 transition-all duration-200 group">
                                            <div className="p-1 bg-blue-100 dark:bg-blue-900 rounded-md mr-3 group-hover:bg-blue-200 dark:group-hover:bg-blue-800 transition-colors">
                                                <Calendar className="h-4 w-4 text-blue-600 dark:text-blue-400" />
                                            </div>
                                            <span className="font-medium">My Appointments</span>
                                            <ArrowUpRight className="h-4 w-4 ml-auto text-slate-400 group-hover:text-blue-500 transition-colors" />
                                        </Button>
                                    </Link>
                                )}
                                {hasPermission('manage_prescriptions') && (
                                    <Link href="/doctor/prescriptions">
                                        <Button variant="outline" className="w-full justify-start h-12 border-slate-200 dark:border-slate-700 hover:bg-purple-50 dark:hover:bg-purple-900/20 hover:border-purple-300 dark:hover:border-purple-600 transition-all duration-200 group">
                                            <div className="p-1 bg-purple-100 dark:bg-purple-900 rounded-md mr-3 group-hover:bg-purple-200 dark:group-hover:bg-purple-800 transition-colors">
                                                <Pill className="h-4 w-4 text-purple-600 dark:text-purple-400" />
                                            </div>
                                            <span className="font-medium">Prescriptions</span>
                                            <ArrowUpRight className="h-4 w-4 ml-auto text-slate-400 group-hover:text-purple-500 transition-colors" />
                                        </Button>
                                    </Link>
                                )}
                                {hasPermission('view_medical_records') && (
                                    <Link href="/doctor/medical-records">
                                        <Button variant="outline" className="w-full justify-start h-12 border-slate-200 dark:border-slate-700 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 hover:border-emerald-300 dark:hover:border-emerald-600 transition-all duration-200 group">
                                            <div className="p-1 bg-emerald-100 dark:bg-emerald-900 rounded-md mr-3 group-hover:bg-emerald-200 dark:group-hover:bg-emerald-800 transition-colors">
                                                <FileText className="h-4 w-4 text-emerald-600 dark:text-emerald-400" />
                                            </div>
                                            <span className="font-medium">Medical Records</span>
                                            <ArrowUpRight className="h-4 w-4 ml-auto text-slate-400 group-hover:text-emerald-500 transition-colors" />
                                        </Button>
                                    </Link>
                                )}
                                {hasPermission('view_patients') && (
                                    <Link href="/doctor/patients">
                                        <Button variant="outline" className="w-full justify-start h-12 border-slate-200 dark:border-slate-700 hover:bg-green-50 dark:hover:bg-green-900/20 hover:border-green-300 dark:hover:border-green-600 transition-all duration-200 group">
                                            <div className="p-1 bg-green-100 dark:bg-green-900 rounded-md mr-3 group-hover:bg-green-200 dark:group-hover:bg-green-800 transition-colors">
                                                <UserCheck className="h-4 w-4 text-green-600 dark:text-green-400" />
                                            </div>
                                            <span className="font-medium">My Patients</span>
                                            <ArrowUpRight className="h-4 w-4 ml-auto text-slate-400 group-hover:text-green-500 transition-colors" />
                                        </Button>
                                    </Link>
                                )}
                                {hasPermission('view_analytics') && (
                                    <Link href="/doctor/analytics">
                                        <Button variant="outline" className="w-full justify-start h-12 border-slate-200 dark:border-slate-700 hover:bg-orange-50 dark:hover:bg-orange-900/20 hover:border-orange-300 dark:hover:border-orange-600 transition-all duration-200 group">
                                            <div className="p-1 bg-orange-100 dark:bg-orange-900 rounded-md mr-3 group-hover:bg-orange-200 dark:group-hover:bg-orange-800 transition-colors">
                                                <BarChart3 className="h-4 w-4 text-orange-600 dark:text-orange-400" />
                                            </div>
                                            <span className="font-medium">My Analytics</span>
                                            <ArrowUpRight className="h-4 w-4 ml-auto text-slate-400 group-hover:text-orange-500 transition-colors" />
                                        </Button>
                                    </Link>
                                )}
                            </div>
                        </CardContent>
                    </Card>

                    <div className="grid gap-6 md:grid-cols-2">
                        {/* Recent Appointments */}
                        <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                            <CardHeader>
                                <CardTitle className="text-lg font-semibold text-slate-900 dark:text-white">Recent Appointments</CardTitle>
                                <CardDescription className="text-slate-600 dark:text-slate-300">
                                    Your latest patient appointments
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-4">
                                    {stats.recentAppointments.length > 0 ? (
                                        stats.recentAppointments.map((appointment) => (
                                            <div key={appointment.id} className="flex items-center justify-between p-3 rounded-lg bg-slate-50 dark:bg-slate-700/50 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                                                <div className="space-y-1">
                                                    <p className="text-sm font-medium leading-none text-slate-900 dark:text-white">
                                                        {appointment.patient_name}
                                                    </p>
                                                    <p className="text-sm text-slate-600 dark:text-slate-400">
                                                        {new Date(appointment.start_at).toLocaleDateString()} at{' '}
                                                        {new Date(appointment.start_at).toLocaleTimeString([], {
                                                            hour: '2-digit',
                                                            minute: '2-digit'
                                                        })}
                                                    </p>
                                                    <p className="text-xs text-slate-500 dark:text-slate-500">
                                                        {appointment.type}
                                                    </p>
                                                </div>
                                                <Badge className={`${getStatusColor(appointment.status)} border-0`}>
                                                    {appointment.status.replace('_', ' ')}
                                                </Badge>
                                            </div>
                                        ))
                                    ) : (
                                        <div className="text-center py-8">
                                            <div className="p-3 bg-blue-100 dark:bg-blue-900/20 rounded-full w-fit mx-auto mb-3">
                                                <Calendar className="h-8 w-8 text-blue-600 dark:text-blue-400" />
                                            </div>
                                            <p className="text-sm text-slate-600 dark:text-slate-400">No recent appointments</p>
                                        </div>
                                    )}
                                </div>
                                <div className="mt-4">
                                    <Button asChild variant="outline" size="sm" className="w-full border-slate-200 dark:border-slate-700 hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:border-blue-300 dark:hover:border-blue-600 transition-all duration-200">
                                        <Link href="/doctor/appointments">
                                            View All Appointments
                                        </Link>
                                    </Button>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Recent Prescriptions */}
                        <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                            <CardHeader>
                                <CardTitle className="text-lg font-semibold text-slate-900 dark:text-white">Recent Prescriptions</CardTitle>
                                <CardDescription className="text-slate-600 dark:text-slate-300">
                                    Your latest prescription orders
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-4">
                                    {stats.recentPrescriptions.length > 0 ? (
                                        stats.recentPrescriptions.map((prescription) => (
                                            <div key={prescription.id} className="flex items-center justify-between p-3 rounded-lg bg-slate-50 dark:bg-slate-700/50 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                                                <div className="space-y-1">
                                                    <p className="text-sm font-medium leading-none text-slate-900 dark:text-white">
                                                        {prescription.patient_name}
                                                    </p>
                                                    <p className="text-sm text-slate-600 dark:text-slate-400">
                                                        {prescription.prescription_number}
                                                    </p>
                                                    <p className="text-xs text-slate-500 dark:text-slate-500">
                                                        {new Date(prescription.issued_at).toLocaleDateString()}
                                                    </p>
                                                </div>
                                                <Badge className={`${getPrescriptionStatusColor(prescription.status)} border-0`}>
                                                    {prescription.status}
                                                </Badge>
                                            </div>
                                        ))
                                    ) : (
                                        <div className="text-center py-8">
                                            <div className="p-3 bg-purple-100 dark:bg-purple-900/20 rounded-full w-fit mx-auto mb-3">
                                                <Pill className="h-8 w-8 text-purple-600 dark:text-purple-400" />
                                            </div>
                                            <p className="text-sm text-slate-600 dark:text-slate-400">No recent prescriptions</p>
                                        </div>
                                    )}
                                </div>
                                <div className="mt-4">
                                    <Button asChild variant="outline" size="sm" className="w-full border-slate-200 dark:border-slate-700 hover:bg-purple-50 dark:hover:bg-purple-900/20 hover:border-purple-300 dark:hover:border-purple-600 transition-all duration-200">
                                        <Link href="/doctor/prescriptions">
                                            View All Prescriptions
                                        </Link>
                                    </Button>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    {/* Alerts and Notifications */}
                    <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2 text-lg font-semibold text-slate-900 dark:text-white">
                                <div className="p-1 bg-orange-100 dark:bg-orange-900/20 rounded-md">
                                    <AlertCircle className="h-5 w-5 text-orange-600 dark:text-orange-400" />
                                </div>
                                Important Alerts
                            </CardTitle>
                            <CardDescription className="text-slate-600 dark:text-slate-300">
                                Items requiring your attention
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                {stats.pendingPrescriptions > 0 && (
                                    <div className="flex items-center gap-4 p-4 bg-gradient-to-r from-yellow-50 to-orange-50 dark:from-yellow-900/10 dark:to-orange-900/10 border border-yellow-200 dark:border-yellow-800/30 rounded-xl hover:shadow-md transition-all duration-200">
                                        <div className="p-2 bg-yellow-100 dark:bg-yellow-900/20 rounded-lg">
                                            <AlertCircle className="h-5 w-5 text-yellow-600 dark:text-yellow-400" />
                                        </div>
                                        <div className="flex-1">
                                            <p className="text-sm font-semibold text-yellow-800 dark:text-yellow-200">
                                                {stats.pendingPrescriptions} prescription(s) pending review
                                            </p>
                                            <p className="text-xs text-yellow-600 dark:text-yellow-400 mt-1">
                                                Please review and approve pending prescriptions
                                            </p>
                                        </div>
                                        <Button asChild size="sm" variant="outline" className="border-yellow-300 dark:border-yellow-700 hover:bg-yellow-100 dark:hover:bg-yellow-900/20 hover:border-yellow-400 dark:hover:border-yellow-600 transition-all duration-200">
                                            <Link href="/doctor/prescriptions?status=draft">
                                                Review
                                            </Link>
                                        </Button>
                                    </div>
                                )}

                                {stats.todayAppointments > 0 && (
                                    <div className="flex items-center gap-4 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/10 dark:to-indigo-900/10 border border-blue-200 dark:border-blue-800/30 rounded-xl hover:shadow-md transition-all duration-200">
                                        <div className="p-2 bg-blue-100 dark:bg-blue-900/20 rounded-lg">
                                            <CheckCircle className="h-5 w-5 text-blue-600 dark:text-blue-400" />
                                        </div>
                                        <div className="flex-1">
                                            <p className="text-sm font-semibold text-blue-800 dark:text-blue-200">
                                                {stats.todayAppointments} appointment(s) scheduled for today
                                            </p>
                                            <p className="text-xs text-blue-600 dark:text-blue-400 mt-1">
                                                Review your schedule and prepare for patient visits
                                            </p>
                                        </div>
                                        <Button asChild size="sm" variant="outline" className="border-blue-300 dark:border-blue-700 hover:bg-blue-100 dark:hover:bg-blue-900/20 hover:border-blue-400 dark:hover:border-blue-600 transition-all duration-200">
                                            <Link href="/doctor/appointments?date=today">
                                                View Schedule
                                            </Link>
                                        </Button>
                                    </div>
                                )}

                                {stats.pendingPrescriptions === 0 && stats.todayAppointments === 0 && (
                                    <div className="text-center py-8">
                                        <div className="p-4 bg-green-100 dark:bg-green-900/20 rounded-full w-fit mx-auto mb-4">
                                            <CheckCircle className="h-12 w-12 text-green-600 dark:text-green-400" />
                                        </div>
                                        <h3 className="text-lg font-semibold text-slate-900 dark:text-white mb-2">All Caught Up!</h3>
                                        <p className="text-sm text-slate-600 dark:text-slate-400">No urgent items requiring your attention at this time.</p>
                                    </div>
                                )}
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AppLayout>
    );
}
