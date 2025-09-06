import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { 
    ClipboardList, 
    Search, 
    CheckCircle,
    XCircle,
    Clock,
    User,
    Calendar,
    Stethoscope
} from 'lucide-react';

export default function CheckIn() {
    const appointments = [
        {
            id: 1,
            patient: 'John Doe',
            doctor: 'Dr. Sarah Johnson',
            appointmentTime: '09:00 AM',
            type: 'Consultation',
            status: 'Scheduled',
            phone: '+1 (555) 123-4567',
            insurance: 'Blue Cross'
        },
        {
            id: 2,
            patient: 'Jane Smith',
            doctor: 'Dr. Michael Brown',
            appointmentTime: '10:30 AM',
            type: 'Follow-up',
            status: 'Scheduled',
            phone: '+1 (555) 234-5678',
            insurance: 'Aetna'
        },
        {
            id: 3,
            patient: 'Bob Johnson',
            doctor: 'Dr. Emily Davis',
            appointmentTime: '11:15 AM',
            type: 'Check-up',
            status: 'Checked In',
            phone: '+1 (555) 345-6789',
            insurance: 'Cigna'
        },
        {
            id: 4,
            patient: 'Alice Brown',
            doctor: 'Dr. James Wilson',
            appointmentTime: '02:00 PM',
            type: 'Consultation',
            status: 'Scheduled',
            phone: '+1 (555) 456-7890',
            insurance: 'Medicare'
        }
    ];

    const getStatusColor = (status: string) => {
        switch (status) {
            case 'Scheduled': return 'default';
            case 'Checked In': return 'secondary';
            case 'Completed': return 'default';
            case 'Cancelled': return 'destructive';
            default: return 'secondary';
        }
    };

    return (
        <AppLayout>
            <Head title="Check-in/Check-out" />
            
            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Check-in/Check-out</h1>
                        <p className="text-muted-foreground">
                            Manage patient check-in and check-out process
                        </p>
                    </div>
                    <Button>
                        <Search className="mr-2 h-4 w-4" />
                        Search Patient
                    </Button>
                </div>

                {/* Quick Check-in */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center">
                            <ClipboardList className="mr-2 h-5 w-5" />
                            Quick Check-in
                        </CardTitle>
                        <CardDescription>
                            Check in patients for their appointments
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        <div className="grid gap-4 md:grid-cols-2">
                            <div className="space-y-2">
                                <Label htmlFor="patient-search">Patient Search</Label>
                                <Input id="patient-search" placeholder="Search by name, phone, or ID" />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="appointment-time">Appointment Time</Label>
                                <Input id="appointment-time" type="time" />
                            </div>
                        </div>
                        <div className="flex space-x-2">
                            <Button>
                                <CheckCircle className="mr-2 h-4 w-4" />
                                Check In
                            </Button>
                            <Button variant="outline">
                                <XCircle className="mr-2 h-4 w-4" />
                                Check Out
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                {/* Today's Appointments */}
                <Card>
                    <CardHeader>
                        <CardTitle>Today's Appointments</CardTitle>
                        <CardDescription>
                            Manage check-in status for today's appointments
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-4">
                            {appointments.map((appointment) => (
                                <div key={appointment.id} className="flex items-center justify-between p-4 border rounded-lg">
                                    <div className="flex items-center space-x-4">
                                        <div className="flex-shrink-0">
                                            <User className="h-8 w-8 text-muted-foreground" />
                                        </div>
                                        <div>
                                            <h3 className="font-medium">{appointment.patient}</h3>
                                            <p className="text-sm text-muted-foreground">{appointment.doctor}</p>
                                            <div className="flex items-center space-x-4 mt-1">
                                                <span className="text-xs text-muted-foreground flex items-center">
                                                    <Calendar className="mr-1 h-3 w-3" />
                                                    {appointment.appointmentTime}
                                                </span>
                                                <span className="text-xs text-muted-foreground">
                                                    {appointment.phone}
                                                </span>
                                                <span className="text-xs text-muted-foreground">
                                                    {appointment.insurance}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div className="flex items-center space-x-4">
                                        <div className="text-right">
                                            <Badge variant="outline" className="mb-1">{appointment.type}</Badge>
                                            <div>
                                                <Badge variant={getStatusColor(appointment.status)}>
                                                    {appointment.status}
                                                </Badge>
                                            </div>
                                        </div>
                                        <div className="flex items-center space-x-2">
                                            {appointment.status === 'Scheduled' && (
                                                <Button variant="outline" size="sm">
                                                    <CheckCircle className="h-4 w-4" />
                                                </Button>
                                            )}
                                            {appointment.status === 'Checked In' && (
                                                <Button variant="outline" size="sm">
                                                    <XCircle className="h-4 w-4" />
                                                </Button>
                                            )}
                                            <Button variant="outline" size="sm">
                                                <Clock className="h-4 w-4" />
                                            </Button>
                                        </div>
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
