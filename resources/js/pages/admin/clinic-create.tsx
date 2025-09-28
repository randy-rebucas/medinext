import { Head } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '@/layouts/app-layout';
import { adminClinicManagement } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import {
    Building2,
    Save,
    ArrowLeft,
    CheckCircle,
    AlertCircle,
    Globe,
    Phone,
    Mail,
    MapPin
} from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Admin Dashboard',
        href: '/admin/dashboard',
    },
    {
        title: 'Clinic Management',
        href: adminClinicManagement(),
    },
    {
        title: 'Create Clinic',
        href: '/admin/clinics/create',
    },
];

interface FormData {
    name: string;
    slug: string;
    timezone: string;
    logo_url: string;
    address: {
        street: string;
        city: string;
        state: string;
        postal_code: string;
        country: string;
    };
    phone: string;
    email: string;
    website: string;
    description: string;
}

const timezones = [
    'America/New_York',
    'America/Chicago',
    'America/Denver',
    'America/Los_Angeles',
    'Europe/London',
    'Europe/Paris',
    'Europe/Berlin',
    'Asia/Tokyo',
    'Asia/Shanghai',
    'Asia/Kolkata',
    'Asia/Manila',
    'Australia/Sydney',
    'Pacific/Auckland',
];

export default function ClinicCreate() {
    const [formData, setFormData] = useState<FormData>({
        name: '',
        slug: '',
        timezone: 'America/New_York',
        logo_url: '',
        address: {
            street: '',
            city: '',
            state: '',
            postal_code: '',
            country: 'United States',
        },
        phone: '',
        email: '',
        website: '',
        description: '',
    });

    const [isSaving, setIsSaving] = useState(false);
    const [saveStatus, setSaveStatus] = useState<'idle' | 'success' | 'error'>('idle');
    const [errorMessage, setErrorMessage] = useState<string>('');
    const [errors, setErrors] = useState<Record<string, string>>({});

    const handleInputChange = (field: string, value: string) => {
        if (field.startsWith('address.')) {
            const addressField = field.split('.')[1];
            setFormData(prev => ({
                ...prev,
                address: {
                    ...prev.address,
                    [addressField]: value
                }
            }));
        } else {
            setFormData(prev => ({
                ...prev,
                [field]: value
            }));

            // Auto-generate slug from name
            if (field === 'name' && !formData.slug) {
                const slug = value.toLowerCase()
                    .replace(/[^a-z0-9\s-]/g, '')
                    .replace(/\s+/g, '-')
                    .replace(/-+/g, '-')
                    .trim();
                setFormData(prev => ({
                    ...prev,
                    slug: slug
                }));
            }
        }

        // Clear field-specific error
        if (errors[field]) {
            setErrors(prev => ({
                ...prev,
                [field]: ''
            }));
        }
    };

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setIsSaving(true);
        setSaveStatus('idle');
        setErrorMessage('');
        setErrors({});

        try {
            const response = await fetch('/admin/clinics', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify(formData),
            });

            const data = await response.json();

            if (data.success) {
                setSaveStatus('success');
                setTimeout(() => {
                    window.location.href = data.data.redirect_url || '/admin/clinic-management';
                }, 2000);
            } else {
                setSaveStatus('error');
                if (data.errors) {
                    setErrors(data.errors);
                } else {
                    setErrorMessage(data.message || 'Failed to create clinic');
                }
            }
        } catch (error) {
            setSaveStatus('error');
            setErrorMessage('Network error. Please try again.');
        } finally {
            setIsSaving(false);
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Create Clinic - Medinext">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&family=instrument-sans:400,500,600" rel="stylesheet" />
            </Head>

            <div className="min-h-screen bg-gradient-to-br from-slate-50 via-white to-blue-50 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900">
                <div className="space-y-6 p-6">
                    {/* Header */}
                    <div className="flex items-center justify-between">
                        <div>
                            <h1 className="text-3xl font-bold text-slate-900 dark:text-white">Create New Clinic</h1>
                            <p className="text-slate-600 dark:text-slate-300 mt-1">
                                Set up a new clinic in your medical practice
                            </p>
                        </div>
                        <Button
                            variant="outline"
                            onClick={() => window.location.href = '/admin/clinic-management'}
                        >
                            <ArrowLeft className="mr-2 h-4 w-4" />
                            Back to Clinics
                        </Button>
                    </div>

                    <form onSubmit={handleSubmit} className="space-y-6">
                        <div className="grid gap-6 lg:grid-cols-2">
                            {/* Basic Information */}
                            <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                                <CardHeader>
                                    <CardTitle className="flex items-center text-slate-900 dark:text-white">
                                        <div className="p-2 bg-blue-500 rounded-lg mr-3">
                                            <Building2 className="h-5 w-5 text-white" />
                                        </div>
                                        Basic Information
                                    </CardTitle>
                                    <CardDescription className="text-slate-600 dark:text-slate-300">
                                        Essential details about your clinic
                                    </CardDescription>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    <div className="space-y-2">
                                        <Label htmlFor="name" className="text-slate-700 dark:text-slate-300 font-medium">
                                            Clinic Name *
                                        </Label>
                                        <Input
                                            id="name"
                                            value={formData.name}
                                            onChange={(e) => handleInputChange('name', e.target.value)}
                                            className="h-11 border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-700 dark:text-white"
                                            placeholder="Enter clinic name"
                                        />
                                        {errors.name && (
                                            <p className="text-sm text-red-600 dark:text-red-400">{errors.name}</p>
                                        )}
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="slug" className="text-slate-700 dark:text-slate-300 font-medium">
                                            URL Slug *
                                        </Label>
                                        <Input
                                            id="slug"
                                            value={formData.slug}
                                            onChange={(e) => handleInputChange('slug', e.target.value)}
                                            className="h-11 border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-700 dark:text-white"
                                            placeholder="clinic-url-slug"
                                        />
                                        {errors.slug && (
                                            <p className="text-sm text-red-600 dark:text-red-400">{errors.slug}</p>
                                        )}
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="timezone" className="text-slate-700 dark:text-slate-300 font-medium">
                                            Timezone *
                                        </Label>
                                        <Select value={formData.timezone} onValueChange={(value) => handleInputChange('timezone', value)}>
                                            <SelectTrigger className="h-11 border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-700 dark:text-white">
                                                <SelectValue placeholder="Select timezone" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {timezones.map((tz) => (
                                                    <SelectItem key={tz} value={tz}>
                                                        {tz}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                        {errors.timezone && (
                                            <p className="text-sm text-red-600 dark:text-red-400">{errors.timezone}</p>
                                        )}
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="description" className="text-slate-700 dark:text-slate-300 font-medium">
                                            Description
                                        </Label>
                                        <Textarea
                                            id="description"
                                            value={formData.description}
                                            onChange={(e) => handleInputChange('description', e.target.value)}
                                            rows={3}
                                            className="border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-700 dark:text-white"
                                            placeholder="Brief description of your clinic"
                                        />
                                        {errors.description && (
                                            <p className="text-sm text-red-600 dark:text-red-400">{errors.description}</p>
                                        )}
                                    </div>
                                </CardContent>
                            </Card>

                            {/* Contact Information */}
                            <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                                <CardHeader>
                                    <CardTitle className="flex items-center text-slate-900 dark:text-white">
                                        <div className="p-2 bg-green-500 rounded-lg mr-3">
                                            <Phone className="h-5 w-5 text-white" />
                                        </div>
                                        Contact Information
                                    </CardTitle>
                                    <CardDescription className="text-slate-600 dark:text-slate-300">
                                        How patients can reach your clinic
                                    </CardDescription>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    <div className="space-y-2">
                                        <Label htmlFor="phone" className="text-slate-700 dark:text-slate-300 font-medium">
                                            Phone Number
                                        </Label>
                                        <Input
                                            id="phone"
                                            value={formData.phone}
                                            onChange={(e) => handleInputChange('phone', e.target.value)}
                                            className="h-11 border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-700 dark:text-white"
                                            placeholder="+1 (555) 123-4567"
                                        />
                                        {errors.phone && (
                                            <p className="text-sm text-red-600 dark:text-red-400">{errors.phone}</p>
                                        )}
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="email" className="text-slate-700 dark:text-slate-300 font-medium">
                                            Email Address
                                        </Label>
                                        <Input
                                            id="email"
                                            type="email"
                                            value={formData.email}
                                            onChange={(e) => handleInputChange('email', e.target.value)}
                                            className="h-11 border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-700 dark:text-white"
                                            placeholder="info@clinic.com"
                                        />
                                        {errors.email && (
                                            <p className="text-sm text-red-600 dark:text-red-400">{errors.email}</p>
                                        )}
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="website" className="text-slate-700 dark:text-slate-300 font-medium">
                                            Website
                                        </Label>
                                        <Input
                                            id="website"
                                            value={formData.website}
                                            onChange={(e) => handleInputChange('website', e.target.value)}
                                            className="h-11 border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-700 dark:text-white"
                                            placeholder="https://www.clinic.com"
                                        />
                                        {errors.website && (
                                            <p className="text-sm text-red-600 dark:text-red-400">{errors.website}</p>
                                        )}
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="logo_url" className="text-slate-700 dark:text-slate-300 font-medium">
                                            Logo URL
                                        </Label>
                                        <Input
                                            id="logo_url"
                                            value={formData.logo_url}
                                            onChange={(e) => handleInputChange('logo_url', e.target.value)}
                                            className="h-11 border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-700 dark:text-white"
                                            placeholder="https://example.com/logo.png"
                                        />
                                        {errors.logo_url && (
                                            <p className="text-sm text-red-600 dark:text-red-400">{errors.logo_url}</p>
                                        )}
                                    </div>
                                </CardContent>
                            </Card>
                        </div>

                        {/* Address Information */}
                        <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                            <CardHeader>
                                <CardTitle className="flex items-center text-slate-900 dark:text-white">
                                    <div className="p-2 bg-orange-500 rounded-lg mr-3">
                                        <MapPin className="h-5 w-5 text-white" />
                                    </div>
                                    Address Information
                                </CardTitle>
                                <CardDescription className="text-slate-600 dark:text-slate-300">
                                    Physical location of your clinic
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="grid gap-4 md:grid-cols-2">
                                    <div className="space-y-2">
                                        <Label htmlFor="street" className="text-slate-700 dark:text-slate-300 font-medium">
                                            Street Address
                                        </Label>
                                        <Input
                                            id="street"
                                            value={formData.address.street}
                                            onChange={(e) => handleInputChange('address.street', e.target.value)}
                                            className="h-11 border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-700 dark:text-white"
                                            placeholder="123 Main Street"
                                        />
                                        {errors['address.street'] && (
                                            <p className="text-sm text-red-600 dark:text-red-400">{errors['address.street']}</p>
                                        )}
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="city" className="text-slate-700 dark:text-slate-300 font-medium">
                                            City
                                        </Label>
                                        <Input
                                            id="city"
                                            value={formData.address.city}
                                            onChange={(e) => handleInputChange('address.city', e.target.value)}
                                            className="h-11 border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-700 dark:text-white"
                                            placeholder="New York"
                                        />
                                        {errors['address.city'] && (
                                            <p className="text-sm text-red-600 dark:text-red-400">{errors['address.city']}</p>
                                        )}
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="state" className="text-slate-700 dark:text-slate-300 font-medium">
                                            State/Province
                                        </Label>
                                        <Input
                                            id="state"
                                            value={formData.address.state}
                                            onChange={(e) => handleInputChange('address.state', e.target.value)}
                                            className="h-11 border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-700 dark:text-white"
                                            placeholder="NY"
                                        />
                                        {errors['address.state'] && (
                                            <p className="text-sm text-red-600 dark:text-red-400">{errors['address.state']}</p>
                                        )}
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="postal_code" className="text-slate-700 dark:text-slate-300 font-medium">
                                            Postal Code
                                        </Label>
                                        <Input
                                            id="postal_code"
                                            value={formData.address.postal_code}
                                            onChange={(e) => handleInputChange('address.postal_code', e.target.value)}
                                            className="h-11 border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-700 dark:text-white"
                                            placeholder="10001"
                                        />
                                        {errors['address.postal_code'] && (
                                            <p className="text-sm text-red-600 dark:text-red-400">{errors['address.postal_code']}</p>
                                        )}
                                    </div>

                                    <div className="space-y-2 md:col-span-2">
                                        <Label htmlFor="country" className="text-slate-700 dark:text-slate-300 font-medium">
                                            Country
                                        </Label>
                                        <Input
                                            id="country"
                                            value={formData.address.country}
                                            onChange={(e) => handleInputChange('address.country', e.target.value)}
                                            className="h-11 border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-700 dark:text-white"
                                            placeholder="United States"
                                        />
                                        {errors['address.country'] && (
                                            <p className="text-sm text-red-600 dark:text-red-400">{errors['address.country']}</p>
                                        )}
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Save Button */}
                        <div className="flex flex-col items-end space-y-3">
                            {saveStatus === 'success' && (
                                <div className="flex items-center text-green-600 dark:text-green-400">
                                    <CheckCircle className="mr-2 h-4 w-4" />
                                    Clinic created successfully! Redirecting...
                                </div>
                            )}
                            {saveStatus === 'error' && (
                                <div className="flex items-center text-red-600 dark:text-red-400">
                                    <AlertCircle className="mr-2 h-4 w-4" />
                                    {errorMessage || 'Failed to create clinic'}
                                </div>
                            )}
                            <Button
                                type="submit"
                                disabled={isSaving}
                                className="bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white shadow-lg min-w-[140px]"
                            >
                                {isSaving ? (
                                    <>
                                        <div className="mr-2 h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent" />
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
                    </form>
                </div>
            </div>
        </AppLayout>
    );
}
