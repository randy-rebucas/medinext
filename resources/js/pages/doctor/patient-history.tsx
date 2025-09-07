import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { doctorPatientHistory } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import {
    Search,
    Eye,
    Filter,
    User,
    FileText,
    TestTube,
    Pill,
    Stethoscope,
    Building2,
    Shield
} from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Doctor Dashboard',
        href: '/doctor/dashboard',
    },
    {
        title: 'Patient History',
        href: doctorPatientHistory(),
    },
];

interface PatientHistoryProps {
    user?: {
        id: number;
        name: string;
        email: string;
        role: string;
        clinic_id?: number;
        clinic?: {
            id: number;
            name: string;
        };
    };
    permissions?: string[];
}

export default function PatientHistory({ user, permissions = [] }: PatientHistoryProps) {
    const hasPermission = (permission: string) => permissions.includes(permission);
    const patients = [
        {
            id: 1,
            name: 'John Doe',
            age: 35,
            gender: 'Male',
            lastVisit: '2024-01-10',
            totalVisits: 12,
            conditions: ['Hypertension', 'Diabetes Type 2']
        },
        {
            id: 2,
            name: 'Jane Smith',
            age: 28,
            gender: 'Female',
            lastVisit: '2024-01-12',
            totalVisits: 8,
            conditions: ['Asthma', 'Allergies']
        },
        {
            id: 3,
            name: 'Bob Johnson',
            age: 45,
            gender: 'Male',
            lastVisit: '2024-01-08',
            totalVisits: 15,
            conditions: ['High Cholesterol', 'Arthritis']
        }
    ];

    const medicalHistory = [
        {
            id: 1,
            patient: 'John Doe',
            date: '2024-01-10',
            type: 'Consultation',
            diagnosis: 'Hypertension follow-up',
            treatment: 'Medication adjustment',
            doctor: 'Dr. Sarah Johnson'
        },
        {
            id: 2,
            patient: 'John Doe',
            date: '2023-12-15',
            type: 'Lab Test',
            diagnosis: 'Blood pressure monitoring',
            treatment: 'Lisinopril 10mg daily',
            doctor: 'Dr. Sarah Johnson'
        },
        {
            id: 3,
            patient: 'Jane Smith',
            date: '2024-01-12',
            type: 'Follow-up',
            diagnosis: 'Asthma management',
            treatment: 'Inhaler adjustment',
            doctor: 'Dr. Michael Brown'
        }
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Patient History - Medinext">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&family=instrument-sans:400,500,600" rel="stylesheet" />
            </Head>
            <div className="min-h-screen bg-gradient-to-br from-slate-50 via-white to-blue-50 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900">
                <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
                    {/* Modern Header */}
                    <div className="relative overflow-hidden rounded-2xl bg-gradient-to-r from-blue-600 to-purple-600 p-8 text-white shadow-xl">
                        <div className="absolute inset-0 bg-black/10"></div>
                        <div className="relative flex items-center justify-between">
                            <div>
                                <h1 className="text-3xl font-bold tracking-tight">Patient History</h1>
                                <p className="mt-2 text-blue-100">
                                    {user?.clinic?.name || 'No Clinic'} • View comprehensive patient medical history and records
                                </p>
                            </div>
                            <div className="flex items-center gap-3">
                                <Badge variant="secondary" className="flex items-center gap-1 bg-white/20 text-white border-white/30 hover:bg-white/30">
                                    <Shield className="h-3 w-3" />
                                    Doctor
                                </Badge>
                                {user?.clinic && (
                                    <Badge variant="secondary" className="flex items-center gap-1 bg-white/20 text-white border-white/30 hover:bg-white/30">
                                        <Building2 className="h-3 w-3" />
                                        {user.clinic.name}
                                    </Badge>
                                )}
                                {hasPermission('search_patients') && (
                                    <Button className="bg-white/20 hover:bg-white/30 text-white border-white/30 hover:border-white/40">
                                        <Search className="mr-2 h-4 w-4" />
                                        Search Patient
                                    </Button>
                                )}
                            </div>
                        </div>
                        {/* Decorative elements */}
                        <div className="absolute -top-4 -right-4 w-24 h-24 bg-white/10 rounded-full"></div>
                        <div className="absolute -bottom-2 -left-2 w-16 h-16 bg-white/5 rounded-full"></div>
                    </div>

                <Tabs defaultValue="patients" className="space-y-4">
                    <TabsList>
                        <TabsTrigger value="patients">Patient List</TabsTrigger>
                        <TabsTrigger value="history">Medical History</TabsTrigger>
                        <TabsTrigger value="records">Medical Records</TabsTrigger>
                    </TabsList>

                    <TabsContent value="patients" className="space-y-4">
                        <Card>
                            <CardHeader>
                                <CardTitle>Patient Directory</CardTitle>
                                <CardDescription>
                                    Select a patient to view their complete medical history
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div className="flex items-center space-x-2 mb-4">
                                    <div className="relative flex-1">
                                        <Search className="absolute left-2 top-2.5 h-4 w-4 text-muted-foreground" />
                                        <Input placeholder="Search patients..." className="pl-8" />
                                    </div>
                                    <Button variant="outline">
                                        <Filter className="mr-2 h-4 w-4" />
                                        Filter
                                    </Button>
                                </div>

                                <div className="space-y-4">
                                    {patients.map((patient) => (
                                        <div key={patient.id} className="flex items-center justify-between p-4 border rounded-lg">
                                            <div className="flex items-center space-x-4">
                                                <div className="flex-shrink-0">
                                                    <User className="h-8 w-8 text-muted-foreground" />
                                                </div>
                                                <div>
                                                    <h3 className="font-medium">{patient.name}</h3>
                                                    <p className="text-sm text-muted-foreground">
                                                        {patient.age} years old, {patient.gender}
                                                    </p>
                                                    <div className="flex items-center space-x-4 mt-1">
                                                        <span className="text-xs text-muted-foreground">
                                                            Last visit: {patient.lastVisit}
                                                        </span>
                                                        <span className="text-xs text-muted-foreground">
                                                            Total visits: {patient.totalVisits}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div className="flex items-center space-x-2">
                                                <div className="text-right">
                                                    <div className="text-sm font-medium">Conditions</div>
                                                    <div className="flex space-x-1">
                                                        {patient.conditions.map((condition) => (
                                                            <Badge key={condition} variant="outline" className="text-xs">
                                                                {condition}
                                                            </Badge>
                                                        ))}
                                                    </div>
                                                </div>
                                                <Button variant="outline" size="sm">
                                                    <Eye className="h-4 w-4" />
                                                </Button>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </CardContent>
                        </Card>
                    </TabsContent>

                    <TabsContent value="history" className="space-y-4">
                        <Card>
                            <CardHeader>
                                <CardTitle>Medical History</CardTitle>
                                <CardDescription>
                                    Complete medical history and treatment records
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-4">
                                    {medicalHistory.map((record) => (
                                        <div key={record.id} className="flex items-center justify-between p-4 border rounded-lg">
                                            <div className="flex items-center space-x-4">
                                                <div className="flex-shrink-0">
                                                    {record.type === 'Consultation' && <Stethoscope className="h-5 w-5 text-blue-600" />}
                                                    {record.type === 'Lab Test' && <TestTube className="h-5 w-5 text-green-600" />}
                                                    {record.type === 'Follow-up' && <FileText className="h-5 w-5 text-purple-600" />}
                                                </div>
                                                <div>
                                                    <h3 className="font-medium">{record.patient}</h3>
                                                    <p className="text-sm text-muted-foreground">{record.diagnosis}</p>
                                                    <p className="text-sm text-muted-foreground">{record.treatment}</p>
                                                </div>
                                            </div>
                                            <div className="text-right">
                                                <div className="text-sm font-medium">{record.date}</div>
                                                <div className="text-xs text-muted-foreground">{record.doctor}</div>
                                                <Badge variant="outline" className="mt-1">{record.type}</Badge>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </CardContent>
                        </Card>
                    </TabsContent>

                    <TabsContent value="records" className="space-y-4">
                        <Card>
                            <CardHeader>
                                <CardTitle>Medical Records</CardTitle>
                                <CardDescription>
                                    Access to all patient medical records and documents
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                                    <div className="p-4 border rounded-lg">
                                        <div className="flex items-center space-x-2 mb-2">
                                            <FileText className="h-5 w-5 text-blue-600" />
                                            <h3 className="font-medium">Medical Reports</h3>
                                        </div>
                                        <p className="text-sm text-muted-foreground mb-2">
                                            View and download medical reports
                                        </p>
                                        <Button variant="outline" size="sm">
                                            <Eye className="mr-2 h-4 w-4" />
                                            View
                                        </Button>
                                    </div>
                                    <div className="p-4 border rounded-lg">
                                        <div className="flex items-center space-x-2 mb-2">
                                            <TestTube className="h-5 w-5 text-green-600" />
                                            <h3 className="font-medium">Lab Results</h3>
                                        </div>
                                        <p className="text-sm text-muted-foreground mb-2">
                                            Laboratory test results and analysis
                                        </p>
                                        <Button variant="outline" size="sm">
                                            <Eye className="mr-2 h-4 w-4" />
                                            View
                                        </Button>
                                    </div>
                                    <div className="p-4 border rounded-lg">
                                        <div className="flex items-center space-x-2 mb-2">
                                            <Pill className="h-5 w-5 text-purple-600" />
                                            <h3 className="font-medium">Prescriptions</h3>
                                        </div>
                                        <p className="text-sm text-muted-foreground mb-2">
                                            Medication history and prescriptions
                                        </p>
                                        <Button variant="outline" size="sm">
                                            <Eye className="mr-2 h-4 w-4" />
                                            View
                                        </Button>
                                    </div>
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
