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
    CreditCard,
    Search,
    Eye,
    Edit,
    Filter,
    User,
    Calendar,
    CheckCircle,
    XCircle,
    Building2,
    Shield
} from 'lucide-react';
import { receptionistInsurance } from '@/routes';
import { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Receptionist Dashboard',
        href: '/receptionist/dashboard',
    },
    {
        title: 'Insurance Management',
        href: receptionistInsurance(),
    },
];

interface InsuranceManagementProps {
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

export default function InsuranceManagement({
    user,
    permissions = []
}: InsuranceManagementProps) {
    const hasPermission = (permission: string) => permissions.includes(permission);
    const insuranceRecords = [
        {
            id: 1,
            patient: 'John Doe',
            provider: 'Blue Cross Blue Shield',
            policyNumber: 'BC123456789',
            groupNumber: 'GRP001',
            subscriberId: 'SUB123',
            effectiveDate: '2024-01-01',
            expiryDate: '2024-12-31',
            status: 'Active',
            coverage: '100%'
        },
        {
            id: 2,
            patient: 'Jane Smith',
            provider: 'Aetna',
            policyNumber: 'AET987654321',
            groupNumber: 'GRP002',
            subscriberId: 'SUB456',
            effectiveDate: '2024-01-01',
            expiryDate: '2024-12-31',
            status: 'Active',
            coverage: '80%'
        },
        {
            id: 3,
            patient: 'Bob Johnson',
            provider: 'Cigna',
            policyNumber: 'CIG456789123',
            groupNumber: 'GRP003',
            subscriberId: 'SUB789',
            effectiveDate: '2024-01-01',
            expiryDate: '2024-12-31',
            status: 'Active',
            coverage: '90%'
        },
        {
            id: 4,
            patient: 'Alice Brown',
            provider: 'Medicare',
            policyNumber: 'MED123456789',
            groupNumber: 'N/A',
            subscriberId: 'SUB012',
            effectiveDate: '2024-01-01',
            expiryDate: '2024-12-31',
            status: 'Active',
            coverage: '100%'
        }
    ];

    const getStatusColor = (status: string) => {
        switch (status) {
            case 'Active': return 'default';
            case 'Expired': return 'destructive';
            case 'Pending': return 'secondary';
            default: return 'secondary';
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Insurance Management - Medinext">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&family=instrument-sans:400,500,600" rel="stylesheet" />
            </Head>
            <div className="min-h-screen bg-gradient-to-br from-slate-50 via-white to-blue-50 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900">
                <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
                    {/* Modern Header */}
                    <div className="relative overflow-hidden rounded-2xl bg-gradient-to-r from-cyan-600 to-blue-600 p-8 text-white shadow-xl">
                        <div className="absolute inset-0 bg-black/10"></div>
                        <div className="relative flex items-center justify-between">
                            <div>
                                <h1 className="text-3xl font-bold tracking-tight">Insurance Management</h1>
                                <p className="mt-2 text-cyan-100">
                                    {user?.clinic?.name || 'No Clinic'} • Manage patient insurance information and verification
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
                                {hasPermission('manage_insurance') && (
                                    <Button className="bg-white/20 hover:bg-white/30 text-white border-white/30 hover:border-white/40">
                                        <CreditCard className="mr-2 h-4 w-4" />
                                        Add Insurance
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
                            <CardTitle className="text-lg font-semibold text-slate-900 dark:text-white">Insurance Records</CardTitle>
                            <CardDescription className="text-slate-600 dark:text-slate-300">
                                View and manage patient insurance information
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="flex items-center space-x-2 mb-4">
                                <div className="relative flex-1">
                                    <Search className="absolute left-2 top-2.5 h-4 w-4 text-slate-400 dark:text-slate-500" />
                                    <Input placeholder="Search insurance records..." className="pl-8 border-slate-200 dark:border-slate-700 focus:border-cyan-500 dark:focus:border-cyan-400" />
                                </div>
                                <Button variant="outline" className="border-slate-200 dark:border-slate-700 hover:bg-cyan-50 dark:hover:bg-cyan-900/20 hover:border-cyan-300 dark:hover:border-cyan-600 transition-all duration-200">
                                    <Filter className="mr-2 h-4 w-4" />
                                    Filter
                                </Button>
                            </div>

                            <Table>
                                <TableHeader>
                                    <TableRow className="border-slate-200 dark:border-slate-700">
                                        <TableHead className="text-slate-700 dark:text-slate-300">Patient</TableHead>
                                        <TableHead className="text-slate-700 dark:text-slate-300">Provider</TableHead>
                                        <TableHead className="text-slate-700 dark:text-slate-300">Policy Number</TableHead>
                                        <TableHead className="text-slate-700 dark:text-slate-300">Group Number</TableHead>
                                        <TableHead className="text-slate-700 dark:text-slate-300">Coverage</TableHead>
                                        <TableHead className="text-slate-700 dark:text-slate-300">Effective Date</TableHead>
                                        <TableHead className="text-slate-700 dark:text-slate-300">Status</TableHead>
                                        <TableHead className="text-right text-slate-700 dark:text-slate-300">Actions</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {insuranceRecords.map((record) => (
                                        <TableRow key={record.id} className="border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                                            <TableCell>
                                                <div className="flex items-center">
                                                    <div className="p-1 bg-cyan-100 dark:bg-cyan-900/20 rounded-md mr-2">
                                                        <User className="h-4 w-4 text-cyan-600 dark:text-cyan-400" />
                                                    </div>
                                                    <span className="text-slate-900 dark:text-white">{record.patient}</span>
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <div className="font-medium text-slate-900 dark:text-white">{record.provider}</div>
                                            </TableCell>
                                            <TableCell>
                                                <div className="font-mono text-sm text-slate-600 dark:text-slate-400">{record.policyNumber}</div>
                                            </TableCell>
                                            <TableCell>
                                                <div className="font-mono text-sm text-slate-600 dark:text-slate-400">{record.groupNumber}</div>
                                            </TableCell>
                                            <TableCell>
                                                <Badge variant="outline" className="border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300">
                                                    {record.coverage}
                                                </Badge>
                                            </TableCell>
                                            <TableCell>
                                                <div className="flex items-center text-sm text-slate-600 dark:text-slate-400">
                                                    <Calendar className="mr-1 h-3 w-3" />
                                                    {record.effectiveDate}
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <Badge variant={getStatusColor(record.status)} className="border-0">
                                                    {record.status}
                                                </Badge>
                                            </TableCell>
                                            <TableCell className="text-right">
                                                <div className="flex items-center justify-end space-x-2">
                                                    <Button variant="ghost" size="sm" className="hover:bg-cyan-100 dark:hover:bg-cyan-900/20 hover:text-cyan-600 dark:hover:text-cyan-400 transition-all duration-200">
                                                        <Eye className="h-4 w-4" />
                                                    </Button>
                                                    <Button variant="ghost" size="sm" className="hover:bg-blue-100 dark:hover:bg-blue-900/20 hover:text-blue-600 dark:hover:text-blue-400 transition-all duration-200">
                                                        <Edit className="h-4 w-4" />
                                                    </Button>
                                                    <Button variant="ghost" size="sm" className="hover:bg-green-100 dark:hover:bg-green-900/20 hover:text-green-600 dark:hover:text-green-400 transition-all duration-200">
                                                        <CheckCircle className="h-4 w-4" />
                                                    </Button>
                                                </div>
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        </CardContent>
                    </Card>

                    {/* Insurance Verification */}
                    <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                        <CardHeader>
                            <CardTitle className="text-lg font-semibold text-slate-900 dark:text-white">Insurance Verification</CardTitle>
                            <CardDescription className="text-slate-600 dark:text-slate-300">
                                Verify patient insurance coverage and eligibility
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="grid gap-4 md:grid-cols-2">
                                <div className="space-y-4">
                                    <div>
                                        <label className="text-sm font-medium text-slate-700 dark:text-slate-300">Patient Name</label>
                                        <Input placeholder="Enter patient name" className="border-slate-200 dark:border-slate-700 focus:border-cyan-500 dark:focus:border-cyan-400" />
                                    </div>
                                    <div>
                                        <label className="text-sm font-medium text-slate-700 dark:text-slate-300">Policy Number</label>
                                        <Input placeholder="Enter policy number" className="border-slate-200 dark:border-slate-700 focus:border-cyan-500 dark:focus:border-cyan-400" />
                                    </div>
                                    <div>
                                        <label className="text-sm font-medium text-slate-700 dark:text-slate-300">Date of Birth</label>
                                        <Input type="date" className="border-slate-200 dark:border-slate-700 focus:border-cyan-500 dark:focus:border-cyan-400" />
                                    </div>
                                </div>
                                <div className="space-y-4">
                                    <div>
                                        <label className="text-sm font-medium text-slate-700 dark:text-slate-300">Insurance Provider</label>
                                        <Input placeholder="Select provider" className="border-slate-200 dark:border-slate-700 focus:border-cyan-500 dark:focus:border-cyan-400" />
                                    </div>
                                    <div>
                                        <label className="text-sm font-medium text-slate-700 dark:text-slate-300">Group Number</label>
                                        <Input placeholder="Enter group number" className="border-slate-200 dark:border-slate-700 focus:border-cyan-500 dark:focus:border-cyan-400" />
                                    </div>
                                    <div className="flex space-x-2">
                                        <Button className="hover:bg-green-600 hover:border-green-600 transition-all duration-200">
                                            <CheckCircle className="mr-2 h-4 w-4" />
                                            Verify Coverage
                                        </Button>
                                        <Button variant="outline" className="border-slate-200 dark:border-slate-700 hover:bg-red-50 dark:hover:bg-red-900/20 hover:border-red-300 dark:hover:border-red-600 transition-all duration-200">
                                            <XCircle className="mr-2 h-4 w-4" />
                                            Clear
                                        </Button>
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
