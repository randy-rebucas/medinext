import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    TestTube,
    Download,
    Eye,
    Calendar,
    Stethoscope,
    AlertCircle,
    CheckCircle,
    TrendingUp,
    Shield,
    User
} from 'lucide-react';
import { patientDashboard, patientLabResults } from '@/routes';
import { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Patient Portal',
        href: patientDashboard(),
    },
    {
        title: 'Lab Results',
        href: patientLabResults(),
    },
];

interface PatientLabResultsProps {
    user?: {
        id: number;
        name: string;
        email: string;
        patient_id: string;
        sex: string;
    };
    permissions?: string[];
}

export default function PatientLabResults({ user, permissions }: PatientLabResultsProps) {
    const hasPermission = (permission: string) => {
        return permissions?.includes(permission) ?? true;
    };
    const labResults = [
        {
            id: 1,
            testName: 'Complete Blood Count',
            date: '2024-01-15',
            results: 'Normal',
            status: 'Completed',
            doctor: 'Dr. Michael Brown',
            notes: 'All values within normal range'
        },
        {
            id: 2,
            testName: 'Lipid Panel',
            date: '2024-01-15',
            results: 'Normal',
            status: 'Completed',
            doctor: 'Dr. Michael Brown',
            notes: 'Cholesterol levels are good'
        },
        {
            id: 3,
            testName: 'Blood Glucose',
            date: '2024-01-15',
            results: 'Normal',
            status: 'Completed',
            doctor: 'Dr. Michael Brown',
            notes: 'Blood sugar levels are within normal range'
        },
        {
            id: 4,
            testName: 'Thyroid Function Test',
            date: '2024-01-10',
            results: 'Abnormal',
            status: 'Completed',
            doctor: 'Dr. Sarah Johnson',
            notes: 'TSH levels slightly elevated, follow-up recommended'
        }
    ];

    const getStatusColor = (status: string) => {
        switch (status) {
            case 'Completed': return 'default';
            case 'Pending': return 'secondary';
            case 'Abnormal': return 'destructive';
            default: return 'secondary';
        }
    };

    const getResultsColor = (results: string) => {
        switch (results) {
            case 'Normal': return 'text-green-600';
            case 'Abnormal': return 'text-red-600';
            case 'Pending': return 'text-yellow-600';
            default: return 'text-gray-600';
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Lab Results - Medinext">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&family=instrument-sans:400,500,600" rel="stylesheet" />
            </Head>
            <div className="min-h-screen bg-gradient-to-br from-slate-50 via-white to-blue-50 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900">
                <div className="space-y-6 p-6">
                    {/* Modern Header */}
                    <div className="relative overflow-hidden rounded-2xl bg-gradient-to-r from-rose-600 to-pink-600 p-8 text-white shadow-xl">
                        <div className="absolute inset-0 bg-black/10"></div>
                        <div className="relative flex items-center justify-between">
                            <div>
                                <h1 className="text-3xl font-bold tracking-tight">Lab Results</h1>
                                <p className="mt-2 text-rose-100">
                                    View your laboratory test results and analysis
                                </p>
                            </div>
                            <div className="flex items-center gap-3">
                                <Badge variant="secondary" className="flex items-center gap-1 bg-white/20 text-white border-white/30 hover:bg-white/30">
                                    <Shield className="h-3 w-3" />
                                    Patient
                                </Badge>
                                {user && (
                                    <Badge variant="secondary" className="flex items-center gap-1 bg-white/20 text-white border-white/30 hover:bg-white/30">
                                        <User className="h-3 w-3" />
                                        {user.sex}
                                    </Badge>
                                )}
                                {hasPermission('lab_results.download') && (
                                    <Button className="bg-white/20 hover:bg-white/30 text-white border-white/30 hover:border-white/40 transition-all duration-200">
                                        <Download className="mr-2 h-4 w-4" />
                                        Download Results
                                    </Button>
                                )}
                            </div>
                        </div>
                        {/* Decorative elements */}
                        <div className="absolute -top-4 -right-4 w-24 h-24 bg-white/10 rounded-full"></div>
                        <div className="absolute -bottom-2 -left-2 w-16 h-16 bg-white/5 rounded-full"></div>
                    </div>

                    {/* Laboratory Results */}
                    <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                        <CardHeader>
                            <CardTitle className="text-lg font-semibold text-slate-900 dark:text-white">Laboratory Results</CardTitle>
                            <CardDescription className="text-slate-600 dark:text-slate-300">
                                Your complete lab test results and analysis
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                {labResults.map((result) => (
                                    <div key={result.id} className="flex items-center justify-between p-4 border border-slate-200 dark:border-slate-700 rounded-lg hover:shadow-md transition-all duration-200 bg-slate-50 dark:bg-slate-700/50">
                                        <div className="flex items-center space-x-4">
                                            <div className="flex-shrink-0">
                                                <div className="p-2 bg-rose-100 dark:bg-rose-900/20 rounded-full">
                                                    <TestTube className="h-5 w-5 text-rose-600 dark:text-rose-400" />
                                                </div>
                                            </div>
                                            <div>
                                                <h3 className="font-medium text-slate-900 dark:text-white">{result.testName}</h3>
                                                <p className="text-sm text-slate-600 dark:text-slate-400">{result.notes}</p>
                                                <div className="flex items-center space-x-4 mt-1">
                                                    <span className="text-xs text-slate-600 dark:text-slate-400 flex items-center">
                                                        <Calendar className="mr-1 h-3 w-3" />
                                                        {result.date}
                                                    </span>
                                                    <span className="text-xs text-slate-600 dark:text-slate-400 flex items-center">
                                                        <Stethoscope className="mr-1 h-3 w-3" />
                                                        {result.doctor}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div className="flex items-center space-x-4">
                                            <div className="text-right">
                                                <div className={`text-sm font-medium ${getResultsColor(result.results)}`}>
                                                    {result.results}
                                                </div>
                                                <Badge variant={getStatusColor(result.status)} className="mt-1">
                                                    {result.status}
                                                </Badge>
                                            </div>
                                            <div className="flex items-center space-x-2">
                                                {hasPermission('lab_results.view') && (
                                                    <Button variant="outline" size="sm" className="border-slate-200 dark:border-slate-700 hover:bg-rose-50 dark:hover:bg-rose-900/20 hover:border-rose-300 dark:hover:border-rose-600 transition-all duration-200">
                                                        <Eye className="h-4 w-4" />
                                                    </Button>
                                                )}
                                                {hasPermission('lab_results.download') && (
                                                    <Button variant="outline" size="sm" className="border-slate-200 dark:border-slate-700 hover:bg-rose-50 dark:hover:bg-rose-900/20 hover:border-rose-300 dark:hover:border-rose-600 transition-all duration-200">
                                                        <Download className="h-4 w-4" />
                                                    </Button>
                                                )}
                                            </div>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </CardContent>
                    </Card>

                    {/* Lab Results Summary */}
                    <div className="grid gap-6 md:grid-cols-3">
                        <Card className="relative overflow-hidden border-0 shadow-lg hover:shadow-xl transition-all duration-300 bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20">
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium text-slate-700 dark:text-slate-300">Normal Results</CardTitle>
                                <div className="p-2 bg-green-500 rounded-lg">
                                    <CheckCircle className="h-4 w-4 text-white" />
                                </div>
                            </CardHeader>
                            <CardContent>
                                <div className="text-3xl font-bold text-slate-900 dark:text-white">3</div>
                                <div className="flex items-center mt-2">
                                    <TrendingUp className="h-3 w-3 text-green-500 mr-1" />
                                    <p className="text-xs text-slate-600 dark:text-slate-400">
                                        Normal test results
                                    </p>
                                </div>
                            </CardContent>
                        </Card>
                        <Card className="relative overflow-hidden border-0 shadow-lg hover:shadow-xl transition-all duration-300 bg-gradient-to-br from-yellow-50 to-yellow-100 dark:from-yellow-900/20 dark:to-yellow-800/20">
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium text-slate-700 dark:text-slate-300">Abnormal Results</CardTitle>
                                <div className="p-2 bg-yellow-500 rounded-lg">
                                    <AlertCircle className="h-4 w-4 text-white" />
                                </div>
                            </CardHeader>
                            <CardContent>
                                <div className="text-3xl font-bold text-slate-900 dark:text-white">1</div>
                                <div className="flex items-center mt-2">
                                    <AlertCircle className="h-3 w-3 text-yellow-500 mr-1" />
                                    <p className="text-xs text-slate-600 dark:text-slate-400">
                                        Require attention
                                    </p>
                                </div>
                            </CardContent>
                        </Card>
                        <Card className="relative overflow-hidden border-0 shadow-lg hover:shadow-xl transition-all duration-300 bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20">
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium text-slate-700 dark:text-slate-300">Total Tests</CardTitle>
                                <div className="p-2 bg-blue-500 rounded-lg">
                                    <TestTube className="h-4 w-4 text-white" />
                                </div>
                            </CardHeader>
                            <CardContent>
                                <div className="text-3xl font-bold text-slate-900 dark:text-white">4</div>
                                <div className="flex items-center mt-2">
                                    <TrendingUp className="h-3 w-3 text-blue-500 mr-1" />
                                    <p className="text-xs text-slate-600 dark:text-slate-400">
                                        All time tests
                                    </p>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    {/* Important Notes */}
                    <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                        <CardHeader>
                            <CardTitle className="flex items-center text-lg font-semibold text-slate-900 dark:text-white">
                                <AlertCircle className="mr-2 h-5 w-5 text-rose-600 dark:text-rose-400" />
                                Important Notes
                            </CardTitle>
                            <CardDescription className="text-slate-600 dark:text-slate-300">
                                Important information about your lab results
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                <div className="flex items-start p-4 border border-slate-200 dark:border-slate-700 rounded-lg bg-yellow-50 dark:bg-yellow-900/20 hover:shadow-md transition-all duration-200">
                                    <AlertCircle className="h-5 w-5 text-yellow-600 mr-3 mt-0.5" />
                                    <div>
                                        <h3 className="font-medium text-slate-900 dark:text-white">Thyroid Function Test</h3>
                                        <p className="text-sm text-slate-600 dark:text-slate-400">
                                            Your TSH levels are slightly elevated. Please schedule a follow-up appointment with Dr. Sarah Johnson to discuss further evaluation and potential treatment options.
                                        </p>
                                    </div>
                                </div>
                                <div className="flex items-start p-4 border border-slate-200 dark:border-slate-700 rounded-lg bg-blue-50 dark:bg-blue-900/20 hover:shadow-md transition-all duration-200">
                                    <CheckCircle className="h-5 w-5 text-blue-600 mr-3 mt-0.5" />
                                    <div>
                                        <h3 className="font-medium text-slate-900 dark:text-white">General Health</h3>
                                        <p className="text-sm text-slate-600 dark:text-slate-400">
                                            Your overall lab results are good. Continue with your current medication regimen and maintain a healthy lifestyle.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AppLayout>
    );
}
