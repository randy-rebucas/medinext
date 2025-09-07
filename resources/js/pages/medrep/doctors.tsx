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
    Users,
    Search,
    Eye,
    Edit,
    Filter,
    Calendar,
    MessageSquare,
    Star,
    Building2,
    Shield
} from 'lucide-react';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Doctor Management',
        href: '/medrep/doctors',
    },
    {
        title: 'Doctor Management',
        href: '/medrep/doctors',
    },
];

interface DoctorManagementProps {
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

export default function DoctorManagement({ user }: DoctorManagementProps = {}) {
    const doctors = [
        {
            id: 1,
            name: 'Dr. Sarah Johnson',
            specialty: 'Cardiology',
            clinic: 'Heart Care Clinic',
            email: 'sarah.johnson@heartcare.com',
            phone: '+1 (555) 123-4567',
            lastInteraction: '2024-01-15',
            totalInteractions: 12,
            rating: 4.9,
            status: 'Active'
        },
        {
            id: 2,
            name: 'Dr. Michael Brown',
            specialty: 'Pediatrics',
            clinic: 'Children\'s Medical Center',
            email: 'michael.brown@childrensmed.com',
            phone: '+1 (555) 234-5678',
            lastInteraction: '2024-01-14',
            totalInteractions: 8,
            rating: 4.8,
            status: 'Active'
        },
        {
            id: 3,
            name: 'Dr. Emily Davis',
            specialty: 'Dermatology',
            clinic: 'Skin Health Clinic',
            email: 'emily.davis@skinhealth.com',
            phone: '+1 (555) 345-6789',
            lastInteraction: '2024-01-13',
            totalInteractions: 15,
            rating: 4.7,
            status: 'Active'
        },
        {
            id: 4,
            name: 'Dr. James Wilson',
            specialty: 'Orthopedics',
            clinic: 'Bone & Joint Center',
            email: 'james.wilson@bonejoint.com',
            phone: '+1 (555) 456-7890',
            lastInteraction: '2024-01-12',
            totalInteractions: 5,
            rating: 4.6,
            status: 'Active'
        }
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Doctor Management - Medinext">
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
                                    Doctor Management
                                </h1>
                                <p className="mt-2 text-emerald-100">
                                    Manage your relationships with healthcare professionals
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
                            <Users className="mr-2 h-4 w-4" />
                            Add Doctor
                        </Button>
                    </div>

                    <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                        <CardHeader>
                            <CardTitle className="text-lg font-semibold text-slate-900 dark:text-white">Doctor Directory</CardTitle>
                            <CardDescription className="text-slate-600 dark:text-slate-300">
                                View and manage all doctors in your network
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="flex items-center space-x-2 mb-4">
                                <div className="relative flex-1">
                                    <Search className="absolute left-2 top-2.5 h-4 w-4 text-slate-400" />
                                    <Input placeholder="Search doctors..." className="pl-8 border-slate-200 dark:border-slate-700" />
                                </div>
                                <Button variant="outline" className="border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800">
                                    <Filter className="mr-2 h-4 w-4" />
                                    Filter
                                </Button>
                            </div>

                            <Table>
                                <TableHeader>
                                    <TableRow className="border-slate-200 dark:border-slate-700">
                                        <TableHead className="text-slate-700 dark:text-slate-300">Doctor</TableHead>
                                        <TableHead className="text-slate-700 dark:text-slate-300">Specialty</TableHead>
                                        <TableHead className="text-slate-700 dark:text-slate-300">Clinic</TableHead>
                                        <TableHead className="text-slate-700 dark:text-slate-300">Contact</TableHead>
                                        <TableHead className="text-slate-700 dark:text-slate-300">Last Interaction</TableHead>
                                        <TableHead className="text-slate-700 dark:text-slate-300">Interactions</TableHead>
                                        <TableHead className="text-slate-700 dark:text-slate-300">Rating</TableHead>
                                        <TableHead className="text-slate-700 dark:text-slate-300">Status</TableHead>
                                        <TableHead className="text-right text-slate-700 dark:text-slate-300">Actions</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {doctors.map((doctor) => (
                                        <TableRow key={doctor.id} className="border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/50">
                                            <TableCell>
                                                <div>
                                                    <div className="font-medium text-slate-900 dark:text-white">{doctor.name}</div>
                                                    <div className="text-sm text-slate-500 dark:text-slate-400">ID: {doctor.id}</div>
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <Badge variant="outline" className="border-slate-200 dark:border-slate-700">{doctor.specialty}</Badge>
                                            </TableCell>
                                            <TableCell className="text-slate-900 dark:text-white">{doctor.clinic}</TableCell>
                                            <TableCell>
                                                <div className="space-y-1">
                                                    <div className="text-sm text-slate-900 dark:text-white">{doctor.email}</div>
                                                    <div className="text-sm text-slate-500 dark:text-slate-400">{doctor.phone}</div>
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <div className="flex items-center text-sm text-slate-600 dark:text-slate-400">
                                                    <Calendar className="mr-1 h-3 w-3" />
                                                    {doctor.lastInteraction}
                                                </div>
                                            </TableCell>
                                            <TableCell className="text-slate-900 dark:text-white">{doctor.totalInteractions}</TableCell>
                                            <TableCell>
                                                <div className="flex items-center text-slate-900 dark:text-white">
                                                    <Star className="mr-1 h-3 w-3 text-yellow-500" />
                                                    {doctor.rating}
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <Badge className="bg-emerald-100 text-emerald-800 border-0">{doctor.status}</Badge>
                                            </TableCell>
                                            <TableCell className="text-right">
                                                <div className="flex items-center justify-end space-x-2">
                                                    <Button variant="ghost" size="sm" className="hover:bg-slate-100 dark:hover:bg-slate-700">
                                                        <Eye className="h-4 w-4" />
                                                    </Button>
                                                    <Button variant="ghost" size="sm" className="hover:bg-slate-100 dark:hover:bg-slate-700">
                                                        <Edit className="h-4 w-4" />
                                                    </Button>
                                                    <Button variant="ghost" size="sm" className="hover:bg-slate-100 dark:hover:bg-slate-700">
                                                        <MessageSquare className="h-4 w-4" />
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
