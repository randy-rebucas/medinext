import { useState } from 'react';
import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { receptionistRegisterPatient } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Badge } from '@/components/ui/badge';
import {
    Save,
    User,
    Phone,
    CreditCard,
    FileText,
    Building2,
    Shield,
    AlertCircle,
    CheckCircle
} from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Receptionist Dashboard',
        href: '/receptionist/dashboard',
    },
    {
        title: 'Register Patient',
        href: receptionistRegisterPatient(),
    },
];

interface PatientFormData {
    // Personal Information
    first_name: string;
    last_name: string;
    date_of_birth: string;
    gender: string;
    ssn: string;

    // Contact Information
    phone: string;
    email: string;
    address: string;
    city: string;
    state: string;
    zip_code: string;

    // Emergency Contact
    emergency_contact_name: string;
    emergency_contact_phone: string;
    emergency_contact_relationship: string;

    // Insurance Information
    insurance_provider: string;
    policy_number: string;
    group_number: string;
    subscriber_id: string;

    // Medical Information
    medical_conditions: string;
    notes: string;
}

interface RegisterPatientProps {
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
    permissions?: string[];
}

export default function RegisterPatient({
    user
}: RegisterPatientProps) {
    // const hasPermission = (permission: string) => permissions.includes(permission);

    const [formData, setFormData] = useState<PatientFormData>({
        first_name: '',
        last_name: '',
        date_of_birth: '',
        gender: '',
        ssn: '',
        phone: '',
        email: '',
        address: '',
        city: '',
        state: '',
        zip_code: '',
        emergency_contact_name: '',
        emergency_contact_phone: '',
        emergency_contact_relationship: '',
        insurance_provider: '',
        policy_number: '',
        group_number: '',
        subscriber_id: '',
        medical_conditions: '',
        notes: ''
    });

    const [errors, setErrors] = useState<Partial<PatientFormData>>({});
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [isSuccess, setIsSuccess] = useState(false);

    const handleInputChange = (field: keyof PatientFormData, value: string) => {
        setFormData(prev => ({ ...prev, [field]: value }));
        // Clear error when user starts typing
        if (errors[field]) {
            setErrors(prev => ({ ...prev, [field]: undefined }));
        }
    };

    const validateForm = (): boolean => {
        const newErrors: Partial<PatientFormData> = {};

        // Required fields validation
        if (!formData.first_name.trim()) newErrors.first_name = 'First name is required';
        if (!formData.last_name.trim()) newErrors.last_name = 'Last name is required';
        if (!formData.date_of_birth) newErrors.date_of_birth = 'Date of birth is required';
        if (!formData.gender) newErrors.gender = 'Gender is required';
        if (!formData.phone.trim()) newErrors.phone = 'Phone number is required';
        if (!formData.email.trim()) newErrors.email = 'Email is required';

        // Email validation
        if (formData.email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.email)) {
            newErrors.email = 'Please enter a valid email address';
        }

        // Phone validation (basic)
        if (formData.phone && !/^[+]?[1-9][\d]{0,15}$/.test(formData.phone.replace(/[\s\-()]/g, ''))) {
            newErrors.phone = 'Please enter a valid phone number';
        }

        setErrors(newErrors);
        return Object.keys(newErrors).length === 0;
    };

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();

        if (!validateForm()) {
            return;
        }

        setIsSubmitting(true);

        try {
            // Simulate API call
            await new Promise(resolve => setTimeout(resolve, 2000));

            // Here you would make the actual API call
            console.log('Registering patient:', formData);

            setIsSuccess(true);
            // Reset form after successful submission
            setTimeout(() => {
                setFormData({
                    first_name: '',
                    last_name: '',
                    date_of_birth: '',
                    gender: '',
                    ssn: '',
                    phone: '',
                    email: '',
                    address: '',
                    city: '',
                    state: '',
                    zip_code: '',
                    emergency_contact_name: '',
                    emergency_contact_phone: '',
                    emergency_contact_relationship: '',
                    insurance_provider: '',
                    policy_number: '',
                    group_number: '',
                    subscriber_id: '',
                    medical_conditions: '',
                    notes: ''
                });
                setIsSuccess(false);
            }, 3000);
        } catch (error) {
            console.error('Error registering patient:', error);
        } finally {
            setIsSubmitting(false);
        }
    };

    const handleCancel = () => {
        // Navigate back or reset form
        window.history.back();
    };
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Register New Patient - Medinext">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&family=instrument-sans:400,500,600" rel="stylesheet" />
            </Head>
            <div className="min-h-screen bg-gradient-to-br from-slate-50 via-white to-blue-50 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900">
                <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
                    {/* Modern Header */}
                    <div className="relative overflow-hidden rounded-2xl bg-gradient-to-r from-green-600 to-blue-600 p-8 text-white shadow-xl">
                        <div className="absolute inset-0 bg-black/10"></div>
                        <div className="relative flex items-center justify-between">
                <div>
                    <h1 className="text-3xl font-bold tracking-tight">Register New Patient</h1>
                                <p className="mt-2 text-green-100">
                                    {user?.clinic?.name || 'No Clinic'} • Add a new patient to the clinic system
                                </p>
                            </div>
                            <div className="flex items-center gap-3">
                                <Badge variant="secondary" className="flex items-center gap-1 bg-white/20 text-white border-white/30 hover:bg-white/30">
                                    <Shield className="h-3 w-3" />
                                    Receptionist
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

                    {/* Success Message */}
                    {isSuccess && (
                        <Card className="border-green-200 bg-green-50 dark:border-green-800 dark:bg-green-900/20">
                            <CardContent className="flex items-center gap-3 p-4">
                                <CheckCircle className="h-5 w-5 text-green-600 dark:text-green-400" />
                                <div>
                                    <h3 className="font-semibold text-green-800 dark:text-green-200">Patient Registered Successfully!</h3>
                                    <p className="text-sm text-green-600 dark:text-green-300">
                                        The patient has been added to the clinic system.
                    </p>
                </div>
                            </CardContent>
                        </Card>
                    )}

                    <form onSubmit={handleSubmit} className="space-y-6">
                    {/* Personal Information */}
                        <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                        <CardHeader>
                                <CardTitle className="flex items-center gap-2 text-lg font-semibold text-slate-900 dark:text-white">
                                    <div className="p-1 bg-blue-100 dark:bg-blue-900/20 rounded-md">
                                        <User className="h-5 w-5 text-blue-600 dark:text-blue-400" />
                                    </div>
                                Personal Information
                            </CardTitle>
                                <CardDescription className="text-slate-600 dark:text-slate-300">
                                Basic patient information and demographics
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid gap-4 md:grid-cols-2">
                                <div className="space-y-2">
                                        <Label htmlFor="first_name" className="text-sm font-medium text-slate-700 dark:text-slate-300">
                                            First Name *
                                        </Label>
                                        <Input
                                            id="first_name"
                                            value={formData.first_name}
                                            onChange={(e) => handleInputChange('first_name', e.target.value)}
                                            placeholder="Enter first name"
                                            className={`border-slate-200 dark:border-slate-700 focus:border-blue-500 dark:focus:border-blue-400 ${errors.first_name ? 'border-red-500 dark:border-red-400' : ''}`}
                                        />
                                        {errors.first_name && (
                                            <p className="text-sm text-red-600 dark:text-red-400 flex items-center gap-1">
                                                <AlertCircle className="h-3 w-3" />
                                                {errors.first_name}
                                            </p>
                                        )}
                                </div>
                                <div className="space-y-2">
                                        <Label htmlFor="last_name" className="text-sm font-medium text-slate-700 dark:text-slate-300">
                                            Last Name *
                                        </Label>
                                        <Input
                                            id="last_name"
                                            value={formData.last_name}
                                            onChange={(e) => handleInputChange('last_name', e.target.value)}
                                            placeholder="Enter last name"
                                            className={`border-slate-200 dark:border-slate-700 focus:border-blue-500 dark:focus:border-blue-400 ${errors.last_name ? 'border-red-500 dark:border-red-400' : ''}`}
                                        />
                                        {errors.last_name && (
                                            <p className="text-sm text-red-600 dark:text-red-400 flex items-center gap-1">
                                                <AlertCircle className="h-3 w-3" />
                                                {errors.last_name}
                                            </p>
                                        )}
                                </div>
                            </div>
                            <div className="grid gap-4 md:grid-cols-2">
                                <div className="space-y-2">
                                        <Label htmlFor="date_of_birth" className="text-sm font-medium text-slate-700 dark:text-slate-300">
                                            Date of Birth *
                                        </Label>
                                        <Input
                                            id="date_of_birth"
                                            type="date"
                                            value={formData.date_of_birth}
                                            onChange={(e) => handleInputChange('date_of_birth', e.target.value)}
                                            className={`border-slate-200 dark:border-slate-700 focus:border-blue-500 dark:focus:border-blue-400 ${errors.date_of_birth ? 'border-red-500 dark:border-red-400' : ''}`}
                                        />
                                        {errors.date_of_birth && (
                                            <p className="text-sm text-red-600 dark:text-red-400 flex items-center gap-1">
                                                <AlertCircle className="h-3 w-3" />
                                                {errors.date_of_birth}
                                            </p>
                                        )}
                                    </div>
                                    <div className="space-y-2">
                                        <Label htmlFor="gender" className="text-sm font-medium text-slate-700 dark:text-slate-300">
                                            Gender *
                                        </Label>
                                        <Select value={formData.gender} onValueChange={(value) => handleInputChange('gender', value)}>
                                            <SelectTrigger className={`border-slate-200 dark:border-slate-700 focus:border-blue-500 dark:focus:border-blue-400 ${errors.gender ? 'border-red-500 dark:border-red-400' : ''}`}>
                                                <SelectValue placeholder="Select gender" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="male">Male</SelectItem>
                                                <SelectItem value="female">Female</SelectItem>
                                                <SelectItem value="other">Other</SelectItem>
                                                <SelectItem value="prefer_not_to_say">Prefer not to say</SelectItem>
                                            </SelectContent>
                                        </Select>
                                        {errors.gender && (
                                            <p className="text-sm text-red-600 dark:text-red-400 flex items-center gap-1">
                                                <AlertCircle className="h-3 w-3" />
                                                {errors.gender}
                                            </p>
                                        )}
                                    </div>
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="ssn" className="text-sm font-medium text-slate-700 dark:text-slate-300">
                                        Social Security Number
                                    </Label>
                                    <Input
                                        id="ssn"
                                        value={formData.ssn}
                                        onChange={(e) => handleInputChange('ssn', e.target.value)}
                                        placeholder="XXX-XX-XXXX"
                                        className="border-slate-200 dark:border-slate-700 focus:border-blue-500 dark:focus:border-blue-400"
                                    />
                            </div>
                        </CardContent>
                    </Card>

                    {/* Contact Information */}
                        <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                        <CardHeader>
                                <CardTitle className="flex items-center gap-2 text-lg font-semibold text-slate-900 dark:text-white">
                                    <div className="p-1 bg-green-100 dark:bg-green-900/20 rounded-md">
                                        <Phone className="h-5 w-5 text-green-600 dark:text-green-400" />
                                    </div>
                                Contact Information
                            </CardTitle>
                                <CardDescription className="text-slate-600 dark:text-slate-300">
                                Patient contact details and address
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid gap-4 md:grid-cols-2">
                                <div className="space-y-2">
                                        <Label htmlFor="phone" className="text-sm font-medium text-slate-700 dark:text-slate-300">
                                            Phone Number *
                                        </Label>
                                        <Input
                                            id="phone"
                                            value={formData.phone}
                                            onChange={(e) => handleInputChange('phone', e.target.value)}
                                            placeholder="+1 (555) 123-4567"
                                            className={`border-slate-200 dark:border-slate-700 focus:border-blue-500 dark:focus:border-blue-400 ${errors.phone ? 'border-red-500 dark:border-red-400' : ''}`}
                                        />
                                        {errors.phone && (
                                            <p className="text-sm text-red-600 dark:text-red-400 flex items-center gap-1">
                                                <AlertCircle className="h-3 w-3" />
                                                {errors.phone}
                                            </p>
                                        )}
                                    </div>
                                    <div className="space-y-2">
                                        <Label htmlFor="email" className="text-sm font-medium text-slate-700 dark:text-slate-300">
                                            Email Address *
                                        </Label>
                                        <Input
                                            id="email"
                                            type="email"
                                            value={formData.email}
                                            onChange={(e) => handleInputChange('email', e.target.value)}
                                            placeholder="patient@email.com"
                                            className={`border-slate-200 dark:border-slate-700 focus:border-blue-500 dark:focus:border-blue-400 ${errors.email ? 'border-red-500 dark:border-red-400' : ''}`}
                                        />
                                        {errors.email && (
                                            <p className="text-sm text-red-600 dark:text-red-400 flex items-center gap-1">
                                                <AlertCircle className="h-3 w-3" />
                                                {errors.email}
                                            </p>
                                        )}
                                    </div>
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="address" className="text-sm font-medium text-slate-700 dark:text-slate-300">
                                        Address
                                    </Label>
                                <Textarea
                                    id="address"
                                        value={formData.address}
                                        onChange={(e) => handleInputChange('address', e.target.value)}
                                    placeholder="Enter full address"
                                    rows={3}
                                        className="border-slate-200 dark:border-slate-700 focus:border-blue-500 dark:focus:border-blue-400"
                                />
                            </div>
                            <div className="grid gap-4 md:grid-cols-3">
                                <div className="space-y-2">
                                        <Label htmlFor="city" className="text-sm font-medium text-slate-700 dark:text-slate-300">
                                            City
                                        </Label>
                                        <Input
                                            id="city"
                                            value={formData.city}
                                            onChange={(e) => handleInputChange('city', e.target.value)}
                                            placeholder="Enter city"
                                            className="border-slate-200 dark:border-slate-700 focus:border-blue-500 dark:focus:border-blue-400"
                                        />
                                </div>
                                <div className="space-y-2">
                                        <Label htmlFor="state" className="text-sm font-medium text-slate-700 dark:text-slate-300">
                                            State
                                        </Label>
                                        <Input
                                            id="state"
                                            value={formData.state}
                                            onChange={(e) => handleInputChange('state', e.target.value)}
                                            placeholder="Enter state"
                                            className="border-slate-200 dark:border-slate-700 focus:border-blue-500 dark:focus:border-blue-400"
                                        />
                                </div>
                                <div className="space-y-2">
                                        <Label htmlFor="zip_code" className="text-sm font-medium text-slate-700 dark:text-slate-300">
                                            ZIP Code
                                        </Label>
                                        <Input
                                            id="zip_code"
                                            value={formData.zip_code}
                                            onChange={(e) => handleInputChange('zip_code', e.target.value)}
                                            placeholder="Enter ZIP code"
                                            className="border-slate-200 dark:border-slate-700 focus:border-blue-500 dark:focus:border-blue-400"
                                        />
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Emergency Contact */}
                        <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                        <CardHeader>
                                <CardTitle className="flex items-center gap-2 text-lg font-semibold text-slate-900 dark:text-white">
                                    <div className="p-1 bg-orange-100 dark:bg-orange-900/20 rounded-md">
                                        <User className="h-5 w-5 text-orange-600 dark:text-orange-400" />
                                    </div>
                                Emergency Contact
                            </CardTitle>
                                <CardDescription className="text-slate-600 dark:text-slate-300">
                                Emergency contact information
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid gap-4 md:grid-cols-2">
                                <div className="space-y-2">
                                        <Label htmlFor="emergency_contact_name" className="text-sm font-medium text-slate-700 dark:text-slate-300">
                                            Emergency Contact Name
                                        </Label>
                                        <Input
                                            id="emergency_contact_name"
                                            value={formData.emergency_contact_name}
                                            onChange={(e) => handleInputChange('emergency_contact_name', e.target.value)}
                                            placeholder="Enter emergency contact name"
                                            className="border-slate-200 dark:border-slate-700 focus:border-blue-500 dark:focus:border-blue-400"
                                        />
                                    </div>
                                    <div className="space-y-2">
                                        <Label htmlFor="emergency_contact_phone" className="text-sm font-medium text-slate-700 dark:text-slate-300">
                                            Emergency Contact Phone
                                        </Label>
                                        <Input
                                            id="emergency_contact_phone"
                                            value={formData.emergency_contact_phone}
                                            onChange={(e) => handleInputChange('emergency_contact_phone', e.target.value)}
                                            placeholder="+1 (555) 123-4567"
                                            className="border-slate-200 dark:border-slate-700 focus:border-blue-500 dark:focus:border-blue-400"
                                        />
                                    </div>
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="emergency_contact_relationship" className="text-sm font-medium text-slate-700 dark:text-slate-300">
                                        Relationship
                                    </Label>
                                    <Select value={formData.emergency_contact_relationship} onValueChange={(value) => handleInputChange('emergency_contact_relationship', value)}>
                                        <SelectTrigger className="border-slate-200 dark:border-slate-700 focus:border-blue-500 dark:focus:border-blue-400">
                                            <SelectValue placeholder="Select relationship" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="spouse">Spouse</SelectItem>
                                            <SelectItem value="parent">Parent</SelectItem>
                                            <SelectItem value="sibling">Sibling</SelectItem>
                                            <SelectItem value="child">Child</SelectItem>
                                            <SelectItem value="friend">Friend</SelectItem>
                                            <SelectItem value="other">Other</SelectItem>
                                        </SelectContent>
                                    </Select>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Insurance Information */}
                        <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                        <CardHeader>
                                <CardTitle className="flex items-center gap-2 text-lg font-semibold text-slate-900 dark:text-white">
                                    <div className="p-1 bg-purple-100 dark:bg-purple-900/20 rounded-md">
                                        <CreditCard className="h-5 w-5 text-purple-600 dark:text-purple-400" />
                                    </div>
                                Insurance Information
                            </CardTitle>
                                <CardDescription className="text-slate-600 dark:text-slate-300">
                                Primary insurance details
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid gap-4 md:grid-cols-2">
                                <div className="space-y-2">
                                        <Label htmlFor="insurance_provider" className="text-sm font-medium text-slate-700 dark:text-slate-300">
                                            Insurance Provider
                                        </Label>
                                        <Input
                                            id="insurance_provider"
                                            value={formData.insurance_provider}
                                            onChange={(e) => handleInputChange('insurance_provider', e.target.value)}
                                            placeholder="e.g., Blue Cross, Aetna"
                                            className="border-slate-200 dark:border-slate-700 focus:border-blue-500 dark:focus:border-blue-400"
                                        />
                                </div>
                                <div className="space-y-2">
                                        <Label htmlFor="policy_number" className="text-sm font-medium text-slate-700 dark:text-slate-300">
                                            Policy Number
                                        </Label>
                                        <Input
                                            id="policy_number"
                                            value={formData.policy_number}
                                            onChange={(e) => handleInputChange('policy_number', e.target.value)}
                                            placeholder="Enter policy number"
                                            className="border-slate-200 dark:border-slate-700 focus:border-blue-500 dark:focus:border-blue-400"
                                        />
                                </div>
                            </div>
                            <div className="grid gap-4 md:grid-cols-2">
                                <div className="space-y-2">
                                        <Label htmlFor="group_number" className="text-sm font-medium text-slate-700 dark:text-slate-300">
                                            Group Number
                                        </Label>
                                        <Input
                                            id="group_number"
                                            value={formData.group_number}
                                            onChange={(e) => handleInputChange('group_number', e.target.value)}
                                            placeholder="Enter group number"
                                            className="border-slate-200 dark:border-slate-700 focus:border-blue-500 dark:focus:border-blue-400"
                                        />
                                </div>
                                <div className="space-y-2">
                                        <Label htmlFor="subscriber_id" className="text-sm font-medium text-slate-700 dark:text-slate-300">
                                            Subscriber ID
                                        </Label>
                                        <Input
                                            id="subscriber_id"
                                            value={formData.subscriber_id}
                                            onChange={(e) => handleInputChange('subscriber_id', e.target.value)}
                                            placeholder="Enter subscriber ID"
                                            className="border-slate-200 dark:border-slate-700 focus:border-blue-500 dark:focus:border-blue-400"
                                        />
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Medical Information */}
                        <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                        <CardHeader>
                                <CardTitle className="flex items-center gap-2 text-lg font-semibold text-slate-900 dark:text-white">
                                    <div className="p-1 bg-red-100 dark:bg-red-900/20 rounded-md">
                                        <FileText className="h-5 w-5 text-red-600 dark:text-red-400" />
                                    </div>
                                Medical Information
                            </CardTitle>
                                <CardDescription className="text-slate-600 dark:text-slate-300">
                                Initial medical information and notes
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="space-y-2">
                                    <Label htmlFor="medical_conditions" className="text-sm font-medium text-slate-700 dark:text-slate-300">
                                        Known Medical Conditions
                                    </Label>
                                <Textarea
                                        id="medical_conditions"
                                        value={formData.medical_conditions}
                                        onChange={(e) => handleInputChange('medical_conditions', e.target.value)}
                                    placeholder="List any known medical conditions, allergies, or medications"
                                    rows={4}
                                        className="border-slate-200 dark:border-slate-700 focus:border-blue-500 dark:focus:border-blue-400"
                                />
                            </div>
                            <div className="space-y-2">
                                    <Label htmlFor="notes" className="text-sm font-medium text-slate-700 dark:text-slate-300">
                                        Additional Notes
                                    </Label>
                                <Textarea
                                    id="notes"
                                        value={formData.notes}
                                        onChange={(e) => handleInputChange('notes', e.target.value)}
                                    placeholder="Any additional information about the patient"
                                    rows={3}
                                        className="border-slate-200 dark:border-slate-700 focus:border-blue-500 dark:focus:border-blue-400"
                                />
                            </div>
                        </CardContent>
                    </Card>

                        {/* Action Buttons */}
                        <div className="flex justify-end gap-3">
                            <Button
                                type="button"
                                variant="outline"
                                onClick={handleCancel}
                                className="border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 hover:border-slate-300 dark:hover:border-slate-600 transition-all duration-200"
                            >
                            Cancel
                        </Button>
                            <Button
                                type="submit"
                                disabled={isSubmitting}
                                className="bg-gradient-to-r from-green-600 to-blue-600 hover:from-green-700 hover:to-blue-700 text-white shadow-lg hover:shadow-xl transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                {isSubmitting ? (
                                    <>
                                        <div className="mr-2 h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"></div>
                                        Registering...
                                    </>
                                ) : (
                                    <>
                            <Save className="mr-2 h-4 w-4" />
                            Register Patient
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
