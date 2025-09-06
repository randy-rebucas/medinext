import { useState } from 'react';
import AppLayout from '@/layouts/app-layout';
import { receptionistDashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { 
    Search, 
    UserPlus, 
    FileText, 
    Users, 
    Clock, 
    Calendar,
    Stethoscope,
    AlertCircle,
    CheckCircle,
    Plus,
    Eye,
    Edit
} from 'lucide-react';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Receptionist Dashboard',
        href: receptionistDashboard(),
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
    emergency_contact?: string;
    allergies?: string[];
    last_visit?: string;
}

interface Encounter {
    id: number;
    patient_id: number;
    patient_name: string;
    encounter_number: string;
    visit_type: string;
    reason_for_visit: string;
    status: string;
    created_at: string;
    queue_position?: number;
}

interface QueueItem {
    id: number;
    encounter_id: number;
    patient_name: string;
    encounter_number: string;
    visit_type: string;
    reason_for_visit: string;
    queue_position: number;
    estimated_wait_time: string;
    status: string;
}

interface ReceptionistDashboardProps {
    stats: {
        totalPatients: number;
        todayAppointments: number;
        activeQueue: number;
        completedEncounters: number;
    };
    recentPatients: Patient[];
    activeQueue: QueueItem[];
    recentEncounters: Encounter[];
}

