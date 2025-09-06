import { Head } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '@/layouts/app-layout';
import { adminReports } from '@/routes';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Admin Dashboard',
        href: '/admin/dashboard',
    },
    {
        title: 'Reports',
        href: adminReports(),
    },
];
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    FileText,
    Download,
    BarChart3,
    PieChart,
    CheckCircle,
    Clock,
    MoreHorizontal,
    Users,
    Stethoscope,
    TrendingUp,
    Calendar
} from 'lucide-react';

export default function Reports() {
    const reports = [
        {
            id: 1,
            title: 'Monthly Patient Report',
            description: 'Patient statistics and demographics for the current month',
            type: 'Patient',
            lastGenerated: '2024-01-14',
            status: 'Ready'
        },
        {
            id: 2,
            title: 'Doctor Performance Report',
            description: 'Appointment statistics and performance metrics by doctor',
            type: 'Doctor',
            lastGenerated: '2024-01-13',
            status: 'Ready'
        },
        {
            id: 3,
            title: 'Revenue Report',
            description: 'Financial summary and billing statistics',
            type: 'Financial',
            lastGenerated: '2024-01-12',
            status: 'Ready'
        },
        {
            id: 4,
            title: 'Appointment Analytics',
            description: 'Appointment trends and scheduling patterns',
            type: 'Analytics',
            lastGenerated: '2024-01-11',
            status: 'Generating'
        }
    ];

    const quickStats = [
        {
            title: 'Total Patients',
            value: '1,234',
            change: '+5.2%',
            icon: Users,
            color: 'text-blue-600'
        },
        {
            title: 'Active Doctors',
            value: '8',
            change: '+1',
            icon: Stethoscope,
            color: 'text-green-600'
        },
        {
            title: 'Monthly Revenue',
            value: '$45,678',
            change: '+12.3%',
            icon: TrendingUp,
            color: 'text-purple-600'
        },
        {
            title: 'Appointments Today',
            value: '23',
            change: '+3',
            icon: Calendar,
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
                <div className="space-y-6 p-6">

                    <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                        {/* Available Reports */}
                        <Card className="col-span-2 border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                            <CardHeader>
                                <div className="flex items-center justify-between">
                                    <div>
                                        <CardTitle className="text-xl font-semibold text-slate-900 dark:text-white">Reports & Analytics</CardTitle>
                                        <CardDescription className="text-slate-600 dark:text-slate-300">
                                            Generate and view clinic reports and analytics
                                        </CardDescription>
                                    </div>
                                    <div className="flex space-x-3">
                                        <Button
                                            variant="outline"
                                            className="border-slate-300 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-700"
                                        >
                                            <BarChart3 className="mr-2 h-4 w-4" />
                                            View Analytics
                                        </Button>
                                        <Button
                                            className="bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white shadow-lg"
                                        >
                                            <FileText className="mr-2 h-4 w-4" />
                                            Generate New Report
                                        </Button>
                                    </div>
                                </div>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-4">
                                    {reports.map((report) => (
                                        <div key={report.id} className="flex items-center justify-between p-4 border border-slate-200 dark:border-slate-700 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors duration-200">
                                            <div className="flex items-center space-x-4">
                                                <div className="flex-shrink-0 p-2 bg-slate-100 dark:bg-slate-700 rounded-lg">
                                                    {report.type === 'Patient' && <Users className="h-5 w-5 text-blue-600" />}
                                                    {report.type === 'Doctor' && <Stethoscope className="h-5 w-5 text-green-600" />}
                                                    {report.type === 'Financial' && <TrendingUp className="h-5 w-5 text-purple-600" />}
                                                    {report.type === 'Analytics' && <BarChart3 className="h-5 w-5 text-orange-600" />}
                                                </div>
                                                <div>
                                                    <h3 className="font-semibold text-slate-900 dark:text-white">{report.title}</h3>
                                                    <p className="text-sm text-slate-600 dark:text-slate-300">{report.description}</p>
                                                    <p className="text-xs text-slate-500 dark:text-slate-400">
                                                        Last generated: {report.lastGenerated}
                                                    </p>
                                                </div>
                                            </div>
                                            <div className="flex items-center space-x-2">
                                                <Badge className={`font-medium ${
                                                    report.status === 'Ready'
                                                        ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400'
                                                        : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400'
                                                }`}>
                                                    {report.status === 'Ready' ? <CheckCircle className="mr-1 h-3 w-3" /> : <Clock className="mr-1 h-3 w-3" />}
                                                    {report.status}
                                                </Badge>
                                                <Button variant="outline" size="sm" className="hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:text-blue-600 dark:hover:text-blue-400">
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
                                <CardTitle className="text-xl font-semibold text-slate-900 dark:text-white">Quick Actions</CardTitle>
                                <CardDescription className="text-slate-600 dark:text-slate-300">
                                    Common report tasks
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-3">
                                <Button className="w-full justify-start h-12 border-slate-300 dark:border-slate-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:text-blue-600 dark:hover:text-blue-400" variant="outline">
                                    <Users className="mr-3 h-4 w-4" />
                                    Patient Demographics
                                </Button>
                                <Button className="w-full justify-start h-12 border-slate-300 dark:border-slate-600 hover:bg-green-50 dark:hover:bg-green-900/20 hover:text-green-600 dark:hover:text-green-400" variant="outline">
                                    <Stethoscope className="mr-3 h-4 w-4" />
                                    Doctor Performance
                                </Button>
                                <Button className="w-full justify-start h-12 border-slate-300 dark:border-slate-600 hover:bg-purple-50 dark:hover:bg-purple-900/20 hover:text-purple-600 dark:hover:text-purple-400" variant="outline">
                                    <Calendar className="mr-3 h-4 w-4" />
                                    Appointment Summary
                                </Button>
                                <Button className="w-full justify-start h-12 border-slate-300 dark:border-slate-600 hover:bg-orange-50 dark:hover:bg-orange-900/20 hover:text-orange-600 dark:hover:text-orange-400" variant="outline">
                                    <TrendingUp className="mr-3 h-4 w-4" />
                                    Revenue Report
                                </Button>
                                <Button className="w-full justify-start h-12 border-slate-300 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-700" variant="outline">
                                    <BarChart3 className="mr-3 h-4 w-4" />
                                    Analytics Dashboard
                                </Button>
                                <Button className="w-full justify-start h-12 border-slate-300 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-700" variant="outline">
                                    <PieChart className="mr-3 h-4 w-4" />
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
