import { Head, router, useForm, usePage } from '@inertiajs/react';
import { useState, useEffect } from 'react';
import AppLayout from '@/layouts/app-layout';
import { adminPatients } from '@/routes';
import { type BreadcrumbItem, type Patient } from '@/types';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import {
    User,
    Plus,
    Search,
    Edit,
    Eye,
    Phone,
    Mail,
    Calendar,
    Heart,
    Activity,
    Save,
    X,
    Trash2,
    Loader2,
    FileText as FileMedical
} from 'lucide-react';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';

// Simple toast implementation using browser notifications
const toast = {
    success: (message: string) => {
        // Create a temporary success notification
        const notification = document.createElement('div');
        notification.className = 'fixed top-4 right-4 z-50 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg transform transition-all duration-300 ease-in-out';
        notification.textContent = message;
        document.body.appendChild(notification);
        
        // Animate in
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 100);
        
        // Remove after 3 seconds
        setTimeout(() => {
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => {
                document.body.removeChild(notification);
            }, 300);
        }, 3000);
    },
    error: (message: string) => {
        // Create a temporary error notification
        const notification = document.createElement('div');
        notification.className = 'fixed top-4 right-4 z-50 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg transform transition-all duration-300 ease-in-out';
        notification.textContent = message;
        document.body.appendChild(notification);
        
        // Animate in
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 100);
        
        // Remove after 3 seconds
        setTimeout(() => {
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => {
                document.body.removeChild(notification);
            }, 300);
        }, 3000);
    },
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Admin Dashboard',
        href: '/admin/dashboard',
    },
    {
        title: 'Patient Management',
        href: adminPatients(),
    },
];

interface PatientManagementProps {
    patients: Patient[];
    permissions: string[];
}

