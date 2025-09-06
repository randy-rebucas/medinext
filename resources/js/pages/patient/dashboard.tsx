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
    Clock,
    User,
    Stethoscope,
    Pill,
    Activity,
    AlertCircle,
    CheckCircle
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
            <Head title="Patient Portal" />
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Welcome, {patient.name}</h1>
                        <p className="text-muted-foreground">
                            Patient ID: {patient.patient_id} | Manage your healthcare
                        </p>
                    </div>
                </div>

                {/* Main Content Tabs */}
                <Tabs defaultValue="overview" className="space-y-4">
                    <TabsList>
                        <TabsTrigger value="overview">Overview</TabsTrigger>
                        <TabsTrigger value="appointments">Appointments</TabsTrigger>
                        <TabsTrigger value="records">Medical Records</TabsTrigger>
                        <TabsTrigger value="prescriptions">Prescriptions</TabsTrigger>
                        <TabsTrigger value="lab-results">Lab Results</TabsTrigger>
                    </TabsList>

                    {/* Overview Tab */}
                    <TabsContent value="overview" className="space-y-4">
                        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                            <Card>
                                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                    <CardTitle className="text-sm font-medium">Upcoming Appointments</CardTitle>
                                    <Calendar className="h-4 w-4 text-muted-foreground" />
                                </CardHeader>
                                <CardContent>
                                    <div className="text-2xl font-bold">{upcomingAppointments.length}</div>
                                </CardContent>
                            </Card>
                            <Card>
                                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                    <CardTitle className="text-sm font-medium">Recent Encounters</CardTitle>
                                    <Stethoscope className="h-4 w-4 text-muted-foreground" />
                                </CardHeader>
                                <CardContent>
                                    <div className="text-2xl font-bold">{recentEncounters.length}</div>
                                </CardContent>
                            </Card>
                            <Card>
                                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                    <CardTitle className="text-sm font-medium">Active Prescriptions</CardTitle>
                                    <Pill className="h-4 w-4 text-muted-foreground" />
                                </CardHeader>
                                <CardContent>
                                    <div className="text-2xl font-bold">{recentPrescriptions.length}</div>
                                </CardContent>
                            </Card>
                            <Card>
                                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                    <CardTitle className="text-sm font-medium">Lab Results</CardTitle>
                                    <Activity className="h-4 w-4 text-muted-foreground" />
                                </CardHeader>
                                <CardContent>
                                    <div className="text-2xl font-bold">{recentLabResults.length}</div>
                                </CardContent>
                            </Card>
                        </div>

                        <div className="grid gap-4 md:grid-cols-2">
                            <Card>
                                <CardHeader>
                                    <CardTitle>Upcoming Appointments</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    {upcomingAppointments.length > 0 ? (
                                        <div className="space-y-3">
                                            {upcomingAppointments.slice(0, 3).map((appointment) => (
                                                <div key={appointment.id} className="flex items-center justify-between p-3 border rounded-lg">
                                                    <div>
                                                        <p className="font-medium">{appointment.doctor_name}</p>
                                                        <p className="text-sm text-muted-foreground">
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
                                        <p className="text-muted-foreground">No upcoming appointments</p>
                                    )}
                                </CardContent>
                            </Card>

                            <Card>
                                <CardHeader>
                                    <CardTitle>Recent Encounters</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    {recentEncounters.length > 0 ? (
                                        <div className="space-y-3">
                                            {recentEncounters.slice(0, 3).map((encounter) => (
                                                <div key={encounter.id} className="flex items-center justify-between p-3 border rounded-lg">
                                                    <div>
                                                        <p className="font-medium">Encounter #{encounter.encounter_number}</p>
                                                        <p className="text-sm text-muted-foreground">
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
                                        <p className="text-muted-foreground">No recent encounters</p>
                                    )}
                                </CardContent>
                            </Card>
                        </div>
                    </TabsContent>

                    {/* Appointments Tab */}
                    <TabsContent value="appointments" className="space-y-4">
                        <Card>
                            <CardHeader>
                                <div className="flex items-center justify-between">
                                    <div>
                                        <CardTitle>Appointments</CardTitle>
                                        <CardDescription>Manage your appointments</CardDescription>
                                    </div>
                                    <Dialog open={isBookingDialogOpen} onOpenChange={setIsBookingDialogOpen}>
                                        <DialogTrigger asChild>
                                            <Button>
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
                                            <Card key={appointment.id} className="p-4">
                                                <div className="flex items-center justify-between">
                                                    <div className="space-y-1">
                                                        <h4 className="font-semibold">{appointment.doctor_name}</h4>
                                                        <p className="text-sm text-muted-foreground">
                                                            {new Date(appointment.start_at).toLocaleDateString()} at {new Date(appointment.start_at).toLocaleTimeString()}
                                                        </p>
                                                        <p className="text-sm">{appointment.reason}</p>
                                                        {appointment.room_name && (
                                                            <p className="text-sm text-muted-foreground">Room: {appointment.room_name}</p>
                                                        )}
                                                    </div>
                                                    <div className="flex gap-2">
                                                        <Badge className={getStatusColor(appointment.status)}>
                                                            {appointment.status}
                                                        </Badge>
                                                        <Button size="sm" variant="outline">
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
                                        <Calendar className="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                                        <h3 className="text-lg font-semibold mb-2">No appointments scheduled</h3>
                                        <p className="text-muted-foreground mb-4">
                                            Book an appointment to get started
                                        </p>
                                        <Button onClick={() => setIsBookingDialogOpen(true)}>
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
