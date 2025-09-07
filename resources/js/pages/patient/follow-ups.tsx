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
    Plus,
    TrendingUp,
    Shield,
    User
} from 'lucide-react';
import { patientDashboard, patientFollowUps } from '@/routes';
import { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Patient Portal',
        href: patientDashboard(),
    },
        {
        title: 'Follow-ups',
        href: patientFollowUps(),
    },
];



interface PatientFollowUpsProps {
    user?: {
        id: number;
        name: string;
        email: string;
        patient_id: string;
        sex: string;
    };
    permissions?: string[];
}

export default function PatientFollowUps({ user, permissions }: PatientFollowUpsProps) {
    const hasPermission = (permission: string) => {
        return permissions?.includes(permission) ?? true;
    };
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
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Follow-ups - Medinext">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&family=instrument-sans:400,500,600" rel="stylesheet" />
            </Head>
            <div className="min-h-screen bg-gradient-to-br from-slate-50 via-white to-blue-50 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900">
                <div className="space-y-6 p-6">
                    {/* Modern Header */}
                    <div className="relative overflow-hidden rounded-2xl bg-gradient-to-r from-rose-600 to-pink-600 p-8 text-white shadow-xl">
                        <div className="absolute inset-0 bg-black/10"></div>
                        <div className="relative flex items-center justify-between">
                            <div>
                                <h1 className="text-3xl font-bold tracking-tight">Follow-ups</h1>
                                <p className="mt-2 text-rose-100">
                                    Track your follow-up appointments and care plans
                                </p>
                            </div>
                            <div className="flex items-center gap-3">
                                <Badge variant="secondary" className="flex items-center gap-1 bg-white/20 text-white border-white/30 hover:bg-white/30">
                                    <Shield className="h-3 w-3" />
                                    Patient
                                </Badge>
                                {user && (
                                    <Badge variant="secondary" className="flex items-center gap-1 bg-white/20 text-white border-white/30 hover:bg-white/30">
                                        <User className="h-3 w-3" />
                                        {user.sex}
                                    </Badge>
                                )}
                                {hasPermission('followups.create') && (
                                    <Button className="bg-white/20 hover:bg-white/30 text-white border-white/30 hover:border-white/40 transition-all duration-200">
                                        <Plus className="mr-2 h-4 w-4" />
                                        Schedule Follow-up
                                    </Button>
                                )}
                            </div>
                        </div>
                        {/* Decorative elements */}
                        <div className="absolute -top-4 -right-4 w-24 h-24 bg-white/10 rounded-full"></div>
                        <div className="absolute -bottom-2 -left-2 w-16 h-16 bg-white/5 rounded-full"></div>
                    </div>

                    {/* Follow-up Summary */}
                    <div className="grid gap-6 md:grid-cols-4">
                        <Card className="relative overflow-hidden border-0 shadow-lg hover:shadow-xl transition-all duration-300 bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20">
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium text-slate-700 dark:text-slate-300">Scheduled</CardTitle>
                                <div className="p-2 bg-blue-500 rounded-lg">
                                    <Calendar className="h-4 w-4 text-white" />
                                </div>
                            </CardHeader>
                            <CardContent>
                                <div className="text-3xl font-bold text-slate-900 dark:text-white">2</div>
                                <div className="flex items-center mt-2">
                                    <TrendingUp className="h-3 w-3 text-blue-500 mr-1" />
                                    <p className="text-xs text-slate-600 dark:text-slate-400">
                                        Upcoming visits
                                    </p>
                                </div>
                            </CardContent>
                        </Card>
                        <Card className="relative overflow-hidden border-0 shadow-lg hover:shadow-xl transition-all duration-300 bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20">
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium text-slate-700 dark:text-slate-300">Completed</CardTitle>
                                <div className="p-2 bg-green-500 rounded-lg">
                                    <CheckCircle className="h-4 w-4 text-white" />
                                </div>
                            </CardHeader>
                            <CardContent>
                                <div className="text-3xl font-bold text-slate-900 dark:text-white">1</div>
                                <div className="flex items-center mt-2">
                                    <CheckCircle className="h-3 w-3 text-green-500 mr-1" />
                                    <p className="text-xs text-slate-600 dark:text-slate-400">
                                        Finished visits
                                    </p>
                                </div>
                            </CardContent>
                        </Card>
                        <Card className="relative overflow-hidden border-0 shadow-lg hover:shadow-xl transition-all duration-300 bg-gradient-to-br from-yellow-50 to-yellow-100 dark:from-yellow-900/20 dark:to-yellow-800/20">
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium text-slate-700 dark:text-slate-300">Pending</CardTitle>
                                <div className="p-2 bg-yellow-500 rounded-lg">
                                    <AlertCircle className="h-4 w-4 text-white" />
                                </div>
                            </CardHeader>
                            <CardContent>
                                <div className="text-3xl font-bold text-slate-900 dark:text-white">1</div>
                                <div className="flex items-center mt-2">
                                    <AlertCircle className="h-3 w-3 text-yellow-500 mr-1" />
                                    <p className="text-xs text-slate-600 dark:text-slate-400">
                                        Awaiting scheduling
                                    </p>
                                </div>
                            </CardContent>
                        </Card>
                        <Card className="relative overflow-hidden border-0 shadow-lg hover:shadow-xl transition-all duration-300 bg-gradient-to-br from-red-50 to-red-100 dark:from-red-900/20 dark:to-red-800/20">
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium text-slate-700 dark:text-slate-300">High Priority</CardTitle>
                                <div className="p-2 bg-red-500 rounded-lg">
                                    <Heart className="h-4 w-4 text-white" />
                                </div>
                            </CardHeader>
                            <CardContent>
                                <div className="text-3xl font-bold text-slate-900 dark:text-white">1</div>
                                <div className="flex items-center mt-2">
                                    <Heart className="h-3 w-3 text-red-500 mr-1" />
                                    <p className="text-xs text-slate-600 dark:text-slate-400">
                                        Urgent care needed
                                    </p>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    {/* Follow-up Appointments */}
                    <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                        <CardHeader>
                            <CardTitle className="text-lg font-semibold text-slate-900 dark:text-white">Follow-up Appointments</CardTitle>
                            <CardDescription className="text-slate-600 dark:text-slate-300">
                                Your scheduled follow-up appointments and care plans
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                {followUps.map((followUp) => (
                                    <div key={followUp.id} className="flex items-center justify-between p-4 border border-slate-200 dark:border-slate-700 rounded-lg hover:shadow-md transition-all duration-200 bg-slate-50 dark:bg-slate-700/50">
                                        <div className="flex items-center space-x-4">
                                            <div className="flex-shrink-0">
                                                <div className="p-2 bg-rose-100 dark:bg-rose-900/20 rounded-full">
                                                    <Heart className="h-5 w-5 text-rose-600 dark:text-rose-400" />
                                                </div>
                                            </div>
                                            <div>
                                                <h3 className="font-medium text-slate-900 dark:text-white">{followUp.type}</h3>
                                                <p className="text-sm text-slate-600 dark:text-slate-400">{followUp.notes}</p>
                                                <div className="flex items-center space-x-4 mt-1">
                                                    <span className="text-xs text-slate-600 dark:text-slate-400 flex items-center">
                                                        <Stethoscope className="mr-1 h-3 w-3" />
                                                        {followUp.doctor}
                                                    </span>
                                                    <span className="text-xs text-slate-600 dark:text-slate-400 flex items-center">
                                                        <Calendar className="mr-1 h-3 w-3" />
                                                        {followUp.scheduledDate}
                                                    </span>
                                                    <span className="text-xs text-slate-600 dark:text-slate-400 flex items-center">
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
                    <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                        <CardHeader>
                            <CardTitle className="text-lg font-semibold text-slate-900 dark:text-white">Care Plan</CardTitle>
                            <CardDescription className="text-slate-600 dark:text-slate-300">
                                Your ongoing care plan and health goals
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                <div className="p-4 border border-slate-200 dark:border-slate-700 rounded-lg bg-blue-50 dark:bg-blue-900/20 hover:shadow-md transition-all duration-200">
                                    <h3 className="font-medium mb-2 text-slate-900 dark:text-white">Blood Pressure Management</h3>
                                    <p className="text-sm text-slate-600 dark:text-slate-400 mb-2">
                                        Continue taking Lisinopril 10mg daily. Monitor blood pressure weekly and report any significant changes.
                                    </p>
                                    <div className="flex items-center space-x-2">
                                        <Badge variant="outline" className="border-slate-200 dark:border-slate-700">Ongoing</Badge>
                                        <Badge variant="default" className="bg-rose-600 hover:bg-rose-700">High Priority</Badge>
                                    </div>
                                </div>
                                <div className="p-4 border border-slate-200 dark:border-slate-700 rounded-lg bg-green-50 dark:bg-green-900/20 hover:shadow-md transition-all duration-200">
                                    <h3 className="font-medium mb-2 text-slate-900 dark:text-white">Diabetes Management</h3>
                                    <p className="text-sm text-slate-600 dark:text-slate-400 mb-2">
                                        Maintain Metformin 500mg twice daily. Monitor blood glucose levels and maintain healthy diet.
                                    </p>
                                    <div className="flex items-center space-x-2">
                                        <Badge variant="outline" className="border-slate-200 dark:border-slate-700">Ongoing</Badge>
                                        <Badge variant="default" className="bg-rose-600 hover:bg-rose-700">Medium Priority</Badge>
                                    </div>
                                </div>
                                <div className="p-4 border border-slate-200 dark:border-slate-700 rounded-lg bg-yellow-50 dark:bg-yellow-900/20 hover:shadow-md transition-all duration-200">
                                    <h3 className="font-medium mb-2 text-slate-900 dark:text-white">Knee Rehabilitation</h3>
                                    <p className="text-sm text-slate-600 dark:text-slate-400 mb-2">
                                        Continue physical therapy exercises. Avoid high-impact activities and use knee support when needed.
                                    </p>
                                    <div className="flex items-center space-x-2">
                                        <Badge variant="outline" className="border-slate-200 dark:border-slate-700">Ongoing</Badge>
                                        <Badge variant="secondary">Low Priority</Badge>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AppLayout>
    );
}
