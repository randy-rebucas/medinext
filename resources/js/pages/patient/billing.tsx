import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { patientBilling, patientDashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    CreditCard,
    Download,
    Eye,
    Calendar,
    DollarSign,
    FileText,
    Clock,
    AlertCircle,
    TrendingUp,
    Shield,
    User
} from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Patient Portal',
        href: patientDashboard(),
    },
    {
        title: 'Billing',
        href: patientBilling(),
    },
];

interface PatientBillingProps {
    user?: {
        id: number;
        name: string;
        email: string;
        patient_id: string;
        sex: string;
    };
    permissions?: string[];
}

export default function PatientBilling({ user, permissions }: PatientBillingProps) {
    const hasPermission = (permission: string) => {
        return permissions?.includes(permission) ?? true;
    };
    const bills = [
        {
            id: 1,
            date: '2024-01-18',
            description: 'Consultation - Dr. Michael Brown',
            amount: 150.00,
            status: 'Paid',
            dueDate: '2024-02-18',
            paymentMethod: 'Credit Card'
        },
        {
            id: 2,
            date: '2024-01-15',
            description: 'Lab Tests - Blood Work',
            amount: 85.50,
            status: 'Paid',
            dueDate: '2024-02-15',
            paymentMethod: 'Insurance'
        },
        {
            id: 3,
            date: '2024-01-12',
            description: 'Follow-up Visit - Dr. Emily Davis',
            amount: 120.00,
            status: 'Pending',
            dueDate: '2024-02-12',
            paymentMethod: 'Pending'
        },
        {
            id: 4,
            date: '2024-01-10',
            description: 'Prescription - Medication',
            amount: 25.00,
            status: 'Overdue',
            dueDate: '2024-01-25',
            paymentMethod: 'Pending'
        }
    ];

    const getStatusColor = (status: string) => {
        switch (status) {
            case 'Paid': return 'default';
            case 'Pending': return 'secondary';
            case 'Overdue': return 'destructive';
            default: return 'secondary';
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Billing - Medinext">
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
                                <h1 className="text-3xl font-bold tracking-tight">Billing</h1>
                                <p className="mt-2 text-rose-100">
                                    View your bills, payments, and insurance information
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
                                {hasPermission('billing.download') && (
                                    <Button className="bg-white/20 hover:bg-white/30 text-white border-white/30 hover:border-white/40 transition-all duration-200">
                                        <Download className="mr-2 h-4 w-4" />
                                        Download Statement
                                    </Button>
                                )}
                            </div>
                        </div>
                        {/* Decorative elements */}
                        <div className="absolute -top-4 -right-4 w-24 h-24 bg-white/10 rounded-full"></div>
                        <div className="absolute -bottom-2 -left-2 w-16 h-16 bg-white/5 rounded-full"></div>
                    </div>

                    {/* Billing Summary */}
                    <div className="grid gap-6 md:grid-cols-4">
                        <Card className="relative overflow-hidden border-0 shadow-lg hover:shadow-xl transition-all duration-300 bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20">
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium text-slate-700 dark:text-slate-300">Total Paid</CardTitle>
                                <div className="p-2 bg-green-500 rounded-lg">
                                    <DollarSign className="h-4 w-4 text-white" />
                                </div>
                            </CardHeader>
                            <CardContent>
                                <div className="text-3xl font-bold text-slate-900 dark:text-white">$235.50</div>
                                <div className="flex items-center mt-2">
                                    <TrendingUp className="h-3 w-3 text-green-500 mr-1" />
                                    <p className="text-xs text-slate-600 dark:text-slate-400">
                                        All time payments
                                    </p>
                                </div>
                            </CardContent>
                        </Card>
                        <Card className="relative overflow-hidden border-0 shadow-lg hover:shadow-xl transition-all duration-300 bg-gradient-to-br from-yellow-50 to-yellow-100 dark:from-yellow-900/20 dark:to-yellow-800/20">
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium text-slate-700 dark:text-slate-300">Pending</CardTitle>
                                <div className="p-2 bg-yellow-500 rounded-lg">
                                    <Clock className="h-4 w-4 text-white" />
                                </div>
                            </CardHeader>
                            <CardContent>
                                <div className="text-3xl font-bold text-slate-900 dark:text-white">$120.00</div>
                                <div className="flex items-center mt-2">
                                    <Clock className="h-3 w-3 text-yellow-500 mr-1" />
                                    <p className="text-xs text-slate-600 dark:text-slate-400">
                                        Awaiting payment
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
                                <div className="text-3xl font-bold text-slate-900 dark:text-white">$25.00</div>
                                <div className="flex items-center mt-2">
                                    <AlertCircle className="h-3 w-3 text-red-500 mr-1" />
                                    <p className="text-xs text-slate-600 dark:text-slate-400">
                                        Past due amount
                                    </p>
                                </div>
                            </CardContent>
                        </Card>
                        <Card className="relative overflow-hidden border-0 shadow-lg hover:shadow-xl transition-all duration-300 bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20">
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium text-slate-700 dark:text-slate-300">Total Balance</CardTitle>
                                <div className="p-2 bg-blue-500 rounded-lg">
                                    <CreditCard className="h-4 w-4 text-white" />
                                </div>
                            </CardHeader>
                            <CardContent>
                                <div className="text-3xl font-bold text-slate-900 dark:text-white">$145.00</div>
                                <div className="flex items-center mt-2">
                                    <CreditCard className="h-3 w-3 text-blue-500 mr-1" />
                                    <p className="text-xs text-slate-600 dark:text-slate-400">
                                        Outstanding balance
                                    </p>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    {/* Billing History */}
                    <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                        <CardHeader>
                            <CardTitle className="text-lg font-semibold text-slate-900 dark:text-white">Billing History</CardTitle>
                            <CardDescription className="text-slate-600 dark:text-slate-300">
                                Your complete billing history and payment records
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                {bills.map((bill) => (
                                    <div key={bill.id} className="flex items-center justify-between p-4 border border-slate-200 dark:border-slate-700 rounded-lg hover:shadow-md transition-all duration-200 bg-slate-50 dark:bg-slate-700/50">
                                        <div className="flex items-center space-x-4">
                                            <div className="flex-shrink-0">
                                                <div className="p-2 bg-rose-100 dark:bg-rose-900/20 rounded-full">
                                                    <FileText className="h-5 w-5 text-rose-600 dark:text-rose-400" />
                                                </div>
                                            </div>
                                            <div>
                                                <h3 className="font-medium text-slate-900 dark:text-white">{bill.description}</h3>
                                                <div className="flex items-center space-x-4 mt-1">
                                                    <span className="text-xs text-slate-600 dark:text-slate-400 flex items-center">
                                                        <Calendar className="mr-1 h-3 w-3" />
                                                        {bill.date}
                                                    </span>
                                                    <span className="text-xs text-slate-600 dark:text-slate-400">
                                                        Due: {bill.dueDate}
                                                    </span>
                                                    <span className="text-xs text-slate-600 dark:text-slate-400">
                                                        {bill.paymentMethod}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div className="flex items-center space-x-4">
                                            <div className="text-right">
                                                <div className="text-lg font-bold text-slate-900 dark:text-white">${bill.amount.toFixed(2)}</div>
                                                <Badge variant={getStatusColor(bill.status)} className="mt-1">
                                                    {bill.status}
                                                </Badge>
                                            </div>
                                            <div className="flex items-center space-x-2">
                                                {hasPermission('billing.view') && (
                                                    <Button variant="outline" size="sm" className="border-slate-200 dark:border-slate-700 hover:bg-rose-50 dark:hover:bg-rose-900/20 hover:border-rose-300 dark:hover:border-rose-600 transition-all duration-200">
                                                        <Eye className="h-4 w-4" />
                                                    </Button>
                                                )}
                                                {hasPermission('billing.download') && (
                                                    <Button variant="outline" size="sm" className="border-slate-200 dark:border-slate-700 hover:bg-rose-50 dark:hover:bg-rose-900/20 hover:border-rose-300 dark:hover:border-rose-600 transition-all duration-200">
                                                        <Download className="h-4 w-4" />
                                                    </Button>
                                                )}
                                                {bill.status !== 'Paid' && hasPermission('billing.pay') && (
                                                    <Button size="sm" className="hover:bg-rose-600 hover:border-rose-600 transition-all duration-200">
                                                        Pay Now
                                                    </Button>
                                                )}
                                            </div>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </CardContent>
                    </Card>

                    {/* Payment Methods */}
                    <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                        <CardHeader>
                            <CardTitle className="text-lg font-semibold text-slate-900 dark:text-white">Payment Methods</CardTitle>
                            <CardDescription className="text-slate-600 dark:text-slate-300">
                                Manage your payment methods and preferences
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                <div className="flex items-center justify-between p-4 border border-slate-200 dark:border-slate-700 rounded-lg hover:shadow-md transition-all duration-200 bg-slate-50 dark:bg-slate-700/50">
                                    <div className="flex items-center space-x-4">
                                        <div className="p-2 bg-blue-100 dark:bg-blue-900/20 rounded-full">
                                            <CreditCard className="h-5 w-5 text-blue-600 dark:text-blue-400" />
                                        </div>
                                        <div>
                                            <h3 className="font-medium text-slate-900 dark:text-white">Visa ending in 1234</h3>
                                            <p className="text-sm text-slate-600 dark:text-slate-400">Expires 12/25</p>
                                        </div>
                                    </div>
                                    <div className="flex items-center space-x-2">
                                        <Badge variant="default" className="bg-rose-600 hover:bg-rose-700">Primary</Badge>
                                        {hasPermission('payment.edit') && (
                                            <Button variant="outline" size="sm" className="border-slate-200 dark:border-slate-700 hover:bg-rose-50 dark:hover:bg-rose-900/20 hover:border-rose-300 dark:hover:border-rose-600 transition-all duration-200">
                                                Edit
                                            </Button>
                                        )}
                                    </div>
                                </div>
                                <div className="flex items-center justify-between p-4 border border-slate-200 dark:border-slate-700 rounded-lg hover:shadow-md transition-all duration-200 bg-slate-50 dark:bg-slate-700/50">
                                    <div className="flex items-center space-x-4">
                                        <div className="p-2 bg-green-100 dark:bg-green-900/20 rounded-full">
                                            <CreditCard className="h-5 w-5 text-green-600 dark:text-green-400" />
                                        </div>
                                        <div>
                                            <h3 className="font-medium text-slate-900 dark:text-white">Mastercard ending in 5678</h3>
                                            <p className="text-sm text-slate-600 dark:text-slate-400">Expires 08/26</p>
                                        </div>
                                    </div>
                                    <div className="flex items-center space-x-2">
                                        {hasPermission('payment.edit') && (
                                            <Button variant="outline" size="sm" className="border-slate-200 dark:border-slate-700 hover:bg-rose-50 dark:hover:bg-rose-900/20 hover:border-rose-300 dark:hover:border-rose-600 transition-all duration-200">
                                                Edit
                                            </Button>
                                        )}
                                    </div>
                                </div>
                            </div>
                            {hasPermission('payment.add') && (
                                <div className="mt-4">
                                    <Button variant="outline" className="border-slate-200 dark:border-slate-700 hover:bg-rose-50 dark:hover:bg-rose-900/20 hover:border-rose-300 dark:hover:border-rose-600 transition-all duration-200">
                                        <CreditCard className="mr-2 h-4 w-4" />
                                        Add Payment Method
                                    </Button>
                                </div>
                            )}
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AppLayout>
    );
}
