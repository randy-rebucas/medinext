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
    FileText,
    Search,
    Eye,
    Edit,
    Filter,
    Calendar,
    User,
    Stethoscope,
    Clock,
    Building2,
    Shield
} from 'lucide-react';
import { receptionistEncounters } from '@/routes';
import { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Receptionist Dashboard',
        href: '/receptionist/dashboard',
    },
    {
        title: 'Encounters',
        href: receptionistEncounters(),
    },
];

interface EncountersProps {
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

export default function Encounters({
    user,
    permissions = []
}: EncountersProps) {
    const hasPermission = (permission: string) => permissions.includes(permission);
    const encounters = [
        {
            id: 1,
            patient: 'John Doe',
            doctor: 'Dr. Sarah Johnson',
            date: '2024-01-15',
            time: '09:00 AM',
            type: 'Consultation',
            status: 'Completed',
            chiefComplaint: 'Chest pain',
            room: 'Room 101'
        },
        {
            id: 2,
            patient: 'Jane Smith',
            doctor: 'Dr. Michael Brown',
            date: '2024-01-15',
            time: '10:30 AM',
            type: 'Follow-up',
            status: 'In Progress',
            chiefComplaint: 'Follow-up visit',
            room: 'Room 102'
        },
        {
            id: 3,
            patient: 'Bob Johnson',
            doctor: 'Dr. Emily Davis',
            date: '2024-01-15',
            time: '11:15 AM',
            type: 'Check-up',
            status: 'Scheduled',
            chiefComplaint: 'Annual physical',
            room: 'Room 103'
        },
        {
            id: 4,
            patient: 'Alice Brown',
            doctor: 'Dr. James Wilson',
            date: '2024-01-15',
            time: '02:00 PM',
            type: 'Consultation',
            status: 'Completed',
            chiefComplaint: 'Knee pain',
            room: 'Room 104'
        }
    ];

    const getStatusColor = (status: string) => {
        switch (status) {
            case 'Completed': return 'default';
            case 'In Progress': return 'secondary';
            case 'Scheduled': return 'default';
            case 'Cancelled': return 'destructive';
            default: return 'secondary';
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Encounters - Medinext">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&family=instrument-sans:400,500,600" rel="stylesheet" />
            </Head>
            <div className="min-h-screen bg-gradient-to-br from-slate-50 via-white to-blue-50 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900">
                <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
                    {/* Modern Header */}
                    <div className="relative overflow-hidden rounded-2xl bg-gradient-to-r from-green-600 to-emerald-600 p-8 text-white shadow-xl">
                        <div className="absolute inset-0 bg-black/10"></div>
                        <div className="relative flex items-center justify-between">
                            <div>
                                <h1 className="text-3xl font-bold tracking-tight">Encounters</h1>
                                <p className="mt-2 text-green-100">
                                    {user?.clinic?.name || 'No Clinic'} • Manage patient encounters and medical records
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
                                {hasPermission('create_encounters') && (
                                    <Button className="bg-white/20 hover:bg-white/30 text-white border-white/30 hover:border-white/40">
                                        <FileText className="mr-2 h-4 w-4" />
                                        New Encounter
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
                            <CardTitle className="text-lg font-semibold text-slate-900 dark:text-white">Patient Encounters</CardTitle>
                            <CardDescription className="text-slate-600 dark:text-slate-300">
                                View and manage all patient encounters
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="flex items-center space-x-2 mb-4">
                                <div className="relative flex-1">
                                    <Search className="absolute left-2 top-2.5 h-4 w-4 text-slate-400 dark:text-slate-500" />
                                    <Input placeholder="Search encounters..." className="pl-8 border-slate-200 dark:border-slate-700 focus:border-green-500 dark:focus:border-green-400" />
                                </div>
                                <Button variant="outline" className="border-slate-200 dark:border-slate-700 hover:bg-green-50 dark:hover:bg-green-900/20 hover:border-green-300 dark:hover:border-green-600 transition-all duration-200">
                                    <Filter className="mr-2 h-4 w-4" />
                                    Filter
                                </Button>
                            </div>

                            <Table>
                                <TableHeader>
                                    <TableRow className="border-slate-200 dark:border-slate-700">
                                        <TableHead className="text-slate-700 dark:text-slate-300">Patient</TableHead>
                                        <TableHead className="text-slate-700 dark:text-slate-300">Doctor</TableHead>
                                        <TableHead className="text-slate-700 dark:text-slate-300">Date & Time</TableHead>
                                        <TableHead className="text-slate-700 dark:text-slate-300">Type</TableHead>
                                        <TableHead className="text-slate-700 dark:text-slate-300">Chief Complaint</TableHead>
                                        <TableHead className="text-slate-700 dark:text-slate-300">Status</TableHead>
                                        <TableHead className="text-slate-700 dark:text-slate-300">Room</TableHead>
                                        <TableHead className="text-right text-slate-700 dark:text-slate-300">Actions</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {encounters.map((encounter) => (
                                        <TableRow key={encounter.id} className="border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                                            <TableCell>
                                                <div className="flex items-center">
                                                    <div className="p-1 bg-green-100 dark:bg-green-900/20 rounded-md mr-2">
                                                        <User className="h-4 w-4 text-green-600 dark:text-green-400" />
                                                    </div>
                                                    <span className="text-slate-900 dark:text-white">{encounter.patient}</span>
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <div className="flex items-center">
                                                    <div className="p-1 bg-blue-100 dark:bg-blue-900/20 rounded-md mr-2">
                                                        <Stethoscope className="h-4 w-4 text-blue-600 dark:text-blue-400" />
                                                    </div>
                                                    <span className="text-slate-900 dark:text-white">{encounter.doctor}</span>
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <div className="flex items-center">
                                                    <div className="p-1 bg-purple-100 dark:bg-purple-900/20 rounded-md mr-2">
                                                        <Calendar className="h-4 w-4 text-purple-600 dark:text-purple-400" />
                                                    </div>
                                                    <div>
                                                        <div className="font-medium text-slate-900 dark:text-white">{encounter.date}</div>
                                                        <div className="text-sm text-slate-600 dark:text-slate-400 flex items-center">
                                                            <Clock className="mr-1 h-3 w-3" />
                                                            {encounter.time}
                                                        </div>
                                                    </div>
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <Badge variant="outline" className="border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300">
                                                    {encounter.type}
                                                </Badge>
                                            </TableCell>
                                            <TableCell className="text-slate-900 dark:text-white">{encounter.chiefComplaint}</TableCell>
                                            <TableCell>
                                                <Badge variant={getStatusColor(encounter.status)} className="border-0">
                                                    {encounter.status}
                                                </Badge>
                                            </TableCell>
                                            <TableCell className="text-slate-900 dark:text-white">{encounter.room}</TableCell>
                                            <TableCell className="text-right">
                                                <div className="flex items-center justify-end space-x-2">
                                                    <Button variant="ghost" size="sm" className="hover:bg-green-100 dark:hover:bg-green-900/20 hover:text-green-600 dark:hover:text-green-400 transition-all duration-200">
                                                        <Eye className="h-4 w-4" />
                                                    </Button>
                                                    <Button variant="ghost" size="sm" className="hover:bg-blue-100 dark:hover:bg-blue-900/20 hover:text-blue-600 dark:hover:text-blue-400 transition-all duration-200">
                                                        <Edit className="h-4 w-4" />
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
