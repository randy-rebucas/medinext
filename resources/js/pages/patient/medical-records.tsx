import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import {
    FileText,
    Download,
    TestTube,
    Pill,
    User,
    Shield
} from 'lucide-react';
import { patientDashboard, patientMedicalRecords } from '@/routes';
import { type BreadcrumbItem } from '@/types';
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Patient Portal',
        href: patientDashboard(),
    },
    {
        title: 'Medical Records',
        href: patientMedicalRecords(),
    },
];

interface MedicalRecordsProps {
    user?: {
        id: number;
        name: string;
        email: string;
        patient_id: string;
        sex: string;
    };
    permissions?: string[];
}

export default function MedicalRecords({ user, permissions }: MedicalRecordsProps) {
    const hasPermission = (permission: string) => {
        return permissions?.includes(permission) ?? true;
    };
    const medicalRecords = [
        {
            id: 1,
            date: '2024-01-18',
            doctor: 'Dr. Michael Brown',
            type: 'Follow-up',
            diagnosis: 'Hypertension management',
            treatment: 'Medication adjustment',
            status: 'Completed'
        },
        {
            id: 2,
            date: '2024-01-15',
            doctor: 'Dr. Emily Davis',
            type: 'Check-up',
            diagnosis: 'Annual physical examination',
            treatment: 'Routine check-up',
            status: 'Completed'
        },
        {
            id: 3,
            date: '2024-01-12',
            doctor: 'Dr. James Wilson',
            type: 'Consultation',
            diagnosis: 'Knee pain evaluation',
            treatment: 'Physical therapy recommended',
            status: 'Completed'
        }
    ];

    const labResults = [
        {
            id: 1,
            testName: 'Complete Blood Count',
            date: '2024-01-15',
            results: 'Normal',
            status: 'Completed'
        },
        {
            id: 2,
            testName: 'Lipid Panel',
            date: '2024-01-15',
            results: 'Normal',
            status: 'Completed'
        },
        {
            id: 3,
            testName: 'Blood Glucose',
            date: '2024-01-15',
            results: 'Normal',
            status: 'Completed'
        }
    ];

    const prescriptions = [
        {
            id: 1,
            medication: 'Lisinopril 10mg',
            dosage: 'Once daily',
            prescribedBy: 'Dr. Michael Brown',
            date: '2024-01-18',
            status: 'Active'
        },
        {
            id: 2,
            medication: 'Metformin 500mg',
            dosage: 'Twice daily',
            prescribedBy: 'Dr. Sarah Johnson',
            date: '2024-01-10',
            status: 'Active'
        }
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Medical Records - Medinext">
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
                                <h1 className="text-3xl font-bold tracking-tight">Medical Records</h1>
                                <p className="mt-2 text-rose-100">
                                    View your complete medical history and records
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
                                {hasPermission('medical_records.download') && (
                                    <Button className="bg-white/20 hover:bg-white/30 text-white border-white/30 hover:border-white/40 transition-all duration-200">
                                        <Download className="mr-2 h-4 w-4" />
                                        Download Records
                                    </Button>
                                )}
                            </div>
                        </div>
                        {/* Decorative elements */}
                        <div className="absolute -top-4 -right-4 w-24 h-24 bg-white/10 rounded-full"></div>
                        <div className="absolute -bottom-2 -left-2 w-16 h-16 bg-white/5 rounded-full"></div>
                    </div>

                    <Tabs defaultValue="records" className="space-y-4">
                        <TabsList className="bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm border border-slate-200 dark:border-slate-700">
                            <TabsTrigger value="records">Medical Records</TabsTrigger>
                            <TabsTrigger value="lab-results">Lab Results</TabsTrigger>
                            <TabsTrigger value="prescriptions">Prescriptions</TabsTrigger>
                        </TabsList>

                        <TabsContent value="records" className="space-y-4">
                            <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                                <CardHeader>
                                    <CardTitle className="text-lg font-semibold text-slate-900 dark:text-white">Medical Records</CardTitle>
                                    <CardDescription className="text-slate-600 dark:text-slate-300">
                                        Your complete medical history and visit records
                                    </CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <div className="space-y-4">
                                        {medicalRecords.map((record) => (
                                            <div key={record.id} className="flex items-center justify-between p-4 border border-slate-200 dark:border-slate-700 rounded-lg hover:shadow-md transition-all duration-200 bg-slate-50 dark:bg-slate-700/50">
                                                <div className="flex items-center space-x-4">
                                                    <div className="flex-shrink-0">
                                                        <div className="p-2 bg-rose-100 dark:bg-rose-900/20 rounded-full">
                                                            <FileText className="h-5 w-5 text-rose-600 dark:text-rose-400" />
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <h3 className="font-medium text-slate-900 dark:text-white">{record.type}</h3>
                                                        <p className="text-sm text-slate-600 dark:text-slate-400">{record.diagnosis}</p>
                                                        <p className="text-sm text-slate-600 dark:text-slate-400">{record.treatment}</p>
                                                    </div>
                                                </div>
                                                <div className="text-right">
                                                    <div className="text-sm font-medium text-slate-900 dark:text-white">{record.date}</div>
                                                    <div className="text-xs text-slate-600 dark:text-slate-400">{record.doctor}</div>
                                                    <Badge variant="default" className="mt-1 bg-rose-600 hover:bg-rose-700">{record.status}</Badge>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                </CardContent>
                            </Card>
                        </TabsContent>

                        <TabsContent value="lab-results" className="space-y-4">
                            <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                                <CardHeader>
                                    <CardTitle className="text-lg font-semibold text-slate-900 dark:text-white">Lab Results</CardTitle>
                                    <CardDescription className="text-slate-600 dark:text-slate-300">
                                        Your laboratory test results and analysis
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
                                                        <p className="text-sm text-slate-600 dark:text-slate-400">Results: {result.results}</p>
                                                    </div>
                                                </div>
                                                <div className="text-right">
                                                    <div className="text-sm font-medium text-slate-900 dark:text-white">{result.date}</div>
                                                    <Badge variant="default" className="mt-1 bg-rose-600 hover:bg-rose-700">{result.status}</Badge>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                </CardContent>
                            </Card>
                        </TabsContent>

                        <TabsContent value="prescriptions" className="space-y-4">
                            <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                                <CardHeader>
                                    <CardTitle className="text-lg font-semibold text-slate-900 dark:text-white">Prescriptions</CardTitle>
                                    <CardDescription className="text-slate-600 dark:text-slate-300">
                                        Your current and past medications
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
                                                        <p className="text-sm text-slate-600 dark:text-slate-400">Prescribed by: {prescription.prescribedBy}</p>
                                                    </div>
                                                </div>
                                                <div className="text-right">
                                                    <div className="text-sm font-medium text-slate-900 dark:text-white">{prescription.date}</div>
                                                    <Badge variant="default" className="mt-1 bg-rose-600 hover:bg-rose-700">{prescription.status}</Badge>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                </CardContent>
                            </Card>
                        </TabsContent>
                    </Tabs>
                </div>
            </div>
        </AppLayout>
    );
}
