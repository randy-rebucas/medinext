import { Head } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '@/layouts/app-layout';
import { adminPatients } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
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
    MoreHorizontal,
    Heart,
    Clock,
    Activity,
    Save,
    X,
    MapPin,
    CreditCard,
    FileText
} from 'lucide-react';

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

export default function PatientManagement() {
    const [searchTerm, setSearchTerm] = useState('');
    const [statusFilter, setStatusFilter] = useState('all');
    const [genderFilter, setGenderFilter] = useState('all');
    const [isAddModalOpen, setIsAddModalOpen] = useState(false);
    const [isEditModalOpen, setIsEditModalOpen] = useState(false);
    const [editingPatient, setEditingPatient] = useState<{
        id: number;
        name: string;
        email: string;
        phone: string;
        age: number;
        gender: string;
        lastVisit: string;
        status: string;
        totalVisits: number;
        nextAppointment: string | null;
        insurance: string;
    } | null>(null);
    const [formData, setFormData] = useState({
        name: '',
        email: '',
        phone: '',
        age: '',
        gender: '',
        dateOfBirth: '',
        address: '',
        city: '',
        state: '',
        zipCode: '',
        emergencyContact: '',
        emergencyPhone: '',
        insurance: '',
        insuranceNumber: '',
        medicalHistory: '',
        allergies: '',
        medications: '',
        notes: ''
    });

    const patients = [
        {
            id: 1,
            name: 'John Doe',
            email: 'john.doe@email.com',
            phone: '+1 (555) 123-4567',
            age: 35,
            gender: 'Male',
            lastVisit: '2024-01-10',
            status: 'Active',
            totalVisits: 12,
            nextAppointment: '2024-01-20',
            insurance: 'Blue Cross'
        },
        {
            id: 2,
            name: 'Jane Smith',
            email: 'jane.smith@email.com',
            phone: '+1 (555) 234-5678',
            age: 28,
            gender: 'Female',
            lastVisit: '2024-01-12',
            status: 'Active',
            totalVisits: 8,
            nextAppointment: '2024-01-25',
            insurance: 'Aetna'
        },
        {
            id: 3,
            name: 'Bob Johnson',
            email: 'bob.johnson@email.com',
            phone: '+1 (555) 345-6789',
            age: 45,
            gender: 'Male',
            lastVisit: '2024-01-08',
            status: 'Active',
            totalVisits: 15,
            nextAppointment: '2024-01-18',
            insurance: 'Cigna'
        },
        {
            id: 4,
            name: 'Alice Brown',
            email: 'alice.brown@email.com',
            phone: '+1 (555) 456-7890',
            age: 52,
            gender: 'Female',
            lastVisit: '2023-12-20',
            status: 'Inactive',
            totalVisits: 6,
            nextAppointment: null,
            insurance: 'Medicare'
        },
        {
            id: 5,
            name: 'David Wilson',
            email: 'david.wilson@email.com',
            phone: '+1 (555) 567-8901',
            age: 38,
            gender: 'Male',
            lastVisit: '2024-01-14',
            status: 'Active',
            totalVisits: 4,
            nextAppointment: '2024-01-22',
            insurance: 'United Health'
        }
    ];

    const filteredPatients = patients.filter(patient => {
        const matchesSearch = patient.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
                            patient.email.toLowerCase().includes(searchTerm.toLowerCase()) ||
                            patient.insurance.toLowerCase().includes(searchTerm.toLowerCase());
        const matchesStatus = statusFilter === 'all' || patient.status === statusFilter;
        const matchesGender = genderFilter === 'all' || patient.gender === genderFilter;

        return matchesSearch && matchesStatus && matchesGender;
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
        setFormData({
            name: '',
            email: '',
            phone: '',
            age: '',
            gender: '',
            dateOfBirth: '',
            address: '',
            city: '',
            state: '',
            zipCode: '',
            emergencyContact: '',
            emergencyPhone: '',
            insurance: '',
            insuranceNumber: '',
            medicalHistory: '',
            allergies: '',
            medications: '',
            notes: ''
        });
    };

    const handleEditPatient = (patient: {
        id: number;
        name: string;
        email: string;
        phone: string;
        age: number;
        gender: string;
        lastVisit: string;
        status: string;
        totalVisits: number;
        nextAppointment: string | null;
        insurance: string;
    }) => {
        setEditingPatient(patient);
        setFormData({
            name: patient.name,
            email: patient.email,
            phone: patient.phone,
            age: patient.age.toString(),
            gender: patient.gender,
            dateOfBirth: '',
            address: '',
            city: '',
            state: '',
            zipCode: '',
            emergencyContact: '',
            emergencyPhone: '',
            insurance: patient.insurance,
            insuranceNumber: '',
            medicalHistory: '',
            allergies: '',
            medications: '',
            notes: ''
        });
        setIsEditModalOpen(true);
    };

    const handleSavePatient = () => {
        // Here you would typically make an API call to save the patient
        console.log('Saving patient:', formData);
        setIsAddModalOpen(false);
        setIsEditModalOpen(false);
        setEditingPatient(null);
    };

    const handleCancel = () => {
        setIsAddModalOpen(false);
        setIsEditModalOpen(false);
        setEditingPatient(null);
        setFormData({
            name: '',
            email: '',
            phone: '',
            age: '',
            gender: '',
            dateOfBirth: '',
            address: '',
            city: '',
            state: '',
            zipCode: '',
            emergencyContact: '',
            emergencyPhone: '',
            insurance: '',
            insuranceNumber: '',
            medicalHistory: '',
            allergies: '',
            medications: '',
            notes: ''
        });
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
                                                            <div className="text-sm text-slate-500 dark:text-slate-400">ID: {patient.id}</div>
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
                                                        <div className="text-sm text-slate-500 dark:text-slate-400">{patient.gender}</div>
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    <div className="flex items-center text-sm">
                                                        <Calendar className="mr-2 h-4 w-4 text-slate-400" />
                                                        <span className="text-slate-700 dark:text-slate-300">{patient.lastVisit}</span>
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    <div className="flex items-center">
                                                        <Activity className="mr-2 h-4 w-4 text-slate-400" />
                                                        <span className="font-medium text-slate-900 dark:text-white">{patient.totalVisits}</span>
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
                                                            className="h-8 w-8 p-0 hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:text-blue-600 dark:hover:text-blue-400"
                                                        >
                                                            <Eye className="h-4 w-4" />
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
                                                            title="More Options"
                                                            className="h-8 w-8 p-0 hover:bg-slate-50 dark:hover:bg-slate-700 hover:text-slate-600 dark:hover:text-slate-300"
                                                        >
                                                            <MoreHorizontal className="h-4 w-4" />
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
            <Dialog open={isAddModalOpen} onOpenChange={setIsAddModalOpen}>
                <DialogContent className="max-w-4xl max-h-[90vh] overflow-y-auto">
                    <DialogHeader>
                        <DialogTitle>Add New Patient</DialogTitle>
                        <DialogDescription>
                            Register a new patient in the system.
                        </DialogDescription>
                    </DialogHeader>
                    <div className="grid gap-4 py-4">
                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="name">Full Name *</Label>
                                <Input
                                    id="name"
                                    value={formData.name}
                                    onChange={(e) => setFormData({...formData, name: e.target.value})}
                                    placeholder="Enter patient name"
                                />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="email">Email *</Label>
                                <Input
                                    id="email"
                                    type="email"
                                    value={formData.email}
                                    onChange={(e) => setFormData({...formData, email: e.target.value})}
                                    placeholder="patient@email.com"
                                />
                            </div>
                        </div>
                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="phone">Phone Number *</Label>
                                <Input
                                    id="phone"
                                    value={formData.phone}
                                    onChange={(e) => setFormData({...formData, phone: e.target.value})}
                                    placeholder="+1 (555) 123-4567"
                                />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="dateOfBirth">Date of Birth *</Label>
                                <Input
                                    id="dateOfBirth"
                                    type="date"
                                    value={formData.dateOfBirth}
                                    onChange={(e) => setFormData({...formData, dateOfBirth: e.target.value})}
                                />
                            </div>
                        </div>
                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="age">Age</Label>
                                <Input
                                    id="age"
                                    type="number"
                                    value={formData.age}
                                    onChange={(e) => setFormData({...formData, age: e.target.value})}
                                    placeholder="35"
                                />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="gender">Gender *</Label>
                                <Select value={formData.gender} onValueChange={(value) => setFormData({...formData, gender: value})}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select gender" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="Male">Male</SelectItem>
                                        <SelectItem value="Female">Female</SelectItem>
                                        <SelectItem value="Other">Other</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="address">Address</Label>
                            <Input
                                id="address"
                                value={formData.address}
                                onChange={(e) => setFormData({...formData, address: e.target.value})}
                                placeholder="Street address"
                            />
                        </div>
                        <div className="grid grid-cols-3 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="city">City</Label>
                                <Input
                                    id="city"
                                    value={formData.city}
                                    onChange={(e) => setFormData({...formData, city: e.target.value})}
                                    placeholder="City"
                                />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="state">State</Label>
                                <Input
                                    id="state"
                                    value={formData.state}
                                    onChange={(e) => setFormData({...formData, state: e.target.value})}
                                    placeholder="State"
                                />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="zipCode">ZIP Code</Label>
                                <Input
                                    id="zipCode"
                                    value={formData.zipCode}
                                    onChange={(e) => setFormData({...formData, zipCode: e.target.value})}
                                    placeholder="12345"
                                />
                            </div>
                        </div>
                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="emergencyContact">Emergency Contact</Label>
                                <Input
                                    id="emergencyContact"
                                    value={formData.emergencyContact}
                                    onChange={(e) => setFormData({...formData, emergencyContact: e.target.value})}
                                    placeholder="Emergency contact name"
                                />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="emergencyPhone">Emergency Phone</Label>
                                <Input
                                    id="emergencyPhone"
                                    value={formData.emergencyPhone}
                                    onChange={(e) => setFormData({...formData, emergencyPhone: e.target.value})}
                                    placeholder="Emergency contact phone"
                                />
                            </div>
                        </div>
                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="insurance">Insurance Provider</Label>
                                <Select value={formData.insurance} onValueChange={(value) => setFormData({...formData, insurance: value})}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select insurance" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="Blue Cross">Blue Cross</SelectItem>
                                        <SelectItem value="Aetna">Aetna</SelectItem>
                                        <SelectItem value="Cigna">Cigna</SelectItem>
                                        <SelectItem value="United Health">United Health</SelectItem>
                                        <SelectItem value="Medicare">Medicare</SelectItem>
                                        <SelectItem value="Medicaid">Medicaid</SelectItem>
                                        <SelectItem value="Self Pay">Self Pay</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="insuranceNumber">Insurance Number</Label>
                                <Input
                                    id="insuranceNumber"
                                    value={formData.insuranceNumber}
                                    onChange={(e) => setFormData({...formData, insuranceNumber: e.target.value})}
                                    placeholder="Insurance policy number"
                                />
                            </div>
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="medicalHistory">Medical History</Label>
                            <Textarea
                                id="medicalHistory"
                                value={formData.medicalHistory}
                                onChange={(e) => setFormData({...formData, medicalHistory: e.target.value})}
                                placeholder="Previous medical conditions, surgeries, etc."
                                rows={3}
                            />
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="allergies">Allergies</Label>
                            <Textarea
                                id="allergies"
                                value={formData.allergies}
                                onChange={(e) => setFormData({...formData, allergies: e.target.value})}
                                placeholder="Known allergies and reactions"
                                rows={2}
                            />
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="medications">Current Medications</Label>
                            <Textarea
                                id="medications"
                                value={formData.medications}
                                onChange={(e) => setFormData({...formData, medications: e.target.value})}
                                placeholder="Current medications and dosages"
                                rows={2}
                            />
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="notes">Notes</Label>
                            <Textarea
                                id="notes"
                                value={formData.notes}
                                onChange={(e) => setFormData({...formData, notes: e.target.value})}
                                placeholder="Additional notes about the patient"
                                rows={3}
                            />
                        </div>
                    </div>
                    <DialogFooter>
                        <Button variant="outline" onClick={handleCancel}>
                            <X className="mr-2 h-4 w-4" />
                            Cancel
                        </Button>
                        <Button onClick={handleSavePatient}>
                            <Save className="mr-2 h-4 w-4" />
                            Add Patient
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            {/* Edit Patient Modal */}
            <Dialog open={isEditModalOpen} onOpenChange={setIsEditModalOpen}>
                <DialogContent className="max-w-4xl max-h-[90vh] overflow-y-auto">
                    <DialogHeader>
                        <DialogTitle>Edit Patient</DialogTitle>
                        <DialogDescription>
                            Update the patient information for {editingPatient?.name}.
                        </DialogDescription>
                    </DialogHeader>
                    <div className="grid gap-4 py-4">
                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="edit-name">Full Name *</Label>
                                <Input
                                    id="edit-name"
                                    value={formData.name}
                                    onChange={(e) => setFormData({...formData, name: e.target.value})}
                                    placeholder="Enter patient name"
                                />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="edit-email">Email *</Label>
                                <Input
                                    id="edit-email"
                                    type="email"
                                    value={formData.email}
                                    onChange={(e) => setFormData({...formData, email: e.target.value})}
                                    placeholder="patient@email.com"
                                />
                            </div>
                        </div>
                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="edit-phone">Phone Number *</Label>
                                <Input
                                    id="edit-phone"
                                    value={formData.phone}
                                    onChange={(e) => setFormData({...formData, phone: e.target.value})}
                                    placeholder="+1 (555) 123-4567"
                                />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="edit-dateOfBirth">Date of Birth *</Label>
                                <Input
                                    id="edit-dateOfBirth"
                                    type="date"
                                    value={formData.dateOfBirth}
                                    onChange={(e) => setFormData({...formData, dateOfBirth: e.target.value})}
                                />
                            </div>
                        </div>
                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="edit-age">Age</Label>
                                <Input
                                    id="edit-age"
                                    type="number"
                                    value={formData.age}
                                    onChange={(e) => setFormData({...formData, age: e.target.value})}
                                    placeholder="35"
                                />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="edit-gender">Gender *</Label>
                                <Select value={formData.gender} onValueChange={(value) => setFormData({...formData, gender: value})}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select gender" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="Male">Male</SelectItem>
                                        <SelectItem value="Female">Female</SelectItem>
                                        <SelectItem value="Other">Other</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="edit-address">Address</Label>
                            <Input
                                id="edit-address"
                                value={formData.address}
                                onChange={(e) => setFormData({...formData, address: e.target.value})}
                                placeholder="Street address"
                            />
                        </div>
                        <div className="grid grid-cols-3 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="edit-city">City</Label>
                                <Input
                                    id="edit-city"
                                    value={formData.city}
                                    onChange={(e) => setFormData({...formData, city: e.target.value})}
                                    placeholder="City"
                                />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="edit-state">State</Label>
                                <Input
                                    id="edit-state"
                                    value={formData.state}
                                    onChange={(e) => setFormData({...formData, state: e.target.value})}
                                    placeholder="State"
                                />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="edit-zipCode">ZIP Code</Label>
                                <Input
                                    id="edit-zipCode"
                                    value={formData.zipCode}
                                    onChange={(e) => setFormData({...formData, zipCode: e.target.value})}
                                    placeholder="12345"
                                />
                            </div>
                        </div>
                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="edit-emergencyContact">Emergency Contact</Label>
                                <Input
                                    id="edit-emergencyContact"
                                    value={formData.emergencyContact}
                                    onChange={(e) => setFormData({...formData, emergencyContact: e.target.value})}
                                    placeholder="Emergency contact name"
                                />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="edit-emergencyPhone">Emergency Phone</Label>
                                <Input
                                    id="edit-emergencyPhone"
                                    value={formData.emergencyPhone}
                                    onChange={(e) => setFormData({...formData, emergencyPhone: e.target.value})}
                                    placeholder="Emergency contact phone"
                                />
                            </div>
                        </div>
                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="edit-insurance">Insurance Provider</Label>
                                <Select value={formData.insurance} onValueChange={(value) => setFormData({...formData, insurance: value})}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select insurance" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="Blue Cross">Blue Cross</SelectItem>
                                        <SelectItem value="Aetna">Aetna</SelectItem>
                                        <SelectItem value="Cigna">Cigna</SelectItem>
                                        <SelectItem value="United Health">United Health</SelectItem>
                                        <SelectItem value="Medicare">Medicare</SelectItem>
                                        <SelectItem value="Medicaid">Medicaid</SelectItem>
                                        <SelectItem value="Self Pay">Self Pay</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="edit-insuranceNumber">Insurance Number</Label>
                                <Input
                                    id="edit-insuranceNumber"
                                    value={formData.insuranceNumber}
                                    onChange={(e) => setFormData({...formData, insuranceNumber: e.target.value})}
                                    placeholder="Insurance policy number"
                                />
                            </div>
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="edit-medicalHistory">Medical History</Label>
                            <Textarea
                                id="edit-medicalHistory"
                                value={formData.medicalHistory}
                                onChange={(e) => setFormData({...formData, medicalHistory: e.target.value})}
                                placeholder="Previous medical conditions, surgeries, etc."
                                rows={3}
                            />
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="edit-allergies">Allergies</Label>
                            <Textarea
                                id="edit-allergies"
                                value={formData.allergies}
                                onChange={(e) => setFormData({...formData, allergies: e.target.value})}
                                placeholder="Known allergies and reactions"
                                rows={2}
                            />
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="edit-medications">Current Medications</Label>
                            <Textarea
                                id="edit-medications"
                                value={formData.medications}
                                onChange={(e) => setFormData({...formData, medications: e.target.value})}
                                placeholder="Current medications and dosages"
                                rows={2}
                            />
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="edit-notes">Notes</Label>
                            <Textarea
                                id="edit-notes"
                                value={formData.notes}
                                onChange={(e) => setFormData({...formData, notes: e.target.value})}
                                placeholder="Additional notes about the patient"
                                rows={3}
                            />
                        </div>
                    </div>
                    <DialogFooter>
                        <Button variant="outline" onClick={handleCancel}>
                            <X className="mr-2 h-4 w-4" />
                            Cancel
                        </Button>
                        <Button onClick={handleSavePatient}>
                            <Save className="mr-2 h-4 w-4" />
                            Update Patient
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </AppLayout>
    );
}
