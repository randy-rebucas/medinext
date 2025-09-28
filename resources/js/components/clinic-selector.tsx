import { useState, useEffect } from 'react';
import { Button } from '@/components/ui/button';
import { 
    Select, 
    SelectContent, 
    SelectItem, 
    SelectTrigger, 
    SelectValue 
} from '@/components/ui/select';
import { 
    Dialog, 
    DialogContent, 
    DialogDescription, 
    DialogHeader, 
    DialogTitle 
} from '@/components/ui/dialog';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { 
    Building2, 
    Users, 
    Calendar, 
    Stethoscope, 
    ChevronDown,
    Check,
    Plus,
    Settings
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

interface ClinicSelectorProps {
    currentClinic?: Clinic;
    onClinicChange?: (clinic: Clinic) => void;
    showCreateButton?: boolean;
    compact?: boolean;
}

export default function ClinicSelector({ 
    currentClinic, 
    onClinicChange, 
    showCreateButton = true,
    compact = false 
}: ClinicSelectorProps) {
    const [clinics, setClinics] = useState<Clinic[]>([]);
    const [loading, setLoading] = useState(true);
    const [switching, setSwitching] = useState(false);
    const [showSelector, setShowSelector] = useState(false);
    const [selectedClinicId, setSelectedClinicId] = useState<string>('');

    useEffect(() => {
        fetchClinics();
    }, []);

    const fetchClinics = async () => {
        try {
            setLoading(true);
            const response = await fetch('/clinics/list', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });

            if (response.ok) {
                const data = await response.json();
                console.log('Fetched clinics data:', data);
                setClinics(data.data.clinics || []);
                
                // Set current clinic if not provided
                if (!currentClinic && data.data.clinics.length > 0) {
                    const current = data.data.clinics.find((c: Clinic) => c.is_current);
                    if (current) {
                        console.log('Setting current clinic:', current);
                        onClinicChange?.(current);
                    }
                }
            } else {
                console.error('Failed to fetch clinics:', response.status, response.statusText);
                const errorData = await response.json().catch(() => ({}));
                console.error('Error data:', errorData);
            }
        } catch (error) {
            console.error('Error fetching clinics:', error);
        } finally {
            setLoading(false);
        }
    };

    const handleClinicSwitch = async (clinicId: string) => {
        if (!clinicId || clinicId === currentClinic?.id.toString()) {
            return;
        }

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
                const newClinic = clinics.find(c => c.id === parseInt(clinicId));
                if (newClinic) {
                    onClinicChange?.(newClinic);
                    // Reload the page to update all clinic-specific data
                    window.location.reload();
                }
            } else {
                const errorData = await response.json();
                console.error('Failed to switch clinic:', errorData.message);
            }
        } catch (error) {
            console.error('Error switching clinic:', error);
        } finally {
            setSwitching(false);
            setShowSelector(false);
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

    if (loading) {
        return (
            <div className="flex items-center space-x-2">
                <div className="h-4 w-4 animate-spin rounded-full border-2 border-gray-300 border-t-blue-600"></div>
                <span className="text-sm text-gray-600">Loading clinics...</span>
            </div>
        );
    }

    if (compact) {
        // Find current clinic from the clinics list if not provided
        const displayClinic = currentClinic || clinics.find(c => c.is_current);
        
        return (
            <div className="flex items-center space-x-2">
                <Select value={displayClinic?.id.toString() || ''} onValueChange={handleClinicSwitch}>
                    <SelectTrigger className="w-[200px]">
                        <SelectValue placeholder="Select clinic">
                            {displayClinic && (
                                <div className="flex items-center space-x-2">
                                    <Building2 className="h-4 w-4" />
                                    <span className="truncate">{displayClinic.name}</span>
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
                {showCreateButton && (
                    <Button size="sm" variant="outline" onClick={() => window.location.href = '/admin/clinics/create'}>
                        <Plus className="h-4 w-4" />
                    </Button>
                )}
            </div>
        );
    }

    // Find current clinic from the clinics list if not provided
    const displayClinic = currentClinic || clinics.find(c => c.is_current);

    return (
        <>
            <Button
                variant="outline"
                onClick={() => setShowSelector(true)}
                className="flex items-center space-x-2"
                disabled={switching}
            >
                <Building2 className="h-4 w-4" />
                <span>{displayClinic?.name || 'Select Clinic'}</span>
                <ChevronDown className="h-4 w-4" />
            </Button>

            <Dialog open={showSelector} onOpenChange={setShowSelector}>
                <DialogContent className="max-w-2xl">
                    <DialogHeader>
                        <DialogTitle>Select Clinic</DialogTitle>
                        <DialogDescription>
                            Choose the clinic you want to work with. You can switch between clinics at any time.
                        </DialogDescription>
                    </DialogHeader>

                    <div className="space-y-4">
                        {clinics.map((clinic) => (
                            <Card 
                                key={clinic.id} 
                                className={`cursor-pointer transition-all hover:shadow-md ${
                                    clinic.is_current ? 'ring-2 ring-blue-500 bg-blue-50 dark:bg-blue-900/20' : ''
                                }`}
                                onClick={() => handleClinicSwitch(clinic.id.toString())}
                            >
                                <CardHeader className="pb-3">
                                    <div className="flex items-center justify-between">
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
                                                <CardTitle className="text-lg">{clinic.name}</CardTitle>
                                                <CardDescription>{typeof clinic.address === 'string' ? clinic.address : 'Address not specified'}</CardDescription>
                                            </div>
                                        </div>
                                        <div className="flex items-center space-x-2">
                                            <Badge className={getRoleBadgeColor(clinic.user_role)}>
                                                {clinic.user_role}
                                            </Badge>
                                            {clinic.is_current && (
                                                <Badge variant="default" className="bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                                                    Current
                                                </Badge>
                                            )}
                                        </div>
                                    </div>
                                </CardHeader>
                                <CardContent className="pt-0">
                                    <div className="flex items-center space-x-6 text-sm text-gray-600 dark:text-gray-400">
                                        <div className="flex items-center space-x-1">
                                            <Stethoscope className="h-4 w-4" />
                                            <span>{clinic.statistics.total_doctors} Doctors</span>
                                        </div>
                                        <div className="flex items-center space-x-1">
                                            <Users className="h-4 w-4" />
                                            <span>{clinic.statistics.total_patients} Patients</span>
                                        </div>
                                        <div className="flex items-center space-x-1">
                                            <Calendar className="h-4 w-4" />
                                            <span>{clinic.statistics.total_appointments} Appointments</span>
                                        </div>
                                    </div>
                                    {clinic.phone && (
                                        <div className="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                            📞 {clinic.phone}
                                        </div>
                                    )}
                                </CardContent>
                            </Card>
                        ))}

                        {showCreateButton && (
                            <Card 
                                className="cursor-pointer transition-all hover:shadow-md border-dashed border-2 border-gray-300 dark:border-gray-600"
                                onClick={() => window.location.href = '/admin/clinics/create'}
                            >
                                <CardContent className="flex items-center justify-center py-8">
                                    <div className="text-center">
                                        <Plus className="h-8 w-8 mx-auto mb-2 text-gray-400" />
                                        <p className="text-gray-600 dark:text-gray-400">Create New Clinic</p>
                                    </div>
                                </CardContent>
                            </Card>
                        )}
                    </div>
                </DialogContent>
            </Dialog>
        </>
    );
}
