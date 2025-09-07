import { useState } from 'react';
import AppLayout from '@/layouts/app-layout';
import { receptionistAppointments } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import {
    Plus,
    Search,
    Eye,
    Filter,
    Clock,
    Stethoscope,
    CheckCircle,
    XCircle,
    Calendar,
    User,
    Building2,
    Shield
} from 'lucide-react';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Receptionist Dashboard',
        href: '/receptionist/dashboard',
    },
    {
        title: 'Appointments',
        href: receptionistAppointments(),
    },
];

interface Appointment {
    id: number;
    patient_name: string;
    patient_id: string;
    doctor_name: string;
    start_at: string;
    end_at: string;
    status: string;
    type: string;
    reason: string;
    room_name?: string;
    priority: string;
    notes?: string;
    phone?: string;
    email?: string;
}

interface ReceptionistAppointmentsProps {
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
    appointments?: Appointment[];
    patients?: Array<{ id: number; name: string }>;
    doctors?: Array<{ id: number; name: string }>;
    rooms?: Array<{ id: number; name: string }>;
    filters?: {
        status: string;
        type: string;
        date: string;
        doctor_id: string;
    };
    permissions?: string[];
}

export default function ReceptionistAppointments({
    user,
    appointments = [],
    patients = [],
    doctors = [],
    rooms = [],
    filters = {
        status: '',
        type: '',
        date: '',
        doctor_id: ''
    },
    permissions = []
}: ReceptionistAppointmentsProps) {
    const hasPermission = (permission: string) => permissions.includes(permission);
    const [searchTerm, setSearchTerm] = useState('');
    const [statusFilter, setStatusFilter] = useState(filters.status || '');
    const [typeFilter, setTypeFilter] = useState(filters.type || '');
    const [dateFilter, setDateFilter] = useState(filters.date || '');
    const [doctorFilter, setDoctorFilter] = useState(filters.doctor_id || '');
    const [activeTab, setActiveTab] = useState('today');
    const [isCreateDialogOpen, setIsCreateDialogOpen] = useState(false);
    const [isEditDialogOpen, setIsEditDialogOpen] = useState(false);
    const [selectedAppointment, setSelectedAppointment] = useState<Appointment | null>(null);

    const filteredAppointments = appointments.filter(appointment => {
        const matchesSearch = appointment.patient_name.toLowerCase().includes(searchTerm.toLowerCase()) ||
                            appointment.doctor_name.toLowerCase().includes(searchTerm.toLowerCase()) ||
                            appointment.reason.toLowerCase().includes(searchTerm.toLowerCase());
        const matchesStatus = !statusFilter || statusFilter === 'all' || appointment.status === statusFilter;
        const matchesType = !typeFilter || typeFilter === 'all' || appointment.type === typeFilter;
        const matchesDate = !dateFilter || appointment.start_at.startsWith(dateFilter);
        const matchesDoctor = !doctorFilter || doctorFilter === 'all' || appointment.doctor_name.includes(doctorFilter);

        return matchesSearch && matchesStatus && matchesType && matchesDate && matchesDoctor;
    });

    const getTabCounts = () => {
        return {
            today: filteredAppointments.filter(a => a.start_at.startsWith(new Date().toISOString().split('T')[0])).length,
            upcoming: filteredAppointments.filter(a => a.start_at > new Date().toISOString()).length,
            completed: filteredAppointments.filter(a => a.status === 'completed').length,
            cancelled: filteredAppointments.filter(a => a.status === 'cancelled').length
        };
    };

    const tabCounts = getTabCounts();

    const handleStatusChange = (appointmentId: number, newStatus: string) => {
        // Handle status change via API
        console.log('Status change:', appointmentId, newStatus);
    };

    const handleDelete = (appointmentId: number) => {
        if (confirm('Are you sure you want to delete this appointment?')) {
            // Handle deletion via API
            console.log('Delete appointment:', appointmentId);
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Manage Appointments - Medinext">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&family=instrument-sans:400,500,600" rel="stylesheet" />
            </Head>
            <div className="min-h-screen bg-gradient-to-br from-slate-50 via-white to-blue-50 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900">
                <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
                    {/* Modern Header */}
                    <div className="relative overflow-hidden rounded-2xl bg-gradient-to-r from-blue-600 to-purple-600 p-8 text-white shadow-xl">
                        <div className="absolute inset-0 bg-black/10"></div>
                        <div className="relative flex items-center justify-between">
                    <div>
                                <h1 className="text-3xl font-bold tracking-tight">Manage Appointments</h1>
                                <p className="mt-2 text-blue-100">
                                    {user?.clinic?.name || 'No Clinic'} • Schedule and manage patient appointments
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
                                {hasPermission('create_appointments') && (
                                    <Dialog open={isCreateDialogOpen} onOpenChange={setIsCreateDialogOpen}>
                                        <DialogTrigger asChild>
                                            <Button className="bg-white/20 hover:bg-white/30 text-white border-white/30 hover:border-white/40">
                                                <Plus className="h-4 w-4 mr-2" />
                        Schedule Appointment
                    </Button>
                                        </DialogTrigger>
                                        <DialogContent className="max-w-2xl">
                                            <DialogHeader>
                                                <DialogTitle>Schedule New Appointment</DialogTitle>
                                                <DialogDescription>
                                                    Create a new appointment for a patient
                                                </DialogDescription>
                                            </DialogHeader>
                                            <CreateAppointmentForm
                                                patients={patients}
                                                doctors={doctors}
                                                rooms={rooms}
                                                onSuccess={() => setIsCreateDialogOpen(false)}
                                            />
                                        </DialogContent>
                                    </Dialog>
                                )}
                            </div>
                        </div>
                        {/* Decorative elements */}
                        <div className="absolute -top-4 -right-4 w-24 h-24 bg-white/10 rounded-full"></div>
                        <div className="absolute -bottom-2 -left-2 w-16 h-16 bg-white/5 rounded-full"></div>
                </div>

                    {/* Filters */}
                    <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                    <CardHeader>
                            <CardTitle className="flex items-center gap-2 text-lg font-semibold text-slate-900 dark:text-white">
                                <div className="p-1 bg-blue-100 dark:bg-blue-900/20 rounded-md">
                                    <Filter className="h-5 w-5 text-blue-600 dark:text-blue-400" />
                                </div>
                                Filters & Search
                            </CardTitle>
                            <CardDescription className="text-slate-600 dark:text-slate-300">
                                Filter and search through appointments
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                            <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-6">
                                <div className="space-y-2">
                                    <Label htmlFor="search" className="text-sm font-medium text-slate-700 dark:text-slate-300">Search</Label>
                                    <div className="relative">
                                        <Search className="absolute left-3 top-3 h-4 w-4 text-slate-400" />
                                        <Input
                                            id="search"
                                            placeholder="Search patients, doctors, or reason..."
                                            value={searchTerm}
                                            onChange={(e) => setSearchTerm(e.target.value)}
                                            className="pl-10 border-slate-200 dark:border-slate-700 focus:border-blue-500 dark:focus:border-blue-400"
                                        />
                                    </div>
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="status" className="text-sm font-medium text-slate-700 dark:text-slate-300">Status</Label>
                                    <Select value={statusFilter} onValueChange={setStatusFilter}>
                                        <SelectTrigger className="border-slate-200 dark:border-slate-700 focus:border-blue-500 dark:focus:border-blue-400">
                                            <SelectValue placeholder="All statuses" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="all">All statuses</SelectItem>
                                            <SelectItem value="scheduled">Scheduled</SelectItem>
                                            <SelectItem value="confirmed">Confirmed</SelectItem>
                                            <SelectItem value="in_progress">In Progress</SelectItem>
                                            <SelectItem value="completed">Completed</SelectItem>
                                            <SelectItem value="cancelled">Cancelled</SelectItem>
                                            <SelectItem value="no_show">No Show</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="type" className="text-sm font-medium text-slate-700 dark:text-slate-300">Type</Label>
                                    <Select value={typeFilter} onValueChange={setTypeFilter}>
                                        <SelectTrigger className="border-slate-200 dark:border-slate-700 focus:border-blue-500 dark:focus:border-blue-400">
                                            <SelectValue placeholder="All types" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="all">All types</SelectItem>
                                            <SelectItem value="consultation">Consultation</SelectItem>
                                            <SelectItem value="follow_up">Follow-up</SelectItem>
                                            <SelectItem value="emergency">Emergency</SelectItem>
                                            <SelectItem value="routine_checkup">Routine Checkup</SelectItem>
                                            <SelectItem value="specialist_consultation">Specialist Consultation</SelectItem>
                                            <SelectItem value="procedure">Procedure</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="doctor" className="text-sm font-medium text-slate-700 dark:text-slate-300">Doctor</Label>
                                    <Select value={doctorFilter} onValueChange={setDoctorFilter}>
                                        <SelectTrigger className="border-slate-200 dark:border-slate-700 focus:border-blue-500 dark:focus:border-blue-400">
                                            <SelectValue placeholder="All doctors" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="all">All doctors</SelectItem>
                                            {doctors.map((doctor) => (
                                                <SelectItem key={doctor.id} value={doctor.name}>
                                                    {doctor.name}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="date" className="text-sm font-medium text-slate-700 dark:text-slate-300">Date</Label>
                                    <Input
                                        id="date"
                                        type="date"
                                        value={dateFilter}
                                        onChange={(e) => setDateFilter(e.target.value)}
                                        className="border-slate-200 dark:border-slate-700 focus:border-blue-500 dark:focus:border-blue-400"
                                    />
                                </div>
                                <div className="space-y-2">
                                    <Label className="text-sm font-medium text-slate-700 dark:text-slate-300">&nbsp;</Label>
                                    <Button
                                        variant="outline"
                                        onClick={() => {
                                            setSearchTerm('');
                                            setStatusFilter('');
                                            setTypeFilter('');
                                            setDateFilter('');
                                            setDoctorFilter('');
                                        }}
                                        className="w-full border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 hover:border-slate-300 dark:hover:border-slate-600 transition-all duration-200"
                                    >
                                        Clear Filters
                                    </Button>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Tabs */}
                    <Tabs value={activeTab} onValueChange={setActiveTab} className="w-full">
                        <TabsList className="grid w-full grid-cols-4">
                            <TabsTrigger value="today" className="flex items-center gap-2">
                                <Calendar className="h-4 w-4" />
                                Today ({tabCounts.today})
                            </TabsTrigger>
                            <TabsTrigger value="upcoming" className="flex items-center gap-2">
                                <Clock className="h-4 w-4" />
                                Upcoming ({tabCounts.upcoming})
                            </TabsTrigger>
                            <TabsTrigger value="completed" className="flex items-center gap-2">
                                <CheckCircle className="h-4 w-4" />
                                Completed ({tabCounts.completed})
                            </TabsTrigger>
                            <TabsTrigger value="cancelled" className="flex items-center gap-2">
                                <XCircle className="h-4 w-4" />
                                Cancelled ({tabCounts.cancelled})
                            </TabsTrigger>
                        </TabsList>

                        <TabsContent value="today" className="space-y-4">
                            <AppointmentsList
                                appointments={filteredAppointments.filter(a => a.start_at.startsWith(new Date().toISOString().split('T')[0]))}
                                onStatusChange={handleStatusChange}
                                onEdit={(appointment) => {
                                    setSelectedAppointment(appointment);
                                    setIsEditDialogOpen(true);
                                }}
                                onDelete={handleDelete}
                            />
                        </TabsContent>

                        <TabsContent value="upcoming" className="space-y-4">
                            <AppointmentsList
                                appointments={filteredAppointments.filter(a => a.start_at > new Date().toISOString())}
                                onStatusChange={handleStatusChange}
                                onEdit={(appointment) => {
                                    setSelectedAppointment(appointment);
                                    setIsEditDialogOpen(true);
                                }}
                                onDelete={handleDelete}
                            />
                        </TabsContent>

                        <TabsContent value="completed" className="space-y-4">
                            <AppointmentsList
                                appointments={filteredAppointments.filter(a => a.status === 'completed')}
                                onStatusChange={handleStatusChange}
                                onEdit={(appointment) => {
                                    setSelectedAppointment(appointment);
                                    setIsEditDialogOpen(true);
                                }}
                                onDelete={handleDelete}
                            />
                        </TabsContent>

                        <TabsContent value="cancelled" className="space-y-4">
                            <AppointmentsList
                                appointments={filteredAppointments.filter(a => a.status === 'cancelled')}
                                onStatusChange={handleStatusChange}
                                onEdit={(appointment) => {
                                    setSelectedAppointment(appointment);
                                    setIsEditDialogOpen(true);
                                }}
                                onDelete={handleDelete}
                            />
                        </TabsContent>
                    </Tabs>

                    {/* Edit Appointment Dialog */}
                    <Dialog open={isEditDialogOpen} onOpenChange={setIsEditDialogOpen}>
                        <DialogContent className="max-w-2xl">
                            <DialogHeader>
                                <DialogTitle>Edit Appointment</DialogTitle>
                                <DialogDescription>
                                    Update appointment details
                                </DialogDescription>
                            </DialogHeader>
                            {selectedAppointment && (
                                <EditAppointmentForm
                                    appointment={selectedAppointment}
                                    patients={patients}
                                    doctors={doctors}
                                    rooms={rooms}
                                    onSuccess={() => {
                                        setIsEditDialogOpen(false);
                                        setSelectedAppointment(null);
                                    }}
                                />
                            )}
                        </DialogContent>
                    </Dialog>
                </div>
                        </div>
        </AppLayout>
    );
}

// Appointments List Component
function AppointmentsList({
    appointments,
    onStatusChange,
    onEdit,
    onDelete
}: {
    appointments: Appointment[];
    onStatusChange: (id: number, status: string) => void;
    onEdit: (appointment: Appointment) => void;
    onDelete: (id: number) => void;
}) {
    const getStatusColor = (status: string) => {
        switch (status.toLowerCase()) {
            case 'scheduled':
            case 'confirmed':
                return 'bg-blue-100 text-blue-800';
            case 'in_progress':
            case 'checked_in':
                return 'bg-yellow-100 text-yellow-800';
            case 'completed':
                return 'bg-green-100 text-green-800';
            case 'cancelled':
                return 'bg-red-100 text-red-800';
            case 'no_show':
                return 'bg-gray-100 text-gray-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    };

    const getPriorityColor = (priority: string) => {
        switch (priority.toLowerCase()) {
            case 'urgent':
            case 'emergency':
                return 'bg-red-100 text-red-800';
            case 'high':
                return 'bg-orange-100 text-orange-800';
            case 'normal':
                return 'bg-blue-100 text-blue-800';
            case 'low':
                return 'bg-gray-100 text-gray-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    };

    if (appointments.length === 0) {
        return (
            <Card>
                <CardContent className="text-center py-12">
                    <Calendar className="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                    <h3 className="text-lg font-semibold mb-2">No appointments found</h3>
                    <p className="text-muted-foreground">
                        No appointments match your current filters.
                    </p>
                </CardContent>
            </Card>
        );
    }

    return (
        <Card>
            <CardContent className="p-0">
                <div className="space-y-0">
                                {appointments.map((appointment) => (
                        <div key={appointment.id} className="border-b p-4 hover:bg-muted/50 transition-colors last:border-b-0">
                            <div className="flex items-center justify-between">
                                <div className="space-y-2 flex-1">
                                    <div className="flex items-center gap-3">
                                        <h3 className="font-semibold">{appointment.patient_name}</h3>
                                        <Badge className={getStatusColor(appointment.status)}>
                                            {appointment.status.replace('_', ' ')}
                                        </Badge>
                                        <Badge variant="outline" className={getPriorityColor(appointment.priority)}>
                                            {appointment.priority}
                                        </Badge>
                                    </div>
                                    <div className="flex items-center gap-4 text-sm text-muted-foreground">
                                        <div className="flex items-center gap-1">
                                            <Stethoscope className="h-4 w-4" />
                                            {appointment.doctor_name}
                                        </div>
                                        <div className="flex items-center gap-1">
                                            <Clock className="h-4 w-4" />
                                            {new Date(appointment.start_at).toLocaleDateString()} at{' '}
                                            {new Date(appointment.start_at).toLocaleTimeString([], {
                                                hour: '2-digit',
                                                minute: '2-digit'
                                            })}
                                            </div>
                                        {appointment.room_name && (
                                            <div className="flex items-center gap-1">
                                                <User className="h-4 w-4" />
                                                {appointment.room_name}
                                            </div>
                                        )}
                                                </div>
                                    <p className="text-sm text-muted-foreground">
                                        <strong>Type:</strong> {appointment.type.replace('_', ' ')} | <strong>Reason:</strong> {appointment.reason}
                                    </p>
                                            </div>
                                <div className="flex items-center gap-2">
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        onClick={() => onEdit(appointment)}
                                    >
                                                    <Eye className="h-4 w-4" />
                                                </Button>
                                    <Select
                                        value={appointment.status}
                                        onValueChange={(value) => onStatusChange(appointment.id, value)}
                                    >
                                        <SelectTrigger className="w-32">
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="scheduled">Scheduled</SelectItem>
                                            <SelectItem value="confirmed">Confirmed</SelectItem>
                                            <SelectItem value="in_progress">In Progress</SelectItem>
                                            <SelectItem value="completed">Completed</SelectItem>
                                            <SelectItem value="cancelled">Cancelled</SelectItem>
                                            <SelectItem value="no_show">No Show</SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        onClick={() => onDelete(appointment.id)}
                                    >
                                                    <XCircle className="h-4 w-4" />
                                                </Button>
                                            </div>
                            </div>
                        </div>
                                ))}
                </div>
                    </CardContent>
                </Card>
    );
}

// Create Appointment Form Component
function CreateAppointmentForm({ patients, doctors, rooms, onSuccess }: {
    patients: Array<{ id: number; name: string }>;
    doctors: Array<{ id: number; name: string }>;
    rooms: Array<{ id: number; name: string }>;
    onSuccess: () => void;
}) {
    const [formData, setFormData] = useState({
        patient_id: '',
        doctor_id: '',
        start_at: '',
        duration: '30',
        type: 'consultation',
        reason: '',
        room_id: '',
        priority: 'normal',
        notes: ''
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        // Handle form submission via API
        console.log('Create appointment:', formData);
        onSuccess();
    };

    return (
        <form onSubmit={handleSubmit} className="space-y-4">
            <div className="grid gap-4 md:grid-cols-2">
                <div className="space-y-2">
                    <Label htmlFor="patient">Patient *</Label>
                    <Select value={formData.patient_id} onValueChange={(value) => setFormData({...formData, patient_id: value})}>
                        <SelectTrigger>
                            <SelectValue placeholder="Select patient" />
                        </SelectTrigger>
                        <SelectContent>
                            {patients.map((patient) => (
                                <SelectItem key={patient.id} value={patient.id.toString()}>
                                    {patient.name}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                </div>
                <div className="space-y-2">
                    <Label htmlFor="doctor">Doctor *</Label>
                    <Select value={formData.doctor_id} onValueChange={(value) => setFormData({...formData, doctor_id: value})}>
                        <SelectTrigger>
                            <SelectValue placeholder="Select doctor" />
                        </SelectTrigger>
                        <SelectContent>
                            {doctors.map((doctor) => (
                                <SelectItem key={doctor.id} value={doctor.id.toString()}>
                                    {doctor.name}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                </div>
            </div>
            <div className="grid gap-4 md:grid-cols-2">
                <div className="space-y-2">
                    <Label htmlFor="start_at">Date & Time *</Label>
                    <Input
                        id="start_at"
                        type="datetime-local"
                        value={formData.start_at}
                        onChange={(e) => setFormData({...formData, start_at: e.target.value})}
                        required
                    />
                </div>
                <div className="space-y-2">
                    <Label htmlFor="duration">Duration (minutes)</Label>
                    <Select value={formData.duration} onValueChange={(value) => setFormData({...formData, duration: value})}>
                        <SelectTrigger>
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="15">15 minutes</SelectItem>
                            <SelectItem value="30">30 minutes</SelectItem>
                            <SelectItem value="45">45 minutes</SelectItem>
                            <SelectItem value="60">60 minutes</SelectItem>
                            <SelectItem value="90">90 minutes</SelectItem>
                            <SelectItem value="120">120 minutes</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </div>
            <div className="grid gap-4 md:grid-cols-2">
                <div className="space-y-2">
                    <Label htmlFor="type">Appointment Type *</Label>
                    <Select value={formData.type} onValueChange={(value) => setFormData({...formData, type: value})}>
                        <SelectTrigger>
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="consultation">Consultation</SelectItem>
                            <SelectItem value="follow_up">Follow-up</SelectItem>
                            <SelectItem value="emergency">Emergency</SelectItem>
                            <SelectItem value="routine_checkup">Routine Checkup</SelectItem>
                            <SelectItem value="specialist_consultation">Specialist Consultation</SelectItem>
                            <SelectItem value="procedure">Procedure</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                <div className="space-y-2">
                    <Label htmlFor="room">Room</Label>
                    <Select value={formData.room_id} onValueChange={(value) => setFormData({...formData, room_id: value})}>
                        <SelectTrigger>
                            <SelectValue placeholder="Select room" />
                        </SelectTrigger>
                        <SelectContent>
                            {rooms.map((room) => (
                                <SelectItem key={room.id} value={room.id.toString()}>
                                    {room.name}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                </div>
            </div>
            <div className="space-y-2">
                <Label htmlFor="reason">Reason for Visit *</Label>
                <Input
                    id="reason"
                    value={formData.reason}
                    onChange={(e) => setFormData({...formData, reason: e.target.value})}
                    placeholder="Brief description of the visit reason"
                    required
                />
            </div>
            <div className="space-y-2">
                <Label htmlFor="priority">Priority</Label>
                <Select value={formData.priority} onValueChange={(value) => setFormData({...formData, priority: value})}>
                    <SelectTrigger>
                        <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="low">Low</SelectItem>
                        <SelectItem value="normal">Normal</SelectItem>
                        <SelectItem value="high">High</SelectItem>
                        <SelectItem value="urgent">Urgent</SelectItem>
                        <SelectItem value="emergency">Emergency</SelectItem>
                    </SelectContent>
                </Select>
            </div>
            <div className="space-y-2">
                <Label htmlFor="notes">Notes</Label>
                <Textarea
                    id="notes"
                    value={formData.notes}
                    onChange={(e) => setFormData({...formData, notes: e.target.value})}
                    placeholder="Additional notes or special instructions"
                    rows={3}
                />
            </div>
            <div className="flex justify-end gap-2">
                <Button type="button" variant="outline" onClick={onSuccess}>
                    Cancel
                </Button>
                <Button type="submit">
                    Schedule Appointment
                </Button>
            </div>
        </form>
    );
}

// Edit Appointment Form Component
function EditAppointmentForm({ appointment, patients, onSuccess }: {
    appointment: Appointment;
    patients: Array<{ id: number; name: string }>;
    doctors: Array<{ id: number; name: string }>;
    rooms: Array<{ id: number; name: string }>;
    onSuccess: () => void;
}) {
    const [formData, setFormData] = useState({
        patient_id: appointment.patient_id,
        doctor_id: appointment.doctor_name,
        start_at: appointment.start_at.slice(0, 16),
        duration: '30',
        type: appointment.type,
        reason: appointment.reason,
        room_id: appointment.room_name || '',
        priority: appointment.priority,
        notes: appointment.notes || '',
        status: appointment.status
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        // Handle form submission via API
        console.log('Update appointment:', appointment.id, formData);
        onSuccess();
    };

    return (
        <form onSubmit={handleSubmit} className="space-y-4">
            <div className="grid gap-4 md:grid-cols-2">
                <div className="space-y-2">
                    <Label htmlFor="patient">Patient *</Label>
                    <Select value={formData.patient_id} onValueChange={(value) => setFormData({...formData, patient_id: value})}>
                        <SelectTrigger>
                            <SelectValue placeholder="Select patient" />
                        </SelectTrigger>
                        <SelectContent>
                            {patients.map((patient) => (
                                <SelectItem key={patient.id} value={patient.id.toString()}>
                                    {patient.name}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                </div>
                <div className="space-y-2">
                    <Label htmlFor="status">Status</Label>
                    <Select value={formData.status} onValueChange={(value) => setFormData({...formData, status: value})}>
                        <SelectTrigger>
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="scheduled">Scheduled</SelectItem>
                            <SelectItem value="confirmed">Confirmed</SelectItem>
                            <SelectItem value="in_progress">In Progress</SelectItem>
                            <SelectItem value="completed">Completed</SelectItem>
                            <SelectItem value="cancelled">Cancelled</SelectItem>
                            <SelectItem value="no_show">No Show</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </div>
            <div className="grid gap-4 md:grid-cols-2">
                <div className="space-y-2">
                    <Label htmlFor="start_at">Date & Time *</Label>
                    <Input
                        id="start_at"
                        type="datetime-local"
                        value={formData.start_at}
                        onChange={(e) => setFormData({...formData, start_at: e.target.value})}
                        required
                    />
                </div>
                <div className="space-y-2">
                    <Label htmlFor="duration">Duration (minutes)</Label>
                    <Select value={formData.duration} onValueChange={(value) => setFormData({...formData, duration: value})}>
                        <SelectTrigger>
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="15">15 minutes</SelectItem>
                            <SelectItem value="30">30 minutes</SelectItem>
                            <SelectItem value="45">45 minutes</SelectItem>
                            <SelectItem value="60">60 minutes</SelectItem>
                            <SelectItem value="90">90 minutes</SelectItem>
                            <SelectItem value="120">120 minutes</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </div>
            <div className="grid gap-4 md:grid-cols-2">
                <div className="space-y-2">
                    <Label htmlFor="type">Appointment Type *</Label>
                    <Select value={formData.type} onValueChange={(value) => setFormData({...formData, type: value})}>
                        <SelectTrigger>
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="consultation">Consultation</SelectItem>
                            <SelectItem value="follow_up">Follow-up</SelectItem>
                            <SelectItem value="emergency">Emergency</SelectItem>
                            <SelectItem value="routine_checkup">Routine Checkup</SelectItem>
                            <SelectItem value="specialist_consultation">Specialist Consultation</SelectItem>
                            <SelectItem value="procedure">Procedure</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                <div className="space-y-2">
                    <Label htmlFor="priority">Priority</Label>
                    <Select value={formData.priority} onValueChange={(value) => setFormData({...formData, priority: value})}>
                        <SelectTrigger>
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="low">Low</SelectItem>
                            <SelectItem value="normal">Normal</SelectItem>
                            <SelectItem value="high">High</SelectItem>
                            <SelectItem value="urgent">Urgent</SelectItem>
                            <SelectItem value="emergency">Emergency</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </div>
            <div className="space-y-2">
                <Label htmlFor="reason">Reason for Visit *</Label>
                <Input
                    id="reason"
                    value={formData.reason}
                    onChange={(e) => setFormData({...formData, reason: e.target.value})}
                    placeholder="Brief description of the visit reason"
                    required
                />
            </div>
            <div className="space-y-2">
                <Label htmlFor="notes">Notes</Label>
                <Textarea
                    id="notes"
                    value={formData.notes}
                    onChange={(e) => setFormData({...formData, notes: e.target.value})}
                    placeholder="Additional notes or special instructions"
                    rows={3}
                />
            </div>
            <div className="flex justify-end gap-2">
                <Button type="button" variant="outline" onClick={onSuccess}>
                    Cancel
                </Button>
                <Button type="submit">
                    Update Appointment
                </Button>
            </div>
        </form>
    );
}
