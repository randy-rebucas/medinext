import { useState } from 'react';
import AppLayout from '@/layouts/app-layout';
import { doctorQueue } from '@/routes';
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
    Stethoscope,
    Activity,
    Pill,
    Upload,
    Download,
    CheckCircle,
    AlertCircle,
    Plus,
    Eye,
    Search,
    Filter,
    Building2,
    Shield
} from 'lucide-react';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Doctor Dashboard',
        href: '/doctor/dashboard',
    },
    {
        title: 'Patient Queue',
        href: doctorQueue(),
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
    user?: {
        id: number;
        name: string;
        email: string;
        role: string;
        clinic_id?: number;
        clinic?: {
            id: number;
            name: string;
        };
    };
    queueItems?: QueueItem[];
    completedEncounters?: QueueItem[];
    permissions?: string[];
}

export default function DoctorQueue({ user, queueItems = [], completedEncounters = [], permissions = [] }: DoctorQueueProps) {
    const hasPermission = (permission: string) => permissions.includes(permission);
    const [selectedPatient, setSelectedPatient] = useState<QueueItem | null>(null);
    const [isEncounterDialogOpen, setIsEncounterDialogOpen] = useState(false);
    const [searchTerm, setSearchTerm] = useState('');
    const [statusFilter, setStatusFilter] = useState('all');
    const [visitTypeFilter, setVisitTypeFilter] = useState('all');

    // Ensure we have valid data
    const safeQueueItems = Array.isArray(queueItems) ? queueItems : [];
    const safeCompletedEncounters = Array.isArray(completedEncounters) ? completedEncounters : [];

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

    // Filter queue items based on search and filters
    const filteredQueueItems = safeQueueItems.filter(item => {
        if (!item) return false;
        const matchesSearch = (item.patient_name || '').toLowerCase().includes(searchTerm.toLowerCase()) ||
                            (item.encounter_number || '').toLowerCase().includes(searchTerm.toLowerCase()) ||
                            (item.reason_for_visit || '').toLowerCase().includes(searchTerm.toLowerCase());
        const matchesStatus = !statusFilter || statusFilter === 'all' || (item.status || '').toLowerCase() === statusFilter.toLowerCase();
        const matchesVisitType = !visitTypeFilter || visitTypeFilter === 'all' || (item.visit_type || '').toLowerCase() === visitTypeFilter.toLowerCase();

        return matchesSearch && matchesStatus && matchesVisitType;
    });

    const filteredCompletedEncounters = safeCompletedEncounters.filter(item => {
        if (!item) return false;
        const matchesSearch = (item.patient_name || '').toLowerCase().includes(searchTerm.toLowerCase()) ||
                            (item.encounter_number || '').toLowerCase().includes(searchTerm.toLowerCase());
        return matchesSearch;
    });

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Patient Queue - Medinext">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&family=instrument-sans:400,500,600" rel="stylesheet" />
            </Head>
            <div className="min-h-screen bg-gradient-to-br from-slate-50 via-white to-blue-50 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900">
                <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
                    {/* Modern Header */}
                    <div className="relative overflow-hidden rounded-2xl bg-gradient-to-r from-blue-600 to-purple-600 p-8 text-white shadow-xl">
                        <div className="absolute inset-0 bg-black/10"></div>
                        <div className="relative flex items-center justify-between">
                            <div>
                                <h1 className="text-3xl font-bold tracking-tight">Patient Queue</h1>
                                <p className="mt-2 text-blue-100">
                                    {user?.clinic?.name || 'No Clinic'} • Manage patient encounters and clinical documentation
                                </p>
                            </div>
                            <div className="flex items-center gap-3">
                                <Badge variant="secondary" className="flex items-center gap-1 bg-white/20 text-white border-white/30 hover:bg-white/30">
                                    <Shield className="h-3 w-3" />
                                    Doctor
                                </Badge>
                                {user?.clinic && (
                                    <Badge variant="secondary" className="flex items-center gap-1 bg-white/20 text-white border-white/30 hover:bg-white/30">
                                        <Building2 className="h-3 w-3" />
                                        {user.clinic.name}
                                    </Badge>
                                )}
                            </div>
                        </div>
                        {/* Decorative elements */}
                        <div className="absolute -top-4 -right-4 w-24 h-24 bg-white/10 rounded-full"></div>
                        <div className="absolute -bottom-2 -left-2 w-16 h-16 bg-white/5 rounded-full"></div>
                    </div>

                    {/* Filters */}
                    <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2 text-lg font-semibold text-slate-900 dark:text-white">
                                <div className="p-1 bg-blue-100 dark:bg-blue-900/20 rounded-md">
                                    <Filter className="h-5 w-5 text-blue-600 dark:text-blue-400" />
                                </div>
                                Filters & Search
                            </CardTitle>
                            <CardDescription className="text-slate-600 dark:text-slate-300">
                                Filter and search through your patient queue
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                                <div className="space-y-2">
                                    <Label htmlFor="search" className="text-sm font-medium text-slate-700 dark:text-slate-300">Search</Label>
                                    <div className="relative">
                                        <Search className="absolute left-3 top-3 h-4 w-4 text-slate-400" />
                                        <Input
                                            id="search"
                                            placeholder="Search patients, encounter numbers, or reason for visit..."
                                            value={searchTerm}
                                            onChange={(e) => setSearchTerm(e.target.value)}
                                            className="pl-10 border-slate-200 dark:border-slate-700 focus:border-blue-500 dark:focus:border-blue-400"
                                        />
                                    </div>
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="status" className="text-sm font-medium text-slate-700 dark:text-slate-300">Status</Label>
                                    <Select value={statusFilter} onValueChange={setStatusFilter}>
                                        <SelectTrigger className="border-slate-200 dark:border-slate-700 focus:border-blue-500 dark:focus:border-blue-400">
                                            <SelectValue placeholder="All statuses" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="all">All statuses</SelectItem>
                                            <SelectItem value="pending">Pending</SelectItem>
                                            <SelectItem value="in-progress">In Progress</SelectItem>
                                            <SelectItem value="completed">Completed</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="visitType" className="text-sm font-medium text-slate-700 dark:text-slate-300">Visit Type</Label>
                                    <Select value={visitTypeFilter} onValueChange={setVisitTypeFilter}>
                                        <SelectTrigger className="border-slate-200 dark:border-slate-700 focus:border-blue-500 dark:focus:border-blue-400">
                                            <SelectValue placeholder="All types" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="all">All types</SelectItem>
                                            <SelectItem value="emergency">Emergency</SelectItem>
                                            <SelectItem value="follow-up">Follow-up</SelectItem>
                                            <SelectItem value="opd">OPD</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                                <div className="space-y-2">
                                    <Label className="text-sm font-medium text-slate-700 dark:text-slate-300">&nbsp;</Label>
                                    <Button
                                        variant="outline"
                                        onClick={() => {
                                            setSearchTerm('');
                                            setStatusFilter('all');
                                            setVisitTypeFilter('all');
                                        }}
                                        className="w-full border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 hover:border-slate-300 dark:hover:border-slate-600 transition-all duration-200"
                                    >
                                        Clear Filters
                                    </Button>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                {/* Main Content Tabs */}
                <Tabs defaultValue="active" className="space-y-4">
                    <TabsList>
                        <TabsTrigger value="active">Active Queue ({filteredQueueItems.length})</TabsTrigger>
                        <TabsTrigger value="completed">Completed ({filteredCompletedEncounters.length})</TabsTrigger>
                    </TabsList>

                    {/* Active Queue Tab */}
                    <TabsContent value="active" className="space-y-4">
                        <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2 text-lg font-semibold text-slate-900 dark:text-white">
                                    <div className="p-1 bg-orange-100 dark:bg-orange-900/20 rounded-md">
                                        <Clock className="h-5 w-5 text-orange-600 dark:text-orange-400" />
                                    </div>
                                    Active Patient Queue ({filteredQueueItems.length})
                                </CardTitle>
                                <CardDescription className="text-slate-600 dark:text-slate-300">
                                    Patients waiting to be seen. Click on a patient to start their encounter.
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                {filteredQueueItems.length > 0 ? (
                                    <div className="space-y-4">
                                        {filteredQueueItems.map((item) => (
                                            <div key={item.id} className="border border-slate-200 dark:border-slate-700 rounded-xl p-6 hover:bg-slate-50 dark:hover:bg-slate-700/50 hover:shadow-md transition-all duration-200 cursor-pointer border-l-4 border-l-blue-500 hover:border-l-blue-600"
                                                 onClick={() => handleSelectPatient(item)}>
                                                <div className="flex items-start justify-between">
                                                    <div className="space-y-3 flex-1">
                                                        <div className="flex items-center gap-3 flex-wrap">
                                                            <Badge className={`${getPriorityColor(item.queue_position)} font-semibold border-0`}>
                                                                #{item.queue_position}
                                                            </Badge>
                                                            <h4 className="font-semibold text-xl text-slate-900 dark:text-white">{item.patient_name}</h4>
                                                            <Badge className={`${getVisitTypeColor(item.visit_type)} font-medium border-0`}>
                                                                {item.visit_type.toUpperCase()}
                                                            </Badge>
                                                            <Badge className={`${getStatusColor(item.status)} border-0`}>
                                                                {item.status.replace('-', ' ').toUpperCase()}
                                                            </Badge>
                                                        </div>

                                                        <div className="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                                            <div className="space-y-1">
                                                                <p className="text-slate-600 dark:text-slate-400"><strong>Patient ID:</strong> {item.patient_id}</p>
                                                                <p className="text-slate-600 dark:text-slate-400"><strong>DOB:</strong> {item.patient_dob} ({item.patient_sex})</p>
                                                                <p className="text-slate-600 dark:text-slate-400"><strong>Encounter:</strong> {item.encounter_number}</p>
                                                            </div>
                                                            <div className="space-y-1">
                                                                <p className="text-slate-600 dark:text-slate-400"><strong>Wait Time:</strong> {item.estimated_wait_time}</p>
                                                                <p className="text-slate-600 dark:text-slate-400"><strong>Arrived:</strong> {new Date(item.created_at).toLocaleTimeString()}</p>
                                                                {item.patient.contact.phone && (
                                                                    <p className="text-slate-600 dark:text-slate-400"><strong>Phone:</strong> {item.patient.contact.phone}</p>
                                                                )}
                                                            </div>
                                                            <div className="space-y-1">
                                                                <p className="text-slate-600 dark:text-slate-400"><strong>Reason for Visit:</strong></p>
                                                                <p className="text-sm bg-slate-50 dark:bg-slate-700/50 p-2 rounded-md text-slate-700 dark:text-slate-300">{item.reason_for_visit}</p>
                                                            </div>
                                                        </div>

                                                        {item.patient.allergies && item.patient.allergies.length > 0 && (
                                                            <div className="mt-3">
                                                                <Badge variant="destructive" className="text-xs">
                                                                    <AlertCircle className="h-3 w-3 mr-1" />
                                                                    Allergies: {item.patient.allergies.join(', ')}
                                                                </Badge>
                                                            </div>
                                                        )}
                                                    </div>

                                                    <div className="text-right ml-6">
                                                        {hasPermission('work_on_queue') && (
                                                            <Button size="sm" className="mb-2 bg-blue-600 hover:bg-blue-700 text-white border-0">
                                                                <Stethoscope className="h-4 w-4 mr-2" />
                                                                Start Encounter
                                                            </Button>
                                                        )}
                                                        <p className="text-xs text-slate-500 dark:text-slate-400">
                                                            Click to begin consultation
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                ) : (
                                    <div className="text-center py-12">
                                        <div className="p-4 bg-slate-100 dark:bg-slate-700/50 rounded-full w-fit mx-auto mb-4">
                                            <Clock className="h-12 w-12 text-slate-400" />
                                        </div>
                                        <h3 className="text-lg font-semibold text-slate-900 dark:text-white mb-2">
                                            {searchTerm || (statusFilter && statusFilter !== 'all') || (visitTypeFilter && visitTypeFilter !== 'all') ? 'No matching patients found' : 'No patients in queue'}
                                        </h3>
                                        <p className="text-slate-600 dark:text-slate-400 mb-4">
                                            {searchTerm || (statusFilter && statusFilter !== 'all') || (visitTypeFilter && visitTypeFilter !== 'all')
                                                ? 'Try adjusting your search criteria or filters to find patients.'
                                                : 'The queue is currently empty. Patients will appear here when they check in.'
                                            }
                                        </p>
                                        {(searchTerm || (statusFilter && statusFilter !== 'all') || (visitTypeFilter && visitTypeFilter !== 'all')) && (
                                            <Button
                                                variant="outline"
                                                className="border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 hover:border-slate-300 dark:hover:border-slate-600 transition-all duration-200"
                                                onClick={() => {
                                                    setSearchTerm('');
                                                    setStatusFilter('all');
                                                    setVisitTypeFilter('all');
                                                }}
                                            >
                                                Clear Filters
                                            </Button>
                                        )}
                                    </div>
                                )}
                            </CardContent>
                        </Card>
                    </TabsContent>

                    {/* Completed Encounters Tab */}
                    <TabsContent value="completed" className="space-y-4">
                        <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2 text-lg font-semibold text-slate-900 dark:text-white">
                                    <div className="p-1 bg-green-100 dark:bg-green-900/20 rounded-md">
                                        <CheckCircle className="h-5 w-5 text-green-600 dark:text-green-400" />
                                    </div>
                                    Completed Encounters ({filteredCompletedEncounters.length})
                                </CardTitle>
                                <CardDescription className="text-slate-600 dark:text-slate-300">
                                    Recently completed patient encounters
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                {filteredCompletedEncounters.length > 0 ? (
                                    <div className="space-y-4">
                                        {filteredCompletedEncounters.map((item) => (
                                            <div key={item.id} className="border border-slate-200 dark:border-slate-700 rounded-xl p-6 hover:bg-slate-50 dark:hover:bg-slate-700/50 hover:shadow-md transition-all duration-200 border-l-4 border-l-green-500">
                                                <div className="flex items-start justify-between">
                                                    <div className="space-y-3 flex-1">
                                                        <div className="flex items-center gap-3 flex-wrap">
                                                            <CheckCircle className="h-5 w-5 text-green-600" />
                                                            <h4 className="font-semibold text-lg text-slate-900 dark:text-white">{item.patient_name}</h4>
                                                            <Badge className={`${getVisitTypeColor(item.visit_type)} font-medium border-0`}>
                                                                {item.visit_type.toUpperCase()}
                                                            </Badge>
                                                            <Badge className={`${getStatusColor(item.status)} border-0`}>
                                                                {item.status.replace('-', ' ').toUpperCase()}
                                                            </Badge>
                                                        </div>

                                                        <div className="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                                            <div className="space-y-1">
                                                                <p className="text-slate-600 dark:text-slate-400"><strong>Encounter:</strong> {item.encounter_number}</p>
                                                                <p className="text-slate-600 dark:text-slate-400"><strong>Completed:</strong> {new Date(item.created_at).toLocaleString()}</p>
                                                            </div>
                                                            <div className="space-y-1">
                                                                <p className="text-slate-600 dark:text-slate-400"><strong>Payment:</strong> {item.encounter.payment_status}</p>
                                                                {item.encounter.follow_up_date && (
                                                                    <p className="text-slate-600 dark:text-slate-400"><strong>Follow-up:</strong> {item.encounter.follow_up_date}</p>
                                                                )}
                                                            </div>
                                                            <div className="space-y-1">
                                                                {item.encounter.diagnosis && item.encounter.diagnosis.length > 0 && (
                                                                    <div>
                                                                        <p className="text-slate-600 dark:text-slate-400"><strong>Diagnosis:</strong></p>
                                                                        <p className="text-sm bg-slate-50 dark:bg-slate-700/50 p-2 rounded-md text-slate-700 dark:text-slate-300">{item.encounter.diagnosis.join(', ')}</p>
                                                                    </div>
                                                                )}
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div className="flex gap-2 ml-6">
                                                        {hasPermission('view_medical_records') && (
                                                            <Button size="sm" variant="outline" className="border-slate-200 dark:border-slate-700 hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:border-blue-300 dark:hover:border-blue-600">
                                                                <Eye className="h-4 w-4 mr-2" />
                                                                View
                                                            </Button>
                                                        )}
                                                        {hasPermission('export_medical_records') && (
                                                            <Button size="sm" variant="outline" className="border-slate-200 dark:border-slate-700 hover:bg-green-50 dark:hover:bg-green-900/20 hover:border-green-300 dark:hover:border-green-600">
                                                                <Download className="h-4 w-4 mr-2" />
                                                                Print
                                                            </Button>
                                                        )}
                                                    </div>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                ) : (
                                    <div className="text-center py-12">
                                        <div className="p-4 bg-slate-100 dark:bg-slate-700/50 rounded-full w-fit mx-auto mb-4">
                                            <CheckCircle className="h-12 w-12 text-slate-400" />
                                        </div>
                                        <h3 className="text-lg font-semibold text-slate-900 dark:text-white mb-2">
                                            {searchTerm ? 'No matching completed encounters found' : 'No completed encounters'}
                                        </h3>
                                        <p className="text-slate-600 dark:text-slate-400 mb-4">
                                            {searchTerm
                                                ? 'Try adjusting your search criteria to find completed encounters.'
                                                : 'Completed encounters will appear here after patient consultations are finished.'
                                            }
                                        </p>
                                        {searchTerm && (
                                            <Button
                                                variant="outline"
                                                className="border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 hover:border-slate-300 dark:hover:border-slate-600 transition-all duration-200"
                                                onClick={() => setSearchTerm('')}
                                            >
                                                Clear Search
                                            </Button>
                                        )}
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
                                onCancel={() => setIsEncounterDialogOpen(false)}
                            />
                        )}
                    </DialogContent>
                </Dialog>
                </div>
            </div>
        </AppLayout>
    );
}

// Clinical Documentation Form Component
function ClinicalDocumentationForm({
    patient,
    onComplete,
    onCancel
}: {
    patient: QueueItem;
    onComplete: () => void;
    onCancel: () => void;
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
                <Button variant="outline" onClick={onCancel}>
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
