import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
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
    ClipboardList,
    Building2
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

    // Get role-specific dashboard content
    const getRoleDashboard = () => {
        switch (user.role) {
            case 'superadmin':
                return <SuperAdminDashboard stats={stats} permissions={permissions} />;
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
            <Head title="Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">
                            Welcome back, {user.name}
                        </h1>
                        <p className="text-muted-foreground">
                            {user.clinic?.name} • {user.role.charAt(0).toUpperCase() + user.role.slice(1)}
                        </p>
                    </div>
                    <div className="flex items-center gap-2">
                        <Badge variant="outline" className="flex items-center gap-1">
                            <Shield className="h-3 w-3" />
                            {user.role}
                        </Badge>
                        {user.clinic && (
                            <Badge variant="secondary" className="flex items-center gap-1">
                                <Building2 className="h-3 w-3" />
                                {user.clinic.name}
                            </Badge>
                        )}
                    </div>
                </div>

                {/* Role-specific Dashboard */}
                {getRoleDashboard()}
            </div>
        </AppLayout>
    );
}

// Super Admin Dashboard
function SuperAdminDashboard({ stats, permissions }: { stats: DashboardStats; permissions: string[] }) {
    const hasPermission = (permission: string) => permissions.includes(permission);
    return (
        <div className="space-y-6">
            {/* System Overview Stats */}
            <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-sm font-medium">Total Users</CardTitle>
                        <Users className="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold">{stats.totalUsers}</div>
                        <p className="text-xs text-muted-foreground">
                            Across all clinics
                        </p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-sm font-medium">Total Clinics</CardTitle>
                        <Building2 className="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold">{stats.totalUsers}</div>
                        <p className="text-xs text-muted-foreground">
                            Active clinics
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
                            System-wide
                        </p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-sm font-medium">System Health</CardTitle>
                        <Activity className="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold text-green-600">98%</div>
                        <p className="text-xs text-muted-foreground">
                            Uptime
                        </p>
                    </CardContent>
                </Card>
            </div>

            {/* Quick Actions */}
            <Card>
                <CardHeader>
                    <CardTitle>System Management</CardTitle>
                    <CardDescription>Manage system-wide settings and configurations</CardDescription>
                </CardHeader>
                <CardContent>
                    <div className="grid gap-4 md:grid-cols-3">
                        {hasPermission('manage_users') && (
                            <Link href="/admin/users">
                                <Button variant="outline" className="w-full justify-start">
                                    <Users className="h-4 w-4 mr-2" />
                                    Manage Users
                                </Button>
                            </Link>
                        )}
                        {hasPermission('manage_clinics') && (
                            <Link href="/admin/clinics">
                                <Button variant="outline" className="w-full justify-start">
                                    <Building2 className="h-4 w-4 mr-2" />
                                    Manage Clinics
                                </Button>
                            </Link>
                        )}
                        {hasPermission('manage_licenses') && (
                            <Link href="/admin/licenses">
                                <Button variant="outline" className="w-full justify-start">
                                    <Shield className="h-4 w-4 mr-2" />
                                    License Management
                                </Button>
                            </Link>
                        )}
                        {hasPermission('view_analytics') && (
                            <Link href="/admin/analytics">
                                <Button variant="outline" className="w-full justify-start">
                                    <BarChart3 className="h-4 w-4 mr-2" />
                                    System Analytics
                                </Button>
                            </Link>
                        )}
                        {hasPermission('manage_settings') && (
                            <Link href="/admin/settings">
                                <Button variant="outline" className="w-full justify-start">
                                    <Settings className="h-4 w-4 mr-2" />
                                    System Settings
                                </Button>
                            </Link>
                        )}
                        {hasPermission('view_activity_logs') && (
                            <Link href="/admin/activity-logs">
                                <Button variant="outline" className="w-full justify-start">
                                    <ClipboardList className="h-4 w-4 mr-2" />
                                    Activity Logs
                                </Button>
                            </Link>
                        )}
                    </div>
                </CardContent>
            </Card>

            {/* Recent System Activity */}
            <Card>
                <CardHeader>
                    <CardTitle>Recent System Activity</CardTitle>
                    <CardDescription>Latest activities across all clinics</CardDescription>
                </CardHeader>
                <CardContent>
                    <div className="space-y-4">
                        {stats.recentActivity.slice(0, 5).map((activity) => (
                            <div key={activity.id} className="flex items-center justify-between p-3 border rounded-lg">
                                <div className="flex items-center gap-3">
                                    <div className="w-2 h-2 bg-blue-500 rounded-full"></div>
                                    <div>
                                        <p className="text-sm font-medium">{activity.description}</p>
                                        <p className="text-xs text-muted-foreground">
                                            by {activity.user_name} • {new Date(activity.created_at).toLocaleString()}
                                        </p>
                                    </div>
                                </div>
                                <Badge variant="outline">{activity.type}</Badge>
                            </div>
                        ))}
                    </div>
                </CardContent>
            </Card>
        </div>
    );
}

