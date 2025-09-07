import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    Briefcase,
    Calendar,
    CheckCircle,
    Clock,
    AlertCircle,
    TrendingUp,
    Building2,
    Shield
} from 'lucide-react';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Commitment Tracking',
        href: '/medrep/commitments',
    },
    {
        title: 'Commitment Tracking',
        href: '/medrep/commitments',
    },
];

interface CommitmentTrackingProps {
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

export default function CommitmentTracking({ user }: CommitmentTrackingProps = {}) {
    const commitments = [
        {
            id: 1,
            title: 'CardioMax Product Trial',
            doctor: 'Dr. Sarah Johnson',
            specialty: 'Cardiology',
            startDate: '2024-01-15',
            endDate: '2024-02-15',
            status: 'In Progress',
            progress: 65,
            notes: 'Doctor agreed to trial CardioMax with 5 patients'
        },
        {
            id: 2,
            title: 'DermaCare Sample Distribution',
            doctor: 'Dr. Emily Davis',
            specialty: 'Dermatology',
            startDate: '2024-01-10',
            endDate: '2024-01-25',
            status: 'Completed',
            progress: 100,
            notes: 'Successfully distributed 10 samples to patients'
        },
        {
            id: 3,
            title: 'NeuroCalm Presentation',
            doctor: 'Dr. Michael Brown',
            specialty: 'Pediatrics',
            startDate: '2024-01-20',
            endDate: '2024-01-30',
            status: 'Scheduled',
            progress: 0,
            notes: 'Scheduled presentation for next week'
        },
        {
            id: 4,
            title: 'OrthoFlex Follow-up',
            doctor: 'Dr. James Wilson',
            specialty: 'Orthopedics',
            startDate: '2024-01-12',
            endDate: '2024-01-26',
            status: 'Overdue',
            progress: 40,
            notes: 'Need to follow up on initial product discussion'
        }
    ];

    const getStatusColor = (status: string) => {
        switch (status) {
            case 'Completed': return 'default';
            case 'In Progress': return 'secondary';
            case 'Scheduled': return 'default';
            case 'Overdue': return 'destructive';
            default: return 'secondary';
        }
    };

    const getStatusIcon = (status: string) => {
        switch (status) {
            case 'Completed': return <CheckCircle className="h-4 w-4 text-green-600" />;
            case 'In Progress': return <Clock className="h-4 w-4 text-blue-600" />;
            case 'Scheduled': return <Calendar className="h-4 w-4 text-purple-600" />;
            case 'Overdue': return <AlertCircle className="h-4 w-4 text-red-600" />;
            default: return <Clock className="h-4 w-4 text-gray-600" />;
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Commitment Tracking - Medinext">
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
                                    Commitment Tracking
                                </h1>
                                <p className="mt-2 text-emerald-100">
                                    Track and manage commitments with healthcare professionals
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
                            <Briefcase className="mr-2 h-4 w-4" />
                            Add Commitment
                        </Button>
                    </div>

                    {/* Commitment Overview */}
                    <div className="grid gap-6 md:grid-cols-4">
                        <Card className="relative overflow-hidden border-0 shadow-lg hover:shadow-xl transition-all duration-300 bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20">
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium text-slate-700 dark:text-slate-300">Total Commitments</CardTitle>
                                <div className="p-2 bg-blue-500 rounded-lg">
                                    <Briefcase className="h-4 w-4 text-white" />
                                </div>
                            </CardHeader>
                            <CardContent>
                                <div className="text-3xl font-bold text-slate-900 dark:text-white">12</div>
                                <div className="flex items-center mt-2">
                                    <TrendingUp className="h-3 w-3 text-blue-500 mr-1" />
                                    <p className="text-xs text-slate-600 dark:text-slate-400">
                                        Active commitments
                                    </p>
                                </div>
                            </CardContent>
                        </Card>
                        <Card className="relative overflow-hidden border-0 shadow-lg hover:shadow-xl transition-all duration-300 bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20">
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium text-slate-700 dark:text-slate-300">Completed</CardTitle>
                                <div className="p-2 bg-green-500 rounded-lg">
                                    <CheckCircle className="h-4 w-4 text-white" />
                                </div>
                            </CardHeader>
                            <CardContent>
                                <div className="text-3xl font-bold text-slate-900 dark:text-white">5</div>
                                <div className="flex items-center mt-2">
                                    <CheckCircle className="h-3 w-3 text-green-500 mr-1" />
                                    <p className="text-xs text-slate-600 dark:text-slate-400">
                                        Successfully completed
                                    </p>
                                </div>
                            </CardContent>
                        </Card>
                        <Card className="relative overflow-hidden border-0 shadow-lg hover:shadow-xl transition-all duration-300 bg-gradient-to-br from-orange-50 to-orange-100 dark:from-orange-900/20 dark:to-orange-800/20">
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium text-slate-700 dark:text-slate-300">In Progress</CardTitle>
                                <div className="p-2 bg-orange-500 rounded-lg">
                                    <Clock className="h-4 w-4 text-white" />
                                </div>
                            </CardHeader>
                            <CardContent>
                                <div className="text-3xl font-bold text-slate-900 dark:text-white">4</div>
                                <div className="flex items-center mt-2">
                                    <Clock className="h-3 w-3 text-orange-500 mr-1" />
                                    <p className="text-xs text-slate-600 dark:text-slate-400">
                                        Currently active
                                    </p>
                                </div>
                            </CardContent>
                        </Card>
                        <Card className="relative overflow-hidden border-0 shadow-lg hover:shadow-xl transition-all duration-300 bg-gradient-to-br from-red-50 to-red-100 dark:from-red-900/20 dark:to-red-800/20">
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium text-slate-700 dark:text-slate-300">Overdue</CardTitle>
                                <div className="p-2 bg-red-500 rounded-lg">
                                    <AlertCircle className="h-4 w-4 text-white" />
                                </div>
                            </CardHeader>
                            <CardContent>
                                <div className="text-3xl font-bold text-slate-900 dark:text-white">1</div>
                                <div className="flex items-center mt-2">
                                    <AlertCircle className="h-3 w-3 text-red-500 mr-1" />
                                    <p className="text-xs text-slate-600 dark:text-slate-400">
                                        Requires attention
                                    </p>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                        <CardHeader>
                            <CardTitle className="text-lg font-semibold text-slate-900 dark:text-white">Active Commitments</CardTitle>
                            <CardDescription className="text-slate-600 dark:text-slate-300">
                                Track progress and manage your commitments
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                {commitments.map((commitment) => (
                                    <div key={commitment.id} className="flex items-center justify-between p-4 rounded-lg bg-slate-50 dark:bg-slate-700/50 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                                        <div className="flex items-center space-x-4">
                                            <div className="flex-shrink-0">
                                                {getStatusIcon(commitment.status)}
                                            </div>
                                            <div>
                                                <h3 className="font-medium text-slate-900 dark:text-white">{commitment.title}</h3>
                                                <p className="text-sm text-slate-600 dark:text-slate-400">{commitment.doctor} • {commitment.specialty}</p>
                                                <p className="text-sm text-slate-600 dark:text-slate-400">{commitment.notes}</p>
                                                <div className="flex items-center space-x-4 mt-1">
                                                    <span className="text-xs text-slate-500 dark:text-slate-500">
                                                        Start: {commitment.startDate}
                                                    </span>
                                                    <span className="text-xs text-slate-500 dark:text-slate-500">
                                                        End: {commitment.endDate}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div className="flex items-center space-x-4">
                                            <div className="text-right">
                                                <div className="text-sm font-medium text-slate-900 dark:text-white">Progress: {commitment.progress}%</div>
                                                <Badge variant={getStatusColor(commitment.status)} className="mt-1 border-0">
                                                    {commitment.status}
                                                </Badge>
                                            </div>
                                            <div className="w-16">
                                                <div className="bg-slate-200 dark:bg-slate-700 rounded-full h-2">
                                                    <div
                                                        className="bg-emerald-500 h-2 rounded-full"
                                                        style={{ width: `${commitment.progress}%` }}
                                                    ></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </CardContent>
                    </Card>

                    {/* Performance Summary */}
                    <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                        <CardHeader>
                            <CardTitle className="text-lg font-semibold text-slate-900 dark:text-white">Commitment Performance</CardTitle>
                            <CardDescription className="text-slate-600 dark:text-slate-300">
                                Overall performance metrics for your commitments
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="grid gap-6 md:grid-cols-3">
                                <div className="text-center p-6 rounded-lg bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20 border-0">
                                    <div className="text-3xl font-bold text-green-600 dark:text-green-400">92%</div>
                                    <div className="text-sm text-slate-600 dark:text-slate-400 mt-1">Completion Rate</div>
                                </div>
                                <div className="text-center p-6 rounded-lg bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 border-0">
                                    <div className="text-3xl font-bold text-blue-600 dark:text-blue-400">8.5</div>
                                    <div className="text-sm text-slate-600 dark:text-slate-400 mt-1">Average Days</div>
                                </div>
                                <div className="text-center p-6 rounded-lg bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20 border-0">
                                    <div className="text-3xl font-bold text-purple-600 dark:text-purple-400">15</div>
                                    <div className="text-sm text-slate-600 dark:text-slate-400 mt-1">Total Value</div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AppLayout>
    );
}
