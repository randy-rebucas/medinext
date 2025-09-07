import { Head } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '@/layouts/app-layout';
import { adminAppointments } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';

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
    X
} from 'lucide-react';

export default function AdminAppointments() {
    const [searchTerm, setSearchTerm] = useState('');
    const [statusFilter, setStatusFilter] = useState('all');
    const [typeFilter, setTypeFilter] = useState('all');
    const [isAddModalOpen, setIsAddModalOpen] = useState(false);
    const [isEditModalOpen, setIsEditModalOpen] = useState(false);
    const [editingAppointment, setEditingAppointment] = useState<{
        id: number;
        patient: string;
        patientEmail: string;
        patientPhone: string;
        doctor: string;
        doctorSpecialization: string;
        date: string;
        time: string;
        type: string;
        status: string;
        room: string;
        duration: string;
        notes: string;
    } | null>(null);
    const [formData, setFormData] = useState({
        patient: '',
        patientEmail: '',
        patientPhone: '',
        doctor: '',
        date: '',
        time: '',
        type: '',
        status: 'Scheduled',
        room: '',
        duration: '30',
        notes: '',
        reason: ''
    });

    const appointments = [
        {
            id: 1,
            patient: 'John Doe',
            patientEmail: 'john.doe@email.com',
            patientPhone: '+1 (555) 123-4567',
            doctor: 'Dr. Sarah Johnson',
            doctorSpecialization: 'Cardiology',
            date: '2024-01-15',
            time: '09:00 AM',
            type: 'Consultation',
            status: 'Scheduled',
            room: 'Room 101',
            duration: '30 min',
            notes: 'Regular check-up'
        },
        {
            id: 2,
            patient: 'Jane Smith',
            patientEmail: 'jane.smith@email.com',
            patientPhone: '+1 (555) 234-5678',
            doctor: 'Dr. Michael Brown',
            doctorSpecialization: 'Pediatrics',
            date: '2024-01-15',
            time: '10:30 AM',
            type: 'Follow-up',
            status: 'Confirmed',
            room: 'Room 102',
            duration: '45 min',
            notes: 'Post-treatment follow-up'
        },
        {
            id: 3,
            patient: 'Bob Johnson',
            patientEmail: 'bob.johnson@email.com',
            patientPhone: '+1 (555) 345-6789',
            doctor: 'Dr. Emily Davis',
            doctorSpecialization: 'Dermatology',
            date: '2024-01-15',
            time: '11:15 AM',
            type: 'Check-up',
            status: 'In Progress',
            room: 'Room 103',
            duration: '20 min',
            notes: 'Annual skin examination'
        },
        {
            id: 4,
            patient: 'Alice Brown',
            patientEmail: 'alice.brown@email.com',
            patientPhone: '+1 (555) 456-7890',
            doctor: 'Dr. James Wilson',
            doctorSpecialization: 'Orthopedics',
            date: '2024-01-15',
            time: '02:00 PM',
            type: 'Consultation',
            status: 'Completed',
            room: 'Room 104',
            duration: '60 min',
            notes: 'Knee pain consultation'
        },
        {
            id: 5,
            patient: 'David Wilson',
            patientEmail: 'david.wilson@email.com',
            patientPhone: '+1 (555) 567-8901',
            doctor: 'Dr. Jennifer Lee',
            doctorSpecialization: 'Neurology',
            date: '2024-01-15',
            time: '03:30 PM',
            type: 'Follow-up',
            status: 'Scheduled',
            room: 'Room 105',
            duration: '30 min',
            notes: 'Medication review'
        }
    ];

    const filteredAppointments = appointments.filter(appointment => {
        const matchesSearch = appointment.patient.toLowerCase().includes(searchTerm.toLowerCase()) ||
                            appointment.doctor.toLowerCase().includes(searchTerm.toLowerCase()) ||
                            appointment.type.toLowerCase().includes(searchTerm.toLowerCase());
        const matchesStatus = statusFilter === 'all' || appointment.status === statusFilter;
        const matchesType = typeFilter === 'all' || appointment.type === typeFilter;

        return matchesSearch && matchesStatus && matchesType;
    });

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
        setFormData({
            patient: '',
            patientEmail: '',
            patientPhone: '',
            doctor: '',
            date: '',
            time: '',
            type: '',
            status: 'Scheduled',
            room: '',
            duration: '30',
            notes: '',
            reason: ''
        });
    };

    const handleEditAppointment = (appointment: {
        id: number;
        patient: string;
        patientEmail: string;
        patientPhone: string;
        doctor: string;
        doctorSpecialization: string;
        date: string;
        time: string;
        type: string;
        status: string;
        room: string;
        duration: string;
        notes: string;
    }) => {
        setEditingAppointment(appointment);
        setFormData({
            patient: appointment.patient,
            patientEmail: appointment.patientEmail,
            patientPhone: appointment.patientPhone,
            doctor: appointment.doctor,
            date: appointment.date,
            time: appointment.time,
            type: appointment.type,
            status: appointment.status,
            room: appointment.room,
            duration: appointment.duration.replace(' min', ''),
            notes: appointment.notes,
            reason: ''
        });
        setIsEditModalOpen(true);
    };

    const handleSaveAppointment = () => {
        // Here you would typically make an API call to save the appointment
        console.log('Saving appointment:', formData);
        setIsAddModalOpen(false);
        setIsEditModalOpen(false);
        setEditingAppointment(null);
    };

    const handleCancel = () => {
        setIsAddModalOpen(false);
        setIsEditModalOpen(false);
        setEditingAppointment(null);
        setFormData({
            patient: '',
            patientEmail: '',
            patientPhone: '',
            doctor: '',
            date: '',
            time: '',
            type: '',
            status: 'Scheduled',
            room: '',
            duration: '30',
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
                                                                {appointment.patient.split(' ').map(n => n[0]).join('')}
                                                            </span>
                                                        </div>
                                                        <div>
                                                            <div className="font-semibold text-slate-900 dark:text-white">{appointment.patient}</div>
                                                            <div className="text-sm text-slate-500 dark:text-slate-400">{appointment.patientEmail}</div>
                                                        </div>
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    <div className="flex items-center space-x-3">
                                                        <div className="p-2 bg-slate-100 dark:bg-slate-700 rounded-lg">
                                                            <Stethoscope className="h-4 w-4 text-slate-600 dark:text-slate-400" />
                                                        </div>
                                                        <div>
                                                            <div className="font-medium text-slate-900 dark:text-white">{appointment.doctor}</div>
                                                            <div className="text-sm text-slate-500 dark:text-slate-400">{appointment.doctorSpecialization}</div>
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
                                                                {appointment.time} • {appointment.duration}
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
                                                        <span className="font-medium text-slate-900 dark:text-white">{appointment.room}</span>
                                                    </div>
                                                </TableCell>
                                                <TableCell className="text-right">
                                                    <div className="flex items-center justify-end space-x-2">
                                                        <Button
                                                            variant="ghost"
                                                            size="sm"
                                                            title="View Details"
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
                                                            title="More Options"
                                                            className="h-8 w-8 p-0 hover:bg-slate-50 dark:hover:bg-slate-700 hover:text-slate-600 dark:hover:text-slate-300"
                                                        >
                                                            <MoreHorizontal className="h-4 w-4" />
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
                                <Label htmlFor="patient">Patient Name *</Label>
                                <Input
                                    id="patient"
                                    value={formData.patient}
                                    onChange={(e) => setFormData({...formData, patient: e.target.value})}
                                    placeholder="Enter patient name"
                                />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="patientEmail">Patient Email *</Label>
                                <Input
                                    id="patientEmail"
                                    type="email"
                                    value={formData.patientEmail}
                                    onChange={(e) => setFormData({...formData, patientEmail: e.target.value})}
                                    placeholder="patient@email.com"
                                />
                            </div>
                        </div>
                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="patientPhone">Patient Phone *</Label>
                                <Input
                                    id="patientPhone"
                                    value={formData.patientPhone}
                                    onChange={(e) => setFormData({...formData, patientPhone: e.target.value})}
                                    placeholder="+1 (555) 123-4567"
                                />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="doctor">Doctor *</Label>
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
                                <Label htmlFor="date">Date *</Label>
                                <Input
                                    id="date"
                                    type="date"
                                    value={formData.date}
                                    onChange={(e) => setFormData({...formData, date: e.target.value})}
                                />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="time">Time *</Label>
                                <Input
                                    id="time"
                                    type="time"
                                    value={formData.time}
                                    onChange={(e) => setFormData({...formData, time: e.target.value})}
                                />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="duration">Duration (minutes) *</Label>
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
                                <Label htmlFor="type">Appointment Type *</Label>
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
                                <Label htmlFor="room">Room *</Label>
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
                        <Button onClick={handleSaveAppointment}>
                            <Save className="mr-2 h-4 w-4" />
                            Schedule Appointment
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
                        <Button onClick={handleSaveAppointment}>
                            <Save className="mr-2 h-4 w-4" />
                            Update Appointment
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </AppLayout>
    );
}
