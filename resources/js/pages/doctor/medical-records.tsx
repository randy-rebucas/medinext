import { useState } from 'react';
import AppLayout from '@/layouts/app-layout';
import { doctorMedicalRecords } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import {
    FileText,
    Search,
    Filter,
    Plus,
    Eye,
    Edit,
    Download,
    Upload,
    Calendar,
    User,
    Stethoscope,
    Pill,
    Activity,
    AlertTriangle
} from 'lucide-react';
import { Link } from '@inertiajs/react';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Doctor Dashboard',
        href: '/doctor/dashboard',
    },
    {
        title: 'Medical Records',
        href: doctorMedicalRecords(),
    },
];

interface Patient {
    id: number;
    name: string;
    dob: string;
    sex: string;
    contact: {
        phone?: string;
        email?: string;
    };
    allergies: string[];
    last_visit: string;
}

interface Encounter {
    id: number;
    patient_id: number;
    patient_name: string;
    date: string;
    type: string;
    chief_complaint: string;
    diagnosis: string;
    treatment: string;
    status: string;
    doctor_notes: string;
}

interface LabResult {
    id: number;
    patient_id: number;
    patient_name: string;
    test_name: string;
    result: string;
    normal_range: string;
    status: string;
    ordered_at: string;
    result_date: string;
}

interface Prescription {
    id: number;
    patient_id: number;
    patient_name: string;
    prescription_number: string;
    medication: string;
    dosage: string;
    instructions: string;
    status: string;
    issued_at: string;
}

interface DoctorMedicalRecordsProps {
    patients?: Patient[];
    encounters?: Encounter[];
    labResults?: LabResult[];
    prescriptions?: Prescription[];
    filters?: {
        search: string;
        patient_id: string;
        date_range: string;
    };
}

