import { useState } from 'react';
import AppLayout from '@/layouts/app-layout';
import { patientDashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import {
    Calendar,
    FileText,
    Download,
    Eye,
    User,
    Stethoscope,
    Pill,
    Activity,
    CheckCircle,
    TrendingUp,
    Shield
} from 'lucide-react';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Patient Portal',
        href: patientDashboard(),
    },
];

interface Patient {
    id: number;
    name: string;
    patient_id: string;
    dob: string;
    sex: string;
    contact: {
        phone?: string;
        email?: string;
    };
    address?: string;
}

interface Appointment {
    id: number;
    doctor_name: string;
    start_at: string;
    end_at: string;
    status: string;
    type: string;
    reason: string;
    room_name?: string;
}

interface Encounter {
    id: number;
    encounter_number: string;
    doctor_name: string;
    visit_type: string;
    reason_for_visit: string;
    status: string;
    created_at: string;
    diagnosis?: string[];
    treatment_plan?: string;
}

interface Prescription {
    id: number;
    prescription_number: string;
    doctor_name: string;
    status: string;
    issued_at: string;
    items: Array<{
        medication_name: string;
        dosage: string;
        frequency: string;
        duration: string;
    }>;
}

interface LabResult {
    id: number;
    test_name: string;
    result_value: string;
    reference_range: string;
    status: string;
    completed_at: string;
}

interface PatientDashboardProps {
    patient: Patient;
    upcomingAppointments: Appointment[];
    recentEncounters: Encounter[];
    recentPrescriptions: Prescription[];
    recentLabResults: LabResult[];
    doctors: Array<{ id: number; name: string; specialization: string; }>;
    availableSlots: Array<{ date: string; time: string; doctor_id: number; }>;
}

