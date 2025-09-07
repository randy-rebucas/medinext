import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    TrendingUp,
    TrendingDown,
    Target,
    Calendar,
    MessageSquare,
    Package,
    Award,
    Building2,
    Shield
} from 'lucide-react';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Performance Metrics',
        href: '/medrep/performance',
    },
    {
        title: 'Performance Metrics',
        href: '/medrep/performance',
    },
];

interface PerformanceMetricsProps {
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

export default function PerformanceMetrics({ user }: PerformanceMetricsProps = {}) {
    const metrics = [
        {
            title: 'Total Interactions',
            value: '156',
            change: '+12.5%',
            trend: 'up',
            icon: MessageSquare,
            color: 'text-blue-600'
        },
        {
            title: 'Meeting Success Rate',
            value: '87%',
            change: '+5.2%',
            trend: 'up',
            icon: Calendar,
            color: 'text-green-600'
        },
        {
            title: 'Sample Conversion',
            value: '23%',
            change: '+3.1%',
            trend: 'up',
            icon: Package,
            color: 'text-purple-600'
        },
        {
            title: 'Territory Coverage',
            value: '94%',
            change: '+2.8%',
            trend: 'up',
            icon: Target,
            color: 'text-orange-600'
        }
    ];

    const goals = [
        {
            id: 1,
            title: 'Monthly Interactions',
            target: 200,
            current: 156,
            progress: 78,
            status: 'On Track'
        },
        {
            id: 2,
            title: 'New Doctor Contacts',
            target: 10,
            current: 7,
            progress: 70,
            status: 'On Track'
        },
        {
            id: 3,
            title: 'Sample Deliveries',
            target: 50,
            current: 35,
            progress: 70,
            status: 'On Track'
        },
        {
            id: 4,
            title: 'Meeting Completion',
            target: 40,
            current: 32,
            progress: 80,
            status: 'On Track'
        }
    ];

    const achievements = [
        {
            id: 1,
            title: 'Top Performer',
            description: 'Highest interaction rate this month',
            date: '2024-01-15',
            icon: Award,
            color: 'text-yellow-600'
        },
        {
            id: 2,
            title: 'Territory Master',
            description: '100% territory coverage achieved',
            date: '2024-01-10',
            icon: Target,
            color: 'text-blue-600'
        },
        {
            id: 3,
            title: 'Sample Champion',
            description: 'Delivered 50+ samples this quarter',
            date: '2024-01-05',
            icon: Package,
            color: 'text-green-600'
        }
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Performance Metrics - Medinext">
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
                                    Performance Metrics
                                </h1>
                                <p className="mt-2 text-emerald-100">
                                    Track your performance and achieve your goals
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

                    {/* Action Button */}
                    <div className="flex justify-end">
                        <Button className="bg-emerald-600 hover:bg-emerald-700 text-white">
                            <TrendingUp className="mr-2 h-4 w-4" />
                            View Report
                        </Button>
                    </div>

                    {/* Key Metrics */}
                    <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                        {metrics.map((metric, index) => {
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
                        {/* Goals Progress */}
                        <Card className="col-span-2 border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                            <CardHeader>
                                <CardTitle className="text-lg font-semibold text-slate-900 dark:text-white">Goals Progress</CardTitle>
                                <CardDescription className="text-slate-600 dark:text-slate-300">
                                    Track your progress towards monthly and quarterly goals
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-4">
                                    {goals.map((goal) => (
                                        <div key={goal.id} className="space-y-3 p-4 rounded-lg bg-slate-50 dark:bg-slate-700/50">
                                            <div className="flex items-center justify-between">
                                                <h3 className="font-medium text-slate-900 dark:text-white">{goal.title}</h3>
                                                <Badge className="bg-emerald-100 text-emerald-800 border-0">{goal.status}</Badge>
                                            </div>
                                            <div className="flex items-center space-x-2">
                                                <div className="flex-1 bg-slate-200 dark:bg-slate-700 rounded-full h-2">
                                                    <div
                                                        className="bg-emerald-500 h-2 rounded-full"
                                                        style={{ width: `${goal.progress}%` }}
                                                    ></div>
                                                </div>
                                                <span className="text-sm text-slate-600 dark:text-slate-400">
                                                    {goal.current}/{goal.target}
                                                </span>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </CardContent>
                        </Card>

                        {/* Achievements */}
                        <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                            <CardHeader>
                                <CardTitle className="text-lg font-semibold text-slate-900 dark:text-white">Achievements</CardTitle>
                                <CardDescription className="text-slate-600 dark:text-slate-300">
                                    Your recent accomplishments and milestones
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-4">
                                    {achievements.map((achievement) => (
                                        <div key={achievement.id} className="flex items-center space-x-3 p-3 rounded-lg bg-slate-50 dark:bg-slate-700/50 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                                            <div className="flex-shrink-0">
                                                <achievement.icon className={`h-5 w-5 ${achievement.color}`} />
                                            </div>
                                            <div>
                                                <h3 className="font-medium text-sm text-slate-900 dark:text-white">{achievement.title}</h3>
                                                <p className="text-xs text-slate-600 dark:text-slate-400">{achievement.description}</p>
                                                <p className="text-xs text-slate-500 dark:text-slate-500">{achievement.date}</p>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    {/* Performance Summary */}
                    <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                        <CardHeader>
                            <CardTitle className="text-lg font-semibold text-slate-900 dark:text-white">Performance Summary</CardTitle>
                            <CardDescription className="text-slate-600 dark:text-slate-300">
                                Overall performance metrics and insights
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="grid gap-6 md:grid-cols-3">
                                <div className="text-center p-6 rounded-lg bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20 border-0">
                                    <div className="text-3xl font-bold text-green-600 dark:text-green-400">A+</div>
                                    <div className="text-sm text-slate-600 dark:text-slate-400 mt-1">Overall Rating</div>
                                </div>
                                <div className="text-center p-6 rounded-lg bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 border-0">
                                    <div className="text-3xl font-bold text-blue-600 dark:text-blue-400">85%</div>
                                    <div className="text-sm text-slate-600 dark:text-slate-400 mt-1">Goal Achievement</div>
                                </div>
                                <div className="text-center p-6 rounded-lg bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20 border-0">
                                    <div className="text-3xl font-bold text-purple-600 dark:text-purple-400">Top 10%</div>
                                    <div className="text-sm text-slate-600 dark:text-slate-400 mt-1">Team Ranking</div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AppLayout>
    );
}
