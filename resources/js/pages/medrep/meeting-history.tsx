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
    Clock, 
    Search, 
    Eye, 
    Edit,
    Filter,
    Calendar,
    Stethoscope,
    MapPin,
    FileText
} from 'lucide-react';

export default function MeetingHistory() {
    const meetings = [
        {
            id: 1,
            doctor: 'Dr. Sarah Johnson',
            specialty: 'Cardiology',
            date: '2024-01-15',
            time: '10:30 AM',
            type: 'Product Presentation',
            location: 'Conference Room A',
            duration: '30 minutes',
            status: 'Completed',
            outcome: 'Positive'
        },
        {
            id: 2,
            doctor: 'Dr. Michael Brown',
            specialty: 'Pediatrics',
            date: '2024-01-14',
            time: '02:00 PM',
            type: 'Follow-up Meeting',
            location: 'Doctor\'s Office',
            duration: '20 minutes',
            status: 'Completed',
            outcome: 'Neutral'
        },
        {
            id: 3,
            doctor: 'Dr. Emily Davis',
            specialty: 'Dermatology',
            date: '2024-01-13',
            time: '11:15 AM',
            type: 'Sample Delivery',
            location: 'Clinic Lobby',
            duration: '15 minutes',
            status: 'Completed',
            outcome: 'Positive'
        },
        {
            id: 4,
            doctor: 'Dr. James Wilson',
            specialty: 'Orthopedics',
            date: '2024-01-12',
            time: '09:00 AM',
            type: 'Initial Meeting',
            location: 'Conference Room B',
            duration: '45 minutes',
            status: 'Completed',
            outcome: 'Positive'
        }
    ];

    const getStatusColor = (status: string) => {
        switch (status) {
            case 'Completed': return 'default';
            case 'Scheduled': return 'secondary';
            case 'Cancelled': return 'destructive';
            default: return 'secondary';
        }
    };

    const getOutcomeColor = (outcome: string) => {
        switch (outcome) {
            case 'Positive': return 'default';
            case 'Neutral': return 'secondary';
            case 'Negative': return 'destructive';
            default: return 'secondary';
        }
    };

    return (
        <AppLayout>
            <Head title="Meeting History" />
            
            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Meeting History</h1>
                        <p className="text-muted-foreground">
                            View and manage your meeting history with healthcare professionals
                        </p>
                    </div>
                    <Button>
                        <Calendar className="mr-2 h-4 w-4" />
                        Schedule Meeting
                    </Button>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Meeting Records</CardTitle>
                        <CardDescription>
                            Complete history of all meetings and interactions
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="flex items-center space-x-2 mb-4">
                            <div className="relative flex-1">
                                <Search className="absolute left-2 top-2.5 h-4 w-4 text-muted-foreground" />
                                <Input placeholder="Search meetings..." className="pl-8" />
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
                                    <TableHead>Date & Time</TableHead>
                                    <TableHead>Type</TableHead>
                                    <TableHead>Location</TableHead>
                                    <TableHead>Duration</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead>Outcome</TableHead>
                                    <TableHead className="text-right">Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {meetings.map((meeting) => (
                                    <TableRow key={meeting.id}>
                                        <TableCell>
                                            <div>
                                                <div className="font-medium">{meeting.doctor}</div>
                                                <div className="text-sm text-muted-foreground">{meeting.specialty}</div>
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <div className="flex items-center">
                                                <Calendar className="mr-2 h-4 w-4" />
                                                <div>
                                                    <div className="font-medium">{meeting.date}</div>
                                                    <div className="text-sm text-muted-foreground flex items-center">
                                                        <Clock className="mr-1 h-3 w-3" />
                                                        {meeting.time}
                                                    </div>
                                                </div>
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <Badge variant="outline">{meeting.type}</Badge>
                                        </TableCell>
                                        <TableCell>
                                            <div className="flex items-center">
                                                <MapPin className="mr-1 h-3 w-3" />
                                                {meeting.location}
                                            </div>
                                        </TableCell>
                                        <TableCell>{meeting.duration}</TableCell>
                                        <TableCell>
                                            <Badge variant={getStatusColor(meeting.status)}>
                                                {meeting.status}
                                            </Badge>
                                        </TableCell>
                                        <TableCell>
                                            <Badge variant={getOutcomeColor(meeting.outcome)}>
                                                {meeting.outcome}
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
                                                    <FileText className="h-4 w-4" />
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
