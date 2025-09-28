import { Head } from '@inertiajs/react';
import { useState, useEffect } from 'react';
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
import { Progress } from '@/components/ui/progress';
import { Alert, AlertDescription } from '@/components/ui/alert';
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
    TrendingUp,
    TrendingDown,
    RefreshCw,
    Loader2,
    Clock,
    Target,
    Award,
    Heart,
    Zap,
    Eye,
    Download,
    Filter
} from 'lucide-react';

interface TopPerformer {
    name: string;
    specialty: string;
    patients: number;
    rating: number;
    revenue: string;
    appointments_count?: number;
    completion_rate?: number;
    average_consultation_time?: number;
    revenue_growth?: number;
    appointments_growth?: number;
}

interface AnalyticsData {
    overview?: {
        total_patients?: number;
        new_patients_this_month?: number;
        new_patients_last_month?: number;
        new_patients_this_week?: number;
        patients_growth?: number;
        total_appointments?: number;
        appointments_this_month?: number;
        appointments_last_month?: number;
        appointments_this_week?: number;
        appointments_growth?: number;
        total_doctors?: number;
        active_doctors?: number;
        revenue_this_month?: number;
        revenue_last_month?: number;
        revenue_this_week?: number;
        revenue_growth?: number;
        average_wait_time?: number;
        no_show_rate?: number;
    };
    doctor_performance?: Array<{
        id: number;
        name: string;
        specialization: string;
        appointments_count: number;
        completed_appointments?: number;
        cancelled_appointments?: number;
        no_show_appointments?: number;
        completion_rate?: number;
        average_consultation_time?: number;
        total_revenue?: number;
    }>;
    appointment_statuses?: Record<string, number>;
    appointment_types?: Record<string, number>;
    daily_trends?: Array<{
        date: string;
        count: number;
    }>;
    weekly_trends?: Array<{
        week: string;
        count: number;
    }>;
    patient_demographics?: Record<string, number>;
    age_distribution?: Record<string, number>;
    peak_hours?: Array<{
        hour: number;
        count: number;
    }>;
}

interface AnalyticsProps {
    topPerformers: TopPerformer[];
    analytics: AnalyticsData;
}

// Mock data for development/testing
const mockTopPerformers: TopPerformer[] = [
    {
        name: 'Dr. Sarah Johnson',
        specialty: 'Cardiology',
        patients: 45,
        rating: 4.8,
        revenue: '$6,750'
    },
    {
        name: 'Dr. Michael Chen',
        specialty: 'Orthopedics',
        patients: 38,
        rating: 4.7,
        revenue: '$5,700'
    },
    {
        name: 'Dr. Emily Rodriguez',
        specialty: 'Pediatrics',
        patients: 42,
        rating: 4.9,
        revenue: '$6,300'
    },
    {
        name: 'Dr. David Thompson',
        specialty: 'Dermatology',
        patients: 35,
        rating: 4.6,
        revenue: '$5,250'
    },
    {
        name: 'Dr. Lisa Wang',
        specialty: 'Neurology',
        patients: 28,
        rating: 4.8,
        revenue: '$4,200'
    }
];

