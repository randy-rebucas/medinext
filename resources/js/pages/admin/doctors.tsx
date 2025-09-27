import { Head, router, useForm, usePage } from '@inertiajs/react';
import { useState, useEffect } from 'react';
import AppLayout from '@/layouts/app-layout';
import { adminDoctors } from '@/routes';
import { type BreadcrumbItem, type Doctor } from '@/types';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Checkbox } from '@/components/ui/checkbox';
// Simple AlertDialog components
const AlertDialog = ({ open, children }: { open: boolean; children: React.ReactNode }) =>
    open ? <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50">{children}</div> : null;

const AlertDialogContent = ({ children }: { children: React.ReactNode }) =>
    <div className="bg-white dark:bg-slate-800 rounded-lg p-6 max-w-md w-full mx-4">{children}</div>;

const AlertDialogHeader = ({ children }: { children: React.ReactNode }) =>
    <div className="mb-4">{children}</div>;

const AlertDialogTitle = ({ children }: { children: React.ReactNode }) =>
    <h2 className="text-lg font-semibold text-slate-900 dark:text-white">{children}</h2>;

const AlertDialogDescription = ({ children }: { children: React.ReactNode }) =>
    <p className="text-sm text-slate-600 dark:text-slate-400">{children}</p>;

const AlertDialogFooter = ({ children }: { children: React.ReactNode }) =>
    <div className="flex justify-end space-x-2 mt-6">{children}</div>;

const AlertDialogCancel = ({ children, onClick }: { children: React.ReactNode; onClick?: () => void }) =>
    <Button variant="outline" onClick={onClick}>{children}</Button>;

const AlertDialogAction = ({ children, onClick, className, disabled }: { children: React.ReactNode; onClick?: () => void; className?: string; disabled?: boolean }) =>
    <Button onClick={onClick} className={className} disabled={disabled}>{children}</Button>;
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import {
    Stethoscope,
    Plus,
    Search,
    Edit,
    Eye,
    Calendar,
    Users,
    Mail,
    Phone,
    Save,
    X,
    Trash2,
    Clock,
    MapPin,
    GraduationCap,
    Award,
    DollarSign,
    Loader2
} from 'lucide-react';

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
        title: 'Doctor Management',
        href: adminDoctors(),
    },
];

interface DoctorManagementProps {
    doctors: Doctor[];
    specializations: string[];
    permissions: string[];
}

