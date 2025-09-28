import { Head, usePage, useForm } from '@inertiajs/react';
import { useState, useEffect } from 'react';
import AppLayout from '@/layouts/app-layout';
import { adminClinicManagement } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { 
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import {
    Building2,
    Plus,
    Users,
    Calendar,
    Stethoscope,
    Settings,
    Eye,
    Edit,
    Trash2,
    MoreHorizontal,
    CheckCircle,
    AlertCircle,
    Save,
    X,
    Loader2
} from 'lucide-react';
import ClinicSelector from '@/components/clinic-selector';

// Simple toast implementation using browser notifications
const toast = {
    success: (message: string) => {
        console.log('Creating success toast:', message); // Debug log
        // Create a temporary success notification
        const notification = document.createElement('div');
        notification.className = 'fixed top-4 right-4 z-[9999] bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg transform transition-all duration-300 ease-in-out';
        notification.style.transform = 'translateX(100%)';
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
                if (document.body.contains(notification)) {
                    document.body.removeChild(notification);
                }
            }, 300);
        }, 3000);
    },
    error: (message: string) => {
        console.log('Creating error toast:', message); // Debug log
        // Create a temporary error notification
        const notification = document.createElement('div');
        notification.className = 'fixed top-4 right-4 z-[9999] bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg transform transition-all duration-300 ease-in-out';
        notification.style.transform = 'translateX(100%)';
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
                if (document.body.contains(notification)) {
                    document.body.removeChild(notification);
                }
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
        title: 'Clinic Management',
        href: adminClinicManagement(),
    },
];

interface Clinic {
    id: number;
    name: string;
    slug: string;
    address: string;
    phone?: string;
    email?: string;
    website?: string;
    logo_url?: string;
    description?: string;
    timezone?: string;
    created_at: string;
    updated_at: string;
    doctors_count: number;
    patients_count: number;
    appointments_count: number;
    user_clinic_roles: Array<{
        id: number;
        role: {
            name: string;
        };
    }>;
    // Additional properties for clinic-selector compatibility
    user_role?: string;
    is_current?: boolean;
    statistics?: {
        total_doctors: number;
        total_patients: number;
        total_appointments: number;
    };
}

interface PageProps {
    clinics: Clinic[];
    currentClinic?: Clinic;
    [key: string]: any;
}

