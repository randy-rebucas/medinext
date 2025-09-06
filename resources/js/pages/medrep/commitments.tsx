import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    Briefcase,
    Calendar,
    CheckCircle,
    Clock,
    AlertCircle,
    Users,
    Target,
    TrendingUp
} from 'lucide-react';

export default function CommitmentTracking() {
    const commitments = [
        {
            id: 1,
            title: 'CardioMax Product Trial',
            doctor: 'Dr. Sarah Johnson',
            specialty: 'Cardiology',
            startDate: '2024-01-15',
            endDate: '2024-02-15',
            status: 'In Progress',
            progress: 65,
            notes: 'Doctor agreed to trial CardioMax with 5 patients'
        },
        {
            id: 2,
            title: 'DermaCare Sample Distribution',
            doctor: 'Dr. Emily Davis',
            specialty: 'Dermatology',
            startDate: '2024-01-10',
            endDate: '2024-01-25',
            status: 'Completed',
            progress: 100,
            notes: 'Successfully distributed 10 samples to patients'
        },
        {
            id: 3,
            title: 'NeuroCalm Presentation',
            doctor: 'Dr. Michael Brown',
            specialty: 'Pediatrics',
            startDate: '2024-01-20',
            endDate: '2024-01-30',
            status: 'Scheduled',
            progress: 0,
            notes: 'Scheduled presentation for next week'
        },
        {
            id: 4,
            title: 'OrthoFlex Follow-up',
            doctor: 'Dr. James Wilson',
            specialty: 'Orthopedics',
            startDate: '2024-01-12',
            endDate: '2024-01-26',
            status: 'Overdue',
            progress: 40,
            notes: 'Need to follow up on initial product discussion'
        }
    ];

    const getStatusColor = (status: string) => {
        switch (status) {
            case 'Completed': return 'default';
            case 'In Progress': return 'secondary';
            case 'Scheduled': return 'default';
            case 'Overdue': return 'destructive';
            default: return 'secondary';
        }
    };

    const getStatusIcon = (status: string) => {
        switch (status) {
            case 'Completed': return <CheckCircle className="h-4 w-4 text-green-600" />;
            case 'In Progress': return <Clock className="h-4 w-4 text-blue-600" />;
            case 'Scheduled': return <Calendar className="h-4 w-4 text-purple-600" />;
            case 'Overdue': return <AlertCircle className="h-4 w-4 text-red-600" />;
            default: return <Clock className="h-4 w-4 text-gray-600" />;
        }
    };

    return (
        <AppLayout>
            <Head title="Commitment Tracking" />

            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Commitment Tracking</h1>
                        <p className="text-muted-foreground">
                            Track and manage commitments with healthcare professionals
                        </p>
                    </div>
                    <Button>
                        <Briefcase className="mr-2 h-4 w-4" />
                        Add Commitment
                    </Button>
                </div>

                {/* Commitment Overview */}
                <div className="grid gap-4 md:grid-cols-4">
                    <Card>
                        <CardContent className="p-6">
                            <div className="flex items-center">
                                <Briefcase className="h-8 w-8 text-blue-600" />
                                <div className="ml-4">
                                    <p className="text-sm font-medium text-muted-foreground">Total Commitments</p>
                                    <p className="text-2xl font-bold">12</p>
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
                                    <p className="text-2xl font-bold">5</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-6">
                            <div className="flex items-center">
                                <Clock className="h-8 w-8 text-yellow-600" />
                                <div className="ml-4">
                                    <p className="text-sm font-medium text-muted-foreground">In Progress</p>
                                    <p className="text-2xl font-bold">4</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-6">
                            <div className="flex items-center">
                                <AlertCircle className="h-8 w-8 text-red-600" />
                                <div className="ml-4">
                                    <p className="text-sm font-medium text-muted-foreground">Overdue</p>
                                    <p className="text-2xl font-bold">1</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Active Commitments</CardTitle>
                        <CardDescription>
                            Track progress and manage your commitments
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-4">
                            {commitments.map((commitment) => (
                                <div key={commitment.id} className="flex items-center justify-between p-4 border rounded-lg">
                                    <div className="flex items-center space-x-4">
                                        <div className="flex-shrink-0">
                                            {getStatusIcon(commitment.status)}
                                        </div>
                                        <div>
                                            <h3 className="font-medium">{commitment.title}</h3>
                                            <p className="text-sm text-muted-foreground">{commitment.doctor} • {commitment.specialty}</p>
                                            <p className="text-sm text-muted-foreground">{commitment.notes}</p>
                                            <div className="flex items-center space-x-4 mt-1">
                                                <span className="text-xs text-muted-foreground">
                                                    Start: {commitment.startDate}
                                                </span>
                                                <span className="text-xs text-muted-foreground">
                                                    End: {commitment.endDate}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div className="flex items-center space-x-4">
                                        <div className="text-right">
                                            <div className="text-sm font-medium">Progress: {commitment.progress}%</div>
                                            <Badge variant={getStatusColor(commitment.status)} className="mt-1">
                                                {commitment.status}
                                            </Badge>
                                        </div>
                                        <div className="w-16">
                                            <div className="bg-gray-200 rounded-full h-2">
                                                <div
                                                    className="bg-primary h-2 rounded-full"
                                                    style={{ width: `${commitment.progress}%` }}
                                                ></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </CardContent>
                </Card>

                {/* Performance Summary */}
                <Card>
                    <CardHeader>
                        <CardTitle>Commitment Performance</CardTitle>
                        <CardDescription>
                            Overall performance metrics for your commitments
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="grid gap-4 md:grid-cols-3">
                            <div className="text-center p-4 border rounded-lg">
                                <div className="text-2xl font-bold text-green-600">92%</div>
                                <div className="text-sm text-muted-foreground">Completion Rate</div>
                            </div>
                            <div className="text-center p-4 border rounded-lg">
                                <div className="text-2xl font-bold text-blue-600">8.5</div>
                                <div className="text-sm text-muted-foreground">Average Days</div>
                            </div>
                            <div className="text-center p-4 border rounded-lg">
                                <div className="text-2xl font-bold text-purple-600">15</div>
                                <div className="text-sm text-muted-foreground">Total Value</div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
