import { Head } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '@/layouts/app-layout';
import { adminDoctors } from '@/routes';
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
    Stethoscope,
    Plus,
    Search,
    Edit,
    Eye,
    Calendar,
    Users,
    MoreHorizontal,
    Mail,
    Phone
} from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Admin Dashboard',
        href: '/admin/dashboard',
    },
    {
        title: 'Doctor Management',
        href: adminDoctors(),
    },
];

export default function DoctorManagement() {
    const [searchTerm, setSearchTerm] = useState('');
    const [specializationFilter, setSpecializationFilter] = useState('all');
    const [statusFilter, setStatusFilter] = useState('all');

    const doctors = [
        {
            id: 1,
            name: 'Dr. Sarah Johnson',
            email: 'sarah.johnson@clinic.com',
            phone: '+1 (555) 123-4567',
            specialization: 'Cardiology',
            license: 'MD12345',
            status: 'Active',
            patients: 156,
            nextAppointment: '2024-01-15 09:00',
            experience: '8 years',
            rating: 4.9
        },
        {
            id: 2,
            name: 'Dr. Michael Brown',
            email: 'michael.brown@clinic.com',
            phone: '+1 (555) 234-5678',
            specialization: 'Pediatrics',
            license: 'MD67890',
            status: 'Active',
            patients: 203,
            nextAppointment: '2024-01-15 10:30',
            experience: '12 years',
            rating: 4.8
        },
        {
            id: 3,
            name: 'Dr. Emily Davis',
            email: 'emily.davis@clinic.com',
            phone: '+1 (555) 345-6789',
            specialization: 'Dermatology',
            license: 'MD11111',
            status: 'On Leave',
            patients: 89,
            nextAppointment: '2024-01-20 14:00',
            experience: '6 years',
            rating: 4.7
        },
        {
            id: 4,
            name: 'Dr. James Wilson',
            email: 'james.wilson@clinic.com',
            phone: '+1 (555) 456-7890',
            specialization: 'Orthopedics',
            license: 'MD22222',
            status: 'Active',
            patients: 134,
            nextAppointment: '2024-01-15 11:15',
            experience: '10 years',
            rating: 4.9
        },
        {
            id: 5,
            name: 'Dr. Jennifer Lee',
            email: 'jennifer.lee@clinic.com',
            phone: '+1 (555) 567-8901',
            specialization: 'Neurology',
            license: 'MD33333',
            status: 'Active',
            patients: 98,
            nextAppointment: '2024-01-15 15:30',
            experience: '15 years',
            rating: 4.8
        }
    ];

    const filteredDoctors = doctors.filter(doctor => {
        const matchesSearch = doctor.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
                            doctor.email.toLowerCase().includes(searchTerm.toLowerCase()) ||
                            doctor.specialization.toLowerCase().includes(searchTerm.toLowerCase());
        const matchesSpecialization = specializationFilter === 'all' || doctor.specialization === specializationFilter;
        const matchesStatus = statusFilter === 'all' || doctor.status === statusFilter;

        return matchesSearch && matchesSpecialization && matchesStatus;
    });

    const getStatusColor = (status: string) => {
        switch (status) {
            case 'Active': return 'default';
            case 'On Leave': return 'secondary';
            case 'Inactive': return 'destructive';
            default: return 'secondary';
        }
    };

    const getSpecializationColor = (specialization: string) => {
        const colors = {
            'Cardiology': 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400',
            'Pediatrics': 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400',
            'Dermatology': 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400',
            'Orthopedics': 'bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-400',
            'Neurology': 'bg-orange-100 text-orange-800 dark:bg-orange-900/20 dark:text-orange-400'
        };
        return colors[specialization as keyof typeof colors] || 'bg-slate-100 text-slate-800 dark:bg-slate-900/20 dark:text-slate-400';
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Doctor Management - Medinext">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&family=instrument-sans:400,500,600" rel="stylesheet" />
            </Head>

            <div className="min-h-screen bg-gradient-to-br from-slate-50 via-white to-blue-50 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900">
                <div className="space-y-6 p-6">
                    <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                        <CardHeader>
                            <div className="flex items-center justify-between">
                                <div>
                                    <CardTitle className="text-xl font-semibold text-slate-900 dark:text-white">Doctor Directory</CardTitle>
                                    <CardDescription className="text-slate-600 dark:text-slate-300">
                                        View and manage all doctors in your clinic
                                    </CardDescription>
                                </div>
                                <div className="flex space-x-3">
                                    <Button
                                        variant="outline"
                                        className="border-slate-300 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-700"
                                    >
                                        <Stethoscope className="mr-2 h-4 w-4" />
                                        View Schedules
                                    </Button>
                                    <Button
                                        className="bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white shadow-lg"
                                    >
                                        <Plus className="mr-2 h-4 w-4" />
                                        Add Doctor
                                    </Button>
                                </div>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div className="flex items-center space-x-3 mb-6">
                                <div className="relative flex-1">
                                    <Search className="absolute left-3 top-3 h-4 w-4 text-slate-400" />
                                    <Input
                                        placeholder="Search doctors..."
                                        className="pl-10 h-11 border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-700 dark:text-white"
                                        value={searchTerm}
                                        onChange={(e) => setSearchTerm(e.target.value)}
                                    />
                                </div>
                                <Select value={specializationFilter} onValueChange={setSpecializationFilter}>
                                    <SelectTrigger className="w-[180px] h-11 border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500">
                                        <SelectValue placeholder="Specialization" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">All Specializations</SelectItem>
                                        <SelectItem value="Cardiology">Cardiology</SelectItem>
                                        <SelectItem value="Pediatrics">Pediatrics</SelectItem>
                                        <SelectItem value="Dermatology">Dermatology</SelectItem>
                                        <SelectItem value="Orthopedics">Orthopedics</SelectItem>
                                        <SelectItem value="Neurology">Neurology</SelectItem>
                                    </SelectContent>
                                </Select>
                                <Select value={statusFilter} onValueChange={setStatusFilter}>
                                    <SelectTrigger className="w-[140px] h-11 border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500">
                                        <SelectValue placeholder="Status" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">All Status</SelectItem>
                                        <SelectItem value="Active">Active</SelectItem>
                                        <SelectItem value="On Leave">On Leave</SelectItem>
                                        <SelectItem value="Inactive">Inactive</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <div className="rounded-lg border border-slate-200 dark:border-slate-700 overflow-hidden">
                                <Table>
                                    <TableHeader className="bg-slate-50 dark:bg-slate-800/50">
                                        <TableRow className="border-slate-200 dark:border-slate-700">
                                            <TableHead className="font-semibold text-slate-700 dark:text-slate-300">Doctor</TableHead>
                                            <TableHead className="font-semibold text-slate-700 dark:text-slate-300">Specialization</TableHead>
                                            <TableHead className="font-semibold text-slate-700 dark:text-slate-300">Contact</TableHead>
                                            <TableHead className="font-semibold text-slate-700 dark:text-slate-300">Patients</TableHead>
                                            <TableHead className="font-semibold text-slate-700 dark:text-slate-300">Status</TableHead>
                                            <TableHead className="font-semibold text-slate-700 dark:text-slate-300">Next Appointment</TableHead>
                                            <TableHead className="text-right font-semibold text-slate-700 dark:text-slate-300">Actions</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {filteredDoctors.map((doctor) => (
                                            <TableRow key={doctor.id} className="border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors duration-200">
                                                <TableCell>
                                                    <div className="flex items-center space-x-3">
                                                        <div className="h-12 w-12 rounded-full bg-gradient-to-r from-blue-500 to-purple-500 flex items-center justify-center shadow-md">
                                                            <span className="text-sm font-bold text-white">
                                                                {doctor.name.split(' ').map(n => n[0]).join('')}
                                                            </span>
                                                        </div>
                                                        <div>
                                                            <div className="font-semibold text-slate-900 dark:text-white">{doctor.name}</div>
                                                            <div className="text-sm text-slate-500 dark:text-slate-400">License: {doctor.license}</div>
                                                            <div className="text-xs text-slate-400 dark:text-slate-500">{doctor.experience} • ⭐ {doctor.rating}</div>
                                                        </div>
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    <Badge className={`font-medium ${getSpecializationColor(doctor.specialization)}`}>
                                                        {doctor.specialization}
                                                    </Badge>
                                                </TableCell>
                                                <TableCell>
                                                    <div className="space-y-1">
                                                        <div className="flex items-center text-sm">
                                                            <Mail className="mr-2 h-3 w-3 text-slate-400" />
                                                            <span className="text-slate-700 dark:text-slate-300">{doctor.email}</span>
                                                        </div>
                                                        <div className="flex items-center text-sm">
                                                            <Phone className="mr-2 h-3 w-3 text-slate-400" />
                                                            <span className="text-slate-500 dark:text-slate-400">{doctor.phone}</span>
                                                        </div>
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    <div className="flex items-center">
                                                        <Users className="mr-2 h-4 w-4 text-slate-400" />
                                                        <span className="font-medium text-slate-900 dark:text-white">{doctor.patients}</span>
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    <Badge
                                                        variant={getStatusColor(doctor.status)}
                                                        className={`font-medium ${
                                                            doctor.status === 'Active'
                                                                ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400'
                                                                : doctor.status === 'On Leave'
                                                                ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400'
                                                                : 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400'
                                                        }`}
                                                    >
                                                        {doctor.status}
                                                    </Badge>
                                                </TableCell>
                                                <TableCell>
                                                    <div className="flex items-center text-sm">
                                                        <Calendar className="mr-2 h-4 w-4 text-slate-400" />
                                                        <span className="text-slate-700 dark:text-slate-300">{doctor.nextAppointment}</span>
                                                    </div>
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
                                                            title="Edit Doctor"
                                                            className="h-8 w-8 p-0 hover:bg-green-50 dark:hover:bg-green-900/20 hover:text-green-600 dark:hover:text-green-400"
                                                        >
                                                            <Edit className="h-4 w-4" />
                                                        </Button>
                                                        <Button
                                                            variant="ghost"
                                                            size="sm"
                                                            title="View Schedule"
                                                            className="h-8 w-8 p-0 hover:bg-purple-50 dark:hover:bg-purple-900/20 hover:text-purple-600 dark:hover:text-purple-400"
                                                        >
                                                            <Calendar className="h-4 w-4" />
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

                            {filteredDoctors.length === 0 && (
                                <div className="text-center py-12">
                                    <div className="mx-auto w-16 h-16 bg-slate-100 dark:bg-slate-700 rounded-full flex items-center justify-center mb-4">
                                        <Stethoscope className="h-8 w-8 text-slate-400" />
                                    </div>
                                    <h3 className="text-lg font-semibold text-slate-900 dark:text-white mb-2">No doctors found</h3>
                                    <p className="text-slate-500 dark:text-slate-400 mb-4">
                                        Try adjusting your search or filter criteria.
                                    </p>
                                    <Button className="bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white">
                                        <Plus className="mr-2 h-4 w-4" />
                                        Add Doctor
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
