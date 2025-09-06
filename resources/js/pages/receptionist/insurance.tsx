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
    XCircle
} from 'lucide-react';

export default function InsuranceManagement() {
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
        <AppLayout>
            <Head title="Insurance Management" />
            
            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Insurance Management</h1>
                        <p className="text-muted-foreground">
                            Manage patient insurance information and verification
                        </p>
                    </div>
                    <Button>
                        <CreditCard className="mr-2 h-4 w-4" />
                        Add Insurance
                    </Button>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Insurance Records</CardTitle>
                        <CardDescription>
                            View and manage patient insurance information
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="flex items-center space-x-2 mb-4">
                            <div className="relative flex-1">
                                <Search className="absolute left-2 top-2.5 h-4 w-4 text-muted-foreground" />
                                <Input placeholder="Search insurance records..." className="pl-8" />
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
                                    <TableHead>Provider</TableHead>
                                    <TableHead>Policy Number</TableHead>
                                    <TableHead>Group Number</TableHead>
                                    <TableHead>Coverage</TableHead>
                                    <TableHead>Effective Date</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead className="text-right">Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {insuranceRecords.map((record) => (
                                    <TableRow key={record.id}>
                                        <TableCell>
                                            <div className="flex items-center">
                                                <User className="mr-2 h-4 w-4" />
                                                {record.patient}
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <div className="font-medium">{record.provider}</div>
                                        </TableCell>
                                        <TableCell>
                                            <div className="font-mono text-sm">{record.policyNumber}</div>
                                        </TableCell>
                                        <TableCell>
                                            <div className="font-mono text-sm">{record.groupNumber}</div>
                                        </TableCell>
                                        <TableCell>
                                            <Badge variant="outline">{record.coverage}</Badge>
                                        </TableCell>
                                        <TableCell>
                                            <div className="flex items-center text-sm">
                                                <Calendar className="mr-1 h-3 w-3" />
                                                {record.effectiveDate}
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <Badge variant={getStatusColor(record.status)}>
                                                {record.status}
                                            </Badge>
                                        </TableCell>
                                        <TableCell className="text-right">
                                            <div className="flex items-center justify-end space-x-2">
                                                <Button variant="ghost" size="sm">
                                                    <Eye className="h-4 w-4" />
                                                </Button>
                                                <Button variant="ghost" size="sm">
                                                    <Edit className="h-4 w-4" />
                                                </Button>
                                                <Button variant="ghost" size="sm">
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
                <Card>
                    <CardHeader>
                        <CardTitle>Insurance Verification</CardTitle>
                        <CardDescription>
                            Verify patient insurance coverage and eligibility
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="grid gap-4 md:grid-cols-2">
                            <div className="space-y-4">
                                <div>
                                    <label className="text-sm font-medium">Patient Name</label>
                                    <Input placeholder="Enter patient name" />
                                </div>
                                <div>
                                    <label className="text-sm font-medium">Policy Number</label>
                                    <Input placeholder="Enter policy number" />
                                </div>
                                <div>
                                    <label className="text-sm font-medium">Date of Birth</label>
                                    <Input type="date" />
                                </div>
                            </div>
                            <div className="space-y-4">
                                <div>
                                    <label className="text-sm font-medium">Insurance Provider</label>
                                    <Input placeholder="Select provider" />
                                </div>
                                <div>
                                    <label className="text-sm font-medium">Group Number</label>
                                    <Input placeholder="Enter group number" />
                                </div>
                                <div className="flex space-x-2">
                                    <Button>
                                        <CheckCircle className="mr-2 h-4 w-4" />
                                        Verify Coverage
                                    </Button>
                                    <Button variant="outline">
                                        <XCircle className="mr-2 h-4 w-4" />
                                        Clear
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
