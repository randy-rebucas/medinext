import { Head, usePage } from '@inertiajs/react';
import { useState, useEffect } from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { 
    Building2, 
    Users, 
    Calendar, 
    Stethoscope, 
    Check,
    Plus,
    ArrowRight,
    Loader2
} from 'lucide-react';

interface Clinic {
    id: number;
    name: string;
    slug: string;
    address: string;
    phone?: string;
    email?: string;
    logo_url?: string;
    user_role: string;
    is_current: boolean;
    statistics: {
        total_doctors: number;
        total_patients: number;
        total_appointments: number;
    };
}

interface PageProps {
    clinics: Clinic[];
    currentClinic?: Clinic;
}

export default function ClinicSelection() {
    const { props } = usePage<PageProps>();
    const [clinics, setClinics] = useState<Clinic[]>(props.clinics || []);
    const [switching, setSwitching] = useState<number | null>(null);

    const handleClinicSwitch = async (clinicId: number) => {
        try {
            setSwitching(clinicId);
            const response = await fetch('/api/clinics/switch', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({
                    clinic_id: clinicId,
                    redirect_url: '/admin/dashboard'
                }),
            });

            if (response.ok) {
                const data = await response.json();
                window.location.href = data.data.redirect_url;
            } else {
                const errorData = await response.json();
                console.error('Failed to switch clinic:', errorData.message);
            }
        } catch (error) {
            console.error('Error switching clinic:', error);
        } finally {
            setSwitching(null);
        }
    };

    const getRoleBadgeColor = (role: string) => {
        switch (role?.toLowerCase()) {
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

    return (
        <>
            <Head title="Select Clinic - Medinext">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&family=instrument-sans:400,500,600" rel="stylesheet" />
            </Head>

            <div className="min-h-screen bg-gradient-to-br from-slate-50 via-white to-blue-50 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900">
                <div className="container mx-auto px-4 py-16">
                    <div className="max-w-4xl mx-auto">
                        {/* Header */}
                        <div className="text-center mb-12">
                            <div className="inline-flex items-center justify-center w-16 h-16 bg-blue-100 dark:bg-blue-900 rounded-full mb-4">
                                <Building2 className="h-8 w-8 text-blue-600 dark:text-blue-400" />
                            </div>
                            <h1 className="text-4xl font-bold text-slate-900 dark:text-white mb-4">
                                Select Your Clinic
                            </h1>
                            <p className="text-xl text-slate-600 dark:text-slate-300 max-w-2xl mx-auto">
                                Choose the clinic you want to work with. You can switch between clinics at any time from the dashboard.
                            </p>
                        </div>

                        {/* Clinics Grid */}
                        <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3 mb-8">
                            {clinics.map((clinic) => (
                                <Card 
                                    key={clinic.id} 
                                    className={`cursor-pointer transition-all hover:shadow-lg ${
                                        clinic.is_current ? 'ring-2 ring-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'hover:shadow-md'
                                    }`}
                                >
                                    <CardHeader className="pb-4">
                                        <div className="flex items-center justify-between mb-3">
                                            {clinic.logo_url ? (
                                                <img 
                                                    src={clinic.logo_url} 
                                                    alt={clinic.name}
                                                    className="h-12 w-12 rounded-lg object-cover"
                                                />
                                            ) : (
                                                <div className="h-12 w-12 rounded-lg bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                                                    <Building2 className="h-6 w-6 text-gray-600 dark:text-gray-400" />
                                                </div>
                                            )}
                                            <div className="flex items-center space-x-2">
                                                <Badge className={getRoleBadgeColor(clinic.user_role)}>
                                                    {clinic.user_role}
                                                </Badge>
                                                {clinic.is_current && (
                                                    <Badge variant="default" className="bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                                                        <Check className="h-3 w-3 mr-1" />
                                                        Current
                                                    </Badge>
                                                )}
                                            </div>
                                        </div>
                                        <CardTitle className="text-lg text-slate-900 dark:text-white">
                                            {clinic.name}
                                        </CardTitle>
                                        <CardDescription className="text-slate-600 dark:text-slate-300">
                                            {clinic.address}
                                        </CardDescription>
                                    </CardHeader>
                                    <CardContent className="pt-0">
                                        <div className="space-y-3">
                                            <div className="flex items-center justify-between text-sm text-gray-600 dark:text-gray-400">
                                                <div className="flex items-center space-x-1">
                                                    <Stethoscope className="h-4 w-4" />
                                                    <span>{clinic.statistics.total_doctors} Doctors</span>
                                                </div>
                                                <div className="flex items-center space-x-1">
                                                    <Users className="h-4 w-4" />
                                                    <span>{clinic.statistics.total_patients} Patients</span>
                                                </div>
                                            </div>
                                            <div className="flex items-center justify-between text-sm text-gray-600 dark:text-gray-400">
                                                <div className="flex items-center space-x-1">
                                                    <Calendar className="h-4 w-4" />
                                                    <span>{clinic.statistics.total_appointments} Appointments</span>
                                                </div>
                                            </div>
                                            {clinic.phone && (
                                                <div className="text-sm text-gray-600 dark:text-gray-400">
                                                    📞 {clinic.phone}
                                                </div>
                                            )}
                                        </div>
                                        
                                        <Button
                                            onClick={() => handleClinicSwitch(clinic.id)}
                                            disabled={switching === clinic.id || clinic.is_current}
                                            className={`w-full mt-4 ${
                                                clinic.is_current 
                                                    ? 'bg-green-600 hover:bg-green-700 text-white' 
                                                    : 'bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white'
                                            }`}
                                        >
                                            {switching === clinic.id ? (
                                                <>
                                                    <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                                                    Switching...
                                                </>
                                            ) : clinic.is_current ? (
                                                <>
                                                    <Check className="mr-2 h-4 w-4" />
                                                    Current Clinic
                                                </>
                                            ) : (
                                                <>
                                                    <ArrowRight className="mr-2 h-4 w-4" />
                                                    Select Clinic
                                                </>
                                            )}
                                        </Button>
                                    </CardContent>
                                </Card>
                            ))}
                        </div>

                        {/* Create New Clinic Option */}
                        <Card className="border-dashed border-2 border-gray-300 dark:border-gray-600 hover:border-blue-500 dark:hover:border-blue-400 transition-colors">
                            <CardContent className="flex items-center justify-center py-12">
                                <div className="text-center">
                                    <div className="inline-flex items-center justify-center w-12 h-12 bg-gray-100 dark:bg-gray-800 rounded-full mb-4">
                                        <Plus className="h-6 w-6 text-gray-600 dark:text-gray-400" />
                                    </div>
                                    <h3 className="text-lg font-medium text-slate-900 dark:text-white mb-2">
                                        Create New Clinic
                                    </h3>
                                    <p className="text-slate-600 dark:text-slate-300 mb-4">
                                        Set up a new clinic in your medical practice
                                    </p>
                                    <Button
                                        onClick={() => window.location.href = '/admin/clinics/create'}
                                        variant="outline"
                                        className="border-blue-500 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20"
                                    >
                                        <Plus className="mr-2 h-4 w-4" />
                                        Create Clinic
                                    </Button>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </>
    );
}
