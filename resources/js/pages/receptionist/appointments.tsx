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
    Calendar, 
    Plus, 
    Search, 
    Edit, 
    Trash2, 
    Eye,
    Filter,
    Clock,
    User,
    Stethoscope,
    CheckCircle,
    XCircle
} from 'lucide-react';

export default function ReceptionistAppointments() {
    const appointments = [
        {
            id: 1,
            patient: 'John Doe',
            doctor: 'Dr. Sarah Johnson',
            date: '2024-01-15',
            time: '09:00 AM',
            type: 'Consultation',
            status: 'Scheduled',
            room: 'Room 101',
            phone: '+1 (555) 123-4567'
        },
        {
            id: 2,
            patient: 'Jane Smith',
            doctor: 'Dr. Michael Brown',
            date: '2024-01-15',
            time: '10:30 AM',
            type: 'Follow-up',
            status: 'Confirmed',
            room: 'Room 102',
            phone: '+1 (555) 234-5678'
        },
        {
            id: 3,
            patient: 'Bob Johnson',
            doctor: 'Dr. Emily Davis',
            date: '2024-01-15',
            time: '11:15 AM',
            type: 'Check-up',
            status: 'Checked In',
            room: 'Room 103',
            phone: '+1 (555) 345-6789'
        },
        {
            id: 4,
            patient: 'Alice Brown',
            doctor: 'Dr. James Wilson',
            date: '2024-01-15',
            time: '02:00 PM',
            type: 'Consultation',
            status: 'Completed',
            room: 'Room 104',
            phone: '+1 (555) 456-7890'
        }
    ];

    const getStatusColor = (status: string) => {
        switch (status) {
            case 'Scheduled': return 'default';
            case 'Confirmed': return 'default';
            case 'Checked In': return 'secondary';
            case 'Completed': return 'default';
            case 'Cancelled': return 'destructive';
            case 'No Show': return 'destructive';
            default: return 'secondary';
        }
    };

    return (
        <AppLayout>
            <Head title="Appointments" />
            
            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Appointments</h1>
                        <p className="text-muted-foreground">
                            Manage patient appointments and scheduling
                        </p>
                    </div>
                    <Button>
                        <Plus className="mr-2 h-4 w-4" />
                        Schedule Appointment
                    </Button>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Today's Appointments</CardTitle>
                        <CardDescription>
                            Manage appointments for today and upcoming days
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="flex items-center space-x-2 mb-4">
                            <div className="relative flex-1">
                                <Search className="absolute left-2 top-2.5 h-4 w-4 text-muted-foreground" />
                                <Input placeholder="Search appointments..." className="pl-8" />
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
                                    <TableHead>Doctor</TableHead>
                                    <TableHead>Time</TableHead>
                                    <TableHead>Type</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead>Room</TableHead>
                                    <TableHead className="text-right">Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {appointments.map((appointment) => (
                                    <TableRow key={appointment.id}>
                                        <TableCell>
                                            <div>
                                                <div className="font-medium">{appointment.patient}</div>
                                                <div className="text-sm text-muted-foreground">{appointment.phone}</div>
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <div className="flex items-center">
                                                <Stethoscope className="mr-2 h-4 w-4" />
                                                {appointment.doctor}
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <div className="flex items-center">
                                                <Clock className="mr-2 h-4 w-4" />
                                                <div>
                                                    <div className="font-medium">{appointment.time}</div>
                                                    <div className="text-sm text-muted-foreground">{appointment.date}</div>
                                                </div>
                                            </div>
                                        </TableCell>
                                        <TableCell>{appointment.type}</TableCell>
                                        <TableCell>
                                            <Badge variant={getStatusColor(appointment.status)}>
                                                {appointment.status}
                                            </Badge>
                                        </TableCell>
                                        <TableCell>{appointment.room}</TableCell>
                                        <TableCell className="text-right">
                                            <div className="flex items-center justify-end space-x-2">
                                                <Button variant="ghost" size="sm">
                                                    <Eye className="h-4 w-4" />
                                                </Button>
                                                <Button variant="ghost" size="sm">
                                                    <Edit className="h-4 w-4" />
                                                </Button>
                                                {appointment.status === 'Confirmed' && (
                                                    <Button variant="ghost" size="sm">
                                                        <CheckCircle className="h-4 w-4" />
                                                    </Button>
                                                )}
                                                <Button variant="ghost" size="sm">
                                                    <XCircle className="h-4 w-4" />
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
