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
    AlertCircle
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

export default function LabResults() {
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
            <Head title="Lab Results" />
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Lab Results</h1>
                        <p className="text-muted-foreground">
                            View and manage laboratory test results
                        </p>
                    </div>
                    <Button>
                        <TestTube className="mr-2 h-4 w-4" />
                        Request New Test
                    </Button>
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
        </AppLayout>
    );
}
