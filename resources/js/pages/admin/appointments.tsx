import { Head } from '@inertiajs/react';
import { useState, useEffect } from 'react';
import AppLayout from '@/layouts/app-layout';
import { adminAppointments } from '@/routes';
import { type BreadcrumbItem, type Appointment } from '@/types';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Checkbox } from '@/components/ui/checkbox';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Admin Dashboard',
        href: '/admin/dashboard',
    },
    {
        title: 'Appointment Management',
        href: adminAppointments(),
    },
];
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import {
    Calendar,
    Plus,
    Search,
    Edit,
    Eye,
    Clock,
    Stethoscope,
    MoreHorizontal,
    MapPin,
    Save,
    X,
    Trash2,
    User,
    Phone,
    Mail,
    AlertTriangle,
    CheckCircle,
    XCircle,
    Loader2,
    Filter,
    CalendarDays,
    Building2
} from 'lucide-react';

// Simple toast implementation
const toast = {
    success: (message: string) => console.log('Success:', message),
    error: (message: string) => console.error('Error:', message),
};

interface Patient {
    id: number;
    name: string;
    patient_id: string;
}

interface Doctor {
    id: number;
    name: string;
    specialization: string;
}

interface Room {
    id: number;
    name: string;
    room_number: string;
}

interface AdminAppointmentsProps {
    appointments: Appointment[];
    patients: Patient[];
    doctors: Doctor[];
    rooms: Room[];
    permissions: string[];
}

