import { useState } from 'react';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { 
    Clock, 
    User, 
    FileText, 
    Stethoscope, 
    Activity, 
    Pill, 
    Upload,
    Download,
    CheckCircle,
    AlertCircle,
    Plus,
    Eye,
    Edit,
    Calendar,
    Heart,
    Thermometer,
    Gauge
} from 'lucide-react';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Checkbox } from '@/components/ui/checkbox';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Doctor Dashboard',
        href: '/doctor/dashboard',
    },
    {
        title: 'Patient Queue',
        href: '/doctor/queue',
    },
];

interface QueueItem {
    id: number;
    encounter_id: number;
    patient_id: number;
    patient_name: string;
    patient_dob: string;
    patient_sex: string;
    encounter_number: string;
    visit_type: string;
    reason_for_visit: string;
    queue_position: number;
    estimated_wait_time: string;
    status: string;
    created_at: string;
    patient: {
        id: number;
        name: string;
        dob: string;
        sex: string;
        contact: {
            phone?: string;
            email?: string;
        };
        allergies?: string[];
        medical_history?: string[];
    };
    encounter: {
        id: number;
        encounter_number: string;
        visit_type: string;
        reason_for_visit: string;
        status: string;
        soap_notes?: {
            subjective: string;
            objective: string;
            assessment: string;
            plan: string;
        };
        vital_signs?: {
            blood_pressure: string;
            heart_rate: number;
            temperature: number;
            weight: number;
            height: number;
        };
        diagnosis?: string[];
        treatment_plan?: string;
        follow_up_date?: string;
        payment_status: string;
    };
}

interface DoctorQueueProps {
    queueItems: QueueItem[];
    completedEncounters: QueueItem[];
}