export default function PatientDashboard({
    patient,
    upcomingAppointments,
    recentEncounters,
    recentPrescriptions,
    recentLabResults,
    doctors,
    availableSlots
}: PatientDashboardProps) {
    const [isBookingDialogOpen, setIsBookingDialogOpen] = useState(false);

    const getStatusColor = (status: string) => {
        switch (status.toLowerCase()) {
            case 'completed':
            case 'confirmed':
                return 'bg-green-100 text-green-800';
            case 'pending':
            case 'scheduled':
                return 'bg-yellow-100 text-yellow-800';
            case 'cancelled':
                return 'bg-red-100 text-red-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Patient Portal - Medinext">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&family=instrument-sans:400,500,600" rel="stylesheet" />
            </Head>
            <div className="min-h-screen bg-gradient-to-br from-slate-50 via-white to-blue-50 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900">
                <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
                    {/* Modern Header */}
                    <div className="relative overflow-hidden rounded-2xl bg-gradient-to-r from-rose-600 to-pink-600 p-8 text-white shadow-xl">
                        <div className="absolute inset-0 bg-black/10"></div>
                        <div className="relative flex items-center justify-between">
                            <div>
                                <h1 className="text-3xl font-bold tracking-tight">Welcome, {patient.name}</h1>
                                <p className="mt-2 text-rose-100">
                                    Patient ID: {patient.patient_id} • Manage your healthcare
                                </p>
                            </div>
                            <div className="flex items-center gap-3">
                                <Badge variant="secondary" className="flex items-center gap-1 bg-white/20 text-white border-white/30 hover:bg-white/30">
                                    <Shield className="h-3 w-3" />
                                    Patient
                                </Badge>
                                <Badge variant="secondary" className="flex items-center gap-1 bg-white/20 text-white border-white/30 hover:bg-white/30">
                                    <User className="h-3 w-3" />
                                    {patient.sex}
                                </Badge>
                            </div>
                        </div>
                        {/* Decorative elements */}
                        <div className="absolute -top-4 -right-4 w-24 h-24 bg-white/10 rounded-full"></div>
                        <div className="absolute -bottom-2 -left-2 w-16 h-16 bg-white/5 rounded-full"></div>
                    </div>

                    {/* Main Content Tabs */}
                    <Tabs defaultValue="overview" className="space-y-4">
                        <TabsList className="bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm border border-slate-200 dark:border-slate-700">
                            <TabsTrigger value="overview">Overview</TabsTrigger>
                            <TabsTrigger value="appointments">Appointments</TabsTrigger>
                            <TabsTrigger value="records">Medical Records</TabsTrigger>
                            <TabsTrigger value="prescriptions">Prescriptions</TabsTrigger>
                            <TabsTrigger value="lab-results">Lab Results</TabsTrigger>
                        </TabsList>

                        {/* Overview Tab */}
                        <TabsContent value="overview" className="space-y-4">
                            <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                                <Card className="relative overflow-hidden border-0 shadow-lg hover:shadow-xl transition-all duration-300 bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20">
                                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                        <CardTitle className="text-sm font-medium text-slate-700 dark:text-slate-300">Upcoming Appointments</CardTitle>
                                        <div className="p-2 bg-blue-500 rounded-lg">
                                            <Calendar className="h-4 w-4 text-white" />
                                        </div>
                                    </CardHeader>
                                    <CardContent>
                                        <div className="text-3xl font-bold text-slate-900 dark:text-white">{upcomingAppointments.length}</div>
                                        <div className="flex items-center mt-2">
                                            <TrendingUp className="h-3 w-3 text-blue-500 mr-1" />
                                            <p className="text-xs text-slate-600 dark:text-slate-400">
                                                Scheduled visits
                                            </p>
                                        </div>
                                    </CardContent>
                                </Card>
                                <Card className="relative overflow-hidden border-0 shadow-lg hover:shadow-xl transition-all duration-300 bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20">
                                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                        <CardTitle className="text-sm font-medium text-slate-700 dark:text-slate-300">Recent Encounters</CardTitle>
                                        <div className="p-2 bg-green-500 rounded-lg">
                                            <Stethoscope className="h-4 w-4 text-white" />
                                        </div>
                                    </CardHeader>
                                    <CardContent>
                                        <div className="text-3xl font-bold text-slate-900 dark:text-white">{recentEncounters.length}</div>
                                        <div className="flex items-center mt-2">
                                            <Activity className="h-3 w-3 text-green-500 mr-1" />
                                            <p className="text-xs text-slate-600 dark:text-slate-400">
                                                Medical visits
                                            </p>
                                        </div>
                                    </CardContent>
                                </Card>
                                <Card className="relative overflow-hidden border-0 shadow-lg hover:shadow-xl transition-all duration-300 bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20">
                                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                        <CardTitle className="text-sm font-medium text-slate-700 dark:text-slate-300">Active Prescriptions</CardTitle>
                                        <div className="p-2 bg-purple-500 rounded-lg">
                                            <Pill className="h-4 w-4 text-white" />
                                        </div>
                                    </CardHeader>
                                    <CardContent>
                                        <div className="text-3xl font-bold text-slate-900 dark:text-white">{recentPrescriptions.length}</div>
                                        <div className="flex items-center mt-2">
                                            <CheckCircle className="h-3 w-3 text-purple-500 mr-1" />
                                            <p className="text-xs text-slate-600 dark:text-slate-400">
                                                Current medications
                                            </p>
                                        </div>
                                    </CardContent>
                                </Card>
                                <Card className="relative overflow-hidden border-0 shadow-lg hover:shadow-xl transition-all duration-300 bg-gradient-to-br from-orange-50 to-orange-100 dark:from-orange-900/20 dark:to-orange-800/20">
                                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                        <CardTitle className="text-sm font-medium text-slate-700 dark:text-slate-300">Lab Results</CardTitle>
                                        <div className="p-2 bg-orange-500 rounded-lg">
                                            <Activity className="h-4 w-4 text-white" />
                                        </div>
                                    </CardHeader>
                                    <CardContent>
                                        <div className="text-3xl font-bold text-slate-900 dark:text-white">{recentLabResults.length}</div>
                                        <div className="flex items-center mt-2">
                                            <FileText className="h-3 w-3 text-orange-500 mr-1" />
                                            <p className="text-xs text-slate-600 dark:text-slate-400">
                                                Test results
                                            </p>
                                        </div>
                                    </CardContent>
                                </Card>
                            </div>

                            <div className="grid gap-6 md:grid-cols-2">
                                <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                                    <CardHeader>
                                        <CardTitle className="text-lg font-semibold text-slate-900 dark:text-white">Upcoming Appointments</CardTitle>
                                    </CardHeader>
                                    <CardContent>
                                        {upcomingAppointments.length > 0 ? (
                                            <div className="space-y-3">
                                                {upcomingAppointments.slice(0, 3).map((appointment) => (
                                                    <div key={appointment.id} className="flex items-center justify-between p-3 border border-slate-200 dark:border-slate-700 rounded-lg hover:shadow-md transition-all duration-200 bg-slate-50 dark:bg-slate-700/50">
                                                        <div>
                                                            <p className="font-medium text-slate-900 dark:text-white">{appointment.doctor_name}</p>
                                                            <p className="text-sm text-slate-600 dark:text-slate-400">
                                                                {new Date(appointment.start_at).toLocaleDateString()} at {new Date(appointment.start_at).toLocaleTimeString()}
                                                            </p>
                                                        </div>
                                                        <Badge className={getStatusColor(appointment.status)}>
                                                            {appointment.status}
                                                        </Badge>
                                                    </div>
                                                ))}
                                            </div>
                                        ) : (
                                            <div className="text-center py-8">
                                                <div className="p-3 bg-blue-100 dark:bg-blue-900/20 rounded-full w-fit mx-auto mb-4">
                                                    <Calendar className="h-8 w-8 text-blue-600 dark:text-blue-400" />
                                                </div>
                                                <p className="text-slate-600 dark:text-slate-400">No upcoming appointments</p>
                                            </div>
                                        )}
                                    </CardContent>
                                </Card>

                                <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                                    <CardHeader>
                                        <CardTitle className="text-lg font-semibold text-slate-900 dark:text-white">Recent Encounters</CardTitle>
                                    </CardHeader>
                                    <CardContent>
                                        {recentEncounters.length > 0 ? (
                                            <div className="space-y-3">
                                                {recentEncounters.slice(0, 3).map((encounter) => (
                                                    <div key={encounter.id} className="flex items-center justify-between p-3 border border-slate-200 dark:border-slate-700 rounded-lg hover:shadow-md transition-all duration-200 bg-slate-50 dark:bg-slate-700/50">
                                                        <div>
                                                            <p className="font-medium text-slate-900 dark:text-white">Encounter #{encounter.encounter_number}</p>
                                                            <p className="text-sm text-slate-600 dark:text-slate-400">
                                                                Dr. {encounter.doctor_name} - {new Date(encounter.created_at).toLocaleDateString()}
                                                            </p>
                                                        </div>
                                                        <Badge className={getStatusColor(encounter.status)}>
                                                            {encounter.status}
                                                        </Badge>
                                                    </div>
                                                ))}
                                            </div>
                                        ) : (
                                            <div className="text-center py-8">
                                                <div className="p-3 bg-green-100 dark:bg-green-900/20 rounded-full w-fit mx-auto mb-4">
                                                    <Stethoscope className="h-8 w-8 text-green-600 dark:text-green-400" />
                                                </div>
                                                <p className="text-slate-600 dark:text-slate-400">No recent encounters</p>
                                            </div>
                                        )}
                                    </CardContent>
                                </Card>
                            </div>
                    </TabsContent>

                        {/* Appointments Tab */}
                        <TabsContent value="appointments" className="space-y-4">
                            <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                                <CardHeader>
                                    <div className="flex items-center justify-between">
                                        <div>
                                            <CardTitle className="text-lg font-semibold text-slate-900 dark:text-white">Appointments</CardTitle>
                                            <CardDescription className="text-slate-600 dark:text-slate-300">Manage your appointments</CardDescription>
                                        </div>
                                        <Dialog open={isBookingDialogOpen} onOpenChange={setIsBookingDialogOpen}>
                                            <DialogTrigger asChild>
                                                <Button className="hover:bg-rose-600 hover:border-rose-600 transition-all duration-200">
                                                    <Calendar className="h-4 w-4 mr-2" />
                                                    Book Appointment
                                                </Button>
                                            </DialogTrigger>
                                            <DialogContent className="max-w-2xl">
                                                <DialogHeader>
                                                    <DialogTitle>Book New Appointment</DialogTitle>
                                                    <DialogDescription>
                                                        Select a doctor and available time slot
                                                    </DialogDescription>
                                                </DialogHeader>
                                                <AppointmentBookingForm
                                                    doctors={doctors}
                                                    availableSlots={availableSlots}
                                                    onSuccess={() => setIsBookingDialogOpen(false)}
                                                />
                                            </DialogContent>
                                        </Dialog>
                                    </div>
                                </CardHeader>
                                <CardContent>
                                    {upcomingAppointments.length > 0 ? (
                                        <div className="space-y-4">
                                            {upcomingAppointments.map((appointment) => (
                                                <Card key={appointment.id} className="p-4 border-slate-200 dark:border-slate-700 hover:shadow-md transition-all duration-200 bg-slate-50 dark:bg-slate-700/50">
                                                    <div className="flex items-center justify-between">
                                                        <div className="space-y-1">
                                                            <h4 className="font-semibold text-slate-900 dark:text-white">{appointment.doctor_name}</h4>
                                                            <p className="text-sm text-slate-600 dark:text-slate-400">
                                                                {new Date(appointment.start_at).toLocaleDateString()} at {new Date(appointment.start_at).toLocaleTimeString()}
                                                            </p>
                                                            <p className="text-sm text-slate-900 dark:text-white">{appointment.reason}</p>
                                                            {appointment.room_name && (
                                                                <p className="text-sm text-slate-600 dark:text-slate-400">Room: {appointment.room_name}</p>
                                                            )}
                                                        </div>
                                                        <div className="flex gap-2">
                                                            <Badge className={getStatusColor(appointment.status)}>
                                                                {appointment.status}
                                                            </Badge>
                                                            <Button size="sm" variant="outline" className="border-slate-200 dark:border-slate-700 hover:bg-rose-50 dark:hover:bg-rose-900/20 hover:border-rose-300 dark:hover:border-rose-600 transition-all duration-200">
                                                                <Eye className="h-4 w-4 mr-2" />
                                                                View
                                                            </Button>
                                                        </div>
                                                    </div>
                                                </Card>
                                            ))}
                                        </div>
                                    ) : (
                                        <div className="text-center py-8">
                                            <div className="p-3 bg-rose-100 dark:bg-rose-900/20 rounded-full w-fit mx-auto mb-4">
                                                <Calendar className="h-8 w-8 text-rose-600 dark:text-rose-400" />
                                            </div>
                                            <h3 className="text-lg font-semibold text-slate-900 dark:text-white mb-2">No appointments scheduled</h3>
                                            <p className="text-slate-600 dark:text-slate-400 mb-4">
                                                Book an appointment to get started
                                            </p>
                                            <Button onClick={() => setIsBookingDialogOpen(true)} className="hover:bg-rose-600 hover:border-rose-600 transition-all duration-200">
                                                <Calendar className="h-4 w-4 mr-2" />
                                                Book Appointment
                                            </Button>
                                        </div>
                                    )}
                                </CardContent>
                            </Card>
                        </TabsContent>

                    {/* Medical Records Tab */}
                    <TabsContent value="records" className="space-y-4">
                        <Card>
                            <CardHeader>
                                <CardTitle>Medical Records</CardTitle>
                                <CardDescription>View your medical history and encounters</CardDescription>
                            </CardHeader>
                            <CardContent>
                                {recentEncounters.length > 0 ? (
                                    <div className="space-y-4">
                                        {recentEncounters.map((encounter) => (
                                            <Card key={encounter.id} className="p-4">
                                                <div className="space-y-3">
                                                    <div className="flex items-center justify-between">
                                                        <h4 className="font-semibold">Encounter #{encounter.encounter_number}</h4>
                                                        <Badge className={getStatusColor(encounter.status)}>
                                                            {encounter.status}
                                                        </Badge>
                                                    </div>
                                                    <div className="grid grid-cols-2 gap-4 text-sm">
                                                        <div>
                                                            <p><strong>Doctor:</strong> {encounter.doctor_name}</p>
                                                            <p><strong>Date:</strong> {new Date(encounter.created_at).toLocaleDateString()}</p>
                                                        </div>
                                                        <div>
                                                            <p><strong>Visit Type:</strong> {encounter.visit_type}</p>
                                                            <p><strong>Reason:</strong> {encounter.reason_for_visit}</p>
                                                        </div>
                                                    </div>
                                                    {encounter.diagnosis && encounter.diagnosis.length > 0 && (
                                                        <div>
                                                            <p className="text-sm font-medium">Diagnosis:</p>
                                                            <div className="flex flex-wrap gap-1 mt-1">
                                                                {encounter.diagnosis.map((diag, index) => (
                                                                    <Badge key={index} variant="outline">{diag}</Badge>
                                                                ))}
                                                            </div>
                                                        </div>
                                                    )}
                                                    <div className="flex gap-2">
                                                        <Button size="sm" variant="outline">
                                                            <Eye className="h-4 w-4 mr-2" />
                                                            View Details
                                                        </Button>
                                                        <Button size="sm" variant="outline">
                                                            <Download className="h-4 w-4 mr-2" />
                                                            Download Report
                                                        </Button>
                                                    </div>
                                                </div>
                                            </Card>
                                        ))}
                                    </div>
                                ) : (
                                    <div className="text-center py-8">
                                        <FileText className="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                                        <h3 className="text-lg font-semibold mb-2">No medical records</h3>
                                        <p className="text-muted-foreground">
                                            Your medical records will appear here after your first visit
                                        </p>
                                    </div>
                                )}
                            </CardContent>
                        </Card>
                    </TabsContent>

                    {/* Prescriptions Tab */}
                    <TabsContent value="prescriptions" className="space-y-4">
                        <Card>
                            <CardHeader>
                                <CardTitle>Prescriptions</CardTitle>
                                <CardDescription>View and download your prescriptions</CardDescription>
                            </CardHeader>
                            <CardContent>
                                {recentPrescriptions.length > 0 ? (
                                    <div className="space-y-4">
                                        {recentPrescriptions.map((prescription) => (
                                            <Card key={prescription.id} className="p-4">
                                                <div className="space-y-3">
                                                    <div className="flex items-center justify-between">
                                                        <h4 className="font-semibold">Prescription #{prescription.prescription_number}</h4>
                                                        <Badge className={getStatusColor(prescription.status)}>
                                                            {prescription.status}
                                                        </Badge>
                                                    </div>
                                                    <div className="grid grid-cols-2 gap-4 text-sm">
                                                        <div>
                                                            <p><strong>Doctor:</strong> {prescription.doctor_name}</p>
                                                            <p><strong>Issued:</strong> {new Date(prescription.issued_at).toLocaleDateString()}</p>
                                                        </div>
                                                        <div>
                                                            <p><strong>Medications:</strong> {prescription.items.length} items</p>
                                                        </div>
                                                    </div>
                                                    <div className="space-y-2">
                                                        <p className="text-sm font-medium">Medications:</p>
                                                        <div className="space-y-1">
                                                            {prescription.items.slice(0, 3).map((item, index) => (
                                                                <div key={index} className="text-sm text-muted-foreground">
                                                                    • {item.medication_name} - {item.dosage} ({item.frequency})
                                                                </div>
                                                            ))}
                                                            {prescription.items.length > 3 && (
                                                                <p className="text-sm text-muted-foreground">
                                                                    ... and {prescription.items.length - 3} more
                                                                </p>
                                                            )}
                                                        </div>
                                                    </div>
                                                    <div className="flex gap-2">
                                                        <Button size="sm" variant="outline">
                                                            <Eye className="h-4 w-4 mr-2" />
                                                            View Details
                                                        </Button>
                                                        <Button size="sm">
                                                            <Download className="h-4 w-4 mr-2" />
                                                            Download PDF
                                                        </Button>
                                                    </div>
                                                </div>
                                            </Card>
                                        ))}
                                    </div>
                                ) : (
                                    <div className="text-center py-8">
                                        <Pill className="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                                        <h3 className="text-lg font-semibold mb-2">No prescriptions</h3>
                                        <p className="text-muted-foreground">
                                            Your prescriptions will appear here after they are issued
                                        </p>
                                    </div>
                                )}
                            </CardContent>
                        </Card>
                    </TabsContent>

                    {/* Lab Results Tab */}
                    <TabsContent value="lab-results" className="space-y-4">
                        <Card>
                            <CardHeader>
                                <CardTitle>Lab Results</CardTitle>
                                <CardDescription>View your laboratory test results</CardDescription>
                            </CardHeader>
                            <CardContent>
                                {recentLabResults.length > 0 ? (
                                    <div className="space-y-4">
                                        {recentLabResults.map((result) => (
                                            <Card key={result.id} className="p-4">
                                                <div className="space-y-3">
                                                    <div className="flex items-center justify-between">
                                                        <h4 className="font-semibold">{result.test_name}</h4>
                                                        <Badge className={getStatusColor(result.status)}>
                                                            {result.status}
                                                        </Badge>
                                                    </div>
                                                    <div className="grid grid-cols-2 gap-4 text-sm">
                                                        <div>
                                                            <p><strong>Result:</strong> {result.result_value}</p>
                                                            <p><strong>Reference Range:</strong> {result.reference_range}</p>
                                                        </div>
                                                        <div>
                                                            <p><strong>Completed:</strong> {new Date(result.completed_at).toLocaleDateString()}</p>
                                                        </div>
                                                    </div>
                                                    <div className="flex gap-2">
                                                        <Button size="sm" variant="outline">
                                                            <Eye className="h-4 w-4 mr-2" />
                                                            View Details
                                                        </Button>
                                                        <Button size="sm">
                                                            <Download className="h-4 w-4 mr-2" />
                                                            Download Report
                                                        </Button>
                                                    </div>
                                                </div>
                                            </Card>
                                        ))}
                                    </div>
                                ) : (
                                    <div className="text-center py-8">
                                        <Activity className="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                                        <h3 className="text-lg font-semibold mb-2">No lab results</h3>
                                        <p className="text-muted-foreground">
                                            Your lab results will appear here after tests are completed
                                        </p>
                                    </div>
                                )}
                            </CardContent>
                        </Card>
                    </TabsContent>
                    </Tabs>
                </div>
            </div>
        </AppLayout>
    );
}

// Appointment Booking Form Component
function AppointmentBookingForm({
    doctors,
    availableSlots,
    onSuccess
}: {
    doctors: Array<{ id: number; name: string; specialization: string; }>;
    availableSlots: Array<{ date: string; time: string; doctor_id: number; }>;
    onSuccess: () => void;
}) {
    const [formData, setFormData] = useState({
        doctor_id: '',
        date: '',
        time: '',
        reason: '',
        type: 'consultation'
    });
    const [isSubmitting, setIsSubmitting] = useState(false);

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setIsSubmitting(true);

        try {
            const response = await fetch('/api/v1/appointments', {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('token')}`,
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData),
            });

            if (response.ok) {
                onSuccess();
            }
        } catch (error) {
            console.error('Error booking appointment:', error);
        } finally {
            setIsSubmitting(false);
        }
    };

    const filteredSlots = availableSlots.filter(slot =>
        slot.doctor_id.toString() === formData.doctor_id && slot.date === formData.date
    );

    return (
        <form onSubmit={handleSubmit} className="space-y-4">
            <div>
                <Label htmlFor="doctor_id">Select Doctor *</Label>
                <Select value={formData.doctor_id} onValueChange={(value) => setFormData({ ...formData, doctor_id: value })}>
                    <SelectTrigger>
                        <SelectValue placeholder="Choose a doctor" />
                    </SelectTrigger>
                    <SelectContent>
                        {doctors.map((doctor) => (
                            <SelectItem key={doctor.id} value={doctor.id.toString()}>
                                {doctor.name} - {doctor.specialization}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
            </div>

            <div>
                <Label htmlFor="date">Select Date *</Label>
                <Input
                    id="date"
                    type="date"
                    value={formData.date}
                    onChange={(e) => setFormData({ ...formData, date: e.target.value })}
                    min={new Date().toISOString().split('T')[0]}
                    required
                />
            </div>

            <div>
                <Label htmlFor="time">Select Time *</Label>
                <Select value={formData.time} onValueChange={(value) => setFormData({ ...formData, time: value })}>
                    <SelectTrigger>
                        <SelectValue placeholder="Choose a time slot" />
                    </SelectTrigger>
                    <SelectContent>
                        {filteredSlots.map((slot, index) => (
                            <SelectItem key={index} value={slot.time}>
                                {slot.time}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
            </div>

            <div>
                <Label htmlFor="type">Appointment Type *</Label>
                <Select value={formData.type} onValueChange={(value) => setFormData({ ...formData, type: value })}>
                    <SelectTrigger>
                        <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="consultation">Consultation</SelectItem>
                        <SelectItem value="follow-up">Follow-up</SelectItem>
                        <SelectItem value="checkup">Checkup</SelectItem>
                        <SelectItem value="emergency">Emergency</SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <div>
                <Label htmlFor="reason">Reason for Visit *</Label>
                <Textarea
                    id="reason"
                    value={formData.reason}
                    onChange={(e) => setFormData({ ...formData, reason: e.target.value })}
                    placeholder="Describe the reason for your appointment..."
                    required
                />
            </div>

            <div className="flex justify-end gap-2">
                <Button type="button" variant="outline" onClick={onSuccess}>
                    Cancel
                </Button>
                <Button type="submit" disabled={isSubmitting}>
                    {isSubmitting ? 'Booking...' : 'Book Appointment'}
                </Button>
            </div>
        </form>
    );
}
