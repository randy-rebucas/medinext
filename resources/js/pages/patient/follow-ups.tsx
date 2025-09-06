import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { 
    Heart, 
    Calendar,
    Stethoscope,
    Clock,
    CheckCircle,
    AlertCircle,
    Plus
} from 'lucide-react';

export default function PatientFollowUps() {
    const followUps = [
        {
            id: 1,
            type: 'Medication Review',
            doctor: 'Dr. Michael Brown',
            scheduledDate: '2024-02-15',
            time: '10:00 AM',
            status: 'Scheduled',
            priority: 'High',
            notes: 'Review blood pressure medication effectiveness'
        },
        {
            id: 2,
            type: 'Lab Follow-up',
            doctor: 'Dr. Sarah Johnson',
            scheduledDate: '2024-02-20',
            time: '02:30 PM',
            status: 'Scheduled',
            priority: 'Medium',
            notes: 'Follow-up on thyroid function test results'
        },
        {
            id: 3,
            type: 'Physical Therapy',
            doctor: 'Dr. James Wilson',
            scheduledDate: '2024-02-10',
            time: '11:15 AM',
            status: 'Completed',
            priority: 'Medium',
            notes: 'Knee rehabilitation progress check'
        },
        {
            id: 4,
            type: 'Annual Check-up',
            doctor: 'Dr. Emily Davis',
            scheduledDate: '2024-03-01',
            time: '09:30 AM',
            status: 'Pending',
            priority: 'Low',
            notes: 'Annual physical examination and health assessment'
        }
    ];

    const getStatusColor = (status: string) => {
        switch (status) {
            case 'Scheduled': return 'default';
            case 'Completed': return 'default';
            case 'Pending': return 'secondary';
            case 'Cancelled': return 'destructive';
            default: return 'secondary';
        }
    };

    const getPriorityColor = (priority: string) => {
        switch (priority) {
            case 'High': return 'destructive';
            case 'Medium': return 'default';
            case 'Low': return 'secondary';
            default: return 'secondary';
        }
    };

    return (
        <AppLayout>
            <Head title="Follow-ups" />
            
            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Follow-ups</h1>
                        <p className="text-muted-foreground">
                            Track your follow-up appointments and care plans
                        </p>
                    </div>
                    <Button>
                        <Plus className="mr-2 h-4 w-4" />
                        Schedule Follow-up
                    </Button>
                </div>

                {/* Follow-up Summary */}
                <div className="grid gap-4 md:grid-cols-4">
                    <Card>
                        <CardContent className="p-6">
                            <div className="flex items-center">
                                <Calendar className="h-8 w-8 text-blue-600" />
                                <div className="ml-4">
                                    <p className="text-sm font-medium text-muted-foreground">Scheduled</p>
                                    <p className="text-2xl font-bold">2</p>
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
                                    <p className="text-2xl font-bold">1</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-6">
                            <div className="flex items-center">
                                <AlertCircle className="h-8 w-8 text-yellow-600" />
                                <div className="ml-4">
                                    <p className="text-sm font-medium text-muted-foreground">Pending</p>
                                    <p className="text-2xl font-bold">1</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-6">
                            <div className="flex items-center">
                                <Heart className="h-8 w-8 text-red-600" />
                                <div className="ml-4">
                                    <p className="text-sm font-medium text-muted-foreground">High Priority</p>
                                    <p className="text-2xl font-bold">1</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Follow-up Appointments</CardTitle>
                        <CardDescription>
                            Your scheduled follow-up appointments and care plans
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-4">
                            {followUps.map((followUp) => (
                                <div key={followUp.id} className="flex items-center justify-between p-4 border rounded-lg">
                                    <div className="flex items-center space-x-4">
                                        <div className="flex-shrink-0">
                                            <Heart className="h-5 w-5 text-red-600" />
                                        </div>
                                        <div>
                                            <h3 className="font-medium">{followUp.type}</h3>
                                            <p className="text-sm text-muted-foreground">{followUp.notes}</p>
                                            <div className="flex items-center space-x-4 mt-1">
                                                <span className="text-xs text-muted-foreground flex items-center">
                                                    <Stethoscope className="mr-1 h-3 w-3" />
                                                    {followUp.doctor}
                                                </span>
                                                <span className="text-xs text-muted-foreground flex items-center">
                                                    <Calendar className="mr-1 h-3 w-3" />
                                                    {followUp.scheduledDate}
                                                </span>
                                                <span className="text-xs text-muted-foreground flex items-center">
                                                    <Clock className="mr-1 h-3 w-3" />
                                                    {followUp.time}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div className="flex items-center space-x-2">
                                        <Badge variant={getPriorityColor(followUp.priority)}>
                                            {followUp.priority}
                                        </Badge>
                                        <Badge variant={getStatusColor(followUp.status)}>
                                            {followUp.status}
                                        </Badge>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </CardContent>
                </Card>

                {/* Care Plan */}
                <Card>
                    <CardHeader>
                        <CardTitle>Care Plan</CardTitle>
                        <CardDescription>
                            Your ongoing care plan and health goals
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-4">
                            <div className="p-4 border rounded-lg bg-blue-50 dark:bg-blue-900/20">
                                <h3 className="font-medium mb-2">Blood Pressure Management</h3>
                                <p className="text-sm text-muted-foreground mb-2">
                                    Continue taking Lisinopril 10mg daily. Monitor blood pressure weekly and report any significant changes.
                                </p>
                                <div className="flex items-center space-x-2">
                                    <Badge variant="outline">Ongoing</Badge>
                                    <Badge variant="default">High Priority</Badge>
                                </div>
                            </div>
                            <div className="p-4 border rounded-lg bg-green-50 dark:bg-green-900/20">
                                <h3 className="font-medium mb-2">Diabetes Management</h3>
                                <p className="text-sm text-muted-foreground mb-2">
                                    Maintain Metformin 500mg twice daily. Monitor blood glucose levels and maintain healthy diet.
                                </p>
                                <div className="flex items-center space-x-2">
                                    <Badge variant="outline">Ongoing</Badge>
                                    <Badge variant="default">Medium Priority</Badge>
                                </div>
                            </div>
                            <div className="p-4 border rounded-lg bg-yellow-50 dark:bg-yellow-900/20">
                                <h3 className="font-medium mb-2">Knee Rehabilitation</h3>
                                <p className="text-sm text-muted-foreground mb-2">
                                    Continue physical therapy exercises. Avoid high-impact activities and use knee support when needed.
                                </p>
                                <div className="flex items-center space-x-2">
                                    <Badge variant="outline">Ongoing</Badge>
                                    <Badge variant="secondary">Low Priority</Badge>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
