import { Head } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '@/layouts/app-layout';
import { adminPatients } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import {
    User,
    Plus,
    Search,
    Edit,
    Eye,
    Phone,
    Mail,
    Calendar,
    MoreHorizontal,
    Heart,
    Clock,
    Activity
} from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Admin Dashboard',
        href: '/admin/dashboard',
    },
    {
        title: 'Patient Management',
        href: adminPatients(),
    },
];

export default function PatientManagement() {
    const [searchTerm, setSearchTerm] = useState('');
    const [statusFilter, setStatusFilter] = useState('all');
    const [genderFilter, setGenderFilter] = useState('all');

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
            totalVisits: 12,
            nextAppointment: '2024-01-20',
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
            totalVisits: 8,
            nextAppointment: '2024-01-25',
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
            totalVisits: 15,
            nextAppointment: '2024-01-18',
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
            totalVisits: 6,
            nextAppointment: null,
            insurance: 'Medicare'
        },
        {
            id: 5,
            name: 'David Wilson',
            email: 'david.wilson@email.com',
            phone: '+1 (555) 567-8901',
            age: 38,
            gender: 'Male',
            lastVisit: '2024-01-14',
            status: 'Active',
            totalVisits: 4,
            nextAppointment: '2024-01-22',
            insurance: 'United Health'
        }
    ];

    const filteredPatients = patients.filter(patient => {
        const matchesSearch = patient.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
                            patient.email.toLowerCase().includes(searchTerm.toLowerCase()) ||
                            patient.insurance.toLowerCase().includes(searchTerm.toLowerCase());
        const matchesStatus = statusFilter === 'all' || patient.status === statusFilter;
        const matchesGender = genderFilter === 'all' || patient.gender === genderFilter;

        return matchesSearch && matchesStatus && matchesGender;
    });

    const getStatusColor = (status: string) => {
        switch (status) {
            case 'Active': return 'default';
            case 'Inactive': return 'secondary';
            default: return 'secondary';
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Patient Management - Medinext">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&family=instrument-sans:400,500,600" rel="stylesheet" />
            </Head>

            <div className="min-h-screen bg-gradient-to-br from-slate-50 via-white to-blue-50 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900">
                <div className="space-y-6 p-6">

                    <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                        <CardHeader>
                            <div className="flex items-center justify-between">
                                <div>
                                    <CardTitle className="text-xl font-semibold text-slate-900 dark:text-white">Patient Directory</CardTitle>
                                    <CardDescription className="text-slate-600 dark:text-slate-300">
                                        View and manage all patients in your clinic
                                    </CardDescription>
                                </div>
                                <div className="flex space-x-3">
                                    <Button
                                        variant="outline"
                                        className="border-slate-300 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-700"
                                    >
                                        <Heart className="mr-2 h-4 w-4" />
                                        Health Records
                                    </Button>
                                    <Button
                                        className="bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white shadow-lg"
                                    >
                                        <Plus className="mr-2 h-4 w-4" />
                                        Add Patient
                                    </Button>
                                </div>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div className="flex items-center space-x-3 mb-6">
                                <div className="relative flex-1">
                                    <Search className="absolute left-3 top-3 h-4 w-4 text-slate-400" />
                                    <Input
                                        placeholder="Search patients..."
                                        className="pl-10 h-11 border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-700 dark:text-white"
                                        value={searchTerm}
                                        onChange={(e) => setSearchTerm(e.target.value)}
                                    />
                                </div>
                                <Select value={statusFilter} onValueChange={setStatusFilter}>
                                    <SelectTrigger className="w-[140px] h-11 border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500">
                                        <SelectValue placeholder="Status" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">All Status</SelectItem>
                                        <SelectItem value="Active">Active</SelectItem>
                                        <SelectItem value="Inactive">Inactive</SelectItem>
                                    </SelectContent>
                                </Select>
                                <Select value={genderFilter} onValueChange={setGenderFilter}>
                                    <SelectTrigger className="w-[140px] h-11 border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500">
                                        <SelectValue placeholder="Gender" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">All Gender</SelectItem>
                                        <SelectItem value="Male">Male</SelectItem>
                                        <SelectItem value="Female">Female</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <div className="rounded-lg border border-slate-200 dark:border-slate-700 overflow-hidden">
                                <Table>
                                    <TableHeader className="bg-slate-50 dark:bg-slate-800/50">
                                        <TableRow className="border-slate-200 dark:border-slate-700">
                                            <TableHead className="font-semibold text-slate-700 dark:text-slate-300">Patient</TableHead>
                                            <TableHead className="font-semibold text-slate-700 dark:text-slate-300">Contact</TableHead>
                                            <TableHead className="font-semibold text-slate-700 dark:text-slate-300">Age & Gender</TableHead>
                                            <TableHead className="font-semibold text-slate-700 dark:text-slate-300">Last Visit</TableHead>
                                            <TableHead className="font-semibold text-slate-700 dark:text-slate-300">Visits</TableHead>
                                            <TableHead className="font-semibold text-slate-700 dark:text-slate-300">Status</TableHead>
                                            <TableHead className="text-right font-semibold text-slate-700 dark:text-slate-300">Actions</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {filteredPatients.map((patient) => (
                                            <TableRow key={patient.id} className="border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors duration-200">
                                                <TableCell>
                                                    <div className="flex items-center space-x-3">
                                                        <div className="h-12 w-12 rounded-full bg-gradient-to-r from-blue-500 to-purple-500 flex items-center justify-center shadow-md">
                                                            <span className="text-sm font-bold text-white">
                                                                {patient.name.split(' ').map(n => n[0]).join('')}
                                                            </span>
                                                        </div>
                                                        <div>
                                                            <div className="font-semibold text-slate-900 dark:text-white">{patient.name}</div>
                                                            <div className="text-sm text-slate-500 dark:text-slate-400">ID: {patient.id}</div>
                                                        </div>
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    <div className="space-y-2">
                                                        <div className="flex items-center text-sm">
                                                            <Mail className="mr-2 h-4 w-4 text-slate-400" />
                                                            <span className="text-slate-700 dark:text-slate-300">{patient.email}</span>
                                                        </div>
                                                        <div className="flex items-center text-sm">
                                                            <Phone className="mr-2 h-4 w-4 text-slate-400" />
                                                            <span className="text-slate-500 dark:text-slate-400">{patient.phone}</span>
                                                        </div>
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    <div>
                                                        <div className="font-medium text-slate-900 dark:text-white">{patient.age} years</div>
                                                        <div className="text-sm text-slate-500 dark:text-slate-400">{patient.gender}</div>
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    <div className="flex items-center text-sm">
                                                        <Calendar className="mr-2 h-4 w-4 text-slate-400" />
                                                        <span className="text-slate-700 dark:text-slate-300">{patient.lastVisit}</span>
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    <div className="flex items-center">
                                                        <Activity className="mr-2 h-4 w-4 text-slate-400" />
                                                        <span className="font-medium text-slate-900 dark:text-white">{patient.totalVisits}</span>
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    <Badge
                                                        variant={getStatusColor(patient.status)}
                                                        className={`font-medium ${
                                                            patient.status === 'Active'
                                                                ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400'
                                                                : 'bg-slate-100 text-slate-800 dark:bg-slate-900/20 dark:text-slate-400'
                                                        }`}
                                                    >
                                                        {patient.status}
                                                    </Badge>
                                                </TableCell>
                                                <TableCell className="text-right">
                                                    <div className="flex items-center justify-end space-x-2">
                                                        <Button
                                                            variant="ghost"
                                                            size="sm"
                                                            title="View Details"
                                                            className="h-8 w-8 p-0 hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:text-blue-600 dark:hover:text-blue-400"
                                                        >
                                                            <Eye className="h-4 w-4" />
                                                        </Button>
                                                        <Button
                                                            variant="ghost"
                                                            size="sm"
                                                            title="Edit Patient"
                                                            className="h-8 w-8 p-0 hover:bg-green-50 dark:hover:bg-green-900/20 hover:text-green-600 dark:hover:text-green-400"
                                                        >
                                                            <Edit className="h-4 w-4" />
                                                        </Button>
                                                        <Button
                                                            variant="ghost"
                                                            size="sm"
                                                            title="More Options"
                                                            className="h-8 w-8 p-0 hover:bg-slate-50 dark:hover:bg-slate-700 hover:text-slate-600 dark:hover:text-slate-300"
                                                        >
                                                            <MoreHorizontal className="h-4 w-4" />
                                                        </Button>
                                                    </div>
                                                </TableCell>
                                            </TableRow>
                                        ))}
                                    </TableBody>
                                </Table>
                            </div>

                            {filteredPatients.length === 0 && (
                                <div className="text-center py-12">
                                    <div className="mx-auto w-16 h-16 bg-slate-100 dark:bg-slate-700 rounded-full flex items-center justify-center mb-4">
                                        <User className="h-8 w-8 text-slate-400" />
                                    </div>
                                    <h3 className="text-lg font-semibold text-slate-900 dark:text-white mb-2">No patients found</h3>
                                    <p className="text-slate-500 dark:text-slate-400 mb-4">
                                        Try adjusting your search or filter criteria.
                                    </p>
                                    <Button className="bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white">
                                        <Plus className="mr-2 h-4 w-4" />
                                        Add Patient
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
