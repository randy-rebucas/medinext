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
    FileText, 
    Search, 
    Eye, 
    Edit,
    Filter,
    Calendar,
    User,
    Stethoscope,
    Clock
} from 'lucide-react';

export default function Encounters() {
    const encounters = [
        {
            id: 1,
            patient: 'John Doe',
            doctor: 'Dr. Sarah Johnson',
            date: '2024-01-15',
            time: '09:00 AM',
            type: 'Consultation',
            status: 'Completed',
            chiefComplaint: 'Chest pain',
            room: 'Room 101'
        },
        {
            id: 2,
            patient: 'Jane Smith',
            doctor: 'Dr. Michael Brown',
            date: '2024-01-15',
            time: '10:30 AM',
            type: 'Follow-up',
            status: 'In Progress',
            chiefComplaint: 'Follow-up visit',
            room: 'Room 102'
        },
        {
            id: 3,
            patient: 'Bob Johnson',
            doctor: 'Dr. Emily Davis',
            date: '2024-01-15',
            time: '11:15 AM',
            type: 'Check-up',
            status: 'Scheduled',
            chiefComplaint: 'Annual physical',
            room: 'Room 103'
        },
        {
            id: 4,
            patient: 'Alice Brown',
            doctor: 'Dr. James Wilson',
            date: '2024-01-15',
            time: '02:00 PM',
            type: 'Consultation',
            status: 'Completed',
            chiefComplaint: 'Knee pain',
            room: 'Room 104'
        }
    ];

    const getStatusColor = (status: string) => {
        switch (status) {
            case 'Completed': return 'default';
            case 'In Progress': return 'secondary';
            case 'Scheduled': return 'default';
            case 'Cancelled': return 'destructive';
            default: return 'secondary';
        }
    };

    return (
        <AppLayout>
            <Head title="Encounters" />
            
            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Encounters</h1>
                        <p className="text-muted-foreground">
                            Manage patient encounters and medical records
                        </p>
                    </div>
                    <Button>
                        <FileText className="mr-2 h-4 w-4" />
                        New Encounter
                    </Button>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Patient Encounters</CardTitle>
                        <CardDescription>
                            View and manage all patient encounters
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="flex items-center space-x-2 mb-4">
                            <div className="relative flex-1">
                                <Search className="absolute left-2 top-2.5 h-4 w-4 text-muted-foreground" />
                                <Input placeholder="Search encounters..." className="pl-8" />
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
                                    <TableHead>Date & Time</TableHead>
                                    <TableHead>Type</TableHead>
                                    <TableHead>Chief Complaint</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead>Room</TableHead>
                                    <TableHead className="text-right">Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {encounters.map((encounter) => (
                                    <TableRow key={encounter.id}>
                                        <TableCell>
                                            <div className="flex items-center">
                                                <User className="mr-2 h-4 w-4" />
                                                {encounter.patient}
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <div className="flex items-center">
                                                <Stethoscope className="mr-2 h-4 w-4" />
                                                {encounter.doctor}
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <div className="flex items-center">
                                                <Calendar className="mr-2 h-4 w-4" />
                                                <div>
                                                    <div className="font-medium">{encounter.date}</div>
                                                    <div className="text-sm text-muted-foreground flex items-center">
                                                        <Clock className="mr-1 h-3 w-3" />
                                                        {encounter.time}
                                                    </div>
                                                </div>
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <Badge variant="outline">{encounter.type}</Badge>
                                        </TableCell>
                                        <TableCell>{encounter.chiefComplaint}</TableCell>
                                        <TableCell>
                                            <Badge variant={getStatusColor(encounter.status)}>
                                                {encounter.status}
                                            </Badge>
                                        </TableCell>
                                        <TableCell>{encounter.room}</TableCell>
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
