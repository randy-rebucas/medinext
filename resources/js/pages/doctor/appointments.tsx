import { useState } from 'react';
import AppLayout from '@/layouts/app-layout';
import { doctorAppointments } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Calendar, Clock, Users, Search, Filter, Plus, Edit, Trash2, Building2, Shield } from 'lucide-react';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Doctor Dashboard',
        href: '/doctor/dashboard',
    },
    {
        title: 'Appointments',
        href: doctorAppointments(),
    },
];

interface Appointment {
    id: number;
    patient_name: string;
    patient_id: number;
    start_at: string;
    end_at: string;
    status: string;
    type: string;
    reason: string;
    room_name?: string;
    priority: string;
    notes?: string;
}

interface DoctorAppointmentsProps {
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
    rooms?: Array<{ id: number; name: string }>;
    filters?: {
        status: string;
        type: string;
        date: string;
    };
    permissions?: string[];
}

export default function DoctorAppointments({
    user,
    appointments = [],
    patients = [],
    rooms = [],
    filters = {
        status: '',
        type: '',
        date: ''
    },
    permissions = []
}: DoctorAppointmentsProps) {
    const hasPermission = (permission: string) => permissions.includes(permission);
    const [searchTerm, setSearchTerm] = useState('');
    const [statusFilter, setStatusFilter] = useState(filters.status || '');
    const [typeFilter, setTypeFilter] = useState(filters.type || '');
    const [dateFilter, setDateFilter] = useState(filters.date || '');
    const [isCreateDialogOpen, setIsCreateDialogOpen] = useState(false);
    const [isEditDialogOpen, setIsEditDialogOpen] = useState(false);
    const [selectedAppointment, setSelectedAppointment] = useState<Appointment | null>(null);

    const getStatusColor = (status: string) => {
        switch (status) {
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
        switch (priority) {
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

    const filteredAppointments = appointments.filter(appointment => {
        const matchesSearch = appointment.patient_name.toLowerCase().includes(searchTerm.toLowerCase()) ||
                            appointment.reason.toLowerCase().includes(searchTerm.toLowerCase());
        const matchesStatus = !statusFilter || statusFilter === 'all' || appointment.status === statusFilter;
        const matchesType = !typeFilter || typeFilter === 'all' || appointment.type === typeFilter;
        const matchesDate = !dateFilter || appointment.start_at.startsWith(dateFilter);

        return matchesSearch && matchesStatus && matchesType && matchesDate;
    });

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
                                    {user?.clinic?.name || 'No Clinic'} • View and manage your patient appointments
                                </p>
                            </div>
                            <div className="flex items-center gap-3">
                                <Badge variant="secondary" className="flex items-center gap-1 bg-white/20 text-white border-white/30 hover:bg-white/30">
                                    <Shield className="h-3 w-3" />
                                    Doctor
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
                                                New Appointment
                                            </Button>
                                        </DialogTrigger>
                                        <DialogContent className="max-w-2xl">
                                            <DialogHeader>
                                                <DialogTitle>Create New Appointment</DialogTitle>
                                                <DialogDescription>
                                                    Schedule a new appointment for a patient
                                                </DialogDescription>
                                            </DialogHeader>
                                            <CreateAppointmentForm
                                                patients={patients}
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
                                Filter and search through your appointments
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-5">
                                <div className="space-y-2">
                                    <Label htmlFor="search" className="text-sm font-medium text-slate-700 dark:text-slate-300">Search</Label>
                                    <div className="relative">
                                        <Search className="absolute left-3 top-3 h-4 w-4 text-slate-400" />
                                        <Input
                                            id="search"
                                            placeholder="Search patients or reason..."
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
                                        }}
                                        className="w-full border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 hover:border-slate-300 dark:hover:border-slate-600 transition-all duration-200"
                                    >
                                        Clear Filters
                                    </Button>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Appointments List */}
                    <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2 text-lg font-semibold text-slate-900 dark:text-white">
                                <div className="p-1 bg-green-100 dark:bg-green-900/20 rounded-md">
                                    <Calendar className="h-5 w-5 text-green-600 dark:text-green-400" />
                                </div>
                                Appointments ({filteredAppointments.length})
                            </CardTitle>
                            <CardDescription className="text-slate-600 dark:text-slate-300">
                                Your scheduled appointments
                            </CardDescription>
                        </CardHeader>
                    <CardContent>
                        {filteredAppointments.length > 0 ? (
                            <div className="space-y-4">
                                {filteredAppointments.map((appointment) => (
                                    <div key={appointment.id} className="border border-slate-200 dark:border-slate-700 rounded-xl p-6 hover:bg-slate-50 dark:hover:bg-slate-700/50 hover:shadow-md transition-all duration-200">
                                        <div className="flex items-center justify-between">
                                            <div className="space-y-3">
                                                <div className="flex items-center gap-3">
                                                    <h3 className="font-semibold text-slate-900 dark:text-white">{appointment.patient_name}</h3>
                                                    <Badge className={`${getStatusColor(appointment.status)} border-0`}>
                                                        {appointment.status.replace('_', ' ')}
                                                    </Badge>
                                                    <Badge variant="outline" className={`${getPriorityColor(appointment.priority)} border-0`}>
                                                        {appointment.priority}
                                                    </Badge>
                                                </div>
                                                <div className="flex items-center gap-6 text-sm text-slate-600 dark:text-slate-400">
                                                    <div className="flex items-center gap-2">
                                                        <div className="p-1 bg-blue-100 dark:bg-blue-900/20 rounded">
                                                            <Calendar className="h-3 w-3 text-blue-600 dark:text-blue-400" />
                                                        </div>
                                                        {new Date(appointment.start_at).toLocaleDateString()}
                                                    </div>
                                                    <div className="flex items-center gap-2">
                                                        <div className="p-1 bg-orange-100 dark:bg-orange-900/20 rounded">
                                                            <Clock className="h-3 w-3 text-orange-600 dark:text-orange-400" />
                                                        </div>
                                                        {new Date(appointment.start_at).toLocaleTimeString([], {
                                                            hour: '2-digit',
                                                            minute: '2-digit'
                                                        })} - {new Date(appointment.end_at).toLocaleTimeString([], {
                                                            hour: '2-digit',
                                                            minute: '2-digit'
                                                        })}
                                                    </div>
                                                    {appointment.room_name && (
                                                        <div className="flex items-center gap-2">
                                                            <div className="p-1 bg-purple-100 dark:bg-purple-900/20 rounded">
                                                                <Users className="h-3 w-3 text-purple-600 dark:text-purple-400" />
                                                            </div>
                                                            {appointment.room_name}
                                                        </div>
                                                    )}
                                                </div>
                                                <div className="space-y-1">
                                                    <p className="text-sm text-slate-600 dark:text-slate-400">
                                                        <span className="font-medium text-slate-700 dark:text-slate-300">Type:</span> {appointment.type.replace('_', ' ')} |
                                                        <span className="font-medium text-slate-700 dark:text-slate-300"> Reason:</span> {appointment.reason}
                                                    </p>
                                                    {appointment.notes && (
                                                        <p className="text-sm text-slate-600 dark:text-slate-400">
                                                            <span className="font-medium text-slate-700 dark:text-slate-300">Notes:</span> {appointment.notes}
                                                        </p>
                                                    )}
                                                </div>
                                            </div>
                                            <div className="flex items-center gap-2">
                                                {hasPermission('update_appointments') && (
                                                    <Select
                                                        value={appointment.status}
                                                        onValueChange={(value) => handleStatusChange(appointment.id, value)}
                                                    >
                                                        <SelectTrigger className="w-32 border-slate-200 dark:border-slate-700">
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
                                                )}
                                                {hasPermission('edit_appointments') && (
                                                    <Button
                                                        variant="outline"
                                                        size="sm"
                                                        onClick={() => {
                                                            setSelectedAppointment(appointment);
                                                            setIsEditDialogOpen(true);
                                                        }}
                                                        className="border-slate-200 dark:border-slate-700 hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:border-blue-300 dark:hover:border-blue-600"
                                                    >
                                                        <Edit className="h-4 w-4" />
                                                    </Button>
                                                )}
                                                {hasPermission('delete_appointments') && (
                                                    <Button
                                                        variant="outline"
                                                        size="sm"
                                                        onClick={() => handleDelete(appointment.id)}
                                                        className="border-slate-200 dark:border-slate-700 text-red-600 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-900/20 hover:border-red-300 dark:hover:border-red-600"
                                                    >
                                                        <Trash2 className="h-4 w-4" />
                                                    </Button>
                                                )}
                                            </div>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        ) : (
                            <div className="text-center py-12">
                                <div className="p-4 bg-slate-100 dark:bg-slate-700/50 rounded-full w-fit mx-auto mb-4">
                                    <Calendar className="h-12 w-12 text-slate-400" />
                                </div>
                                <h3 className="text-lg font-semibold text-slate-900 dark:text-white mb-2">No appointments found</h3>
                                <p className="text-slate-600 dark:text-slate-400 mb-4">
                                    {searchTerm || statusFilter || typeFilter || dateFilter
                                        ? 'Try adjusting your filters to see more results.'
                                        : 'You don\'t have any appointments scheduled yet.'}
                                </p>
                                {hasPermission('create_appointments') && (
                                    <Button
                                        onClick={() => setIsCreateDialogOpen(true)}
                                        className="bg-blue-600 hover:bg-blue-700 text-white"
                                    >
                                        <Plus className="h-4 w-4 mr-2" />
                                        Schedule First Appointment
                                    </Button>
                                )}
                            </div>
                        )}
                    </CardContent>
                </Card>

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

// Create Appointment Form Component
function CreateAppointmentForm({ patients, rooms, onSuccess }: {
    patients: Array<{ id: number; name: string }>;
    rooms: Array<{ id: number; name: string }>;
    onSuccess: () => void;
}) {
    const [formData, setFormData] = useState({
        patient_id: '',
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
                    Create Appointment
                </Button>
            </div>
        </form>
    );
}

// Edit Appointment Form Component
function EditAppointmentForm({ appointment, patients, onSuccess }: {
    appointment: Appointment;
    patients: Array<{ id: number; name: string }>;
    onSuccess: () => void;
}) {
    const [formData, setFormData] = useState({
        patient_id: appointment.patient_id.toString(),
        start_at: appointment.start_at.slice(0, 16), // Convert to datetime-local format
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
