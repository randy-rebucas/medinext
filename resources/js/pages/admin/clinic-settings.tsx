import { Head, usePage } from '@inertiajs/react';
import { useState, useEffect } from 'react';
import AppLayout from '@/layouts/app-layout';
import { adminClinicSettings } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { useSettings } from '@/hooks/use-settings';
import { applyBrandingStyles } from '@/lib/branding-utils';
import axios from 'axios';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Admin Dashboard',
        href: '/admin/dashboard',
    },
    {
        title: 'Clinic Settings',
        href: adminClinicSettings(),
    },
];
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Switch } from '@/components/ui/switch';
import {
    Settings,
    Save,
    Clock,
    Phone,
    CheckCircle,
    AlertCircle
} from 'lucide-react';

interface ClinicSettingsData {
    clinic_name: string;
    clinic_code: string;
    description: string;
    phone: string;
    email: string;
    address: string;
    website: string;
    license: string;
    opening_time: string;
    closing_time: string;
    working_days: string[];
    email_notifications: boolean;
    sms_notifications: boolean;
    online_booking: boolean;
    patient_portal: boolean;
}

interface Clinic {
    id: number;
    name: string;
    slug?: string;
    timezone: string;
    logo_url?: string;
    address?: string | { formatted?: string; [key: string]: unknown };
    phone?: string;
    email?: string;
    website?: string;
    description?: string;
    settings?: Record<string, unknown>;
}

interface PageProps {
    clinic: Clinic;
    settings: ClinicSettingsData;
    [key: string]: unknown;
}

