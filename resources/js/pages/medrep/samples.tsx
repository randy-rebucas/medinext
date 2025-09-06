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
    Target, 
    Plus, 
    Search, 
    Edit, 
    Trash2, 
    Eye,
    Filter,
    Calendar,
    Package,
    Stethoscope,
    CheckCircle
} from 'lucide-react';

export default function SampleManagement() {
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
        <AppLayout>
            <Head title="Sample Management" />
            
            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Sample Management</h1>
                        <p className="text-muted-foreground">
                            Track and manage product samples for healthcare professionals
                        </p>
                    </div>
                    <Button>
                        <Plus className="mr-2 h-4 w-4" />
                        Request Sample
                    </Button>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Sample Tracking</CardTitle>
                        <CardDescription>
                            View and manage all product samples
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="flex items-center space-x-2 mb-4">
                            <div className="relative flex-1">
                                <Search className="absolute left-2 top-2.5 h-4 w-4 text-muted-foreground" />
                                <Input placeholder="Search samples..." className="pl-8" />
                            </div>
                            <Button variant="outline">
                                <Filter className="mr-2 h-4 w-4" />
                                Filter
                            </Button>
                        </div>

                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Product</TableHead>
                                    <TableHead>Doctor</TableHead>
                                    <TableHead>Quantity</TableHead>
                                    <TableHead>Date</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead>Tracking</TableHead>
                                    <TableHead>Notes</TableHead>
                                    <TableHead className="text-right">Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {samples.map((sample) => (
                                    <TableRow key={sample.id}>
                                        <TableCell>
                                            <div className="flex items-center">
                                                <Package className="mr-2 h-4 w-4" />
                                                {sample.product}
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <div className="flex items-center">
                                                <Stethoscope className="mr-2 h-4 w-4" />
                                                {sample.doctor}
                                            </div>
                                        </TableCell>
                                        <TableCell>{sample.quantity}</TableCell>
                                        <TableCell>
                                            <div className="flex items-center text-sm">
                                                <Calendar className="mr-1 h-3 w-3" />
                                                {sample.date}
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <Badge variant={getStatusColor(sample.status)}>
                                                {sample.status}
                                            </Badge>
                                        </TableCell>
                                        <TableCell>
                                            <div className="font-mono text-sm">{sample.trackingNumber}</div>
                                        </TableCell>
                                        <TableCell>
                                            <div className="max-w-xs truncate">
                                                {sample.notes}
                                            </div>
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
            </div>
        </AppLayout>
    );
}
