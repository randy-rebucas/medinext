import { Head } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '@/layouts/app-layout';
import { adminAnalytics } from '@/routes';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Admin Dashboard',
        href: '/admin/dashboard',
    },
    {
        title: 'Analytics',
        href: adminAnalytics(),
    },
];
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    BarChart3,
    Activity,
    CheckCircle,
    AlertCircle,
    Star,
    Users,
    Calendar,
    DollarSign,
    Stethoscope,
    TrendingUp
} from 'lucide-react';

export default function Analytics() {

    const topPerformers = [
        {
            name: 'Dr. Sarah Johnson',
            specialty: 'Cardiology',
            patients: 156,
            rating: 4.9,
            revenue: '$12,450'
        },
        {
            name: 'Dr. Michael Brown',
            specialty: 'Pediatrics',
            patients: 203,
            rating: 4.8,
            revenue: '$15,230'
        },
        {
            name: 'Dr. Emily Davis',
            specialty: 'Dermatology',
            patients: 89,
            rating: 4.7,
            revenue: '$8,920'
        }
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Analytics - Medinext">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&family=instrument-sans:400,500,600" rel="stylesheet" />
            </Head>

            <div className="min-h-screen bg-gradient-to-br from-slate-50 via-white to-blue-50 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900">
                <div className="space-y-6 p-6">


                    <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                        {/* Top Performing Doctors */}
                        <Card className="col-span-2 border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                            <CardHeader>
                                <div className="flex items-center justify-between">
                                    <div>
                                        <CardTitle className="text-xl font-semibold text-slate-900 dark:text-white">Analytics Dashboard</CardTitle>
                                        <CardDescription className="text-slate-600 dark:text-slate-300">
                                            Comprehensive insights into your clinic's performance
                                        </CardDescription>
                                    </div>
                                    <div className="flex space-x-3">
                                        <Button
                                            variant="outline"
                                            className="border-slate-300 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-700"
                                        >
                                            <Activity className="mr-2 h-4 w-4" />
                                            Real-time Data
                                        </Button>
                                        <Button
                                            className="bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white shadow-lg"
                                        >
                                            <BarChart3 className="mr-2 h-4 w-4" />
                                            Export Report
                                        </Button>
                                    </div>
                                </div>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-4">
                                    {topPerformers.map((doctor, index) => (
                                        <div key={doctor.name} className="flex items-center justify-between p-4 border border-slate-200 dark:border-slate-700 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors duration-200">
                                            <div className="flex items-center space-x-4">
                                                <div className="flex-shrink-0 w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-500 text-white rounded-full flex items-center justify-center text-sm font-bold">
                                                    {index + 1}
                                                </div>
                                                <div>
                                                    <h3 className="font-semibold text-slate-900 dark:text-white">{doctor.name}</h3>
                                                    <p className="text-sm text-slate-600 dark:text-slate-300">{doctor.specialty}</p>
                                                </div>
                                            </div>
                                            <div className="flex items-center space-x-6 text-sm">
                                                <div className="text-center">
                                                    <div className="font-semibold text-slate-900 dark:text-white">{doctor.patients}</div>
                                                    <div className="text-slate-500 dark:text-slate-400">Patients</div>
                                                </div>
                                                <div className="text-center">
                                                    <div className="flex items-center justify-center">
                                                        <Star className="h-3 w-3 text-yellow-500 mr-1" />
                                                        <span className="font-semibold text-slate-900 dark:text-white">{doctor.rating}</span>
                                                    </div>
                                                    <div className="text-slate-500 dark:text-slate-400">Rating</div>
                                                </div>
                                                <div className="text-center">
                                                    <div className="font-semibold text-slate-900 dark:text-white">{doctor.revenue}</div>
                                                    <div className="text-slate-500 dark:text-slate-400">Revenue</div>
                                                </div>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </CardContent>
                        </Card>

                        {/* Quick Insights */}
                        <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                            <CardHeader>
                                <CardTitle className="text-xl font-semibold text-slate-900 dark:text-white">Quick Insights</CardTitle>
                                <CardDescription className="text-slate-600 dark:text-slate-300">
                                    Key performance indicators
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-6">
                                <div className="space-y-4">
                                    <div className="flex items-center justify-between p-3 border border-slate-200 dark:border-slate-700 rounded-lg">
                                        <span className="text-sm font-medium text-slate-700 dark:text-slate-300">Patient Satisfaction</span>
                                        <Badge className="bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400">
                                            <CheckCircle className="mr-1 h-3 w-3" />
                                            4.8/5
                                        </Badge>
                                    </div>
                                    <div className="flex items-center justify-between p-3 border border-slate-200 dark:border-slate-700 rounded-lg">
                                        <span className="text-sm font-medium text-slate-700 dark:text-slate-300">Appointment No-shows</span>
                                        <Badge className="bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400">
                                            <AlertCircle className="mr-1 h-3 w-3" />
                                            3.2%
                                        </Badge>
                                    </div>
                                    <div className="flex items-center justify-between p-3 border border-slate-200 dark:border-slate-700 rounded-lg">
                                        <span className="text-sm font-medium text-slate-700 dark:text-slate-300">Average Wait Time</span>
                                        <Badge className="bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400">
                                            <Activity className="mr-1 h-3 w-3" />
                                            12 min
                                        </Badge>
                                    </div>
                                    <div className="flex items-center justify-between p-3 border border-slate-200 dark:border-slate-700 rounded-lg">
                                        <span className="text-sm font-medium text-slate-700 dark:text-slate-300">Revenue per Patient</span>
                                        <Badge className="bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-400">
                                            <DollarSign className="mr-1 h-3 w-3" />
                                            $89
                                        </Badge>
                                    </div>
                                </div>

                                <div className="pt-4 border-t border-slate-200 dark:border-slate-700">
                                    <h4 className="font-semibold mb-4 text-slate-900 dark:text-white">Trends</h4>
                                    <div className="space-y-3 text-sm">
                                        <div className="flex items-center justify-between p-2 rounded-lg bg-green-50 dark:bg-green-900/10">
                                            <span className="text-slate-700 dark:text-slate-300">Patient Growth</span>
                                            <div className="flex items-center text-green-600 dark:text-green-400">
                                                <TrendingUp className="mr-1 h-3 w-3" />
                                                +12.5%
                                            </div>
                                        </div>
                                        <div className="flex items-center justify-between p-2 rounded-lg bg-green-50 dark:bg-green-900/10">
                                            <span className="text-slate-700 dark:text-slate-300">Revenue Growth</span>
                                            <div className="flex items-center text-green-600 dark:text-green-400">
                                                <TrendingUp className="mr-1 h-3 w-3" />
                                                +15.2%
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
