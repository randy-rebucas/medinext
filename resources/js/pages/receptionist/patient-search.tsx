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
    Search,
    User,
    Phone,
    Mail,
    Calendar,
    Eye,
    Edit,
    Plus,
    Building2,
    Shield
} from 'lucide-react';
import { BreadcrumbItem } from '@/types';
import { receptionistPatientSearch } from '@/routes';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Receptionist Dashboard',
        href: '/receptionist/dashboard',
    },
    {
        title: 'Patient Search',
        href: receptionistPatientSearch(),
    },
];

interface PatientSearchProps {
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

export default function PatientSearch({
    user,
    permissions = []
}: PatientSearchProps) {
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
            status: 'Active',
            insurance: 'Blue Cross'
        },
        {
            id: 2,
            name: 'Jane Smith',
            email: 'jane.smith@email.com',
            phone: '+1 (555) 234-5678',
            age: 28,
            gender: 'Female',
            lastVisit: '2024-01-12',
            status: 'Active',
            insurance: 'Aetna'
        },
        {
            id: 3,
            name: 'Bob Johnson',
            email: 'bob.johnson@email.com',
            phone: '+1 (555) 345-6789',
            age: 45,
            gender: 'Male',
            lastVisit: '2024-01-08',
            status: 'Active',
            insurance: 'Cigna'
        },
        {
            id: 4,
            name: 'Alice Brown',
            email: 'alice.brown@email.com',
            phone: '+1 (555) 456-7890',
            age: 52,
            gender: 'Female',
            lastVisit: '2023-12-20',
            status: 'Inactive',
            insurance: 'Medicare'
        }
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Patient Search - Medinext">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&family=instrument-sans:400,500,600" rel="stylesheet" />
            </Head>
            <div className="min-h-screen bg-gradient-to-br from-slate-50 via-white to-blue-50 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900">
                <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
                    {/* Modern Header */}
                    <div className="relative overflow-hidden rounded-2xl bg-gradient-to-r from-purple-600 to-indigo-600 p-8 text-white shadow-xl">
                        <div className="absolute inset-0 bg-black/10"></div>
                        <div className="relative flex items-center justify-between">
                            <div>
                                <h1 className="text-3xl font-bold tracking-tight">Patient Search</h1>
                                <p className="mt-2 text-purple-100">
                                    {user?.clinic?.name || 'No Clinic'} • Search and manage patient information
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
                                {hasPermission('register_patients') && (
                                    <Button className="bg-white/20 hover:bg-white/30 text-white border-white/30 hover:border-white/40">
                                        <Plus className="mr-2 h-4 w-4" />
                                        Register New Patient
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
                                Search for patients by name, phone, email, or ID
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="flex items-center space-x-2 mb-4">
                                <div className="relative flex-1">
                                    <Search className="absolute left-2 top-2.5 h-4 w-4 text-slate-400 dark:text-slate-500" />
                                    <Input
                                        placeholder="Search patients by name, phone, email, or ID..."
                                        className="pl-8 border-slate-200 dark:border-slate-700 focus:border-purple-500 dark:focus:border-purple-400"
                                    />
                                </div>
                                <Button variant="outline" className="border-slate-200 dark:border-slate-700 hover:bg-purple-50 dark:hover:bg-purple-900/20 hover:border-purple-300 dark:hover:border-purple-600 transition-all duration-200">
                                    Advanced Search
                                </Button>
                            </div>

                            <Table>
                                <TableHeader>
                                    <TableRow className="border-slate-200 dark:border-slate-700">
                                        <TableHead className="text-slate-700 dark:text-slate-300">Patient</TableHead>
                                        <TableHead className="text-slate-700 dark:text-slate-300">Contact</TableHead>
                                        <TableHead className="text-slate-700 dark:text-slate-300">Age</TableHead>
                                        <TableHead className="text-slate-700 dark:text-slate-300">Gender</TableHead>
                                        <TableHead className="text-slate-700 dark:text-slate-300">Last Visit</TableHead>
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
                                                    <div className="p-1 bg-purple-100 dark:bg-purple-900/20 rounded-md mr-2">
                                                        <User className="h-4 w-4 text-purple-600 dark:text-purple-400" />
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
                                                <div className="flex items-center justify-end space-x-2">
                                                    <Button variant="ghost" size="sm" className="hover:bg-purple-100 dark:hover:bg-purple-900/20 hover:text-purple-600 dark:hover:text-purple-400 transition-all duration-200">
                                                        <Eye className="h-4 w-4" />
                                                    </Button>
                                                    <Button variant="ghost" size="sm" className="hover:bg-blue-100 dark:hover:bg-blue-900/20 hover:text-blue-600 dark:hover:text-blue-400 transition-all duration-200">
                                                        <Edit className="h-4 w-4" />
                                                    </Button>
                                                    <Button variant="ghost" size="sm" className="hover:bg-green-100 dark:hover:bg-green-900/20 hover:text-green-600 dark:hover:text-green-400 transition-all duration-200">
                                                        <Calendar className="h-4 w-4" />
                                                    </Button>
                                                </div>
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AppLayout>
    );
}
