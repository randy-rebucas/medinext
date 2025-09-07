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
    MapPin,
    FileText,
    Building2,
    Shield
} from 'lucide-react';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Meeting History',
        href: '/medrep/meeting-history',
    },
];

interface MeetingHistoryProps {
    user?: {
        id: number;
        name: string;
        email: string;
        role: string;
        company_id?: number;
        company?: {
            id: number;
            name: string;
        };
    };
}

export default function MeetingHistory({ user }: MeetingHistoryProps = {}) {
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
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Meeting History - Medinext">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&family=instrument-sans:400,500,600" rel="stylesheet" />
            </Head>
            <div className="min-h-screen bg-gradient-to-br from-slate-50 via-white to-blue-50 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900">
                <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
                    {/* Modern Header */}
                    <div className="relative overflow-hidden rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 p-8 text-white shadow-xl">
                        <div className="absolute inset-0 bg-black/10"></div>
                        <div className="relative flex items-center justify-between">
                            <div>
                                <h1 className="text-3xl font-bold tracking-tight">
                                    Meeting History
                                </h1>
                                <p className="mt-2 text-emerald-100">
                                    View and manage your meeting history with healthcare professionals
                                </p>
                            </div>
                            <div className="flex items-center gap-3">
                                <Badge variant="secondary" className="flex items-center gap-1 bg-white/20 text-white border-white/30 hover:bg-white/30">
                                    <Shield className="h-3 w-3" />
                                    Medical Rep
                                </Badge>
                                {user?.company && (
                                    <Badge variant="secondary" className="flex items-center gap-1 bg-white/20 text-white border-white/30 hover:bg-white/30">
                                        <Building2 className="h-3 w-3" />
                                        {user.company.name}
                                    </Badge>
                                )}
                            </div>
                        </div>
                        {/* Decorative elements */}
                        <div className="absolute -top-4 -right-4 w-24 h-24 bg-white/10 rounded-full"></div>
                        <div className="absolute -bottom-2 -left-2 w-16 h-16 bg-white/5 rounded-full"></div>
                    </div>

                    {/* Action Button */}
                    <div className="flex justify-end">
                        <Button className="bg-emerald-600 hover:bg-emerald-700 text-white">
                            <Calendar className="mr-2 h-4 w-4" />
                            Schedule Meeting
                        </Button>
                    </div>

                    <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                        <CardHeader>
                            <CardTitle className="text-lg font-semibold text-slate-900 dark:text-white">Meeting Records</CardTitle>
                            <CardDescription className="text-slate-600 dark:text-slate-300">
                                Complete history of all meetings and interactions
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="flex items-center space-x-2 mb-4">
                                <div className="relative flex-1">
                                    <Search className="absolute left-2 top-2.5 h-4 w-4 text-slate-400" />
                                    <Input placeholder="Search meetings..." className="pl-8 border-slate-200 dark:border-slate-700" />
                                </div>
                                <Button variant="outline" className="border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800">
                                    <Filter className="mr-2 h-4 w-4" />
                                    Filter
                                </Button>
                            </div>

                            <Table>
                                <TableHeader>
                                    <TableRow className="border-slate-200 dark:border-slate-700">
                                        <TableHead className="text-slate-700 dark:text-slate-300">Doctor</TableHead>
                                        <TableHead className="text-slate-700 dark:text-slate-300">Date & Time</TableHead>
                                        <TableHead className="text-slate-700 dark:text-slate-300">Type</TableHead>
                                        <TableHead className="text-slate-700 dark:text-slate-300">Location</TableHead>
                                        <TableHead className="text-slate-700 dark:text-slate-300">Duration</TableHead>
                                        <TableHead className="text-slate-700 dark:text-slate-300">Status</TableHead>
                                        <TableHead className="text-slate-700 dark:text-slate-300">Outcome</TableHead>
                                        <TableHead className="text-right text-slate-700 dark:text-slate-300">Actions</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {meetings.map((meeting) => (
                                        <TableRow key={meeting.id} className="border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/50">
                                            <TableCell>
                                                <div>
                                                    <div className="font-medium text-slate-900 dark:text-white">{meeting.doctor}</div>
                                                    <div className="text-sm text-slate-500 dark:text-slate-400">{meeting.specialty}</div>
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <div className="flex items-center">
                                                    <Calendar className="mr-2 h-4 w-4 text-slate-400" />
                                                    <div>
                                                        <div className="font-medium text-slate-900 dark:text-white">{meeting.date}</div>
                                                        <div className="text-sm text-slate-500 dark:text-slate-400 flex items-center">
                                                            <Clock className="mr-1 h-3 w-3" />
                                                            {meeting.time}
                                                        </div>
                                                    </div>
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <Badge variant="outline" className="border-slate-200 dark:border-slate-700">{meeting.type}</Badge>
                                            </TableCell>
                                            <TableCell>
                                                <div className="flex items-center text-slate-600 dark:text-slate-400">
                                                    <MapPin className="mr-1 h-3 w-3" />
                                                    {meeting.location}
                                                </div>
                                            </TableCell>
                                            <TableCell className="text-slate-900 dark:text-white">{meeting.duration}</TableCell>
                                            <TableCell>
                                                <Badge variant={getStatusColor(meeting.status)} className="border-0">
                                                    {meeting.status}
                                                </Badge>
                                            </TableCell>
                                            <TableCell>
                                                <Badge variant={getOutcomeColor(meeting.outcome)} className="border-0">
                                                    {meeting.outcome}
                                                </Badge>
                                            </TableCell>
                                            <TableCell className="text-right">
                                                <div className="flex items-center justify-end space-x-2">
                                                    <Button variant="ghost" size="sm" className="hover:bg-slate-100 dark:hover:bg-slate-700">
                                                        <Eye className="h-4 w-4" />
                                                    </Button>
                                                    <Button variant="ghost" size="sm" className="hover:bg-slate-100 dark:hover:bg-slate-700">
                                                        <Edit className="h-4 w-4" />
                                                    </Button>
                                                    <Button variant="ghost" size="sm" className="hover:bg-slate-100 dark:hover:bg-slate-700">
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
            </div>
        </AppLayout>
    );
}
