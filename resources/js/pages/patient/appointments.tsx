import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { patientAppointments } from '@/routes';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Patient Portal',
        href: '/patient/dashboard',
    },
    {
        title: 'My Appointments',
        href: patientAppointments(),
    },
];
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { 
    Calendar, 
    Clock,
    Stethoscope,
    User,
    CheckCircle,
    XCircle,
    Edit,
    Eye
} from 'lucide-react';

export default function PatientAppointments() {
    const appointments = [
        {
            id: 1,
            doctor: 'Dr. Sarah Johnson',
            specialty: 'Cardiology',
            date: '2024-01-20',
            time: '09:00 AM',
            type: 'Consultation',
            status: 'Scheduled',
            room: 'Room 101',
            duration: '30 minutes'
        },
        {
            id: 2,
            doctor: 'Dr. Michael Brown',
            specialty: 'Pediatrics',
            date: '2024-01-18',
            time: '10:30 AM',
            type: 'Follow-up',
            status: 'Completed',
            room: 'Room 102',
            duration: '45 minutes'
        },
        {
            id: 3,
            doctor: 'Dr. Emily Davis',
            specialty: 'Dermatology',
            date: '2024-01-15',
            time: '02:00 PM',
            type: 'Check-up',
            status: 'Completed',
            room: 'Room 103',
            duration: '30 minutes'
        },
        {
            id: 4,
            doctor: 'Dr. James Wilson',
            specialty: 'Orthopedics',
            date: '2024-01-12',
            time: '11:15 AM',
            type: 'Consultation',
            status: 'Cancelled',
            room: 'Room 104',
            duration: '30 minutes'
        }
    ];

    const getStatusColor = (status: string) => {
        switch (status) {
            case 'Scheduled': return 'default';
            case 'Completed': return 'default';
            case 'Cancelled': return 'destructive';
            case 'Rescheduled': return 'secondary';
            default: return 'secondary';
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="My Appointments" />
            
            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">My Appointments</h1>
                        <p className="text-muted-foreground">
                            View and manage your scheduled appointments
                        </p>
                    </div>
                    <Button>
                        <Calendar className="mr-2 h-4 w-4" />
                        Book New Appointment
                    </Button>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Appointment History</CardTitle>
                        <CardDescription>
                            Your upcoming and past appointments
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-4">
                            {appointments.map((appointment) => (
                                <div key={appointment.id} className="flex items-center justify-between p-4 border rounded-lg">
                                    <div className="flex items-center space-x-4">
                                        <div className="flex-shrink-0">
                                            <div className="w-12 h-12 bg-primary text-primary-foreground rounded-full flex items-center justify-center">
                                                <Calendar className="h-6 w-6" />
                                            </div>
                                        </div>
                                        <div>
                                            <h3 className="font-medium">{appointment.doctor}</h3>
                                            <p className="text-sm text-muted-foreground">{appointment.specialty}</p>
                                            <div className="flex items-center space-x-4 mt-1">
                                                <span className="text-xs text-muted-foreground flex items-center">
                                                    <Calendar className="mr-1 h-3 w-3" />
                                                    {appointment.date}
                                                </span>
                                                <span className="text-xs text-muted-foreground flex items-center">
                                                    <Clock className="mr-1 h-3 w-3" />
                                                    {appointment.time}
                                                </span>
                                                <span className="text-xs text-muted-foreground">
                                                    {appointment.duration}
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
                                            <div className="text-xs text-muted-foreground mt-1">
                                                {appointment.room}
                                            </div>
                                        </div>
                                        <div className="flex items-center space-x-2">
                                            {appointment.status === 'Scheduled' && (
                                                <>
                                                    <Button variant="outline" size="sm">
                                                        <Edit className="h-4 w-4" />
                                                    </Button>
                                                    <Button variant="outline" size="sm">
                                                        <XCircle className="h-4 w-4" />
                                                    </Button>
                                                </>
                                            )}
                                            <Button variant="outline" size="sm">
                                                <Eye className="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </CardContent>
                </Card>

                {/* Quick Actions */}
                <div className="grid gap-4 md:grid-cols-3">
                    <Card>
                        <CardContent className="p-6">
                            <div className="flex items-center">
                                <Calendar className="h-8 w-8 text-blue-600" />
                                <div className="ml-4">
                                    <p className="text-sm font-medium text-muted-foreground">Upcoming</p>
                                    <p className="text-2xl font-bold">1</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-6">
                            <div className="flex items-center">
                                <CheckCircle className="h-8 w-8 text-green-600" />
                                <div className="ml-4">
                                    <p className="text-sm font-medium text-muted-foreground">Completed</p>
                                    <p className="text-2xl font-bold">2</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-6">
                            <div className="flex items-center">
                                <XCircle className="h-8 w-8 text-red-600" />
                                <div className="ml-4">
                                    <p className="text-sm font-medium text-muted-foreground">Cancelled</p>
                                    <p className="text-2xl font-bold">1</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AppLayout>
    );
}