export default function DoctorQueue({ queueItems, completedEncounters }: DoctorQueueProps) {
    const [selectedPatient, setSelectedPatient] = useState<QueueItem | null>(null);
    const [isEncounterDialogOpen, setIsEncounterDialogOpen] = useState(false);
    const [isVitalsDialogOpen, setIsVitalsDialogOpen] = useState(false);
    const [isPrescriptionDialogOpen, setIsPrescriptionDialogOpen] = useState(false);
    const [isLabResultsDialogOpen, setIsLabResultsDialogOpen] = useState(false);
    const [isFileUploadDialogOpen, setIsFileUploadDialogOpen] = useState(false);

    const handleSelectPatient = (patient: QueueItem) => {
        setSelectedPatient(patient);
        setIsEncounterDialogOpen(true);
    };

    const handleCompleteEncounter = async (encounterId: number) => {
        try {
            const response = await fetch(`/api/v1/encounters/${encounterId}/complete`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('token')}`,
                    'Content-Type': 'application/json',
                },
            });

            if (response.ok) {
                // Refresh the page or update state
                window.location.reload();
            }
        } catch (error) {
            console.error('Error completing encounter:', error);
        }
    };

    const getStatusColor = (status: string) => {
        switch (status.toLowerCase()) {
            case 'completed':
                return 'bg-green-100 text-green-800';
            case 'in-progress':
                return 'bg-blue-100 text-blue-800';
            case 'pending':
                return 'bg-yellow-100 text-yellow-800';
            case 'cancelled':
                return 'bg-red-100 text-red-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    };

    const getVisitTypeColor = (type: string) => {
        switch (type.toLowerCase()) {
            case 'emergency':
                return 'bg-red-100 text-red-800';
            case 'follow-up':
                return 'bg-blue-100 text-blue-800';
            case 'opd':
                return 'bg-green-100 text-green-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    };

    const getPriorityColor = (position: number) => {
        if (position <= 3) return 'bg-red-100 text-red-800';
        if (position <= 6) return 'bg-yellow-100 text-yellow-800';
        return 'bg-green-100 text-green-800';
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Patient Queue" />
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Patient Queue</h1>
                        <p className="text-muted-foreground">
                            Manage patient encounters and clinical documentation
                        </p>
                    </div>
                </div>

                {/* Main Content Tabs */}
                <Tabs defaultValue="active" className="space-y-4">
                    <TabsList>
                        <TabsTrigger value="active">Active Queue ({queueItems.length})</TabsTrigger>
                        <TabsTrigger value="completed">Completed ({completedEncounters.length})</TabsTrigger>
                    </TabsList>

                    {/* Active Queue Tab */}
                    <TabsContent value="active" className="space-y-4">
                        <Card>
                            <CardHeader>
                                <CardTitle>Active Patient Queue</CardTitle>
                                <CardDescription>
                                    Patients waiting to be seen. Click on a patient to start their encounter.
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                {queueItems.length > 0 ? (
                                    <div className="space-y-4">
                                        {queueItems.map((item) => (
                                            <Card key={item.id} className="p-4 hover:shadow-md transition-shadow cursor-pointer"
                                                  onClick={() => handleSelectPatient(item)}>
                                                <div className="flex items-center justify-between">
                                                    <div className="space-y-2">
                                                        <div className="flex items-center gap-3">
                                                            <Badge className={getPriorityColor(item.queue_position)}>
                                                                #{item.queue_position}
                                                            </Badge>
                                                            <h4 className="font-semibold text-lg">{item.patient_name}</h4>
                                                            <Badge className={getVisitTypeColor(item.visit_type)}>
                                                                {item.visit_type.toUpperCase()}
                                                            </Badge>
                                                        </div>
                                                        <div className="grid grid-cols-2 gap-4 text-sm text-muted-foreground">
                                                            <div>
                                                                <p><strong>Patient ID:</strong> {item.patient_id}</p>
                                                                <p><strong>DOB:</strong> {item.patient_dob} ({item.patient_sex})</p>
                                                                <p><strong>Encounter:</strong> {item.encounter_number}</p>
                                                            </div>
                                                            <div>
                                                                <p><strong>Wait Time:</strong> {item.estimated_wait_time}</p>
                                                                <p><strong>Arrived:</strong> {new Date(item.created_at).toLocaleTimeString()}</p>
                                                                {item.patient.contact.phone && (
                                                                    <p><strong>Phone:</strong> {item.patient.contact.phone}</p>
                                                                )}
                                                            </div>
                                                        </div>
                                                        <div className="mt-2">
                                                            <p className="text-sm"><strong>Reason for Visit:</strong> {item.reason_for_visit}</p>
                                                        </div>
                                                        {item.patient.allergies && item.patient.allergies.length > 0 && (
                                                            <div className="mt-2">
                                                                <Badge variant="destructive" className="text-xs">
                                                                    <AlertCircle className="h-3 w-3 mr-1" />
                                                                    Allergies: {item.patient.allergies.join(', ')}
                                                                </Badge>
                                                            </div>
                                                        )}
                                                    </div>
                                                    <div className="text-right">
                                                        <Button size="sm">
                                                            <Stethoscope className="h-4 w-4 mr-2" />
                                                            Start Encounter
                                                        </Button>
                                                        <p className="text-xs text-muted-foreground mt-2">
                                                            Click to begin consultation
                                                        </p>
                                                    </div>
                                                </div>
                                            </Card>
                                        ))}
                                    </div>
                                ) : (
                                    <div className="text-center py-12">
                                        <Clock className="h-16 w-16 text-muted-foreground mx-auto mb-4" />
                                        <h3 className="text-xl font-semibold mb-2">No patients in queue</h3>
                                        <p className="text-muted-foreground">
                                            The queue is currently empty. Patients will appear here when they check in.
                                        </p>
                                    </div>
                                )}
                            </CardContent>
                        </Card>
                    </TabsContent>

                    {/* Completed Encounters Tab */}
                    <TabsContent value="completed" className="space-y-4">
                        <Card>
                            <CardHeader>
                                <CardTitle>Completed Encounters</CardTitle>
                                <CardDescription>
                                    Recently completed patient encounters
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                {completedEncounters.length > 0 ? (
                                    <div className="space-y-4">
                                        {completedEncounters.map((item) => (
                                            <Card key={item.id} className="p-4">
                                                <div className="flex items-center justify-between">
                                                    <div className="space-y-2">
                                                        <div className="flex items-center gap-3">
                                                            <CheckCircle className="h-5 w-5 text-green-600" />
                                                            <h4 className="font-semibold">{item.patient_name}</h4>
                                                            <Badge className={getVisitTypeColor(item.visit_type)}>
                                                                {item.visit_type.toUpperCase()}
                                                            </Badge>
                                                            <Badge className={getStatusColor(item.status)}>
                                                                {item.status}
                                                            </Badge>
                                                        </div>
                                                        <div className="grid grid-cols-2 gap-4 text-sm text-muted-foreground">
                                                            <div>
                                                                <p><strong>Encounter:</strong> {item.encounter_number}</p>
                                                                <p><strong>Completed:</strong> {new Date(item.created_at).toLocaleString()}</p>
                                                            </div>
                                                            <div>
                                                                <p><strong>Payment:</strong> {item.encounter.payment_status}</p>
                                                                {item.encounter.follow_up_date && (
                                                                    <p><strong>Follow-up:</strong> {item.encounter.follow_up_date}</p>
                                                                )}
                                                            </div>
                                                        </div>
                                                        {item.encounter.diagnosis && item.encounter.diagnosis.length > 0 && (
                                                            <div className="mt-2">
                                                                <p className="text-sm"><strong>Diagnosis:</strong> {item.encounter.diagnosis.join(', ')}</p>
                                                            </div>
                                                        )}
                                                    </div>
                                                    <div className="flex gap-2">
                                                        <Button size="sm" variant="outline">
                                                            <Eye className="h-4 w-4 mr-2" />
                                                            View
                                                        </Button>
                                                        <Button size="sm" variant="outline">
                                                            <Download className="h-4 w-4 mr-2" />
                                                            Print
                                                        </Button>
                                                    </div>
                                                </div>
                                            </Card>
                                        ))}
                                    </div>
                                ) : (
                                    <div className="text-center py-12">
                                        <CheckCircle className="h-16 w-16 text-muted-foreground mx-auto mb-4" />
                                        <h3 className="text-xl font-semibold mb-2">No completed encounters</h3>
                                        <p className="text-muted-foreground">
                                            Completed encounters will appear here.
                                        </p>
                                    </div>
                                )}
                            </CardContent>
                        </Card>
                    </TabsContent>
                </Tabs>

                {/* Clinical Documentation Dialog */}
                <Dialog open={isEncounterDialogOpen} onOpenChange={setIsEncounterDialogOpen}>
                    <DialogContent className="max-w-6xl max-h-[90vh] overflow-y-auto">
                        <DialogHeader>
                            <DialogTitle>Clinical Documentation - {selectedPatient?.patient_name}</DialogTitle>
                            <DialogDescription>
                                Complete the patient encounter documentation
                            </DialogDescription>
                        </DialogHeader>
                        {selectedPatient && (
                            <ClinicalDocumentationForm 
                                patient={selectedPatient}
                                onComplete={() => {
                                    setIsEncounterDialogOpen(false);
                                    handleCompleteEncounter(selectedPatient.encounter_id);
                                }}
                            />
                        )}
                    </DialogContent>
                </Dialog>
            </div>
        </AppLayout>
    );
}

// Clinical Documentation Form Component
function ClinicalDocumentationForm({ 
    patient, 
    onComplete 
}: { 
    patient: QueueItem;
    onComplete: () => void;
}) {
    const [activeTab, setActiveTab] = useState('soap');
    const [formData, setFormData] = useState({
        soap_notes: {
            subjective: '',
            objective: '',
            assessment: '',
            plan: ''
        },
        vital_signs: {
            blood_pressure: '',
            heart_rate: '',
            temperature: '',
            weight: '',
            height: ''
        },
        diagnosis: '',
        treatment_plan: '',
        follow_up_date: '',
        payment_status: 'pending',
        icd_codes: [] as string[]
    });
    const [isSubmitting, setIsSubmitting] = useState(false);

    const handleSubmit = async () => {
        setIsSubmitting(true);
        try {
            const response = await fetch(`/api/v1/encounters/${patient.encounter_id}`, {
                method: 'PUT',
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('token')}`,
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData),
            });

            if (response.ok) {
                onComplete();
            }
        } catch (error) {
            console.error('Error updating encounter:', error);
        } finally {
            setIsSubmitting(false);
        }
    };

    return (
        <div className="space-y-6">
            {/* Patient Info Header */}
            <Card>
                <CardHeader>
                    <CardTitle className="flex items-center gap-2">
                        <User className="h-5 w-5" />
                        Patient Information
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div className="grid grid-cols-3 gap-4">
                        <div>
                            <Label className="text-sm font-medium">Name</Label>
                            <p className="text-sm">{patient.patient_name}</p>
                        </div>
                        <div>
                            <Label className="text-sm font-medium">DOB / Sex</Label>
                            <p className="text-sm">{patient.patient_dob} ({patient.patient_sex})</p>
                        </div>
                        <div>
                            <Label className="text-sm font-medium">Encounter #</Label>
                            <p className="text-sm">{patient.encounter_number}</p>
                        </div>
                        <div>
                            <Label className="text-sm font-medium">Visit Type</Label>
                            <p className="text-sm">{patient.visit_type}</p>
                        </div>
                        <div>
                            <Label className="text-sm font-medium">Reason for Visit</Label>
                            <p className="text-sm">{patient.reason_for_visit}</p>
                        </div>
                        {patient.patient.allergies && patient.patient.allergies.length > 0 && (
                            <div>
                                <Label className="text-sm font-medium text-red-600">Allergies</Label>
                                <p className="text-sm text-red-600">{patient.patient.allergies.join(', ')}</p>
                            </div>
                        )}
                    </div>
                </CardContent>
            </Card>

            {/* Documentation Tabs */}
            <Tabs value={activeTab} onValueChange={setActiveTab} className="space-y-4">
                <TabsList className="grid w-full grid-cols-6">
                    <TabsTrigger value="soap">SOAP Notes</TabsTrigger>
                    <TabsTrigger value="vitals">Vital Signs</TabsTrigger>
                    <TabsTrigger value="diagnosis">Diagnosis</TabsTrigger>
                    <TabsTrigger value="prescription">Prescription</TabsTrigger>
                    <TabsTrigger value="lab">Lab Results</TabsTrigger>
                    <TabsTrigger value="files">Files</TabsTrigger>
                </TabsList>

                {/* SOAP Notes Tab */}
                <TabsContent value="soap" className="space-y-4">
                    <Card>
                        <CardHeader>
                            <CardTitle>SOAP Notes</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div>
                                <Label htmlFor="subjective">Subjective</Label>
                                <Textarea
                                    id="subjective"
                                    placeholder="Patient's chief complaint, history of present illness, symptoms..."
                                    value={formData.soap_notes.subjective}
                                    onChange={(e) => setFormData({
                                        ...formData,
                                        soap_notes: { ...formData.soap_notes, subjective: e.target.value }
                                    })}
                                    rows={4}
                                />
                            </div>
                            <div>
                                <Label htmlFor="objective">Objective</Label>
                                <Textarea
                                    id="objective"
                                    placeholder="Physical examination findings, vital signs, test results..."
                                    value={formData.soap_notes.objective}
                                    onChange={(e) => setFormData({
                                        ...formData,
                                        soap_notes: { ...formData.soap_notes, objective: e.target.value }
                                    })}
                                    rows={4}
                                />
                            </div>
                            <div>
                                <Label htmlFor="assessment">Assessment</Label>
                                <Textarea
                                    id="assessment"
                                    placeholder="Clinical impression, differential diagnosis..."
                                    value={formData.soap_notes.assessment}
                                    onChange={(e) => setFormData({
                                        ...formData,
                                        soap_notes: { ...formData.soap_notes, assessment: e.target.value }
                                    })}
                                    rows={3}
                                />
                            </div>
                            <div>
                                <Label htmlFor="plan">Plan</Label>
                                <Textarea
                                    id="plan"
                                    placeholder="Treatment plan, medications, follow-up instructions..."
                                    value={formData.soap_notes.plan}
                                    onChange={(e) => setFormData({
                                        ...formData,
                                        soap_notes: { ...formData.soap_notes, plan: e.target.value }
                                    })}
                                    rows={4}
                                />
                            </div>
                        </CardContent>
                    </Card>
                </TabsContent>

                {/* Vital Signs Tab */}
                <TabsContent value="vitals" className="space-y-4">
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Activity className="h-5 w-5" />
                                Vital Signs
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <Label htmlFor="blood_pressure">Blood Pressure</Label>
                                    <Input
                                        id="blood_pressure"
                                        placeholder="120/80"
                                        value={formData.vital_signs.blood_pressure}
                                        onChange={(e) => setFormData({
                                            ...formData,
                                            vital_signs: { ...formData.vital_signs, blood_pressure: e.target.value }
                                        })}
                                    />
                                </div>
                                <div>
                                    <Label htmlFor="heart_rate">Heart Rate (BPM)</Label>
                                    <Input
                                        id="heart_rate"
                                        type="number"
                                        placeholder="72"
                                        value={formData.vital_signs.heart_rate}
                                        onChange={(e) => setFormData({
                                            ...formData,
                                            vital_signs: { ...formData.vital_signs, heart_rate: e.target.value }
                                        })}
                                    />
                                </div>
                                <div>
                                    <Label htmlFor="temperature">Temperature (°F)</Label>
                                    <Input
                                        id="temperature"
                                        type="number"
                                        step="0.1"
                                        placeholder="98.6"
                                        value={formData.vital_signs.temperature}
                                        onChange={(e) => setFormData({
                                            ...formData,
                                            vital_signs: { ...formData.vital_signs, temperature: e.target.value }
                                        })}
                                    />
                                </div>
                                <div>
                                    <Label htmlFor="weight">Weight (lbs)</Label>
                                    <Input
                                        id="weight"
                                        type="number"
                                        step="0.1"
                                        placeholder="150"
                                        value={formData.vital_signs.weight}
                                        onChange={(e) => setFormData({
                                            ...formData,
                                            vital_signs: { ...formData.vital_signs, weight: e.target.value }
                                        })}
                                    />
                                </div>
                                <div>
                                    <Label htmlFor="height">Height (inches)</Label>
                                    <Input
                                        id="height"
                                        type="number"
                                        step="0.1"
                                        placeholder="68"
                                        value={formData.vital_signs.height}
                                        onChange={(e) => setFormData({
                                            ...formData,
                                            vital_signs: { ...formData.vital_signs, height: e.target.value }
                                        })}
                                    />
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </TabsContent>

                {/* Diagnosis Tab */}
                <TabsContent value="diagnosis" className="space-y-4">
                    <Card>
                        <CardHeader>
                            <CardTitle>Diagnosis & Treatment Plan</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div>
                                <Label htmlFor="diagnosis">Primary Diagnosis</Label>
                                <Textarea
                                    id="diagnosis"
                                    placeholder="Enter primary diagnosis..."
                                    value={formData.diagnosis}
                                    onChange={(e) => setFormData({ ...formData, diagnosis: e.target.value })}
                                    rows={3}
                                />
                            </div>
                            <div>
                                <Label htmlFor="icd_codes">ICD-10 Codes</Label>
                                <Input
                                    id="icd_codes"
                                    placeholder="Enter ICD-10 codes separated by commas..."
                                    value={formData.icd_codes.join(', ')}
                                    onChange={(e) => setFormData({
                                        ...formData,
                                        icd_codes: e.target.value.split(',').map(code => code.trim()).filter(code => code)
                                    })}
                                />
                            </div>
                            <div>
                                <Label htmlFor="treatment_plan">Treatment Plan</Label>
                                <Textarea
                                    id="treatment_plan"
                                    placeholder="Describe the treatment plan..."
                                    value={formData.treatment_plan}
                                    onChange={(e) => setFormData({ ...formData, treatment_plan: e.target.value })}
                                    rows={4}
                                />
                            </div>
                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <Label htmlFor="follow_up_date">Follow-up Date</Label>
                                    <Input
                                        id="follow_up_date"
                                        type="date"
                                        value={formData.follow_up_date}
                                        onChange={(e) => setFormData({ ...formData, follow_up_date: e.target.value })}
                                    />
                                </div>
                                <div>
                                    <Label htmlFor="payment_status">Payment Status</Label>
                                    <Select value={formData.payment_status} onValueChange={(value) => setFormData({ ...formData, payment_status: value })}>
                                        <SelectTrigger>
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="pending">Pending</SelectItem>
                                            <SelectItem value="paid">Paid</SelectItem>
                                            <SelectItem value="partial">Partial</SelectItem>
                                            <SelectItem value="insurance">Insurance</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </TabsContent>

                {/* Prescription Tab */}
                <TabsContent value="prescription" className="space-y-4">
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Pill className="h-5 w-5" />
                                Prescription Management
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-center py-8">
                                <Pill className="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                                <h3 className="text-lg font-semibold mb-2">Prescription Management</h3>
                                <p className="text-muted-foreground mb-4">
                                    Create and manage prescriptions for this patient
                                </p>
                                <Button>
                                    <Plus className="h-4 w-4 mr-2" />
                                    Create Prescription
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                </TabsContent>

                {/* Lab Results Tab */}
                <TabsContent value="lab" className="space-y-4">
                    <Card>
                        <CardHeader>
                            <CardTitle>Lab Results</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-center py-8">
                                <Activity className="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                                <h3 className="text-lg font-semibold mb-2">Lab Results</h3>
                                <p className="text-muted-foreground mb-4">
                                    View and manage lab results for this patient
                                </p>
                                <Button variant="outline">
                                    <Upload className="h-4 w-4 mr-2" />
                                    Upload Lab Results
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                </TabsContent>

                {/* Files Tab */}
                <TabsContent value="files" className="space-y-4">
                    <Card>
                        <CardHeader>
                            <CardTitle>File Attachments</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-center py-8">
                                <Upload className="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                                <h3 className="text-lg font-semibold mb-2">File Attachments</h3>
                                <p className="text-muted-foreground mb-4">
                                    Upload X-rays, ECG, reports, and other medical files
                                </p>
                                <Button variant="outline">
                                    <Upload className="h-4 w-4 mr-2" />
                                    Upload Files
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                </TabsContent>
            </Tabs>

            {/* Action Buttons */}
            <div className="flex justify-between pt-4">
                <Button variant="outline" onClick={() => setIsEncounterDialogOpen(false)}>
                    Save Draft
                </Button>
                <div className="flex gap-2">
                    <Button variant="outline">
                        <Download className="h-4 w-4 mr-2" />
                        Print Prescription
                    </Button>
                    <Button variant="outline">
                        <Download className="h-4 w-4 mr-2" />
                        Print Medical Report
                    </Button>
                    <Button onClick={handleSubmit} disabled={isSubmitting}>
                        <CheckCircle className="h-4 w-4 mr-2" />
                        {isSubmitting ? 'Completing...' : 'Complete Encounter'}
                    </Button>
                </div>
            </div>
        </div>
    );
}
