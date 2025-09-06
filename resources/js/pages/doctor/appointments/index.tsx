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
import { Calendar, Clock, Users, Search, Filter, Plus, Eye, Edit, Trash2, CheckCircle, XCircle } from 'lucide-react';
import { Link } from '@inertiajs/react';
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
        href: doctorAppointments().url,
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
    appointments: Appointment[];
    patients: Array<{ id: number; name: string }>;
    rooms: Array<{ id: number; name: string }>;
    filters: {
        status: string;
        type: string;
        date: string;
    };
}

export default function DoctorAppointments({ appointments, patients, rooms, filters }: DoctorAppointmentsProps) {
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
        const matchesStatus = !statusFilter || appointment.status === statusFilter;
        const matchesType = !typeFilter || appointment.type === typeFilter;
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
            <Head title="Manage Appointments" />
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Manage Appointments</h1>
                        <p className="text-muted-foreground">
                            View and manage your patient appointments
                        </p>
                    </div>
                    <Dialog open={isCreateDialogOpen} onOpenChange={setIsCreateDialogOpen}>
                        <DialogTrigger asChild>
                            <Button>
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
                </div>

                {/* Filters */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <Filter className="h-5 w-5" />
                            Filters
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-5">
                            <div className="space-y-2">
                                <Label htmlFor="search">Search</Label>
                                <div className="relative">
                                    <Search className="absolute left-2 top-2.5 h-4 w-4 text-muted-foreground" />
                                    <Input
                                        id="search"
                                        placeholder="Search patients or reason..."
                                        value={searchTerm}
                                        onChange={(e) => setSearchTerm(e.target.value)}
                                        className="pl-8"
                                    />
                                </div>
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="status">Status</Label>
                                <Select value={statusFilter} onValueChange={setStatusFilter}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="All statuses" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="">All statuses</SelectItem>
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
                                <Label htmlFor="type">Type</Label>
                                <Select value={typeFilter} onValueChange={setTypeFilter}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="All types" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="">All types</SelectItem>
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
                                <Label htmlFor="date">Date</Label>
                                <Input
                                    id="date"
                                    type="date"
                                    value={dateFilter}
                                    onChange={(e) => setDateFilter(e.target.value)}
                                />
                            </div>
                            <div className="space-y-2">
                                <Label>&nbsp;</Label>
                                <Button 
                                    variant="outline" 
                                    onClick={() => {
                                        setSearchTerm('');
                                        setStatusFilter('');
                                        setTypeFilter('');
                                        setDateFilter('');
                                    }}
                                    className="w-full"
                                >
                                    Clear Filters
                                </Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {/* Appointments List */}
                <Card>
                    <CardHeader>
                        <CardTitle>Appointments ({filteredAppointments.length})</CardTitle>
                        <CardDescription>
                            Your scheduled appointments
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        {filteredAppointments.length > 0 ? (
                            <div className="space-y-4">
                                {filteredAppointments.map((appointment) => (
                                    <div key={appointment.id} className="border rounded-lg p-4 hover:bg-muted/50 transition-colors">
                                        <div className="flex items-center justify-between">
                                            <div className="space-y-2">
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
                                                        <Calendar className="h-4 w-4" />
                                                        {new Date(appointment.start_at).toLocaleDateString()}
                                                    </div>
                                                    <div className="flex items-center gap-1">
                                                        <Clock className="h-4 w-4" />
                                                        {new Date(appointment.start_at).toLocaleTimeString([], { 
                                                            hour: '2-digit', 
                                                            minute: '2-digit' 
                                                        })} - {new Date(appointment.end_at).toLocaleTimeString([], { 
                                                            hour: '2-digit', 
                                                            minute: '2-digit' 
                                                        })}
                                                    </div>
                                                    {appointment.room_name && (
                                                        <div className="flex items-center gap-1">
                                                            <Users className="h-4 w-4" />
                                                            {appointment.room_name}
                                                        </div>
                                                    )}
                                                </div>
                                                <p className="text-sm text-muted-foreground">
                                                    <strong>Type:</strong> {appointment.type.replace('_', ' ')} | 
                                                    <strong> Reason:</strong> {appointment.reason}
                                                </p>
                                                {appointment.notes && (
                                                    <p className="text-sm text-muted-foreground">
                                                        <strong>Notes:</strong> {appointment.notes}
                                                    </p>
                                                )}
                                            </div>
                                            <div className="flex items-center gap-2">
                                                <Select 
                                                    value={appointment.status} 
                                                    onValueChange={(value) => handleStatusChange(appointment.id, value)}
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
                                                    onClick={() => {
                                                        setSelectedAppointment(appointment);
                                                        setIsEditDialogOpen(true);
                                                    }}
                                                >
                                                    <Edit className="h-4 w-4" />
                                                </Button>
                                                <Button
                                                    variant="outline"
                                                    size="sm"
                                                    onClick={() => handleDelete(appointment.id)}
                                                >
                                                    <Trash2 className="h-4 w-4" />
                                                </Button>
                                            </div>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        ) : (
                            <div className="text-center py-12">
                                <Calendar className="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                                <h3 className="text-lg font-semibold mb-2">No appointments found</h3>
                                <p className="text-muted-foreground mb-4">
                                    {searchTerm || statusFilter || typeFilter || dateFilter
                                        ? 'Try adjusting your filters to see more results.'
                                        : 'You don\'t have any appointments scheduled yet.'}
                                </p>
                                <Button onClick={() => setIsCreateDialogOpen(true)}>
                                    <Plus className="h-4 w-4 mr-2" />
                                    Schedule First Appointment
                                </Button>
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
function EditAppointmentForm({ appointment, patients, rooms, onSuccess }: { 
    appointment: Appointment;
    patients: Array<{ id: number; name: string }>; 
    rooms: Array<{ id: number; name: string }>;
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
