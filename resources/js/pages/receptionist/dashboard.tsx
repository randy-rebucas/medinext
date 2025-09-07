import { useState } from 'react';
import AppLayout from '@/layouts/app-layout';
import { receptionistDashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import {
    Search,
    UserPlus,
    FileText,
    Users,
    Clock,
    Calendar,
    AlertCircle,
    CheckCircle,
    Eye,
    TrendingUp,
    Activity,
    ArrowUpRight,
    Building2,
    Shield,
    ClipboardList
} from 'lucide-react';
import { Link } from '@inertiajs/react';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Receptionist Dashboard',
        href: receptionistDashboard(),
    },
];

interface Patient {
    id: number;
    name: string;
    patient_id: string;
    dob: string;
    sex: string;
    contact: {
        phone?: string;
        email?: string;
    };
    address?: string;
    emergency_contact?: string;
    allergies?: string[];
    last_visit?: string;
}

interface Encounter {
    id: number;
    patient_id: number;
    patient_name: string;
    encounter_number: string;
    visit_type: string;
    reason_for_visit: string;
    status: string;
    created_at: string;
    queue_position?: number;
}

interface QueueItem {
    id: number;
    encounter_id: number;
    patient_name: string;
    encounter_number: string;
    visit_type: string;
    reason_for_visit: string;
    queue_position: number;
    estimated_wait_time: string;
    status: string;
}

interface ReceptionistDashboardProps {
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
    stats?: {
        totalPatients: number;
        todayAppointments: number;
        activeQueue: number;
        completedEncounters: number;
    };
    activeQueue?: QueueItem[];
    recentEncounters?: Encounter[];
    permissions?: string[];
}

