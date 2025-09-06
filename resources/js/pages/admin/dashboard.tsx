import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { adminDashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Users,
    Calendar,
    FileText,
    Stethoscope,
    Building2,
    TrendingUp,
    AlertCircle
} from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Admin Dashboard',
        href: adminDashboard(),
    },
];

export default function AdminDashboard() {
    const stats = [
        {
            title: 'Total Staff',
            value: '24',
            change: '+2 this month',
            icon: Users,
            color: 'text-blue-600'
        },
        {
            title: 'Active Doctors',
            value: '8',
            change: '+1 this month',
            icon: Stethoscope,
            color: 'text-green-600'
        },
        {
            title: 'Today\'s Appointments',
            value: '45',
            change: '+12% from yesterday',
            icon: Calendar,
            color: 'text-purple-600'
        },
        {
            title: 'Total Patients',
            value: '1,234',
            change: '+5% this month',
            icon: Users,
            color: 'text-orange-600'
        }
    ];

    const recentActivities = [
        {
            id: 1,
            type: 'appointment',
            message: 'New appointment scheduled for Dr. Smith',
            time: '2 minutes ago',
            status: 'success'
        },
        {
            id: 2,
            type: 'staff',
            message: 'New receptionist added to the system',
            time: '15 minutes ago',
            status: 'info'
        },
        {
            id: 3,
            type: 'patient',
            message: 'Patient John Doe checked in',
            time: '30 minutes ago',
            status: 'success'
        },
        {
            id: 4,
            type: 'system',
            message: 'System maintenance scheduled for tonight',
            time: '1 hour ago',
            status: 'warning'
        }
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Admin Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
                <div>
                    <h1 className="text-3xl font-bold tracking-tight">Admin Dashboard</h1>
                    <p className="text-muted-foreground">
                        Welcome back! Here's what's happening at your clinic today.
                    </p>
                </div>

                {/* Stats Grid */}
                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    {stats.map((stat) => (
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
                                {recentActivities.map((activity) => (
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
                                ))}
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
                            <Button className="w-full justify-start" variant="outline">
                                <Users className="mr-2 h-4 w-4" />
                                Manage Staff
                            </Button>
                            <Button className="w-full justify-start" variant="outline">
                                <Stethoscope className="mr-2 h-4 w-4" />
                                Doctor Management
                            </Button>
                            <Button className="w-full justify-start" variant="outline">
                                <Calendar className="mr-2 h-4 w-4" />
                                View Appointments
                            </Button>
                            <Button className="w-full justify-start" variant="outline">
                                <Building2 className="mr-2 h-4 w-4" />
                                Room Management
                            </Button>
                            <Button className="w-full justify-start" variant="outline">
                                <FileText className="mr-2 h-4 w-4" />
                                Generate Reports
                            </Button>
                            <Button className="w-full justify-start" variant="outline">
                                <TrendingUp className="mr-2 h-4 w-4" />
                                View Analytics
                            </Button>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AppLayout>
    );
}
