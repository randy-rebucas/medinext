import { Head } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '@/layouts/app-layout';
import { adminClinicSettings } from '@/routes';
import { type BreadcrumbItem } from '@/types';

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
    Building2,
    Clock,
    Mail,
    Phone,
    MapPin,
    Globe,
    CheckCircle,
    AlertCircle
} from 'lucide-react';

export default function ClinicSettings() {
    const [isSaving, setIsSaving] = useState(false);
    const [saveStatus, setSaveStatus] = useState<'idle' | 'success' | 'error'>('idle');

    const handleSave = async () => {
        setIsSaving(true);
        // Simulate save operation
        await new Promise(resolve => setTimeout(resolve, 2000));
        setIsSaving(false);
        setSaveStatus('success');
        setTimeout(() => setSaveStatus('idle'), 3000);
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
                                            defaultValue="MediNext Clinic"
                                            className="h-11 border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-700 dark:text-white"
                                        />
                                    </div>
                                    <div className="space-y-2">
                                        <Label htmlFor="clinic-code" className="text-slate-700 dark:text-slate-300 font-medium">Clinic Code</Label>
                                        <Input
                                            id="clinic-code"
                                            defaultValue="MNC001"
                                            className="h-11 border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-700 dark:text-white"
                                        />
                                    </div>
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="description" className="text-slate-700 dark:text-slate-300 font-medium">Description</Label>
                                    <Textarea
                                        id="description"
                                        defaultValue="A modern healthcare facility providing comprehensive medical services."
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
                                            defaultValue="+1 (555) 123-4567"
                                            className="h-11 border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-700 dark:text-white"
                                        />
                                    </div>
                                    <div className="space-y-2">
                                        <Label htmlFor="email" className="text-slate-700 dark:text-slate-300 font-medium">Email Address</Label>
                                        <Input
                                            id="email"
                                            type="email"
                                            defaultValue="info@medinext.com"
                                            className="h-11 border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-700 dark:text-white"
                                        />
                                    </div>
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="address" className="text-slate-700 dark:text-slate-300 font-medium">Address</Label>
                                    <Textarea
                                        id="address"
                                        defaultValue="123 Medical Center Drive, Healthcare City, HC 12345"
                                        rows={2}
                                        className="border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-700 dark:text-white"
                                    />
                                </div>
                                <div className="grid gap-6 md:grid-cols-2">
                                    <div className="space-y-2">
                                        <Label htmlFor="website" className="text-slate-700 dark:text-slate-300 font-medium">Website</Label>
                                        <Input
                                            id="website"
                                            defaultValue="https://www.medinext.com"
                                            className="h-11 border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-700 dark:text-white"
                                        />
                                    </div>
                                    <div className="space-y-2">
                                        <Label htmlFor="license" className="text-slate-700 dark:text-slate-300 font-medium">License Number</Label>
                                        <Input
                                            id="license"
                                            defaultValue="HC-2024-001"
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
                                            defaultValue="08:00"
                                            className="h-11 border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-700 dark:text-white"
                                        />
                                    </div>
                                    <div className="space-y-2">
                                        <Label htmlFor="closing-time" className="text-slate-700 dark:text-slate-300 font-medium">Closing Time</Label>
                                        <Input
                                            id="closing-time"
                                            type="time"
                                            defaultValue="18:00"
                                            className="h-11 border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-700 dark:text-white"
                                        />
                                    </div>
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="working-days" className="text-slate-700 dark:text-slate-300 font-medium">Working Days</Label>
                                    <div className="flex flex-wrap gap-2">
                                        {['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'].map((day) => (
                                            <Button
                                                key={day}
                                                variant="outline"
                                                size="sm"
                                                className="border-slate-300 dark:border-slate-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:text-blue-600 dark:hover:text-blue-400 hover:border-blue-500"
                                            >
                                                {day}
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
                                        <Switch defaultChecked />
                                    </div>
                                    <div className="flex items-center justify-between p-4 border border-slate-200 dark:border-slate-700 rounded-xl">
                                        <div className="space-y-1">
                                            <Label className="text-slate-700 dark:text-slate-300 font-medium">SMS Notifications</Label>
                                            <p className="text-sm text-slate-600 dark:text-slate-400">
                                                Send SMS reminders for appointments
                                            </p>
                                        </div>
                                        <Switch defaultChecked />
                                    </div>
                                    <div className="flex items-center justify-between p-4 border border-slate-200 dark:border-slate-700 rounded-xl">
                                        <div className="space-y-1">
                                            <Label className="text-slate-700 dark:text-slate-300 font-medium">Online Booking</Label>
                                            <p className="text-sm text-slate-600 dark:text-slate-400">
                                                Allow patients to book appointments online
                                            </p>
                                        </div>
                                        <Switch defaultChecked />
                                    </div>
                                    <div className="flex items-center justify-between p-4 border border-slate-200 dark:border-slate-700 rounded-xl">
                                        <div className="space-y-1">
                                            <Label className="text-slate-700 dark:text-slate-300 font-medium">Patient Portal</Label>
                                            <p className="text-sm text-slate-600 dark:text-slate-400">
                                                Enable patient portal access
                                            </p>
                                        </div>
                                        <Switch defaultChecked />
                                    </div>
                                </div>
                            </CardContent>
                    </Card>

                        {/* Save Button */}
                        <div className="flex justify-end space-x-3">
                            {saveStatus === 'success' && (
                                <div className="flex items-center text-green-600 dark:text-green-400">
                                    <CheckCircle className="mr-2 h-4 w-4" />
                                    Settings saved successfully!
                                </div>
                            )}
                            {saveStatus === 'error' && (
                                <div className="flex items-center text-red-600 dark:text-red-400">
                                    <AlertCircle className="mr-2 h-4 w-4" />
                                    Failed to save settings
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