export default function PatientManagement({ patients: initialPatients }: PatientManagementProps) {
    const { props } = usePage();
    const [patients] = useState<Patient[]>(initialPatients);
    const [searchTerm, setSearchTerm] = useState('');
    const [statusFilter, setStatusFilter] = useState('all');
    const [genderFilter, setGenderFilter] = useState('all');
    const [ageFilter, setAgeFilter] = useState('all');
    const [isAddModalOpen, setIsAddModalOpen] = useState(false);
    const [isEditModalOpen, setIsEditModalOpen] = useState(false);
    const [isViewModalOpen, setIsViewModalOpen] = useState(false);
    const [isDeleteModalOpen, setIsDeleteModalOpen] = useState(false);
    const [isHealthRecordsModalOpen, setIsHealthRecordsModalOpen] = useState(false);
    const [editingPatient, setEditingPatient] = useState<Patient | null>(null);
    const [viewingPatient, setViewingPatient] = useState<Patient | null>(null);
    const [deletingPatient, setDeletingPatient] = useState<Patient | null>(null);
    const [healthRecordsData, setHealthRecordsData] = useState<{
        patient: Patient;
        appointments: Array<{
            id: number;
            date: string;
            doctor: string;
            type: string;
            status: string;
            room: string;
            reason: string;
            notes: string;
        }>;
        encounters: Array<{
            id: number;
            date: string;
            doctor: string;
            type: string;
            chief_complaint: string;
            diagnosis: string;
            treatment: string;
            notes: string;
        }>;
        prescriptions: Array<{
            id: number;
            date: string;
            doctor: string;
            medication: string;
            dosage: string;
            frequency: string;
            duration: string;
            instructions: string;
            status: string;
        }>;
    } | null>(null);

    // Handle flash messages
    useEffect(() => {
        const flash = props.flash as any;
        if (flash?.success) {
            toast.success(flash.success);
        }
        if (flash?.error) {
            toast.error(flash.error);
        }
    }, [props.flash]);

    // Inertia form for creating/updating patients
    const { data, setData, post, put, processing, errors: formErrors, reset } = useForm({
        first_name: '',
        last_name: '',
        dob: '',
        sex: '',
        contact: {
            email: '',
            phone: '',
            address: '',
            city: '',
            state: '',
            zip_code: ''
        },
        emergency_contact: {
            name: '',
            phone: '',
            relationship: ''
        },
        insurance: {
            provider: '',
            policy_number: '',
            group_number: ''
        },
        allergies: [] as string[],
        medical_history: '',
        medications: '',
        notes: ''
    });

    const savePatient = () => {
        if (isEditModalOpen && editingPatient) {
            // Update existing patient
            put(`/admin/patients/${editingPatient.id}`, {
                onSuccess: () => {
                    handleCancel();
                }
            });
        } else {
            // Create new patient
            post('/admin/patients', {
                onSuccess: () => {
                    handleCancel();
                }
            });
        }
    };

    const deletePatient = () => {
        if (!deletingPatient) return;

        router.delete(`/admin/patients/${deletingPatient.id}`, {
            onSuccess: () => {
                setIsDeleteModalOpen(false);
                setDeletingPatient(null);
            }
        });
    };

    const filteredPatients = patients.filter(patient => {
        const matchesSearch = patient.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
                            patient.email.toLowerCase().includes(searchTerm.toLowerCase()) ||
                            (patient.insurance?.provider || '').toLowerCase().includes(searchTerm.toLowerCase());
        const matchesStatus = statusFilter === 'all' || patient.status === statusFilter;
        const matchesGender = genderFilter === 'all' || patient.sex === genderFilter;
        const matchesAge = ageFilter === 'all' ||
            (ageFilter === '0-18' && patient.age >= 0 && patient.age <= 18) ||
            (ageFilter === '19-35' && patient.age >= 19 && patient.age <= 35) ||
            (ageFilter === '36-55' && patient.age >= 36 && patient.age <= 55) ||
            (ageFilter === '55+' && patient.age >= 55);

        return matchesSearch && matchesStatus && matchesGender && matchesAge;
    });

    const getStatusColor = (status: string) => {
        switch (status) {
            case 'Active': return 'default';
            case 'Inactive': return 'secondary';
            default: return 'secondary';
        }
    };

    const handleAddPatient = () => {
        setIsAddModalOpen(true);
        reset();
    };

    const handleEditPatient = (patient: Patient) => {
        setEditingPatient(patient);
        setData({
            first_name: patient.first_name || '',
            last_name: patient.last_name || '',
            dob: patient.dob || '',
            sex: patient.sex || '',
            contact: {
                email: patient.email || '',
                phone: patient.phone || '',
                address: patient.address || '',
                city: patient.city || '',
                state: patient.state || '',
                zip_code: patient.zip_code || ''
            },
            emergency_contact: patient.emergency_contact || {
                name: '',
                phone: '',
                relationship: ''
            },
            insurance: patient.insurance || {
                provider: '',
                policy_number: '',
                group_number: ''
            },
            allergies: Array.isArray(patient.allergies) ? patient.allergies : [],
            medical_history: patient.medical_history || '',
            medications: patient.medications || '',
            notes: patient.notes || ''
        });
        setIsEditModalOpen(true);
    };

    const handleViewPatient = (patient: Patient) => {
        setViewingPatient(patient);
        setIsViewModalOpen(true);
    };

    const handleDeletePatient = (patient: Patient) => {
        setDeletingPatient(patient);
        setIsDeleteModalOpen(true);
    };

    const fetchHealthRecords = async (patientId: number) => {
        try {
            const response = await fetch(`/admin/patients/${patientId}/health-records`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (response.ok) {
                const data = await response.json();
                setHealthRecordsData(data);
            } else {
                console.error('Failed to fetch health records');
            }
        } catch (error) {
            console.error('Error fetching health records:', error);
        }
    };

    const handleViewHealthRecords = (patient: Patient) => {
        setViewingPatient(patient);
        setIsHealthRecordsModalOpen(true);
        fetchHealthRecords(patient.id);
    };

    const handleSavePatient = () => {
        savePatient();
    };

    const handleCancel = () => {
        setIsAddModalOpen(false);
        setIsEditModalOpen(false);
        setIsViewModalOpen(false);
        setIsHealthRecordsModalOpen(false);
        setEditingPatient(null);
        setViewingPatient(null);
        reset();
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Patient Management - Medinext">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&family=instrument-sans:400,500,600" rel="stylesheet" />
            </Head>

            <div className="min-h-screen bg-gradient-to-br from-slate-50 via-white to-blue-50 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900">
                <div className="space-y-6 p-6">

                    <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                        <CardHeader>
                            <div className="flex items-center justify-between">
                                <div>
                                    <CardTitle className="text-xl font-semibold text-slate-900 dark:text-white">Patient Directory</CardTitle>
                                    <CardDescription className="text-slate-600 dark:text-slate-300">
                                        View and manage all patients in your clinic
                                    </CardDescription>
                                </div>
                                <div className="flex space-x-3">
                                    <Button
                                        variant="outline"
                                        className="border-slate-300 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-700"
                                    >
                                        <Heart className="mr-2 h-4 w-4" />
                                        Health Records
                                    </Button>
                                    <Button
                                        onClick={handleAddPatient}
                                        className="bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white shadow-lg"
                                    >
                                        <Plus className="mr-2 h-4 w-4" />
                                        Add Patient
                                    </Button>
                                </div>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div className="flex items-center space-x-3 mb-6">
                                <div className="relative flex-1">
                                    <Search className="absolute left-3 top-3 h-4 w-4 text-slate-400" />
                                    <Input
                                        placeholder="Search patients..."
                                        className="pl-10 h-11 border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-700 dark:text-white"
                                        value={searchTerm}
                                        onChange={(e) => setSearchTerm(e.target.value)}
                                    />
                                </div>
                                <Select value={statusFilter} onValueChange={setStatusFilter}>
                                    <SelectTrigger className="w-[140px] h-11 border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500">
                                        <SelectValue placeholder="Status" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">All Status</SelectItem>
                                        <SelectItem value="Active">Active</SelectItem>
                                        <SelectItem value="Inactive">Inactive</SelectItem>
                                    </SelectContent>
                                </Select>
                                <Select value={genderFilter} onValueChange={setGenderFilter}>
                                    <SelectTrigger className="w-[140px] h-11 border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500">
                                        <SelectValue placeholder="Gender" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">All Gender</SelectItem>
                                        <SelectItem value="Male">Male</SelectItem>
                                        <SelectItem value="Female">Female</SelectItem>
                                        <SelectItem value="Other">Other</SelectItem>
                                    </SelectContent>
                                </Select>
                                <Select value={ageFilter} onValueChange={setAgeFilter}>
                                    <SelectTrigger className="w-[140px] h-11 border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500">
                                        <SelectValue placeholder="Age" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">All Ages</SelectItem>
                                        <SelectItem value="0-18">0-18 years</SelectItem>
                                        <SelectItem value="19-35">19-35 years</SelectItem>
                                        <SelectItem value="36-55">36-55 years</SelectItem>
                                        <SelectItem value="55+">55+ years</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <div className="rounded-lg border border-slate-200 dark:border-slate-700 overflow-hidden">
                                <Table>
                                    <TableHeader className="bg-slate-50 dark:bg-slate-800/50">
                                        <TableRow className="border-slate-200 dark:border-slate-700">
                                            <TableHead className="font-semibold text-slate-700 dark:text-slate-300">Patient</TableHead>
                                            <TableHead className="font-semibold text-slate-700 dark:text-slate-300">Contact</TableHead>
                                            <TableHead className="font-semibold text-slate-700 dark:text-slate-300">Age & Gender</TableHead>
                                            <TableHead className="font-semibold text-slate-700 dark:text-slate-300">Last Visit</TableHead>
                                            <TableHead className="font-semibold text-slate-700 dark:text-slate-300">Visits</TableHead>
                                            <TableHead className="font-semibold text-slate-700 dark:text-slate-300">Status</TableHead>
                                            <TableHead className="text-right font-semibold text-slate-700 dark:text-slate-300">Actions</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {filteredPatients.map((patient) => (
                                            <TableRow key={patient.id} className="border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors duration-200">
                                                <TableCell>
                                                    <div className="flex items-center space-x-3">
                                                        <div className="h-12 w-12 rounded-full bg-gradient-to-r from-blue-500 to-purple-500 flex items-center justify-center shadow-md">
                                                            <span className="text-sm font-bold text-white">
                                                                {patient.name.split(' ').map(n => n[0]).join('')}
                                                            </span>
                                                        </div>
                                                        <div>
                                                            <div className="font-semibold text-slate-900 dark:text-white">{patient.name}</div>
                                                            <div className="text-sm text-slate-500 dark:text-slate-400">ID: {patient.patient_id}</div>
                                                        </div>
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    <div className="space-y-2">
                                                        <div className="flex items-center text-sm">
                                                            <Mail className="mr-2 h-4 w-4 text-slate-400" />
                                                            <span className="text-slate-700 dark:text-slate-300">{patient.email}</span>
                                                        </div>
                                                        <div className="flex items-center text-sm">
                                                            <Phone className="mr-2 h-4 w-4 text-slate-400" />
                                                            <span className="text-slate-500 dark:text-slate-400">{patient.phone}</span>
                                                        </div>
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    <div>
                                                        <div className="font-medium text-slate-900 dark:text-white">{patient.age} years</div>
                                                        <div className="text-sm text-slate-500 dark:text-slate-400">{patient.sex}</div>
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    <div className="flex items-center text-sm">
                                                        <Calendar className="mr-2 h-4 w-4 text-slate-400" />
                                                        <span className="text-slate-700 dark:text-slate-300">
                                                            {patient.last_visit ? new Date(patient.last_visit).toLocaleDateString() : 'No visits'}
                                                        </span>
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    <div className="flex items-center">
                                                        <Activity className="mr-2 h-4 w-4 text-slate-400" />
                                                        <span className="font-medium text-slate-900 dark:text-white">{patient.total_visits}</span>
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    <Badge
                                                        variant={getStatusColor(patient.status)}
                                                        className={`font-medium ${
                                                            patient.status === 'Active'
                                                                ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400'
                                                                : 'bg-slate-100 text-slate-800 dark:bg-slate-900/20 dark:text-slate-400'
                                                        }`}
                                                    >
                                                        {patient.status}
                                                    </Badge>
                                                </TableCell>
                                                <TableCell className="text-right">
                                                    <div className="flex items-center justify-end space-x-2">
                                                        <Button
                                                            variant="ghost"
                                                            size="sm"
                                                            title="View Details"
                                                            onClick={() => handleViewPatient(patient)}
                                                            className="h-8 w-8 p-0 hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:text-blue-600 dark:hover:text-blue-400"
                                                        >
                                                            <Eye className="h-4 w-4" />
                                                        </Button>
                                                        <Button
                                                            variant="ghost"
                                                            size="sm"
                                                            title="Health Records"
                                                            onClick={() => handleViewHealthRecords(patient)}
                                                            className="h-8 w-8 p-0 hover:bg-purple-50 dark:hover:bg-purple-900/20 hover:text-purple-600 dark:hover:text-purple-400"
                                                        >
                                                            <FileMedical className="h-4 w-4" />
                                                        </Button>
                                                        <Button
                                                            variant="ghost"
                                                            size="sm"
                                                            title="Edit Patient"
                                                            onClick={() => handleEditPatient(patient)}
                                                            className="h-8 w-8 p-0 hover:bg-green-50 dark:hover:bg-green-900/20 hover:text-green-600 dark:hover:text-green-400"
                                                        >
                                                            <Edit className="h-4 w-4" />
                                                        </Button>
                                                        <Button
                                                            variant="ghost"
                                                            size="sm"
                                                            title="Delete Patient"
                                                            onClick={() => handleDeletePatient(patient)}
                                                            className="h-8 w-8 p-0 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-600 dark:hover:text-red-400"
                                                        >
                                                            <Trash2 className="h-4 w-4" />
                                                        </Button>
                                                    </div>
                                                </TableCell>
                                            </TableRow>
                                        ))}
                                    </TableBody>
                                </Table>
                            </div>

                            {filteredPatients.length === 0 && (
                                <div className="text-center py-12">
                                    <div className="mx-auto w-16 h-16 bg-slate-100 dark:bg-slate-700 rounded-full flex items-center justify-center mb-4">
                                        <User className="h-8 w-8 text-slate-400" />
                                    </div>
                                    <h3 className="text-lg font-semibold text-slate-900 dark:text-white mb-2">No patients found</h3>
                                    <p className="text-slate-500 dark:text-slate-400 mb-4">
                                        Try adjusting your search or filter criteria.
                                    </p>
                                    <Button className="bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white">
                                        <Plus className="mr-2 h-4 w-4" />
                                        Add Patient
                                    </Button>
                                </div>
                            )}
                        </CardContent>
                    </Card>
                </div>
            </div>

            {/* Add Patient Modal */}
            {isAddModalOpen && (
                <div className="fixed inset-0 z-[9999]">
                    <div className="fixed inset-0 bg-black/50" onClick={() => setIsAddModalOpen(false)} />
                    <div className="fixed right-0 top-0 h-full w-[50vw] min-w-[600px] max-w-[800px] bg-white dark:bg-slate-800 border-l border-slate-200 dark:border-slate-700 rounded-l-lg shadow-2xl flex flex-col animate-in slide-in-from-right duration-300">
                    <div className="p-6 pb-4 border-b border-slate-200 dark:border-slate-700">
                        <h2 className="text-lg font-semibold text-slate-900 dark:text-white">Add New Patient</h2>
                        <p className="text-sm text-slate-600 dark:text-slate-400 mt-1">
                            Register a new patient in the system.
                        </p>
                    </div>
                    <div className="flex-1 overflow-y-auto p-6">
                        <div className="grid gap-4">
                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="first_name">First Name *</Label>
                                <Input
                                    id="first_name"
                                    value={data.first_name}
                                    onChange={(e) => setData('first_name', e.target.value)}
                                    placeholder="Enter first name"
                                    className={formErrors.first_name ? 'border-red-500' : ''}
                                />
                                {formErrors.first_name && <p className="text-sm text-red-500">{formErrors.first_name}</p>}
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="last_name">Last Name *</Label>
                                <Input
                                    id="last_name"
                                    value={data.last_name}
                                    onChange={(e) => setData('last_name', e.target.value)}
                                    placeholder="Enter last name"
                                    className={formErrors.last_name ? 'border-red-500' : ''}
                                />
                                {formErrors.last_name && <p className="text-sm text-red-500">{formErrors.last_name}</p>}
                            </div>
                        </div>
                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="email">Email *</Label>
                                <Input
                                    id="email"
                                    type="email"
                                    value={data.contact.email}
                                    onChange={(e) => setData('contact', {...data.contact, email: e.target.value})}
                                    placeholder="patient@email.com"
                                    className={formErrors['contact.email'] ? 'border-red-500' : ''}
                                />
                                {formErrors['contact.email'] && <p className="text-sm text-red-500">{formErrors['contact.email']}</p>}
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="phone">Phone Number *</Label>
                                <Input
                                    id="phone"
                                    value={data.contact.phone}
                                    onChange={(e) => setData('contact', {...data.contact, phone: e.target.value})}
                                    placeholder="+1 (555) 123-4567"
                                    className={formErrors['contact.phone'] ? 'border-red-500' : ''}
                                />
                                {formErrors['contact.phone'] && <p className="text-sm text-red-500">{formErrors['contact.phone']}</p>}
                            </div>
                        </div>
                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="dob">Date of Birth *</Label>
                                <Input
                                    id="dob"
                                    type="date"
                                    value={data.dob}
                                    onChange={(e) => setData('dob', e.target.value)}
                                    className={formErrors.dob ? 'border-red-500' : ''}
                                />
                                {formErrors.dob && <p className="text-sm text-red-500">{formErrors.dob}</p>}
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="sex">Gender *</Label>
                                <Select value={data.sex} onValueChange={(value) => setData('sex', value)}>
                                    <SelectTrigger className={formErrors.sex ? 'border-red-500' : ''}>
                                        <SelectValue placeholder="Select gender" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="Male">Male</SelectItem>
                                        <SelectItem value="Female">Female</SelectItem>
                                        <SelectItem value="Other">Other</SelectItem>
                                    </SelectContent>
                                </Select>
                                {formErrors.sex && <p className="text-sm text-red-500">{formErrors.sex}</p>}
                            </div>
                        </div>
                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="address">Address</Label>
                                <Input
                                    id="address"
                                    value={data.contact.address}
                                    onChange={(e) => setData('contact', {...data.contact, address: e.target.value})}
                                    placeholder="Enter address"
                                />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="city">City</Label>
                                <Input
                                    id="city"
                                    value={data.contact.city}
                                    onChange={(e) => setData('contact', {...data.contact, city: e.target.value})}
                                    placeholder="Enter city"
                                />
                            </div>
                        </div>
                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="state">State</Label>
                                <Input
                                    id="state"
                                    value={data.contact.state}
                                    onChange={(e) => setData('contact', {...data.contact, state: e.target.value})}
                                    placeholder="Enter state"
                                />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="zip_code">ZIP Code</Label>
                                <Input
                                    id="zip_code"
                                    value={data.contact.zip_code}
                                    onChange={(e) => setData('contact', {...data.contact, zip_code: e.target.value})}
                                    placeholder="Enter ZIP code"
                                />
                            </div>
                        </div>
                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="emergency_name">Emergency Contact Name</Label>
                                <Input
                                    id="emergency_name"
                                    value={data.emergency_contact.name}
                                    onChange={(e) => setData('emergency_contact', {...data.emergency_contact, name: e.target.value})}
                                    placeholder="Enter emergency contact name"
                                />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="emergency_phone">Emergency Contact Phone</Label>
                                <Input
                                    id="emergency_phone"
                                    value={data.emergency_contact.phone}
                                    onChange={(e) => setData('emergency_contact', {...data.emergency_contact, phone: e.target.value})}
                                    placeholder="Enter emergency contact phone"
                                />
                            </div>
                        </div>
                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="emergency_relationship">Emergency Contact Relationship</Label>
                                <Input
                                    id="emergency_relationship"
                                    value={data.emergency_contact.relationship}
                                    onChange={(e) => setData('emergency_contact', {...data.emergency_contact, relationship: e.target.value})}
                                    placeholder="e.g., Spouse, Parent, Sibling"
                                />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="insurance_provider">Insurance Provider</Label>
                                <Input
                                    id="insurance_provider"
                                    value={data.insurance.provider}
                                    onChange={(e) => setData('insurance', {...data.insurance, provider: e.target.value})}
                                    placeholder="Enter insurance provider"
                                />
                            </div>
                        </div>
                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="policy_number">Policy Number</Label>
                                <Input
                                    id="policy_number"
                                    value={data.insurance.policy_number}
                                    onChange={(e) => setData('insurance', {...data.insurance, policy_number: e.target.value})}
                                    placeholder="Enter policy number"
                                />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="group_number">Group Number</Label>
                                <Input
                                    id="group_number"
                                    value={data.insurance.group_number}
                                    onChange={(e) => setData('insurance', {...data.insurance, group_number: e.target.value})}
                                    placeholder="Enter group number"
                                />
                            </div>
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="medical_history">Medical History</Label>
                            <Textarea
                                id="medical_history"
                                value={data.medical_history}
                                onChange={(e) => setData('medical_history', e.target.value)}
                                placeholder="Enter medical history"
                                rows={3}
                            />
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="medications">Current Medications</Label>
                            <Textarea
                                id="medications"
                                value={data.medications}
                                onChange={(e) => setData('medications', e.target.value)}
                                placeholder="Enter current medications"
                                rows={3}
                            />
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="notes">Notes</Label>
                            <Textarea
                                id="notes"
                                value={data.notes}
                                onChange={(e) => setData('notes', e.target.value)}
                                placeholder="Enter additional notes"
                                rows={3}
                            />
                        </div>
                        </div>
                    </div>
                    <div className="p-6 pt-4 border-t border-slate-200 dark:border-slate-700 flex justify-end space-x-3">
                        <Button variant="outline" onClick={handleCancel}>
                            <X className="mr-2 h-4 w-4" />
                            Cancel
                        </Button>
                        <Button onClick={handleSavePatient} disabled={processing}>
                            {processing ? (
                                <>
                                    <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                                    Adding...
                                </>
                            ) : (
                                <>
                                    <Save className="mr-2 h-4 w-4" />
                                    Add Patient
                                </>
                            )}
                        </Button>
                    </div>
                    </div>
                </div>
            )}

            {/* Edit Patient Modal */}
            {isEditModalOpen && (
                <div className="fixed inset-0 z-[9999]">
                    <div className="fixed inset-0 bg-black/50" onClick={() => setIsEditModalOpen(false)} />
                    <div className="fixed right-0 top-0 h-full w-[50vw] min-w-[600px] max-w-[800px] bg-white dark:bg-slate-800 border-l border-slate-200 dark:border-slate-700 rounded-l-lg shadow-2xl flex flex-col animate-in slide-in-from-right duration-300">
                    <div className="p-6 pb-4 border-b border-slate-200 dark:border-slate-700">
                        <h2 className="text-lg font-semibold text-slate-900 dark:text-white">Edit Patient</h2>
                        <p className="text-sm text-slate-600 dark:text-slate-400 mt-1">
                            Update patient information for {editingPatient?.name}.
                        </p>
                    </div>
                    <div className="flex-1 overflow-y-auto p-6">
                        <div className="grid gap-4">
                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="edit-first_name">First Name *</Label>
                                <Input
                                    id="edit-first_name"
                                    value={data.first_name}
                                    onChange={(e) => setData('first_name', e.target.value)}
                                    placeholder="Enter first name"
                                    className={formErrors.first_name ? 'border-red-500' : ''}
                                />
                                {formErrors.first_name && <p className="text-sm text-red-500">{formErrors.first_name}</p>}
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="edit-last_name">Last Name *</Label>
                                <Input
                                    id="edit-last_name"
                                    value={data.last_name}
                                    onChange={(e) => setData('last_name', e.target.value)}
                                    placeholder="Enter last name"
                                    className={formErrors.last_name ? 'border-red-500' : ''}
                                />
                                {formErrors.last_name && <p className="text-sm text-red-500">{formErrors.last_name}</p>}
                            </div>
                        </div>
                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="edit-email">Email *</Label>
                                <Input
                                    id="edit-email"
                                    type="email"
                                    value={data.contact.email}
                                    onChange={(e) => setData('contact', {...data.contact, email: e.target.value})}
                                    placeholder="patient@email.com"
                                    className={formErrors['contact.email'] ? 'border-red-500' : ''}
                                />
                                {formErrors['contact.email'] && <p className="text-sm text-red-500">{formErrors['contact.email']}</p>}
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="edit-phone">Phone Number *</Label>
                                <Input
                                    id="edit-phone"
                                    value={data.contact.phone}
                                    onChange={(e) => setData('contact', {...data.contact, phone: e.target.value})}
                                    placeholder="+1 (555) 123-4567"
                                    className={formErrors['contact.phone'] ? 'border-red-500' : ''}
                                />
                                {formErrors['contact.phone'] && <p className="text-sm text-red-500">{formErrors['contact.phone']}</p>}
                            </div>
                        </div>
                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="edit-dob">Date of Birth *</Label>
                                <Input
                                    id="edit-dob"
                                    type="date"
                                    value={data.dob}
                                    onChange={(e) => setData('dob', e.target.value)}
                                    className={formErrors.dob ? 'border-red-500' : ''}
                                />
                                {formErrors.dob && <p className="text-sm text-red-500">{formErrors.dob}</p>}
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="edit-sex">Gender *</Label>
                                <Select value={data.sex} onValueChange={(value) => setData('sex', value)}>
                                    <SelectTrigger className={formErrors.sex ? 'border-red-500' : ''}>
                                        <SelectValue placeholder="Select gender" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="Male">Male</SelectItem>
                                        <SelectItem value="Female">Female</SelectItem>
                                        <SelectItem value="Other">Other</SelectItem>
                                    </SelectContent>
                                </Select>
                                {formErrors.sex && <p className="text-sm text-red-500">{formErrors.sex}</p>}
                            </div>
                        </div>
                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="edit-address">Address</Label>
                                <Input
                                    id="edit-address"
                                    value={data.contact.address}
                                    onChange={(e) => setData('contact', {...data.contact, address: e.target.value})}
                                    placeholder="Enter address"
                                />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="edit-city">City</Label>
                                <Input
                                    id="edit-city"
                                    value={data.contact.city}
                                    onChange={(e) => setData('contact', {...data.contact, city: e.target.value})}
                                    placeholder="Enter city"
                                />
                            </div>
                        </div>
                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="edit-state">State</Label>
                                <Input
                                    id="edit-state"
                                    value={data.contact.state}
                                    onChange={(e) => setData('contact', {...data.contact, state: e.target.value})}
                                    placeholder="Enter state"
                                />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="edit-zip_code">ZIP Code</Label>
                                <Input
                                    id="edit-zip_code"
                                    value={data.contact.zip_code}
                                    onChange={(e) => setData('contact', {...data.contact, zip_code: e.target.value})}
                                    placeholder="Enter ZIP code"
                                />
                            </div>
                        </div>
                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="edit-emergency_name">Emergency Contact Name</Label>
                                <Input
                                    id="edit-emergency_name"
                                    value={data.emergency_contact.name}
                                    onChange={(e) => setData('emergency_contact', {...data.emergency_contact, name: e.target.value})}
                                    placeholder="Enter emergency contact name"
                                />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="edit-emergency_phone">Emergency Contact Phone</Label>
                                <Input
                                    id="edit-emergency_phone"
                                    value={data.emergency_contact.phone}
                                    onChange={(e) => setData('emergency_contact', {...data.emergency_contact, phone: e.target.value})}
                                    placeholder="Enter emergency contact phone"
                                />
                            </div>
                        </div>
                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="edit-emergency_relationship">Emergency Contact Relationship</Label>
                                <Input
                                    id="edit-emergency_relationship"
                                    value={data.emergency_contact.relationship}
                                    onChange={(e) => setData('emergency_contact', {...data.emergency_contact, relationship: e.target.value})}
                                    placeholder="e.g., Spouse, Parent, Sibling"
                                />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="edit-insurance_provider">Insurance Provider</Label>
                                <Input
                                    id="edit-insurance_provider"
                                    value={data.insurance.provider}
                                    onChange={(e) => setData('insurance', {...data.insurance, provider: e.target.value})}
                                    placeholder="Enter insurance provider"
                                />
                            </div>
                        </div>
                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="edit-policy_number">Policy Number</Label>
                                <Input
                                    id="edit-policy_number"
                                    value={data.insurance.policy_number}
                                    onChange={(e) => setData('insurance', {...data.insurance, policy_number: e.target.value})}
                                    placeholder="Enter policy number"
                                />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="edit-group_number">Group Number</Label>
                                <Input
                                    id="edit-group_number"
                                    value={data.insurance.group_number}
                                    onChange={(e) => setData('insurance', {...data.insurance, group_number: e.target.value})}
                                    placeholder="Enter group number"
                                />
                            </div>
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="edit-medical_history">Medical History</Label>
                            <Textarea
                                id="edit-medical_history"
                                value={data.medical_history}
                                onChange={(e) => setData('medical_history', e.target.value)}
                                placeholder="Enter medical history"
                                rows={3}
                            />
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="edit-medications">Current Medications</Label>
                            <Textarea
                                id="edit-medications"
                                value={data.medications}
                                onChange={(e) => setData('medications', e.target.value)}
                                placeholder="Enter current medications"
                                rows={3}
                            />
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="edit-notes">Notes</Label>
                            <Textarea
                                id="edit-notes"
                                value={data.notes}
                                onChange={(e) => setData('notes', e.target.value)}
                                placeholder="Enter additional notes"
                                rows={3}
                            />
                        </div>
                        </div>
                    </div>
                    <div className="p-6 pt-4 border-t border-slate-200 dark:border-slate-700 flex justify-end space-x-3">
                        <Button variant="outline" onClick={handleCancel}>
                            <X className="mr-2 h-4 w-4" />
                            Cancel
                        </Button>
                        <Button onClick={handleSavePatient} disabled={processing}>
                            {processing ? (
                                <>
                                    <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                                    Updating...
                                </>
                            ) : (
                                <>
                                    <Save className="mr-2 h-4 w-4" />
                                    Update Patient
                                </>
                            )}
                        </Button>
                    </div>
                    </div>
                </div>
            )}

            {/* View Patient Details Modal */}
            {isViewModalOpen && (
                <div className="fixed inset-0 z-[9999]">
                    <div className="fixed inset-0 bg-black/50" onClick={() => setIsViewModalOpen(false)} />
                    <div className="fixed right-0 top-0 h-full w-[50vw] min-w-[600px] max-w-[800px] bg-white dark:bg-slate-800 border-l border-slate-200 dark:border-slate-700 rounded-l-lg shadow-2xl flex flex-col animate-in slide-in-from-right duration-300">
                    <div className="p-6 pb-4 border-b border-slate-200 dark:border-slate-700">
                        <h2 className="text-lg font-semibold text-slate-900 dark:text-white">Patient Details</h2>
                        <p className="text-sm text-slate-600 dark:text-slate-400 mt-1">
                            Complete information about {viewingPatient?.name}
                        </p>
                    </div>
                    {viewingPatient && (
                        <div className="flex-1 overflow-y-auto p-6">
                            <div className="space-y-6">
                            <div className="grid grid-cols-2 gap-6">
                                <div className="space-y-4">
                                    <div className="flex items-center space-x-3">
                                        <div className="h-16 w-16 rounded-full bg-gradient-to-r from-blue-500 to-purple-500 flex items-center justify-center shadow-md">
                                            <span className="text-lg font-bold text-white">
                                                {viewingPatient.name.split(' ').map(n => n[0]).join('')}
                                            </span>
                                        </div>
                                        <div>
                                            <h3 className="text-xl font-semibold text-slate-900 dark:text-white">{viewingPatient.name}</h3>
                                            <p className="text-slate-600 dark:text-slate-400">Patient ID: {viewingPatient.patient_id}</p>
                                            <Badge
                                                variant={getStatusColor(viewingPatient.status)}
                                                className={`font-medium ${
                                                    viewingPatient.status === 'Active'
                                                        ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400'
                                                        : 'bg-slate-100 text-slate-800 dark:bg-slate-900/20 dark:text-slate-400'
                                                }`}
                                            >
                                                {viewingPatient.status}
                                            </Badge>
                                        </div>
                                    </div>
                                </div>
                                <div className="space-y-4">
                                    <div className="flex items-center space-x-2">
                                        <Mail className="h-4 w-4 text-slate-400" />
                                        <span className="text-slate-700 dark:text-slate-300">{viewingPatient.email}</span>
                                    </div>
                                    <div className="flex items-center space-x-2">
                                        <Phone className="h-4 w-4 text-slate-400" />
                                        <span className="text-slate-700 dark:text-slate-300">{viewingPatient.phone}</span>
                                    </div>
                                    <div className="flex items-center space-x-2">
                                        <Calendar className="h-4 w-4 text-slate-400" />
                                        <span className="text-slate-700 dark:text-slate-300">{viewingPatient.age} years old</span>
                                    </div>
                                    <div className="flex items-center space-x-2">
                                        <User className="h-4 w-4 text-slate-400" />
                                        <span className="text-slate-700 dark:text-slate-300">{viewingPatient.sex}</span>
                                    </div>
                                </div>
                            </div>

                            <Tabs defaultValue="basic" className="w-full">
                                <TabsList className="grid w-full grid-cols-4">
                                    <TabsTrigger value="basic">Basic Info</TabsTrigger>
                                    <TabsTrigger value="contact">Contact</TabsTrigger>
                                    <TabsTrigger value="medical">Medical</TabsTrigger>
                                    <TabsTrigger value="history">History</TabsTrigger>
                                </TabsList>

                                <TabsContent value="basic" className="space-y-4">
                                    <div className="grid grid-cols-2 gap-4">
                                        <div>
                                            <Label className="text-sm font-medium text-slate-700 dark:text-slate-300">Date of Birth</Label>
                                            <div className="text-slate-900 dark:text-white">{new Date(viewingPatient.dob).toLocaleDateString()}</div>
                                        </div>
                                        <div>
                                            <Label className="text-sm font-medium text-slate-700 dark:text-slate-300">Total Visits</Label>
                                            <div className="text-slate-900 dark:text-white">{viewingPatient.total_visits}</div>
                                        </div>
                                    </div>
                                </TabsContent>

                                <TabsContent value="contact" className="space-y-4">
                                    <div className="space-y-2">
                                        <Label className="text-sm font-medium text-slate-700 dark:text-slate-300">Address</Label>
                                        <div className="text-slate-600 dark:text-slate-400">
                                            {viewingPatient.address}<br />
                                            {viewingPatient.city}, {viewingPatient.state} {viewingPatient.zip_code}
                                        </div>
                                    </div>
                                    {viewingPatient.emergency_contact && (
                                        <div className="space-y-2">
                                            <Label className="text-sm font-medium text-slate-700 dark:text-slate-300">Emergency Contact</Label>
                                            <div className="text-slate-600 dark:text-slate-400">
                                                {viewingPatient.emergency_contact.name} ({viewingPatient.emergency_contact.relationship})<br />
                                                {viewingPatient.emergency_contact.phone}
                                            </div>
                                        </div>
                                    )}
                                </TabsContent>

                                <TabsContent value="medical" className="space-y-4">
                                    {viewingPatient.insurance && (
                                        <div className="space-y-2">
                                            <Label className="text-sm font-medium text-slate-700 dark:text-slate-300">Insurance</Label>
                                            <div className="text-slate-600 dark:text-slate-400">
                                                {viewingPatient.insurance.provider}<br />
                                                Policy: {viewingPatient.insurance.policy_number}
                                            </div>
                                        </div>
                                    )}
                                    {viewingPatient.medical_history && (
                                        <div className="space-y-2">
                                            <Label className="text-sm font-medium text-slate-700 dark:text-slate-300">Medical History</Label>
                                            <div className="text-slate-600 dark:text-slate-400">{viewingPatient.medical_history}</div>
                                        </div>
                                    )}
                                    {viewingPatient.medications && (
                                        <div className="space-y-2">
                                            <Label className="text-sm font-medium text-slate-700 dark:text-slate-300">Current Medications</Label>
                                            <div className="text-slate-600 dark:text-slate-400">{viewingPatient.medications}</div>
                                        </div>
                                    )}
                                </TabsContent>

                                <TabsContent value="history" className="space-y-4">
                                    <div className="space-y-2">
                                        <Label className="text-sm font-medium text-slate-700 dark:text-slate-300">Last Visit</Label>
                                        <div className="text-slate-600 dark:text-slate-400">
                                            {viewingPatient.last_visit ? new Date(viewingPatient.last_visit).toLocaleDateString() : 'No visits'}
                                        </div>
                                    </div>
                                    <div className="space-y-2">
                                        <Label className="text-sm font-medium text-slate-700 dark:text-slate-300">Next Appointment</Label>
                                        <div className="text-slate-600 dark:text-slate-400">
                                            {viewingPatient.next_appointment ? new Date(viewingPatient.next_appointment).toLocaleDateString() : 'No upcoming appointments'}
                                        </div>
                                    </div>
                                </TabsContent>
                            </Tabs>
                            </div>
                        </div>
                    )}
                    <div className="p-6 pt-4 border-t border-slate-200 dark:border-slate-700 flex justify-end space-x-3">
                        <Button variant="outline" onClick={() => setIsViewModalOpen(false)}>
                            Close
                        </Button>
                        <Button onClick={() => {
                            setIsViewModalOpen(false);
                            handleEditPatient(viewingPatient!);
                        }}>
                            <Edit className="mr-2 h-4 w-4" />
                            Edit Patient
                        </Button>
                    </div>
                    </div>
                </div>
            )}

            {/* Health Records Modal */}
            {isHealthRecordsModalOpen && (
                <div className="fixed inset-0 z-[9999]">
                    <div className="fixed inset-0 bg-black/50" onClick={() => setIsHealthRecordsModalOpen(false)} />
                    <div className="fixed right-0 top-0 h-full w-[60vw] min-w-[700px] max-w-[900px] bg-white dark:bg-slate-800 border-l border-slate-200 dark:border-slate-700 rounded-l-lg shadow-2xl flex flex-col animate-in slide-in-from-right duration-300">
                    <div className="p-6 pb-4 border-b border-slate-200 dark:border-slate-700">
                        <h2 className="text-lg font-semibold text-slate-900 dark:text-white">Health Records</h2>
                        <p className="text-sm text-slate-600 dark:text-slate-400 mt-1">
                            Complete medical history for {viewingPatient?.name}
                        </p>
                    </div>
                    {healthRecordsData && (
                        <div className="flex-1 overflow-y-auto p-6">
                            <div className="space-y-6">
                            <Tabs defaultValue="appointments" className="w-full">
                                <TabsList className="grid w-full grid-cols-3">
                                    <TabsTrigger value="appointments">Appointments</TabsTrigger>
                                    <TabsTrigger value="encounters">Encounters</TabsTrigger>
                                    <TabsTrigger value="prescriptions">Prescriptions</TabsTrigger>
                                </TabsList>

                                <TabsContent value="appointments" className="space-y-4">
                                    <div className="space-y-2">
                                        {healthRecordsData.appointments.map((appointment) => (
                                            <div key={appointment.id} className="p-3 border rounded-lg">
                                                <div className="flex items-center justify-between">
                                                    <div>
                                                        <div className="font-medium">{new Date(appointment.date).toLocaleDateString()}</div>
                                                        <div className="text-sm text-slate-500">{appointment.doctor} - {appointment.type}</div>
                                                    </div>
                                                    <Badge>{appointment.status}</Badge>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                </TabsContent>

                                <TabsContent value="encounters" className="space-y-4">
                                    <div className="space-y-2">
                                        {healthRecordsData.encounters.map((encounter) => (
                                            <div key={encounter.id} className="p-3 border rounded-lg">
                                                <div className="font-medium">{new Date(encounter.date).toLocaleDateString()}</div>
                                                <div className="text-sm text-slate-500">{encounter.doctor} - {encounter.type}</div>
                                                {encounter.chief_complaint && (
                                                    <div className="text-sm mt-1">Chief Complaint: {encounter.chief_complaint}</div>
                                                )}
                                            </div>
                                        ))}
                                    </div>
                                </TabsContent>

                                <TabsContent value="prescriptions" className="space-y-4">
                                    <div className="space-y-2">
                                        {healthRecordsData.prescriptions.map((prescription) => (
                                            <div key={prescription.id} className="p-3 border rounded-lg">
                                                <div className="font-medium">{prescription.medication}</div>
                                                <div className="text-sm text-slate-500">{prescription.dosage} - {prescription.frequency}</div>
                                                <div className="text-sm mt-1">Prescribed by: {prescription.doctor}</div>
                                            </div>
                                        ))}
                                    </div>
                                </TabsContent>
                            </Tabs>
                            </div>
                        </div>
                    )}
                    <div className="p-6 pt-4 border-t border-slate-200 dark:border-slate-700 flex justify-end">
                        <Button variant="outline" onClick={() => setIsHealthRecordsModalOpen(false)}>
                            Close
                        </Button>
                    </div>
                    </div>
                </div>
            )}

            {/* Delete Confirmation Modal */}
            {isDeleteModalOpen && (
                <div className="fixed inset-0 z-[9999]">
                    <div className="fixed inset-0 bg-black/50" onClick={() => setIsDeleteModalOpen(false)} />
                    <div className="fixed right-0 top-0 h-full w-[40vw] min-w-[500px] max-w-[600px] bg-white dark:bg-slate-800 border-l border-slate-200 dark:border-slate-700 rounded-l-lg shadow-2xl flex flex-col animate-in slide-in-from-right duration-300">
                    <div className="p-6 pb-4 border-b border-slate-200 dark:border-slate-700">
                        <h2 className="text-lg font-semibold text-slate-900 dark:text-white">Delete Patient</h2>
                        <p className="text-sm text-slate-600 dark:text-slate-400 mt-1">
                            Are you sure you want to delete {deletingPatient?.name}? This action cannot be undone.
                        </p>
                    </div>
                    <div className="flex-1 p-6">
                        <div className="flex items-center space-x-3 p-4 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800">
                            <div className="h-12 w-12 rounded-full bg-red-100 dark:bg-red-900/40 flex items-center justify-center">
                                <Trash2 className="h-6 w-6 text-red-600 dark:text-red-400" />
                            </div>
                            <div>
                                <h4 className="font-semibold text-red-900 dark:text-red-100">Warning</h4>
                                <p className="text-sm text-red-700 dark:text-red-300">
                                    This will permanently delete the patient record and all associated data.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div className="p-6 pt-4 border-t border-slate-200 dark:border-slate-700 flex justify-end space-x-3">
                        <Button variant="outline" onClick={() => setIsDeleteModalOpen(false)}>
                            Cancel
                        </Button>
                        <Button
                            onClick={deletePatient}
                            className="bg-red-600 hover:bg-red-700 text-white"
                            disabled={processing}
                        >
                            {processing ? (
                                <>
                                    <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                                    Deleting...
                                </>
                            ) : (
                                <>
                                    <Trash2 className="mr-2 h-4 w-4" />
                                    Delete
                                </>
                            )}
                        </Button>
                    </div>
                    </div>
                </div>
            )}
        </AppLayout>
    );
}
