import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { useUserRole } from '@/hooks/use-user-role';
import {
    Users,
    Calendar,
    FileText,
    Stethoscope,
    UserPlus,
    Clock,
    Activity,
    Pill,
    Package,
    Settings,
    Shield,
    AlertCircle,
    CheckCircle,
    Eye,
    BarChart3,
    UserCheck,
    Building2,
    TrendingUp,
    ArrowUpRight
} from 'lucide-react';
import { Link } from '@inertiajs/react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

interface User {
    id: number;
    name: string;
    email: string;
    role: string;
    clinic_id?: number;
    clinic?: {
        id: number;
        name: string;
    };
}

interface DashboardStats {
    totalUsers: number;
    totalPatients: number;
    totalAppointments: number;
    totalEncounters: number;
    totalPrescriptions: number;
    totalProducts: number;
    totalMeetings: number;
    totalInteractions: number;
    todayAppointments: number;
    activeQueue: number;
    completedEncounters: number;
    pendingPrescriptions: number;
    upcomingMeetings: number;
    recentActivity: Array<{
        id: number;
        type: string;
        description: string;
        user_name: string;
        created_at: string;
    }>;
}

interface DashboardProps {
    user: User;
    stats: DashboardStats;
    permissions: string[];
}

export default function Dashboard({ user, stats, permissions }: DashboardProps) {
    const { userRole } = useUserRole();

    // Debug logging
    console.log('Dashboard props:', { user, stats, permissions });
    console.log('User role from hook:', userRole);

    // Get role-specific dashboard content
    const getRoleDashboard = () => {
        const role = userRole || user?.role || 'default';
        console.log('User role:', role);

        switch (role) {
            case 'admin':
                return <AdminDashboard stats={stats} permissions={permissions} />;
            case 'doctor':
                return <DoctorDashboard stats={stats} permissions={permissions} />;
            case 'receptionist':
                return <ReceptionistDashboard stats={stats} permissions={permissions} />;
            case 'patient':
                return <PatientDashboard stats={stats} permissions={permissions} />;
            case 'medrep':
                return <MedrepDashboard stats={stats} permissions={permissions} />;
            default:
                return <DefaultDashboard />;
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard - Medinext">
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
                                    Welcome back, {user?.name || 'User'}
                                </h1>
                                <p className="mt-2 text-blue-100">
                                    {user?.clinic?.name || 'No Clinic'} • {userRole ? userRole.charAt(0).toUpperCase() + userRole.slice(1) : user?.role ? user.role.charAt(0).toUpperCase() + user.role.slice(1) : 'User'}
                                </p>
                            </div>
                            <div className="flex items-center gap-3">
                                <Badge variant="secondary" className="flex items-center gap-1 bg-white/20 text-white border-white/30 hover:bg-white/30">
                                    <Shield className="h-3 w-3" />
                                    {userRole || user?.role || 'User'}
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

                    {/* Role-specific Dashboard */}
                    {getRoleDashboard()}
                </div>
            </div>
        </AppLayout>
    );
}

// Admin Dashboard
function AdminDashboard({ stats, permissions }: { stats: DashboardStats; permissions: string[] }) {
    const hasPermission = (permission: string) => permissions.includes(permission);
    return (
        <div className="space-y-6">
            {/* Clinic Overview Stats */}
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
                            <TrendingUp className="h-3 w-3 text-green-500 mr-1" />
                            <p className="text-xs text-slate-600 dark:text-slate-400">
                                Scheduled for today
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
                        <CardTitle className="text-sm font-medium text-slate-700 dark:text-slate-300">Total Patients</CardTitle>
                        <div className="p-2 bg-green-500 rounded-lg">
                            <UserCheck className="h-4 w-4 text-white" />
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div className="text-3xl font-bold text-slate-900 dark:text-white">{stats.totalPatients}</div>
                        <div className="flex items-center mt-2">
                            <TrendingUp className="h-3 w-3 text-green-500 mr-1" />
                            <p className="text-xs text-slate-600 dark:text-slate-400">
                                Registered patients
                            </p>
                        </div>
                    </CardContent>
                </Card>
                <Card className="relative overflow-hidden border-0 shadow-lg hover:shadow-xl transition-all duration-300 bg-gradient-to-br from-emerald-50 to-emerald-100 dark:from-emerald-900/20 dark:to-emerald-800/20">
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-sm font-medium text-slate-700 dark:text-slate-300">Completed Today</CardTitle>
                        <div className="p-2 bg-emerald-500 rounded-lg">
                            <CheckCircle className="h-4 w-4 text-white" />
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div className="text-3xl font-bold text-slate-900 dark:text-white">{stats.completedEncounters}</div>
                        <div className="flex items-center mt-2">
                            <CheckCircle className="h-3 w-3 text-emerald-500 mr-1" />
                            <p className="text-xs text-slate-600 dark:text-slate-400">
                                Encounters completed
                            </p>
                        </div>
                    </CardContent>
                </Card>
            </div>

            {/* Quick Actions */}
            <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                <CardHeader>
                    <CardTitle className="text-xl font-semibold text-slate-900 dark:text-white">Clinic Management</CardTitle>
                    <CardDescription className="text-slate-600 dark:text-slate-300">Manage your clinic operations</CardDescription>
                </CardHeader>
                <CardContent>
                    <div className="grid gap-4 md:grid-cols-3">
                        {hasPermission('manage_staff') && (
                            <Link href="/admin/staff">
                                <Button variant="outline" className="w-full justify-start h-12 border-slate-200 dark:border-slate-700 hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:border-blue-300 dark:hover:border-blue-600 transition-all duration-200 group">
                                    <div className="p-1 bg-blue-100 dark:bg-blue-900 rounded-md mr-3 group-hover:bg-blue-200 dark:group-hover:bg-blue-800 transition-colors">
                                        <Users className="h-4 w-4 text-blue-600 dark:text-blue-400" />
                                    </div>
                                    <span className="font-medium">Manage Staff</span>
                                    <ArrowUpRight className="h-4 w-4 ml-auto text-slate-400 group-hover:text-blue-500 transition-colors" />
                                </Button>
                            </Link>
                        )}
                        {hasPermission('manage_doctors') && (
                            <Link href="/admin/doctors">
                                <Button variant="outline" className="w-full justify-start h-12 border-slate-200 dark:border-slate-700 hover:bg-green-50 dark:hover:bg-green-900/20 hover:border-green-300 dark:hover:border-green-600 transition-all duration-200 group">
                                    <div className="p-1 bg-green-100 dark:bg-green-900 rounded-md mr-3 group-hover:bg-green-200 dark:group-hover:bg-green-800 transition-colors">
                                        <Stethoscope className="h-4 w-4 text-green-600 dark:text-green-400" />
                                    </div>
                                    <span className="font-medium">Manage Doctors</span>
                                    <ArrowUpRight className="h-4 w-4 ml-auto text-slate-400 group-hover:text-green-500 transition-colors" />
                                </Button>
                            </Link>
                        )}
                        {hasPermission('view_appointments') && (
                            <Link href="/admin/appointments">
                                <Button variant="outline" className="w-full justify-start h-12 border-slate-200 dark:border-slate-700 hover:bg-purple-50 dark:hover:bg-purple-900/20 hover:border-purple-300 dark:hover:border-purple-600 transition-all duration-200 group">
                                    <div className="p-1 bg-purple-100 dark:bg-purple-900 rounded-md mr-3 group-hover:bg-purple-200 dark:group-hover:bg-purple-800 transition-colors">
                                        <Calendar className="h-4 w-4 text-purple-600 dark:text-purple-400" />
                                    </div>
                                    <span className="font-medium">View Appointments</span>
                                    <ArrowUpRight className="h-4 w-4 ml-auto text-slate-400 group-hover:text-purple-500 transition-colors" />
                                </Button>
                            </Link>
                        )}
                        {hasPermission('view_patients') && (
                            <Link href="/admin/patients">
                                <Button variant="outline" className="w-full justify-start h-12 border-slate-200 dark:border-slate-700 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 hover:border-emerald-300 dark:hover:border-emerald-600 transition-all duration-200 group">
                                    <div className="p-1 bg-emerald-100 dark:bg-emerald-900 rounded-md mr-3 group-hover:bg-emerald-200 dark:group-hover:bg-emerald-800 transition-colors">
                                        <UserCheck className="h-4 w-4 text-emerald-600 dark:text-emerald-400" />
                                    </div>
                                    <span className="font-medium">View Patients</span>
                                    <ArrowUpRight className="h-4 w-4 ml-auto text-slate-400 group-hover:text-emerald-500 transition-colors" />
                                </Button>
                            </Link>
                        )}
                        {hasPermission('view_reports') && (
                            <Link href="/admin/reports">
                                <Button variant="outline" className="w-full justify-start h-12 border-slate-200 dark:border-slate-700 hover:bg-orange-50 dark:hover:bg-orange-900/20 hover:border-orange-300 dark:hover:border-orange-600 transition-all duration-200 group">
                                    <div className="p-1 bg-orange-100 dark:bg-orange-900 rounded-md mr-3 group-hover:bg-orange-200 dark:group-hover:bg-orange-800 transition-colors">
                                        <BarChart3 className="h-4 w-4 text-orange-600 dark:text-orange-400" />
                                    </div>
                                    <span className="font-medium">View Reports</span>
                                    <ArrowUpRight className="h-4 w-4 ml-auto text-slate-400 group-hover:text-orange-500 transition-colors" />
                                </Button>
                            </Link>
                        )}
                        {hasPermission('manage_settings') && (
                            <Link href="/admin/clinic-settings">
                                <Button variant="outline" className="w-full justify-start h-12 border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 hover:border-slate-300 dark:hover:border-slate-600 transition-all duration-200 group">
                                    <div className="p-1 bg-slate-100 dark:bg-slate-700 rounded-md mr-3 group-hover:bg-slate-200 dark:group-hover:bg-slate-600 transition-colors">
                                        <Settings className="h-4 w-4 text-slate-600 dark:text-slate-400" />
                                    </div>
                                    <span className="font-medium">Clinic Settings</span>
                                    <ArrowUpRight className="h-4 w-4 ml-auto text-slate-400 group-hover:text-slate-500 transition-colors" />
                                </Button>
                            </Link>
                        )}
                    </div>
                </CardContent>
            </Card>
        </div>
    );
}

// Doctor Dashboard
function DoctorDashboard({ stats, permissions }: { stats: DashboardStats; permissions: string[] }) {
    const hasPermission = (permission: string) => permissions.includes(permission);
    return (
        <div className="space-y-6">
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
        </div>
    );
}

// Receptionist Dashboard
function ReceptionistDashboard({ stats, permissions }: { stats: DashboardStats; permissions: string[] }) {
    const hasPermission = (permission: string) => permissions.includes(permission);
    return (
        <div className="space-y-6">
            {/* Receptionist Stats */}
            <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-sm font-medium">Today's Appointments</CardTitle>
                        <Calendar className="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold">{stats.todayAppointments}</div>
                        <p className="text-xs text-muted-foreground">
                            Scheduled today
                        </p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-sm font-medium">Active Queue</CardTitle>
                        <Clock className="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold">{stats.activeQueue}</div>
                        <p className="text-xs text-muted-foreground">
                            Patients waiting
                        </p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-sm font-medium">Total Patients</CardTitle>
                        <UserCheck className="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold">{stats.totalPatients}</div>
                        <p className="text-xs text-muted-foreground">
                            Registered patients
                        </p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-sm font-medium">New Today</CardTitle>
                        <UserPlus className="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold">12</div>
                        <p className="text-xs text-muted-foreground">
                            New registrations
                        </p>
                    </CardContent>
                </Card>
            </div>

            {/* Quick Actions */}
            <Card>
                <CardHeader>
                    <CardTitle>Receptionist Tools</CardTitle>
                    <CardDescription>Manage patient flow and appointments</CardDescription>
                </CardHeader>
                <CardContent>
                    <div className="grid gap-4 md:grid-cols-3">
                        {hasPermission('search_patients') && (
                            <Link href="/receptionist/dashboard">
                                <Button variant="outline" className="w-full justify-start">
                                    <UserCheck className="h-4 w-4 mr-2" />
                                    Patient Search
                                </Button>
                            </Link>
                        )}
                        {hasPermission('manage_appointments') && (
                            <Link href="/receptionist/appointments">
                                <Button variant="outline" className="w-full justify-start">
                                    <Calendar className="h-4 w-4 mr-2" />
                                    Manage Appointments
                                </Button>
                            </Link>
                        )}
                        {hasPermission('manage_queue') && (
                            <Link href="/receptionist/queue">
                                <Button variant="outline" className="w-full justify-start">
                                    <Clock className="h-4 w-4 mr-2" />
                                    Manage Queue
                                </Button>
                            </Link>
                        )}
                        {hasPermission('register_patients') && (
                            <Link href="/receptionist/patients">
                                <Button variant="outline" className="w-full justify-start">
                                    <UserPlus className="h-4 w-4 mr-2" />
                                    Register Patients
                                </Button>
                            </Link>
                        )}
                        {hasPermission('view_encounters') && (
                            <Link href="/receptionist/encounters">
                                <Button variant="outline" className="w-full justify-start">
                                    <FileText className="h-4 w-4 mr-2" />
                                    View Encounters
                                </Button>
                            </Link>
                        )}
                        {hasPermission('view_reports') && (
                            <Link href="/receptionist/reports">
                                <Button variant="outline" className="w-full justify-start">
                                    <BarChart3 className="h-4 w-4 mr-2" />
                                    Daily Reports
                                </Button>
                            </Link>
                        )}
                    </div>
                </CardContent>
            </Card>
        </div>
    );
}

// Patient Dashboard
function PatientDashboard({ stats, permissions }: { stats: DashboardStats; permissions: string[] }) {
    const hasPermission = (permission: string) => permissions.includes(permission);
    return (
        <div className="space-y-6">
            {/* Patient Stats */}
            <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-sm font-medium">Upcoming Appointments</CardTitle>
                        <Calendar className="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold">{stats.todayAppointments}</div>
                        <p className="text-xs text-muted-foreground">
                            Scheduled appointments
                        </p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-sm font-medium">Medical Records</CardTitle>
                        <FileText className="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold">{stats.totalEncounters}</div>
                        <p className="text-xs text-muted-foreground">
                            Total encounters
                        </p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-sm font-medium">Active Prescriptions</CardTitle>
                        <Pill className="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold">{stats.totalPrescriptions}</div>
                        <p className="text-xs text-muted-foreground">
                            Current prescriptions
                        </p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-sm font-medium">Lab Results</CardTitle>
                        <Activity className="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold">8</div>
                        <p className="text-xs text-muted-foreground">
                            Available results
                        </p>
                    </CardContent>
                </Card>
            </div>

            {/* Quick Actions */}
            <Card>
                <CardHeader>
                    <CardTitle>Patient Portal</CardTitle>
                    <CardDescription>Manage your healthcare and appointments</CardDescription>
                </CardHeader>
                <CardContent>
                    <div className="grid gap-4 md:grid-cols-3">
                        {hasPermission('book_appointments') && (
                            <Link href="/patient/dashboard">
                                <Button variant="outline" className="w-full justify-start">
                                    <Calendar className="h-4 w-4 mr-2" />
                                    Book Appointment
                                </Button>
                            </Link>
                        )}
                        {hasPermission('view_medical_records') && (
                            <Link href="/patient/records">
                                <Button variant="outline" className="w-full justify-start">
                                    <FileText className="h-4 w-4 mr-2" />
                                    Medical Records
                                </Button>
                            </Link>
                        )}
                        {hasPermission('view_prescriptions') && (
                            <Link href="/patient/prescriptions">
                                <Button variant="outline" className="w-full justify-start">
                                    <Pill className="h-4 w-4 mr-2" />
                                    My Prescriptions
                                </Button>
                            </Link>
                        )}
                        {hasPermission('view_lab_results') && (
                            <Link href="/patient/lab-results">
                                <Button variant="outline" className="w-full justify-start">
                                    <Activity className="h-4 w-4 mr-2" />
                                    Lab Results
                                </Button>
                            </Link>
                        )}
                        {hasPermission('view_appointments') && (
                            <Link href="/patient/appointments">
                                <Button variant="outline" className="w-full justify-start">
                                    <Eye className="h-4 w-4 mr-2" />
                                    My Appointments
                                </Button>
                            </Link>
                        )}
                        {hasPermission('update_profile') && (
                            <Link href="/patient/profile">
                                <Button variant="outline" className="w-full justify-start">
                                    <UserCheck className="h-4 w-4 mr-2" />
                                    Update Profile
                                </Button>
                            </Link>
                        )}
                    </div>
                </CardContent>
            </Card>
        </div>
    );
}

// Medical Representative Dashboard
function MedrepDashboard({ stats, permissions }: { stats: DashboardStats; permissions: string[] }) {
    const hasPermission = (permission: string) => permissions.includes(permission);
    return (
        <div className="space-y-6">
            {/* Medrep Stats */}
            <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-sm font-medium">Total Products</CardTitle>
                        <Package className="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold">{stats.totalProducts}</div>
                        <p className="text-xs text-muted-foreground">
                            In catalog
                        </p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-sm font-medium">Upcoming Meetings</CardTitle>
                        <Calendar className="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold">{stats.upcomingMeetings}</div>
                        <p className="text-xs text-muted-foreground">
                            Scheduled meetings
                        </p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-sm font-medium">Total Interactions</CardTitle>
                        <Users className="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold">{stats.totalInteractions}</div>
                        <p className="text-xs text-muted-foreground">
                            Doctor interactions
                        </p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-sm font-medium">Active Doctors</CardTitle>
                        <Stethoscope className="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold">24</div>
                        <p className="text-xs text-muted-foreground">
                            In network
                        </p>
                    </CardContent>
                </Card>
            </div>

            {/* Quick Actions */}
            <Card>
                <CardHeader>
                    <CardTitle>Medical Representative Tools</CardTitle>
                    <CardDescription>Manage products, meetings, and doctor relationships</CardDescription>
                </CardHeader>
                <CardContent>
                    <div className="grid gap-4 md:grid-cols-3">
                        {hasPermission('manage_products') && (
                            <Link href="/medrep/dashboard">
                                <Button variant="outline" className="w-full justify-start">
                                    <Package className="h-4 w-4 mr-2" />
                                    Product Catalog
                                </Button>
                            </Link>
                        )}
                        {hasPermission('schedule_meetings') && (
                            <Link href="/medrep/meetings">
                                <Button variant="outline" className="w-full justify-start">
                                    <Calendar className="h-4 w-4 mr-2" />
                                    Schedule Meetings
                                </Button>
                            </Link>
                        )}
                        {hasPermission('track_interactions') && (
                            <Link href="/medrep/interactions">
                                <Button variant="outline" className="w-full justify-start">
                                    <Users className="h-4 w-4 mr-2" />
                                    Track Interactions
                                </Button>
                            </Link>
                        )}
                        {hasPermission('manage_doctors') && (
                            <Link href="/medrep/doctors">
                                <Button variant="outline" className="w-full justify-start">
                                    <Stethoscope className="h-4 w-4 mr-2" />
                                    Doctor Directory
                                </Button>
                            </Link>
                        )}
                        {hasPermission('view_analytics') && (
                            <Link href="/medrep/analytics">
                                <Button variant="outline" className="w-full justify-start">
                                    <BarChart3 className="h-4 w-4 mr-2" />
                                    Performance Analytics
                                </Button>
                            </Link>
                        )}
                        {hasPermission('manage_samples') && (
                            <Link href="/medrep/samples">
                                <Button variant="outline" className="w-full justify-start">
                                    <Package className="h-4 w-4 mr-2" />
                                    Sample Management
                                </Button>
                            </Link>
                        )}
                    </div>
                </CardContent>
            </Card>
        </div>
    );
}

// Default Dashboard (fallback)
function DefaultDashboard() {
    return (
        <div className="space-y-6">
            <Card>
                <CardHeader>
                    <CardTitle>Welcome to MediNext</CardTitle>
                    <CardDescription>Your role permissions are being configured</CardDescription>
                </CardHeader>
                <CardContent>
                    <div className="text-center py-8">
                        <AlertCircle className="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                        <h3 className="text-lg font-semibold mb-2">Access Pending</h3>
                        <p className="text-muted-foreground">
                            Please contact your administrator to configure your role permissions.
                        </p>
                    </div>
                </CardContent>
            </Card>
        </div>
    );
}
