import { Head, router } from '@inertiajs/react';
import { useState, useEffect } from 'react';
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
import {
    FileText,
    BarChart3,
    Users,
    Stethoscope,
    TrendingUp,
    Calendar,
    Plus,
    Loader2,
    Eye,
    RefreshCw,
    DollarSign,
    TrendingDown,
    Download,
    AlertCircle,
    CheckCircle,
    Clock,
    Activity
} from 'lucide-react';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { Progress } from '@/components/ui/progress';

// Enhanced toast implementation
const toast = {
    success: (message: string) => {
        // Create a temporary toast element
        const toastEl = document.createElement('div');
        toastEl.className = 'fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg z-50';
        toastEl.textContent = message;
        document.body.appendChild(toastEl);
        setTimeout(() => {
            document.body.removeChild(toastEl);
        }, 3000);
    },
    error: (message: string) => {
        const toastEl = document.createElement('div');
        toastEl.className = 'fixed top-4 right-4 bg-red-500 text-white px-4 py-2 rounded-lg shadow-lg z-50';
        toastEl.textContent = message;
        document.body.appendChild(toastEl);
        setTimeout(() => {
            document.body.removeChild(toastEl);
        }, 3000);
    },
};

interface AnalyticsData {
    overview?: {
        total_patients?: number;
        new_patients_this_month?: number;
        new_patients_last_month?: number;
        patients_growth?: number;
        total_appointments?: number;
        appointments_this_month?: number;
        appointments_last_month?: number;
        appointments_growth?: number;
        total_doctors?: number;
        active_doctors?: number;
        revenue_this_month?: number;
        revenue_last_month?: number;
        revenue_growth?: number;
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
    }>;
    appointment_statuses?: Record<string, number>;
    daily_trends?: Array<{
        date: string;
        count: number;
    }>;
}

interface ReportData {
    id: number;
    report_type: string;
    original_name: string;
    start_date: string;
    end_date: string;
    generated_at: string;
    file_name?: string;
}

interface ReportsProps {
    analytics: AnalyticsData;
    recentReports: ReportData[];
    permissions: string[];
}

// Mock data for development/testing
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

const mockRecentReports: ReportData[] = [
    {
        id: 1,
        report_type: 'monthly_patient',
        original_name: 'Monthly Patient Report_2024-01-01_to_2024-01-31.pdf',
        start_date: '2024-01-01',
        end_date: '2024-01-31',
        generated_at: '2024-01-31 14:30:00',
    },
    {
        id: 2,
        report_type: 'doctor_performance',
        original_name: 'Doctor Performance Report_2024-01-01_to_2024-01-31.pdf',
        start_date: '2024-01-01',
        end_date: '2024-01-31',
        generated_at: '2024-01-30 10:15:00',
    },
    {
        id: 3,
        report_type: 'revenue',
        original_name: 'Revenue Report_2024-01-01_to_2024-01-31.pdf',
        start_date: '2024-01-01',
        end_date: '2024-01-31',
        generated_at: '2024-01-29 16:45:00',
    },
];

