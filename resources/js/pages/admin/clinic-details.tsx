import { Head, Link, usePage, useForm, router } from '@inertiajs/react';
import { useState } from 'react';
import { useEffect } from 'react';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { 
    Building2,
    ArrowLeft,
    Users,
    Calendar,
    Stethoscope,
    FileText,
    Settings,
    Edit,
    MapPin,
    Phone,
    Mail,
    Globe,
    Clock,
    Save,
    X,
    Loader2
} from 'lucide-react';

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
    timezone: string;
    created_at: string;
    updated_at: string;
}

interface Stats {
    total_doctors: number;
    total_patients: number;
    total_appointments: number;
    total_encounters: number;
    total_staff: number;
}

interface PageProps {
    clinic: Clinic;
    stats: Stats;
    [key: string]: any;
}

export default function ClinicDetails() {
    const { props } = usePage<PageProps>();
    const { clinic, stats } = props;
    const [isEditModalOpen, setIsEditModalOpen] = useState(false);

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

    // Inertia form for updating clinic
    const { data, setData, put, processing, errors: formErrors, reset } = useForm({
        name: clinic.name,
        slug: clinic.slug,
        phone: clinic.phone || '',
        email: clinic.email || '',
        website: clinic.website || '',
        description: clinic.description || '',
        timezone: clinic.timezone,
        address: {
            street: '',
            city: '',
            state: '',
            postal_code: '',
            country: '',
        }
    });

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Admin Dashboard',
            href: '/admin/dashboard',
        },
        {
            title: 'Clinic Management',
            href: '/admin/clinics',
        },
        {
            title: clinic.name,
            href: `/admin/clinics/${clinic.id}`,
        },
    ];

    const formatDate = (dateString: string) => {
        return new Date(dateString).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    };

    const handleEditClick = () => {
        setIsEditModalOpen(true);
    };

    const handleSaveClinic = () => {
        put(`/admin/clinics/${clinic.id}`, {
            onSuccess: (page: any) => {
                setIsEditModalOpen(false);
                // The page will be refreshed with updated data automatically
            },
            onError: (errors: any) => {
                console.error('Form validation errors:', errors);
            }
        });
    };

    const handleCancel = () => {
        setIsEditModalOpen(false);
        reset();
    };

    const handleAddressChange = (field: string, value: string) => {
        setData('address', {
            ...data.address,
            [field]: value,
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`${clinic.name} - Clinic Details`}>
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&family=instrument-sans:400,500,600" rel="stylesheet" />
            </Head>

            <div className="min-h-screen bg-gradient-to-br from-slate-50 via-white to-blue-50 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900">
                <div className="space-y-6 p-6">
                    {/* Header */}
                    <div className="flex items-center justify-between">
                        <div className="flex items-center space-x-4">
                            <Link href="/admin/clinics">
                                <Button variant="outline" size="sm">
                                    <ArrowLeft className="mr-2 h-4 w-4" />
                                    Back to Clinics
                                </Button>
                            </Link>
                            <div>
                                <h1 className="text-3xl font-bold text-slate-900 dark:text-white">{clinic.name}</h1>
                                <p className="text-slate-600 dark:text-slate-300 mt-1">
                                    Clinic details and statistics
                                </p>
                            </div>
                        </div>
                        <div className="flex items-center space-x-3">
                            <Button variant="outline" onClick={handleEditClick}>
                                <Edit className="mr-2 h-4 w-4" />
                                Edit Clinic
                            </Button>
                            <Button 
                                variant="outline" 
                                onClick={() => toast.success('Test success message')}
                                className="bg-yellow-500 hover:bg-yellow-600 text-white"
                            >
                                Test Toast
                            </Button>
                            <Link href={`/admin/clinics/${clinic.id}/settings`}>
                                <Button className="bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white">
                                    <Settings className="mr-2 h-4 w-4" />
                                    Settings
                                </Button>
                            </Link>
                        </div>
                    </div>

                    {/* Statistics Cards */}
                    <div className="grid gap-6 md:grid-cols-5">
                        <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium text-slate-600 dark:text-slate-300">
                                    Doctors
                                </CardTitle>
                                <Stethoscope className="h-4 w-4 text-slate-600 dark:text-slate-300" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold text-slate-900 dark:text-white">
                                    {stats.total_doctors}
                                </div>
                            </CardContent>
                        </Card>

                        <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium text-slate-600 dark:text-slate-300">
                                    Patients
                                </CardTitle>
                                <Users className="h-4 w-4 text-slate-600 dark:text-slate-300" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold text-slate-900 dark:text-white">
                                    {stats.total_patients}
                                </div>
                            </CardContent>
                        </Card>

                        <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium text-slate-600 dark:text-slate-300">
                                    Appointments
                                </CardTitle>
                                <Calendar className="h-4 w-4 text-slate-600 dark:text-slate-300" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold text-slate-900 dark:text-white">
                                    {stats.total_appointments}
                                </div>
                            </CardContent>
                        </Card>

                        <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium text-slate-600 dark:text-slate-300">
                                    Encounters
                                </CardTitle>
                                <FileText className="h-4 w-4 text-slate-600 dark:text-slate-300" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold text-slate-900 dark:text-white">
                                    {stats.total_encounters}
                                </div>
                            </CardContent>
                        </Card>

                        <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium text-slate-600 dark:text-slate-300">
                                    Staff
                                </CardTitle>
                                <Users className="h-4 w-4 text-slate-600 dark:text-slate-300" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold text-slate-900 dark:text-white">
                                    {stats.total_staff}
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    {/* Clinic Information */}
                    <div className="grid gap-6 md:grid-cols-2">
                        <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                            <CardHeader>
                                <CardTitle className="text-xl font-semibold text-slate-900 dark:text-white">
                                    Clinic Information
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="flex items-center space-x-3">
                                    <Building2 className="h-5 w-5 text-slate-600 dark:text-slate-300" />
                                    <div>
                                        <p className="font-medium text-slate-900 dark:text-white">{clinic.name}</p>
                                        <p className="text-sm text-slate-600 dark:text-slate-300">Clinic Name</p>
                                    </div>
                                </div>

                                {clinic.description && (
                                    <div className="flex items-start space-x-3">
                                        <FileText className="h-5 w-5 text-slate-600 dark:text-slate-300 mt-0.5" />
                                        <div>
                                            <p className="text-slate-900 dark:text-white">{clinic.description}</p>
                                            <p className="text-sm text-slate-600 dark:text-slate-300">Description</p>
                                        </div>
                                    </div>
                                )}

                                <div className="flex items-center space-x-3">
                                    <MapPin className="h-5 w-5 text-slate-600 dark:text-slate-300" />
                                    <div>
                                        <p className="text-slate-900 dark:text-white">
                                            {typeof clinic.address === 'string' ? clinic.address : 'Address not specified'}
                                        </p>
                                        <p className="text-sm text-slate-600 dark:text-slate-300">Address</p>
                                    </div>
                                </div>

                                <div className="flex items-center space-x-3">
                                    <Clock className="h-5 w-5 text-slate-600 dark:text-slate-300" />
                                    <div>
                                        <p className="text-slate-900 dark:text-white">{clinic.timezone}</p>
                                        <p className="text-sm text-slate-600 dark:text-slate-300">Timezone</p>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                            <CardHeader>
                                <CardTitle className="text-xl font-semibold text-slate-900 dark:text-white">
                                    Contact Information
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                {clinic.phone && (
                                    <div className="flex items-center space-x-3">
                                        <Phone className="h-5 w-5 text-slate-600 dark:text-slate-300" />
                                        <div>
                                            <p className="text-slate-900 dark:text-white">{clinic.phone}</p>
                                            <p className="text-sm text-slate-600 dark:text-slate-300">Phone</p>
                                        </div>
                                    </div>
                                )}

                                {clinic.email && (
                                    <div className="flex items-center space-x-3">
                                        <Mail className="h-5 w-5 text-slate-600 dark:text-slate-300" />
                                        <div>
                                            <p className="text-slate-900 dark:text-white">{clinic.email}</p>
                                            <p className="text-sm text-slate-600 dark:text-slate-300">Email</p>
                                        </div>
                                    </div>
                                )}

                                {clinic.website && (
                                    <div className="flex items-center space-x-3">
                                        <Globe className="h-5 w-5 text-slate-600 dark:text-slate-300" />
                                        <div>
                                            <a 
                                                href={clinic.website} 
                                                target="_blank" 
                                                rel="noopener noreferrer"
                                                className="text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300"
                                            >
                                                {clinic.website}
                                            </a>
                                            <p className="text-sm text-slate-600 dark:text-slate-300">Website</p>
                                        </div>
                                    </div>
                                )}

                                <div className="flex items-center space-x-3">
                                    <Calendar className="h-5 w-5 text-slate-600 dark:text-slate-300" />
                                    <div>
                                        <p className="text-slate-900 dark:text-white">Created {formatDate(clinic.created_at)}</p>
                                        <p className="text-sm text-slate-600 dark:text-slate-300">Created Date</p>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>

            {/* Edit Clinic Modal */}
            {isEditModalOpen && (
                <div className="fixed inset-0 z-[9999]">
                    <div className="fixed inset-0 bg-black/50" onClick={() => setIsEditModalOpen(false)} />
                    <div className="fixed right-0 top-0 h-full w-[50vw] min-w-[600px] max-w-[800px] bg-white dark:bg-slate-800 border-l border-slate-200 dark:border-slate-700 rounded-l-lg shadow-2xl flex flex-col animate-in slide-in-from-right duration-300">
                        <div className="p-6 pb-4 border-b border-slate-200 dark:border-slate-700">
                            <h2 className="text-lg font-semibold text-slate-900 dark:text-white">Edit Clinic</h2>
                            <p className="text-sm text-slate-600 dark:text-slate-400 mt-1">
                                Update the details for {clinic.name}.
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
                                            className={formErrors.name ? 'border-red-500' : ''}
                                        />
                                        {formErrors.name && <p className="text-sm text-red-500">{formErrors.name}</p>}
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="edit-slug">Slug *</Label>
                                        <Input
                                            id="edit-slug"
                                            value={data.slug}
                                            onChange={(e) => setData('slug', e.target.value)}
                                            placeholder="clinic-slug"
                                            className={formErrors.slug ? 'border-red-500' : ''}
                                        />
                                        {formErrors.slug && <p className="text-sm text-red-500">{formErrors.slug}</p>}
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
