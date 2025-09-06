import { PlaceholderPattern } from '@/components/ui/placeholder-pattern';
import AppLayout from '@/layouts/app-layout';
import { doctorDashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Calendar, Clock, Users, FileText, Pill, Stethoscope, AlertCircle, CheckCircle } from 'lucide-react';
import { Link } from '@inertiajs/react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Doctor Dashboard',
        href: doctorDashboard().url,
    },
];

interface DoctorDashboardProps {
    stats: {
        todayAppointments: number;
        upcomingAppointments: number;
        totalPatients: number;
        pendingPrescriptions: number;
        recentAppointments: Array<{
            id: number;
            patient_name: string;
            start_at: string;
            status: string;
            type: string;
        }>;
        recentPrescriptions: Array<{
            id: number;
            patient_name: string;
            prescription_number: string;
            status: string;
            issued_at: string;
        }>;
    };
}

export default function DoctorDashboard({ stats }: DoctorDashboardProps) {
    const getStatusColor = (status: string) => {
        switch (status) {
            case 'scheduled':
            case 'confirmed':
                return 'bg-blue-100 text-blue-800';
            case 'in_progress':
            case 'checked_in':
                return 'bg-yellow-100 text-yellow-800';
            case 'completed':
                return 'bg-green-100 text-green-800';
            case 'cancelled':
                return 'bg-red-100 text-red-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    };

    const getPrescriptionStatusColor = (status: string) => {
        switch (status) {
            case 'active':
                return 'bg-green-100 text-green-800';
            case 'draft':
                return 'bg-yellow-100 text-yellow-800';
            case 'dispensed':
                return 'bg-blue-100 text-blue-800';
            case 'expired':
                return 'bg-red-100 text-red-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Doctor Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
                {/* Stats Overview */}
                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Today's Appointments</CardTitle>
                            <Calendar className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.todayAppointments}</div>
                            <p className="text-xs text-muted-foreground">
                                Scheduled for today
                            </p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Upcoming Appointments</CardTitle>
                            <Clock className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.upcomingAppointments}</div>
                            <p className="text-xs text-muted-foreground">
                                Next 7 days
                            </p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total Patients</CardTitle>
                            <Users className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.totalPatients}</div>
                            <p className="text-xs text-muted-foreground">
                                Active patients
                            </p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Pending Prescriptions</CardTitle>
                            <Pill className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.pendingPrescriptions}</div>
                            <p className="text-xs text-muted-foreground">
                                Awaiting review
                            </p>
                        </CardContent>
                    </Card>
                </div>

                {/* Quick Actions */}
                <Card>
                    <CardHeader>
                        <CardTitle>Quick Actions</CardTitle>
                        <CardDescription>
                            Common tasks and shortcuts for your daily workflow
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                            <Button asChild variant="outline" className="h-20 flex-col gap-2">
                                <Link href="/doctor/appointments">
                                    <Calendar className="h-6 w-6" />
                                    <span>Manage Appointments</span>
                                </Link>
                            </Button>
                            <Button asChild variant="outline" className="h-20 flex-col gap-2">
                                <Link href="/doctor/medical-records">
                                    <FileText className="h-6 w-6" />
                                    <span>Medical Records</span>
                                </Link>
                            </Button>
                            <Button asChild variant="outline" className="h-20 flex-col gap-2">
                                <Link href="/doctor/prescriptions">
                                    <Pill className="h-6 w-6" />
                                    <span>Prescriptions</span>
                                </Link>
                            </Button>
                            <Button asChild variant="outline" className="h-20 flex-col gap-2">
                                <Link href="/doctor/advice">
                                    <Stethoscope className="h-6 w-6" />
                                    <span>Medical Advice</span>
                                </Link>
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                <div className="grid gap-6 md:grid-cols-2">
                    {/* Recent Appointments */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Recent Appointments</CardTitle>
                            <CardDescription>
                                Your latest patient appointments
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                {stats.recentAppointments.length > 0 ? (
                                    stats.recentAppointments.map((appointment) => (
                                        <div key={appointment.id} className="flex items-center justify-between">
                                            <div className="space-y-1">
                                                <p className="text-sm font-medium leading-none">
                                                    {appointment.patient_name}
                                                </p>
                                                <p className="text-sm text-muted-foreground">
                                                    {new Date(appointment.start_at).toLocaleDateString()} at{' '}
                                                    {new Date(appointment.start_at).toLocaleTimeString([], { 
                                                        hour: '2-digit', 
                                                        minute: '2-digit' 
                                                    })}
                                                </p>
                                                <p className="text-xs text-muted-foreground">
                                                    {appointment.type}
                                                </p>
                                            </div>
                                            <Badge className={getStatusColor(appointment.status)}>
                                                {appointment.status.replace('_', ' ')}
                                            </Badge>
                                        </div>
                                    ))
                                ) : (
                                    <div className="text-center py-6">
                                        <Calendar className="h-12 w-12 text-muted-foreground mx-auto mb-2" />
                                        <p className="text-sm text-muted-foreground">No recent appointments</p>
                                    </div>
                                )}
                            </div>
                            <div className="mt-4">
                                <Button asChild variant="outline" size="sm" className="w-full">
                                    <Link href="/doctor/appointments">
                                        View All Appointments
                                    </Link>
                                </Button>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Recent Prescriptions */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Recent Prescriptions</CardTitle>
                            <CardDescription>
                                Your latest prescription orders
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                {stats.recentPrescriptions.length > 0 ? (
                                    stats.recentPrescriptions.map((prescription) => (
                                        <div key={prescription.id} className="flex items-center justify-between">
                                            <div className="space-y-1">
                                                <p className="text-sm font-medium leading-none">
                                                    {prescription.patient_name}
                                                </p>
                                                <p className="text-sm text-muted-foreground">
                                                    {prescription.prescription_number}
                                                </p>
                                                <p className="text-xs text-muted-foreground">
                                                    {new Date(prescription.issued_at).toLocaleDateString()}
                                                </p>
                                            </div>
                                            <Badge className={getPrescriptionStatusColor(prescription.status)}>
                                                {prescription.status}
                                            </Badge>
                                        </div>
                                    ))
                                ) : (
                                    <div className="text-center py-6">
                                        <Pill className="h-12 w-12 text-muted-foreground mx-auto mb-2" />
                                        <p className="text-sm text-muted-foreground">No recent prescriptions</p>
                                    </div>
                                )}
                            </div>
                            <div className="mt-4">
                                <Button asChild variant="outline" size="sm" className="w-full">
                                    <Link href="/doctor/prescriptions">
                                        View All Prescriptions
                                    </Link>
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Alerts and Notifications */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <AlertCircle className="h-5 w-5" />
                            Important Alerts
                        </CardTitle>
                        <CardDescription>
                            Items requiring your attention
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-3">
                            {stats.pendingPrescriptions > 0 && (
                                <div className="flex items-center gap-3 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                                    <AlertCircle className="h-5 w-5 text-yellow-600" />
                                    <div>
                                        <p className="text-sm font-medium text-yellow-800">
                                            {stats.pendingPrescriptions} prescription(s) pending review
                                        </p>
                                        <p className="text-xs text-yellow-600">
                                            Please review and approve pending prescriptions
                                        </p>
                                    </div>
                                    <Button asChild size="sm" variant="outline" className="ml-auto">
                                        <Link href="/doctor/prescriptions?status=draft">
                                            Review
                                        </Link>
                                    </Button>
                                </div>
                            )}
                            
                            {stats.todayAppointments > 0 && (
                                <div className="flex items-center gap-3 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                    <CheckCircle className="h-5 w-5 text-blue-600" />
                                    <div>
                                        <p className="text-sm font-medium text-blue-800">
                                            {stats.todayAppointments} appointment(s) scheduled for today
                                        </p>
                                        <p className="text-xs text-blue-600">
                                            Review your schedule and prepare for patient visits
                                        </p>
                                    </div>
                                    <Button asChild size="sm" variant="outline" className="ml-auto">
                                        <Link href="/doctor/appointments?date=today">
                                            View Schedule
                                        </Link>
                                    </Button>
                                </div>
                            )}

                            {stats.pendingPrescriptions === 0 && stats.todayAppointments === 0 && (
                                <div className="text-center py-6">
                                    <CheckCircle className="h-12 w-12 text-green-500 mx-auto mb-2" />
                                    <p className="text-sm text-muted-foreground">All caught up! No urgent items requiring attention.</p>
                                </div>
                            )}
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