export default function ReceptionistDashboard({
    user,
    stats = {
        totalPatients: 0,
        todayAppointments: 0,
        activeQueue: 0,
        completedEncounters: 0
    },
    activeQueue = [],
    recentEncounters = [],
    permissions = []
}: ReceptionistDashboardProps) {
    const hasPermission = (permission: string) => permissions.includes(permission);
    const [searchQuery, setSearchQuery] = useState('');
    const [searchResults, setSearchResults] = useState<Patient[]>([]);
    const [selectedPatient, setSelectedPatient] = useState<Patient | null>(null);
    const [isSearching, setIsSearching] = useState(false);
    const [isPatientDialogOpen, setIsPatientDialogOpen] = useState(false);
    const [isEncounterDialogOpen, setIsEncounterDialogOpen] = useState(false);
    const [isNewPatientDialogOpen, setIsNewPatientDialogOpen] = useState(false);

    // Patient search function
    const handlePatientSearch = async () => {
        if (!searchQuery.trim()) return;

        setIsSearching(true);
        try {
            // This would call the API endpoint for patient search
            const response = await fetch(`/api/v1/patients/search?q=${encodeURIComponent(searchQuery)}`, {
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('token')}`,
                    'Content-Type': 'application/json',
                },
            });

            if (response.ok) {
                const data = await response.json();
                setSearchResults(data.data || []);
            } else {
                setSearchResults([]);
            }
        } catch (error) {
            console.error('Search error:', error);
            setSearchResults([]);
        } finally {
            setIsSearching(false);
        }
    };

    // Handle patient selection
    const handlePatientSelect = (patient: Patient) => {
        setSelectedPatient(patient);
        setIsPatientDialogOpen(true);
    };

    // Handle new encounter creation
    const handleCreateEncounter = () => {
        if (selectedPatient) {
            setIsEncounterDialogOpen(true);
        }
    };


    const getStatusColor = (status: string) => {
        switch (status.toLowerCase()) {
            case 'completed':
                return 'bg-green-100 text-green-800';
            case 'in-progress':
                return 'bg-blue-100 text-blue-800';
            case 'pending':
                return 'bg-yellow-100 text-yellow-800';
            case 'cancelled':
                return 'bg-red-100 text-red-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    };

    const getVisitTypeColor = (type: string) => {
        switch (type.toLowerCase()) {
            case 'emergency':
                return 'bg-red-100 text-red-800';
            case 'follow-up':
                return 'bg-blue-100 text-blue-800';
            case 'opd':
                return 'bg-green-100 text-green-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Receptionist Dashboard - Medinext">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&family=instrument-sans:400,500,600" rel="stylesheet" />
            </Head>
            <div className="min-h-screen bg-gradient-to-br from-slate-50 via-white to-blue-50 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900">
                <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
                    {/* Modern Header */}
                    <div className="relative overflow-hidden rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 p-8 text-white shadow-xl">
                        <div className="absolute inset-0 bg-black/10"></div>
                        <div className="relative flex items-center justify-between">
                            <div>
                                <h1 className="text-3xl font-bold tracking-tight">
                                    Welcome back, {user?.name || 'Receptionist'}
                                </h1>
                                <p className="mt-2 text-emerald-100">
                                    {user?.clinic?.name || 'No Clinic'} • Receptionist Dashboard
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

                    {/* Receptionist Stats */}
                    <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                        <Card className="relative overflow-hidden border-0 shadow-lg hover:shadow-xl transition-all duration-300 bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20">
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium text-slate-700 dark:text-slate-300">Total Patients</CardTitle>
                                <div className="p-2 bg-blue-500 rounded-lg">
                                    <Users className="h-4 w-4 text-white" />
                                </div>
                            </CardHeader>
                            <CardContent>
                                <div className="text-3xl font-bold text-slate-900 dark:text-white">{stats.totalPatients}</div>
                                <div className="flex items-center mt-2">
                                    <TrendingUp className="h-3 w-3 text-blue-500 mr-1" />
                                    <p className="text-xs text-slate-600 dark:text-slate-400">
                                        Registered patients
                                    </p>
                                </div>
                            </CardContent>
                        </Card>
                        <Card className="relative overflow-hidden border-0 shadow-lg hover:shadow-xl transition-all duration-300 bg-gradient-to-br from-emerald-50 to-emerald-100 dark:from-emerald-900/20 dark:to-emerald-800/20">
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium text-slate-700 dark:text-slate-300">Today's Appointments</CardTitle>
                                <div className="p-2 bg-emerald-500 rounded-lg">
                                    <Calendar className="h-4 w-4 text-white" />
                                </div>
                            </CardHeader>
                            <CardContent>
                                <div className="text-3xl font-bold text-slate-900 dark:text-white">{stats.todayAppointments}</div>
                                <div className="flex items-center mt-2">
                                    <Activity className="h-3 w-3 text-emerald-500 mr-1" />
                                    <p className="text-xs text-slate-600 dark:text-slate-400">
                                        Scheduled today
                                    </p>
                                </div>
                            </CardContent>
                        </Card>
                        <Card className="relative overflow-hidden border-0 shadow-lg hover:shadow-xl transition-all duration-300 bg-gradient-to-br from-orange-50 to-orange-100 dark:from-orange-900/20 dark:to-orange-800/20">
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium text-slate-700 dark:text-slate-300">Active Queue</CardTitle>
                                <div className="p-2 bg-orange-500 rounded-lg">
                                    <Clock className="h-4 w-4 text-white" />
                                </div>
                            </CardHeader>
                            <CardContent>
                                <div className="text-3xl font-bold text-slate-900 dark:text-white">{stats.activeQueue}</div>
                                <div className="flex items-center mt-2">
                                    <Activity className="h-3 w-3 text-orange-500 mr-1" />
                                    <p className="text-xs text-slate-600 dark:text-slate-400">
                                        Patients waiting
                                    </p>
                                </div>
                            </CardContent>
                        </Card>
                        <Card className="relative overflow-hidden border-0 shadow-lg hover:shadow-xl transition-all duration-300 bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20">
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium text-slate-700 dark:text-slate-300">Completed Today</CardTitle>
                                <div className="p-2 bg-green-500 rounded-lg">
                                    <CheckCircle className="h-4 w-4 text-white" />
                                </div>
                            </CardHeader>
                            <CardContent>
                                <div className="text-3xl font-bold text-slate-900 dark:text-white">{stats.completedEncounters}</div>
                                <div className="flex items-center mt-2">
                                    <CheckCircle className="h-3 w-3 text-green-500 mr-1" />
                                    <p className="text-xs text-slate-600 dark:text-slate-400">
                                        Encounters completed
                                    </p>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    {/* Quick Actions */}
                    <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                        <CardHeader>
                            <CardTitle className="text-xl font-semibold text-slate-900 dark:text-white">Receptionist Tools</CardTitle>
                            <CardDescription className="text-slate-600 dark:text-slate-300">Access your patient management and administrative tools</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="grid gap-4 md:grid-cols-3">
                                {hasPermission('register_patients') && (
                                    <Link href="/receptionist/register-patient">
                                        <Button variant="outline" className="w-full justify-start h-12 border-slate-200 dark:border-slate-700 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 hover:border-emerald-300 dark:hover:border-emerald-600 transition-all duration-200 group">
                                            <div className="p-1 bg-emerald-100 dark:bg-emerald-900 rounded-md mr-3 group-hover:bg-emerald-200 dark:group-hover:bg-emerald-800 transition-colors">
                                                <UserPlus className="h-4 w-4 text-emerald-600 dark:text-emerald-400" />
                                            </div>
                                            <span className="font-medium">Register Patient</span>
                                            <ArrowUpRight className="h-4 w-4 ml-auto text-slate-400 group-hover:text-emerald-500 transition-colors" />
                                        </Button>
                                    </Link>
                                )}
                                {hasPermission('view_queue') && (
                                    <Link href="/receptionist/queue">
                                        <Button variant="outline" className="w-full justify-start h-12 border-slate-200 dark:border-slate-700 hover:bg-orange-50 dark:hover:bg-orange-900/20 hover:border-orange-300 dark:hover:border-orange-600 transition-all duration-200 group">
                                            <div className="p-1 bg-orange-100 dark:bg-orange-900 rounded-md mr-3 group-hover:bg-orange-200 dark:group-hover:bg-orange-800 transition-colors">
                                                <Clock className="h-4 w-4 text-orange-600 dark:text-orange-400" />
                                            </div>
                                            <span className="font-medium">Patient Queue</span>
                                            <ArrowUpRight className="h-4 w-4 ml-auto text-slate-400 group-hover:text-orange-500 transition-colors" />
                                        </Button>
                                    </Link>
                                )}
                                {hasPermission('view_appointments') && (
                                    <Link href="/receptionist/appointments">
                                        <Button variant="outline" className="w-full justify-start h-12 border-slate-200 dark:border-slate-700 hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:border-blue-300 dark:hover:border-blue-600 transition-all duration-200 group">
                                            <div className="p-1 bg-blue-100 dark:bg-blue-900 rounded-md mr-3 group-hover:bg-blue-200 dark:group-hover:bg-blue-800 transition-colors">
                                                <Calendar className="h-4 w-4 text-blue-600 dark:text-blue-400" />
                                            </div>
                                            <span className="font-medium">Appointments</span>
                                            <ArrowUpRight className="h-4 w-4 ml-auto text-slate-400 group-hover:text-blue-500 transition-colors" />
                                        </Button>
                                    </Link>
                                )}
                                {hasPermission('view_patients') && (
                                    <Link href="/receptionist/patient-search">
                                        <Button variant="outline" className="w-full justify-start h-12 border-slate-200 dark:border-slate-700 hover:bg-purple-50 dark:hover:bg-purple-900/20 hover:border-purple-300 dark:hover:border-purple-600 transition-all duration-200 group">
                                            <div className="p-1 bg-purple-100 dark:bg-purple-900 rounded-md mr-3 group-hover:bg-purple-200 dark:group-hover:bg-purple-800 transition-colors">
                                                <Search className="h-4 w-4 text-purple-600 dark:text-purple-400" />
                                            </div>
                                            <span className="font-medium">Patient Search</span>
                                            <ArrowUpRight className="h-4 w-4 ml-auto text-slate-400 group-hover:text-purple-500 transition-colors" />
                                        </Button>
                                    </Link>
                                )}
                                {hasPermission('view_encounters') && (
                                    <Link href="/receptionist/encounters">
                                        <Button variant="outline" className="w-full justify-start h-12 border-slate-200 dark:border-slate-700 hover:bg-green-50 dark:hover:bg-green-900/20 hover:border-green-300 dark:hover:border-green-600 transition-all duration-200 group">
                                            <div className="p-1 bg-green-100 dark:bg-green-900 rounded-md mr-3 group-hover:bg-green-200 dark:group-hover:bg-green-800 transition-colors">
                                                <FileText className="h-4 w-4 text-green-600 dark:text-green-400" />
                                            </div>
                                            <span className="font-medium">Encounters</span>
                                            <ArrowUpRight className="h-4 w-4 ml-auto text-slate-400 group-hover:text-green-500 transition-colors" />
                                        </Button>
                                    </Link>
                                )}
                                {hasPermission('view_reports') && (
                                    <Link href="/receptionist/reports">
                                        <Button variant="outline" className="w-full justify-start h-12 border-slate-200 dark:border-slate-700 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 hover:border-indigo-300 dark:hover:border-indigo-600 transition-all duration-200 group">
                                            <div className="p-1 bg-indigo-100 dark:bg-indigo-900 rounded-md mr-3 group-hover:bg-indigo-200 dark:group-hover:bg-indigo-800 transition-colors">
                                                <ClipboardList className="h-4 w-4 text-indigo-600 dark:text-indigo-400" />
                                            </div>
                                            <span className="font-medium">Reports</span>
                                            <ArrowUpRight className="h-4 w-4 ml-auto text-slate-400 group-hover:text-indigo-500 transition-colors" />
                                        </Button>
                                    </Link>
                                )}
                            </div>
                        </CardContent>
                    </Card>

                {/* Main Content Tabs */}
                <Tabs defaultValue="search" className="space-y-4">
                    <TabsList className="bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm border border-slate-200 dark:border-slate-700">
                        <TabsTrigger value="search">Patient Search</TabsTrigger>
                        <TabsTrigger value="queue">Active Queue</TabsTrigger>
                        <TabsTrigger value="recent">Recent Encounters</TabsTrigger>
                    </TabsList>

                    {/* Patient Search Tab */}
                    <TabsContent value="search" className="space-y-4">
                        <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                            <CardHeader>
                                <CardTitle className="text-lg font-semibold text-slate-900 dark:text-white">Search for Patient</CardTitle>
                                <CardDescription className="text-slate-600 dark:text-slate-300">
                                    Search by patient name or patient ID to find existing patients
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="flex gap-2">
                                    <Input
                                        placeholder="Enter patient name or ID..."
                                        value={searchQuery}
                                        onChange={(e) => setSearchQuery(e.target.value)}
                                        onKeyPress={(e) => e.key === 'Enter' && handlePatientSearch()}
                                    />
                                    <Button onClick={handlePatientSearch} disabled={isSearching}>
                                        <Search className="h-4 w-4 mr-2" />
                                        {isSearching ? 'Searching...' : 'Search'}
                                    </Button>
                                </div>

                                {/* Search Results */}
                                {searchResults.length > 0 && (
                                    <div className="space-y-2">
                                        <h3 className="text-lg font-semibold">Search Results</h3>
                                        {searchResults.map((patient) => (
                                            <Card key={patient.id} className="p-4 border-slate-200 dark:border-slate-700 hover:shadow-md transition-all duration-200">
                                                <div className="flex items-center justify-between">
                                                    <div className="space-y-1">
                                                        <h4 className="font-semibold text-slate-900 dark:text-white">{patient.name}</h4>
                                                        <p className="text-sm text-slate-600 dark:text-slate-400">
                                                            ID: {patient.patient_id} | DOB: {patient.dob} | {patient.sex}
                                                        </p>
                                                        {patient.contact.phone && (
                                                            <p className="text-sm text-slate-600 dark:text-slate-400">
                                                                Phone: {patient.contact.phone}
                                                            </p>
                                                        )}
                                                    </div>
                                                    <Button
                                                        onClick={() => handlePatientSelect(patient)}
                                                        size="sm"
                                                        className="hover:bg-emerald-600 hover:border-emerald-600 transition-all duration-200"
                                                    >
                                                        <Eye className="h-4 w-4 mr-2" />
                                                        Select
                                                    </Button>
                                                </div>
                                            </Card>
                                        ))}
                                    </div>
                                )}

                                {searchResults.length === 0 && searchQuery && !isSearching && (
                                    <div className="text-center py-8">
                                        <div className="p-3 bg-orange-100 dark:bg-orange-900/20 rounded-full w-fit mx-auto mb-4">
                                            <AlertCircle className="h-8 w-8 text-orange-600 dark:text-orange-400" />
                                        </div>
                                        <h3 className="text-lg font-semibold text-slate-900 dark:text-white mb-2">Patient not found</h3>
                                        <p className="text-slate-600 dark:text-slate-400 mb-4">
                                            No patient found with the search criteria.
                                        </p>
                                        <Dialog open={isNewPatientDialogOpen} onOpenChange={setIsNewPatientDialogOpen}>
                                            <DialogTrigger asChild>
                                                <Button className="hover:bg-emerald-600 hover:border-emerald-600 transition-all duration-200">
                                                    <UserPlus className="h-4 w-4 mr-2" />
                                                    Register New Patient
                                                </Button>
                                            </DialogTrigger>
                                            <DialogContent className="max-w-2xl">
                                                <DialogHeader>
                                                    <DialogTitle>Register New Patient</DialogTitle>
                                                    <DialogDescription>
                                                        Enter the patient's information to create a new record
                                                    </DialogDescription>
                                                </DialogHeader>
                                                <NewPatientForm onSuccess={() => setIsNewPatientDialogOpen(false)} />
                                            </DialogContent>
                                        </Dialog>
                                    </div>
                                )}
                            </CardContent>
                        </Card>
                    </TabsContent>

                    {/* Active Queue Tab */}
                    <TabsContent value="queue" className="space-y-4">
                        <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                            <CardHeader>
                                <CardTitle className="text-lg font-semibold text-slate-900 dark:text-white">Active Queue</CardTitle>
                                <CardDescription className="text-slate-600 dark:text-slate-300">
                                    Current patients waiting to see the doctor
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                {activeQueue.length > 0 ? (
                                    <div className="space-y-4">
                                        {activeQueue.map((item) => (
                                            <Card key={item.id} className="p-4 border-slate-200 dark:border-slate-700 hover:shadow-md transition-all duration-200">
                                                <div className="flex items-center justify-between">
                                                    <div className="space-y-1">
                                                        <div className="flex items-center gap-2">
                                                            <Badge variant="outline" className="border-orange-300 dark:border-orange-700 text-orange-700 dark:text-orange-300">#{item.queue_position}</Badge>
                                                            <h4 className="font-semibold text-slate-900 dark:text-white">{item.patient_name}</h4>
                                                        </div>
                                                        <p className="text-sm text-slate-600 dark:text-slate-400">
                                                            Encounter: {item.encounter_number}
                                                        </p>
                                                        <div className="flex gap-2">
                                                            <Badge className={getVisitTypeColor(item.visit_type)}>
                                                                {item.visit_type}
                                                            </Badge>
                                                            <span className="text-sm text-slate-600 dark:text-slate-400">
                                                                Wait time: {item.estimated_wait_time}
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div className="text-right">
                                                        <p className="text-sm font-medium text-slate-900 dark:text-white">{item.reason_for_visit}</p>
                                                        <Badge className={getStatusColor(item.status)}>
                                                            {item.status}
                                                        </Badge>
                                                    </div>
                                                </div>
                                            </Card>
                                        ))}
                                    </div>
                                ) : (
                                    <div className="text-center py-8">
                                        <div className="p-3 bg-orange-100 dark:bg-orange-900/20 rounded-full w-fit mx-auto mb-4">
                                            <Clock className="h-8 w-8 text-orange-600 dark:text-orange-400" />
                                        </div>
                                        <h3 className="text-lg font-semibold text-slate-900 dark:text-white mb-2">No patients in queue</h3>
                                        <p className="text-slate-600 dark:text-slate-400">
                                            The queue is currently empty.
                                        </p>
                                    </div>
                                )}
                            </CardContent>
                        </Card>
                    </TabsContent>

                    {/* Recent Encounters Tab */}
                    <TabsContent value="recent" className="space-y-4">
                        <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                            <CardHeader>
                                <CardTitle className="text-lg font-semibold text-slate-900 dark:text-white">Recent Encounters</CardTitle>
                                <CardDescription className="text-slate-600 dark:text-slate-300">
                                    Latest patient encounters and their status
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                {recentEncounters.length > 0 ? (
                                    <div className="space-y-4">
                                        {recentEncounters.map((encounter) => (
                                            <Card key={encounter.id} className="p-4 border-slate-200 dark:border-slate-700 hover:shadow-md transition-all duration-200">
                                                <div className="flex items-center justify-between">
                                                    <div className="space-y-1">
                                                        <h4 className="font-semibold text-slate-900 dark:text-white">{encounter.patient_name}</h4>
                                                        <p className="text-sm text-slate-600 dark:text-slate-400">
                                                            Encounter: {encounter.encounter_number}
                                                        </p>
                                                        <div className="flex gap-2">
                                                            <Badge className={getVisitTypeColor(encounter.visit_type)}>
                                                                {encounter.visit_type}
                                                            </Badge>
                                                            <span className="text-sm text-slate-600 dark:text-slate-400">
                                                                {new Date(encounter.created_at).toLocaleString()}
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div className="text-right">
                                                        <p className="text-sm font-medium text-slate-900 dark:text-white">{encounter.reason_for_visit}</p>
                                                        <Badge className={getStatusColor(encounter.status)}>
                                                            {encounter.status}
                                                        </Badge>
                                                    </div>
                                                </div>
                                            </Card>
                                        ))}
                                    </div>
                                ) : (
                                    <div className="text-center py-8">
                                        <div className="p-3 bg-green-100 dark:bg-green-900/20 rounded-full w-fit mx-auto mb-4">
                                            <FileText className="h-8 w-8 text-green-600 dark:text-green-400" />
                                        </div>
                                        <h3 className="text-lg font-semibold text-slate-900 dark:text-white mb-2">No recent encounters</h3>
                                        <p className="text-slate-600 dark:text-slate-400">
                                            No encounters have been created today.
                                        </p>
                                    </div>
                                )}
                            </CardContent>
                        </Card>
                    </TabsContent>
                </Tabs>

                {/* Alerts and Notifications */}
                <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2 text-lg font-semibold text-slate-900 dark:text-white">
                            <div className="p-1 bg-orange-100 dark:bg-orange-900/20 rounded-md">
                                <AlertCircle className="h-5 w-5 text-orange-600 dark:text-orange-400" />
                            </div>
                            Important Alerts
                        </CardTitle>
                        <CardDescription className="text-slate-600 dark:text-slate-300">
                            Items requiring your attention
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-4">
                            {stats.activeQueue > 0 && (
                                <div className="flex items-center gap-4 p-4 bg-gradient-to-r from-orange-50 to-yellow-50 dark:from-orange-900/10 dark:to-yellow-900/10 border border-orange-200 dark:border-orange-800/30 rounded-xl hover:shadow-md transition-all duration-200">
                                    <div className="p-2 bg-orange-100 dark:bg-orange-900/20 rounded-lg">
                                        <Clock className="h-5 w-5 text-orange-600 dark:text-orange-400" />
                                    </div>
                                    <div className="flex-1">
                                        <p className="text-sm font-semibold text-orange-800 dark:text-orange-200">
                                            {stats.activeQueue} patient(s) waiting in queue
                                        </p>
                                        <p className="text-xs text-orange-600 dark:text-orange-400 mt-1">
                                            Monitor the queue and assist with patient flow
                                        </p>
                                    </div>
                                    <Button asChild size="sm" variant="outline" className="border-orange-300 dark:border-orange-700 hover:bg-orange-100 dark:hover:bg-orange-900/20 hover:border-orange-400 dark:hover:border-orange-600 transition-all duration-200">
                                        <Link href="/receptionist/queue">
                                            View Queue
                                        </Link>
                                    </Button>
                                </div>
                            )}

                            {stats.todayAppointments > 0 && (
                                <div className="flex items-center gap-4 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/10 dark:to-indigo-900/10 border border-blue-200 dark:border-blue-800/30 rounded-xl hover:shadow-md transition-all duration-200">
                                    <div className="p-2 bg-blue-100 dark:bg-blue-900/20 rounded-lg">
                                        <Calendar className="h-5 w-5 text-blue-600 dark:text-blue-400" />
                                    </div>
                                    <div className="flex-1">
                                        <p className="text-sm font-semibold text-blue-800 dark:text-blue-200">
                                            {stats.todayAppointments} appointment(s) scheduled for today
                                        </p>
                                        <p className="text-xs text-blue-600 dark:text-blue-400 mt-1">
                                            Review appointments and prepare for patient arrivals
                                        </p>
                                    </div>
                                    <Button asChild size="sm" variant="outline" className="border-blue-300 dark:border-blue-700 hover:bg-blue-100 dark:hover:bg-blue-900/20 hover:border-blue-400 dark:hover:border-blue-600 transition-all duration-200">
                                        <Link href="/receptionist/appointments?date=today">
                                            View Appointments
                                        </Link>
                                    </Button>
                                </div>
                            )}

                            {stats.activeQueue === 0 && stats.todayAppointments === 0 && (
                                <div className="text-center py-8">
                                    <div className="p-4 bg-green-100 dark:bg-green-900/20 rounded-full w-fit mx-auto mb-4">
                                        <CheckCircle className="h-12 w-12 text-green-600 dark:text-green-400" />
                                    </div>
                                    <h3 className="text-lg font-semibold text-slate-900 dark:text-white mb-2">All Caught Up!</h3>
                                    <p className="text-sm text-slate-600 dark:text-slate-400">No urgent items requiring your attention at this time.</p>
                                </div>
                            )}
                        </div>
                    </CardContent>
                </Card>

                {/* Patient Actions Dialog */}
                <Dialog open={isPatientDialogOpen} onOpenChange={setIsPatientDialogOpen}>
                    <DialogContent className="max-w-2xl">
                        <DialogHeader>
                            <DialogTitle>Patient Profile</DialogTitle>
                            <DialogDescription>
                                Patient information and available actions
                            </DialogDescription>
                        </DialogHeader>
                        {selectedPatient && (
                            <div className="space-y-4">
                                <div className="grid grid-cols-2 gap-4">
                                    <div>
                                        <Label className="text-sm font-medium">Name</Label>
                                        <p className="text-sm">{selectedPatient.name}</p>
                                    </div>
                                    <div>
                                        <Label className="text-sm font-medium">Patient ID</Label>
                                        <p className="text-sm">{selectedPatient.patient_id}</p>
                                    </div>
                                    <div>
                                        <Label className="text-sm font-medium">Date of Birth</Label>
                                        <p className="text-sm">{selectedPatient.dob}</p>
                                    </div>
                                    <div>
                                        <Label className="text-sm font-medium">Sex</Label>
                                        <p className="text-sm">{selectedPatient.sex}</p>
                                    </div>
                                    {selectedPatient.contact.phone && (
                                        <div>
                                            <Label className="text-sm font-medium">Phone</Label>
                                            <p className="text-sm">{selectedPatient.contact.phone}</p>
                                        </div>
                                    )}
                                    {selectedPatient.contact.email && (
                                        <div>
                                            <Label className="text-sm font-medium">Email</Label>
                                            <p className="text-sm">{selectedPatient.contact.email}</p>
                                        </div>
                                    )}
                                </div>

                                <div className="flex gap-2 pt-4">
                                    <Button onClick={handleCreateEncounter}>
                                        <FileText className="h-4 w-4 mr-2" />
                                        Add New Encounter
                                    </Button>
                                    <Button variant="outline">
                                        <Eye className="h-4 w-4 mr-2" />
                                        View History
                                    </Button>
                                </div>
                            </div>
                        )}
                    </DialogContent>
                </Dialog>

                {/* New Encounter Dialog */}
                <Dialog open={isEncounterDialogOpen} onOpenChange={setIsEncounterDialogOpen}>
                    <DialogContent className="max-w-2xl">
                        <DialogHeader>
                            <DialogTitle>Create New Encounter</DialogTitle>
                            <DialogDescription>
                                Create a new encounter for {selectedPatient?.name}
                            </DialogDescription>
                        </DialogHeader>
                        <NewEncounterForm
                            patient={selectedPatient}
                            onSuccess={() => {
                                setIsEncounterDialogOpen(false);
                                setIsPatientDialogOpen(false);
                            }}
                        />
                    </DialogContent>
                </Dialog>
                </div>
            </div>
        </AppLayout>
    );
}

// New Patient Registration Form Component
function NewPatientForm({ onSuccess }: { onSuccess: () => void }) {
    const [formData, setFormData] = useState({
        name: '',
        dob: '',
        sex: '',
        phone: '',
        email: '',
        address: '',
        emergency_contact: '',
        allergies: ''
    });
    const [isSubmitting, setIsSubmitting] = useState(false);

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setIsSubmitting(true);

        try {
            const response = await fetch('/api/v1/patients', {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('token')}`,
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData),
            });

            if (response.ok) {
                onSuccess();
                // Reset form
                setFormData({
                    name: '',
                    dob: '',
                    sex: '',
                    phone: '',
                    email: '',
                    address: '',
                    emergency_contact: '',
                    allergies: ''
                });
            }
        } catch (error) {
            console.error('Error creating patient:', error);
        } finally {
            setIsSubmitting(false);
        }
    };

    return (
        <form onSubmit={handleSubmit} className="space-y-4">
            <div className="grid grid-cols-2 gap-4">
                <div>
                    <Label htmlFor="name">Full Name *</Label>
                    <Input
                        id="name"
                        value={formData.name}
                        onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                        required
                    />
                </div>
                <div>
                    <Label htmlFor="dob">Date of Birth *</Label>
                    <Input
                        id="dob"
                        type="date"
                        value={formData.dob}
                        onChange={(e) => setFormData({ ...formData, dob: e.target.value })}
                        required
                    />
                </div>
                <div>
                    <Label htmlFor="sex">Sex *</Label>
                    <Select value={formData.sex} onValueChange={(value) => setFormData({ ...formData, sex: value })}>
                        <SelectTrigger>
                            <SelectValue placeholder="Select sex" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="male">Male</SelectItem>
                            <SelectItem value="female">Female</SelectItem>
                            <SelectItem value="other">Other</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                <div>
                    <Label htmlFor="phone">Phone Number</Label>
                    <Input
                        id="phone"
                        value={formData.phone}
                        onChange={(e) => setFormData({ ...formData, phone: e.target.value })}
                    />
                </div>
                <div>
                    <Label htmlFor="email">Email</Label>
                    <Input
                        id="email"
                        type="email"
                        value={formData.email}
                        onChange={(e) => setFormData({ ...formData, email: e.target.value })}
                    />
                </div>
                <div>
                    <Label htmlFor="emergency_contact">Emergency Contact</Label>
                    <Input
                        id="emergency_contact"
                        value={formData.emergency_contact}
                        onChange={(e) => setFormData({ ...formData, emergency_contact: e.target.value })}
                    />
                </div>
            </div>
            <div>
                <Label htmlFor="address">Address</Label>
                <Textarea
                    id="address"
                    value={formData.address}
                    onChange={(e) => setFormData({ ...formData, address: e.target.value })}
                />
            </div>
            <div>
                <Label htmlFor="allergies">Known Allergies</Label>
                <Textarea
                    id="allergies"
                    value={formData.allergies}
                    onChange={(e) => setFormData({ ...formData, allergies: e.target.value })}
                    placeholder="List any known allergies..."
                />
            </div>
            <div className="flex justify-end gap-2">
                <Button type="button" variant="outline" onClick={onSuccess}>
                    Cancel
                </Button>
                <Button type="submit" disabled={isSubmitting}>
                    {isSubmitting ? 'Creating...' : 'Create Patient'}
                </Button>
            </div>
        </form>
    );
}

