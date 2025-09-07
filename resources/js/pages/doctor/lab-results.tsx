import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { doctorLabResults } from '@/routes';
import { type BreadcrumbItem } from '@/types';
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
    TestTube,
    Search,
    Eye,
    Download,
    Filter,
    Calendar,
    User,
    AlertCircle,
    Building2,
    Shield
} from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Doctor Dashboard',
        href: '/doctor/dashboard',
    },
    {
        title: 'Lab Results',
        href: doctorLabResults(),
    },
];

interface LabResultsProps {
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

export default function LabResults({ user, permissions = [] }: LabResultsProps) {
    const hasPermission = (permission: string) => permissions.includes(permission);
    const labResults = [
        {
            id: 1,
            patient: 'John Doe',
            testName: 'Complete Blood Count',
            testDate: '2024-01-14',
            status: 'Completed',
            results: 'Normal',
            doctor: 'Dr. Sarah Johnson',
            priority: 'Normal'
        },
        {
            id: 2,
            patient: 'Jane Smith',
            testName: 'Lipid Panel',
            testDate: '2024-01-13',
            status: 'Completed',
            results: 'Abnormal',
            doctor: 'Dr. Michael Brown',
            priority: 'High'
        },
        {
            id: 3,
            patient: 'Bob Johnson',
            testName: 'Thyroid Function Test',
            testDate: '2024-01-12',
            status: 'Pending',
            results: 'Pending',
            doctor: 'Dr. Emily Davis',
            priority: 'Normal'
        },
        {
            id: 4,
            patient: 'Alice Brown',
            testName: 'Blood Glucose',
            testDate: '2024-01-11',
            status: 'Completed',
            results: 'Normal',
            doctor: 'Dr. James Wilson',
            priority: 'Normal'
        }
    ];

    const getStatusColor = (status: string) => {
        switch (status) {
            case 'Completed': return 'default';
            case 'Pending': return 'secondary';
            case 'Abnormal': return 'destructive';
            default: return 'secondary';
        }
    };

    const getPriorityColor = (priority: string) => {
        switch (priority) {
            case 'High': return 'destructive';
            case 'Normal': return 'default';
            case 'Low': return 'secondary';
            default: return 'secondary';
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Lab Results - Medinext">
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
                                <h1 className="text-3xl font-bold tracking-tight">Lab Results</h1>
                                <p className="mt-2 text-blue-100">
                                    {user?.clinic?.name || 'No Clinic'} • View and manage laboratory test results
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
                                {hasPermission('request_lab_tests') && (
                                    <Button className="bg-white/20 hover:bg-white/30 text-white border-white/30 hover:border-white/40">
                                        <TestTube className="mr-2 h-4 w-4" />
                                        Request New Test
                                    </Button>
                                )}
                            </div>
                        </div>
                        {/* Decorative elements */}
                        <div className="absolute -top-4 -right-4 w-24 h-24 bg-white/10 rounded-full"></div>
                        <div className="absolute -bottom-2 -left-2 w-16 h-16 bg-white/5 rounded-full"></div>
                    </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Laboratory Results</CardTitle>
                        <CardDescription>
                            Review and analyze patient lab test results
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="flex items-center space-x-2 mb-4">
                            <div className="relative flex-1">
                                <Search className="absolute left-2 top-2.5 h-4 w-4 text-muted-foreground" />
                                <Input placeholder="Search lab results..." className="pl-8" />
                            </div>
                            <Button variant="outline">
                                <Filter className="mr-2 h-4 w-4" />
                                Filter
                            </Button>
                        </div>

                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Patient</TableHead>
                                    <TableHead>Test Name</TableHead>
                                    <TableHead>Test Date</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead>Results</TableHead>
                                    <TableHead>Priority</TableHead>
                                    <TableHead className="text-right">Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {labResults.map((result) => (
                                    <TableRow key={result.id}>
                                        <TableCell>
                                            <div className="flex items-center">
                                                <User className="mr-2 h-4 w-4" />
                                                {result.patient}
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <div className="font-medium">{result.testName}</div>
                                        </TableCell>
                                        <TableCell>
                                            <div className="flex items-center text-sm">
                                                <Calendar className="mr-1 h-3 w-3" />
                                                {result.testDate}
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <Badge variant={getStatusColor(result.status)}>
                                                {result.status}
                                            </Badge>
                                        </TableCell>
                                        <TableCell>
                                            <div className="flex items-center">
                                                {result.results === 'Abnormal' && (
                                                    <AlertCircle className="mr-1 h-3 w-3 text-red-600" />
                                                )}
                                                <span className={result.results === 'Abnormal' ? 'text-red-600 font-medium' : ''}>
                                                    {result.results}
                                                </span>
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <Badge variant={getPriorityColor(result.priority)}>
                                                {result.priority}
                                            </Badge>
                                        </TableCell>
                                        <TableCell className="text-right">
                                            <div className="flex items-center justify-end space-x-2">
                                                <Button variant="ghost" size="sm">
                                                    <Eye className="h-4 w-4" />
                                                </Button>
                                                <Button variant="ghost" size="sm">
                                                    <Download className="h-4 w-4" />
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
