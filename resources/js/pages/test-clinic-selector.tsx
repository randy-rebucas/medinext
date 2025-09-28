import { Head } from '@inertiajs/react';
import { useState, useEffect } from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Building2, Check, Loader2 } from 'lucide-react';

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

export default function TestClinicSelector() {
    const [clinics, setClinics] = useState<Clinic[]>([]);
    const [loading, setLoading] = useState(true);
    const [switching, setSwitching] = useState(false);
    const [currentClinic, setCurrentClinic] = useState<Clinic | null>(null);

    useEffect(() => {
        fetchClinics();
    }, []);

    const fetchClinics = async () => {
        try {
            setLoading(true);
            // Use a simple fetch to test the clinic data
            const response = await fetch('/clinics/list', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });

            console.log('Response status:', response.status);
            
            if (response.ok) {
                const data = await response.json();
                console.log('Fetched clinics data:', data);
                setClinics(data.data.clinics || []);
                
                // Find current clinic
                const current = data.data.clinics.find((c: Clinic) => c.is_current);
                if (current) {
                    setCurrentClinic(current);
                }
            } else {
                const errorData = await response.json().catch(() => ({}));
                console.error('Failed to fetch clinics:', response.status, response.statusText, errorData);
            }
        } catch (error) {
            console.error('Error fetching clinics:', error);
        } finally {
            setLoading(false);
        }
    };

    const handleClinicSwitch = async (clinicId: string) => {
        if (clinicId === currentClinic?.id.toString()) return;

        try {
            setSwitching(true);
            const response = await fetch('/clinics/switch', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    clinic_id: parseInt(clinicId),
                    redirect_url: window.location.pathname
                }),
            });

            if (response.ok) {
                const data = await response.json();
                console.log('Switch response:', data);
                
                // Update current clinic
                const newClinic = clinics.find(c => c.id === parseInt(clinicId));
                if (newClinic) {
                    setCurrentClinic(newClinic);
                }
                
                // Refresh the page to reflect changes
                window.location.reload();
            } else {
                const errorData = await response.json().catch(() => ({}));
                console.error('Failed to switch clinic:', response.status, response.statusText, errorData);
            }
        } catch (error) {
            console.error('Error switching clinic:', error);
        } finally {
            setSwitching(false);
        }
    };

    return (
        <>
            <Head title="Test Clinic Selector - Medinext" />
            <div className="min-h-screen bg-gradient-to-br from-slate-50 via-white to-blue-50 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900 p-6">
                <div className="max-w-4xl mx-auto">
                    <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                        <CardHeader>
                            <CardTitle className="text-2xl font-bold text-slate-900 dark:text-white">
                                <Building2 className="inline-block h-6 w-6 mr-2 text-blue-600" />
                                Test Clinic Selector
                            </CardTitle>
                            <CardDescription>
                                Testing the clinic selector functionality
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-6">
                            {loading ? (
                                <div className="flex items-center justify-center py-8">
                                    <Loader2 className="h-8 w-8 animate-spin text-blue-600" />
                                    <span className="ml-2 text-slate-600 dark:text-slate-300">Loading clinics...</span>
                                </div>
                            ) : (
                                <>
                                    <div className="space-y-4">
                                        <h3 className="text-lg font-semibold text-slate-800 dark:text-slate-200">
                                            Current Clinic
                                        </h3>
                                        {currentClinic ? (
                                            <div className="p-4 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-200 dark:border-green-800">
                                                <div className="flex items-center space-x-2">
                                                    <Check className="h-5 w-5 text-green-600" />
                                                    <span className="font-medium text-green-800 dark:text-green-200">
                                                        {currentClinic.name}
                                                    </span>
                                                </div>
                                                <p className="text-sm text-green-600 dark:text-green-300 mt-1">
                                                    {typeof currentClinic.address === 'string' ? currentClinic.address : 'Address not specified'}
                                                </p>
                                            </div>
                                        ) : (
                                            <div className="p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border border-yellow-200 dark:border-yellow-800">
                                                <span className="text-yellow-800 dark:text-yellow-200">
                                                    No current clinic selected
                                                </span>
                                            </div>
                                        )}
                                    </div>

                                    <div className="space-y-4">
                                        <h3 className="text-lg font-semibold text-slate-800 dark:text-slate-200">
                                            Available Clinics
                                        </h3>
                                        <div className="grid gap-4 md:grid-cols-2">
                                            {clinics.map((clinic) => (
                                                <Card key={clinic.id} className={`cursor-pointer transition-all hover:shadow-md ${
                                                    clinic.is_current ? 'ring-2 ring-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'hover:shadow-md'
                                                }`}>
                                                    <CardContent className="p-4">
                                                        <div className="flex items-center justify-between mb-3">
                                                            <h4 className="font-medium text-slate-900 dark:text-white">
                                                                {clinic.name}
                                                            </h4>
                                                            {clinic.is_current && (
                                                                <Check className="h-5 w-5 text-green-600" />
                                                            )}
                                                        </div>
                                                        <p className="text-sm text-slate-600 dark:text-slate-300 mb-3">
                                                            {typeof clinic.address === 'string' ? clinic.address : 'Address not specified'}
                                                        </p>
                                                        <div className="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
                                                            <span>Role: {clinic.user_role}</span>
                                                            <span>{clinic.statistics.total_doctors} doctors</span>
                                                        </div>
                                                    </CardContent>
                                                </Card>
                                            ))}
                                        </div>
                                    </div>

                                    <div className="space-y-4">
                                        <h3 className="text-lg font-semibold text-slate-800 dark:text-slate-200">
                                            Clinic Selector Component
                                        </h3>
                                        <div className="flex items-center space-x-2">
                                            <Select 
                                                value={currentClinic?.id.toString() || ''} 
                                                onValueChange={handleClinicSwitch}
                                                disabled={switching}
                                            >
                                                <SelectTrigger className="w-[300px]">
                                                    <SelectValue placeholder="Select clinic">
                                                        {currentClinic && (
                                                            <div className="flex items-center space-x-2">
                                                                <Building2 className="h-4 w-4" />
                                                                <span className="truncate">{currentClinic.name}</span>
                                                            </div>
                                                        )}
                                                    </SelectValue>
                                                </SelectTrigger>
                                                <SelectContent>
                                                    {clinics.map((clinic) => (
                                                        <SelectItem key={clinic.id} value={clinic.id.toString()}>
                                                            <div className="flex items-center justify-between w-full">
                                                                <span>{clinic.name}</span>
                                                                {clinic.is_current && <Check className="h-4 w-4 ml-2" />}
                                                            </div>
                                                        </SelectItem>
                                                    ))}
                                                </SelectContent>
                                            </Select>
                                            {switching && (
                                                <Loader2 className="h-4 w-4 animate-spin text-blue-600" />
                                            )}
                                        </div>
                                    </div>
                                </>
                            )}
                        </CardContent>
                    </Card>
                </div>
            </div>
        </>
    );
}