export default function ReceptionistDashboard({ 
    stats, 
    recentPatients, 
    activeQueue, 
    recentEncounters 
}: ReceptionistDashboardProps) {
    const [searchQuery, setSearchQuery] = useState('');
    const [searchResults, setSearchResults] = useState<Patient[]>([]);
    const [selectedPatient, setSelectedPatient] = useState<Patient | null>(null);
    const [isSearching, setIsSearching] = useState(false);
    const [isPatientDialogOpen, setIsPatientDialogOpen] = useState(false);
    const [isEncounterDialogOpen, setIsEncounterDialogOpen] = useState(false);
    const [isNewPatientDialogOpen, setIsNewPatientDialogOpen] = useState(false);

    // Patient search function
    const handlePatientSearch = async () => {
        if (!searchQuery.trim()) return;
        
        setIsSearching(true);
        try {
            // This would call the API endpoint for patient search
            const response = await fetch(`/api/v1/patients/search?q=${encodeURIComponent(searchQuery)}`, {
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('token')}`,
                    'Content-Type': 'application/json',
                },
            });
            
            if (response.ok) {
                const data = await response.json();
                setSearchResults(data.data || []);
            } else {
                setSearchResults([]);
            }
        } catch (error) {
            console.error('Search error:', error);
            setSearchResults([]);
        } finally {
            setIsSearching(false);
        }
    };

    // Handle patient selection
    const handlePatientSelect = (patient: Patient) => {
        setSelectedPatient(patient);
        setIsPatientDialogOpen(true);
    };

    // Handle new encounter creation
    const handleCreateEncounter = () => {
        if (selectedPatient) {
            setIsEncounterDialogOpen(true);
        }
    };

    // Handle adding to queue
    const handleAddToQueue = async (encounterId: number) => {
        try {
            const response = await fetch(`/api/v1/encounters/${encounterId}/queue`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('token')}`,
                    'Content-Type': 'application/json',
                },
            });
            
            if (response.ok) {
                // Refresh the queue data
                window.location.reload();
            }
        } catch (error) {
            console.error('Error adding to queue:', error);
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

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Receptionist Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Receptionist Dashboard</h1>
                        <p className="text-muted-foreground">
                            Manage patient registration, encounters, and queue
                        </p>
                    </div>
                </div>

                {/* Stats Cards */}
                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total Patients</CardTitle>
                            <Users className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.totalPatients}</div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Today's Appointments</CardTitle>
                            <Calendar className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.todayAppointments}</div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Active Queue</CardTitle>
                            <Clock className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.activeQueue}</div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Completed Today</CardTitle>
                            <CheckCircle className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.completedEncounters}</div>
                        </CardContent>
                    </Card>
                </div>

                {/* Main Content Tabs */}
                <Tabs defaultValue="search" className="space-y-4">
                    <TabsList>
                        <TabsTrigger value="search">Patient Search</TabsTrigger>
                        <TabsTrigger value="queue">Active Queue</TabsTrigger>
                        <TabsTrigger value="recent">Recent Encounters</TabsTrigger>
                    </TabsList>

                    {/* Patient Search Tab */}
                    <TabsContent value="search" className="space-y-4">
                        <Card>
                            <CardHeader>
                                <CardTitle>Search for Patient</CardTitle>
                                <CardDescription>
                                    Search by patient name or patient ID to find existing patients
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="flex gap-2">
                                    <Input
                                        placeholder="Enter patient name or ID..."
                                        value={searchQuery}
                                        onChange={(e) => setSearchQuery(e.target.value)}
                                        onKeyPress={(e) => e.key === 'Enter' && handlePatientSearch()}
                                    />
                                    <Button onClick={handlePatientSearch} disabled={isSearching}>
                                        <Search className="h-4 w-4 mr-2" />
                                        {isSearching ? 'Searching...' : 'Search'}
                                    </Button>
                                </div>

                                {/* Search Results */}
                                {searchResults.length > 0 && (
                                    <div className="space-y-2">
                                        <h3 className="text-lg font-semibold">Search Results</h3>
                                        {searchResults.map((patient) => (
                                            <Card key={patient.id} className="p-4">
                                                <div className="flex items-center justify-between">
                                                    <div className="space-y-1">
                                                        <h4 className="font-semibold">{patient.name}</h4>
                                                        <p className="text-sm text-muted-foreground">
                                                            ID: {patient.patient_id} | DOB: {patient.dob} | {patient.sex}
                                                        </p>
                                                        {patient.contact.phone && (
                                                            <p className="text-sm text-muted-foreground">
                                                                Phone: {patient.contact.phone}
                                                            </p>
                                                        )}
                                                    </div>
                                                    <Button 
                                                        onClick={() => handlePatientSelect(patient)}
                                                        size="sm"
                                                    >
                                                        <Eye className="h-4 w-4 mr-2" />
                                                        Select
                                                    </Button>
                                                </div>
                                            </Card>
                                        ))}
                                    </div>
                                )}

                                {searchResults.length === 0 && searchQuery && !isSearching && (
                                    <div className="text-center py-8">
                                        <AlertCircle className="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                                        <h3 className="text-lg font-semibold mb-2">Patient not found</h3>
                                        <p className="text-muted-foreground mb-4">
                                            No patient found with the search criteria.
                                        </p>
                                        <Dialog open={isNewPatientDialogOpen} onOpenChange={setIsNewPatientDialogOpen}>
                                            <DialogTrigger asChild>
                                                <Button>
                                                    <UserPlus className="h-4 w-4 mr-2" />
                                                    Register New Patient
                                                </Button>
                                            </DialogTrigger>
                                            <DialogContent className="max-w-2xl">
                                                <DialogHeader>
                                                    <DialogTitle>Register New Patient</DialogTitle>
                                                    <DialogDescription>
                                                        Enter the patient's information to create a new record
                                                    </DialogDescription>
                                                </DialogHeader>
                                                <NewPatientForm onSuccess={() => setIsNewPatientDialogOpen(false)} />
                                            </DialogContent>
                                        </Dialog>
                                    </div>
                                )}
                            </CardContent>
                        </Card>
                    </TabsContent>

                    {/* Active Queue Tab */}
                    <TabsContent value="queue" className="space-y-4">
                        <Card>
                            <CardHeader>
                                <CardTitle>Active Queue</CardTitle>
                                <CardDescription>
                                    Current patients waiting to see the doctor
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                {activeQueue.length > 0 ? (
                                    <div className="space-y-4">
                                        {activeQueue.map((item) => (
                                            <Card key={item.id} className="p-4">
                                                <div className="flex items-center justify-between">
                                                    <div className="space-y-1">
                                                        <div className="flex items-center gap-2">
                                                            <Badge variant="outline">#{item.queue_position}</Badge>
                                                            <h4 className="font-semibold">{item.patient_name}</h4>
                                                        </div>
                                                        <p className="text-sm text-muted-foreground">
                                                            Encounter: {item.encounter_number}
                                                        </p>
                                                        <div className="flex gap-2">
                                                            <Badge className={getVisitTypeColor(item.visit_type)}>
                                                                {item.visit_type}
                                                            </Badge>
                                                            <span className="text-sm text-muted-foreground">
                                                                Wait time: {item.estimated_wait_time}
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div className="text-right">
                                                        <p className="text-sm font-medium">{item.reason_for_visit}</p>
                                                        <Badge className={getStatusColor(item.status)}>
                                                            {item.status}
                                                        </Badge>
                                                    </div>
                                                </div>
                                            </Card>
                                        ))}
                                    </div>
                                ) : (
                                    <div className="text-center py-8">
                                        <Clock className="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                                        <h3 className="text-lg font-semibold mb-2">No patients in queue</h3>
                                        <p className="text-muted-foreground">
                                            The queue is currently empty.
                                        </p>
                                    </div>
                                )}
                            </CardContent>
                        </Card>
                    </TabsContent>

                    {/* Recent Encounters Tab */}
                    <TabsContent value="recent" className="space-y-4">
                        <Card>
                            <CardHeader>
                                <CardTitle>Recent Encounters</CardTitle>
                                <CardDescription>
                                    Latest patient encounters and their status
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                {recentEncounters.length > 0 ? (
                                    <div className="space-y-4">
                                        {recentEncounters.map((encounter) => (
                                            <Card key={encounter.id} className="p-4">
                                                <div className="flex items-center justify-between">
                                                    <div className="space-y-1">
                                                        <h4 className="font-semibold">{encounter.patient_name}</h4>
                                                        <p className="text-sm text-muted-foreground">
                                                            Encounter: {encounter.encounter_number}
                                                        </p>
                                                        <div className="flex gap-2">
                                                            <Badge className={getVisitTypeColor(encounter.visit_type)}>
                                                                {encounter.visit_type}
                                                            </Badge>
                                                            <span className="text-sm text-muted-foreground">
                                                                {new Date(encounter.created_at).toLocaleString()}
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div className="text-right">
                                                        <p className="text-sm font-medium">{encounter.reason_for_visit}</p>
                                                        <Badge className={getStatusColor(encounter.status)}>
                                                            {encounter.status}
                                                        </Badge>
                                                    </div>
                                                </div>
                                            </Card>
                                        ))}
                                    </div>
                                ) : (
                                    <div className="text-center py-8">
                                        <FileText className="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                                        <h3 className="text-lg font-semibold mb-2">No recent encounters</h3>
                                        <p className="text-muted-foreground">
                                            No encounters have been created today.
                                        </p>
                                    </div>
                                )}
                            </CardContent>
                        </Card>
                    </TabsContent>
                </Tabs>

                {/* Patient Actions Dialog */}
                <Dialog open={isPatientDialogOpen} onOpenChange={setIsPatientDialogOpen}>
                    <DialogContent className="max-w-2xl">
                        <DialogHeader>
                            <DialogTitle>Patient Profile</DialogTitle>
                            <DialogDescription>
                                Patient information and available actions
                            </DialogDescription>
                        </DialogHeader>
                        {selectedPatient && (
                            <div className="space-y-4">
                                <div className="grid grid-cols-2 gap-4">
                                    <div>
                                        <Label className="text-sm font-medium">Name</Label>
                                        <p className="text-sm">{selectedPatient.name}</p>
                                    </div>
                                    <div>
                                        <Label className="text-sm font-medium">Patient ID</Label>
                                        <p className="text-sm">{selectedPatient.patient_id}</p>
                                    </div>
                                    <div>
                                        <Label className="text-sm font-medium">Date of Birth</Label>
                                        <p className="text-sm">{selectedPatient.dob}</p>
                                    </div>
                                    <div>
                                        <Label className="text-sm font-medium">Sex</Label>
                                        <p className="text-sm">{selectedPatient.sex}</p>
                                    </div>
                                    {selectedPatient.contact.phone && (
                                        <div>
                                            <Label className="text-sm font-medium">Phone</Label>
                                            <p className="text-sm">{selectedPatient.contact.phone}</p>
                                        </div>
                                    )}
                                    {selectedPatient.contact.email && (
                                        <div>
                                            <Label className="text-sm font-medium">Email</Label>
                                            <p className="text-sm">{selectedPatient.contact.email}</p>
                                        </div>
                                    )}
                                </div>
                                
                                <div className="flex gap-2 pt-4">
                                    <Button onClick={handleCreateEncounter}>
                                        <FileText className="h-4 w-4 mr-2" />
                                        Add New Encounter
                                    </Button>
                                    <Button variant="outline">
                                        <Eye className="h-4 w-4 mr-2" />
                                        View History
                                    </Button>
                                </div>
                            </div>
                        )}
                    </DialogContent>
                </Dialog>

                {/* New Encounter Dialog */}
                <Dialog open={isEncounterDialogOpen} onOpenChange={setIsEncounterDialogOpen}>
                    <DialogContent className="max-w-2xl">
                        <DialogHeader>
                            <DialogTitle>Create New Encounter</DialogTitle>
                            <DialogDescription>
                                Create a new encounter for {selectedPatient?.name}
                            </DialogDescription>
                        </DialogHeader>
                        <NewEncounterForm 
                            patient={selectedPatient}
                            onSuccess={() => {
                                setIsEncounterDialogOpen(false);
                                setIsPatientDialogOpen(false);
                            }}
                        />
                    </DialogContent>
                </Dialog>
            </div>
        </AppLayout>
    );
}

