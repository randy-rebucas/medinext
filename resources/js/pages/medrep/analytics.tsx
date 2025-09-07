import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    BarChart3,
    TrendingUp,
    TrendingDown,
    Users,
    Calendar,
    MessageSquare,
    Package,
    Target,
    Building2,
    Shield
} from 'lucide-react';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Analytics',
        href: '/medrep/analytics',
    },
    {
        title: 'Analytics',
        href: '/medrep/analytics',
    },
];

interface MedrepAnalyticsProps {
    user?: {
        id: number;
        name: string;
        email: string;
        role: string;
        company_id?: number;
        company?: {
            id: number;
            name: string;
        };
    };
}

export default function MedrepAnalytics({ user }: MedrepAnalyticsProps = {}) {
    const analytics = [
        {
            title: 'Total Interactions',
            value: '156',
            change: '+12.5%',
            trend: 'up',
            icon: MessageSquare,
            color: 'text-blue-600'
        },
        {
            title: 'Active Doctors',
            value: '24',
            change: '+2',
            trend: 'up',
            icon: Users,
            color: 'text-green-600'
        },
        {
            title: 'Meetings Scheduled',
            value: '45',
            change: '+8.7%',
            trend: 'up',
            icon: Calendar,
            color: 'text-purple-600'
        },
        {
            title: 'Product Samples',
            value: '89',
            change: '-3.2%',
            trend: 'down',
            icon: Package,
            color: 'text-orange-600'
        }
    ];

    const topPerformers = [
        {
            name: 'Dr. Sarah Johnson',
            specialty: 'Cardiology',
            interactions: 25,
            rating: 4.9,
            samples: 12
        },
        {
            name: 'Dr. Emily Davis',
            specialty: 'Dermatology',
            interactions: 22,
            rating: 4.8,
            samples: 15
        },
        {
            name: 'Dr. Michael Brown',
            specialty: 'Pediatrics',
            interactions: 18,
            rating: 4.7,
            samples: 8
        }
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Analytics - Medinext">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&family=instrument-sans:400,500,600" rel="stylesheet" />
            </Head>
            <div className="min-h-screen bg-gradient-to-br from-slate-50 via-white to-blue-50 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900">
                <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
                    {/* Modern Header */}
                    <div className="relative overflow-hidden rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 p-8 text-white shadow-xl">
                        <div className="absolute inset-0 bg-black/10"></div>
                        <div className="relative flex items-center justify-between">
                            <div>
                                <h1 className="text-3xl font-bold tracking-tight">
                                    Analytics Dashboard
                                </h1>
                                <p className="mt-2 text-emerald-100">
                                    Comprehensive insights into your medical representative performance
                                </p>
                            </div>
                            <div className="flex items-center gap-3">
                                <Badge variant="secondary" className="flex items-center gap-1 bg-white/20 text-white border-white/30 hover:bg-white/30">
                                    <Shield className="h-3 w-3" />
                                    Medical Rep
                                </Badge>
                                {user?.company && (
                                    <Badge variant="secondary" className="flex items-center gap-1 bg-white/20 text-white border-white/30 hover:bg-white/30">
                                        <Building2 className="h-3 w-3" />
                                        {user.company.name}
                                    </Badge>
                                )}
                            </div>
                        </div>
                        {/* Decorative elements */}
                        <div className="absolute -top-4 -right-4 w-24 h-24 bg-white/10 rounded-full"></div>
                        <div className="absolute -bottom-2 -left-2 w-16 h-16 bg-white/5 rounded-full"></div>
                    </div>

                    {/* Action Buttons */}
                    <div className="flex justify-end gap-3">
                        <Button variant="outline" className="border-slate-200 dark:border-slate-700 hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:border-blue-300 dark:hover:border-blue-600 transition-all duration-200">
                            <BarChart3 className="mr-2 h-4 w-4" />
                            Export Report
                        </Button>
                        <Button className="bg-emerald-600 hover:bg-emerald-700 text-white">
                            <Target className="mr-2 h-4 w-4" />
                            View Details
                        </Button>
                    </div>

                    {/* Key Metrics */}
                    <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                        {analytics.map((metric, index) => {
                            const colors = [
                                'from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20',
                                'from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20',
                                'from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20',
                                'from-orange-50 to-orange-100 dark:from-orange-900/20 dark:to-orange-800/20'
                            ];
                            const iconColors = [
                                'bg-blue-500',
                                'bg-green-500',
                                'bg-purple-500',
                                'bg-orange-500'
                            ];

                            return (
                                <Card key={metric.title} className={`relative overflow-hidden border-0 shadow-lg hover:shadow-xl transition-all duration-300 bg-gradient-to-br ${colors[index]}`}>
                                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                        <CardTitle className="text-sm font-medium text-slate-700 dark:text-slate-300">
                                            {metric.title}
                                        </CardTitle>
                                        <div className={`p-2 ${iconColors[index]} rounded-lg`}>
                                            <metric.icon className="h-4 w-4 text-white" />
                                        </div>
                                    </CardHeader>
                                    <CardContent>
                                        <div className="text-3xl font-bold text-slate-900 dark:text-white">{metric.value}</div>
                                        <div className="flex items-center mt-2">
                                            {metric.trend === 'up' ? (
                                                <TrendingUp className="h-3 w-3 text-green-500 mr-1" />
                                            ) : (
                                                <TrendingDown className="h-3 w-3 text-red-500 mr-1" />
                                            )}
                                            <p className="text-xs text-slate-600 dark:text-slate-400">
                                                {metric.change} from last month
                                            </p>
                                        </div>
                                    </CardContent>
                                </Card>
                            );
                        })}
                    </div>

                    <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                        {/* Top Performing Doctors */}
                        <Card className="col-span-2 border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                            <CardHeader>
                                <CardTitle className="text-lg font-semibold text-slate-900 dark:text-white">Top Performing Doctors</CardTitle>
                                <CardDescription className="text-slate-600 dark:text-slate-300">
                                    Doctors with highest interaction rates and engagement
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-4">
                                    {topPerformers.map((doctor, index) => (
                                        <div key={doctor.name} className="flex items-center justify-between p-4 rounded-lg bg-slate-50 dark:bg-slate-700/50 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                                            <div className="flex items-center space-x-4">
                                                <div className="flex-shrink-0 w-8 h-8 bg-emerald-500 text-white rounded-full flex items-center justify-center text-sm font-medium">
                                                    {index + 1}
                                                </div>
                                                <div>
                                                    <h3 className="font-medium text-slate-900 dark:text-white">{doctor.name}</h3>
                                                    <p className="text-sm text-slate-600 dark:text-slate-400">{doctor.specialty}</p>
                                                </div>
                                            </div>
                                            <div className="flex items-center space-x-4 text-sm">
                                                <div className="text-center">
                                                    <div className="font-medium text-slate-900 dark:text-white">{doctor.interactions}</div>
                                                    <div className="text-slate-600 dark:text-slate-400">Interactions</div>
                                                </div>
                                                <div className="text-center">
                                                    <div className="font-medium text-slate-900 dark:text-white">{doctor.rating}</div>
                                                    <div className="text-slate-600 dark:text-slate-400">Rating</div>
                                                </div>
                                                <div className="text-center">
                                                    <div className="font-medium text-slate-900 dark:text-white">{doctor.samples}</div>
                                                    <div className="text-slate-600 dark:text-slate-400">Samples</div>
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
                                <CardTitle className="text-lg font-semibold text-slate-900 dark:text-white">Quick Insights</CardTitle>
                                <CardDescription className="text-slate-600 dark:text-slate-300">
                                    Key performance indicators
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="space-y-3">
                                    <div className="flex items-center justify-between p-3 rounded-lg bg-slate-50 dark:bg-slate-700/50">
                                        <span className="text-sm font-medium text-slate-700 dark:text-slate-300">Meeting Success Rate</span>
                                        <Badge className="bg-emerald-100 text-emerald-800 border-0">87%</Badge>
                                    </div>
                                    <div className="flex items-center justify-between p-3 rounded-lg bg-slate-50 dark:bg-slate-700/50">
                                        <span className="text-sm font-medium text-slate-700 dark:text-slate-300">Sample Conversion</span>
                                        <Badge className="bg-blue-100 text-blue-800 border-0">23%</Badge>
                                    </div>
                                    <div className="flex items-center justify-between p-3 rounded-lg bg-slate-50 dark:bg-slate-700/50">
                                        <span className="text-sm font-medium text-slate-700 dark:text-slate-300">Average Response Time</span>
                                        <Badge className="bg-orange-100 text-orange-800 border-0">2.3 days</Badge>
                                    </div>
                                    <div className="flex items-center justify-between p-3 rounded-lg bg-slate-50 dark:bg-slate-700/50">
                                        <span className="text-sm font-medium text-slate-700 dark:text-slate-300">Territory Coverage</span>
                                        <Badge className="bg-purple-100 text-purple-800 border-0">94%</Badge>
                                    </div>
                                </div>

                                <div className="pt-4 border-t border-slate-200 dark:border-slate-700">
                                    <h4 className="font-medium mb-3 text-slate-900 dark:text-white">Trends</h4>
                                    <div className="space-y-3 text-sm">
                                        <div className="flex items-center justify-between p-2 rounded-lg bg-green-50 dark:bg-green-900/20">
                                            <span className="text-slate-700 dark:text-slate-300">Interaction Growth</span>
                                            <div className="flex items-center text-green-600 dark:text-green-400">
                                                <TrendingUp className="mr-1 h-3 w-3" />
                                                +12.5%
                                            </div>
                                        </div>
                                        <div className="flex items-center justify-between p-2 rounded-lg bg-green-50 dark:bg-green-900/20">
                                            <span className="text-slate-700 dark:text-slate-300">Meeting Growth</span>
                                            <div className="flex items-center text-green-600 dark:text-green-400">
                                                <TrendingUp className="mr-1 h-3 w-3" />
                                                +8.7%
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
