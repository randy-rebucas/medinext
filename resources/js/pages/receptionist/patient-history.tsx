import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import {
    Eye,
    Search,
    User,
    Calendar,
    Stethoscope,
    FileText,
    Phone,
    Mail,
    Building2,
    Shield
} from 'lucide-react';
import { receptionistPatientHistory } from '@/routes';
import { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Receptionist Dashboard',
        href: '/receptionist/dashboard',
    },
    {
        title: 'Patient History',
        href: receptionistPatientHistory(),
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

export default function PatientHistory({
    user,
    permissions = []
}: PatientHistoryProps) {
    const hasPermission = (permission: string) => permissions.includes(permission);
    const patients = [
        {
            id: 1,
            name: 'John Doe',
            email: 'john.doe@email.com',
            phone: '+1 (555) 123-4567',
            age: 35,
            gender: 'Male',
            lastVisit: '2024-01-10',
            totalVisits: 12,
            insurance: 'Blue Cross',
            status: 'Active'
        },
        {
            id: 2,
            name: 'Jane Smith',
            email: 'jane.smith@email.com',
            phone: '+1 (555) 234-5678',
            age: 28,
            gender: 'Female',
            lastVisit: '2024-01-12',
            totalVisits: 8,
            insurance: 'Aetna',
            status: 'Active'
        },
        {
            id: 3,
            name: 'Bob Johnson',
            email: 'bob.johnson@email.com',
            phone: '+1 (555) 345-6789',
            age: 45,
            gender: 'Male',
            lastVisit: '2024-01-08',
            totalVisits: 15,
            insurance: 'Cigna',
            status: 'Active'
        }
    ];

    const visitHistory = [
        {
            id: 1,
            patient: 'John Doe',
            date: '2024-01-10',
            doctor: 'Dr. Sarah Johnson',
            type: 'Consultation',
            diagnosis: 'Hypertension follow-up',
            status: 'Completed'
        },
        {
            id: 2,
            patient: 'John Doe',
            date: '2023-12-15',
            doctor: 'Dr. Sarah Johnson',
            type: 'Follow-up',
            diagnosis: 'Blood pressure monitoring',
            status: 'Completed'
        },
        {
            id: 3,
            patient: 'Jane Smith',
            date: '2024-01-12',
            doctor: 'Dr. Michael Brown',
            type: 'Check-up',
            diagnosis: 'Annual physical',
            status: 'Completed'
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
                    <div className="relative overflow-hidden rounded-2xl bg-gradient-to-r from-teal-600 to-cyan-600 p-8 text-white shadow-xl">
                        <div className="absolute inset-0 bg-black/10"></div>
                        <div className="relative flex items-center justify-between">
                            <div>
                                <h1 className="text-3xl font-bold tracking-tight">Patient History</h1>
                                <p className="mt-2 text-teal-100">
                                    {user?.clinic?.name || 'No Clinic'} • View patient history and visit records
                                </p>
                            </div>
                            <div className="flex items-center gap-3">
                                <Badge variant="secondary" className="flex items-center gap-1 bg-white/20 text-white border-white/30 hover:bg-white/30">
                                    <Shield className="h-3 w-3" />
                                    Receptionist
                                </Badge>
                                {user?.clinic && (
                                    <Badge variant="secondary" className="flex items-center gap-1 bg-white/20 text-white border-white/30 hover:bg-white/30">
                                        <Building2 className="h-3 w-3" />
                                        {user.clinic.name}
                                    </Badge>
                                )}
                                {hasPermission('view_patients') && (
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

                    <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                        <CardHeader>
                            <CardTitle className="text-lg font-semibold text-slate-900 dark:text-white">Patient Directory</CardTitle>
                            <CardDescription className="text-slate-600 dark:text-slate-300">
                                Search and view patient history and records
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="flex items-center space-x-2 mb-4">
                                <div className="relative flex-1">
                                    <Search className="absolute left-2 top-2.5 h-4 w-4 text-slate-400 dark:text-slate-500" />
                                    <Input placeholder="Search patients..." className="pl-8 border-slate-200 dark:border-slate-700 focus:border-teal-500 dark:focus:border-teal-400" />
                                </div>
                            </div>

                            <Table>
                                <TableHeader>
                                    <TableRow className="border-slate-200 dark:border-slate-700">
                                        <TableHead className="text-slate-700 dark:text-slate-300">Patient</TableHead>
                                        <TableHead className="text-slate-700 dark:text-slate-300">Contact</TableHead>
                                        <TableHead className="text-slate-700 dark:text-slate-300">Age</TableHead>
                                        <TableHead className="text-slate-700 dark:text-slate-300">Gender</TableHead>
                                        <TableHead className="text-slate-700 dark:text-slate-300">Last Visit</TableHead>
                                        <TableHead className="text-slate-700 dark:text-slate-300">Total Visits</TableHead>
                                        <TableHead className="text-slate-700 dark:text-slate-300">Insurance</TableHead>
                                        <TableHead className="text-slate-700 dark:text-slate-300">Status</TableHead>
                                        <TableHead className="text-right text-slate-700 dark:text-slate-300">Actions</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {patients.map((patient) => (
                                        <TableRow key={patient.id} className="border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                                            <TableCell>
                                                <div className="flex items-center">
                                                    <div className="p-1 bg-teal-100 dark:bg-teal-900/20 rounded-md mr-2">
                                                        <User className="h-4 w-4 text-teal-600 dark:text-teal-400" />
                                                    </div>
                                                    <div>
                                                        <div className="font-medium text-slate-900 dark:text-white">{patient.name}</div>
                                                        <div className="text-sm text-slate-600 dark:text-slate-400">ID: {patient.id}</div>
                                                    </div>
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <div className="space-y-1">
                                                    <div className="flex items-center text-sm text-slate-600 dark:text-slate-400">
                                                        <Mail className="mr-1 h-3 w-3" />
                                                        {patient.email}
                                                    </div>
                                                    <div className="flex items-center text-sm text-slate-600 dark:text-slate-400">
                                                        <Phone className="mr-1 h-3 w-3" />
                                                        {patient.phone}
                                                    </div>
                                                </div>
                                            </TableCell>
                                            <TableCell className="text-slate-900 dark:text-white">{patient.age}</TableCell>
                                            <TableCell className="text-slate-900 dark:text-white">{patient.gender}</TableCell>
                                            <TableCell>
                                                <div className="flex items-center text-sm text-slate-600 dark:text-slate-400">
                                                    <Calendar className="mr-1 h-3 w-3" />
                                                    {patient.lastVisit}
                                                </div>
                                            </TableCell>
                                            <TableCell className="text-slate-900 dark:text-white">{patient.totalVisits}</TableCell>
                                            <TableCell>
                                                <Badge variant="outline" className="border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300">
                                                    {patient.insurance}
                                                </Badge>
                                            </TableCell>
                                            <TableCell>
                                                <Badge variant={patient.status === 'Active' ? 'default' : 'secondary'} className="border-0">
                                                    {patient.status}
                                                </Badge>
                                            </TableCell>
                                            <TableCell className="text-right">
                                                <Button variant="ghost" size="sm" className="hover:bg-teal-100 dark:hover:bg-teal-900/20 hover:text-teal-600 dark:hover:text-teal-400 transition-all duration-200">
                                                    <Eye className="h-4 w-4" />
                                                </Button>
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        </CardContent>
                    </Card>

                    <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                        <CardHeader>
                            <CardTitle className="text-lg font-semibold text-slate-900 dark:text-white">Recent Visit History</CardTitle>
                            <CardDescription className="text-slate-600 dark:text-slate-300">
                                Latest patient visits and consultations
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                {visitHistory.map((visit) => (
                                    <div key={visit.id} className="flex items-center justify-between p-4 border border-slate-200 dark:border-slate-700 rounded-lg hover:shadow-md transition-all duration-200 bg-slate-50 dark:bg-slate-700/50">
                                        <div className="flex items-center space-x-4">
                                            <div className="flex-shrink-0">
                                                <div className="p-1 bg-teal-100 dark:bg-teal-900/20 rounded-md">
                                                    <FileText className="h-5 w-5 text-teal-600 dark:text-teal-400" />
                                                </div>
                                            </div>
                                            <div>
                                                <h3 className="font-medium text-slate-900 dark:text-white">{visit.patient}</h3>
                                                <p className="text-sm text-slate-600 dark:text-slate-400">{visit.diagnosis}</p>
                                                <div className="flex items-center space-x-4 mt-1">
                                                    <span className="text-xs text-slate-500 dark:text-slate-500 flex items-center">
                                                        <Calendar className="mr-1 h-3 w-3" />
                                                        {visit.date}
                                                    </span>
                                                    <span className="text-xs text-slate-500 dark:text-slate-500 flex items-center">
                                                        <Stethoscope className="mr-1 h-3 w-3" />
                                                        {visit.doctor}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div className="flex items-center space-x-2">
                                            <Badge variant="outline" className="border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300">{visit.type}</Badge>
                                            <Badge variant="default" className="border-0">{visit.status}</Badge>
                                            <Button variant="outline" size="sm" className="border-slate-200 dark:border-slate-700 hover:bg-teal-50 dark:hover:bg-teal-900/20 hover:border-teal-300 dark:hover:border-teal-600 transition-all duration-200">
                                                <Eye className="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AppLayout>
    );
}