// New Patient Registration Form Component
function NewPatientForm({ onSuccess }: { onSuccess: () => void }) {
    const [formData, setFormData] = useState({
        name: '',
        dob: '',
        sex: '',
        phone: '',
        email: '',
        address: '',
        emergency_contact: '',
        allergies: ''
    });
    const [isSubmitting, setIsSubmitting] = useState(false);

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setIsSubmitting(true);

        try {
            const response = await fetch('/api/v1/patients', {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('token')}`,
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData),
            });

            if (response.ok) {
                onSuccess();
                // Reset form
                setFormData({
                    name: '',
                    dob: '',
                    sex: '',
                    phone: '',
                    email: '',
                    address: '',
                    emergency_contact: '',
                    allergies: ''
                });
            }
        } catch (error) {
            console.error('Error creating patient:', error);
        } finally {
            setIsSubmitting(false);
        }
    };

    return (
        <form onSubmit={handleSubmit} className="space-y-4">
            <div className="grid grid-cols-2 gap-4">
                <div>
                    <Label htmlFor="name">Full Name *</Label>
                    <Input
                        id="name"
                        value={formData.name}
                        onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                        required
                    />
                </div>
                <div>
                    <Label htmlFor="dob">Date of Birth *</Label>
                    <Input
                        id="dob"
                        type="date"
                        value={formData.dob}
                        onChange={(e) => setFormData({ ...formData, dob: e.target.value })}
                        required
                    />
                </div>
                <div>
                    <Label htmlFor="sex">Sex *</Label>
                    <Select value={formData.sex} onValueChange={(value) => setFormData({ ...formData, sex: value })}>
                        <SelectTrigger>
                            <SelectValue placeholder="Select sex" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="male">Male</SelectItem>
                            <SelectItem value="female">Female</SelectItem>
                            <SelectItem value="other">Other</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                <div>
                    <Label htmlFor="phone">Phone Number</Label>
                    <Input
                        id="phone"
                        value={formData.phone}
                        onChange={(e) => setFormData({ ...formData, phone: e.target.value })}
                    />
                </div>
                <div>
                    <Label htmlFor="email">Email</Label>
                    <Input
                        id="email"
                        type="email"
                        value={formData.email}
                        onChange={(e) => setFormData({ ...formData, email: e.target.value })}
                    />
                </div>
                <div>
                    <Label htmlFor="emergency_contact">Emergency Contact</Label>
                    <Input
                        id="emergency_contact"
                        value={formData.emergency_contact}
                        onChange={(e) => setFormData({ ...formData, emergency_contact: e.target.value })}
                    />
                </div>
            </div>
            <div>
                <Label htmlFor="address">Address</Label>
                <Textarea
                    id="address"
                    value={formData.address}
                    onChange={(e) => setFormData({ ...formData, address: e.target.value })}
                />
            </div>
            <div>
                <Label htmlFor="allergies">Known Allergies</Label>
                <Textarea
                    id="allergies"
                    value={formData.allergies}
                    onChange={(e) => setFormData({ ...formData, allergies: e.target.value })}
                    placeholder="List any known allergies..."
                />
            </div>
            <div className="flex justify-end gap-2">
                <Button type="button" variant="outline" onClick={onSuccess}>
                    Cancel
                </Button>
                <Button type="submit" disabled={isSubmitting}>
                    {isSubmitting ? 'Creating...' : 'Create Patient'}
                </Button>
            </div>
        </form>
    );
}

// New Encounter Form Component
function NewEncounterForm({ 
    patient, 
    onSuccess 
}: { 
    patient: Patient | null;
    onSuccess: () => void;
}) {
    const [formData, setFormData] = useState({
        visit_type: '',
        reason_for_visit: '',
        notes: ''
    });
    const [isSubmitting, setIsSubmitting] = useState(false);

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        if (!patient) return;

        setIsSubmitting(true);

        try {
            const response = await fetch('/api/v1/encounters', {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('token')}`,
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    patient_id: patient.id,
                    ...formData
                }),
            });

            if (response.ok) {
                const encounter = await response.json();
                onSuccess();
                // Optionally add to queue immediately
                // await handleAddToQueue(encounter.data.id);
            }
        } catch (error) {
            console.error('Error creating encounter:', error);
        } finally {
            setIsSubmitting(false);
        }
    };

    return (
        <form onSubmit={handleSubmit} className="space-y-4">
            <div>
                <Label htmlFor="visit_type">Visit Type *</Label>
                <Select value={formData.visit_type} onValueChange={(value) => setFormData({ ...formData, visit_type: value })}>
                    <SelectTrigger>
                        <SelectValue placeholder="Select visit type" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="opd">OPD (Outpatient Department)</SelectItem>
                        <SelectItem value="emergency">Emergency</SelectItem>
                        <SelectItem value="follow-up">Follow-up</SelectItem>
                        <SelectItem value="consultation">Consultation</SelectItem>
                    </SelectContent>
                </Select>
            </div>
            <div>
                <Label htmlFor="reason_for_visit">Reason for Visit *</Label>
                <Textarea
                    id="reason_for_visit"
                    value={formData.reason_for_visit}
                    onChange={(e) => setFormData({ ...formData, reason_for_visit: e.target.value })}
                    placeholder="Describe the reason for the visit..."
                    required
                />
            </div>
            <div>
                <Label htmlFor="notes">Additional Notes</Label>
                <Textarea
                    id="notes"
                    value={formData.notes}
                    onChange={(e) => setFormData({ ...formData, notes: e.target.value })}
                    placeholder="Any additional information..."
                />
            </div>
            <div className="flex justify-end gap-2">
                <Button type="button" variant="outline" onClick={onSuccess}>
                    Cancel
                </Button>
                <Button type="submit" disabled={isSubmitting}>
                    {isSubmitting ? 'Creating...' : 'Create Encounter'}
                </Button>
            </div>
        </form>
    );
}
