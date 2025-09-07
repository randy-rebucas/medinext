import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    BarChart3,
    Download,
    Calendar,
    Users,
    Clock,
    FileText,
    TrendingUp,
    Building2,
    Shield
} from 'lucide-react';
import { receptionistReports } from '@/routes';
import { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Receptionist Dashboard',
        href: '/receptionist/dashboard',
    },
    {
        title: 'Reports',
        href: receptionistReports(),
    },
];

interface ReceptionistReportsProps {
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

export default function ReceptionistReports({
    user,
    permissions = []
}: ReceptionistReportsProps) {
    const hasPermission = (permission: string) => permissions.includes(permission);
    const reports = [
        {
            id: 1,
            title: 'Daily Appointment Report',
            description: 'Summary of appointments for today',
            type: 'Appointment',
            lastGenerated: '2024-01-15',
            status: 'Ready'
        },
        {
            id: 2,
            title: 'Patient Check-in Report',
            description: 'Patient check-in and check-out statistics',
            type: 'Patient',
            lastGenerated: '2024-01-14',
            status: 'Ready'
        },
        {
            id: 3,
            title: 'Queue Management Report',
            description: 'Patient queue and waiting time analysis',
            type: 'Queue',
            lastGenerated: '2024-01-13',
            status: 'Ready'
        },
        {
            id: 4,
            title: 'Receptionist Activity Report',
            description: 'Daily receptionist activities and tasks',
            type: 'Activity',
            lastGenerated: '2024-01-12',
            status: 'Generating'
        }
    ];

    const quickStats = [
        {
            title: 'Today\'s Appointments',
            value: '23',
            change: '+3',
            icon: Calendar,
            color: 'text-blue-600'
        },
        {
            title: 'Checked In',
            value: '18',
            change: '+2',
            icon: Users,
            color: 'text-green-600'
        },
        {
            title: 'Average Wait Time',
            value: '12 min',
            change: '-2 min',
            icon: Clock,
            color: 'text-purple-600'
        },
        {
            title: 'Completed Today',
            value: '15',
            change: '+5',
            icon: FileText,
            color: 'text-orange-600'
        }
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Reports - Medinext">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&family=instrument-sans:400,500,600" rel="stylesheet" />
            </Head>
            <div className="min-h-screen bg-gradient-to-br from-slate-50 via-white to-blue-50 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900">
                <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
                    {/* Modern Header */}
                    <div className="relative overflow-hidden rounded-2xl bg-gradient-to-r from-violet-600 to-purple-600 p-8 text-white shadow-xl">
                        <div className="absolute inset-0 bg-black/10"></div>
                        <div className="relative flex items-center justify-between">
                            <div>
                                <h1 className="text-3xl font-bold tracking-tight">Reports</h1>
                                <p className="mt-2 text-violet-100">
                                    {user?.clinic?.name || 'No Clinic'} • Generate and view receptionist reports
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
                                {hasPermission('generate_reports') && (
                                    <Button className="bg-white/20 hover:bg-white/30 text-white border-white/30 hover:border-white/40">
                                        <BarChart3 className="mr-2 h-4 w-4" />
                                        Generate Report
                                    </Button>
                                )}
                            </div>
                        </div>
                        {/* Decorative elements */}
                        <div className="absolute -top-4 -right-4 w-24 h-24 bg-white/10 rounded-full"></div>
                        <div className="absolute -bottom-2 -left-2 w-16 h-16 bg-white/5 rounded-full"></div>
                    </div>

                    {/* Quick Stats */}
                    <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                        {quickStats.map((stat) => (
                            <Card key={stat.title} className="relative overflow-hidden border-0 shadow-lg hover:shadow-xl transition-all duration-300 bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-800/20 dark:to-slate-700/20">
                                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                    <CardTitle className="text-sm font-medium text-slate-700 dark:text-slate-300">
                                        {stat.title}
                                    </CardTitle>
                                    <div className="p-2 bg-slate-500 rounded-lg">
                                        <stat.icon className="h-4 w-4 text-white" />
                                    </div>
                                </CardHeader>
                                <CardContent>
                                    <div className="text-3xl font-bold text-slate-900 dark:text-white">{stat.value}</div>
                                    <div className="flex items-center mt-2">
                                        <TrendingUp className="h-3 w-3 text-slate-500 mr-1" />
                                        <p className="text-xs text-slate-600 dark:text-slate-400">
                                            {stat.change} from yesterday
                                        </p>
                                    </div>
                                </CardContent>
                            </Card>
                        ))}
                    </div>

                    <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                        {/* Available Reports */}
                        <Card className="col-span-2 border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                            <CardHeader>
                                <CardTitle className="text-lg font-semibold text-slate-900 dark:text-white">Available Reports</CardTitle>
                                <CardDescription className="text-slate-600 dark:text-slate-300">
                                    Generate and download receptionist reports
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-4">
                                    {reports.map((report) => (
                                        <div key={report.id} className="flex items-center justify-between p-4 border border-slate-200 dark:border-slate-700 rounded-lg hover:shadow-md transition-all duration-200 bg-slate-50 dark:bg-slate-700/50">
                                            <div className="flex items-center space-x-4">
                                                <div className="flex-shrink-0">
                                                    <div className="p-1 bg-violet-100 dark:bg-violet-900/20 rounded-md">
                                                        {report.type === 'Appointment' && <Calendar className="h-5 w-5 text-violet-600 dark:text-violet-400" />}
                                                        {report.type === 'Patient' && <Users className="h-5 w-5 text-violet-600 dark:text-violet-400" />}
                                                        {report.type === 'Queue' && <Clock className="h-5 w-5 text-violet-600 dark:text-violet-400" />}
                                                        {report.type === 'Activity' && <FileText className="h-5 w-5 text-violet-600 dark:text-violet-400" />}
                                                    </div>
                                                </div>
                                                <div>
                                                    <h3 className="font-medium text-slate-900 dark:text-white">{report.title}</h3>
                                                    <p className="text-sm text-slate-600 dark:text-slate-400">{report.description}</p>
                                                    <p className="text-xs text-slate-500 dark:text-slate-500">
                                                        Last generated: {report.lastGenerated}
                                                    </p>
                                                </div>
                                            </div>
                                            <div className="flex items-center space-x-2">
                                                <Badge variant={report.status === 'Ready' ? 'default' : 'secondary'} className="border-0">
                                                    {report.status}
                                                </Badge>
                                                <Button variant="outline" size="sm" className="border-slate-200 dark:border-slate-700 hover:bg-violet-50 dark:hover:bg-violet-900/20 hover:border-violet-300 dark:hover:border-violet-600 transition-all duration-200">
                                                    <Download className="h-4 w-4" />
                                                </Button>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </CardContent>
                        </Card>

                        {/* Quick Actions */}
                        <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                            <CardHeader>
                                <CardTitle className="text-lg font-semibold text-slate-900 dark:text-white">Quick Actions</CardTitle>
                                <CardDescription className="text-slate-600 dark:text-slate-300">
                                    Common report tasks
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-2">
                                <Button className="w-full justify-start border-slate-200 dark:border-slate-700 hover:bg-violet-50 dark:hover:bg-violet-900/20 hover:border-violet-300 dark:hover:border-violet-600 transition-all duration-200" variant="outline">
                                    <Calendar className="mr-2 h-4 w-4" />
                                    Daily Appointments
                                </Button>
                                <Button className="w-full justify-start border-slate-200 dark:border-slate-700 hover:bg-violet-50 dark:hover:bg-violet-900/20 hover:border-violet-300 dark:hover:border-violet-600 transition-all duration-200" variant="outline">
                                    <Users className="mr-2 h-4 w-4" />
                                    Patient Check-ins
                                </Button>
                                <Button className="w-full justify-start border-slate-200 dark:border-slate-700 hover:bg-violet-50 dark:hover:bg-violet-900/20 hover:border-violet-300 dark:hover:border-violet-600 transition-all duration-200" variant="outline">
                                    <Clock className="mr-2 h-4 w-4" />
                                    Queue Analysis
                                </Button>
                                <Button className="w-full justify-start border-slate-200 dark:border-slate-700 hover:bg-violet-50 dark:hover:bg-violet-900/20 hover:border-violet-300 dark:hover:border-violet-600 transition-all duration-200" variant="outline">
                                    <FileText className="mr-2 h-4 w-4" />
                                    Activity Summary
                                </Button>
                                <Button className="w-full justify-start border-slate-200 dark:border-slate-700 hover:bg-violet-50 dark:hover:bg-violet-900/20 hover:border-violet-300 dark:hover:border-violet-600 transition-all duration-200" variant="outline">
                                    <TrendingUp className="mr-2 h-4 w-4" />
                                    Performance Metrics
                                </Button>
                                <Button className="w-full justify-start border-slate-200 dark:border-slate-700 hover:bg-violet-50 dark:hover:bg-violet-900/20 hover:border-violet-300 dark:hover:border-violet-600 transition-all duration-200" variant="outline">
                                    <BarChart3 className="mr-2 h-4 w-4" />
                                    Custom Report
                                </Button>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