export default function ClinicManagement() {
    const { props } = usePage<PageProps>();
    const [clinics, setClinics] = useState<Clinic[]>(props.clinics || []);
    const [currentClinic, setCurrentClinic] = useState<Clinic | undefined>(props.currentClinic);
    const [loading, setLoading] = useState(false);
    const [deleteDialogOpen, setDeleteDialogOpen] = useState(false);
    const [clinicToDelete, setClinicToDelete] = useState<Clinic | null>(null);
    const [deleting, setDeleting] = useState(false);
    const [isCreateModalOpen, setIsCreateModalOpen] = useState(false);
    const [isEditModalOpen, setIsEditModalOpen] = useState(false);
    const [clinicToEdit, setClinicToEdit] = useState<Clinic | null>(null);

    // Inertia form for creating/updating clinics
    const { data, setData, post, put, processing, errors: formErrors, reset } = useForm({
        name: '',
        slug: '',
        phone: '',
        email: '',
        website: '',
        description: '',
        timezone: 'Asia/Manila',
        address: {
            street: '',
            city: '',
            state: '',
            postal_code: '',
            country: '',
        }
    });

    // Handle flash messages
    useEffect(() => {
        const flash = props.flash as any;
        console.log('Flash messages:', flash); // Debug log
        if (flash?.success) {
            console.log('Showing success toast:', flash.success); // Debug log
            toast.success(flash.success);
        }
        if (flash?.error) {
            console.log('Showing error toast:', flash.error); // Debug log
            toast.error(flash.error);
        }
    }, [props.flash]);

    const handleClinicChange = (clinic: any) => {
        // Find the full clinic data from our clinics array
        const fullClinic = clinics.find(c => c.id === clinic.id);
        if (fullClinic) {
            setCurrentClinic(fullClinic);
        }
    };

    const handleDeleteClick = (clinic: Clinic) => {
        setClinicToDelete(clinic);
        setDeleteDialogOpen(true);
    };

    const handleCreateClick = () => {
        setIsCreateModalOpen(true);
        reset();
    };

    const handleEditClick = (clinic: Clinic) => {
        setClinicToEdit(clinic);
        setData({
            name: clinic.name,
            slug: clinic.slug,
            phone: clinic.phone || '',
            email: clinic.email || '',
            website: clinic.website || '',
            description: clinic.description || '',
            timezone: 'Asia/Manila', // Default timezone
            address: {
                street: '',
                city: '',
                state: '',
                postal_code: '',
                country: '',
            }
        });
        setIsEditModalOpen(true);
    };

    const saveClinic = () => {
        if (isEditModalOpen && clinicToEdit) {
            // Update existing clinic
            put(`/admin/clinics/${clinicToEdit.id}`, {
                onSuccess: (page: any) => {
                    // Update the clinics list with the new data
                    if (page.props.clinics) {
                        setClinics(page.props.clinics);
                    }
                    handleCancel();
                },
                onError: (errors: any) => {
                    console.error('Form validation errors:', errors);
                }
            });
        } else {
            // Create new clinic
            post('/admin/clinics', {
                onSuccess: (page: any) => {
                    // Update the clinics list with the new data
                    if (page.props.clinics) {
                        setClinics(page.props.clinics);
                    }
                    handleCancel();
                },
                onError: (errors: any) => {
                    console.error('Form validation errors:', errors);
                }
            });
        }
    };

    const handleSaveClinic = () => {
        saveClinic();
    };

    const handleCancel = () => {
        setIsCreateModalOpen(false);
        setIsEditModalOpen(false);
        setClinicToEdit(null);
        reset();
    };

    const handleAddressChange = (field: string, value: string) => {
        setData('address', {
            ...data.address,
                [field]: value,
        });
    };

    const handleDeleteConfirm = async () => {
        if (!clinicToDelete) return;

        try {
            setDeleting(true);
            const response = await fetch(`/admin/clinics/${clinicToDelete.id}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (response.ok) {
                const responseData = await response.json();
                setClinics(prev => prev.filter(c => c.id !== clinicToDelete.id));
                setDeleteDialogOpen(false);
                setClinicToDelete(null);
                
                // Show success toast if message is provided
                if (responseData.message) {
                    toast.success(responseData.message);
                }
            } else {
                const errorData = await response.json();
                console.error('Failed to delete clinic:', errorData.message);
                if (errorData.message) {
                    toast.error(errorData.message);
                }
            }
        } catch (error) {
            console.error('Error deleting clinic:', error);
            toast.error('An error occurred while deleting the clinic');
        } finally {
            setDeleting(false);
        }
    };

    const getRoleBadgeColor = (roleName: string) => {
        switch (roleName?.toLowerCase()) {
            case 'admin':
            case 'superadmin':
                return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
            case 'doctor':
                return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300';
            case 'receptionist':
                return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
            case 'nurse':
                return 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300';
            default:
                return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300';
        }
    };

    const formatDate = (dateString: string) => {
        return new Date(dateString).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Clinic Management - Medinext">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&family=instrument-sans:400,500,600" rel="stylesheet" />
            </Head>

            <div className="min-h-screen bg-gradient-to-br from-slate-50 via-white to-blue-50 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900">
                <div className="space-y-6 p-6">
                    {/* Header */}
                    <div className="flex items-center justify-between">
                        <div>
                            <h1 className="text-3xl font-bold text-slate-900 dark:text-white">Clinic Management</h1>
                            <p className="text-slate-600 dark:text-slate-300 mt-1">
                                Manage your clinics and switch between different clinic contexts
                            </p>
                        </div>
                        <div className="flex items-center space-x-3">
                            <ClinicSelector 
                                currentClinic={currentClinic ? {
                                    ...currentClinic,
                                    user_role: currentClinic.user_role || 'admin',
                                    is_current: true,
                                    statistics: {
                                        total_doctors: currentClinic.doctors_count,
                                        total_patients: currentClinic.patients_count,
                                        total_appointments: currentClinic.appointments_count
                                    }
                                } : undefined}
                                onClinicChange={handleClinicChange}
                                showCreateButton={false}
                                compact={true}
                            />
                            <Button
                                onClick={handleCreateClick}
                                className="bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white shadow-lg"
                            >
                                <Plus className="mr-2 h-4 w-4" />
                                Create Clinic
                            </Button>
                        </div>
                    </div>

                    {/* Statistics Cards */}
                    <div className="grid gap-6 md:grid-cols-4">
                        <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium text-slate-600 dark:text-slate-300">
                                    Total Clinics
                                </CardTitle>
                                <Building2 className="h-4 w-4 text-slate-600 dark:text-slate-300" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold text-slate-900 dark:text-white">
                                    {clinics.length}
                                </div>
                            </CardContent>
                        </Card>

                        <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium text-slate-600 dark:text-slate-300">
                                    Total Doctors
                                </CardTitle>
                                <Stethoscope className="h-4 w-4 text-slate-600 dark:text-slate-300" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold text-slate-900 dark:text-white">
                                    {clinics.reduce((sum, clinic) => sum + clinic.doctors_count, 0)}
                                </div>
                            </CardContent>
                        </Card>

                        <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium text-slate-600 dark:text-slate-300">
                                    Total Patients
                                </CardTitle>
                                <Users className="h-4 w-4 text-slate-600 dark:text-slate-300" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold text-slate-900 dark:text-white">
                                    {clinics.reduce((sum, clinic) => sum + clinic.patients_count, 0)}
                                </div>
                            </CardContent>
                        </Card>

                        <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium text-slate-600 dark:text-slate-300">
                                    Total Appointments
                                </CardTitle>
                                <Calendar className="h-4 w-4 text-slate-600 dark:text-slate-300" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold text-slate-900 dark:text-white">
                                    {clinics.reduce((sum, clinic) => sum + clinic.appointments_count, 0)}
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    {/* Clinics Table */}
                    <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                        <CardHeader>
                            <CardTitle className="text-xl font-semibold text-slate-900 dark:text-white">
                                Your Clinics
                            </CardTitle>
                            <CardDescription className="text-slate-600 dark:text-slate-300">
                                Manage and switch between your clinics
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Clinic</TableHead>
                                        <TableHead>Your Role</TableHead>
                                        <TableHead>Statistics</TableHead>
                                        <TableHead>Created</TableHead>
                                        <TableHead>Actions</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {clinics.map((clinic) => (
                                        <TableRow key={clinic.id} className={clinic.id === currentClinic?.id ? 'bg-blue-50 dark:bg-blue-900/20' : ''}>
                                            <TableCell>
                                                <div className="flex items-center space-x-3">
                                                    {clinic.logo_url ? (
                                                        <img 
                                                            src={clinic.logo_url} 
                                                            alt={clinic.name}
                                                            className="h-10 w-10 rounded-lg object-cover"
                                                        />
                                                    ) : (
                                                        <div className="h-10 w-10 rounded-lg bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                                                            <Building2 className="h-5 w-5 text-gray-600 dark:text-gray-400" />
                                                        </div>
                                                    )}
                                                    <div>
                                                        <div className="font-medium text-slate-900 dark:text-white">
                                                            {clinic.name}
                                                            {clinic.id === currentClinic?.id && (
                                                                <CheckCircle className="inline h-4 w-4 ml-2 text-green-600" />
                                                            )}
                                                        </div>
                                                        <div className="text-sm text-slate-600 dark:text-slate-300">
                                                            {typeof clinic.address === 'string' ? clinic.address : 'Address not specified'}
                                                        </div>
                                                    </div>
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                {clinic.user_clinic_roles.map((role, index) => (
                                                    <Badge 
                                                        key={index}
                                                        className={getRoleBadgeColor(role.role.name)}
                                                    >
                                                        {role.role.name}
                                                    </Badge>
                                                ))}
                                            </TableCell>
                                            <TableCell>
                                                <div className="text-sm space-y-1">
                                                    <div className="flex items-center space-x-1">
                                                        <Stethoscope className="h-3 w-3" />
                                                        <span>{clinic.doctors_count} doctors</span>
                                                    </div>
                                                    <div className="flex items-center space-x-1">
                                                        <Users className="h-3 w-3" />
                                                        <span>{clinic.patients_count} patients</span>
                                                    </div>
                                                    <div className="flex items-center space-x-1">
                                                        <Calendar className="h-3 w-3" />
                                                        <span>{clinic.appointments_count} appointments</span>
                                                    </div>
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <div className="text-sm text-slate-600 dark:text-slate-300">
                                                    {formatDate(clinic.created_at)}
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <div className="flex items-center space-x-2">
                                                    <Button
                                                        size="sm"
                                                        variant="outline"
                                                        onClick={() => window.location.href = `/admin/clinics/${clinic.id}`}
                                                    >
                                                        <Eye className="h-4 w-4" />
                                                    </Button>
                                                    <Button
                                                        size="sm"
                                                        variant="outline"
                                                        onClick={() => handleEditClick(clinic)}
                                                    >
                                                        <Edit className="h-4 w-4" />
                                                    </Button>
                                                    <Button
                                                        size="sm"
                                                        variant="outline"
                                                        onClick={() => window.location.href = `/admin/clinics/${clinic.id}/settings`}
                                                    >
                                                        <Settings className="h-4 w-4" />
                                                    </Button>
                                                    <Button
                                                        size="sm"
                                                        variant="outline"
                                                        onClick={() => handleDeleteClick(clinic)}
                                                        className="text-red-600 hover:text-red-700 hover:bg-red-50"
                                                    >
                                                        <Trash2 className="h-4 w-4" />
                                                    </Button>
                                                </div>
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>

                            {clinics.length === 0 && (
                                <div className="text-center py-12">
                                    <Building2 className="h-12 w-12 mx-auto text-gray-400 mb-4" />
                                    <h3 className="text-lg font-medium text-slate-900 dark:text-white mb-2">
                                        No clinics found
                                    </h3>
                                    <p className="text-slate-600 dark:text-slate-300 mb-4">
                                        Create your first clinic to get started
                                    </p>
                                    <Button
                                        onClick={handleCreateClick}
                                        className="bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white"
                                    >
                                        <Plus className="mr-2 h-4 w-4" />
                                        Create Clinic
                                    </Button>
                                </div>
                            )}
                        </CardContent>
                    </Card>
                </div>
            </div>

            {/* Delete Confirmation Dialog */}
            <Dialog open={deleteDialogOpen} onOpenChange={setDeleteDialogOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Delete Clinic</DialogTitle>
                        <DialogDescription>
                            Are you sure you want to delete "{clinicToDelete?.name}"? This action cannot be undone.
                            All data associated with this clinic will be permanently removed.
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter>
                        <Button
                            variant="outline"
                            onClick={() => setDeleteDialogOpen(false)}
                            disabled={deleting}
                        >
                            Cancel
                        </Button>
                        <Button
                            variant="destructive"
                            onClick={handleDeleteConfirm}
                            disabled={deleting}
                        >
                            {deleting ? 'Deleting...' : 'Delete Clinic'}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            {/* Create Clinic Modal */}
            {isCreateModalOpen && (
                <div className="fixed inset-0 z-[9999]">
                    <div className="fixed inset-0 bg-black/50" onClick={() => setIsCreateModalOpen(false)} />
                    <div className="fixed right-0 top-0 h-full w-[50vw] min-w-[600px] max-w-[800px] bg-white dark:bg-slate-800 border-l border-slate-200 dark:border-slate-700 rounded-l-lg shadow-2xl flex flex-col animate-in slide-in-from-right duration-300">
                        <div className="p-6 pb-4 border-b border-slate-200 dark:border-slate-700">
                            <h2 className="text-lg font-semibold text-slate-900 dark:text-white">Create New Clinic</h2>
                            <p className="text-sm text-slate-600 dark:text-slate-400 mt-1">
                                Enter the details for the new clinic.
                            </p>
                        </div>
                        <div className="flex-1 overflow-y-auto p-6">
                            <div className="grid gap-4 py-4">
                                {/* Basic Information */}
                                <div className="space-y-4">
                                    <h3 className="text-lg font-semibold">Basic Information</h3>
                                    
                                    <div className="space-y-2">
                                        <Label htmlFor="name">Clinic Name *</Label>
                                        <Input
                                            id="name"
                                            value={data.name}
                                            onChange={(e) => setData('name', e.target.value)}
                                            placeholder="Enter clinic name"
                                            className={formErrors.name ? 'border-red-500' : ''}
                                        />
                                        {formErrors.name && <p className="text-sm text-red-500">{formErrors.name}</p>}
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="slug">Slug *</Label>
                                        <Input
                                            id="slug"
                                            value={data.slug}
                                            onChange={(e) => setData('slug', e.target.value)}
                                            placeholder="clinic-slug"
                                            className={formErrors.slug ? 'border-red-500' : ''}
                                        />
                                        {formErrors.slug && <p className="text-sm text-red-500">{formErrors.slug}</p>}
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="description">Description</Label>
                                        <Textarea
                                            id="description"
                                            value={data.description}
                                            onChange={(e) => setData('description', e.target.value)}
                                            placeholder="Enter clinic description"
                                            rows={3}
                                        />
                                    </div>
                                </div>

                                {/* Contact Information */}
                                <div className="space-y-4">
                                    <h3 className="text-lg font-semibold">Contact Information</h3>
                                    
                                    <div className="space-y-2">
                                        <Label htmlFor="phone">Phone</Label>
                                        <Input
                                            id="phone"
                                            value={data.phone}
                                            onChange={(e) => setData('phone', e.target.value)}
                                            placeholder="Enter phone number"
                                        />
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="email">Email</Label>
                                        <Input
                                            id="email"
                                            type="email"
                                            value={data.email}
                                            onChange={(e) => setData('email', e.target.value)}
                                            placeholder="Enter email address"
                                        />
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="website">Website</Label>
                                        <Input
                                            id="website"
                                            value={data.website}
                                            onChange={(e) => setData('website', e.target.value)}
                                            placeholder="https://example.com"
                                        />
                                    </div>
                                </div>

                                {/* Address Information */}
                                <div className="space-y-4">
                                    <h3 className="text-lg font-semibold">Address</h3>
                                    
                                    <div className="space-y-2">
                                        <Label htmlFor="street">Street</Label>
                                        <Input
                                            id="street"
                                            value={data.address.street}
                                            onChange={(e) => handleAddressChange('street', e.target.value)}
                                            placeholder="Enter street address"
                                        />
                                    </div>

                                    <div className="grid grid-cols-2 gap-4">
                                        <div className="space-y-2">
                                            <Label htmlFor="city">City</Label>
                                            <Input
                                                id="city"
                                                value={data.address.city}
                                                onChange={(e) => handleAddressChange('city', e.target.value)}
                                                placeholder="City"
                                            />
                                        </div>
                                        <div className="space-y-2">
                                            <Label htmlFor="state">State</Label>
                                            <Input
                                                id="state"
                                                value={data.address.state}
                                                onChange={(e) => handleAddressChange('state', e.target.value)}
                                                placeholder="State"
                                            />
                                        </div>
                                    </div>

                                    <div className="grid grid-cols-2 gap-4">
                                        <div className="space-y-2">
                                            <Label htmlFor="postal_code">Postal Code</Label>
                                            <Input
                                                id="postal_code"
                                                value={data.address.postal_code}
                                                onChange={(e) => handleAddressChange('postal_code', e.target.value)}
                                                placeholder="Postal code"
                                            />
                                        </div>
                                        <div className="space-y-2">
                                            <Label htmlFor="country">Country</Label>
                                            <Input
                                                id="country"
                                                value={data.address.country}
                                                onChange={(e) => handleAddressChange('country', e.target.value)}
                                                placeholder="Country"
                                            />
                                        </div>
                                    </div>
                                </div>

                                {/* Timezone */}
                                <div className="space-y-2">
                                    <Label htmlFor="timezone">Timezone</Label>
                                    <Input
                                        id="timezone"
                                        value={data.timezone}
                                        onChange={(e) => setData('timezone', e.target.value)}
                                        placeholder="Asia/Manila"
                                    />
                                </div>
                            </div>
                        </div>
                        <div className="p-6 pt-4 border-t border-slate-200 dark:border-slate-700 flex justify-end space-x-3">
                            <Button variant="outline" onClick={handleCancel}>
                                <X className="mr-2 h-4 w-4" />
                                Cancel
                            </Button>
                            <Button onClick={handleSaveClinic} disabled={processing}>
                                {processing ? (
                                    <>
                                        <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                                        Creating...
                                    </>
                                ) : (
                                    <>
                                        <Save className="mr-2 h-4 w-4" />
                                        Create Clinic
                                    </>
                                )}
                            </Button>
                        </div>
                    </div>
                </div>
            )}

            {/* Edit Clinic Modal */}
            {isEditModalOpen && (
                <div className="fixed inset-0 z-[9999]">
                    <div className="fixed inset-0 bg-black/50" onClick={() => setIsEditModalOpen(false)} />
                    <div className="fixed right-0 top-0 h-full w-[50vw] min-w-[600px] max-w-[800px] bg-white dark:bg-slate-800 border-l border-slate-200 dark:border-slate-700 rounded-l-lg shadow-2xl flex flex-col animate-in slide-in-from-right duration-300">
                        <div className="p-6 pb-4 border-b border-slate-200 dark:border-slate-700">
                            <h2 className="text-lg font-semibold text-slate-900 dark:text-white">Edit Clinic</h2>
                            <p className="text-sm text-slate-600 dark:text-slate-400 mt-1">
                                Update the details for {clinicToEdit?.name}.
                            </p>
                        </div>
                        <div className="flex-1 overflow-y-auto p-6">
                            <div className="grid gap-4 py-4">
                        {/* Basic Information */}
                        <div className="space-y-4">
                            <h3 className="text-lg font-semibold">Basic Information</h3>
                            
                            <div className="space-y-2">
                                        <Label htmlFor="edit-name">Clinic Name *</Label>
                                <Input
                                    id="edit-name"
                                            value={data.name}
                                            onChange={(e) => setData('name', e.target.value)}
                                    placeholder="Enter clinic name"
                                />
                            </div>

                            <div className="space-y-2">
                                        <Label htmlFor="edit-slug">Slug *</Label>
                                <Input
                                    id="edit-slug"
                                            value={data.slug}
                                            onChange={(e) => setData('slug', e.target.value)}
                                    placeholder="clinic-slug"
                                />
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="edit-description">Description</Label>
                                <Textarea
                                    id="edit-description"
                                            value={data.description}
                                            onChange={(e) => setData('description', e.target.value)}
                                    placeholder="Enter clinic description"
                                    rows={3}
                                />
                            </div>
                        </div>

                        {/* Contact Information */}
                        <div className="space-y-4">
                            <h3 className="text-lg font-semibold">Contact Information</h3>
                            
                            <div className="space-y-2">
                                <Label htmlFor="edit-phone">Phone</Label>
                                <Input
                                    id="edit-phone"
                                            value={data.phone}
                                            onChange={(e) => setData('phone', e.target.value)}
                                    placeholder="Enter phone number"
                                />
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="edit-email">Email</Label>
                                <Input
                                    id="edit-email"
                                    type="email"
                                            value={data.email}
                                            onChange={(e) => setData('email', e.target.value)}
                                    placeholder="Enter email address"
                                />
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="edit-website">Website</Label>
                                <Input
                                    id="edit-website"
                                            value={data.website}
                                            onChange={(e) => setData('website', e.target.value)}
                                    placeholder="https://example.com"
                                />
                            </div>
                        </div>

                        {/* Address Information */}
                        <div className="space-y-4">
                            <h3 className="text-lg font-semibold">Address</h3>
                            
                            <div className="space-y-2">
                                <Label htmlFor="edit-street">Street</Label>
                                <Input
                                    id="edit-street"
                                            value={data.address.street}
                                    onChange={(e) => handleAddressChange('street', e.target.value)}
                                    placeholder="Enter street address"
                                />
                            </div>

                            <div className="grid grid-cols-2 gap-4">
                                <div className="space-y-2">
                                    <Label htmlFor="edit-city">City</Label>
                                    <Input
                                        id="edit-city"
                                                value={data.address.city}
                                        onChange={(e) => handleAddressChange('city', e.target.value)}
                                        placeholder="City"
                                    />
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="edit-state">State</Label>
                                    <Input
                                        id="edit-state"
                                                value={data.address.state}
                                        onChange={(e) => handleAddressChange('state', e.target.value)}
                                        placeholder="State"
                                    />
                                </div>
                            </div>

                            <div className="grid grid-cols-2 gap-4">
                                <div className="space-y-2">
                                            <Label htmlFor="edit-postal_code">Postal Code</Label>
                                    <Input
                                                id="edit-postal_code"
                                                value={data.address.postal_code}
                                        onChange={(e) => handleAddressChange('postal_code', e.target.value)}
                                        placeholder="Postal code"
                                    />
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="edit-country">Country</Label>
                                    <Input
                                        id="edit-country"
                                                value={data.address.country}
                                        onChange={(e) => handleAddressChange('country', e.target.value)}
                                        placeholder="Country"
                                    />
                                </div>
                            </div>
                        </div>

                        {/* Timezone */}
                        <div className="space-y-2">
                            <Label htmlFor="edit-timezone">Timezone</Label>
                            <Input
                                id="edit-timezone"
                                        value={data.timezone}
                                        onChange={(e) => setData('timezone', e.target.value)}
                                placeholder="Asia/Manila"
                            />
                                </div>
                            </div>
                        </div>
                        <div className="p-6 pt-4 border-t border-slate-200 dark:border-slate-700 flex justify-end space-x-3">
                            <Button variant="outline" onClick={handleCancel}>
                                <X className="mr-2 h-4 w-4" />
                                Cancel
                            </Button>
                            <Button onClick={handleSaveClinic} disabled={processing}>
                                {processing ? (
                                    <>
                                        <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                                        Updating...
                                    </>
                                ) : (
                                    <>
                                        <Save className="mr-2 h-4 w-4" />
                                        Update Clinic
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
