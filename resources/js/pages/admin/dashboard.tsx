import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '@/layouts/app-layout';
import {
    adminDashboard,
    adminStaff,
    adminDoctors,
    adminAppointments,
    adminPatients,
    adminRooms,
    adminReports,
    adminAnalytics,
    adminClinicSettings
} from '@/routes';
import { type BreadcrumbItem, type AdminDashboardData, type StatCard, type RecentActivity } from '@/types';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Skeleton } from '@/components/ui/skeleton';
import {
    Users,
    Calendar,
    FileText,
    Stethoscope,
    Building2,
    TrendingUp,
    AlertCircle,
    Plus,
    Settings,
    RefreshCw
} from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Admin Dashboard',
        href: adminDashboard(),
    },
];

interface AdminDashboardProps {
    user: AdminDashboardData['user'];
    stats: AdminDashboardData['stats'];
    permissions: AdminDashboardData['permissions'];
}

export default function AdminDashboard({ user, stats }: AdminDashboardProps) {
    const [isRefreshing, setIsRefreshing] = useState(false);

    // Refresh dashboard data
    const refreshDashboard = () => {
        setIsRefreshing(true);
        router.reload({
            only: ['stats'],
            onFinish: () => setIsRefreshing(false)
        });
    };

    // Validate stats data
    const hasValidStats = stats && typeof stats === 'object';

    // Create dynamic stats cards from the data
    const statsCards: StatCard[] = hasValidStats ? [
        {
            title: 'Total Staff',
            value: stats.totalUsers,
            change: stats.changes?.totalUsers || 'No change this month',
            icon: Users,
            color: 'text-blue-600'
        },
        {
            title: 'Total Patients',
            value: stats.totalPatients,
            change: stats.changes?.totalPatients || 'No change this month',
            icon: Users,
            color: 'text-orange-600'
        },
        {
            title: 'Today\'s Appointments',
            value: stats.todayAppointments,
            change: stats.changes?.todayAppointments || 'No change from yesterday',
            icon: Calendar,
            color: 'text-purple-600'
        },
        {
            title: 'Active Queue',
            value: stats.activeQueue,
            change: stats.changes?.activeQueue || 'Real-time',
            icon: Stethoscope,
            color: 'text-green-600'
        },
        {
            title: 'Total Appointments',
            value: stats.totalAppointments,
            change: stats.changes?.totalAppointments || 'No change this month',
            icon: Calendar,
            color: 'text-blue-500'
        },
        {
            title: 'Total Encounters',
            value: stats.totalEncounters,
            change: stats.changes?.totalEncounters || 'No change this month',
            icon: FileText,
            color: 'text-indigo-600'
        },
        {
            title: 'Pending Prescriptions',
            value: stats.pendingPrescriptions,
            change: stats.changes?.pendingPrescriptions || 'Needs attention',
            icon: FileText,
            color: 'text-red-600'
        },
        {
            title: 'Completed Today',
            value: stats.completedEncounters,
            change: stats.changes?.completedEncounters || 'Today\'s completions',
            icon: TrendingUp,
            color: 'text-green-500'
        }
    ] : [];

    // Transform recent activity data
    const recentActivities: RecentActivity[] = hasValidStats && stats.recentActivity ?
        stats.recentActivity.map((activity) => ({
            id: activity.id,
            type: activity.type as RecentActivity['type'],
            message: activity.description,
            time: formatTimeAgo(activity.created_at),
            status: getActivityStatus(activity.type)
        })) : [];

    // Helper function to format time ago
    function formatTimeAgo(dateString: string): string {
        const date = new Date(dateString);
        const now = new Date();
        const diffInMinutes = Math.floor((now.getTime() - date.getTime()) / (1000 * 60));

        if (diffInMinutes < 1) return 'Just now';
        if (diffInMinutes < 60) return `${diffInMinutes} minutes ago`;
        if (diffInMinutes < 1440) return `${Math.floor(diffInMinutes / 60)} hours ago`;
        return `${Math.floor(diffInMinutes / 1440)} days ago`;
    }

    // Helper function to determine activity status
    function getActivityStatus(type: string): RecentActivity['status'] {
        switch (type) {
            case 'appointment':
            case 'patient':
                return 'success';
            case 'staff':
                return 'info';
            case 'system':
                return 'warning';
            default:
                return 'info';
        }
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Admin Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Admin Dashboard</h1>
                        <p className="text-muted-foreground">
                            Welcome back, {user.name}! Here's what's happening at your clinic today.
                        </p>
                    </div>
                    <div className="flex space-x-3">
                        <Button
                            variant="outline"
                            onClick={refreshDashboard}
                            disabled={isRefreshing}
                            className="gap-2"
                        >
                            <RefreshCw className={`h-4 w-4 ${isRefreshing ? 'animate-spin' : ''}`} />
                            {isRefreshing ? 'Refreshing...' : 'Refresh'}
                        </Button>
                        <Link href={adminStaff()}>
                            <Button variant="outline" className="gap-2">
                                <Plus className="h-4 w-4" />
                                Add Staff
                            </Button>
                        </Link>
                        <Link href={adminDoctors()}>
                            <Button variant="outline" className="gap-2">
                                <Plus className="h-4 w-4" />
                                Add Doctor
                            </Button>
                        </Link>
                        <Link href={adminAppointments()}>
                            <Button className="gap-2">
                                <Plus className="h-4 w-4" />
                                New Appointment
                            </Button>
                        </Link>
                    </div>
                </div>

                {/* Stats Grid */}
                {hasValidStats ? (
                    <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-4">
                        {statsCards.slice(0, 4).map((stat) => (
                            <Card key={stat.title}>
                                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                    <CardTitle className="text-sm font-medium">
                                        {stat.title}
                                    </CardTitle>
                                    <stat.icon className={`h-4 w-4 ${stat.color}`} />
                                </CardHeader>
                                <CardContent>
                                    <div className="text-2xl font-bold">{stat.value}</div>
                                    <p className="text-xs text-muted-foreground">
                                        {stat.change}
                                    </p>
                                </CardContent>
                            </Card>
                        ))}
                    </div>
                ) : (
                    <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-4">
                        {Array.from({ length: 4 }).map((_, index) => (
                            <Card key={index}>
                                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                    <Skeleton className="h-4 w-24" />
                                    <Skeleton className="h-4 w-4" />
                                </CardHeader>
                                <CardContent>
                                    <Skeleton className="h-8 w-16 mb-2" />
                                    <Skeleton className="h-3 w-20" />
                                </CardContent>
                            </Card>
                        ))}
                    </div>
                )}

                {/* Second Row of Stats */}
                {hasValidStats ? (
                    <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-4">
                        {statsCards.slice(4).map((stat) => (
                            <Card key={stat.title}>
                                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                    <CardTitle className="text-sm font-medium">
                                        {stat.title}
                                    </CardTitle>
                                    <stat.icon className={`h-4 w-4 ${stat.color}`} />
                                </CardHeader>
                                <CardContent>
                                    <div className="text-2xl font-bold">{stat.value}</div>
                                    <p className="text-xs text-muted-foreground">
                                        {stat.change}
                                    </p>
                                </CardContent>
                            </Card>
                        ))}
                    </div>
                ) : (
                    <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-4">
                        {Array.from({ length: 4 }).map((_, index) => (
                            <Card key={index}>
                                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                    <Skeleton className="h-4 w-24" />
                                    <Skeleton className="h-4 w-4" />
                                </CardHeader>
                                <CardContent>
                                    <Skeleton className="h-8 w-16 mb-2" />
                                    <Skeleton className="h-3 w-20" />
                                </CardContent>
                            </Card>
                        ))}
                    </div>
                )}

                {/* Clinic Information */}
                {user.clinic && (
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Building2 className="h-5 w-5" />
                                Clinic Information
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="grid gap-4 md:grid-cols-2">
                                <div>
                                    <h3 className="font-semibold text-lg">{user.clinic.name}</h3>
                                    <p className="text-muted-foreground">
                                        {user.clinic.address ?
                                            [
                                                user.clinic.address.street,
                                                user.clinic.address.city,
                                                user.clinic.address.state,
                                                user.clinic.address.country
                                            ].filter(Boolean).join(', ') || 'Address not specified'
                                            : 'Address not specified'
                                        }
                                    </p>
                                    <p className="text-sm text-muted-foreground mt-2">
                                        License: {user.clinic.settings?.license_number || 'Not specified'}
                                    </p>
                                </div>
                                <div className="text-right">
                                    <p className="text-sm text-muted-foreground">Phone: {user.clinic.phone}</p>
                                    <p className="text-sm text-muted-foreground">Email: {user.clinic.email}</p>
                                    <Badge variant="outline" className="mt-2">
                                        {user.role}
                                    </Badge>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                )}

                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-7">
                    {/* Recent Activities */}
                    <Card className="col-span-4">
                        <CardHeader>
                            <CardTitle>Recent Activities</CardTitle>
                            <CardDescription>
                                Latest updates from your clinic
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                {recentActivities.length > 0 ? (
                                    recentActivities.map((activity) => (
                                        <div key={activity.id} className="flex items-center space-x-4">
                                            <div className="flex-shrink-0">
                                                {activity.type === 'appointment' && <Calendar className="h-4 w-4 text-blue-600" />}
                                                {activity.type === 'staff' && <Users className="h-4 w-4 text-green-600" />}
                                                {activity.type === 'patient' && <Users className="h-4 w-4 text-purple-600" />}
                                                {activity.type === 'system' && <AlertCircle className="h-4 w-4 text-orange-600" />}
                                            </div>
                                            <div className="flex-1 space-y-1">
                                                <p className="text-sm font-medium leading-none">
                                                    {activity.message}
                                                </p>
                                                <p className="text-sm text-muted-foreground">
                                                    {activity.time}
                                                </p>
                                            </div>
                                            <Badge variant={activity.status === 'success' ? 'default' : activity.status === 'warning' ? 'destructive' : 'secondary'}>
                                                {activity.status}
                                            </Badge>
                                        </div>
                                    ))
                                ) : (
                                    <div className="text-center py-8 text-muted-foreground">
                                        <AlertCircle className="h-8 w-8 mx-auto mb-2" />
                                        <p>No recent activities found</p>
                                    </div>
                                )}
                            </div>
                        </CardContent>
                    </Card>

                    {/* Quick Actions */}
                    <Card className="col-span-3">
                        <CardHeader>
                            <CardTitle>Quick Actions</CardTitle>
                            <CardDescription>
                                Common administrative tasks
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-2">
                            <Link href={adminStaff()}>
                                <Button className="w-full justify-start mb-2" variant="outline">
                                    <Users className="mr-2 h-4 w-4" />
                                    Manage Staff
                                </Button>
                            </Link>
                            <Link href={adminDoctors()}>
                                <Button className="w-full justify-start mb-2" variant="outline">
                                    <Stethoscope className="mr-2 h-4 w-4" />
                                    Doctor Management
                                </Button>
                            </Link>
                            <Link href={adminAppointments()}>
                                <Button className="w-full justify-start mb-2" variant="outline">
                                    <Calendar className="mr-2 h-4 w-4" />
                                    View Appointments
                                </Button>
                            </Link>
                            <Link href={adminPatients()}>
                                <Button className="w-full justify-start mb-2" variant="outline">
                                    <Users className="mr-2 h-4 w-4" />
                                    Patient Management
                                </Button>
                            </Link>
                            <Link href={adminRooms()}>
                                <Button className="w-full justify-start mb-2" variant="outline">
                                    <Building2 className="mr-2 h-4 w-4" />
                                    Room Management
                                </Button>
                            </Link>
                            <Link href={adminReports()}>
                                <Button className="w-full justify-start mb-2" variant="outline">
                                    <FileText className="mr-2 h-4 w-4" />
                                    Generate Reports
                                </Button>
                            </Link>
                            <Link href={adminAnalytics()}>
                                <Button className="w-full justify-start mb-2" variant="outline">
                                    <TrendingUp className="mr-2 h-4 w-4" />
                                    View Analytics
                                </Button>
                            </Link>
                            <Link href={adminClinicSettings()}>
                                <Button className="w-full justify-start mb-2" variant="outline">
                                    <Settings className="mr-2 h-4 w-4" />
                                    Clinic Settings
                                </Button>
                            </Link>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AppLayout>
    );
}