const mockAnalytics: AnalyticsData = {
    overview: {
        total_patients: 1247,
        new_patients_this_month: 89,
        new_patients_last_month: 76,
        patients_growth: 17.1,
        total_appointments: 3421,
        appointments_this_month: 287,
        appointments_last_month: 245,
        appointments_growth: 17.1,
        total_doctors: 12,
        active_doctors: 10,
        revenue_this_month: 45680,
        revenue_last_month: 38950,
        revenue_growth: 17.3,
    },
    doctor_performance: [
        {
            id: 1,
            name: 'Dr. Sarah Johnson',
            specialization: 'Cardiology',
            appointments_count: 45,
            completed_appointments: 42,
            cancelled_appointments: 2,
            no_show_appointments: 1,
            completion_rate: 93.3,
            average_consultation_time: 25.5,
        },
        {
            id: 2,
            name: 'Dr. Michael Chen',
            specialization: 'Orthopedics',
            appointments_count: 38,
            completed_appointments: 35,
            cancelled_appointments: 2,
            no_show_appointments: 1,
            completion_rate: 92.1,
            average_consultation_time: 22.8,
        },
        {
            id: 3,
            name: 'Dr. Emily Rodriguez',
            specialization: 'Pediatrics',
            appointments_count: 42,
            completed_appointments: 40,
            cancelled_appointments: 1,
            no_show_appointments: 1,
            completion_rate: 95.2,
            average_consultation_time: 18.3,
        },
        {
            id: 4,
            name: 'Dr. David Thompson',
            specialization: 'Dermatology',
            appointments_count: 35,
            completed_appointments: 32,
            cancelled_appointments: 2,
            no_show_appointments: 1,
            completion_rate: 91.4,
            average_consultation_time: 20.1,
        },
        {
            id: 5,
            name: 'Dr. Lisa Wang',
            specialization: 'Neurology',
            appointments_count: 28,
            completed_appointments: 26,
            cancelled_appointments: 1,
            no_show_appointments: 1,
            completion_rate: 92.9,
            average_consultation_time: 35.2,
        },
    ],
    appointment_statuses: {
        scheduled: 156,
        completed: 287,
        cancelled: 23,
        no_show: 12,
        rescheduled: 8,
    },
    daily_trends: [
        { date: '2024-01-01', count: 12 },
        { date: '2024-01-02', count: 15 },
        { date: '2024-01-03', count: 18 },
        { date: '2024-01-04', count: 14 },
        { date: '2024-01-05', count: 16 },
        { date: '2024-01-06', count: 8 },
        { date: '2024-01-07', count: 6 },
        { date: '2024-01-08', count: 19 },
        { date: '2024-01-09', count: 22 },
        { date: '2024-01-10', count: 17 },
    ],
};

