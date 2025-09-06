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
    Stethoscope,
    MessageSquare,
    Star
} from 'lucide-react';

export default function DoctorManagement() {
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
        <AppLayout>
            <Head title="Doctor Management" />
            
            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Doctor Management</h1>
                        <p className="text-muted-foreground">
                            Manage your relationships with healthcare professionals
                        </p>
                    </div>
                    <Button>
                        <Users className="mr-2 h-4 w-4" />
                        Add Doctor
                    </Button>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Doctor Directory</CardTitle>
                        <CardDescription>
                            View and manage all doctors in your network
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="flex items-center space-x-2 mb-4">
                            <div className="relative flex-1">
                                <Search className="absolute left-2 top-2.5 h-4 w-4 text-muted-foreground" />
                                <Input placeholder="Search doctors..." className="pl-8" />
                            </div>
                            <Button variant="outline">
                                <Filter className="mr-2 h-4 w-4" />
                                Filter
                            </Button>
                        </div>

                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Doctor</TableHead>
                                    <TableHead>Specialty</TableHead>
                                    <TableHead>Clinic</TableHead>
                                    <TableHead>Contact</TableHead>
                                    <TableHead>Last Interaction</TableHead>
                                    <TableHead>Interactions</TableHead>
                                    <TableHead>Rating</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead className="text-right">Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {doctors.map((doctor) => (
                                    <TableRow key={doctor.id}>
                                        <TableCell>
                                            <div>
                                                <div className="font-medium">{doctor.name}</div>
                                                <div className="text-sm text-muted-foreground">ID: {doctor.id}</div>
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <Badge variant="outline">{doctor.specialty}</Badge>
                                        </TableCell>
                                        <TableCell>{doctor.clinic}</TableCell>
                                        <TableCell>
                                            <div className="space-y-1">
                                                <div className="text-sm">{doctor.email}</div>
                                                <div className="text-sm text-muted-foreground">{doctor.phone}</div>
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <div className="flex items-center text-sm">
                                                <Calendar className="mr-1 h-3 w-3" />
                                                {doctor.lastInteraction}
                                            </div>
                                        </TableCell>
                                        <TableCell>{doctor.totalInteractions}</TableCell>
                                        <TableCell>
                                            <div className="flex items-center">
                                                <Star className="mr-1 h-3 w-3 text-yellow-500" />
                                                {doctor.rating}
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <Badge variant="default">{doctor.status}</Badge>
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
        </AppLayout>
    );
}
