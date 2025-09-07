import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';

import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    Pill,
    Download,
    Eye,
    Calendar,
    Stethoscope,
    Clock,
    AlertCircle,
    Shield,
    User
} from 'lucide-react';
import { patientDashboard,patientPrescriptions } from '@/routes';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Patient Portal',
        href: patientDashboard(),
    },
    {
        title: 'My Prescriptions',
        href: patientPrescriptions(),
    },
];

interface PatientPrescriptionsProps {
    user?: {
        id: number;
        name: string;
        email: string;
        patient_id: string;
        sex: string;
    };
    permissions?: string[];
}

export default function PatientPrescriptions({ user, permissions }: PatientPrescriptionsProps) {
    const hasPermission = (permission: string) => {
        return permissions?.includes(permission) ?? true;
    };
    const prescriptions = [
        {
            id: 1,
            medication: 'Lisinopril 10mg',
            dosage: 'Once daily',
            prescribedBy: 'Dr. Michael Brown',
            date: '2024-01-18',
            status: 'Active',
            refills: 3,
            nextRefill: '2024-02-18',
            instructions: 'Take with food, preferably in the morning'
        },
        {
            id: 2,
            medication: 'Metformin 500mg',
            dosage: 'Twice daily',
            prescribedBy: 'Dr. Sarah Johnson',
            date: '2024-01-10',
            status: 'Active',
            refills: 2,
            nextRefill: '2024-02-10',
            instructions: 'Take with meals to reduce stomach upset'
        },
        {
            id: 3,
            medication: 'Atorvastatin 20mg',
            dosage: 'Once daily at bedtime',
            prescribedBy: 'Dr. Emily Davis',
            date: '2024-01-15',
            status: 'Active',
            refills: 1,
            nextRefill: '2024-02-15',
            instructions: 'Take at bedtime, avoid alcohol'
        },
        {
            id: 4,
            medication: 'Ibuprofen 400mg',
            dosage: 'As needed for pain',
            prescribedBy: 'Dr. James Wilson',
            date: '2024-01-12',
            status: 'Completed',
            refills: 0,
            nextRefill: 'N/A',
            instructions: 'Take with food, maximum 3 times daily'
        }
    ];

    const getStatusColor = (status: string) => {
        switch (status) {
            case 'Active': return 'default';
            case 'Completed': return 'secondary';
            case 'Expired': return 'destructive';
            default: return 'secondary';
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="My Prescriptions - Medinext">
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
                                <h1 className="text-3xl font-bold tracking-tight">My Prescriptions</h1>
                                <p className="mt-2 text-rose-100">
                                    View and manage your current medications
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
                                {hasPermission('prescriptions.download') && (
                                    <Button className="bg-white/20 hover:bg-white/30 text-white border-white/30 hover:border-white/40 transition-all duration-200">
                                        <Download className="mr-2 h-4 w-4" />
                                        Download Prescriptions
                                    </Button>
                                )}
                            </div>
                        </div>
                        {/* Decorative elements */}
                        <div className="absolute -top-4 -right-4 w-24 h-24 bg-white/10 rounded-full"></div>
                        <div className="absolute -bottom-2 -left-2 w-16 h-16 bg-white/5 rounded-full"></div>
                    </div>

                    {/* Current Prescriptions */}
                    <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                        <CardHeader>
                            <CardTitle className="text-lg font-semibold text-slate-900 dark:text-white">Current Prescriptions</CardTitle>
                            <CardDescription className="text-slate-600 dark:text-slate-300">
                                Your active and past medication prescriptions
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                {prescriptions.map((prescription) => (
                                    <div key={prescription.id} className="flex items-center justify-between p-4 border border-slate-200 dark:border-slate-700 rounded-lg hover:shadow-md transition-all duration-200 bg-slate-50 dark:bg-slate-700/50">
                                        <div className="flex items-center space-x-4">
                                            <div className="flex-shrink-0">
                                                <div className="p-2 bg-rose-100 dark:bg-rose-900/20 rounded-full">
                                                    <Pill className="h-5 w-5 text-rose-600 dark:text-rose-400" />
                                                </div>
                                            </div>
                                            <div>
                                                <h3 className="font-medium text-slate-900 dark:text-white">{prescription.medication}</h3>
                                                <p className="text-sm text-slate-600 dark:text-slate-400">Dosage: {prescription.dosage}</p>
                                                <p className="text-sm text-slate-600 dark:text-slate-400">Instructions: {prescription.instructions}</p>
                                                <div className="flex items-center space-x-4 mt-1">
                                                    <span className="text-xs text-slate-600 dark:text-slate-400 flex items-center">
                                                        <Stethoscope className="mr-1 h-3 w-3" />
                                                        {prescription.prescribedBy}
                                                    </span>
                                                    <span className="text-xs text-slate-600 dark:text-slate-400 flex items-center">
                                                        <Calendar className="mr-1 h-3 w-3" />
                                                        {prescription.date}
                                                    </span>
                                                    <span className="text-xs text-slate-600 dark:text-slate-400">
                                                        Refills: {prescription.refills}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div className="flex items-center space-x-4">
                                            <div className="text-right">
                                                <Badge variant={getStatusColor(prescription.status)} className="mb-1">
                                                    {prescription.status}
                                                </Badge>
                                                <div className="text-sm font-medium text-slate-900 dark:text-white">Next Refill</div>
                                                <div className="text-xs text-slate-600 dark:text-slate-400">{prescription.nextRefill}</div>
                                            </div>
                                            <div className="flex items-center space-x-2">
                                                {hasPermission('prescriptions.view') && (
                                                    <Button variant="outline" size="sm" className="border-slate-200 dark:border-slate-700 hover:bg-rose-50 dark:hover:bg-rose-900/20 hover:border-rose-300 dark:hover:border-rose-600 transition-all duration-200">
                                                        <Eye className="h-4 w-4" />
                                                    </Button>
                                                )}
                                                {hasPermission('prescriptions.download') && (
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

                    {/* Prescription Alerts */}
                    <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                        <CardHeader>
                            <CardTitle className="flex items-center text-lg font-semibold text-slate-900 dark:text-white">
                                <AlertCircle className="mr-2 h-5 w-5 text-rose-600 dark:text-rose-400" />
                                Prescription Alerts
                            </CardTitle>
                            <CardDescription className="text-slate-600 dark:text-slate-300">
                                Important reminders about your medications
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                <div className="flex items-center p-4 border border-slate-200 dark:border-slate-700 rounded-lg bg-yellow-50 dark:bg-yellow-900/20 hover:shadow-md transition-all duration-200">
                                    <AlertCircle className="h-5 w-5 text-yellow-600 mr-3" />
                                    <div>
                                        <h3 className="font-medium text-slate-900 dark:text-white">Refill Reminder</h3>
                                        <p className="text-sm text-slate-600 dark:text-slate-400">
                                            Your Lisinopril prescription will need a refill in 5 days.
                                        </p>
                                    </div>
                                </div>
                                <div className="flex items-center p-4 border border-slate-200 dark:border-slate-700 rounded-lg bg-blue-50 dark:bg-blue-900/20 hover:shadow-md transition-all duration-200">
                                    <Clock className="h-5 w-5 text-blue-600 mr-3" />
                                    <div>
                                        <h3 className="font-medium text-slate-900 dark:text-white">Medication Reminder</h3>
                                        <p className="text-sm text-slate-600 dark:text-slate-400">
                                            Remember to take your Metformin with meals.
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