export default function ClinicSettings() {
    const { props } = usePage<PageProps>();
    const [isSaving, setIsSaving] = useState(false);
    const { settings, getBrandingColor, getClinicName } = useSettings();

    // Apply branding styles when settings change
    useEffect(() => {
        if (settings?.branding) {
            applyBrandingStyles(
                settings.branding.primary_color,
                settings.branding.secondary_color
            );
        }
    }, [settings]);
    const [saveStatus, setSaveStatus] = useState<'idle' | 'success' | 'error'>('idle');
    const [errorMessage, setErrorMessage] = useState<string>('');
    const [formData, setFormData] = useState<ClinicSettingsData>({
        clinic_name: props.settings?.clinic_name || props.clinic?.name || '',
        clinic_code: props.settings?.clinic_code || '',
        description: props.settings?.description || props.clinic?.description || '',
        phone: props.settings?.phone || props.clinic?.phone || '',
        email: props.settings?.email || props.clinic?.email || '',
        address: props.settings?.address || (props.clinic?.address ?
            (typeof props.clinic.address === 'string' ? props.clinic.address : props.clinic.address.formatted || '') : ''),
        website: props.settings?.website || props.clinic?.website || '',
        license: props.settings?.license || '',
        opening_time: props.settings?.opening_time || '08:00',
        closing_time: props.settings?.closing_time || '18:00',
        working_days: props.settings?.working_days || ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
        email_notifications: props.settings?.email_notifications ?? true,
        sms_notifications: props.settings?.sms_notifications ?? true,
        online_booking: props.settings?.online_booking ?? true,
        patient_portal: props.settings?.patient_portal ?? true,
    });

    const handleInputChange = (field: keyof ClinicSettingsData, value: string | boolean | string[]) => {
        setFormData(prev => ({
            ...prev,
            [field]: value
        }));
    };

    const handleWorkingDayToggle = (day: string) => {
        setFormData(prev => ({
            ...prev,
            working_days: prev.working_days.includes(day)
                ? prev.working_days.filter(d => d !== day)
                : [...prev.working_days, day]
        }));
    };

    const handleSave = async () => {
        setIsSaving(true);
        setSaveStatus('idle');
        setErrorMessage('');

        try {
            const response = await axios.put('/api/settings/clinic', formData);

            if (response.data.success) {
                setSaveStatus('success');
                setTimeout(() => setSaveStatus('idle'), 3000);
            } else {
                setSaveStatus('error');
                setErrorMessage(response.data.message || 'Failed to save settings');
            }
        } catch (error: unknown) {
            setSaveStatus('error');
            if (error && typeof error === 'object' && 'response' in error) {
                const axiosError = error as { response?: { data?: { errors?: Record<string, string[]>; message?: string } } };
                if (axiosError.response?.data?.errors) {
                    const errors = Object.values(axiosError.response.data.errors).flat();
                    setErrorMessage(errors.join(', '));
                } else {
                    setErrorMessage(axiosError.response?.data?.message || 'Failed to save settings');
                }
            } else {
                setErrorMessage('Failed to save settings');
            }
        } finally {
            setIsSaving(false);
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Clinic Settings - Medinext">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&family=instrument-sans:400,500,600" rel="stylesheet" />
            </Head>

            <div className="min-h-screen bg-gradient-to-br from-slate-50 via-white to-blue-50 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900">
                <div className="space-y-6 p-6">

                    <div className="grid gap-6">
                        {/* Basic Information */}
                        <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                            <CardHeader>
                                <div className="flex items-center justify-between">
                                    <div>
                                        <CardTitle className="text-xl font-semibold text-slate-900 dark:text-white">Clinic Settings</CardTitle>
                                        <CardDescription className="text-slate-600 dark:text-slate-300">
                                            Manage your clinic's configuration and preferences
                                        </CardDescription>
                                    </div>
                                    <div className="flex space-x-3">
                                        <Button
                                            onClick={handleSave}
                                            disabled={isSaving}
                                            className="bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white shadow-lg"
                                        >
                                            <Save className="mr-2 h-4 w-4" />
                                            {isSaving ? 'Saving...' : 'Save Changes'}
                                        </Button>
                                    </div>
                                </div>
                            </CardHeader>
                            <CardContent className="space-y-6">
                                <div className="grid gap-6 md:grid-cols-2">
                                    <div className="space-y-2">
                                        <Label htmlFor="clinic-name" className="text-slate-700 dark:text-slate-300 font-medium">Clinic Name</Label>
                                        <Input
                                            id="clinic-name"
                                            value={formData.clinic_name}
                                            onChange={(e) => handleInputChange('clinic_name', e.target.value)}
                                            className="h-11 border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-700 dark:text-white"
                                        />
                                    </div>
                                    <div className="space-y-2">
                                        <Label htmlFor="clinic-code" className="text-slate-700 dark:text-slate-300 font-medium">Clinic Code</Label>
                                        <Input
                                            id="clinic-code"
                                            value={formData.clinic_code}
                                            onChange={(e) => handleInputChange('clinic_code', e.target.value)}
                                            className="h-11 border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-700 dark:text-white"
                                        />
                                    </div>
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="description" className="text-slate-700 dark:text-slate-300 font-medium">Description</Label>
                                    <Textarea
                                        id="description"
                                        value={formData.description}
                                        onChange={(e) => handleInputChange('description', e.target.value)}
                                        rows={3}
                                        className="border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-700 dark:text-white"
                                    />
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
                                    Update contact details and location information
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-6">
                                <div className="grid gap-6 md:grid-cols-2">
                                    <div className="space-y-2">
                                        <Label htmlFor="phone" className="text-slate-700 dark:text-slate-300 font-medium">Phone Number</Label>
                                        <Input
                                            id="phone"
                                            value={formData.phone}
                                            onChange={(e) => handleInputChange('phone', e.target.value)}
                                            className="h-11 border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-700 dark:text-white"
                                        />
                                    </div>
                                    <div className="space-y-2">
                                        <Label htmlFor="email" className="text-slate-700 dark:text-slate-300 font-medium">Email Address</Label>
                                        <Input
                                            id="email"
                                            type="email"
                                            value={formData.email}
                                            onChange={(e) => handleInputChange('email', e.target.value)}
                                            className="h-11 border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-700 dark:text-white"
                                        />
                                    </div>
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="address" className="text-slate-700 dark:text-slate-300 font-medium">Address</Label>
                                    <Textarea
                                        id="address"
                                        value={formData.address}
                                        onChange={(e) => handleInputChange('address', e.target.value)}
                                        rows={2}
                                        className="border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-700 dark:text-white"
                                    />
                                </div>
                                <div className="grid gap-6 md:grid-cols-2">
                                    <div className="space-y-2">
                                        <Label htmlFor="website" className="text-slate-700 dark:text-slate-300 font-medium">Website</Label>
                                        <Input
                                            id="website"
                                            value={formData.website}
                                            onChange={(e) => handleInputChange('website', e.target.value)}
                                            className="h-11 border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-700 dark:text-white"
                                        />
                                    </div>
                                    <div className="space-y-2">
                                        <Label htmlFor="license" className="text-slate-700 dark:text-slate-300 font-medium">License Number</Label>
                                        <Input
                                            id="license"
                                            value={formData.license}
                                            onChange={(e) => handleInputChange('license', e.target.value)}
                                            className="h-11 border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-700 dark:text-white"
                                        />
                                    </div>
                                </div>
                            </CardContent>
                    </Card>

                        {/* Operating Hours */}
                        <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                            <CardHeader>
                                <CardTitle className="flex items-center text-slate-900 dark:text-white">
                                    <div className="p-2 bg-orange-500 rounded-lg mr-3">
                                        <Clock className="h-5 w-5 text-white" />
                                    </div>
                                    Operating Hours
                                </CardTitle>
                                <CardDescription className="text-slate-600 dark:text-slate-300">
                                    Set your clinic's operating schedule
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-6">
                                <div className="grid gap-6 md:grid-cols-2">
                                    <div className="space-y-2">
                                        <Label htmlFor="opening-time" className="text-slate-700 dark:text-slate-300 font-medium">Opening Time</Label>
                                        <Input
                                            id="opening-time"
                                            type="time"
                                            value={formData.opening_time}
                                            onChange={(e) => handleInputChange('opening_time', e.target.value)}
                                            className="h-11 border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-700 dark:text-white"
                                        />
                                    </div>
                                    <div className="space-y-2">
                                        <Label htmlFor="closing-time" className="text-slate-700 dark:text-slate-300 font-medium">Closing Time</Label>
                                        <Input
                                            id="closing-time"
                                            type="time"
                                            value={formData.closing_time}
                                            onChange={(e) => handleInputChange('closing_time', e.target.value)}
                                            className="h-11 border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-700 dark:text-white"
                                        />
                                    </div>
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="working-days" className="text-slate-700 dark:text-slate-300 font-medium">Working Days</Label>
                                    <div className="flex flex-wrap gap-2">
                                        {['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'].map((day) => (
                                            <Button
                                                key={day}
                                                type="button"
                                                variant={formData.working_days.includes(day) ? "default" : "outline"}
                                                size="sm"
                                                onClick={() => handleWorkingDayToggle(day)}
                                                className={formData.working_days.includes(day)
                                                    ? "bg-blue-600 text-white hover:bg-blue-700"
                                                    : "border-slate-300 dark:border-slate-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:text-blue-600 dark:hover:text-blue-400 hover:border-blue-500"
                                                }
                                            >
                                                {day.charAt(0).toUpperCase() + day.slice(1)}
                                            </Button>
                                        ))}
                                    </div>
                                </div>
                            </CardContent>
                    </Card>

                        {/* System Settings */}
                        <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                            <CardHeader>
                                <CardTitle className="flex items-center text-slate-900 dark:text-white">
                                    <div className="p-2 bg-purple-500 rounded-lg mr-3">
                                        <Settings className="h-5 w-5 text-white" />
                                    </div>
                                    System Settings
                                </CardTitle>
                                <CardDescription className="text-slate-600 dark:text-slate-300">
                                    Configure system preferences and features
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-6">
                                <div className="space-y-6">
                                    <div className="flex items-center justify-between p-4 border border-slate-200 dark:border-slate-700 rounded-xl">
                                        <div className="space-y-1">
                                            <Label className="text-slate-700 dark:text-slate-300 font-medium">Email Notifications</Label>
                                            <p className="text-sm text-slate-600 dark:text-slate-400">
                                                Send email notifications for appointments and updates
                                            </p>
                                        </div>
                                        <Switch
                                            checked={formData.email_notifications}
                                            onCheckedChange={(checked) => handleInputChange('email_notifications', checked)}
                                        />
                                    </div>
                                    <div className="flex items-center justify-between p-4 border border-slate-200 dark:border-slate-700 rounded-xl">
                                        <div className="space-y-1">
                                            <Label className="text-slate-700 dark:text-slate-300 font-medium">SMS Notifications</Label>
                                            <p className="text-sm text-slate-600 dark:text-slate-400">
                                                Send SMS reminders for appointments
                                            </p>
                                        </div>
                                        <Switch
                                            checked={formData.sms_notifications}
                                            onCheckedChange={(checked) => handleInputChange('sms_notifications', checked)}
                                        />
                                    </div>
                                    <div className="flex items-center justify-between p-4 border border-slate-200 dark:border-slate-700 rounded-xl">
                                        <div className="space-y-1">
                                            <Label className="text-slate-700 dark:text-slate-300 font-medium">Online Booking</Label>
                                            <p className="text-sm text-slate-600 dark:text-slate-400">
                                                Allow patients to book appointments online
                                            </p>
                                        </div>
                                        <Switch
                                            checked={formData.online_booking}
                                            onCheckedChange={(checked) => handleInputChange('online_booking', checked)}
                                        />
                                    </div>
                                    <div className="flex items-center justify-between p-4 border border-slate-200 dark:border-slate-700 rounded-xl">
                                        <div className="space-y-1">
                                            <Label className="text-slate-700 dark:text-slate-300 font-medium">Patient Portal</Label>
                                            <p className="text-sm text-slate-600 dark:text-slate-400">
                                                Enable patient portal access
                                            </p>
                                        </div>
                                        <Switch
                                            checked={formData.patient_portal}
                                            onCheckedChange={(checked) => handleInputChange('patient_portal', checked)}
                                        />
                                    </div>
                                </div>
                            </CardContent>
                    </Card>

                        {/* Save Button */}
                        <div className="flex flex-col items-end space-y-3">
                            {saveStatus === 'success' && (
                                <div className="flex items-center text-green-600 dark:text-green-400">
                                    <CheckCircle className="mr-2 h-4 w-4" />
                                    Settings saved successfully!
                                </div>
                            )}
                            {saveStatus === 'error' && (
                                <div className="flex items-center text-red-600 dark:text-red-400">
                                    <AlertCircle className="mr-2 h-4 w-4" />
                                    {errorMessage || 'Failed to save settings'}
                                </div>
                            )}
                            <Button
                                onClick={handleSave}
                                disabled={isSaving}
                                className="bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white shadow-lg min-w-[140px]"
                            >
                                {isSaving ? (
                                    <>
                                        <div className="mr-2 h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent" />
                                        Saving...
                                    </>
                                ) : (
                                    <>
                                        <Save className="mr-2 h-4 w-4" />
                                        Save Settings
                                    </>
                                )}
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
