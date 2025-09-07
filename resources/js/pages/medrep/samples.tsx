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
    Plus,
    Search,
    Edit,
    Eye,
    Filter,
    Calendar,
    Package,
    Stethoscope,
    CheckCircle,
    Building2,
    Shield
} from 'lucide-react';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Sample Management',
        href: '/medrep/samples',
    },
    {
        title: 'Sample Management',
        href: '/medrep/samples',
    },
];

interface SampleManagementProps {
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

export default function SampleManagement({ user }: SampleManagementProps = {}) {
    const samples = [
        {
            id: 1,
            product: 'CardioMax 10mg',
            doctor: 'Dr. Sarah Johnson',
            quantity: 5,
            date: '2024-01-15',
            status: 'Delivered',
            trackingNumber: 'TRK001234',
            notes: 'Delivered during product presentation'
        },
        {
            id: 2,
            product: 'DermaCare Cream',
            doctor: 'Dr. Emily Davis',
            quantity: 3,
            date: '2024-01-14',
            status: 'Delivered',
            trackingNumber: 'TRK001235',
            notes: 'Doctor requested samples for patient trials'
        },
        {
            id: 3,
            product: 'NeuroCalm 5mg',
            doctor: 'Dr. Michael Brown',
            quantity: 2,
            date: '2024-01-13',
            status: 'In Transit',
            trackingNumber: 'TRK001236',
            notes: 'Scheduled for delivery tomorrow'
        },
        {
            id: 4,
            product: 'OrthoFlex 500mg',
            doctor: 'Dr. James Wilson',
            quantity: 4,
            date: '2024-01-12',
            status: 'Pending',
            trackingNumber: 'TRK001237',
            notes: 'Awaiting doctor confirmation'
        }
    ];

    const getStatusColor = (status: string) => {
        switch (status) {
            case 'Delivered': return 'default';
            case 'In Transit': return 'secondary';
            case 'Pending': return 'secondary';
            case 'Cancelled': return 'destructive';
            default: return 'secondary';
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Sample Management - Medinext">
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
                                    Sample Management
                                </h1>
                                <p className="mt-2 text-emerald-100">
                                    Track and manage product samples for healthcare professionals
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
                            <Plus className="mr-2 h-4 w-4" />
                            Request Sample
                        </Button>
                    </div>

                    <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                        <CardHeader>
                            <CardTitle className="text-lg font-semibold text-slate-900 dark:text-white">Sample Tracking</CardTitle>
                            <CardDescription className="text-slate-600 dark:text-slate-300">
                                View and manage all product samples
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="flex items-center space-x-2 mb-4">
                                <div className="relative flex-1">
                                    <Search className="absolute left-2 top-2.5 h-4 w-4 text-slate-400" />
                                    <Input placeholder="Search samples..." className="pl-8 border-slate-200 dark:border-slate-700" />
                                </div>
                                <Button variant="outline" className="border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800">
                                    <Filter className="mr-2 h-4 w-4" />
                                    Filter
                                </Button>
                            </div>

                            <Table>
                                <TableHeader>
                                    <TableRow className="border-slate-200 dark:border-slate-700">
                                        <TableHead className="text-slate-700 dark:text-slate-300">Product</TableHead>
                                        <TableHead className="text-slate-700 dark:text-slate-300">Doctor</TableHead>
                                        <TableHead className="text-slate-700 dark:text-slate-300">Quantity</TableHead>
                                        <TableHead className="text-slate-700 dark:text-slate-300">Date</TableHead>
                                        <TableHead className="text-slate-700 dark:text-slate-300">Status</TableHead>
                                        <TableHead className="text-slate-700 dark:text-slate-300">Tracking</TableHead>
                                        <TableHead className="text-slate-700 dark:text-slate-300">Notes</TableHead>
                                        <TableHead className="text-right text-slate-700 dark:text-slate-300">Actions</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {samples.map((sample) => (
                                        <TableRow key={sample.id} className="border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/50">
                                            <TableCell>
                                                <div className="flex items-center">
                                                    <Package className="mr-2 h-4 w-4 text-slate-400" />
                                                    <span className="text-slate-900 dark:text-white">{sample.product}</span>
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <div className="flex items-center">
                                                    <Stethoscope className="mr-2 h-4 w-4 text-slate-400" />
                                                    <span className="text-slate-900 dark:text-white">{sample.doctor}</span>
                                                </div>
                                            </TableCell>
                                            <TableCell className="text-slate-900 dark:text-white">{sample.quantity}</TableCell>
                                            <TableCell>
                                                <div className="flex items-center text-sm text-slate-600 dark:text-slate-400">
                                                    <Calendar className="mr-1 h-3 w-3" />
                                                    {sample.date}
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <Badge variant={getStatusColor(sample.status)} className="border-0">
                                                    {sample.status}
                                                </Badge>
                                            </TableCell>
                                            <TableCell>
                                                <div className="font-mono text-sm text-slate-600 dark:text-slate-400">{sample.trackingNumber}</div>
                                            </TableCell>
                                            <TableCell>
                                                <div className="max-w-xs truncate text-slate-600 dark:text-slate-400">
                                                    {sample.notes}
                                                </div>
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
                </div>
            </div>
        </AppLayout>
    );
}