export default function DoctorManagement({ doctors: initialDoctors, specializations }: DoctorManagementProps) {
    const { props } = usePage();
    const [doctors] = useState<Doctor[]>(initialDoctors);
    const [searchTerm, setSearchTerm] = useState('');
    const [specializationFilter, setSpecializationFilter] = useState('all');
    const [statusFilter, setStatusFilter] = useState('all');
    const [isAddModalOpen, setIsAddModalOpen] = useState(false);
    const [isEditModalOpen, setIsEditModalOpen] = useState(false);
    const [isViewModalOpen, setIsViewModalOpen] = useState(false);
    const [isDeleteModalOpen, setIsDeleteModalOpen] = useState(false);
    const [isScheduleModalOpen, setIsScheduleModalOpen] = useState(false);
    const [editingDoctor, setEditingDoctor] = useState<Doctor | null>(null);
    const [viewingDoctor, setViewingDoctor] = useState<Doctor | null>(null);
    const [deletingDoctor, setDeletingDoctor] = useState<Doctor | null>(null);
    const [loading, setLoading] = useState(false);

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


    const filteredDoctors = doctors.filter(doctor => {
        const matchesSearch = doctor.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
                            doctor.email.toLowerCase().includes(searchTerm.toLowerCase()) ||
                            doctor.specialization.toLowerCase().includes(searchTerm.toLowerCase());
        const matchesSpecialization = specializationFilter === 'all' || doctor.specialization === specializationFilter;
        const matchesStatus = statusFilter === 'all' || doctor.status === statusFilter;

        return matchesSearch && matchesSpecialization && matchesStatus;
    });

    // Inertia form for creating/updating doctors
    const { data, setData, post, put, processing, errors: formErrors, reset } = useForm({
        name: '',
        email: '',
        phone: '',
        specialization: '',
        license: '',
        status: 'Active',
        experience: '',
        education: '',
        certifications: '',
        address: '',
        emergency_contact: '',
        emergency_phone: '',
        notes: '',
        consultation_fee: '',
        availability: {
            monday: { start: '09:00', end: '17:00', available: true },
            tuesday: { start: '09:00', end: '17:00', available: true },
            wednesday: { start: '09:00', end: '17:00', available: true },
            thursday: { start: '09:00', end: '17:00', available: true },
            friday: { start: '09:00', end: '17:00', available: true },
            saturday: { start: '09:00', end: '13:00', available: false },
            sunday: { start: '09:00', end: '13:00', available: false }
        }
    });

    const saveDoctor = () => {
        if (isEditModalOpen && editingDoctor) {
            // Update existing doctor
            put(`/admin/doctors/${editingDoctor.id}`, {
                onSuccess: () => {
                    handleCancel();
                }
            });
        } else {
            // Create new doctor
            post('/admin/doctors', {
                onSuccess: () => {
                    handleCancel();
                }
            });
        }
    };

    const deleteDoctor = () => {
        if (!deletingDoctor) return;

        router.delete(`/admin/doctors/${deletingDoctor.id}`, {
            onSuccess: () => {
                setIsDeleteModalOpen(false);
                setDeletingDoctor(null);
            }
        });
    };

    const getStatusColor = (status: string) => {
        switch (status) {
            case 'Active': return 'default';
            case 'On Leave': return 'secondary';
            case 'Inactive': return 'destructive';
            default: return 'secondary';
        }
    };

    const getSpecializationColor = (specialization: string) => {
        const colors = {
            'Cardiology': 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400',
            'Pediatrics': 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400',
            'Dermatology': 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400',
            'Orthopedics': 'bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-400',
            'Neurology': 'bg-orange-100 text-orange-800 dark:bg-orange-900/20 dark:text-orange-400'
        };
        return colors[specialization as keyof typeof colors] || 'bg-slate-100 text-slate-800 dark:bg-slate-900/20 dark:text-slate-400';
    };

    const handleAddDoctor = () => {
        setIsAddModalOpen(true);
        reset();
    };

    const handleEditDoctor = (doctor: Doctor) => {
        setEditingDoctor(doctor);
        setData({
            name: doctor.name,
            email: doctor.email,
            phone: doctor.phone || '',
            specialization: doctor.specialization,
            license: doctor.license || doctor.license_number,
            status: doctor.status || 'Active',
            experience: doctor.experience || '',
            education: doctor.education || '',
            certifications: doctor.certifications || '',
            address: doctor.address || '',
            emergency_contact: doctor.emergency_contact || '',
            emergency_phone: doctor.emergency_phone || '',
            notes: doctor.notes || '',
            consultation_fee: String(doctor.consultation_fee || ''),
            availability: (typeof doctor.availability === 'object' && !Array.isArray(doctor.availability)) ? doctor.availability : {
                monday: { start: '09:00', end: '17:00', available: true },
                tuesday: { start: '09:00', end: '17:00', available: true },
                wednesday: { start: '09:00', end: '17:00', available: true },
                thursday: { start: '09:00', end: '17:00', available: true },
                friday: { start: '09:00', end: '17:00', available: true },
                saturday: { start: '09:00', end: '13:00', available: false },
                sunday: { start: '09:00', end: '13:00', available: false }
            }
        });
        setIsEditModalOpen(true);
    };

    const handleViewDoctor = (doctor: Doctor) => {
        setViewingDoctor(doctor);
        setIsViewModalOpen(true);
    };

    const handleDeleteDoctor = (doctor: Doctor) => {
        setDeletingDoctor(doctor);
        setIsDeleteModalOpen(true);
    };

    const handleViewSchedule = (doctor: Doctor) => {
        setViewingDoctor(doctor);
        setIsScheduleModalOpen(true);
    };

    const handleSaveDoctor = () => {
        saveDoctor();
    };

    const handleCancel = () => {
        setIsAddModalOpen(false);
        setIsEditModalOpen(false);
        setIsViewModalOpen(false);
        setIsScheduleModalOpen(false);
        setEditingDoctor(null);
        setViewingDoctor(null);
        reset();
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Doctor Management - Medinext">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&family=instrument-sans:400,500,600" rel="stylesheet" />
            </Head>

            <div className="min-h-screen bg-gradient-to-br from-slate-50 via-white to-blue-50 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900">
                <div className="space-y-6 p-6">
                    <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                        <CardHeader>
                            <div className="flex items-center justify-between">
                                <div>
                                    <CardTitle className="text-xl font-semibold text-slate-900 dark:text-white">Doctor Directory</CardTitle>
                                    <CardDescription className="text-slate-600 dark:text-slate-300">
                                        View and manage all doctors in your clinic
                                    </CardDescription>
                                </div>
                                <div className="flex space-x-3">
                                    <Button
                                        variant="outline"
                                        className="border-slate-300 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-700"
                                    >
                                        <Stethoscope className="mr-2 h-4 w-4" />
                                        View Schedules
                                    </Button>
                                    <Button
                                        onClick={handleAddDoctor}
                                        className="bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white shadow-lg"
                                    >
                                        <Plus className="mr-2 h-4 w-4" />
                                        Add Doctor
                                    </Button>
                                </div>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div className="flex items-center space-x-3 mb-6">
                                <div className="relative flex-1">
                                    <Search className="absolute left-3 top-3 h-4 w-4 text-slate-400" />
                                    <Input
                                        placeholder="Search doctors..."
                                        className="pl-10 h-11 border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-700 dark:text-white"
                                        value={searchTerm}
                                        onChange={(e) => setSearchTerm(e.target.value)}
                                    />
                                </div>
                                <Select value={specializationFilter} onValueChange={setSpecializationFilter}>
                                    <SelectTrigger className="w-[180px] h-11 border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500">
                                        <SelectValue placeholder="Specialization" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">All Specializations</SelectItem>
                                        {specializations.map((spec) => (
                                            <SelectItem key={spec} value={spec}>{spec}</SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                <Select value={statusFilter} onValueChange={setStatusFilter}>
                                    <SelectTrigger className="w-[140px] h-11 border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500">
                                        <SelectValue placeholder="Status" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">All Status</SelectItem>
                                        <SelectItem value="Active">Active</SelectItem>
                                        <SelectItem value="On Leave">On Leave</SelectItem>
                                        <SelectItem value="Inactive">Inactive</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <div className="rounded-lg border border-slate-200 dark:border-slate-700 overflow-hidden">
                                <Table>
                                    <TableHeader className="bg-slate-50 dark:bg-slate-800/50">
                                        <TableRow className="border-slate-200 dark:border-slate-700">
                                            <TableHead className="font-semibold text-slate-700 dark:text-slate-300">Doctor</TableHead>
                                            <TableHead className="font-semibold text-slate-700 dark:text-slate-300">Specialization</TableHead>
                                            <TableHead className="font-semibold text-slate-700 dark:text-slate-300">Contact</TableHead>
                                            <TableHead className="font-semibold text-slate-700 dark:text-slate-300">Patients</TableHead>
                                            <TableHead className="font-semibold text-slate-700 dark:text-slate-300">Status</TableHead>
                                            <TableHead className="font-semibold text-slate-700 dark:text-slate-300">Next Appointment</TableHead>
                                            <TableHead className="text-right font-semibold text-slate-700 dark:text-slate-300">Actions</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {filteredDoctors.map((doctor) => (
                                            <TableRow key={doctor.id} className="border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors duration-200">
                                                <TableCell>
                                                    <div className="flex items-center space-x-3">
                                                        <div className="h-12 w-12 rounded-full bg-gradient-to-r from-blue-500 to-purple-500 flex items-center justify-center shadow-md">
                                                            <span className="text-sm font-bold text-white">
                                                                {doctor.name.split(' ').map(n => n[0]).join('')}
                                                            </span>
                                                        </div>
                                                        <div>
                                                            <div className="font-semibold text-slate-900 dark:text-white">{doctor.name}</div>
                                                            <div className="text-sm text-slate-500 dark:text-slate-400">License: {doctor.license || doctor.license_number}</div>
                                                            <div className="text-xs text-slate-400 dark:text-slate-500">{doctor.experience || 'N/A'} • ⭐ {doctor.rating || 'N/A'}</div>
                                                        </div>
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    <Badge className={`font-medium ${getSpecializationColor(doctor.specialization)}`}>
                                                        {doctor.specialization}
                                                    </Badge>
                                                </TableCell>
                                                <TableCell>
                                                    <div className="space-y-1">
                                                        <div className="flex items-center text-sm">
                                                            <Mail className="mr-2 h-3 w-3 text-slate-400" />
                                                            <span className="text-slate-700 dark:text-slate-300">{doctor.email}</span>
                                                        </div>
                                                        <div className="flex items-center text-sm">
                                                            <Phone className="mr-2 h-3 w-3 text-slate-400" />
                                                            <span className="text-slate-500 dark:text-slate-400">{doctor.phone}</span>
                                                        </div>
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    <div className="flex items-center">
                                                        <Users className="mr-2 h-4 w-4 text-slate-400" />
                                                        <span className="font-medium text-slate-900 dark:text-white">{doctor.patients || 0}</span>
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    <Badge
                                                        variant={getStatusColor(doctor.status || 'Active')}
                                                        className={`font-medium ${
                                                            (doctor.status || 'Active') === 'Active'
                                                                ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400'
                                                                : (doctor.status || 'Active') === 'On Leave'
                                                                ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400'
                                                                : 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400'
                                                        }`}
                                                    >
                                                        {doctor.status || 'Active'}
                                                    </Badge>
                                                </TableCell>
                                                <TableCell>
                                                    <div className="flex items-center text-sm">
                                                        <Calendar className="mr-2 h-4 w-4 text-slate-400" />
                                                        <span className="text-slate-700 dark:text-slate-300">{doctor.nextAppointment || doctor.next_appointment || 'No upcoming appointments'}</span>
                                                    </div>
                                                </TableCell>
                                                <TableCell className="text-right">
                                                    <div className="flex items-center justify-end space-x-2">
                                                        <Button
                                                            variant="ghost"
                                                            size="sm"
                                                            title="View Details"
                                                            onClick={() => handleViewDoctor(doctor)}
                                                            className="h-8 w-8 p-0 hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:text-blue-600 dark:hover:text-blue-400"
                                                        >
                                                            <Eye className="h-4 w-4" />
                                                        </Button>
                                                        <Button
                                                            variant="ghost"
                                                            size="sm"
                                                            title="Edit Doctor"
                                                            onClick={() => handleEditDoctor(doctor)}
                                                            className="h-8 w-8 p-0 hover:bg-green-50 dark:hover:bg-green-900/20 hover:text-green-600 dark:hover:text-green-400"
                                                        >
                                                            <Edit className="h-4 w-4" />
                                                        </Button>
                                                        <Button
                                                            variant="ghost"
                                                            size="sm"
                                                            title="View Schedule"
                                                            onClick={() => handleViewSchedule(doctor)}
                                                            className="h-8 w-8 p-0 hover:bg-purple-50 dark:hover:bg-purple-900/20 hover:text-purple-600 dark:hover:text-purple-400"
                                                        >
                                                            <Calendar className="h-4 w-4" />
                                                        </Button>
                                                        <Button
                                                            variant="ghost"
                                                            size="sm"
                                                            title="Delete Doctor"
                                                            onClick={() => handleDeleteDoctor(doctor)}
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

                            {filteredDoctors.length === 0 && (
                                <div className="text-center py-12">
                                    <div className="mx-auto w-16 h-16 bg-slate-100 dark:bg-slate-700 rounded-full flex items-center justify-center mb-4">
                                        <Stethoscope className="h-8 w-8 text-slate-400" />
                                    </div>
                                    <h3 className="text-lg font-semibold text-slate-900 dark:text-white mb-2">No doctors found</h3>
                                    <p className="text-slate-500 dark:text-slate-400 mb-4">
                                        Try adjusting your search or filter criteria.
                                    </p>
                                    <Button className="bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white">
                                        <Plus className="mr-2 h-4 w-4" />
                                        Add Doctor
                                    </Button>
                                </div>
                            )}
                        </CardContent>
                    </Card>
                </div>
            </div>

            {/* Add Doctor Modal */}
            <Dialog open={isAddModalOpen} onOpenChange={setIsAddModalOpen}>
                <DialogContent className="max-w-4xl max-h-[90vh] overflow-y-auto">
                    <DialogHeader>
                        <DialogTitle>Add New Doctor</DialogTitle>
                        <DialogDescription>
                            Enter the details for the new doctor.
                        </DialogDescription>
                    </DialogHeader>
                    <div className="grid gap-4 py-4">
                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="name">Full Name *</Label>
                                <Input
                                    id="name"
                                    value={data.name}
                                    onChange={(e) => setData('name', e.target.value)}
                                    placeholder="Dr. John Smith"
                                    className={formErrors.name ? 'border-red-500' : ''}
                                />
                                {formErrors.name && <p className="text-sm text-red-500">{formErrors.name}</p>}
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="email">Email *</Label>
                                <Input
                                    id="email"
                                    type="email"
                                    value={data.email}
                                    onChange={(e) => setData('email', e.target.value)}
                                    placeholder="doctor@clinic.com"
                                    className={formErrors.email ? 'border-red-500' : ''}
                                />
                                {formErrors.email && <p className="text-sm text-red-500">{formErrors.email}</p>}
                            </div>
                        </div>
                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="phone">Phone Number *</Label>
                                <Input
                                    id="phone"
                                    value={data.phone}
                                    onChange={(e) => setData('phone', e.target.value)}
                                    placeholder="+1 (555) 123-4567"
                                    className={formErrors.phone ? 'border-red-500' : ''}
                                />
                                {formErrors.phone && <p className="text-sm text-red-500">{formErrors.phone}</p>}
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="license">Medical License *</Label>
                                <Input
                                    id="license"
                                    value={data.license}
                                    onChange={(e) => setData('license', e.target.value)}
                                    placeholder="MD12345"
                                    className={formErrors.license ? 'border-red-500' : ''}
                                />
                                {formErrors.license && <p className="text-sm text-red-500">{formErrors.license}</p>}
                            </div>
                        </div>
                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="specialization">Specialization *</Label>
                                <Select value={data.specialization} onValueChange={(value) => setData('specialization', value)}>
                                    <SelectTrigger className={formErrors.specialization ? 'border-red-500' : ''}>
                                        <SelectValue placeholder="Select specialization" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {specializations.map((spec) => (
                                            <SelectItem key={spec} value={spec}>{spec}</SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                {formErrors.specialization && <p className="text-sm text-red-500">{formErrors.specialization}</p>}
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="experience">Years of Experience *</Label>
                                <Input
                                    id="experience"
                                    value={data.experience}
                                    onChange={(e) => setData('experience', e.target.value)}
                                    placeholder="5 years"
                                    className={formErrors.experience ? 'border-red-500' : ''}
                                />
                                {formErrors.experience && <p className="text-sm text-red-500">{formErrors.experience}</p>}
                            </div>
                        </div>
                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="consultationFee">Consultation Fee</Label>
                                <Input
                                    id="consultationFee"
                                    value={data.consultation_fee}
                                    onChange={(e) => setData('consultation_fee', e.target.value)}
                                    placeholder="$150"
                                />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="status">Status</Label>
                                <Select value={data.status} onValueChange={(value) => setData('status', value)}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select status" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="Active">Active</SelectItem>
                                        <SelectItem value="On Leave">On Leave</SelectItem>
                                        <SelectItem value="Inactive">Inactive</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="education">Education</Label>
                            <Textarea
                                id="education"
                                value={data.education}
                                onChange={(e) => setData('education', e.target.value)}
                                placeholder="Medical degree, residency, fellowship details..."
                                rows={2}
                            />
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="certifications">Certifications</Label>
                            <Textarea
                                id="certifications"
                                value={data.certifications}
                                onChange={(e) => setData('certifications', e.target.value)}
                                placeholder="Board certifications, special training..."
                                rows={2}
                            />
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="address">Address</Label>
                            <Textarea
                                id="address"
                                value={data.address}
                                onChange={(e) => setData('address', e.target.value)}
                                placeholder="Home address"
                                rows={2}
                            />
                        </div>
                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="emergencyContact">Emergency Contact</Label>
                                <Input
                                    id="emergencyContact"
                                    value={data.emergency_contact}
                                    onChange={(e) => setData('emergency_contact', e.target.value)}
                                    placeholder="Emergency contact name"
                                />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="emergencyPhone">Emergency Phone</Label>
                                <Input
                                    id="emergencyPhone"
                                    value={data.emergency_phone}
                                    onChange={(e) => setData('emergency_phone', e.target.value)}
                                    placeholder="Emergency contact phone"
                                />
                            </div>
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="notes">Notes</Label>
                            <Textarea
                                id="notes"
                                value={data.notes}
                                onChange={(e) => setData('notes', e.target.value)}
                                placeholder="Additional notes about the doctor"
                                rows={3}
                            />
                        </div>

                        {/* Availability Schedule */}
                        <div className="space-y-4">
                            <Label className="text-base font-medium">Availability Schedule</Label>
                            <div className="space-y-3">
                                {Object.entries(data.availability).map(([day, schedule]) => (
                                    <div key={day} className="flex items-center justify-between p-3 border rounded-lg">
                                        <div className="flex items-center space-x-3">
                                            <Checkbox
                                                checked={schedule.available}
                                                onCheckedChange={(checked) => {
                                                    setData('availability', {
                                                        ...data.availability,
                                                        [day]: { ...schedule, available: checked }
                                                    });
                                                }}
                                            />
                                            <span className="font-medium capitalize text-slate-900 dark:text-white min-w-[80px]">{day}</span>
                                        </div>
                                        {schedule.available && (
                                            <div className="flex items-center space-x-2">
                                                <Input
                                                    type="time"
                                                    value={schedule.start}
                                                    onChange={(e) => {
                                                        setData('availability', {
                                                            ...data.availability,
                                                            [day]: { ...schedule, start: e.target.value }
                                                        });
                                                    }}
                                                    className="w-32"
                                                />
                                                <span className="text-slate-500">to</span>
                                                <Input
                                                    type="time"
                                                    value={schedule.end}
                                                    onChange={(e) => {
                                                        setData('availability', {
                                                            ...data.availability,
                                                            [day]: { ...schedule, end: e.target.value }
                                                        });
                                                    }}
                                                    className="w-32"
                                                />
                                            </div>
                                        )}
                                    </div>
                                ))}
                            </div>
                        </div>
                    </div>
                    <DialogFooter>
                        <Button variant="outline" onClick={handleCancel}>
                            <X className="mr-2 h-4 w-4" />
                            Cancel
                        </Button>
                        <Button onClick={handleSaveDoctor} disabled={processing}>
                            {processing ? (
                                <>
                                    <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                                    Adding...
                                </>
                            ) : (
                                <>
                                    <Save className="mr-2 h-4 w-4" />
                                    Add Doctor
                                </>
                            )}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            {/* Edit Doctor Modal */}
            <Dialog open={isEditModalOpen} onOpenChange={setIsEditModalOpen}>
                <DialogContent className="max-w-4xl max-h-[90vh] overflow-y-auto">
                    <DialogHeader>
                        <DialogTitle>Edit Doctor</DialogTitle>
                        <DialogDescription>
                            Update the details for {editingDoctor?.name}.
                        </DialogDescription>
                    </DialogHeader>
                    <div className="grid gap-4 py-4">
                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="edit-name">Full Name *</Label>
                                <Input
                                    id="edit-name"
                                    value={data.name}
                                    onChange={(e) => setData('name', e.target.value)}
                                    placeholder="Dr. John Smith"
                                />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="edit-email">Email *</Label>
                                <Input
                                    id="edit-email"
                                    type="email"
                                    value={data.email}
                                    onChange={(e) => setData('email', e.target.value)}
                                    placeholder="doctor@clinic.com"
                                />
                            </div>
                        </div>
                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="edit-phone">Phone Number *</Label>
                                <Input
                                    id="edit-phone"
                                    value={data.phone}
                                    onChange={(e) => setData('phone', e.target.value)}
                                    placeholder="+1 (555) 123-4567"
                                />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="edit-license">Medical License *</Label>
                                <Input
                                    id="edit-license"
                                    value={data.license}
                                    onChange={(e) => setData('license', e.target.value)}
                                    placeholder="MD12345"
                                />
                            </div>
                        </div>
                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="edit-specialization">Specialization *</Label>
                                <Select value={data.specialization} onValueChange={(value) => setData('specialization', value)}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select specialization" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="Cardiology">Cardiology</SelectItem>
                                        <SelectItem value="Pediatrics">Pediatrics</SelectItem>
                                        <SelectItem value="Dermatology">Dermatology</SelectItem>
                                        <SelectItem value="Orthopedics">Orthopedics</SelectItem>
                                        <SelectItem value="Neurology">Neurology</SelectItem>
                                        <SelectItem value="Internal Medicine">Internal Medicine</SelectItem>
                                        <SelectItem value="Emergency Medicine">Emergency Medicine</SelectItem>
                                        <SelectItem value="Radiology">Radiology</SelectItem>
                                        <SelectItem value="Pathology">Pathology</SelectItem>
                                        <SelectItem value="Anesthesiology">Anesthesiology</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="edit-experience">Years of Experience *</Label>
                                <Input
                                    id="edit-experience"
                                    value={data.experience}
                                    onChange={(e) => setData('experience', e.target.value)}
                                    placeholder="5 years"
                                />
                            </div>
                        </div>
                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="edit-consultationFee">Consultation Fee</Label>
                                <Input
                                    id="edit-consultationFee"
                                    value={data.consultation_fee}
                                    onChange={(e) => setData('consultation_fee', e.target.value)}
                                    placeholder="$150"
                                />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="edit-status">Status</Label>
                                <Select value={data.status} onValueChange={(value) => setData('status', value)}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select status" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="Active">Active</SelectItem>
                                        <SelectItem value="On Leave">On Leave</SelectItem>
                                        <SelectItem value="Inactive">Inactive</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="edit-education">Education</Label>
                            <Textarea
                                id="edit-education"
                                value={data.education}
                                onChange={(e) => setData('education', e.target.value)}
                                placeholder="Medical degree, residency, fellowship details..."
                                rows={2}
                            />
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="edit-certifications">Certifications</Label>
                            <Textarea
                                id="edit-certifications"
                                value={data.certifications}
                                onChange={(e) => setData('certifications', e.target.value)}
                                placeholder="Board certifications, special training..."
                                rows={2}
                            />
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="edit-address">Address</Label>
                            <Textarea
                                id="edit-address"
                                value={data.address}
                                onChange={(e) => setData('address', e.target.value)}
                                placeholder="Home address"
                                rows={2}
                            />
                        </div>
                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="edit-emergencyContact">Emergency Contact</Label>
                                <Input
                                    id="edit-emergencyContact"
                                    value={data.emergency_contact}
                                    onChange={(e) => setData('emergency_contact', e.target.value)}
                                    placeholder="Emergency contact name"
                                />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="edit-emergencyPhone">Emergency Phone</Label>
                                <Input
                                    id="edit-emergencyPhone"
                                    value={data.emergency_phone}
                                    onChange={(e) => setData('emergency_phone', e.target.value)}
                                    placeholder="Emergency contact phone"
                                />
                            </div>
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="edit-notes">Notes</Label>
                            <Textarea
                                id="edit-notes"
                                value={data.notes}
                                onChange={(e) => setData('notes', e.target.value)}
                                placeholder="Additional notes about the doctor"
                                rows={3}
                            />
                        </div>

                        {/* Availability Schedule */}
                        <div className="space-y-4">
                            <Label className="text-base font-medium">Availability Schedule</Label>
                            <div className="space-y-3">
                                {Object.entries(data.availability).map(([day, schedule]) => (
                                    <div key={day} className="flex items-center justify-between p-3 border rounded-lg">
                                        <div className="flex items-center space-x-3">
                                            <Checkbox
                                                checked={schedule.available}
                                                onCheckedChange={(checked) => {
                                                    setData('availability', {
                                                        ...data.availability,
                                                        [day]: { ...schedule, available: checked }
                                                    });
                                                }}
                                            />
                                            <span className="font-medium capitalize text-slate-900 dark:text-white min-w-[80px]">{day}</span>
                                        </div>
                                        {schedule.available && (
                                            <div className="flex items-center space-x-2">
                                                <Input
                                                    type="time"
                                                    value={schedule.start}
                                                    onChange={(e) => {
                                                        setData('availability', {
                                                            ...data.availability,
                                                            [day]: { ...schedule, start: e.target.value }
                                                        });
                                                    }}
                                                    className="w-32"
                                                />
                                                <span className="text-slate-500">to</span>
                                                <Input
                                                    type="time"
                                                    value={schedule.end}
                                                    onChange={(e) => {
                                                        setData('availability', {
                                                            ...data.availability,
                                                            [day]: { ...schedule, end: e.target.value }
                                                        });
                                                    }}
                                                    className="w-32"
                                                />
                                            </div>
                                        )}
                                    </div>
                                ))}
                            </div>
                        </div>
                    </div>
                    <DialogFooter>
                        <Button variant="outline" onClick={handleCancel}>
                            <X className="mr-2 h-4 w-4" />
                            Cancel
                        </Button>
                        <Button onClick={handleSaveDoctor} disabled={processing}>
                            {processing ? (
                                <>
                                    <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                                    Updating...
                                </>
                            ) : (
                                <>
                                    <Save className="mr-2 h-4 w-4" />
                                    Update Doctor
                                </>
                            )}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            {/* View Doctor Details Modal */}
            <Dialog open={isViewModalOpen} onOpenChange={setIsViewModalOpen}>
                <DialogContent className="max-w-4xl max-h-[90vh] overflow-y-auto">
                    <DialogHeader>
                        <DialogTitle>Doctor Details</DialogTitle>
                        <DialogDescription>
                            Complete information about {viewingDoctor?.name}
                        </DialogDescription>
                    </DialogHeader>
                    {viewingDoctor && (
                        <div className="space-y-6">
                            <div className="grid grid-cols-2 gap-6">
                                <div className="space-y-4">
                                    <div className="flex items-center space-x-3">
                                        <div className="h-16 w-16 rounded-full bg-gradient-to-r from-blue-500 to-purple-500 flex items-center justify-center shadow-md">
                                            <span className="text-lg font-bold text-white">
                                                {viewingDoctor.name.split(' ').map(n => n[0]).join('')}
                                            </span>
                                        </div>
                                        <div>
                                            <h3 className="text-xl font-semibold text-slate-900 dark:text-white">{viewingDoctor.name}</h3>
                                            <p className="text-slate-600 dark:text-slate-400">{viewingDoctor.specialization}</p>
                                            <Badge
                                                variant={getStatusColor(viewingDoctor.status || 'Active')}
                                                className={`font-medium ${
                                                    (viewingDoctor.status || 'Active') === 'Active'
                                                        ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400'
                                                        : (viewingDoctor.status || 'Active') === 'On Leave'
                                                        ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400'
                                                        : 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400'
                                                }`}
                                            >
                                                {viewingDoctor.status || 'Active'}
                                            </Badge>
                                        </div>
                                    </div>
                                </div>
                                <div className="space-y-4">
                                    <div className="flex items-center space-x-2">
                                        <Mail className="h-4 w-4 text-slate-400" />
                                        <span className="text-slate-700 dark:text-slate-300">{viewingDoctor.email}</span>
                                    </div>
                                    <div className="flex items-center space-x-2">
                                        <Phone className="h-4 w-4 text-slate-400" />
                                        <span className="text-slate-700 dark:text-slate-300">{viewingDoctor.phone}</span>
                                    </div>
                                    <div className="flex items-center space-x-2">
                                        <Award className="h-4 w-4 text-slate-400" />
                                        <span className="text-slate-700 dark:text-slate-300">License: {viewingDoctor.license || viewingDoctor.license_number}</span>
                                    </div>
                                    <div className="flex items-center space-x-2">
                                        <Clock className="h-4 w-4 text-slate-400" />
                                        <span className="text-slate-700 dark:text-slate-300">{viewingDoctor.experience || 'N/A'}</span>
                                    </div>
                                </div>
                            </div>

                            <Tabs defaultValue="basic" className="w-full">
                                <TabsList className="grid w-full grid-cols-4">
                                    <TabsTrigger value="basic">Basic Info</TabsTrigger>
                                    <TabsTrigger value="education">Education</TabsTrigger>
                                    <TabsTrigger value="contact">Contact</TabsTrigger>
                                    <TabsTrigger value="schedule">Schedule</TabsTrigger>
                                </TabsList>

                                <TabsContent value="basic" className="space-y-4">
                                    <div className="grid grid-cols-2 gap-4">
                                        <div>
                                            <Label className="text-sm font-medium text-slate-700 dark:text-slate-300">Consultation Fee</Label>
                                            <div className="flex items-center space-x-2 mt-1">
                                                <DollarSign className="h-4 w-4 text-slate-400" />
                                                <span className="text-slate-900 dark:text-white">${viewingDoctor.consultation_fee || 'Not set'}</span>
                                            </div>
                                        </div>
                                        <div>
                                            <Label className="text-sm font-medium text-slate-700 dark:text-slate-300">Total Patients</Label>
                                            <div className="flex items-center space-x-2 mt-1">
                                                <Users className="h-4 w-4 text-slate-400" />
                                                <span className="text-slate-900 dark:text-white">{viewingDoctor.patients || 0}</span>
                                            </div>
                                        </div>
                                    </div>
                                    {viewingDoctor.notes && (
                                        <div>
                                            <Label className="text-sm font-medium text-slate-700 dark:text-slate-300">Notes</Label>
                                            <p className="mt-1 text-slate-600 dark:text-slate-400">{viewingDoctor.notes}</p>
                                        </div>
                                    )}
                                </TabsContent>

                                <TabsContent value="education" className="space-y-4">
                                    {viewingDoctor.education && (
                                        <div>
                                            <Label className="text-sm font-medium text-slate-700 dark:text-slate-300">Education</Label>
                                            <div className="flex items-start space-x-2 mt-1">
                                                <GraduationCap className="h-4 w-4 text-slate-400 mt-0.5" />
                                                <p className="text-slate-600 dark:text-slate-400">{viewingDoctor.education}</p>
                                            </div>
                                        </div>
                                    )}
                                    {viewingDoctor.certifications && (
                                        <div>
                                            <Label className="text-sm font-medium text-slate-700 dark:text-slate-300">Certifications</Label>
                                            <div className="flex items-start space-x-2 mt-1">
                                                <Award className="h-4 w-4 text-slate-400 mt-0.5" />
                                                <p className="text-slate-600 dark:text-slate-400">{viewingDoctor.certifications}</p>
                                            </div>
                                        </div>
                                    )}
                                </TabsContent>

                                <TabsContent value="contact" className="space-y-4">
                                    {viewingDoctor.address && (
                                        <div>
                                            <Label className="text-sm font-medium text-slate-700 dark:text-slate-300">Address</Label>
                                            <div className="flex items-start space-x-2 mt-1">
                                                <MapPin className="h-4 w-4 text-slate-400 mt-0.5" />
                                                <p className="text-slate-600 dark:text-slate-400">{viewingDoctor.address}</p>
                                            </div>
                                        </div>
                                    )}
                                    {viewingDoctor.emergency_contact && (
                                        <div className="grid grid-cols-2 gap-4">
                                            <div>
                                                <Label className="text-sm font-medium text-slate-700 dark:text-slate-300">Emergency Contact</Label>
                                                <p className="mt-1 text-slate-600 dark:text-slate-400">{viewingDoctor.emergency_contact}</p>
                                            </div>
                                            <div>
                                                <Label className="text-sm font-medium text-slate-700 dark:text-slate-300">Emergency Phone</Label>
                                                <p className="mt-1 text-slate-600 dark:text-slate-400">{viewingDoctor.emergency_phone}</p>
                                            </div>
                                        </div>
                                    )}
                                </TabsContent>

                                <TabsContent value="schedule" className="space-y-4">
                                    <div className="space-y-3">
                                        {viewingDoctor.availability && typeof viewingDoctor.availability === 'object' && !Array.isArray(viewingDoctor.availability) && Object.entries(viewingDoctor.availability).map(([day, schedule]) => (
                                            <div key={day} className="flex items-center justify-between p-3 border rounded-lg">
                                                <div className="flex items-center space-x-3">
                                                    <Checkbox checked={schedule.available} disabled />
                                                    <span className="font-medium capitalize text-slate-900 dark:text-white">{day}</span>
                                                </div>
                                                {schedule.available && (
                                                    <div className="flex items-center space-x-2 text-sm text-slate-600 dark:text-slate-400">
                                                        <Clock className="h-4 w-4" />
                                                        <span>{schedule.start} - {schedule.end}</span>
                                                    </div>
                                                )}
                                            </div>
                                        ))}
                                    </div>
                                </TabsContent>
                            </Tabs>
                        </div>
                    )}
                    <DialogFooter>
                        <Button variant="outline" onClick={() => setIsViewModalOpen(false)}>
                            Close
                        </Button>
                        <Button onClick={() => {
                            setIsViewModalOpen(false);
                            handleEditDoctor(viewingDoctor!);
                        }}>
                            <Edit className="mr-2 h-4 w-4" />
                            Edit Doctor
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            {/* View Schedule Modal */}
            <Dialog open={isScheduleModalOpen} onOpenChange={setIsScheduleModalOpen}>
                <DialogContent className="max-w-4xl max-h-[90vh] overflow-y-auto">
                    <DialogHeader>
                        <DialogTitle>Doctor Schedule</DialogTitle>
                        <DialogDescription>
                            View and manage schedule for {viewingDoctor?.name}
                        </DialogDescription>
                    </DialogHeader>
                    {viewingDoctor && (
                        <div className="space-y-6">
                            <div className="text-center">
                                <div className="h-16 w-16 rounded-full bg-gradient-to-r from-blue-500 to-purple-500 flex items-center justify-center shadow-md mx-auto mb-4">
                                    <span className="text-lg font-bold text-white">
                                        {viewingDoctor.name.split(' ').map(n => n[0]).join('')}
                                    </span>
                                </div>
                                <h3 className="text-xl font-semibold text-slate-900 dark:text-white">{viewingDoctor.name}</h3>
                                <p className="text-slate-600 dark:text-slate-400">{viewingDoctor.specialization}</p>
                            </div>

                            <div className="grid grid-cols-7 gap-2">
                                {['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'].map((day) => {
                                    const dayKey = day.toLowerCase() as keyof typeof viewingDoctor.availability;
                                    const schedule = viewingDoctor.availability && typeof viewingDoctor.availability === 'object' && !Array.isArray(viewingDoctor.availability) ? viewingDoctor.availability[dayKey] : undefined;
                                    return (
                                        <div key={day} className="text-center">
                                            <div className="font-medium text-sm text-slate-700 dark:text-slate-300 mb-2">{day}</div>
                                            <div className={`p-3 rounded-lg border ${
                                                schedule && typeof schedule === 'object' && 'available' in schedule && (schedule as { available: boolean }).available
                                                    ? 'bg-green-50 border-green-200 dark:bg-green-900/20 dark:border-green-800'
                                                    : 'bg-slate-50 border-slate-200 dark:bg-slate-800 dark:border-slate-700'
                                            }`}>
                                                {schedule && typeof schedule === 'object' && 'available' in schedule && (schedule as { available: boolean }).available ? (
                                                    <div className="space-y-1">
                                                        <div className="text-xs font-medium text-green-800 dark:text-green-400">Available</div>
                                                        <div className="text-xs text-green-600 dark:text-green-500">
                                                            {'start' in schedule && 'end' in schedule ? `${(schedule as { start: string; end: string }).start} - ${(schedule as { start: string; end: string }).end}` : 'N/A'}
                                                        </div>
                                                    </div>
                                                ) : (
                                                    <div className="text-xs text-slate-500 dark:text-slate-400">Not Available</div>
                                                )}
                                            </div>
                                        </div>
                                    );
                                })}
                            </div>

                            <div className="bg-slate-50 dark:bg-slate-800 rounded-lg p-4">
                                <h4 className="font-medium text-slate-900 dark:text-white mb-2">Upcoming Appointments</h4>
                                <div className="text-sm text-slate-600 dark:text-slate-400">
                                    {viewingDoctor.next_appointment || viewingDoctor.nextAppointment ? (
                                        <div className="flex items-center space-x-2">
                                            <Calendar className="h-4 w-4" />
                                            <span>{viewingDoctor.next_appointment || viewingDoctor.nextAppointment}</span>
                                        </div>
                                    ) : (
                                        <span>No upcoming appointments</span>
                                    )}
                                </div>
                            </div>
                        </div>
                    )}
                    <DialogFooter>
                        <Button variant="outline" onClick={() => setIsScheduleModalOpen(false)}>
                            Close
                        </Button>
                        <Button onClick={() => {
                            setIsScheduleModalOpen(false);
                            handleEditDoctor(viewingDoctor!);
                        }}>
                            <Edit className="mr-2 h-4 w-4" />
                            Edit Schedule
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            {/* Delete Confirmation Modal */}
            <AlertDialog open={isDeleteModalOpen}>
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>Delete Doctor</AlertDialogTitle>
                        <AlertDialogDescription>
                            Are you sure you want to deactivate {deletingDoctor?.name}? This action will make the doctor inactive and they will not be able to access the system.
                        </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                        <AlertDialogCancel>Cancel</AlertDialogCancel>
                        <AlertDialogAction
                            onClick={deleteDoctor}
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
                        </AlertDialogAction>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>
        </AppLayout>
    );
}