export default function Analytics({ topPerformers = [], analytics = {} }: AnalyticsProps) {
    // Use real data if available, otherwise use empty defaults
    const [analyticsData, setAnalyticsData] = useState<AnalyticsData>(analytics && Object.keys(analytics).length > 0 ? analytics : {});
    const [performers, setPerformers] = useState<TopPerformer[]>(topPerformers.length > 0 ? topPerformers : []);
    const [loading, setLoading] = useState(false);
    const [refreshing, setRefreshing] = useState(false);
    const [error, setError] = useState<string>('');
    const [lastUpdated, setLastUpdated] = useState<Date>(new Date());

    // Refresh analytics data without page reload
    const refreshAnalytics = async () => {
        setRefreshing(true);
        setError('');
        try {
            const response = await fetch('/admin/reports/analytics', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
            });

            if (response.ok) {
                const data = await response.json();
                if (data.success && data.analytics) {
                    setAnalyticsData(data.analytics);
                    setLastUpdated(new Date());
                } else {
                    setError('No analytics data available');
                }
            } else {
                setError('Failed to fetch analytics data');
            }
        } catch (error) {
            console.error('Error refreshing analytics:', error);
            setError('Failed to refresh analytics data');
        } finally {
            setRefreshing(false);
        }
    };

    // Auto-refresh data every 5 minutes
    useEffect(() => {
        const interval = setInterval(() => {
            refreshAnalytics();
        }, 5 * 60 * 1000); // 5 minutes

        return () => clearInterval(interval);
    }, []);

    // Export analytics report
    const exportReport = () => {
        // This would trigger a report generation
        window.open('/admin/reports?type=analytics', '_blank');
    };

    // Calculate key metrics
    const keyMetrics = [
        {
            title: 'Total Patients',
            value: analyticsData?.overview?.total_patients?.toLocaleString() || '0',
            change: analyticsData?.overview?.patients_growth ? `${analyticsData.overview.patients_growth > 0 ? '+' : ''}${analyticsData.overview.patients_growth}%` : '0%',
            icon: Users,
            color: 'text-blue-600',
            trend: (analyticsData?.overview?.patients_growth ?? 0) >= 0 ? 'up' : 'down',
            subtitle: `${analyticsData?.overview?.new_patients_this_month || 0} new this month`
        },
        {
            title: 'Active Doctors',
            value: analyticsData?.overview?.active_doctors?.toString() || '0',
            change: `${analyticsData?.overview?.total_doctors || 0} total`,
            icon: Stethoscope,
            color: 'text-green-600',
            trend: 'neutral',
            subtitle: 'Currently active'
        },
        {
            title: 'Monthly Revenue',
            value: analyticsData?.overview?.revenue_this_month ? `$${analyticsData.overview.revenue_this_month.toLocaleString()}` : '$0',
            change: analyticsData?.overview?.revenue_growth ? `${analyticsData.overview.revenue_growth > 0 ? '+' : ''}${analyticsData.overview.revenue_growth}%` : '0%',
            icon: DollarSign,
            color: 'text-purple-600',
            trend: (analyticsData?.overview?.revenue_growth ?? 0) >= 0 ? 'up' : 'down',
            subtitle: 'This month'
        },
        {
            title: 'Appointments',
            value: analyticsData?.overview?.appointments_this_month?.toLocaleString() || '0',
            change: analyticsData?.overview?.appointments_growth ? `${analyticsData.overview.appointments_growth > 0 ? '+' : ''}${analyticsData.overview.appointments_growth}%` : '0%',
            icon: Calendar,
            color: 'text-orange-600',
            trend: (analyticsData?.overview?.appointments_growth ?? 0) >= 0 ? 'up' : 'down',
            subtitle: 'This month'
        }
    ];

    // Calculate performance insights from real data
    const performanceInsights = [
        {
            title: 'Average Wait Time',
            value: analyticsData?.overview?.average_wait_time ? `${analyticsData.overview.average_wait_time} min` : 'N/A',
            icon: Clock,
            color: 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400',
            trend: 'neutral'
        },
        {
            title: 'No-Show Rate',
            value: analyticsData?.overview?.no_show_rate ? `${analyticsData.overview.no_show_rate}%` : 'N/A',
            icon: AlertCircle,
            color: analyticsData?.overview?.no_show_rate && analyticsData.overview.no_show_rate > 10 
                ? 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400'
                : 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400',
            trend: analyticsData?.overview?.no_show_rate && analyticsData.overview.no_show_rate > 10 ? 'down' : 'up'
        },
        {
            title: 'Weekly Revenue',
            value: analyticsData?.overview?.revenue_this_week ? `$${analyticsData.overview.revenue_this_week.toLocaleString()}` : '$0',
            icon: DollarSign,
            color: 'bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-400',
            trend: 'neutral'
        },
        {
            title: 'Weekly Appointments',
            value: analyticsData?.overview?.appointments_this_week?.toString() || '0',
            icon: Calendar,
            color: 'bg-orange-100 text-orange-800 dark:bg-orange-900/20 dark:text-orange-400',
            trend: 'neutral'
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
                    {/* Error Alert */}
                    {error && (
                        <Alert className="border-red-200 bg-red-50 dark:border-red-800 dark:bg-red-900/20">
                            <AlertCircle className="h-4 w-4 text-red-600" />
                            <AlertDescription className="text-red-800 dark:text-red-200">
                                {error}
                            </AlertDescription>
                        </Alert>
                    )}

                    {/* Header */}
                    <div className="flex items-center justify-between">
                        <div>
                            <h1 className="text-3xl font-bold text-slate-900 dark:text-white">Analytics Dashboard</h1>
                            <p className="text-slate-600 dark:text-slate-300 mt-1">
                                Comprehensive insights into your clinic's performance
                            </p>
                            {lastUpdated && (
                                <p className="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                    Last updated: {lastUpdated.toLocaleTimeString()}
                                </p>
                            )}
                        </div>
                        <div className="flex space-x-3">
                            <Button
                                variant="outline"
                                className="border-slate-300 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-700"
                                onClick={refreshAnalytics}
                                disabled={refreshing}
                            >
                                {refreshing ? <Loader2 className="mr-2 h-4 w-4 animate-spin" /> : <RefreshCw className="mr-2 h-4 w-4" />}
                                Refresh Data
                            </Button>
                            <Button
                                className="bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white shadow-lg"
                                onClick={exportReport}
                            >
                                <Download className="mr-2 h-4 w-4" />
                                Export Report
                            </Button>
                        </div>
                    </div>

                    {/* Key Metrics */}
                    <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                        {keyMetrics.map((metric, index) => {
                            const IconComponent = metric.icon;
                            return (
                                <Card key={index} className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                                    <CardContent className="p-6">
                                        <div className="flex items-center justify-between">
                                            <div className="space-y-1">
                                                <p className="text-sm font-medium text-slate-600 dark:text-slate-400">{metric.title}</p>
                                                <p className="text-2xl font-bold text-slate-900 dark:text-white">{metric.value}</p>
                                                <p className="text-xs text-slate-500 dark:text-slate-400">{metric.subtitle}</p>
                                            </div>
                                            <div className="flex flex-col items-end space-y-1">
                                                <IconComponent className={`h-8 w-8 ${metric.color}`} />
                                                <div className="flex items-center">
                                                    {metric.trend === 'up' && <TrendingUp className="h-3 w-3 text-green-500 mr-1" />}
                                                    {metric.trend === 'down' && <TrendingDown className="h-3 w-3 text-red-500 mr-1" />}
                                                    <span className={`text-xs font-medium ${metric.trend === 'up' ? 'text-green-600' : metric.trend === 'down' ? 'text-red-600' : 'text-slate-600'}`}>
                                                        {metric.change}
                                                    </span>
                                                </div>
                                            </div>
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
                                <div className="flex items-center justify-between">
                                    <div>
                                        <CardTitle className="text-xl font-semibold text-slate-900 dark:text-white">Top Performing Doctors</CardTitle>
                                        <CardDescription className="text-slate-600 dark:text-slate-300">
                                            Best performing doctors by patient count and ratings
                                        </CardDescription>
                                    </div>
                                    <Badge className="bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400">
                                        <Award className="mr-1 h-3 w-3" />
                                        Top 5
                                    </Badge>
                                </div>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-4">
                                    {loading ? (
                                        <div className="text-center py-8">
                                            <div className="flex items-center justify-center">
                                                <Loader2 className="h-6 w-6 animate-spin text-blue-600" />
                                                <span className="ml-2 text-slate-600 dark:text-slate-300">Loading analytics...</span>
                                            </div>
                                        </div>
                                    ) : performers.length > 0 ? (
                                        performers.map((doctor, index) => (
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
                                        ))
                                    ) : (
                                        <div className="text-center py-8">
                                            <div className="flex flex-col items-center justify-center">
                                                <Users className="h-12 w-12 text-slate-400 mb-4" />
                                                <h3 className="text-lg font-medium text-slate-900 dark:text-white mb-2">No Doctor Data Available</h3>
                                                <p className="text-slate-600 dark:text-slate-300 text-sm">
                                                    No doctors found or no appointment data available for the current period.
                                                </p>
                                            </div>
                                        </div>
                                    )}
                                </div>
                            </CardContent>
                        </Card>

                        {/* Performance Insights */}
                        <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                            <CardHeader>
                                <CardTitle className="text-xl font-semibold text-slate-900 dark:text-white">Performance Insights</CardTitle>
                                <CardDescription className="text-slate-600 dark:text-slate-300">
                                    Key performance indicators
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                {performanceInsights.map((insight, index) => {
                                    const IconComponent = insight.icon;
                                    return (
                                        <div key={index} className="flex items-center justify-between p-3 border border-slate-200 dark:border-slate-700 rounded-lg">
                                            <div className="flex items-center space-x-3">
                                                <IconComponent className="h-4 w-4 text-slate-600 dark:text-slate-400" />
                                                <span className="text-sm font-medium text-slate-700 dark:text-slate-300">{insight.title}</span>
                                            </div>
                                            <Badge className={insight.color}>
                                                {insight.value}
                                            </Badge>
                                        </div>
                                    );
                                })}

                                <div className="pt-4 border-t border-slate-200 dark:border-slate-700">
                                    <h4 className="font-semibold mb-4 text-slate-900 dark:text-white">Growth Trends</h4>
                                    <div className="space-y-3 text-sm">
                                        <div className={`flex items-center justify-between p-2 rounded-lg ${
                                            (analyticsData?.overview?.patients_growth ?? 0) >= 0 
                                                ? 'bg-green-50 dark:bg-green-900/10' 
                                                : 'bg-red-50 dark:bg-red-900/10'
                                        }`}>
                                            <span className="text-slate-700 dark:text-slate-300">Patient Growth</span>
                                            <div className={`flex items-center ${
                                                (analyticsData?.overview?.patients_growth ?? 0) >= 0 
                                                    ? 'text-green-600 dark:text-green-400' 
                                                    : 'text-red-600 dark:text-red-400'
                                            }`}>
                                                {(analyticsData?.overview?.patients_growth ?? 0) >= 0 ? (
                                                    <TrendingUp className="mr-1 h-3 w-3" />
                                                ) : (
                                                    <TrendingDown className="mr-1 h-3 w-3" />
                                                )}
                                                {analyticsData?.overview?.patients_growth ? 
                                                    `${analyticsData.overview.patients_growth > 0 ? '+' : ''}${analyticsData.overview.patients_growth}%` 
                                                    : '0%'
                                                }
                                            </div>
                                        </div>
                                        <div className={`flex items-center justify-between p-2 rounded-lg ${
                                            (analyticsData?.overview?.revenue_growth ?? 0) >= 0 
                                                ? 'bg-green-50 dark:bg-green-900/10' 
                                                : 'bg-red-50 dark:bg-red-900/10'
                                        }`}>
                                            <span className="text-slate-700 dark:text-slate-300">Revenue Growth</span>
                                            <div className={`flex items-center ${
                                                (analyticsData?.overview?.revenue_growth ?? 0) >= 0 
                                                    ? 'text-green-600 dark:text-green-400' 
                                                    : 'text-red-600 dark:text-red-400'
                                            }`}>
                                                {(analyticsData?.overview?.revenue_growth ?? 0) >= 0 ? (
                                                    <TrendingUp className="mr-1 h-3 w-3" />
                                                ) : (
                                                    <TrendingDown className="mr-1 h-3 w-3" />
                                                )}
                                                {analyticsData?.overview?.revenue_growth ? 
                                                    `${analyticsData.overview.revenue_growth > 0 ? '+' : ''}${analyticsData.overview.revenue_growth}%` 
                                                    : '0%'
                                                }
                                            </div>
                                        </div>
                                        <div className={`flex items-center justify-between p-2 rounded-lg ${
                                            (analyticsData?.overview?.appointments_growth ?? 0) >= 0 
                                                ? 'bg-green-50 dark:bg-green-900/10' 
                                                : 'bg-red-50 dark:bg-red-900/10'
                                        }`}>
                                            <span className="text-slate-700 dark:text-slate-300">Appointment Growth</span>
                                            <div className={`flex items-center ${
                                                (analyticsData?.overview?.appointments_growth ?? 0) >= 0 
                                                    ? 'text-green-600 dark:text-green-400' 
                                                    : 'text-red-600 dark:text-red-400'
                                            }`}>
                                                {(analyticsData?.overview?.appointments_growth ?? 0) >= 0 ? (
                                                    <TrendingUp className="mr-1 h-3 w-3" />
                                                ) : (
                                                    <TrendingDown className="mr-1 h-3 w-3" />
                                                )}
                                                {analyticsData?.overview?.appointments_growth ? 
                                                    `${analyticsData.overview.appointments_growth > 0 ? '+' : ''}${analyticsData.overview.appointments_growth}%` 
                                                    : '0%'
                                                }
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    {/* Additional Analytics Sections */}
                    <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                        {/* Doctor Performance Details */}
                        {analyticsData?.doctor_performance && analyticsData.doctor_performance.length > 0 && (
                            <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                                <CardHeader>
                                    <CardTitle className="text-xl font-semibold text-slate-900 dark:text-white">Doctor Performance Details</CardTitle>
                                    <CardDescription className="text-slate-600 dark:text-slate-300">
                                        Detailed performance metrics by doctor
                                    </CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <div className="space-y-4">
                                        {analyticsData.doctor_performance.slice(0, 3).map((doctor, index) => (
                                            <div key={doctor.id} className="p-4 bg-slate-50 dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700">
                                                <div className="flex items-center justify-between mb-3">
                                                    <div>
                                                        <h3 className="font-medium text-slate-900 dark:text-white">{doctor.name}</h3>
                                                        <p className="text-sm text-slate-600 dark:text-slate-400">{doctor.specialization}</p>
                                                    </div>
                                                    <Badge className="bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400">
                                                        {doctor.appointments_count} appointments
                                                    </Badge>
                                                </div>
                                                <div className="grid grid-cols-3 gap-4 text-sm">
                                                    <div>
                                                        <p className="text-slate-500 dark:text-slate-400">Completion Rate</p>
                                                        <div className="flex items-center space-x-2">
                                                            <Progress value={doctor.completion_rate || 0} className="flex-1 h-2" />
                                                            <span className="font-medium text-slate-900 dark:text-white">{doctor.completion_rate}%</span>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <p className="text-slate-500 dark:text-slate-400">Avg. Time</p>
                                                        <p className="font-medium text-slate-900 dark:text-white">{doctor.average_consultation_time} min</p>
                                                    </div>
                                                    <div>
                                                        <p className="text-slate-500 dark:text-slate-400">Completed</p>
                                                        <p className="font-medium text-slate-900 dark:text-white">{doctor.completed_appointments}/{doctor.appointments_count}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                </CardContent>
                            </Card>
                        )}

                        {/* Appointment Status Breakdown */}
                        {analyticsData?.appointment_statuses && (
                            <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                                <CardHeader>
                                    <CardTitle className="text-xl font-semibold text-slate-900 dark:text-white">Appointment Status</CardTitle>
                                    <CardDescription className="text-slate-600 dark:text-slate-300">
                                        Current appointment distribution
                                    </CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <div className="space-y-4">
                                        {Object.entries(analyticsData.appointment_statuses).map(([status, count]: [string, number]) => {
                                            const total = Object.values(analyticsData.appointment_statuses || {}).reduce((sum, c) => sum + c, 0);
                                            const percentage = total > 0 ? (count / total) * 100 : 0;
                                            const statusColors: Record<string, string> = {
                                                booked: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                                                arrived: 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
                                                'in-room': 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200',
                                                completed: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                                'no-show': 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200',
                                                canceled: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                                                cancelled: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200', // Alternative spelling
                                            };
                                            return (
                                                <div key={status} className="flex items-center justify-between p-3 border border-slate-200 dark:border-slate-700 rounded-lg">
                                                    <div className="flex items-center space-x-3">
                                                        <div className={`w-3 h-3 rounded-full ${statusColors[status]?.split(' ')[0] || 'bg-slate-100'}`}></div>
                                                        <span className="text-sm font-medium text-slate-700 dark:text-slate-300 capitalize">{status.replace('_', ' ').replace('-', ' ')}</span>
                                                    </div>
                                                    <div className="flex items-center space-x-2">
                                                        <span className="text-sm font-medium text-slate-900 dark:text-white">{count}</span>
                                                        <Badge variant="outline" className="text-xs">
                                                            {percentage.toFixed(1)}%
                                                        </Badge>
                                                    </div>
                                                </div>
                                            );
                                        })}
                                    </div>
                                </CardContent>
                            </Card>
                        )}

                        {/* Patient Demographics */}
                        {analyticsData?.patient_demographics && (
                            <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                                <CardHeader>
                                    <CardTitle className="text-xl font-semibold text-slate-900 dark:text-white">Patient Demographics</CardTitle>
                                    <CardDescription className="text-slate-600 dark:text-slate-300">
                                        Gender distribution of patients
                                    </CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <div className="space-y-4">
                                        {Object.entries(analyticsData.patient_demographics).map(([gender, count]: [string, number]) => {
                                            const total = Object.values(analyticsData.patient_demographics || {}).reduce((sum, c) => sum + c, 0);
                                            const percentage = total > 0 ? (count / total) * 100 : 0;
                                            return (
                                                <div key={gender} className="flex items-center justify-between p-3 border border-slate-200 dark:border-slate-700 rounded-lg">
                                                    <div className="flex items-center space-x-3">
                                                        <div className={`w-3 h-3 rounded-full ${
                                                            gender.toLowerCase() === 'male' ? 'bg-blue-500' : 
                                                            gender.toLowerCase() === 'female' ? 'bg-pink-500' : 'bg-slate-500'
                                                        }`}></div>
                                                        <span className="text-sm font-medium text-slate-700 dark:text-slate-300 capitalize">{gender}</span>
                                                    </div>
                                                    <div className="flex items-center space-x-2">
                                                        <span className="text-sm font-medium text-slate-900 dark:text-white">{count}</span>
                                                        <Badge variant="outline" className="text-xs">
                                                            {percentage.toFixed(1)}%
                                                        </Badge>
                                                    </div>
                                                </div>
                                            );
                                        })}
                                    </div>
                                </CardContent>
                            </Card>
                        )}

                        {/* Peak Hours */}
                        {analyticsData?.peak_hours && analyticsData.peak_hours.length > 0 && (
                            <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                                <CardHeader>
                                    <CardTitle className="text-xl font-semibold text-slate-900 dark:text-white">Peak Hours</CardTitle>
                                    <CardDescription className="text-slate-600 dark:text-slate-300">
                                        Busiest appointment times
                                    </CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <div className="space-y-4">
                                        {analyticsData.peak_hours.map((hourData, index) => (
                                            <div key={hourData.hour} className="flex items-center justify-between p-3 border border-slate-200 dark:border-slate-700 rounded-lg">
                                                <div className="flex items-center space-x-3">
                                                    <div className="w-8 h-8 bg-gradient-to-r from-blue-500 to-purple-500 text-white rounded-full flex items-center justify-center text-sm font-bold">
                                                        {index + 1}
                                                    </div>
                                                    <span className="text-sm font-medium text-slate-700 dark:text-slate-300">
                                                        {hourData.hour}:00 - {hourData.hour + 1}:00
                                                    </span>
                                                </div>
                                                <div className="flex items-center space-x-2">
                                                    <span className="text-sm font-medium text-slate-900 dark:text-white">{hourData.count}</span>
                                                    <span className="text-xs text-slate-500 dark:text-slate-400">appointments</span>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                </CardContent>
                            </Card>
                        )}

                        {/* Age Distribution */}
                        {analyticsData?.age_distribution && (
                            <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                                <CardHeader>
                                    <CardTitle className="text-xl font-semibold text-slate-900 dark:text-white">Age Distribution</CardTitle>
                                    <CardDescription className="text-slate-600 dark:text-slate-300">
                                        Patient age groups
                                    </CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <div className="space-y-4">
                                        {Object.entries(analyticsData.age_distribution).map(([ageGroup, count]: [string, number]) => {
                                            const total = Object.values(analyticsData.age_distribution || {}).reduce((sum, c) => sum + c, 0);
                                            const percentage = total > 0 ? (count / total) * 100 : 0;
                                            return (
                                                <div key={ageGroup} className="flex items-center justify-between p-3 border border-slate-200 dark:border-slate-700 rounded-lg">
                                                    <div className="flex items-center space-x-3">
                                                        <div className="w-3 h-3 rounded-full bg-gradient-to-r from-green-400 to-blue-500"></div>
                                                        <span className="text-sm font-medium text-slate-700 dark:text-slate-300">{ageGroup} years</span>
                                                    </div>
                                                    <div className="flex items-center space-x-2">
                                                        <span className="text-sm font-medium text-slate-900 dark:text-white">{count}</span>
                                                        <Badge variant="outline" className="text-xs">
                                                            {percentage.toFixed(1)}%
                                                        </Badge>
                                                    </div>
                                                </div>
                                            );
                                        })}
                                    </div>
                                </CardContent>
                            </Card>
                        )}
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
