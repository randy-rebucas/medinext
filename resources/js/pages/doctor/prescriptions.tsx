import { useState } from 'react';
import AppLayout from '@/layouts/app-layout';
import { doctorPrescriptions } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import {
    Pill,
    Search,
    Filter,
    Plus,
    Eye,
    Download,
    CheckCircle,
    XCircle,
    Clock,
    AlertTriangle,
    FileText,
    Calendar,
    User,
    Building2,
    Shield
} from 'lucide-react';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Doctor Dashboard',
        href: '/doctor/dashboard',
    },
    {
        title: 'Prescriptions',
        href: doctorPrescriptions(),
    },
];

interface Prescription {
    id: number;
    prescription_number: string;
    patient_id: number;
    patient_name: string;
    status: string;
    prescription_type: string;
    diagnosis: string;
    instructions: string;
    issued_at: string;
    expiry_date: string;
    refills_allowed: number;
    refills_remaining: number;
    total_cost: number;
    verification_status: boolean | null;
    items: PrescriptionItem[];
}

interface PrescriptionItem {
    id: number;
    medication_name: string;
    dosage: string;
    frequency: string;
    duration: string;
    quantity: number;
    instructions: string;
    side_effects?: string;
    contraindications?: string;
}

interface DoctorPrescriptionsProps {
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
    prescriptions?: Prescription[];
    patients?: Array<{ id: number; name: string }>;
    filters?: {
        status: string;
        type: string;
        patient_id: string;
        date_range: string;
    };
    permissions?: string[];
}