// New Encounter Form Component
function NewEncounterForm({
    patient,
    onSuccess
}: {
    patient: Patient | null;
    onSuccess: () => void;
}) {
    const [formData, setFormData] = useState({
        visit_type: '',
        reason_for_visit: '',
        notes: ''
    });
    const [isSubmitting, setIsSubmitting] = useState(false);

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        if (!patient) return;

        setIsSubmitting(true);

        try {
            const response = await fetch('/api/v1/encounters', {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('token')}`,
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    patient_id: patient.id,
                    ...formData
                }),
            });

            if (response.ok) {
                onSuccess();
                // Optionally add to queue immediately
                // await handleAddToQueue(encounter.data.id);
            }
        } catch (error) {
            console.error('Error creating encounter:', error);
        } finally {
            setIsSubmitting(false);
        }
    };

    return (
        <form onSubmit={handleSubmit} className="space-y-4">
            <div>
                <Label htmlFor="visit_type">Visit Type *</Label>
                <Select value={formData.visit_type} onValueChange={(value) => setFormData({ ...formData, visit_type: value })}>
                    <SelectTrigger>
                        <SelectValue placeholder="Select visit type" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="opd">OPD (Outpatient Department)</SelectItem>
                        <SelectItem value="emergency">Emergency</SelectItem>
                        <SelectItem value="follow-up">Follow-up</SelectItem>
                        <SelectItem value="consultation">Consultation</SelectItem>
                    </SelectContent>
                </Select>
            </div>
            <div>
                <Label htmlFor="reason_for_visit">Reason for Visit *</Label>
                <Textarea
                    id="reason_for_visit"
                    value={formData.reason_for_visit}
                    onChange={(e) => setFormData({ ...formData, reason_for_visit: e.target.value })}
                    placeholder="Describe the reason for the visit..."
                    required
                />
            </div>
            <div>
                <Label htmlFor="notes">Additional Notes</Label>
                <Textarea
                    id="notes"
                    value={formData.notes}
                    onChange={(e) => setFormData({ ...formData, notes: e.target.value })}
                    placeholder="Any additional information..."
                />
            </div>
            <div className="flex justify-end gap-2">
                <Button type="button" variant="outline" onClick={onSuccess}>
                    Cancel
                </Button>
                <Button type="submit" disabled={isSubmitting}>
                    {isSubmitting ? 'Creating...' : 'Create Encounter'}
                </Button>
            </div>
        </form>
    );
}