// Admin Dashboard
function AdminDashboard({ stats, permissions }: { stats: DashboardStats; permissions: string[] }) {
    const hasPermission = (permission: string) => permissions.includes(permission);
    return (
        <div className="space-y-6">
            {/* Clinic Overview Stats */}
            <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-sm font-medium">Today's Appointments</CardTitle>
                        <Calendar className="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold">{stats.todayAppointments}</div>
                        <p className="text-xs text-muted-foreground">
                            Scheduled for today
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
                        <CardTitle className="text-sm font-medium">Completed Today</CardTitle>
                        <CheckCircle className="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold">{stats.completedEncounters}</div>
                        <p className="text-xs text-muted-foreground">
                            Encounters completed
                        </p>
                    </CardContent>
                </Card>
            </div>

            {/* Quick Actions */}
            <Card>
                <CardHeader>
                    <CardTitle>Clinic Management</CardTitle>
                    <CardDescription>Manage your clinic operations</CardDescription>
                </CardHeader>
                <CardContent>
                    <div className="grid gap-4 md:grid-cols-3">
                        {hasPermission('manage_staff') && (
                            <Link href="/admin/staff">
                                <Button variant="outline" className="w-full justify-start">
                                    <Users className="h-4 w-4 mr-2" />
                                    Manage Staff
                                </Button>
                            </Link>
                        )}
                        {hasPermission('manage_doctors') && (
                            <Link href="/admin/doctors">
                                <Button variant="outline" className="w-full justify-start">
                                    <Stethoscope className="h-4 w-4 mr-2" />
                                    Manage Doctors
                                </Button>
                            </Link>
                        )}
                        {hasPermission('view_appointments') && (
                            <Link href="/admin/appointments">
                                <Button variant="outline" className="w-full justify-start">
                                    <Calendar className="h-4 w-4 mr-2" />
                                    View Appointments
                                </Button>
                            </Link>
                        )}
                        {hasPermission('view_patients') && (
                            <Link href="/admin/patients">
                                <Button variant="outline" className="w-full justify-start">
                                    <UserCheck className="h-4 w-4 mr-2" />
                                    View Patients
                                </Button>
                            </Link>
                        )}
                        {hasPermission('view_reports') && (
                            <Link href="/admin/reports">
                                <Button variant="outline" className="w-full justify-start">
                                    <BarChart3 className="h-4 w-4 mr-2" />
                                    View Reports
                                </Button>
                            </Link>
                        )}
                        {hasPermission('manage_settings') && (
                            <Link href="/admin/settings">
                                <Button variant="outline" className="w-full justify-start">
                                    <Settings className="h-4 w-4 mr-2" />
                                    Clinic Settings
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
            <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-sm font-medium">Today's Appointments</CardTitle>
                        <Calendar className="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold">{stats.todayAppointments}</div>
                        <p className="text-xs text-muted-foreground">
                            Your appointments
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
                        <CardTitle className="text-sm font-medium">Completed Today</CardTitle>
                        <CheckCircle className="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold">{stats.completedEncounters}</div>
                        <p className="text-xs text-muted-foreground">
                            Encounters completed
                        </p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-sm font-medium">Pending Prescriptions</CardTitle>
                        <Pill className="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold">{stats.pendingPrescriptions}</div>
                        <p className="text-xs text-muted-foreground">
                            Awaiting verification
                        </p>
                    </CardContent>
                </Card>
            </div>

            {/* Quick Actions */}
            <Card>
                <CardHeader>
                    <CardTitle>Doctor Tools</CardTitle>
                    <CardDescription>Access your medical tools and patient management</CardDescription>
                </CardHeader>
                <CardContent>
                    <div className="grid gap-4 md:grid-cols-3">
                        {hasPermission('work_on_queue') && (
                            <Link href="/doctor/queue">
                                <Button variant="outline" className="w-full justify-start">
                                    <Clock className="h-4 w-4 mr-2" />
                                    Patient Queue
                                </Button>
                            </Link>
                        )}
                        {hasPermission('view_appointments') && (
                            <Link href="/doctor/appointments">
                                <Button variant="outline" className="w-full justify-start">
                                    <Calendar className="h-4 w-4 mr-2" />
                                    My Appointments
                                </Button>
                            </Link>
                        )}
                        {hasPermission('manage_prescriptions') && (
                            <Link href="/doctor/prescriptions">
                                <Button variant="outline" className="w-full justify-start">
                                    <Pill className="h-4 w-4 mr-2" />
                                    Prescriptions
                                </Button>
                            </Link>
                        )}
                        {hasPermission('view_medical_records') && (
                            <Link href="/doctor/medical-records">
                                <Button variant="outline" className="w-full justify-start">
                                    <FileText className="h-4 w-4 mr-2" />
                                    Medical Records
                                </Button>
                            </Link>
                        )}
                        {hasPermission('view_patients') && (
                            <Link href="/doctor/patients">
                                <Button variant="outline" className="w-full justify-start">
                                    <UserCheck className="h-4 w-4 mr-2" />
                                    My Patients
                                </Button>
                            </Link>
                        )}
                        {hasPermission('view_analytics') && (
                            <Link href="/doctor/analytics">
                                <Button variant="outline" className="w-full justify-start">
                                    <BarChart3 className="h-4 w-4 mr-2" />
                                    My Analytics
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