export default function AdminAppointments({ appointments: initialAppointments, patients, doctors, rooms }: AdminAppointmentsProps) {
    const [appointments, setAppointments] = useState<Appointment[]>(initialAppointments);
    const [searchTerm, setSearchTerm] = useState('');
    const [statusFilter, setStatusFilter] = useState('all');
    const [typeFilter, setTypeFilter] = useState('all');
    const [dateFilter, setDateFilter] = useState('all');
    const [isAddModalOpen, setIsAddModalOpen] = useState(false);
    const [isEditModalOpen, setIsEditModalOpen] = useState(false);
    const [isViewModalOpen, setIsViewModalOpen] = useState(false);
    const [isDeleteModalOpen, setIsDeleteModalOpen] = useState(false);
    const [isCalendarModalOpen, setIsCalendarModalOpen] = useState(false);
    const [editingAppointment, setEditingAppointment] = useState<Appointment | null>(null);
    const [viewingAppointment, setViewingAppointment] = useState<Appointment | null>(null);
    const [deletingAppointment, setDeletingAppointment] = useState<Appointment | null>(null);
    const [loading, setLoading] = useState(false);
    const [errors, setErrors] = useState<Record<string, string>>({});
    const [calendarData, setCalendarData] = useState<Appointment[]>([]);
    const [formData, setFormData] = useState({
        patient_id: '',
        doctor_id: '',
        start_at: '',
        end_at: '',
        type: '',
        status: 'Scheduled',
        room_id: '',
        priority: 'Normal',
        notes: '',
        reason: ''
    });


    const filteredAppointments = appointments.filter(appointment => {
        const matchesSearch = appointment.patient_name.toLowerCase().includes(searchTerm.toLowerCase()) ||
                            appointment.doctor_name.toLowerCase().includes(searchTerm.toLowerCase()) ||
                            appointment.type.toLowerCase().includes(searchTerm.toLowerCase());
        const matchesStatus = statusFilter === 'all' || appointment.status === statusFilter;
        const matchesType = typeFilter === 'all' || appointment.type === typeFilter;
        const matchesDate = dateFilter === 'all' ||
            (dateFilter === 'today' && appointment.date === new Date().toISOString().split('T')[0]) ||
            (dateFilter === 'tomorrow' && appointment.date === new Date(Date.now() + 86400000).toISOString().split('T')[0]) ||
            (dateFilter === 'this_week' && new Date(appointment.date) >= new Date(Date.now() - 7 * 24 * 60 * 60 * 1000));

        return matchesSearch && matchesStatus && matchesType && matchesDate;
    });

    // API Functions
    const fetchAppointments = async () => {
        try {
            setLoading(true);
            const response = await fetch('/admin/appointments');
            const data = await response.json();
            if (data.success) {
                setAppointments(data.appointments);
            }
        } catch {
            toast.error('Failed to fetch appointments');
        } finally {
            setLoading(false);
        }
    };

    const fetchCalendarData = async () => {
        try {
            const response = await fetch('/admin/appointments/calendar/data');
            const data = await response.json();
            if (data.success) {
                setCalendarData(data.appointments);
            }
        } catch {
            toast.error('Failed to fetch calendar data');
        }
    };

    const saveAppointment = async (appointmentData: Record<string, unknown>, isEdit = false) => {
        try {
            setLoading(true);
            setErrors({});

            const url = isEdit ? `/admin/appointments/${editingAppointment?.id}` : '/admin/appointments';
            const method = isEdit ? 'PUT' : 'POST';

            const response = await fetch(url, {
                method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                body: JSON.stringify(appointmentData),
            });

            const data = await response.json();

            if (data.success) {
                toast.success(data.message);
                await fetchAppointments();
                handleCancel();
            } else {
                if (data.errors) {
                    setErrors(data.errors);
                }
                toast.error(data.message || 'Failed to save appointment');
            }
        } catch {
            toast.error('Failed to save appointment');
        } finally {
            setLoading(false);
        }
    };

    const deleteAppointment = async () => {
        if (!deletingAppointment) return;

        try {
            setLoading(true);
            const response = await fetch(`/admin/appointments/${deletingAppointment.id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
            });

            const data = await response.json();

            if (data.success) {
                toast.success(data.message);
                await fetchAppointments();
                setIsDeleteModalOpen(false);
                setDeletingAppointment(null);
            } else {
                toast.error(data.message || 'Failed to delete appointment');
            }
        } catch {
            toast.error('Failed to delete appointment');
        } finally {
            setLoading(false);
        }
    };

    // const updateAppointmentStatus = async (appointmentId: number, status: string) => {
    //     try {
    //         const response = await fetch(`/admin/appointments/${appointmentId}/status`, {
    //             method: 'PUT',
    //             headers: {
    //                 'Content-Type': 'application/json',
    //                 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
    //             },
    //             body: JSON.stringify({ status }),
    //         });

    //         const data = await response.json();

    //         if (data.success) {
    //             toast.success(data.message);
    //             await fetchAppointments();
    //         } else {
    //             toast.error(data.message || 'Failed to update appointment status');
    //         }
    //     } catch {
    //         toast.error('Failed to update appointment status');
    //     }
    // };

    const getStatusColor = (status: string) => {
        switch (status) {
            case 'Scheduled': return 'default';
            case 'Confirmed': return 'default';
            case 'In Progress': return 'secondary';
            case 'Completed': return 'default';
            case 'Cancelled': return 'destructive';
            default: return 'secondary';
        }
    };

    const getTypeColor = (type: string) => {
        const colors = {
            'Consultation': 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400',
            'Follow-up': 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400',
            'Check-up': 'bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-400',
            'Emergency': 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400'
        };
        return colors[type as keyof typeof colors] || 'bg-slate-100 text-slate-800 dark:bg-slate-900/20 dark:text-slate-400';
    };

    const handleAddAppointment = () => {
        setIsAddModalOpen(true);
        setErrors({});
        setFormData({
            patient_id: '',
            doctor_id: '',
            start_at: '',
            end_at: '',
            type: '',
            status: 'Scheduled',
            room_id: '',
            priority: 'Normal',
            notes: '',
            reason: ''
        });
    };

    const handleEditAppointment = (appointment: Appointment) => {
        setEditingAppointment(appointment);
        setErrors({});
        setFormData({
            patient_id: String(appointment.patient_id || ''),
            doctor_id: String(appointment.doctor_id || ''),
            start_at: appointment.start_at || '',
            end_at: appointment.end_at || '',
            type: appointment.type || '',
            status: appointment.status || 'Scheduled',
            room_id: String(appointment.room_id || ''),
            priority: appointment.priority || 'Normal',
            notes: appointment.notes || '',
            reason: appointment.reason || ''
        });
        setIsEditModalOpen(true);
    };

    const handleViewAppointment = (appointment: Appointment) => {
        setViewingAppointment(appointment);
        setIsViewModalOpen(true);
    };

    const handleDeleteAppointment = (appointment: Appointment) => {
        setDeletingAppointment(appointment);
        setIsDeleteModalOpen(true);
    };

    const handleViewCalendar = () => {
        setIsCalendarModalOpen(true);
        fetchCalendarData();
    };

    const handleSaveAppointment = () => {
        saveAppointment(formData, isEditModalOpen);
    };

    const handleCancel = () => {
        setIsAddModalOpen(false);
        setIsEditModalOpen(false);
        setIsViewModalOpen(false);
        setIsCalendarModalOpen(false);
        setEditingAppointment(null);
        setViewingAppointment(null);
        setErrors({});
        setFormData({
            patient_id: '',
            doctor_id: '',
            start_at: '',
            end_at: '',
            type: '',
            status: 'Scheduled',
            room_id: '',
            priority: 'Normal',
            notes: '',
            reason: ''
        });
    };


    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Appointment Management - Medinext">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&family=instrument-sans:400,500,600" rel="stylesheet" />
            </Head>

            <div className="min-h-screen bg-gradient-to-br from-slate-50 via-white to-blue-50 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900">
                <div className="space-y-6 p-6">

                    <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                        <CardHeader>
                            <div className="flex items-center justify-between">
                                <div>
                                    <CardTitle className="text-xl font-semibold text-slate-900 dark:text-white">All Appointments</CardTitle>
                                    <CardDescription className="text-slate-600 dark:text-slate-300">
                                        Manage appointments across all doctors and patients
                                    </CardDescription>
                                </div>
                                <div className="flex space-x-3">
                                    <Button
                                        variant="outline"
                                        onClick={handleViewCalendar}
                                        className="border-slate-300 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-700"
                                    >
                                        <Calendar className="mr-2 h-4 w-4" />
                                        View Calendar
                                    </Button>
                                    <Button
                                        onClick={handleAddAppointment}
                                        className="bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white shadow-lg"
                                    >
                                        <Plus className="mr-2 h-4 w-4" />
                                        Schedule Appointment
                                    </Button>
                                </div>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div className="flex items-center space-x-3 mb-6">
                                <div className="relative flex-1">
                                    <Search className="absolute left-3 top-3 h-4 w-4 text-slate-400" />
                                    <Input
                                        placeholder="Search appointments..."
                                        className="pl-10 h-11 border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-700 dark:text-white"
                                        value={searchTerm}
                                        onChange={(e) => setSearchTerm(e.target.value)}
                                    />
                                </div>
                                <Select value={statusFilter} onValueChange={setStatusFilter}>
                                    <SelectTrigger className="w-[160px] h-11 border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500">
                                        <SelectValue placeholder="Status" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">All Status</SelectItem>
                                        <SelectItem value="Scheduled">Scheduled</SelectItem>
                                        <SelectItem value="Confirmed">Confirmed</SelectItem>
                                        <SelectItem value="In Progress">In Progress</SelectItem>
                                        <SelectItem value="Completed">Completed</SelectItem>
                                        <SelectItem value="Cancelled">Cancelled</SelectItem>
                                    </SelectContent>
                                </Select>
                                <Select value={typeFilter} onValueChange={setTypeFilter}>
                                    <SelectTrigger className="w-[160px] h-11 border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500">
                                        <SelectValue placeholder="Type" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">All Types</SelectItem>
                                        <SelectItem value="Consultation">Consultation</SelectItem>
                                        <SelectItem value="Follow-up">Follow-up</SelectItem>
                                        <SelectItem value="Check-up">Check-up</SelectItem>
                                        <SelectItem value="Emergency">Emergency</SelectItem>
                                    </SelectContent>
                                </Select>
                                <Select value={dateFilter} onValueChange={setDateFilter}>
                                    <SelectTrigger className="w-[160px] h-11 border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500">
                                        <SelectValue placeholder="Date" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">All Dates</SelectItem>
                                        <SelectItem value="today">Today</SelectItem>
                                        <SelectItem value="tomorrow">Tomorrow</SelectItem>
                                        <SelectItem value="this_week">This Week</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <div className="rounded-lg border border-slate-200 dark:border-slate-700 overflow-hidden">
                                <Table>
                                    <TableHeader className="bg-slate-50 dark:bg-slate-800/50">
                                        <TableRow className="border-slate-200 dark:border-slate-700">
                                            <TableHead className="font-semibold text-slate-700 dark:text-slate-300">Patient</TableHead>
                                            <TableHead className="font-semibold text-slate-700 dark:text-slate-300">Doctor</TableHead>
                                            <TableHead className="font-semibold text-slate-700 dark:text-slate-300">Date & Time</TableHead>
                                            <TableHead className="font-semibold text-slate-700 dark:text-slate-300">Type</TableHead>
                                            <TableHead className="font-semibold text-slate-700 dark:text-slate-300">Status</TableHead>
                                            <TableHead className="font-semibold text-slate-700 dark:text-slate-300">Room</TableHead>
                                            <TableHead className="text-right font-semibold text-slate-700 dark:text-slate-300">Actions</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {filteredAppointments.map((appointment) => (
                                            <TableRow key={appointment.id} className="border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors duration-200">
                                                <TableCell>
                                                    <div className="flex items-center space-x-3">
                                                        <div className="h-10 w-10 rounded-full bg-gradient-to-r from-blue-500 to-purple-500 flex items-center justify-center shadow-md">
                                                            <span className="text-sm font-bold text-white">
                                                                {appointment.patient_name.split(' ').map(n => n[0]).join('')}
                                                            </span>
                                                        </div>
                                                        <div>
                                                            <div className="font-semibold text-slate-900 dark:text-white">{appointment.patient_name}</div>
                                                            <div className="text-sm text-slate-500 dark:text-slate-400">{appointment.patient_email}</div>
                                                        </div>
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    <div className="flex items-center space-x-3">
                                                        <div className="p-2 bg-slate-100 dark:bg-slate-700 rounded-lg">
                                                            <Stethoscope className="h-4 w-4 text-slate-600 dark:text-slate-400" />
                                                        </div>
                                                        <div>
                                                            <div className="font-medium text-slate-900 dark:text-white">{appointment.doctor_name}</div>
                                                            <div className="text-sm text-slate-500 dark:text-slate-400">{appointment.doctor_specialization}</div>
                                                        </div>
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    <div className="flex items-center space-x-3">
                                                        <div className="p-2 bg-slate-100 dark:bg-slate-700 rounded-lg">
                                                            <Calendar className="h-4 w-4 text-slate-600 dark:text-slate-400" />
                                                        </div>
                                                        <div>
                                                            <div className="font-medium text-slate-900 dark:text-white">{appointment.date}</div>
                                                            <div className="text-sm text-slate-500 dark:text-slate-400 flex items-center">
                                                                <Clock className="mr-1 h-3 w-3" />
                                                                {appointment.time} • {appointment.duration} min
                                                            </div>
                                                        </div>
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    <Badge className={`font-medium ${getTypeColor(appointment.type)}`}>
                                                        {appointment.type}
                                                    </Badge>
                                                </TableCell>
                                                <TableCell>
                                                    <Badge
                                                        variant={getStatusColor(appointment.status)}
                                                        className={`font-medium ${
                                                            appointment.status === 'Completed'
                                                                ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400'
                                                                : appointment.status === 'In Progress'
                                                                ? 'bg-orange-100 text-orange-800 dark:bg-orange-900/20 dark:text-orange-400'
                                                                : appointment.status === 'Scheduled' || appointment.status === 'Confirmed'
                                                                ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400'
                                                                : 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400'
                                                        }`}
                                                    >
                                                        {appointment.status}
                                                    </Badge>
                                                </TableCell>
                                                <TableCell>
                                                    <div className="flex items-center">
                                                        <MapPin className="mr-2 h-4 w-4 text-slate-400" />
                                                        <span className="font-medium text-slate-900 dark:text-white">{appointment.room_name}</span>
                                                    </div>
                                                </TableCell>
                                                <TableCell className="text-right">
                                                    <div className="flex items-center justify-end space-x-2">
                                                        <Button
                                                            variant="ghost"
                                                            size="sm"
                                                            title="View Details"
                                                            onClick={() => handleViewAppointment(appointment)}
                                                            className="h-8 w-8 p-0 hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:text-blue-600 dark:hover:text-blue-400"
                                                        >
                                                            <Eye className="h-4 w-4" />
                                                        </Button>
                                                        <Button
                                                            variant="ghost"
                                                            size="sm"
                                                            title="Edit Appointment"
                                                            onClick={() => handleEditAppointment(appointment)}
                                                            className="h-8 w-8 p-0 hover:bg-green-50 dark:hover:bg-green-900/20 hover:text-green-600 dark:hover:text-green-400"
                                                        >
                                                            <Edit className="h-4 w-4" />
                                                        </Button>
                                                        <Button
                                                            variant="ghost"
                                                            size="sm"
                                                            title="Delete Appointment"
                                                            onClick={() => handleDeleteAppointment(appointment)}
                                                            className="h-8 w-8 p-0 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-600 dark:hover:text-red-400"
                                                        >
                                                            <Trash2 className="h-4 w-4" />
                                                        </Button>
                                                    </div>
                                                </TableCell>
                                            </TableRow>
                                        ))}
                                    </TableBody>
                                </Table>
                            </div>

                            {filteredAppointments.length === 0 && (
                                <div className="text-center py-12">
                                    <div className="mx-auto w-16 h-16 bg-slate-100 dark:bg-slate-700 rounded-full flex items-center justify-center mb-4">
                                        <Calendar className="h-8 w-8 text-slate-400" />
                                    </div>
                                    <h3 className="text-lg font-semibold text-slate-900 dark:text-white mb-2">No appointments found</h3>
                                    <p className="text-slate-500 dark:text-slate-400 mb-4">
                                        Try adjusting your search or filter criteria.
                                    </p>
                                    <Button className="bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white">
                                        <Plus className="mr-2 h-4 w-4" />
                                        Schedule Appointment
                                    </Button>
                                </div>
                            )}
                        </CardContent>
                    </Card>
                </div>
            </div>

            {/* Add Appointment Modal */}
            <Dialog open={isAddModalOpen} onOpenChange={setIsAddModalOpen}>
                <DialogContent className="max-w-4xl max-h-[90vh] overflow-y-auto">
                    <DialogHeader>
                        <DialogTitle>Schedule New Appointment</DialogTitle>
                        <DialogDescription>
                            Create a new appointment for a patient.
                        </DialogDescription>
                    </DialogHeader>
                    <div className="grid gap-4 py-4">
                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="patient_id">Patient *</Label>
                                <Select value={formData.patient_id} onValueChange={(value) => setFormData({...formData, patient_id: value})}>
                                    <SelectTrigger className={errors.patient_id ? 'border-red-500' : ''}>
                                        <SelectValue placeholder="Select patient" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {patients.map((patient) => (
                                            <SelectItem key={patient.id} value={String(patient.id)}>
                                                {patient.name} ({patient.patient_id})
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                {errors.patient_id && <p className="text-sm text-red-500">{errors.patient_id}</p>}
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="doctor_id">Doctor *</Label>
                                <Select value={formData.doctor_id} onValueChange={(value) => setFormData({...formData, doctor_id: value})}>
                                    <SelectTrigger className={errors.doctor_id ? 'border-red-500' : ''}>
                                        <SelectValue placeholder="Select doctor" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {doctors.map((doctor) => (
                                            <SelectItem key={doctor.id} value={String(doctor.id)}>
                                                {doctor.name} - {doctor.specialization}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                {errors.doctor_id && <p className="text-sm text-red-500">{errors.doctor_id}</p>}
                            </div>
                        </div>
                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="start_at">Start Date & Time *</Label>
                                <Input
                                    id="start_at"
                                    type="datetime-local"
                                    value={formData.start_at}
                                    onChange={(e) => setFormData({...formData, start_at: e.target.value})}
                                    className={errors.start_at ? 'border-red-500' : ''}
                                />
                                {errors.start_at && <p className="text-sm text-red-500">{errors.start_at}</p>}
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="end_at">End Date & Time *</Label>
                                <Input
                                    id="end_at"
                                    type="datetime-local"
                                    value={formData.end_at}
                                    onChange={(e) => setFormData({...formData, end_at: e.target.value})}
                                    className={errors.end_at ? 'border-red-500' : ''}
                                />
                                {errors.end_at && <p className="text-sm text-red-500">{errors.end_at}</p>}
                            </div>
                        </div>
                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="type">Appointment Type *</Label>
                                <Select value={formData.type} onValueChange={(value) => setFormData({...formData, type: value})}>
                                    <SelectTrigger className={errors.type ? 'border-red-500' : ''}>
                                        <SelectValue placeholder="Select type" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="Consultation">Consultation</SelectItem>
                                        <SelectItem value="Follow-up">Follow-up</SelectItem>
                                        <SelectItem value="Check-up">Check-up</SelectItem>
                                        <SelectItem value="Emergency">Emergency</SelectItem>
                                        <SelectItem value="Procedure">Procedure</SelectItem>
                                    </SelectContent>
                                </Select>
                                {errors.type && <p className="text-sm text-red-500">{errors.type}</p>}
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="priority">Priority *</Label>
                                <Select value={formData.priority} onValueChange={(value) => setFormData({...formData, priority: value})}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select priority" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="Low">Low</SelectItem>
                                        <SelectItem value="Normal">Normal</SelectItem>
                                        <SelectItem value="High">High</SelectItem>
                                        <SelectItem value="Urgent">Urgent</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>
                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="room_id">Room</Label>
                                <Select value={formData.room_id} onValueChange={(value) => setFormData({...formData, room_id: value})}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select room" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {rooms.map((room) => (
                                            <SelectItem key={room.id} value={String(room.id)}>
                                                {room.name} ({room.room_number})
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="status">Status *</Label>
                                <Select value={formData.status} onValueChange={(value) => setFormData({...formData, status: value})}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select status" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="Scheduled">Scheduled</SelectItem>
                                        <SelectItem value="Confirmed">Confirmed</SelectItem>
                                        <SelectItem value="In Progress">In Progress</SelectItem>
                                        <SelectItem value="Completed">Completed</SelectItem>
                                        <SelectItem value="Cancelled">Cancelled</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="reason">Reason for Visit</Label>
                            <Textarea
                                id="reason"
                                value={formData.reason}
                                onChange={(e) => setFormData({...formData, reason: e.target.value})}
                                placeholder="Brief description of the reason for the appointment"
                                rows={2}
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
                    </div>
                    <DialogFooter>
                        <Button variant="outline" onClick={handleCancel}>
                            <X className="mr-2 h-4 w-4" />
                            Cancel
                        </Button>
                        <Button onClick={handleSaveAppointment} disabled={loading}>
                            {loading ? (
                                <>
                                    <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                                    Scheduling...
                                </>
                            ) : (
                                <>
                                    <Save className="mr-2 h-4 w-4" />
                                    Schedule Appointment
                                </>
                            )}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            {/* Edit Appointment Modal */}
            <Dialog open={isEditModalOpen} onOpenChange={setIsEditModalOpen}>
                <DialogContent className="max-w-4xl max-h-[90vh] overflow-y-auto">
                    <DialogHeader>
                        <DialogTitle>Edit Appointment</DialogTitle>
                        <DialogDescription>
                            Update the appointment details for {editingAppointment?.patient}.
                        </DialogDescription>
                    </DialogHeader>
                    <div className="grid gap-4 py-4">
                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="edit-patient">Patient Name *</Label>
                                <Input
                                    id="edit-patient"
                                    value={formData.patient}
                                    onChange={(e) => setFormData({...formData, patient: e.target.value})}
                                    placeholder="Enter patient name"
                                />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="edit-patientEmail">Patient Email *</Label>
                                <Input
                                    id="edit-patientEmail"
                                    type="email"
                                    value={formData.patientEmail}
                                    onChange={(e) => setFormData({...formData, patientEmail: e.target.value})}
                                    placeholder="patient@email.com"
                                />
                            </div>
                        </div>
                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="edit-patientPhone">Patient Phone *</Label>
                                <Input
                                    id="edit-patientPhone"
                                    value={formData.patientPhone}
                                    onChange={(e) => setFormData({...formData, patientPhone: e.target.value})}
                                    placeholder="+1 (555) 123-4567"
                                />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="edit-doctor">Doctor *</Label>
                                <Select value={formData.doctor} onValueChange={(value) => setFormData({...formData, doctor: value})}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select doctor" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="Dr. Sarah Johnson">Dr. Sarah Johnson - Cardiology</SelectItem>
                                        <SelectItem value="Dr. Michael Brown">Dr. Michael Brown - Pediatrics</SelectItem>
                                        <SelectItem value="Dr. Emily Davis">Dr. Emily Davis - Dermatology</SelectItem>
                                        <SelectItem value="Dr. James Wilson">Dr. James Wilson - Orthopedics</SelectItem>
                                        <SelectItem value="Dr. Jennifer Lee">Dr. Jennifer Lee - Neurology</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>
                        <div className="grid grid-cols-3 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="edit-date">Date *</Label>
                                <Input
                                    id="edit-date"
                                    type="date"
                                    value={formData.date}
                                    onChange={(e) => setFormData({...formData, date: e.target.value})}
                                />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="edit-time">Time *</Label>
                                <Input
                                    id="edit-time"
                                    type="time"
                                    value={formData.time}
                                    onChange={(e) => setFormData({...formData, time: e.target.value})}
                                />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="edit-duration">Duration (minutes) *</Label>
                                <Select value={formData.duration} onValueChange={(value) => setFormData({...formData, duration: value})}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select duration" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="15">15 minutes</SelectItem>
                                        <SelectItem value="30">30 minutes</SelectItem>
                                        <SelectItem value="45">45 minutes</SelectItem>
                                        <SelectItem value="60">60 minutes</SelectItem>
                                        <SelectItem value="90">90 minutes</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>
                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="edit-type">Appointment Type *</Label>
                                <Select value={formData.type} onValueChange={(value) => setFormData({...formData, type: value})}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select type" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="Consultation">Consultation</SelectItem>
                                        <SelectItem value="Follow-up">Follow-up</SelectItem>
                                        <SelectItem value="Check-up">Check-up</SelectItem>
                                        <SelectItem value="Emergency">Emergency</SelectItem>
                                        <SelectItem value="Procedure">Procedure</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="edit-room">Room *</Label>
                                <Select value={formData.room} onValueChange={(value) => setFormData({...formData, room: value})}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select room" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="Room 101">Room 101</SelectItem>
                                        <SelectItem value="Room 102">Room 102</SelectItem>
                                        <SelectItem value="Room 103">Room 103</SelectItem>
                                        <SelectItem value="Room 104">Room 104</SelectItem>
                                        <SelectItem value="Room 105">Room 105</SelectItem>
                                        <SelectItem value="Emergency Room">Emergency Room</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="edit-reason">Reason for Visit</Label>
                            <Textarea
                                id="edit-reason"
                                value={formData.reason}
                                onChange={(e) => setFormData({...formData, reason: e.target.value})}
                                placeholder="Brief description of the reason for the appointment"
                                rows={2}
                            />
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="edit-notes">Notes</Label>
                            <Textarea
                                id="edit-notes"
                                value={formData.notes}
                                onChange={(e) => setFormData({...formData, notes: e.target.value})}
                                placeholder="Additional notes or special instructions"
                                rows={3}
                            />
                        </div>
                    </div>
                    <DialogFooter>
                        <Button variant="outline" onClick={handleCancel}>
                            <X className="mr-2 h-4 w-4" />
                            Cancel
                        </Button>
                        <Button onClick={handleSaveAppointment} disabled={loading}>
                            {loading ? (
                                <>
                                    <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                                    Updating...
                                </>
                            ) : (
                                <>
                                    <Save className="mr-2 h-4 w-4" />
                                    Update Appointment
                                </>
                            )}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            {/* View Appointment Details Modal */}
            <Dialog open={isViewModalOpen} onOpenChange={setIsViewModalOpen}>
                <DialogContent className="max-w-4xl max-h-[90vh] overflow-y-auto">
                    <DialogHeader>
                        <DialogTitle>Appointment Details</DialogTitle>
                        <DialogDescription>
                            Complete information about the appointment
                        </DialogDescription>
                    </DialogHeader>
                    {viewingAppointment && (
                        <div className="space-y-6">
                            <div className="grid grid-cols-2 gap-6">
                                <div className="space-y-4">
                                    <div className="flex items-center space-x-3">
                                        <div className="h-16 w-16 rounded-full bg-gradient-to-r from-blue-500 to-purple-500 flex items-center justify-center shadow-md">
                                            <span className="text-lg font-bold text-white">
                                                {viewingAppointment.patient_name.split(' ').map(n => n[0]).join('')}
                                            </span>
                                        </div>
                                        <div>
                                            <h3 className="text-xl font-semibold text-slate-900 dark:text-white">{viewingAppointment.patient_name}</h3>
                                            <p className="text-slate-600 dark:text-slate-400">Patient ID: {viewingAppointment.patient_id}</p>
                                            <Badge
                                                variant={getStatusColor(viewingAppointment.status)}
                                                className={`font-medium ${
                                                    viewingAppointment.status === 'Completed'
                                                        ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400'
                                                        : viewingAppointment.status === 'In Progress'
                                                        ? 'bg-orange-100 text-orange-800 dark:bg-orange-900/20 dark:text-orange-400'
                                                        : viewingAppointment.status === 'Scheduled' || viewingAppointment.status === 'Confirmed'
                                                        ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400'
                                                        : 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400'
                                                }`}
                                            >
                                                {viewingAppointment.status}
                                            </Badge>
                                        </div>
                                    </div>
                                </div>
                                <div className="space-y-4">
                                    <div className="flex items-center space-x-2">
                                        <Stethoscope className="h-4 w-4 text-slate-400" />
                                        <span className="text-slate-700 dark:text-slate-300">{viewingAppointment.doctor_name}</span>
                                    </div>
                                    <div className="flex items-center space-x-2">
                                        <Calendar className="h-4 w-4 text-slate-400" />
                                        <span className="text-slate-700 dark:text-slate-300">{viewingAppointment.date} at {viewingAppointment.time}</span>
                                    </div>
                                    <div className="flex items-center space-x-2">
                                        <Clock className="h-4 w-4 text-slate-400" />
                                        <span className="text-slate-700 dark:text-slate-300">{viewingAppointment.duration} minutes</span>
                                    </div>
                                    <div className="flex items-center space-x-2">
                                        <MapPin className="h-4 w-4 text-slate-400" />
                                        <span className="text-slate-700 dark:text-slate-300">{viewingAppointment.room_name}</span>
                                    </div>
                                </div>
                            </div>

                            <Tabs defaultValue="basic" className="w-full">
                                <TabsList className="grid w-full grid-cols-3">
                                    <TabsTrigger value="basic">Basic Info</TabsTrigger>
                                    <TabsTrigger value="contact">Contact</TabsTrigger>
                                    <TabsTrigger value="notes">Notes</TabsTrigger>
                                </TabsList>

                                <TabsContent value="basic" className="space-y-4">
                                    <div className="grid grid-cols-2 gap-4">
                                        <div>
                                            <Label className="text-sm font-medium text-slate-700 dark:text-slate-300">Appointment Type</Label>
                                            <Badge className={`font-medium ${getTypeColor(viewingAppointment.type)}`}>
                                                {viewingAppointment.type}
                                            </Badge>
                                        </div>
                                        <div>
                                            <Label className="text-sm font-medium text-slate-700 dark:text-slate-300">Priority</Label>
                                            <div className="text-slate-900 dark:text-white">{viewingAppointment.priority}</div>
                                        </div>
                                    </div>
                                    {viewingAppointment.reason && (
                                        <div>
                                            <Label className="text-sm font-medium text-slate-700 dark:text-slate-300">Reason for Visit</Label>
                                            <p className="mt-1 text-slate-600 dark:text-slate-400">{viewingAppointment.reason}</p>
                                        </div>
                                    )}
                                </TabsContent>

                                <TabsContent value="contact" className="space-y-4">
                                    <div className="flex items-center space-x-2">
                                        <Mail className="h-4 w-4 text-slate-400" />
                                        <span className="text-slate-700 dark:text-slate-300">{viewingAppointment.patient_email}</span>
                                    </div>
                                    <div className="flex items-center space-x-2">
                                        <Phone className="h-4 w-4 text-slate-400" />
                                        <span className="text-slate-700 dark:text-slate-300">{viewingAppointment.patient_phone}</span>
                                    </div>
                                </TabsContent>

                                <TabsContent value="notes" className="space-y-4">
                                    {viewingAppointment.notes ? (
                                        <p className="text-slate-600 dark:text-slate-400">{viewingAppointment.notes}</p>
                                    ) : (
                                        <p className="text-slate-500 dark:text-slate-400 italic">No notes available</p>
                                    )}
                                </TabsContent>
                            </Tabs>
                        </div>
                    )}
                    <DialogFooter>
                        <Button variant="outline" onClick={() => setIsViewModalOpen(false)}>
                            Close
                        </Button>
                        <Button onClick={() => {
                            setIsViewModalOpen(false);
                            handleEditAppointment(viewingAppointment!);
                        }}>
                            <Edit className="mr-2 h-4 w-4" />
                            Edit Appointment
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            {/* Calendar View Modal */}
            <Dialog open={isCalendarModalOpen} onOpenChange={setIsCalendarModalOpen}>
                <DialogContent className="max-w-6xl max-h-[90vh] overflow-y-auto">
                    <DialogHeader>
                        <DialogTitle>Appointment Calendar</DialogTitle>
                        <DialogDescription>
                            View all appointments in calendar format
                        </DialogDescription>
                    </DialogHeader>
                    <div className="space-y-6">
                        <div className="grid grid-cols-7 gap-2 text-center">
                            {['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'].map((day) => (
                                <div key={day} className="font-medium text-slate-700 dark:text-slate-300 p-2">
                                    {day}
                                </div>
                            ))}
                        </div>
                        <div className="space-y-2">
                            {calendarData.map((appointment) => (
                                <div key={appointment.id} className="p-3 border rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800">
                                    <div className="flex items-center justify-between">
                                        <div>
                                            <div className="font-medium text-slate-900 dark:text-white">
                                                {appointment.patient} - {appointment.doctor}
                                            </div>
                                            <div className="text-sm text-slate-500 dark:text-slate-400">
                                                {new Date(appointment.start).toLocaleString()} - {appointment.room}
                                            </div>
                                        </div>
                                        <Badge className={getTypeColor(appointment.type)}>
                                            {appointment.type}
                                        </Badge>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>
                    <DialogFooter>
                        <Button variant="outline" onClick={() => setIsCalendarModalOpen(false)}>
                            Close
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            {/* Delete Confirmation Modal */}
            <Dialog open={isDeleteModalOpen} onOpenChange={setIsDeleteModalOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Delete Appointment</DialogTitle>
                        <DialogDescription>
                            Are you sure you want to delete the appointment for {deletingAppointment?.patient_name}? This action cannot be undone.
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter>
                        <Button variant="outline" onClick={() => setIsDeleteModalOpen(false)}>
                            Cancel
                        </Button>
                        <Button
                            onClick={deleteAppointment}
                            className="bg-red-600 hover:bg-red-700 text-white"
                            disabled={loading}
                        >
                            {loading ? (
                                <>
                                    <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                                    Deleting...
                                </>
                            ) : (
                                <>
                                    <Trash2 className="mr-2 h-4 w-4" />
                                    Delete
                                </>
                            )}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </AppLayout>
    );
}

