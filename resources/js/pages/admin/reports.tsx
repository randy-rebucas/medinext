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
    TrendingDown
} from 'lucide-react';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';

// Simple toast implementation
const toast = {
    success: (message: string) => console.log('Success:', message),
    error: (message: string) => console.error('Error:', message),
};

interface AnalyticsData {
    overview?: {
        total_patients?: number;
        patients_growth?: number;
        active_doctors?: number;
        total_doctors?: number;
        revenue_this_month?: number;
        revenue_growth?: number;
        appointments_this_month?: number;
        appointments_growth?: number;
    };
    doctor_performance?: Array<{
        id: number;
        name: string;
        specialization: string;
        appointments_count: number;
    }>;
    appointment_statuses?: Record<string, number>;
}

interface ReportData {
    id: number;
    report_type: string;
    original_name: string;
    start_date: string;
    end_date: string;
    generated_at: string;
}

interface ReportsProps {
    analytics: AnalyticsData;
    recentReports: ReportData[];
    permissions: string[];
}

export default function Reports({ analytics: initialAnalytics }: ReportsProps) {
    const [analytics, setAnalytics] = useState(initialAnalytics);
    const [isGenerateModalOpen, setIsGenerateModalOpen] = useState(false);
    const [isViewAnalyticsModalOpen, setIsViewAnalyticsModalOpen] = useState(false);
    const [loading, setLoading] = useState(false);
    const [errors, setErrors] = useState<Record<string, string>>({});
    const [formData, setFormData] = useState({
        report_type: '',
        start_date: '',
        end_date: '',
        format: 'pdf'
    });

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

    // API Functions
    const fetchAnalytics = async () => {
        try {
            setLoading(true);
            const response = await fetch('/admin/reports/analytics');
            const data = await response.json();
            if (data.success) {
                setAnalytics(data.analytics);
            }
        } catch {
            toast.error('Failed to fetch analytics');
        } finally {
            setLoading(false);
        }
    };

    const generateReport = async () => {
        try {
            setLoading(true);
            setErrors({});

            const response = await fetch('/admin/reports/generate', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                body: JSON.stringify(formData),
            });

            const data = await response.json();

            if (data.success) {
                toast.success(data.message);
                setIsGenerateModalOpen(false);
                // Refresh recent reports
                window.location.reload();
            } else {
                if (data.errors) {
                    setErrors(data.errors);
                }
                toast.error(data.message || 'Failed to generate report');
            }
        } catch {
            toast.error('Failed to generate report');
        } finally {
            setLoading(false);
        }
    };


    const handleGenerateReport = () => {
        setIsGenerateModalOpen(true);
        setFormData({
            report_type: '',
            start_date: '',
            end_date: '',
            format: 'pdf'
        });
        setErrors({});
    };

    const handleViewAnalytics = () => {
        setIsViewAnalyticsModalOpen(true);
        fetchAnalytics();
    };

    const handleCancel = () => {
        setIsGenerateModalOpen(false);
        setIsViewAnalyticsModalOpen(false);
        setErrors({});
    };

    const quickStats = [
        {
            title: 'Total Patients',
            value: analytics?.overview?.total_patients?.toLocaleString() || '0',
            change: analytics?.overview?.patients_growth ? `+${analytics.overview.patients_growth}%` : '0%',
            icon: Users,
            color: 'text-blue-600',
            trend: (analytics?.overview?.patients_growth ?? 0) >= 0 ? 'up' : 'down'
        },
        {
            title: 'Active Doctors',
            value: analytics?.overview?.active_doctors?.toString() || '0',
            change: `${analytics?.overview?.total_doctors || 0} total`,
            icon: Stethoscope,
            color: 'text-green-600',
            trend: 'neutral'
        },
        {
            title: 'Monthly Revenue',
            value: analytics?.overview?.revenue_this_month ? `$${analytics.overview.revenue_this_month.toLocaleString()}` : '$0',
            change: analytics?.overview?.revenue_growth ? `+${analytics.overview.revenue_growth}%` : '0%',
            icon: DollarSign,
            color: 'text-purple-600',
            trend: (analytics?.overview?.revenue_growth ?? 0) >= 0 ? 'up' : 'down'
        },
        {
            title: 'Appointments',
            value: analytics?.overview?.appointments_this_month?.toLocaleString() || '0',
            change: analytics?.overview?.appointments_growth ? `+${analytics.overview.appointments_growth}%` : '0%',
            icon: Calendar,
            color: 'text-orange-600',
            trend: (analytics?.overview?.appointments_growth ?? 0) >= 0 ? 'up' : 'down'
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

                        {/* Quick Actions */}
                        <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                            <CardHeader>
                                <CardTitle className="text-xl font-semibold text-slate-900 dark:text-white">Quick Actions</CardTitle>
                                <CardDescription className="text-slate-600 dark:text-slate-300">
                                    Common report tasks
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-3">
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
                            </CardContent>
                        </Card>
                    </div>
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
                    <div className="space-y-4">
                        <div className="space-y-2">
                            <Label htmlFor="report_type">Report Type *</Label>
                            <Select
                                value={formData.report_type}
                                onValueChange={(value) => setFormData(prev => ({ ...prev, report_type: value }))}
                            >
                                <SelectTrigger className={errors.report_type ? 'border-red-500' : ''}>
                                    <SelectValue placeholder="Select report type" />
                                </SelectTrigger>
                                <SelectContent>
                                    {reportTypes.map((report) => (
                                        <SelectItem key={report.id} value={report.id}>
                                            {report.title}
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
                                    onChange={(e) => setFormData(prev => ({ ...prev, start_date: e.target.value }))}
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
                                    onChange={(e) => setFormData(prev => ({ ...prev, end_date: e.target.value }))}
                                    className={errors.end_date ? 'border-red-500' : ''}
                                />
                                {errors.end_date && <p className="text-sm text-red-500">{errors.end_date}</p>}
                            </div>
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="format">Format *</Label>
                            <Select
                                value={formData.format}
                                onValueChange={(value) => setFormData(prev => ({ ...prev, format: value }))}
                            >
                                <SelectTrigger className={errors.format ? 'border-red-500' : ''}>
                                    <SelectValue placeholder="Select format" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="pdf">PDF</SelectItem>
                                    <SelectItem value="excel">Excel</SelectItem>
                                    <SelectItem value="csv">CSV</SelectItem>
                                </SelectContent>
                            </Select>
                            {errors.format && <p className="text-sm text-red-500">{errors.format}</p>}
                        </div>
                    </div>
                    <DialogFooter>
                        <Button variant="outline" onClick={handleCancel}>Cancel</Button>
                        <Button onClick={generateReport} disabled={loading}>
                            {loading && <Loader2 className="mr-2 h-4 w-4 animate-spin" />}
                            Generate Report
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
                                    <h3 className="text-lg font-semibold">Top Performing Doctors</h3>
                                    <div className="space-y-2">
                                        {analytics.doctor_performance.slice(0, 5).map((doctor, index: number) => (
                                            <div key={doctor.id} className="flex items-center justify-between p-3 bg-slate-50 dark:bg-slate-800 rounded-lg">
                                                <div className="flex items-center space-x-3">
                                                    <div className="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                                                        <span className="text-sm font-medium text-blue-600 dark:text-blue-400">{index + 1}</span>
                                                    </div>
                                                    <div>
                                                        <p className="font-medium text-slate-900 dark:text-white">{doctor.name}</p>
                                                        <p className="text-sm text-slate-600 dark:text-slate-400">{doctor.specialization}</p>
                                                    </div>
                                                </div>
                                                <div className="text-right">
                                                    <p className="font-medium text-slate-900 dark:text-white">{doctor.appointments_count} appointments</p>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                </div>
                            )}

                            {/* Appointment Status Breakdown */}
                            {analytics.appointment_statuses && (
                                <div className="space-y-4">
                                    <h3 className="text-lg font-semibold">Appointment Status Breakdown</h3>
                                    <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                                        {Object.entries(analytics.appointment_statuses).map(([status, count]: [string, number]) => (
                                            <div key={status} className="p-4 bg-slate-50 dark:bg-slate-800 rounded-lg text-center">
                                                <p className="text-2xl font-bold text-slate-900 dark:text-white">{count}</p>
                                                <p className="text-sm text-slate-600 dark:text-slate-400 capitalize">{status.replace('_', ' ')}</p>
                                            </div>
                                        ))}
                                    </div>
                                </div>
                            )}
                        </div>
                    )}
                    <DialogFooter>
                        <Button variant="outline" onClick={handleCancel}>Close</Button>
                        <Button onClick={fetchAnalytics} disabled={loading}>
                            {loading && <Loader2 className="mr-2 h-4 w-4 animate-spin" />}
                            <RefreshCw className="mr-2 h-4 w-4" />
                            Refresh
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </AppLayout>
    );
}