export default function Reports({ analytics: initialAnalytics, recentReports: initialRecentReports = [], permissions = [] }: ReportsProps) {
    // Use mock data if no real data is provided (for development)
    const [analytics] = useState(initialAnalytics && Object.keys(initialAnalytics).length > 0 ? initialAnalytics : mockAnalytics);
    const [recentReports] = useState(initialRecentReports.length > 0 ? initialRecentReports : mockRecentReports);
    const [isGenerateModalOpen, setIsGenerateModalOpen] = useState(false);
    const [isViewAnalyticsModalOpen, setIsViewAnalyticsModalOpen] = useState(false);
    const [loading, setLoading] = useState(false);
    const [refreshing, setRefreshing] = useState(false);
    const [errors, setErrors] = useState<Record<string, string>>({});
    const [success, setSuccess] = useState<string>('');
    const [formData, setFormData] = useState({
        report_type: '',
        start_date: '',
        end_date: '',
        format: 'pdf'
    });

    // Set default date range (last 30 days)
    useEffect(() => {
        const today = new Date();
        const thirtyDaysAgo = new Date(today.getTime() - 30 * 24 * 60 * 60 * 1000);
        
        setFormData(prev => ({
            ...prev,
            start_date: thirtyDaysAgo.toISOString().split('T')[0],
            end_date: today.toISOString().split('T')[0]
        }));
    }, []);

    const reportTypes = [
        {
            id: 'monthly_patient',
            title: 'Monthly Patient Report',
            description: 'Patient statistics and demographics for the selected period',
            type: 'Patient',
            icon: Users
        },
        {
            id: 'doctor_performance',
            title: 'Doctor Performance Report',
            description: 'Appointment statistics and performance metrics by doctor',
            type: 'Doctor',
            icon: Stethoscope
        },
        {
            id: 'revenue',
            title: 'Revenue Report',
            description: 'Financial summary and billing statistics',
            type: 'Financial',
            icon: DollarSign
        },
        {
            id: 'appointment_analytics',
            title: 'Appointment Analytics',
            description: 'Appointment trends and scheduling patterns',
            type: 'Analytics',
            icon: BarChart3
        }
    ];

    // Enhanced report generation with better error handling
    const generateReport = async () => {
        setLoading(true);
        setErrors({});
        setSuccess('');

        // Validate form data
        const validationErrors: Record<string, string> = {};
        if (!formData.report_type) validationErrors.report_type = 'Report type is required';
        if (!formData.start_date) validationErrors.start_date = 'Start date is required';
        if (!formData.end_date) validationErrors.end_date = 'End date is required';
        if (!formData.format) validationErrors.format = 'Format is required';

        if (Object.keys(validationErrors).length > 0) {
            setErrors(validationErrors);
            setLoading(false);
            return;
        }

        // Validate date range
        if (new Date(formData.start_date) > new Date(formData.end_date)) {
            setErrors({ date_range: 'Start date must be before end date' });
            setLoading(false);
            return;
        }

        try {
            const response = await fetch('/admin/reports/generate', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify(formData),
            });

            if (response.ok) {
                const data = await response.json();
                if (data.success) {
                    toast.success('Report generated successfully!');
                    setSuccess('Report generated successfully!');
                    setIsGenerateModalOpen(false);
                    // Refresh the page to show new report
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    setErrors(data.errors || { general: 'Failed to generate report' });
                    toast.error('Failed to generate report');
                }
            } else {
                const errorData = await response.json();
                setErrors(errorData.errors || { general: 'Server error occurred' });
                toast.error('Server error occurred');
            }
        } catch (error) {
            console.error('Error generating report:', error);
            setErrors({ general: 'Network error occurred' });
            toast.error('Network error occurred');
        } finally {
            setLoading(false);
        }
    };

    // Refresh analytics data
    const refreshAnalytics = async () => {
        setRefreshing(true);
        try {
            const response = await fetch('/admin/reports/analytics', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (response.ok) {
                const data = await response.json();
                if (data.success) {
                    // Update analytics data
                    window.location.reload();
                }
            }
        } catch (error) {
            console.error('Error refreshing analytics:', error);
            toast.error('Failed to refresh analytics');
        } finally {
            setRefreshing(false);
        }
    };


    const handleGenerateReport = () => {
        setIsGenerateModalOpen(true);
        setErrors({});
        setSuccess('');
    };

    const handleViewAnalytics = () => {
        setIsViewAnalyticsModalOpen(true);
    };

    const handleCancel = () => {
        setIsGenerateModalOpen(false);
        setIsViewAnalyticsModalOpen(false);
        setErrors({});
        setSuccess('');
    };

    // Download report function
    const downloadReport = (reportId: number) => {
        window.open(`/admin/reports/download/${reportId}`, '_blank');
    };

    // Format date for display
    const formatDate = (dateString: string) => {
        return new Date(dateString).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    };

    // Get report type display name
    const getReportTypeDisplayName = (reportType: string) => {
        const typeMap: Record<string, string> = {
            'monthly_patient': 'Monthly Patient Report',
            'doctor_performance': 'Doctor Performance Report',
            'revenue': 'Revenue Report',
            'appointment_analytics': 'Appointment Analytics Report'
        };
        return typeMap[reportType] || reportType;
    };

    const quickStats = [
        {
            title: 'Total Patients',
            value: analytics?.overview?.total_patients?.toLocaleString() || '0',
            change: analytics?.overview?.patients_growth ? `${analytics.overview.patients_growth > 0 ? '+' : ''}${analytics.overview.patients_growth}%` : '0%',
            icon: Users,
            color: 'text-blue-600',
            trend: (analytics?.overview?.patients_growth ?? 0) >= 0 ? 'up' : 'down',
            subtitle: `${analytics?.overview?.new_patients_this_month || 0} new this month`
        },
        {
            title: 'Active Doctors',
            value: analytics?.overview?.active_doctors?.toString() || '0',
            change: `${analytics?.overview?.total_doctors || 0} total`,
            icon: Stethoscope,
            color: 'text-green-600',
            trend: 'neutral',
            subtitle: 'Currently active'
        },
        {
            title: 'Monthly Revenue',
            value: analytics?.overview?.revenue_this_month ? `$${analytics.overview.revenue_this_month.toLocaleString()}` : '$0',
            change: analytics?.overview?.revenue_growth ? `${analytics.overview.revenue_growth > 0 ? '+' : ''}${analytics.overview.revenue_growth}%` : '0%',
            icon: DollarSign,
            color: 'text-purple-600',
            trend: (analytics?.overview?.revenue_growth ?? 0) >= 0 ? 'up' : 'down',
            subtitle: 'This month'
        },
        {
            title: 'Appointments',
            value: analytics?.overview?.appointments_this_month?.toLocaleString() || '0',
            change: analytics?.overview?.appointments_growth ? `${analytics.overview.appointments_growth > 0 ? '+' : ''}${analytics.overview.appointments_growth}%` : '0%',
            icon: Calendar,
            color: 'text-orange-600',
            trend: (analytics?.overview?.appointments_growth ?? 0) >= 0 ? 'up' : 'down',
            subtitle: 'This month'
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
                    {/* Success/Error Messages */}
                    {success && (
                        <Alert className="border-green-200 bg-green-50 dark:border-green-800 dark:bg-green-900/20">
                            <CheckCircle className="h-4 w-4 text-green-600" />
                            <AlertDescription className="text-green-800 dark:text-green-200">
                                {success}
                            </AlertDescription>
                        </Alert>
                    )}

                    {errors.general && (
                        <Alert className="border-red-200 bg-red-50 dark:border-red-800 dark:bg-red-900/20">
                            <AlertCircle className="h-4 w-4 text-red-600" />
                            <AlertDescription className="text-red-800 dark:text-red-200">
                                {errors.general}
                            </AlertDescription>
                        </Alert>
                    )}

                    {/* Overview Stats */}
                    <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                        {quickStats.map((stat, index) => {
                            const IconComponent = stat.icon;
                            return (
                                <Card key={index} className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                                    <CardContent className="p-6">
                                        <div className="flex items-center justify-between">
                                            <div className="space-y-1">
                                                <p className="text-sm font-medium text-slate-600 dark:text-slate-400">{stat.title}</p>
                                                <p className="text-2xl font-bold text-slate-900 dark:text-white">{stat.value}</p>
                                                <p className="text-xs text-slate-500 dark:text-slate-400">{stat.subtitle}</p>
                                            </div>
                                            <div className="flex flex-col items-end space-y-1">
                                                <IconComponent className={`h-8 w-8 ${stat.color}`} />
                                                <div className="flex items-center">
                                                    {stat.trend === 'up' && <TrendingUp className="h-3 w-3 text-green-500 mr-1" />}
                                                    {stat.trend === 'down' && <TrendingDown className="h-3 w-3 text-red-500 mr-1" />}
                                                    <span className={`text-xs font-medium ${stat.trend === 'up' ? 'text-green-600' : stat.trend === 'down' ? 'text-red-600' : 'text-slate-600'}`}>
                                                        {stat.change}
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
                                            onClick={handleViewAnalytics}
                                        >
                                            <Eye className="mr-2 h-4 w-4" />
                                            View Analytics
                                        </Button>
                                        <Button
                                            className="bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white shadow-lg"
                                            onClick={handleGenerateReport}
                                        >
                                            <Plus className="mr-2 h-4 w-4" />
                                            Generate New Report
                                        </Button>
                                    </div>
                                </div>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-4">
                                    {reportTypes.map((report) => {
                                        const IconComponent = report.icon;
                                        return (
                                        <div key={report.id} className="flex items-center justify-between p-4 border border-slate-200 dark:border-slate-700 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors duration-200">
                                            <div className="flex items-center space-x-4">
                                                <div className="flex-shrink-0 p-2 bg-slate-100 dark:bg-slate-700 rounded-lg">
                                                    <IconComponent className="h-5 w-5 text-blue-600" />
                                                </div>
                                                <div>
                                                    <h3 className="font-semibold text-slate-900 dark:text-white">{report.title}</h3>
                                                    <p className="text-sm text-slate-600 dark:text-slate-300">{report.description}</p>
                                                    <p className="text-xs text-slate-500 dark:text-slate-400">
                                                        Type: {report.type}
                                                    </p>
                                                </div>
                                            </div>
                                            <div className="flex items-center space-x-2">
                                                <Button
                                                    variant="outline"
                                                    size="sm"
                                                    onClick={() => {
                                                        setFormData(prev => ({ ...prev, report_type: report.id }));
                                                        setIsGenerateModalOpen(true);
                                                    }}
                                                >
                                                    <FileText className="h-4 w-4 mr-1" />
                                                    Generate
                                                </Button>
                                            </div>
                                        </div>
                                        );
                                    })}
                                </div>
                            </CardContent>
                        </Card>

                        {/* Recent Reports */}
                        <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                            <CardHeader>
                                <div className="flex items-center justify-between">
                                    <div>
                                        <CardTitle className="text-xl font-semibold text-slate-900 dark:text-white">Recent Reports</CardTitle>
                                        <CardDescription className="text-slate-600 dark:text-slate-300">
                                            Recently generated reports
                                        </CardDescription>
                                    </div>
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        onClick={refreshAnalytics}
                                        disabled={refreshing}
                                    >
                                        {refreshing ? <Loader2 className="h-4 w-4 animate-spin" /> : <RefreshCw className="h-4 w-4" />}
                                    </Button>
                                </div>
                            </CardHeader>
                            <CardContent>
                                {recentReports.length > 0 ? (
                                    <div className="space-y-3">
                                        {recentReports.slice(0, 5).map((report) => (
                                            <div key={report.id} className="flex items-center justify-between p-3 border border-slate-200 dark:border-slate-700 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                                                <div className="flex-1 min-w-0">
                                                    <div className="flex items-center space-x-2">
                                                        <FileText className="h-4 w-4 text-blue-600 flex-shrink-0" />
                                                        <div className="min-w-0 flex-1">
                                                            <p className="text-sm font-medium text-slate-900 dark:text-white truncate">
                                                                {getReportTypeDisplayName(report.report_type)}
                                                            </p>
                                                            <p className="text-xs text-slate-500 dark:text-slate-400">
                                                                {formatDate(report.generated_at)}
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <div className="mt-1 flex items-center space-x-2">
                                                        <Badge variant="outline" className="text-xs">
                                                            {report.start_date} - {report.end_date}
                                                        </Badge>
                                                    </div>
                                                </div>
                                                <Button
                                                    variant="outline"
                                                    size="sm"
                                                    onClick={() => downloadReport(report.id)}
                                                    className="ml-2"
                                                >
                                                    <Download className="h-3 w-3" />
                                                </Button>
                                            </div>
                                        ))}
                                    </div>
                                ) : (
                                    <div className="text-center py-8">
                                        <FileText className="h-12 w-12 text-slate-400 mx-auto mb-4" />
                                        <p className="text-slate-500 dark:text-slate-400">No reports generated yet</p>
                                        <p className="text-sm text-slate-400 dark:text-slate-500">Generate your first report to get started</p>
                                    </div>
                                )}
                            </CardContent>
                        </Card>
                    </div>

                    {/* Quick Actions */}
                    <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                        <CardHeader>
                            <CardTitle className="text-xl font-semibold text-slate-900 dark:text-white">Quick Actions</CardTitle>
                            <CardDescription className="text-slate-600 dark:text-slate-300">
                                Common report tasks and shortcuts
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="grid gap-3 md:grid-cols-2 lg:grid-cols-3">
                                <Button
                                    className="w-full justify-start h-12 border-slate-300 dark:border-slate-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:text-blue-600 dark:hover:text-blue-400"
                                    variant="outline"
                                    onClick={() => {
                                        setFormData(prev => ({ ...prev, report_type: 'monthly_patient' }));
                                        setIsGenerateModalOpen(true);
                                    }}
                                >
                                    <Users className="mr-3 h-4 w-4" />
                                    Patient Demographics
                                </Button>
                                <Button
                                    className="w-full justify-start h-12 border-slate-300 dark:border-slate-600 hover:bg-green-50 dark:hover:bg-green-900/20 hover:text-green-600 dark:hover:text-green-400"
                                    variant="outline"
                                    onClick={() => {
                                        setFormData(prev => ({ ...prev, report_type: 'doctor_performance' }));
                                        setIsGenerateModalOpen(true);
                                    }}
                                >
                                    <Stethoscope className="mr-3 h-4 w-4" />
                                    Doctor Performance
                                </Button>
                                <Button
                                    className="w-full justify-start h-12 border-slate-300 dark:border-slate-600 hover:bg-purple-50 dark:hover:bg-purple-900/20 hover:text-purple-600 dark:hover:text-purple-400"
                                    variant="outline"
                                    onClick={() => {
                                        setFormData(prev => ({ ...prev, report_type: 'appointment_analytics' }));
                                        setIsGenerateModalOpen(true);
                                    }}
                                >
                                    <Calendar className="mr-3 h-4 w-4" />
                                    Appointment Analytics
                                </Button>
                                <Button
                                    className="w-full justify-start h-12 border-slate-300 dark:border-slate-600 hover:bg-orange-50 dark:hover:bg-orange-900/20 hover:text-orange-600 dark:hover:text-orange-400"
                                    variant="outline"
                                    onClick={() => {
                                        setFormData(prev => ({ ...prev, report_type: 'revenue' }));
                                        setIsGenerateModalOpen(true);
                                    }}
                                >
                                    <DollarSign className="mr-3 h-4 w-4" />
                                    Revenue Report
                                </Button>
                                <Button
                                    className="w-full justify-start h-12 border-slate-300 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-700"
                                    variant="outline"
                                    onClick={handleViewAnalytics}
                                >
                                    <BarChart3 className="mr-3 h-4 w-4" />
                                    Analytics Dashboard
                                </Button>
                                <Button
                                    className="w-full justify-start h-12 border-slate-300 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-700"
                                    variant="outline"
                                    onClick={handleGenerateReport}
                                >
                                    <Plus className="mr-3 h-4 w-4" />
                                    Custom Report
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>

            {/* Generate Report Modal */}
            <Dialog open={isGenerateModalOpen} onOpenChange={setIsGenerateModalOpen}>
                <DialogContent className="max-w-md">
                    <DialogHeader>
                        <DialogTitle>Generate New Report</DialogTitle>
                        <DialogDescription>
                            Select the report type, date range, and format for your report.
                        </DialogDescription>
                    </DialogHeader>
                    
                    {errors.date_range && (
                        <Alert className="border-red-200 bg-red-50 dark:border-red-800 dark:bg-red-900/20">
                            <AlertCircle className="h-4 w-4 text-red-600" />
                            <AlertDescription className="text-red-800 dark:text-red-200">
                                {errors.date_range}
                            </AlertDescription>
                        </Alert>
                    )}

                    <div className="space-y-4">
                        <div className="space-y-2">
                            <Label htmlFor="report_type">Report Type *</Label>
                            <Select
                                value={formData.report_type}
                                onValueChange={(value) => {
                                    setFormData(prev => ({ ...prev, report_type: value }));
                                    setErrors(prev => ({ ...prev, report_type: '' }));
                                }}
                            >
                                <SelectTrigger className={errors.report_type ? 'border-red-500' : ''}>
                                    <SelectValue placeholder="Select report type" />
                                </SelectTrigger>
                                <SelectContent>
                                    {reportTypes.map((report) => (
                                        <SelectItem key={report.id} value={report.id}>
                                            <div className="flex items-center space-x-2">
                                                <report.icon className="h-4 w-4" />
                                                <span>{report.title}</span>
                                            </div>
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            {errors.report_type && <p className="text-sm text-red-500">{errors.report_type}</p>}
                        </div>

                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="start_date">Start Date *</Label>
                                <Input
                                    id="start_date"
                                    type="date"
                                    value={formData.start_date}
                                    onChange={(e) => {
                                        setFormData(prev => ({ ...prev, start_date: e.target.value }));
                                        setErrors(prev => ({ ...prev, start_date: '', date_range: '' }));
                                    }}
                                    className={errors.start_date ? 'border-red-500' : ''}
                                />
                                {errors.start_date && <p className="text-sm text-red-500">{errors.start_date}</p>}
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="end_date">End Date *</Label>
                                <Input
                                    id="end_date"
                                    type="date"
                                    value={formData.end_date}
                                    onChange={(e) => {
                                        setFormData(prev => ({ ...prev, end_date: e.target.value }));
                                        setErrors(prev => ({ ...prev, end_date: '', date_range: '' }));
                                    }}
                                    className={errors.end_date ? 'border-red-500' : ''}
                                />
                                {errors.end_date && <p className="text-sm text-red-500">{errors.end_date}</p>}
                            </div>
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="format">Format *</Label>
                            <Select
                                value={formData.format}
                                onValueChange={(value) => {
                                    setFormData(prev => ({ ...prev, format: value }));
                                    setErrors(prev => ({ ...prev, format: '' }));
                                }}
                            >
                                <SelectTrigger className={errors.format ? 'border-red-500' : ''}>
                                    <SelectValue placeholder="Select format" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="pdf">
                                        <div className="flex items-center space-x-2">
                                            <FileText className="h-4 w-4" />
                                            <span>PDF Document</span>
                                        </div>
                                    </SelectItem>
                                    <SelectItem value="excel">
                                        <div className="flex items-center space-x-2">
                                            <BarChart3 className="h-4 w-4" />
                                            <span>Excel Spreadsheet</span>
                                        </div>
                                    </SelectItem>
                                    <SelectItem value="csv">
                                        <div className="flex items-center space-x-2">
                                            <FileText className="h-4 w-4" />
                                            <span>CSV File</span>
                                        </div>
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            {errors.format && <p className="text-sm text-red-500">{errors.format}</p>}
                        </div>
                    </div>
                    <DialogFooter>
                        <Button variant="outline" onClick={handleCancel} disabled={loading}>
                            Cancel
                        </Button>
                        <Button onClick={generateReport} disabled={loading}>
                            {loading && <Loader2 className="mr-2 h-4 w-4 animate-spin" />}
                            {loading ? 'Generating...' : 'Generate Report'}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            {/* View Analytics Modal */}
            <Dialog open={isViewAnalyticsModalOpen} onOpenChange={setIsViewAnalyticsModalOpen}>
                <DialogContent className="max-w-6xl max-h-[90vh] overflow-y-auto">
                    <DialogHeader>
                        <DialogTitle>Analytics Dashboard</DialogTitle>
                        <DialogDescription>
                            Comprehensive analytics and insights for your clinic
                        </DialogDescription>
                    </DialogHeader>
                    {analytics && (
                        <div className="space-y-6">
                            {/* Overview Stats */}
                            <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                                {quickStats.map((stat, index) => {
                                    const IconComponent = stat.icon;
                                    return (
                                        <div key={index} className="p-4 bg-slate-50 dark:bg-slate-800 rounded-lg">
                                            <div className="flex items-center justify-between">
                                                <div>
                                                    <p className="text-sm text-slate-600 dark:text-slate-400">{stat.title}</p>
                                                    <p className="text-2xl font-bold text-slate-900 dark:text-white">{stat.value}</p>
                                                    <p className="text-xs text-slate-500 dark:text-slate-400">{stat.subtitle}</p>
                                                </div>
                                                <IconComponent className={`h-8 w-8 ${stat.color}`} />
                                            </div>
                                            <div className="flex items-center mt-2">
                                                {stat.trend === 'up' && <TrendingUp className="h-4 w-4 text-green-500 mr-1" />}
                                                {stat.trend === 'down' && <TrendingDown className="h-4 w-4 text-red-500 mr-1" />}
                                                <span className={`text-sm ${stat.trend === 'up' ? 'text-green-600' : stat.trend === 'down' ? 'text-red-600' : 'text-slate-600'}`}>
                                                    {stat.change}
                                                </span>
                                            </div>
                                        </div>
                                    );
                                })}
                            </div>

                            {/* Doctor Performance */}
                            {analytics.doctor_performance && analytics.doctor_performance.length > 0 && (
                                <div className="space-y-4">
                                    <h3 className="text-lg font-semibold text-slate-900 dark:text-white">Top Performing Doctors</h3>
                                    <div className="space-y-3">
                                        {analytics.doctor_performance.slice(0, 5).map((doctor, index: number) => (
                                            <div key={doctor.id} className="p-4 bg-slate-50 dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700">
                                                <div className="flex items-center justify-between">
                                                    <div className="flex items-center space-x-3">
                                                        <div className="w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                                                            <span className="text-sm font-medium text-blue-600 dark:text-blue-400">{index + 1}</span>
                                                        </div>
                                                        <div>
                                                            <p className="font-medium text-slate-900 dark:text-white">{doctor.name}</p>
                                                            <p className="text-sm text-slate-600 dark:text-slate-400">{doctor.specialization}</p>
                                                        </div>
                                                    </div>
                                                    <div className="text-right space-y-1">
                                                        <p className="font-medium text-slate-900 dark:text-white">{doctor.appointments_count} appointments</p>
                                                        {doctor.completion_rate && (
                                                            <div className="flex items-center space-x-2">
                                                                <Progress value={doctor.completion_rate} className="w-16 h-2" />
                                                                <span className="text-xs text-slate-500">{doctor.completion_rate}%</span>
                                                            </div>
                                                        )}
                                                    </div>
                                                </div>
                                                {doctor.average_consultation_time && (
                                                    <div className="mt-3 flex items-center justify-between text-sm">
                                                        <span className="text-slate-600 dark:text-slate-400">Avg. consultation time:</span>
                                                        <span className="font-medium text-slate-900 dark:text-white">{doctor.average_consultation_time} min</span>
                                                    </div>
                                                )}
                                            </div>
                                        ))}
                                    </div>
                                </div>
                            )}

                            {/* Appointment Status Breakdown */}
                            {analytics.appointment_statuses && (
                                <div className="space-y-4">
                                    <h3 className="text-lg font-semibold text-slate-900 dark:text-white">Appointment Status Breakdown</h3>
                                    <div className="grid grid-cols-2 md:grid-cols-5 gap-4">
                                        {Object.entries(analytics.appointment_statuses).map(([status, count]: [string, number]) => {
                                            const total = Object.values(analytics.appointment_statuses || {}).reduce((sum, c) => sum + c, 0);
                                            const percentage = total > 0 ? (count / total) * 100 : 0;
                                            const statusColors: Record<string, string> = {
                                                scheduled: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                                                completed: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                                cancelled: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                                                no_show: 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200',
                                                rescheduled: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                                            };
                                            return (
                                                <div key={status} className="p-4 bg-slate-50 dark:bg-slate-800 rounded-lg text-center border border-slate-200 dark:border-slate-700">
                                                    <p className="text-2xl font-bold text-slate-900 dark:text-white">{count}</p>
                                                    <p className="text-sm text-slate-600 dark:text-slate-400 capitalize mb-2">{status.replace('_', ' ')}</p>
                                                    <Badge className={statusColors[status] || 'bg-slate-100 text-slate-800'}>
                                                        {percentage.toFixed(1)}%
                                                    </Badge>
                                                </div>
                                            );
                                        })}
                                    </div>
                                </div>
                            )}

                            {/* Daily Trends */}
                            {analytics.daily_trends && analytics.daily_trends.length > 0 && (
                                <div className="space-y-4">
                                    <h3 className="text-lg font-semibold text-slate-900 dark:text-white">Daily Appointment Trends (Last 10 Days)</h3>
                                    <div className="p-4 bg-slate-50 dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700">
                                        <div className="space-y-2">
                                            {analytics.daily_trends.slice(0, 10).map((trend, index) => {
                                                const maxCount = Math.max(...(analytics.daily_trends || []).map(t => t.count));
                                                const percentage = maxCount > 0 ? (trend.count / maxCount) * 100 : 0;
                                                return (
                                                    <div key={index} className="flex items-center space-x-3">
                                                        <div className="w-20 text-sm text-slate-600 dark:text-slate-400">
                                                            {new Date(trend.date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' })}
                                                        </div>
                                                        <div className="flex-1">
                                                            <div className="flex items-center space-x-2">
                                                                <Progress value={percentage} className="flex-1 h-2" />
                                                                <span className="text-sm font-medium text-slate-900 dark:text-white w-8 text-right">
                                                                    {trend.count}
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                );
                                            })}
                                        </div>
                                    </div>
                                </div>
                            )}
                        </div>
                    )}
                    <DialogFooter>
                        <Button variant="outline" onClick={handleCancel}>Close</Button>
                        <Button onClick={refreshAnalytics} disabled={refreshing}>
                            {refreshing && <Loader2 className="mr-2 h-4 w-4 animate-spin" />}
                            <RefreshCw className="mr-2 h-4 w-4" />
                            Refresh Data
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </AppLayout>
    );
}
