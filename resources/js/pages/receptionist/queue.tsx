import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { 
    Clock, 
    User, 
    Stethoscope,
    CheckCircle,
    XCircle,
    AlertCircle,
    Play,
    Pause
} from 'lucide-react';

export default function QueueManagement() {
    const queue = [
        {
            id: 1,
            patient: 'John Doe',
            doctor: 'Dr. Sarah Johnson',
            appointmentTime: '09:00 AM',
            checkInTime: '08:45 AM',
            waitTime: '15 min',
            status: 'Waiting',
            priority: 'Normal',
            room: 'Room 101'
        },
        {
            id: 2,
            patient: 'Jane Smith',
            doctor: 'Dr. Michael Brown',
            appointmentTime: '10:30 AM',
            checkInTime: '10:25 AM',
            waitTime: '5 min',
            status: 'In Progress',
            priority: 'Normal',
            room: 'Room 102'
        },
        {
            id: 3,
            patient: 'Bob Johnson',
            doctor: 'Dr. Emily Davis',
            appointmentTime: '11:15 AM',
            checkInTime: '11:10 AM',
            waitTime: '5 min',
            status: 'Waiting',
            priority: 'High',
            room: 'Room 103'
        },
        {
            id: 4,
            patient: 'Alice Brown',
            doctor: 'Dr. James Wilson',
            appointmentTime: '02:00 PM',
            checkInTime: '01:55 PM',
            waitTime: '5 min',
            status: 'Completed',
            priority: 'Normal',
            room: 'Room 104'
        }
    ];

    const getStatusColor = (status: string) => {
        switch (status) {
            case 'Waiting': return 'default';
            case 'In Progress': return 'secondary';
            case 'Completed': return 'default';
            case 'Cancelled': return 'destructive';
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
        <AppLayout>
            <Head title="Queue Management" />
            
            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Queue Management</h1>
                        <p className="text-muted-foreground">
                            Manage patient queue and waiting times
                        </p>
                    </div>
                    <div className="flex space-x-2">
                        <Button variant="outline">
                            <Pause className="mr-2 h-4 w-4" />
                            Pause Queue
                        </Button>
                        <Button>
                            <Play className="mr-2 h-4 w-4" />
                            Start Queue
                        </Button>
                    </div>
                </div>

                {/* Queue Statistics */}
                <div className="grid gap-4 md:grid-cols-4">
                    <Card>
                        <CardContent className="p-6">
                            <div className="flex items-center">
                                <Clock className="h-8 w-8 text-blue-600" />
                                <div className="ml-4">
                                    <p className="text-sm font-medium text-muted-foreground">Total in Queue</p>
                                    <p className="text-2xl font-bold">12</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-6">
                            <div className="flex items-center">
                                <User className="h-8 w-8 text-green-600" />
                                <div className="ml-4">
                                    <p className="text-sm font-medium text-muted-foreground">Waiting</p>
                                    <p className="text-2xl font-bold">8</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-6">
                            <div className="flex items-center">
                                <Stethoscope className="h-8 w-8 text-purple-600" />
                                <div className="ml-4">
                                    <p className="text-sm font-medium text-muted-foreground">In Progress</p>
                                    <p className="text-2xl font-bold">3</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-6">
                            <div className="flex items-center">
                                <CheckCircle className="h-8 w-8 text-orange-600" />
                                <div className="ml-4">
                                    <p className="text-sm font-medium text-muted-foreground">Completed</p>
                                    <p className="text-2xl font-bold">1</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Patient Queue</CardTitle>
                        <CardDescription>
                            Current patient queue status and management
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-4">
                            {queue.map((patient) => (
                                <div key={patient.id} className="flex items-center justify-between p-4 border rounded-lg">
                                    <div className="flex items-center space-x-4">
                                        <div className="flex-shrink-0">
                                            <div className="w-10 h-10 bg-primary text-primary-foreground rounded-full flex items-center justify-center text-sm font-medium">
                                                {patient.id}
                                            </div>
                                        </div>
                                        <div>
                                            <h3 className="font-medium">{patient.patient}</h3>
                                            <p className="text-sm text-muted-foreground">{patient.doctor}</p>
                                            <div className="flex items-center space-x-4 mt-1">
                                                <span className="text-xs text-muted-foreground">
                                                    Appt: {patient.appointmentTime}
                                                </span>
                                                <span className="text-xs text-muted-foreground">
                                                    Check-in: {patient.checkInTime}
                                                </span>
                                                <span className="text-xs text-muted-foreground">
                                                    Wait: {patient.waitTime}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div className="flex items-center space-x-4">
                                        <div className="text-right">
                                            <div className="text-sm font-medium">{patient.room}</div>
                                            <div className="flex items-center space-x-2 mt-1">
                                                <Badge variant={getStatusColor(patient.status)}>
                                                    {patient.status}
                                                </Badge>
                                                <Badge variant={getPriorityColor(patient.priority)}>
                                                    {patient.priority}
                                                </Badge>
                                            </div>
                                        </div>
                                        <div className="flex items-center space-x-2">
                                            {patient.status === 'Waiting' && (
                                                <Button variant="outline" size="sm">
                                                    <CheckCircle className="h-4 w-4" />
                                                </Button>
                                            )}
                                            {patient.status === 'In Progress' && (
                                                <Button variant="outline" size="sm">
                                                    <XCircle className="h-4 w-4" />
                                                </Button>
                                            )}
                                            <Button variant="outline" size="sm">
                                                <AlertCircle className="h-4 w-4" />
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
