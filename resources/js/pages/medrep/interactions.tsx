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
    MessageSquare, 
    Search, 
    Eye, 
    Edit,
    Filter,
    Calendar,
    User,
    Stethoscope,
    Clock,
    FileText
} from 'lucide-react';

export default function DoctorInteractions() {
    const interactions = [
        {
            id: 1,
            doctor: 'Dr. Sarah Johnson',
            specialty: 'Cardiology',
            date: '2024-01-15',
            time: '10:30 AM',
            type: 'Product Presentation',
            status: 'Completed',
            outcome: 'Positive',
            notes: 'Discussed CardioMax benefits, doctor showed interest in samples'
        },
        {
            id: 2,
            doctor: 'Dr. Michael Brown',
            specialty: 'Pediatrics',
            date: '2024-01-14',
            time: '02:00 PM',
            type: 'Follow-up Call',
            status: 'Completed',
            outcome: 'Neutral',
            notes: 'Followed up on previous meeting, doctor requested more information'
        },
        {
            id: 3,
            doctor: 'Dr. Emily Davis',
            specialty: 'Dermatology',
            date: '2024-01-13',
            time: '11:15 AM',
            type: 'Sample Delivery',
            status: 'Completed',
            outcome: 'Positive',
            notes: 'Delivered DermaCare samples, doctor was very interested'
        },
        {
            id: 4,
            doctor: 'Dr. James Wilson',
            specialty: 'Orthopedics',
            date: '2024-01-12',
            time: '09:00 AM',
            type: 'Initial Meeting',
            status: 'Scheduled',
            outcome: 'Pending',
            notes: 'First meeting scheduled to discuss OrthoFlex product line'
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
            case 'Pending': return 'secondary';
            default: return 'secondary';
        }
    };

    return (
        <AppLayout>
            <Head title="Doctor Interactions" />
            
            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Doctor Interactions</h1>
                        <p className="text-muted-foreground">
                            Track and manage your interactions with healthcare professionals
                        </p>
                    </div>
                    <Button>
                        <MessageSquare className="mr-2 h-4 w-4" />
                        Log Interaction
                    </Button>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Interaction History</CardTitle>
                        <CardDescription>
                            View and manage all doctor interactions
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="flex items-center space-x-2 mb-4">
                            <div className="relative flex-1">
                                <Search className="absolute left-2 top-2.5 h-4 w-4 text-muted-foreground" />
                                <Input placeholder="Search interactions..." className="pl-8" />
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
                                    <TableHead>Status</TableHead>
                                    <TableHead>Outcome</TableHead>
                                    <TableHead>Notes</TableHead>
                                    <TableHead className="text-right">Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {interactions.map((interaction) => (
                                    <TableRow key={interaction.id}>
                                        <TableCell>
                                            <div>
                                                <div className="font-medium">{interaction.doctor}</div>
                                                <div className="text-sm text-muted-foreground">{interaction.specialty}</div>
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <div className="flex items-center">
                                                <Calendar className="mr-2 h-4 w-4" />
                                                <div>
                                                    <div className="font-medium">{interaction.date}</div>
                                                    <div className="text-sm text-muted-foreground flex items-center">
                                                        <Clock className="mr-1 h-3 w-3" />
                                                        {interaction.time}
                                                    </div>
                                                </div>
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <Badge variant="outline">{interaction.type}</Badge>
                                        </TableCell>
                                        <TableCell>
                                            <Badge variant={getStatusColor(interaction.status)}>
                                                {interaction.status}
                                            </Badge>
                                        </TableCell>
                                        <TableCell>
                                            <Badge variant={getOutcomeColor(interaction.outcome)}>
                                                {interaction.outcome}
                                            </Badge>
                                        </TableCell>
                                        <TableCell>
                                            <div className="max-w-xs truncate">
                                                {interaction.notes}
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