export default function DoctorMedicalRecords({
    patients = [],
    encounters = [],
    labResults = [],
    prescriptions = [],
    filters = {
        search: '',
        patient_id: '',
        date_range: ''
    }
}: DoctorMedicalRecordsProps) {
    const [searchTerm, setSearchTerm] = useState(filters.search || '');
    const [selectedPatient, setSelectedPatient] = useState(filters.patient_id || '');
    const [dateRange, setDateRange] = useState(filters.date_range || '');
    const [activeTab, setActiveTab] = useState('patients');
    const [isCreateEncounterOpen, setIsCreateEncounterOpen] = useState(false);
    const [isViewPatientOpen, setIsViewPatientOpen] = useState(false);
    const [selectedPatientData, setSelectedPatientData] = useState<Patient | null>(null);

    const getStatusColor = (status: string) => {
        switch (status) {
            case 'active':
            case 'completed':
                return 'bg-green-100 text-green-800';
            case 'pending':
            case 'in_progress':
                return 'bg-yellow-100 text-yellow-800';
            case 'cancelled':
            case 'expired':
                return 'bg-red-100 text-red-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    };

    const filteredPatients = patients.filter(patient => {
        const matchesSearch = patient.name.toLowerCase().includes(searchTerm.toLowerCase());
        return matchesSearch;
    });

    const filteredEncounters = encounters.filter(encounter => {
        const matchesSearch = encounter.patient_name.toLowerCase().includes(searchTerm.toLowerCase()) ||
                            encounter.chief_complaint.toLowerCase().includes(searchTerm.toLowerCase()) ||
                            encounter.diagnosis.toLowerCase().includes(searchTerm.toLowerCase());
        const matchesPatient = !selectedPatient || selectedPatient === 'all' || encounter.patient_id.toString() === selectedPatient;
        return matchesSearch && matchesPatient;
    });

    const filteredLabResults = labResults.filter(result => {
        const matchesSearch = result.patient_name.toLowerCase().includes(searchTerm.toLowerCase()) ||
                            result.test_name.toLowerCase().includes(searchTerm.toLowerCase());
        const matchesPatient = !selectedPatient || selectedPatient === 'all' || result.patient_id.toString() === selectedPatient;
        return matchesSearch && matchesPatient;
    });

    const filteredPrescriptions = prescriptions.filter(prescription => {
        const matchesSearch = prescription.patient_name.toLowerCase().includes(searchTerm.toLowerCase()) ||
                            prescription.medication.toLowerCase().includes(searchTerm.toLowerCase());
        const matchesPatient = !selectedPatient || selectedPatient === 'all' || prescription.patient_id.toString() === selectedPatient;
        return matchesSearch && matchesPatient;
    });

    const handleViewPatient = (patient: Patient) => {
        setSelectedPatientData(patient);
        setIsViewPatientOpen(true);
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Medical Records" />
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Medical Records</h1>
                        <p className="text-muted-foreground">
                            Manage patient medical records and history
                        </p>
                    </div>
                    <div className="flex gap-2">
                        <Button variant="outline">
                            <Download className="h-4 w-4 mr-2" />
                            Export Records
                        </Button>
                        <Button variant="outline">
                            <Upload className="h-4 w-4 mr-2" />
                            Import Records
                        </Button>
                    </div>
                </div>

                {/* Filters */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <Filter className="h-5 w-5" />
                            Search & Filters
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="grid gap-4 md:grid-cols-3">
                            <div className="space-y-2">
                                <Label htmlFor="search">Search</Label>
                                <div className="relative">
                                    <Search className="absolute left-2 top-2.5 h-4 w-4 text-muted-foreground" />
                                    <Input
                                        id="search"
                                        placeholder="Search patients, diagnoses, medications..."
                                        value={searchTerm}
                                        onChange={(e) => setSearchTerm(e.target.value)}
                                        className="pl-8"
                                    />
                                </div>
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="patient">Patient</Label>
                                <Select value={selectedPatient} onValueChange={setSelectedPatient}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="All patients" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">All patients</SelectItem>
                                        {patients.map((patient) => (
                                            <SelectItem key={patient.id} value={patient.id.toString()}>
                                                {patient.name}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="date_range">Date Range</Label>
                                <Select value={dateRange} onValueChange={setDateRange}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="All dates" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">All dates</SelectItem>
                                        <SelectItem value="today">Today</SelectItem>
                                        <SelectItem value="week">This week</SelectItem>
                                        <SelectItem value="month">This month</SelectItem>
                                        <SelectItem value="quarter">This quarter</SelectItem>
                                        <SelectItem value="year">This year</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {/* Tabs */}
                <Tabs value={activeTab} onValueChange={setActiveTab} className="w-full">
                    <TabsList className="grid w-full grid-cols-4">
                        <TabsTrigger value="patients" className="flex items-center gap-2">
                            <User className="h-4 w-4" />
                            Patients ({filteredPatients.length})
                        </TabsTrigger>
                        <TabsTrigger value="encounters" className="flex items-center gap-2">
                            <Stethoscope className="h-4 w-4" />
                            Encounters ({filteredEncounters.length})
                        </TabsTrigger>
                        <TabsTrigger value="lab-results" className="flex items-center gap-2">
                            <Activity className="h-4 w-4" />
                            Lab Results ({filteredLabResults.length})
                        </TabsTrigger>
                        <TabsTrigger value="prescriptions" className="flex items-center gap-2">
                            <Pill className="h-4 w-4" />
                            Prescriptions ({filteredPrescriptions.length})
                        </TabsTrigger>
                    </TabsList>

                    {/* Patients Tab */}
                    <TabsContent value="patients" className="space-y-4">
                        <Card>
                            <CardHeader>
                                <CardTitle>Patients</CardTitle>
                                <CardDescription>
                                    Patient directory and medical history
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                {filteredPatients.length > 0 ? (
                                    <div className="space-y-4">
                                        {filteredPatients.map((patient) => (
                                            <div key={patient.id} className="border rounded-lg p-4 hover:bg-muted/50 transition-colors">
                                                <div className="flex items-center justify-between">
                                                    <div className="space-y-2">
                                                        <div className="flex items-center gap-3">
                                                            <h3 className="font-semibold">{patient.name}</h3>
                                                            <Badge variant="outline">
                                                                {patient.sex}
                                                            </Badge>
                                                            <Badge variant="outline">
                                                                Age: {new Date().getFullYear() - new Date(patient.dob).getFullYear()}
                                                            </Badge>
                                                        </div>
                                                        <div className="flex items-center gap-4 text-sm text-muted-foreground">
                                                            <div className="flex items-center gap-1">
                                                                <Calendar className="h-4 w-4" />
                                                                DOB: {new Date(patient.dob).toLocaleDateString()}
                                                            </div>
                                                            {patient.contact.phone && (
                                                                <div className="flex items-center gap-1">
                                                                    <span>📞</span>
                                                                    {patient.contact.phone}
                                                                </div>
                                                            )}
                                                            {patient.contact.email && (
                                                                <div className="flex items-center gap-1">
                                                                    <span>✉️</span>
                                                                    {patient.contact.email}
                                                                </div>
                                                            )}
                                                        </div>
                                                        {patient.allergies.length > 0 && (
                                                            <div className="flex items-center gap-2">
                                                                <AlertTriangle className="h-4 w-4 text-red-500" />
                                                                <span className="text-sm text-red-600">
                                                                    Allergies: {patient.allergies.join(', ')}
                                                                </span>
                                                            </div>
                                                        )}
                                                        <p className="text-sm text-muted-foreground">
                                                            Last visit: {new Date(patient.last_visit).toLocaleDateString()}
                                                        </p>
                                                    </div>
                                                    <div className="flex items-center gap-2">
                                                        <Button
                                                            variant="outline"
                                                            size="sm"
                                                            onClick={() => handleViewPatient(patient)}
                                                        >
                                                            <Eye className="h-4 w-4" />
                                                        </Button>
                                                        <Button variant="outline" size="sm" asChild>
                                                            <Link href={`/doctor/medical-records/patient/${patient.id}`}>
                                                                <FileText className="h-4 w-4" />
                                                            </Link>
                                                        </Button>
                                                    </div>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                ) : (
                                    <div className="text-center py-12">
                                        <User className="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                                        <h3 className="text-lg font-semibold mb-2">No patients found</h3>
                                        <p className="text-muted-foreground">
                                            {searchTerm ? 'Try adjusting your search terms.' : 'No patients in the system yet.'}
                                        </p>
                                    </div>
                                )}
                            </CardContent>
                        </Card>
                    </TabsContent>

                    {/* Encounters Tab */}
                    <TabsContent value="encounters" className="space-y-4">
                        <Card>
                            <CardHeader>
                                <div className="flex items-center justify-between">
                                    <div>
                                        <CardTitle>Medical Encounters</CardTitle>
                                        <CardDescription>
                                            Patient visits and consultations
                                        </CardDescription>
                                    </div>
                                    <Dialog open={isCreateEncounterOpen} onOpenChange={setIsCreateEncounterOpen}>
                                        <DialogTrigger asChild>
                                            <Button>
                                                <Plus className="h-4 w-4 mr-2" />
                                                New Encounter
                                            </Button>
                                        </DialogTrigger>
                                        <DialogContent className="max-w-2xl">
                                            <DialogHeader>
                                                <DialogTitle>Create New Encounter</DialogTitle>
                                                <DialogDescription>
                                                    Record a new patient encounter
                                                </DialogDescription>
                                            </DialogHeader>
                                            <CreateEncounterForm
                                                patients={patients}
                                                onSuccess={() => setIsCreateEncounterOpen(false)}
                                            />
                                        </DialogContent>
                                    </Dialog>
                                </div>
                            </CardHeader>
                            <CardContent>
                                {filteredEncounters.length > 0 ? (
                                    <div className="space-y-4">
                                        {filteredEncounters.map((encounter) => (
                                            <div key={encounter.id} className="border rounded-lg p-4 hover:bg-muted/50 transition-colors">
                                                <div className="flex items-center justify-between">
                                                    <div className="space-y-2">
                                                        <div className="flex items-center gap-3">
                                                            <h3 className="font-semibold">{encounter.patient_name}</h3>
                                                            <Badge className={getStatusColor(encounter.status)}>
                                                                {encounter.status.replace('_', ' ')}
                                                            </Badge>
                                                            <Badge variant="outline">
                                                                {encounter.type.replace('_', ' ')}
                                                            </Badge>
                                                        </div>
                                                        <div className="flex items-center gap-4 text-sm text-muted-foreground">
                                                            <div className="flex items-center gap-1">
                                                                <Calendar className="h-4 w-4" />
                                                                {new Date(encounter.date).toLocaleDateString()}
                                                            </div>
                                                        </div>
                                                        <p className="text-sm">
                                                            <strong>Chief Complaint:</strong> {encounter.chief_complaint}
                                                        </p>
                                                        <p className="text-sm">
                                                            <strong>Diagnosis:</strong> {encounter.diagnosis}
                                                        </p>
                                                        <p className="text-sm">
                                                            <strong>Treatment:</strong> {encounter.treatment}
                                                        </p>
                                                        {encounter.doctor_notes && (
                                                            <p className="text-sm text-muted-foreground">
                                                                <strong>Notes:</strong> {encounter.doctor_notes}
                                                            </p>
                                                        )}
                                                    </div>
                                                    <div className="flex items-center gap-2">
                                                        <Button variant="outline" size="sm">
                                                            <Eye className="h-4 w-4" />
                                                        </Button>
                                                        <Button variant="outline" size="sm">
                                                            <Edit className="h-4 w-4" />
                                                        </Button>
                                                    </div>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                ) : (
                                    <div className="text-center py-12">
                                        <Stethoscope className="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                                        <h3 className="text-lg font-semibold mb-2">No encounters found</h3>
                                        <p className="text-muted-foreground mb-4">
                                            {searchTerm || selectedPatient ? 'Try adjusting your filters.' : 'No medical encounters recorded yet.'}
                                        </p>
                                        <Button onClick={() => setIsCreateEncounterOpen(true)}>
                                            <Plus className="h-4 w-4 mr-2" />
                                            Record First Encounter
                                        </Button>
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
                                <CardDescription>
                                    Laboratory test results and reports
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                {filteredLabResults.length > 0 ? (
                                    <div className="space-y-4">
                                        {filteredLabResults.map((result) => (
                                            <div key={result.id} className="border rounded-lg p-4 hover:bg-muted/50 transition-colors">
                                                <div className="flex items-center justify-between">
                                                    <div className="space-y-2">
                                                        <div className="flex items-center gap-3">
                                                            <h3 className="font-semibold">{result.patient_name}</h3>
                                                            <Badge className={getStatusColor(result.status)}>
                                                                {result.status}
                                                            </Badge>
                                                        </div>
                                                        <div className="flex items-center gap-4 text-sm text-muted-foreground">
                                                            <div className="flex items-center gap-1">
                                                                <Calendar className="h-4 w-4" />
                                                                Ordered: {new Date(result.ordered_at).toLocaleDateString()}
                                                            </div>
                                                            <div className="flex items-center gap-1">
                                                                <Calendar className="h-4 w-4" />
                                                                Result: {new Date(result.result_date).toLocaleDateString()}
                                                            </div>
                                                        </div>
                                                        <p className="text-sm">
                                                            <strong>Test:</strong> {result.test_name}
                                                        </p>
                                                        <p className="text-sm">
                                                            <strong>Result:</strong> {result.result}
                                                        </p>
                                                        <p className="text-sm text-muted-foreground">
                                                            <strong>Normal Range:</strong> {result.normal_range}
                                                        </p>
                                                    </div>
                                                    <div className="flex items-center gap-2">
                                                        <Button variant="outline" size="sm">
                                                            <Eye className="h-4 w-4" />
                                                        </Button>
                                                        <Button variant="outline" size="sm">
                                                            <Download className="h-4 w-4" />
                                                        </Button>
                                                    </div>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                ) : (
                                    <div className="text-center py-12">
                                        <Activity className="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                                        <h3 className="text-lg font-semibold mb-2">No lab results found</h3>
                                        <p className="text-muted-foreground">
                                            {searchTerm || selectedPatient ? 'Try adjusting your filters.' : 'No lab results available yet.'}
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
                                <CardDescription>
                                    Medication prescriptions and orders
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                {filteredPrescriptions.length > 0 ? (
                                    <div className="space-y-4">
                                        {filteredPrescriptions.map((prescription) => (
                                            <div key={prescription.id} className="border rounded-lg p-4 hover:bg-muted/50 transition-colors">
                                                <div className="flex items-center justify-between">
                                                    <div className="space-y-2">
                                                        <div className="flex items-center gap-3">
                                                            <h3 className="font-semibold">{prescription.patient_name}</h3>
                                                            <Badge className={getStatusColor(prescription.status)}>
                                                                {prescription.status}
                                                            </Badge>
                                                        </div>
                                                        <div className="flex items-center gap-4 text-sm text-muted-foreground">
                                                            <div className="flex items-center gap-1">
                                                                <Calendar className="h-4 w-4" />
                                                                Issued: {new Date(prescription.issued_at).toLocaleDateString()}
                                                            </div>
                                                        </div>
                                                        <p className="text-sm">
                                                            <strong>Prescription #:</strong> {prescription.prescription_number}
                                                        </p>
                                                        <p className="text-sm">
                                                            <strong>Medication:</strong> {prescription.medication}
                                                        </p>
                                                        <p className="text-sm">
                                                            <strong>Dosage:</strong> {prescription.dosage}
                                                        </p>
                                                        <p className="text-sm">
                                                            <strong>Instructions:</strong> {prescription.instructions}
                                                        </p>
                                                    </div>
                                                    <div className="flex items-center gap-2">
                                                        <Button variant="outline" size="sm">
                                                            <Eye className="h-4 w-4" />
                                                        </Button>
                                                        <Button variant="outline" size="sm">
                                                            <Download className="h-4 w-4" />
                                                        </Button>
                                                    </div>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                ) : (
                                    <div className="text-center py-12">
                                        <Pill className="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                                        <h3 className="text-lg font-semibold mb-2">No prescriptions found</h3>
                                        <p className="text-muted-foreground">
                                            {searchTerm || selectedPatient ? 'Try adjusting your filters.' : 'No prescriptions recorded yet.'}
                                        </p>
                                    </div>
                                )}
                            </CardContent>
                        </Card>
                    </TabsContent>
                </Tabs>

                {/* View Patient Dialog */}
                <Dialog open={isViewPatientOpen} onOpenChange={setIsViewPatientOpen}>
                    <DialogContent className="max-w-2xl">
                        <DialogHeader>
                            <DialogTitle>Patient Details</DialogTitle>
                            <DialogDescription>
                                Complete patient information and medical history
                            </DialogDescription>
                        </DialogHeader>
                        {selectedPatientData && (
                            <PatientDetailsView patient={selectedPatientData} />
                        )}
                    </DialogContent>
                </Dialog>
            </div>
        </AppLayout>
    );
}

// Create Encounter Form Component
function CreateEncounterForm({ patients, onSuccess }: {
    patients: Patient[];
    onSuccess: () => void;
}) {
    const [formData, setFormData] = useState({
        patient_id: '',
        type: 'consultation',
        chief_complaint: '',
        diagnosis: '',
        treatment: '',
        doctor_notes: '',
        status: 'completed'
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        // Handle form submission via API
        console.log('Create encounter:', formData);
        onSuccess();
    };

    return (
        <form onSubmit={handleSubmit} className="space-y-4">
            <div className="space-y-2">
                <Label htmlFor="patient">Patient *</Label>
                <Select value={formData.patient_id} onValueChange={(value) => setFormData({...formData, patient_id: value})}>
                    <SelectTrigger>
                        <SelectValue placeholder="Select patient" />
                    </SelectTrigger>
                    <SelectContent>
                        {patients.map((patient) => (
                            <SelectItem key={patient.id} value={patient.id.toString()}>
                                {patient.name}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
            </div>
            <div className="grid gap-4 md:grid-cols-2">
                <div className="space-y-2">
                    <Label htmlFor="type">Encounter Type *</Label>
                    <Select value={formData.type} onValueChange={(value) => setFormData({...formData, type: value})}>
                        <SelectTrigger>
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="consultation">Consultation</SelectItem>
                            <SelectItem value="follow_up">Follow-up</SelectItem>
                            <SelectItem value="emergency">Emergency</SelectItem>
                            <SelectItem value="routine_checkup">Routine Checkup</SelectItem>
                            <SelectItem value="specialist_consultation">Specialist Consultation</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                <div className="space-y-2">
                    <Label htmlFor="status">Status</Label>
                    <Select value={formData.status} onValueChange={(value) => setFormData({...formData, status: value})}>
                        <SelectTrigger>
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="in_progress">In Progress</SelectItem>
                            <SelectItem value="completed">Completed</SelectItem>
                            <SelectItem value="pending">Pending</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </div>
            <div className="space-y-2">
                <Label htmlFor="chief_complaint">Chief Complaint *</Label>
                <Textarea
                    id="chief_complaint"
                    value={formData.chief_complaint}
                    onChange={(e) => setFormData({...formData, chief_complaint: e.target.value})}
                    placeholder="Primary reason for the visit"
                    rows={3}
                    required
                />
            </div>
            <div className="space-y-2">
                <Label htmlFor="diagnosis">Diagnosis *</Label>
                <Textarea
                    id="diagnosis"
                    value={formData.diagnosis}
                    onChange={(e) => setFormData({...formData, diagnosis: e.target.value})}
                    placeholder="Medical diagnosis"
                    rows={3}
                    required
                />
            </div>
            <div className="space-y-2">
                <Label htmlFor="treatment">Treatment *</Label>
                <Textarea
                    id="treatment"
                    value={formData.treatment}
                    onChange={(e) => setFormData({...formData, treatment: e.target.value})}
                    placeholder="Treatment plan and recommendations"
                    rows={3}
                    required
                />
            </div>
            <div className="space-y-2">
                <Label htmlFor="doctor_notes">Doctor Notes</Label>
                <Textarea
                    id="doctor_notes"
                    value={formData.doctor_notes}
                    onChange={(e) => setFormData({...formData, doctor_notes: e.target.value})}
                    placeholder="Additional notes and observations"
                    rows={3}
                />
            </div>
            <div className="flex justify-end gap-2">
                <Button type="button" variant="outline" onClick={onSuccess}>
                    Cancel
                </Button>
                <Button type="submit">
                    Create Encounter
                </Button>
            </div>
        </form>
    );
}

// Patient Details View Component
function PatientDetailsView({ patient }: { patient: Patient }) {
    return (
        <div className="space-y-6">
            <div className="grid gap-4 md:grid-cols-2">
                <div className="space-y-2">
                    <Label>Full Name</Label>
                    <p className="text-sm font-medium">{patient.name}</p>
                </div>
                <div className="space-y-2">
                    <Label>Date of Birth</Label>
                    <p className="text-sm">{new Date(patient.dob).toLocaleDateString()}</p>
                </div>
                <div className="space-y-2">
                    <Label>Sex</Label>
                    <p className="text-sm">{patient.sex}</p>
                </div>
                <div className="space-y-2">
                    <Label>Age</Label>
                    <p className="text-sm">{new Date().getFullYear() - new Date(patient.dob).getFullYear()} years</p>
                </div>
            </div>

            <div className="space-y-2">
                <Label>Contact Information</Label>
                <div className="space-y-1">
                    {patient.contact.phone && (
                        <p className="text-sm">📞 {patient.contact.phone}</p>
                    )}
                    {patient.contact.email && (
                        <p className="text-sm">✉️ {patient.contact.email}</p>
                    )}
                </div>
            </div>

            {patient.allergies.length > 0 && (
                <div className="space-y-2">
                    <Label>Allergies</Label>
                    <div className="flex flex-wrap gap-2">
                        {patient.allergies.map((allergy, index) => (
                            <Badge key={index} variant="destructive">
                                {allergy}
                            </Badge>
                        ))}
                    </div>
                </div>
            )}

            <div className="space-y-2">
                <Label>Last Visit</Label>
                <p className="text-sm">{new Date(patient.last_visit).toLocaleDateString()}</p>
            </div>
        </div>
    );
}
