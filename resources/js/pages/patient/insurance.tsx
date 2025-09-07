import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    Shield,
    CreditCard,
    Calendar,
    CheckCircle,
    AlertCircle,
    FileText,
    Download,
    TrendingUp,
    User
} from 'lucide-react';
import { patientDashboard, patientInsurance } from '@/routes';
import { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Patient Portal',
        href: patientDashboard(),
    },
    {
        title: 'Insurance',
        href: patientInsurance(),
    },
];

interface PatientInsuranceProps {
    user?: {
        id: number;
        name: string;
        email: string;
        patient_id: string;
        sex: string;
    };
    permissions?: string[];
}

export default function PatientInsurance({ user, permissions }: PatientInsuranceProps) {
    const hasPermission = (permission: string) => {
        return permissions?.includes(permission) ?? true;
    };
    const insuranceInfo = {
        primary: {
            provider: 'Blue Cross Blue Shield',
            policyNumber: 'BC123456789',
            groupNumber: 'GRP001',
            subscriberId: 'SUB123',
            effectiveDate: '2024-01-01',
            expiryDate: '2024-12-31',
            status: 'Active',
            coverage: '100%'
        },
        secondary: {
            provider: 'Medicare',
            policyNumber: 'MED123456789',
            groupNumber: 'N/A',
            subscriberId: 'SUB456',
            effectiveDate: '2024-01-01',
            expiryDate: '2024-12-31',
            status: 'Active',
            coverage: '80%'
        }
    };

    const claims = [
        {
            id: 1,
            date: '2024-01-18',
            description: 'Consultation - Dr. Michael Brown',
            amount: 150.00,
            covered: 150.00,
            status: 'Approved',
            claimNumber: 'CLM001234'
        },
        {
            id: 2,
            date: '2024-01-15',
            description: 'Lab Tests - Blood Work',
            amount: 85.50,
            covered: 85.50,
            status: 'Approved',
            claimNumber: 'CLM001235'
        },
        {
            id: 3,
            date: '2024-01-12',
            description: 'Follow-up Visit - Dr. Emily Davis',
            amount: 120.00,
            covered: 120.00,
            status: 'Pending',
            claimNumber: 'CLM001236'
        }
    ];

    const getStatusColor = (status: string) => {
        switch (status) {
            case 'Active': return 'default';
            case 'Approved': return 'default';
            case 'Pending': return 'secondary';
            case 'Denied': return 'destructive';
            default: return 'secondary';
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Insurance - Medinext">
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
                                <h1 className="text-3xl font-bold tracking-tight">Insurance</h1>
                                <p className="mt-2 text-rose-100">
                                    Manage your insurance information and claims
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
                                {hasPermission('insurance.download') && (
                                    <Button className="bg-white/20 hover:bg-white/30 text-white border-white/30 hover:border-white/40 transition-all duration-200">
                                        <Download className="mr-2 h-4 w-4" />
                                        Download Card
                                    </Button>
                                )}
                            </div>
                        </div>
                        {/* Decorative elements */}
                        <div className="absolute -top-4 -right-4 w-24 h-24 bg-white/10 rounded-full"></div>
                        <div className="absolute -bottom-2 -left-2 w-16 h-16 bg-white/5 rounded-full"></div>
                    </div>

                    {/* Insurance Cards */}
                    <div className="grid gap-6 md:grid-cols-2">
                        <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                            <CardHeader>
                                <CardTitle className="flex items-center text-lg font-semibold text-slate-900 dark:text-white">
                                    <Shield className="mr-2 h-5 w-5 text-rose-600 dark:text-rose-400" />
                                    Primary Insurance
                                </CardTitle>
                                <CardDescription className="text-slate-600 dark:text-slate-300">
                                    Your primary insurance coverage
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="space-y-2">
                                    <div className="flex justify-between">
                                        <span className="text-sm font-medium text-slate-700 dark:text-slate-300">Provider:</span>
                                        <span className="text-sm text-slate-900 dark:text-white">{insuranceInfo.primary.provider}</span>
                                    </div>
                                    <div className="flex justify-between">
                                        <span className="text-sm font-medium text-slate-700 dark:text-slate-300">Policy Number:</span>
                                        <span className="text-sm font-mono text-slate-900 dark:text-white">{insuranceInfo.primary.policyNumber}</span>
                                    </div>
                                    <div className="flex justify-between">
                                        <span className="text-sm font-medium text-slate-700 dark:text-slate-300">Group Number:</span>
                                        <span className="text-sm font-mono text-slate-900 dark:text-white">{insuranceInfo.primary.groupNumber}</span>
                                    </div>
                                    <div className="flex justify-between">
                                        <span className="text-sm font-medium text-slate-700 dark:text-slate-300">Coverage:</span>
                                        <Badge variant="outline" className="border-slate-200 dark:border-slate-700">{insuranceInfo.primary.coverage}</Badge>
                                    </div>
                                    <div className="flex justify-between">
                                        <span className="text-sm font-medium text-slate-700 dark:text-slate-300">Status:</span>
                                        <Badge variant={getStatusColor(insuranceInfo.primary.status)}>
                                            {insuranceInfo.primary.status}
                                        </Badge>
                                    </div>
                                    <div className="flex justify-between">
                                        <span className="text-sm font-medium text-slate-700 dark:text-slate-300">Effective Date:</span>
                                        <span className="text-sm text-slate-900 dark:text-white">{insuranceInfo.primary.effectiveDate}</span>
                                    </div>
                                    <div className="flex justify-between">
                                        <span className="text-sm font-medium text-slate-700 dark:text-slate-300">Expiry Date:</span>
                                        <span className="text-sm text-slate-900 dark:text-white">{insuranceInfo.primary.expiryDate}</span>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                            <CardHeader>
                                <CardTitle className="flex items-center text-lg font-semibold text-slate-900 dark:text-white">
                                    <Shield className="mr-2 h-5 w-5 text-rose-600 dark:text-rose-400" />
                                    Secondary Insurance
                                </CardTitle>
                                <CardDescription className="text-slate-600 dark:text-slate-300">
                                    Your secondary insurance coverage
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="space-y-2">
                                    <div className="flex justify-between">
                                        <span className="text-sm font-medium text-slate-700 dark:text-slate-300">Provider:</span>
                                        <span className="text-sm text-slate-900 dark:text-white">{insuranceInfo.secondary.provider}</span>
                                    </div>
                                    <div className="flex justify-between">
                                        <span className="text-sm font-medium text-slate-700 dark:text-slate-300">Policy Number:</span>
                                        <span className="text-sm font-mono text-slate-900 dark:text-white">{insuranceInfo.secondary.policyNumber}</span>
                                    </div>
                                    <div className="flex justify-between">
                                        <span className="text-sm font-medium text-slate-700 dark:text-slate-300">Group Number:</span>
                                        <span className="text-sm font-mono text-slate-900 dark:text-white">{insuranceInfo.secondary.groupNumber}</span>
                                    </div>
                                    <div className="flex justify-between">
                                        <span className="text-sm font-medium text-slate-700 dark:text-slate-300">Coverage:</span>
                                        <Badge variant="outline" className="border-slate-200 dark:border-slate-700">{insuranceInfo.secondary.coverage}</Badge>
                                    </div>
                                    <div className="flex justify-between">
                                        <span className="text-sm font-medium text-slate-700 dark:text-slate-300">Status:</span>
                                        <Badge variant={getStatusColor(insuranceInfo.secondary.status)}>
                                            {insuranceInfo.secondary.status}
                                        </Badge>
                                    </div>
                                    <div className="flex justify-between">
                                        <span className="text-sm font-medium text-slate-700 dark:text-slate-300">Effective Date:</span>
                                        <span className="text-sm text-slate-900 dark:text-white">{insuranceInfo.secondary.effectiveDate}</span>
                                    </div>
                                    <div className="flex justify-between">
                                        <span className="text-sm font-medium text-slate-700 dark:text-slate-300">Expiry Date:</span>
                                        <span className="text-sm text-slate-900 dark:text-white">{insuranceInfo.secondary.expiryDate}</span>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    {/* Claims History */}
                    <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                        <CardHeader>
                            <CardTitle className="text-lg font-semibold text-slate-900 dark:text-white">Claims History</CardTitle>
                            <CardDescription className="text-slate-600 dark:text-slate-300">
                                Your insurance claims and coverage details
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                {claims.map((claim) => (
                                    <div key={claim.id} className="flex items-center justify-between p-4 border border-slate-200 dark:border-slate-700 rounded-lg hover:shadow-md transition-all duration-200 bg-slate-50 dark:bg-slate-700/50">
                                        <div className="flex items-center space-x-4">
                                            <div className="flex-shrink-0">
                                                <div className="p-2 bg-rose-100 dark:bg-rose-900/20 rounded-full">
                                                    <FileText className="h-5 w-5 text-rose-600 dark:text-rose-400" />
                                                </div>
                                            </div>
                                            <div>
                                                <h3 className="font-medium text-slate-900 dark:text-white">{claim.description}</h3>
                                                <p className="text-sm text-slate-600 dark:text-slate-400">Claim #: {claim.claimNumber}</p>
                                                <div className="flex items-center space-x-4 mt-1">
                                                    <span className="text-xs text-slate-600 dark:text-slate-400 flex items-center">
                                                        <Calendar className="mr-1 h-3 w-3" />
                                                        {claim.date}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div className="flex items-center space-x-4">
                                            <div className="text-right">
                                                <div className="text-sm font-medium text-slate-900 dark:text-white">${claim.amount.toFixed(2)}</div>
                                                <div className="text-xs text-slate-600 dark:text-slate-400">Covered: ${claim.covered.toFixed(2)}</div>
                                                <Badge variant={getStatusColor(claim.status)} className="mt-1">
                                                    {claim.status}
                                                </Badge>
                                            </div>
                                            {hasPermission('insurance.download') && (
                                                <Button variant="outline" size="sm" className="border-slate-200 dark:border-slate-700 hover:bg-rose-50 dark:hover:bg-rose-900/20 hover:border-rose-300 dark:hover:border-rose-600 transition-all duration-200">
                                                    <Download className="h-4 w-4" />
                                                </Button>
                                            )}
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </CardContent>
                    </Card>

                    {/* Insurance Summary */}
                    <div className="grid gap-6 md:grid-cols-3">
                        <Card className="relative overflow-hidden border-0 shadow-lg hover:shadow-xl transition-all duration-300 bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20">
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium text-slate-700 dark:text-slate-300">Approved Claims</CardTitle>
                                <div className="p-2 bg-green-500 rounded-lg">
                                    <CheckCircle className="h-4 w-4 text-white" />
                                </div>
                            </CardHeader>
                            <CardContent>
                                <div className="text-3xl font-bold text-slate-900 dark:text-white">2</div>
                                <div className="flex items-center mt-2">
                                    <TrendingUp className="h-3 w-3 text-green-500 mr-1" />
                                    <p className="text-xs text-slate-600 dark:text-slate-400">
                                        Approved claims
                                    </p>
                                </div>
                            </CardContent>
                        </Card>
                        <Card className="relative overflow-hidden border-0 shadow-lg hover:shadow-xl transition-all duration-300 bg-gradient-to-br from-yellow-50 to-yellow-100 dark:from-yellow-900/20 dark:to-yellow-800/20">
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium text-slate-700 dark:text-slate-300">Pending Claims</CardTitle>
                                <div className="p-2 bg-yellow-500 rounded-lg">
                                    <AlertCircle className="h-4 w-4 text-white" />
                                </div>
                            </CardHeader>
                            <CardContent>
                                <div className="text-3xl font-bold text-slate-900 dark:text-white">1</div>
                                <div className="flex items-center mt-2">
                                    <AlertCircle className="h-3 w-3 text-yellow-500 mr-1" />
                                    <p className="text-xs text-slate-600 dark:text-slate-400">
                                        Awaiting approval
                                    </p>
                                </div>
                            </CardContent>
                        </Card>
                        <Card className="relative overflow-hidden border-0 shadow-lg hover:shadow-xl transition-all duration-300 bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20">
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium text-slate-700 dark:text-slate-300">Total Covered</CardTitle>
                                <div className="p-2 bg-blue-500 rounded-lg">
                                    <CreditCard className="h-4 w-4 text-white" />
                                </div>
                            </CardHeader>
                            <CardContent>
                                <div className="text-3xl font-bold text-slate-900 dark:text-white">$355.50</div>
                                <div className="flex items-center mt-2">
                                    <CreditCard className="h-3 w-3 text-blue-500 mr-1" />
                                    <p className="text-xs text-slate-600 dark:text-slate-400">
                                        Insurance coverage
                                    </p>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
