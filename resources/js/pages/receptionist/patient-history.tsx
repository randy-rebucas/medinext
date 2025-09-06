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
    Eye, 
    Search, 
    User,
    Calendar,
    Stethoscope,
    FileText,
    Phone,
    Mail
} from 'lucide-react';

export default function PatientHistory() {
    const patients = [
        {
            id: 1,
            name: 'John Doe',
            email: 'john.doe@email.com',
            phone: '+1 (555) 123-4567',
            age: 35,
            gender: 'Male',
            lastVisit: '2024-01-10',
            totalVisits: 12,
            insurance: 'Blue Cross',
            status: 'Active'
        },
        {
            id: 2,
            name: 'Jane Smith',
            email: 'jane.smith@email.com',
            phone: '+1 (555) 234-5678',
            age: 28,
            gender: 'Female',
            lastVisit: '2024-01-12',
            totalVisits: 8,
            insurance: 'Aetna',
            status: 'Active'
        },
        {
            id: 3,
            name: 'Bob Johnson',
            email: 'bob.johnson@email.com',
            phone: '+1 (555) 345-6789',
            age: 45,
            gender: 'Male',
            lastVisit: '2024-01-08',
            totalVisits: 15,
            insurance: 'Cigna',
            status: 'Active'
        }
    ];

    const visitHistory = [
        {
            id: 1,
            patient: 'John Doe',
            date: '2024-01-10',
            doctor: 'Dr. Sarah Johnson',
            type: 'Consultation',
            diagnosis: 'Hypertension follow-up',
            status: 'Completed'
        },
        {
            id: 2,
            patient: 'John Doe',
            date: '2023-12-15',
            doctor: 'Dr. Sarah Johnson',
            type: 'Follow-up',
            diagnosis: 'Blood pressure monitoring',
            status: 'Completed'
        },
        {
            id: 3,
            patient: 'Jane Smith',
            date: '2024-01-12',
            doctor: 'Dr. Michael Brown',
            type: 'Check-up',
            diagnosis: 'Annual physical',
            status: 'Completed'
        }
    ];

    return (
        <AppLayout>
            <Head title="Patient History" />
            
            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Patient History</h1>
                        <p className="text-muted-foreground">
                            View patient history and visit records
                        </p>
                    </div>
                    <Button>
                        <Search className="mr-2 h-4 w-4" />
                        Search Patient
                    </Button>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Patient Directory</CardTitle>
                        <CardDescription>
                            Search and view patient history and records
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="flex items-center space-x-2 mb-4">
                            <div className="relative flex-1">
                                <Search className="absolute left-2 top-2.5 h-4 w-4 text-muted-foreground" />
                                <Input placeholder="Search patients..." className="pl-8" />
                            </div>
                        </div>

                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Patient</TableHead>
                                    <TableHead>Contact</TableHead>
                                    <TableHead>Age</TableHead>
                                    <TableHead>Gender</TableHead>
                                    <TableHead>Last Visit</TableHead>
                                    <TableHead>Total Visits</TableHead>
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
                                        <TableCell>{patient.totalVisits}</TableCell>
                                        <TableCell>
                                            <Badge variant="outline">{patient.insurance}</Badge>
                                        </TableCell>
                                        <TableCell>
                                            <Badge variant={patient.status === 'Active' ? 'default' : 'secondary'}>
                                                {patient.status}
                                            </Badge>
                                        </TableCell>
                                        <TableCell className="text-right">
                                            <Button variant="ghost" size="sm">
                                                <Eye className="h-4 w-4" />
                                            </Button>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Recent Visit History</CardTitle>
                        <CardDescription>
                            Latest patient visits and consultations
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-4">
                            {visitHistory.map((visit) => (
                                <div key={visit.id} className="flex items-center justify-between p-4 border rounded-lg">
                                    <div className="flex items-center space-x-4">
                                        <div className="flex-shrink-0">
                                            <FileText className="h-5 w-5 text-blue-600" />
                                        </div>
                                        <div>
                                            <h3 className="font-medium">{visit.patient}</h3>
                                            <p className="text-sm text-muted-foreground">{visit.diagnosis}</p>
                                            <div className="flex items-center space-x-4 mt-1">
                                                <span className="text-xs text-muted-foreground flex items-center">
                                                    <Calendar className="mr-1 h-3 w-3" />
                                                    {visit.date}
                                                </span>
                                                <span className="text-xs text-muted-foreground flex items-center">
                                                    <Stethoscope className="mr-1 h-3 w-3" />
                                                    {visit.doctor}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div className="flex items-center space-x-2">
                                        <Badge variant="outline">{visit.type}</Badge>
                                        <Badge variant="default">{visit.status}</Badge>
                                        <Button variant="outline" size="sm">
                                            <Eye className="h-4 w-4" />
                                        </Button>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