export default function DoctorPrescriptions({
    user,
    prescriptions = [],
    patients = [],
    filters = {
        status: '',
        type: '',
        patient_id: '',
        date_range: ''
    },
    permissions = []
}: DoctorPrescriptionsProps) {
    const hasPermission = (permission: string) => permissions.includes(permission);
    const [searchTerm, setSearchTerm] = useState('');
    const [statusFilter, setStatusFilter] = useState(filters.status || '');
    const [typeFilter, setTypeFilter] = useState(filters.type || '');
    const [patientFilter, setPatientFilter] = useState(filters.patient_id || '');
    const [dateRange, setDateRange] = useState(filters.date_range || '');
    const [activeTab, setActiveTab] = useState('all');
    const [isCreateDialogOpen, setIsCreateDialogOpen] = useState(false);
    const [isViewDialogOpen, setIsViewDialogOpen] = useState(false);
    const [selectedPrescription, setSelectedPrescription] = useState<Prescription | null>(null);

    const filteredPrescriptions = prescriptions.filter(prescription => {
        const matchesSearch = prescription.patient_name.toLowerCase().includes(searchTerm.toLowerCase()) ||
                            prescription.prescription_number.toLowerCase().includes(searchTerm.toLowerCase()) ||
                            prescription.diagnosis.toLowerCase().includes(searchTerm.toLowerCase());
        const matchesStatus = !statusFilter || statusFilter === 'all' || prescription.status === statusFilter;
        const matchesType = !typeFilter || typeFilter === 'all' || prescription.prescription_type === typeFilter;
        const matchesPatient = !patientFilter || patientFilter === 'all' || prescription.patient_id.toString() === patientFilter;

        return matchesSearch && matchesStatus && matchesType && matchesPatient;
    });

    const getTabCounts = () => {
        return {
            all: filteredPrescriptions.length,
            active: filteredPrescriptions.filter(p => p.status === 'active').length,
            draft: filteredPrescriptions.filter(p => p.status === 'draft').length,
            expired: filteredPrescriptions.filter(p => p.status === 'expired').length,
            pending: filteredPrescriptions.filter(p => p.verification_status === null).length
        };
    };

    const tabCounts = getTabCounts();

    const handleViewPrescription = (prescription: Prescription) => {
        setSelectedPrescription(prescription);
        setIsViewDialogOpen(true);
    };

    const handleVerifyPrescription = (prescriptionId: number, verified: boolean) => {
        // Handle verification via API
        console.log('Verify prescription:', prescriptionId, verified);
    };

    const handleDeletePrescription = (prescriptionId: number) => {
        if (confirm('Are you sure you want to delete this prescription?')) {
            // Handle deletion via API
            console.log('Delete prescription:', prescriptionId);
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Prescriptions - Medinext">
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
                                <h1 className="text-3xl font-bold tracking-tight">Prescriptions</h1>
                                <p className="mt-2 text-blue-100">
                                    {user?.clinic?.name || 'No Clinic'} • Manage patient prescriptions and medication orders
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
                                <div className="flex gap-2">
                                    {hasPermission('export_prescriptions') && (
                                        <Button variant="outline" className="bg-white/20 hover:bg-white/30 text-white border-white/30 hover:border-white/40">
                                            <Download className="h-4 w-4 mr-2" />
                                            Export
                                        </Button>
                                    )}
                                    {hasPermission('create_prescriptions') && (
                                        <Dialog open={isCreateDialogOpen} onOpenChange={setIsCreateDialogOpen}>
                                            <DialogTrigger asChild>
                                                <Button className="bg-white/20 hover:bg-white/30 text-white border-white/30 hover:border-white/40">
                                                    <Plus className="h-4 w-4 mr-2" />
                                                    New Prescription
                                                </Button>
                                            </DialogTrigger>
                                            <DialogContent className="max-w-4xl max-h-[90vh] overflow-y-auto">
                                                <DialogHeader>
                                                    <DialogTitle>Create New Prescription</DialogTitle>
                                                    <DialogDescription>
                                                        Write a new prescription for a patient
                                                    </DialogDescription>
                                                </DialogHeader>
                                                <CreatePrescriptionForm
                                                    patients={patients}
                                                    onSuccess={() => setIsCreateDialogOpen(false)}
                                                />
                                            </DialogContent>
                                        </Dialog>
                                    )}
                                </div>
                            </div>
                        </div>
                        {/* Decorative elements */}
                        <div className="absolute -top-4 -right-4 w-24 h-24 bg-white/10 rounded-full"></div>
                        <div className="absolute -bottom-2 -left-2 w-16 h-16 bg-white/5 rounded-full"></div>
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
                        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-5">
                            <div className="space-y-2">
                                <Label htmlFor="search">Search</Label>
                                <div className="relative">
                                    <Search className="absolute left-2 top-2.5 h-4 w-4 text-muted-foreground" />
                                    <Input
                                        id="search"
                                        placeholder="Search prescriptions..."
                                        value={searchTerm}
                                        onChange={(e) => setSearchTerm(e.target.value)}
                                        className="pl-8"
                                    />
                                </div>
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="status">Status</Label>
                                <Select value={statusFilter} onValueChange={setStatusFilter}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="All statuses" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">All statuses</SelectItem>
                                        <SelectItem value="draft">Draft</SelectItem>
                                        <SelectItem value="active">Active</SelectItem>
                                        <SelectItem value="dispensed">Dispensed</SelectItem>
                                        <SelectItem value="expired">Expired</SelectItem>
                                        <SelectItem value="cancelled">Cancelled</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="type">Type</Label>
                                <Select value={typeFilter} onValueChange={setTypeFilter}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="All types" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">All types</SelectItem>
                                        <SelectItem value="new">New</SelectItem>
                                        <SelectItem value="refill">Refill</SelectItem>
                                        <SelectItem value="emergency">Emergency</SelectItem>
                                        <SelectItem value="controlled">Controlled</SelectItem>
                                        <SelectItem value="maintenance">Maintenance</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="patient">Patient</Label>
                                <Select value={patientFilter} onValueChange={setPatientFilter}>
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
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {/* Tabs */}
                <Tabs value={activeTab} onValueChange={setActiveTab} className="w-full">
                    <TabsList className="grid w-full grid-cols-5">
                        <TabsTrigger value="all" className="flex items-center gap-2">
                            <Pill className="h-4 w-4" />
                            All ({tabCounts.all})
                        </TabsTrigger>
                        <TabsTrigger value="active" className="flex items-center gap-2">
                            <CheckCircle className="h-4 w-4" />
                            Active ({tabCounts.active})
                        </TabsTrigger>
                        <TabsTrigger value="draft" className="flex items-center gap-2">
                            <Clock className="h-4 w-4" />
                            Draft ({tabCounts.draft})
                        </TabsTrigger>
                        <TabsTrigger value="expired" className="flex items-center gap-2">
                            <XCircle className="h-4 w-4" />
                            Expired ({tabCounts.expired})
                        </TabsTrigger>
                        <TabsTrigger value="pending" className="flex items-center gap-2">
                            <AlertTriangle className="h-4 w-4" />
                            Pending ({tabCounts.pending})
                        </TabsTrigger>
                    </TabsList>

                    <TabsContent value="all" className="space-y-4">
                        <PrescriptionsList
                            prescriptions={filteredPrescriptions}
                            onView={handleViewPrescription}
                            onVerify={handleVerifyPrescription}
                            onDelete={handleDeletePrescription}
                        />
                    </TabsContent>

                    <TabsContent value="active" className="space-y-4">
                        <PrescriptionsList
                            prescriptions={filteredPrescriptions.filter(p => p.status === 'active')}
                            onView={handleViewPrescription}
                            onVerify={handleVerifyPrescription}
                            onDelete={handleDeletePrescription}
                        />
                    </TabsContent>

                    <TabsContent value="draft" className="space-y-4">
                        <PrescriptionsList
                            prescriptions={filteredPrescriptions.filter(p => p.status === 'draft')}
                            onView={handleViewPrescription}
                            onVerify={handleVerifyPrescription}
                            onDelete={handleDeletePrescription}
                        />
                    </TabsContent>

                    <TabsContent value="expired" className="space-y-4">
                        <PrescriptionsList
                            prescriptions={filteredPrescriptions.filter(p => p.status === 'expired')}
                            onView={handleViewPrescription}
                            onVerify={handleVerifyPrescription}
                            onDelete={handleDeletePrescription}
                        />
                    </TabsContent>

                    <TabsContent value="pending" className="space-y-4">
                        <PrescriptionsList
                            prescriptions={filteredPrescriptions.filter(p => p.verification_status === null)}
                            onView={handleViewPrescription}
                            onVerify={handleVerifyPrescription}
                            onDelete={handleDeletePrescription}
                        />
                    </TabsContent>
                </Tabs>

                {/* View Prescription Dialog */}
                <Dialog open={isViewDialogOpen} onOpenChange={setIsViewDialogOpen}>
                    <DialogContent className="max-w-4xl max-h-[90vh] overflow-y-auto">
                        <DialogHeader>
                            <DialogTitle>Prescription Details</DialogTitle>
                            <DialogDescription>
                                Complete prescription information and medication details
                            </DialogDescription>
                        </DialogHeader>
                        {selectedPrescription && (
                            <PrescriptionDetailsView prescription={selectedPrescription} />
                        )}
                    </DialogContent>
                </Dialog>
                </div>
            </div>
        </AppLayout>
    );
}

// Prescriptions List Component
function PrescriptionsList({
    prescriptions,
    onView,
    onVerify,
    onDelete
}: {
    prescriptions: Prescription[];
    onView: (prescription: Prescription) => void;
    onVerify: (id: number, verified: boolean) => void;
    onDelete: (id: number) => void;
}) {
    const getStatusColor = (status: string) => {
        switch (status) {
            case 'active':
                return 'bg-green-100 text-green-800';
            case 'draft':
                return 'bg-yellow-100 text-yellow-800';
            case 'dispensed':
                return 'bg-blue-100 text-blue-800';
            case 'expired':
                return 'bg-red-100 text-red-800';
            case 'cancelled':
                return 'bg-gray-100 text-gray-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    };

    const getTypeColor = (type: string) => {
        switch (type) {
            case 'emergency':
                return 'bg-red-100 text-red-800';
            case 'controlled':
                return 'bg-orange-100 text-orange-800';
            case 'new':
                return 'bg-blue-100 text-blue-800';
            case 'refill':
                return 'bg-green-100 text-green-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    };

    const getVerificationColor = (status: boolean | null) => {
        if (status === null) return 'bg-yellow-100 text-yellow-800';
        return status ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
    };

    if (prescriptions.length === 0) {
        return (
            <Card>
                <CardContent className="text-center py-12">
                    <Pill className="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                    <h3 className="text-lg font-semibold mb-2">No prescriptions found</h3>
                    <p className="text-muted-foreground">
                        No prescriptions match your current filters.
                    </p>
                </CardContent>
            </Card>
        );
    }

    return (
        <Card>
            <CardContent className="p-0">
                <div className="space-y-0">
                    {prescriptions.map((prescription) => (
                        <div key={prescription.id} className="border-b p-4 hover:bg-muted/50 transition-colors last:border-b-0">
                            <div className="flex items-center justify-between">
                                <div className="space-y-2 flex-1">
                                    <div className="flex items-center gap-3">
                                        <h3 className="font-semibold">{prescription.patient_name}</h3>
                                        <Badge className={getStatusColor(prescription.status)}>
                                            {prescription.status}
                                        </Badge>
                                        <Badge variant="outline" className={getTypeColor(prescription.prescription_type)}>
                                            {prescription.prescription_type}
                                        </Badge>
                                        <Badge variant="outline" className={getVerificationColor(prescription.verification_status)}>
                                            {prescription.verification_status === null ? 'Pending' :
                                             prescription.verification_status ? 'Verified' : 'Rejected'}
                                        </Badge>
                                    </div>
                                    <div className="flex items-center gap-4 text-sm text-muted-foreground">
                                        <div className="flex items-center gap-1">
                                            <FileText className="h-4 w-4" />
                                            {prescription.prescription_number}
                                        </div>
                                        <div className="flex items-center gap-1">
                                            <Calendar className="h-4 w-4" />
                                            {new Date(prescription.issued_at).toLocaleDateString()}
                                        </div>
                                        <div className="flex items-center gap-1">
                                            <Pill className="h-4 w-4" />
                                            {prescription.items.length} medication(s)
                                        </div>
                                        {prescription.refills_remaining > 0 && (
                                            <div className="flex items-center gap-1">
                                                <span>Refills: {prescription.refills_remaining}</span>
                                            </div>
                                        )}
                                    </div>
                                    <p className="text-sm">
                                        <strong>Diagnosis:</strong> {prescription.diagnosis}
                                    </p>
                                    <p className="text-sm text-muted-foreground">
                                        <strong>Instructions:</strong> {prescription.instructions}
                                    </p>
                                </div>
                                <div className="flex items-center gap-2">
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        onClick={() => onView(prescription)}
                                    >
                                        <Eye className="h-4 w-4" />
                                    </Button>
                                    {prescription.verification_status === null && (
                                        <>
                                            <Button
                                                variant="outline"
                                                size="sm"
                                                onClick={() => onVerify(prescription.id, true)}
                                                className="text-green-600 hover:text-green-700"
                                            >
                                                <CheckCircle className="h-4 w-4" />
                                            </Button>
                                            <Button
                                                variant="outline"
                                                size="sm"
                                                onClick={() => onVerify(prescription.id, false)}
                                                className="text-red-600 hover:text-red-700"
                                            >
                                                <XCircle className="h-4 w-4" />
                                            </Button>
                                        </>
                                    )}
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        onClick={() => onDelete(prescription.id)}
                                    >
                                        <XCircle className="h-4 w-4" />
                                    </Button>
                                </div>
                            </div>
                        </div>
                    ))}
                </div>
            </CardContent>
        </Card>
    );
}

// Create Prescription Form Component
function CreatePrescriptionForm({ patients, onSuccess }: {
    patients: Array<{ id: number; name: string }>;
    onSuccess: () => void;
}) {
    const [formData, setFormData] = useState({
        patient_id: '',
        prescription_type: 'new',
        diagnosis: '',
        instructions: '',
        refills_allowed: '0',
        expiry_days: '30',
        items: [{
            medication_name: '',
            dosage: '',
            frequency: '',
            duration: '',
            quantity: '',
            instructions: '',
            side_effects: '',
            contraindications: ''
        }]
    });

    const addMedication = () => {
        setFormData({
            ...formData,
            items: [...formData.items, {
                medication_name: '',
                dosage: '',
                frequency: '',
                duration: '',
                quantity: '',
                instructions: '',
                side_effects: '',
                contraindications: ''
            }]
        });
    };

    const removeMedication = (index: number) => {
        if (formData.items.length > 1) {
            setFormData({
                ...formData,
                items: formData.items.filter((_, i) => i !== index)
            });
        }
    };

    const updateMedication = (index: number, field: string, value: string) => {
        const updatedItems = [...formData.items];
        updatedItems[index] = { ...updatedItems[index], [field]: value };
        setFormData({ ...formData, items: updatedItems });
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        // Handle form submission via API
        console.log('Create prescription:', formData);
        onSuccess();
    };

    return (
        <form onSubmit={handleSubmit} className="space-y-6">
            <div className="grid gap-4 md:grid-cols-2">
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
                <div className="space-y-2">
                    <Label htmlFor="type">Prescription Type *</Label>
                    <Select value={formData.prescription_type} onValueChange={(value) => setFormData({...formData, prescription_type: value})}>
                        <SelectTrigger>
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="new">New Prescription</SelectItem>
                            <SelectItem value="refill">Refill</SelectItem>
                            <SelectItem value="emergency">Emergency</SelectItem>
                            <SelectItem value="controlled">Controlled Substance</SelectItem>
                            <SelectItem value="maintenance">Maintenance</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </div>

            <div className="space-y-2">
                <Label htmlFor="diagnosis">Diagnosis *</Label>
                <Textarea
                    id="diagnosis"
                    value={formData.diagnosis}
                    onChange={(e) => setFormData({...formData, diagnosis: e.target.value})}
                    placeholder="Medical diagnosis for this prescription"
                    rows={3}
                    required
                />
            </div>

            <div className="grid gap-4 md:grid-cols-2">
                <div className="space-y-2">
                    <Label htmlFor="refills">Refills Allowed</Label>
                    <Select value={formData.refills_allowed} onValueChange={(value) => setFormData({...formData, refills_allowed: value})}>
                        <SelectTrigger>
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="0">0 refills</SelectItem>
                            <SelectItem value="1">1 refill</SelectItem>
                            <SelectItem value="2">2 refills</SelectItem>
                            <SelectItem value="3">3 refills</SelectItem>
                            <SelectItem value="4">4 refills</SelectItem>
                            <SelectItem value="5">5 refills</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                <div className="space-y-2">
                    <Label htmlFor="expiry">Expiry (days)</Label>
                    <Select value={formData.expiry_days} onValueChange={(value) => setFormData({...formData, expiry_days: value})}>
                        <SelectTrigger>
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="7">7 days</SelectItem>
                            <SelectItem value="14">14 days</SelectItem>
                            <SelectItem value="30">30 days</SelectItem>
                            <SelectItem value="60">60 days</SelectItem>
                            <SelectItem value="90">90 days</SelectItem>
                            <SelectItem value="180">180 days</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </div>

            <div className="space-y-2">
                <Label htmlFor="instructions">General Instructions</Label>
                <Textarea
                    id="instructions"
                    value={formData.instructions}
                    onChange={(e) => setFormData({...formData, instructions: e.target.value})}
                    placeholder="General instructions for the patient"
                    rows={3}
                />
            </div>

            {/* Medications */}
            <div className="space-y-4">
                <div className="flex items-center justify-between">
                    <Label>Medications</Label>
                    <Button type="button" variant="outline" size="sm" onClick={addMedication}>
                        <Plus className="h-4 w-4 mr-2" />
                        Add Medication
                    </Button>
                </div>

                {formData.items.map((item, index) => (
                    <Card key={index} className="p-4">
                        <div className="flex items-center justify-between mb-4">
                            <h4 className="font-medium">Medication {index + 1}</h4>
                            {formData.items.length > 1 && (
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    onClick={() => removeMedication(index)}
                                >
                                    <XCircle className="h-4 w-4" />
                                </Button>
                            )}
                        </div>

                        <div className="grid gap-4 md:grid-cols-2">
                            <div className="space-y-2">
                                <Label>Medication Name *</Label>
                                <Input
                                    value={item.medication_name}
                                    onChange={(e) => updateMedication(index, 'medication_name', e.target.value)}
                                    placeholder="Enter medication name"
                                    required
                                />
                            </div>
                            <div className="space-y-2">
                                <Label>Dosage *</Label>
                                <Input
                                    value={item.dosage}
                                    onChange={(e) => updateMedication(index, 'dosage', e.target.value)}
                                    placeholder="e.g., 500mg, 10ml"
                                    required
                                />
                            </div>
                            <div className="space-y-2">
                                <Label>Frequency *</Label>
                                <Select value={item.frequency} onValueChange={(value) => updateMedication(index, 'frequency', value)}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select frequency" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="once_daily">Once daily</SelectItem>
                                        <SelectItem value="twice_daily">Twice daily</SelectItem>
                                        <SelectItem value="three_times_daily">Three times daily</SelectItem>
                                        <SelectItem value="four_times_daily">Four times daily</SelectItem>
                                        <SelectItem value="as_needed">As needed</SelectItem>
                                        <SelectItem value="every_4_hours">Every 4 hours</SelectItem>
                                        <SelectItem value="every_6_hours">Every 6 hours</SelectItem>
                                        <SelectItem value="every_8_hours">Every 8 hours</SelectItem>
                                        <SelectItem value="every_12_hours">Every 12 hours</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div className="space-y-2">
                                <Label>Duration *</Label>
                                <Input
                                    value={item.duration}
                                    onChange={(e) => updateMedication(index, 'duration', e.target.value)}
                                    placeholder="e.g., 7 days, 2 weeks"
                                    required
                                />
                            </div>
                            <div className="space-y-2">
                                <Label>Quantity</Label>
                                <Input
                                    value={item.quantity}
                                    onChange={(e) => updateMedication(index, 'quantity', e.target.value)}
                                    placeholder="e.g., 30 tablets, 100ml"
                                />
                            </div>
                            <div className="space-y-2">
                                <Label>Special Instructions</Label>
                                <Input
                                    value={item.instructions}
                                    onChange={(e) => updateMedication(index, 'instructions', e.target.value)}
                                    placeholder="e.g., Take with food, Before bedtime"
                                />
                            </div>
                        </div>

                        <div className="grid gap-4 md:grid-cols-2 mt-4">
                            <div className="space-y-2">
                                <Label>Side Effects</Label>
                                <Textarea
                                    value={item.side_effects}
                                    onChange={(e) => updateMedication(index, 'side_effects', e.target.value)}
                                    placeholder="Known side effects"
                                    rows={2}
                                />
                            </div>
                            <div className="space-y-2">
                                <Label>Contraindications</Label>
                                <Textarea
                                    value={item.contraindications}
                                    onChange={(e) => updateMedication(index, 'contraindications', e.target.value)}
                                    placeholder="Contraindications and warnings"
                                    rows={2}
                                />
                            </div>
                        </div>
                    </Card>
                ))}
            </div>

            <div className="flex justify-end gap-2">
                <Button type="button" variant="outline" onClick={onSuccess}>
                    Cancel
                </Button>
                <Button type="submit">
                    Create Prescription
                </Button>
            </div>
        </form>
    );
}

// Prescription Details View Component
function PrescriptionDetailsView({ prescription }: { prescription: Prescription }) {
    const getStatusColor = (status: string) => {
        switch (status) {
            case 'active':
                return 'bg-green-100 text-green-800';
            case 'draft':
                return 'bg-yellow-100 text-yellow-800';
            case 'dispensed':
                return 'bg-blue-100 text-blue-800';
            case 'expired':
                return 'bg-red-100 text-red-800';
            case 'cancelled':
                return 'bg-gray-100 text-gray-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    };

    const getTypeColor = (type: string) => {
        switch (type) {
            case 'emergency':
                return 'bg-red-100 text-red-800';
            case 'controlled':
                return 'bg-orange-100 text-orange-800';
            case 'new':
                return 'bg-blue-100 text-blue-800';
            case 'refill':
                return 'bg-green-100 text-green-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    };

    return (
        <div className="space-y-6">
            {/* Header */}
            <div className="flex items-center justify-between">
                <div>
                    <h2 className="text-2xl font-bold">{prescription.prescription_number}</h2>
                    <p className="text-muted-foreground">Prescription Details</p>
                </div>
                <div className="flex gap-2">
                    <Badge className={getStatusColor(prescription.status)}>
                        {prescription.status}
                    </Badge>
                    <Badge variant="outline" className={getTypeColor(prescription.prescription_type)}>
                        {prescription.prescription_type}
                    </Badge>
                </div>
            </div>

            {/* Patient Info */}
            <Card>
                <CardHeader>
                    <CardTitle className="flex items-center gap-2">
                        <User className="h-5 w-5" />
                        Patient Information
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div className="grid gap-4 md:grid-cols-2">
                        <div>
                            <Label>Patient Name</Label>
                            <p className="text-sm font-medium">{prescription.patient_name}</p>
                        </div>
                        <div>
                            <Label>Prescription Number</Label>
                            <p className="text-sm font-medium">{prescription.prescription_number}</p>
                        </div>
                        <div>
                            <Label>Issued Date</Label>
                            <p className="text-sm">{new Date(prescription.issued_at).toLocaleDateString()}</p>
                        </div>
                        <div>
                            <Label>Expiry Date</Label>
                            <p className="text-sm">{new Date(prescription.expiry_date).toLocaleDateString()}</p>
                        </div>
                    </div>
                </CardContent>
            </Card>

            {/* Diagnosis */}
            <Card>
                <CardHeader>
                    <CardTitle>Diagnosis</CardTitle>
                </CardHeader>
                <CardContent>
                    <p className="text-sm">{prescription.diagnosis}</p>
                </CardContent>
            </Card>

            {/* Instructions */}
            <Card>
                <CardHeader>
                    <CardTitle>Instructions</CardTitle>
                </CardHeader>
                <CardContent>
                    <p className="text-sm">{prescription.instructions}</p>
                </CardContent>
            </Card>

            {/* Medications */}
            <Card>
                <CardHeader>
                    <CardTitle>Medications</CardTitle>
                </CardHeader>
                <CardContent>
                    <div className="space-y-4">
                        {prescription.items.map((item, index) => (
                            <div key={index} className="border rounded-lg p-4">
                                <h4 className="font-medium mb-2">{item.medication_name}</h4>
                                <div className="grid gap-2 md:grid-cols-2 text-sm">
                                    <div>
                                        <strong>Dosage:</strong> {item.dosage}
                                    </div>
                                    <div>
                                        <strong>Frequency:</strong> {item.frequency.replace('_', ' ')}
                                    </div>
                                    <div>
                                        <strong>Duration:</strong> {item.duration}
                                    </div>
                                    <div>
                                        <strong>Quantity:</strong> {item.quantity}
                                    </div>
                                </div>
                                {item.instructions && (
                                    <div className="mt-2">
                                        <strong>Special Instructions:</strong>
                                        <p className="text-sm text-muted-foreground">{item.instructions}</p>
                                    </div>
                                )}
                                {item.side_effects && (
                                    <div className="mt-2">
                                        <strong>Side Effects:</strong>
                                        <p className="text-sm text-muted-foreground">{item.side_effects}</p>
                                    </div>
                                )}
                                {item.contraindications && (
                                    <div className="mt-2">
                                        <strong>Contraindications:</strong>
                                        <p className="text-sm text-muted-foreground">{item.contraindications}</p>
                                    </div>
                                )}
                            </div>
                        ))}
                    </div>
                </CardContent>
            </Card>

            {/* Refill Information */}
            <Card>
                <CardHeader>
                    <CardTitle>Refill Information</CardTitle>
                </CardHeader>
                <CardContent>
                    <div className="grid gap-4 md:grid-cols-2">
                        <div>
                            <Label>Refills Allowed</Label>
                            <p className="text-sm font-medium">{prescription.refills_allowed}</p>
                        </div>
                        <div>
                            <Label>Refills Remaining</Label>
                            <p className="text-sm font-medium">{prescription.refills_remaining}</p>
                        </div>
                    </div>
                </CardContent>
            </Card>

            {/* Cost Information */}
            {prescription.total_cost && (
                <Card>
                    <CardHeader>
                        <CardTitle>Cost Information</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div>
                            <Label>Total Cost</Label>
                            <p className="text-sm font-medium">${prescription.total_cost.toFixed(2)}</p>
                        </div>
                    </CardContent>
                </Card>
            )}
        </div>
    );
}
