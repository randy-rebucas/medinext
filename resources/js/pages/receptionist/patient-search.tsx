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
    Search, 
    User, 
    Phone, 
    Mail,
    Calendar,
    Eye,
    Edit,
    Plus
} from 'lucide-react';

export default function PatientSearch() {
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
            insurance: 'Medicare'
        }
    ];

    return (
        <AppLayout>
            <Head title="Patient Search" />
            
            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Patient Search</h1>
                        <p className="text-muted-foreground">
                            Search and manage patient information
                        </p>
                    </div>
                    <Button>
                        <Plus className="mr-2 h-4 w-4" />
                        Register New Patient
                    </Button>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Patient Directory</CardTitle>
                        <CardDescription>
                            Search for patients by name, phone, email, or ID
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="flex items-center space-x-2 mb-4">
                            <div className="relative flex-1">
                                <Search className="absolute left-2 top-2.5 h-4 w-4 text-muted-foreground" />
                                <Input placeholder="Search patients by name, phone, email, or ID..." className="pl-8" />
                            </div>
                            <Button variant="outline">
                                Advanced Search
                            </Button>
                        </div>

                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Patient</TableHead>
                                    <TableHead>Contact</TableHead>
                                    <TableHead>Age</TableHead>
                                    <TableHead>Gender</TableHead>
                                    <TableHead>Last Visit</TableHead>
                                    <TableHead>Insurance</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead className="text-right">Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {patients.map((patient) => (
                                    <TableRow key={patient.id}>
                                        <TableCell>
                                            <div className="flex items-center">
                                                <User className="mr-2 h-4 w-4" />
                                                <div>
                                                    <div className="font-medium">{patient.name}</div>
                                                    <div className="text-sm text-muted-foreground">ID: {patient.id}</div>
                                                </div>
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <div className="space-y-1">
                                                <div className="flex items-center text-sm">
                                                    <Mail className="mr-1 h-3 w-3" />
                                                    {patient.email}
                                                </div>
                                                <div className="flex items-center text-sm">
                                                    <Phone className="mr-1 h-3 w-3" />
                                                    {patient.phone}
                                                </div>
                                            </div>
                                        </TableCell>
                                        <TableCell>{patient.age}</TableCell>
                                        <TableCell>{patient.gender}</TableCell>
                                        <TableCell>
                                            <div className="flex items-center text-sm">
                                                <Calendar className="mr-1 h-3 w-3" />
                                                {patient.lastVisit}
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <Badge variant="outline">{patient.insurance}</Badge>
                                        </TableCell>
                                        <TableCell>
                                            <Badge variant={patient.status === 'Active' ? 'default' : 'secondary'}>
                                                {patient.status}
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
                                                    <Calendar className="h-4 w-4" />
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
