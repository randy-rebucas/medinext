import { Head, usePage } from '@inertiajs/react';
import { useState, useEffect } from 'react';
import AppLayout from '@/layouts/app-layout';
import { adminSchedules } from '@/routes';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Admin Dashboard',
        href: '/admin/dashboard',
    },
    {
        title: 'Schedule Management',
        href: adminSchedules(),
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
    Clock,
    Plus,
    Search,
    Edit,
    Eye,
    Calendar,
    Stethoscope,
    Building2,
    MoreHorizontal,
    Activity
} from 'lucide-react';

interface Schedule {
    id: number;
    clinic_id: number;
    doctor_id: number;
    room_id?: number;
    day_of_week: string;
    start_time: string;
    end_time: string;
    status: string;
    is_recurring: boolean;
    recurring_type: string;
    recurring_interval: number;
    recurring_end_date?: string;
    notes?: string;
    max_appointments: number;
    appointment_duration: number;
    break_duration: number;
    is_active: boolean;
    created_by: number;
    updated_by?: number;
    created_at: string;
    updated_at: string;
    doctor?: {
        id: number;
        name: string;
        email: string;
    };
    room?: {
        id: number;
        name: string;
        type: string;
    };
    appointments?: Array<{
        id: number;
        appointment_date: string;
        start_time: string;
        end_time: string;
    }>;
}

interface Doctor {
    id: number;
    name: string;
    email: string;
}

interface Room {
    id: number;
    name: string;
    type: string;
    status: string;
}

interface PageProps {
    schedules: Schedule[];
    doctors: Doctor[];
    rooms: Room[];
    permissions: string[];
    security: {
        can_create_schedules: boolean;
        can_edit_schedules: boolean;
        can_delete_schedules: boolean;
        current_user_role: string;
        is_superadmin: boolean;
    };
    filters: {
        search: string;
        doctor: string;
        status: string;
        day: string;
    };
    [key: string]: any;
}

export default function ScheduleManagement() {
    const { props } = usePage<PageProps>();
    const [searchTerm, setSearchTerm] = useState(props.filters.search || '');
    const [statusFilter, setStatusFilter] = useState(props.filters.status || 'all');
    const [dayFilter, setDayFilter] = useState(props.filters.day || 'all');
    const [doctorFilter, setDoctorFilter] = useState(props.filters.doctor || 'all');

    const [schedules, setSchedules] = useState<Schedule[]>(props.schedules || []);
    const [doctors, setDoctors] = useState<Doctor[]>(props.doctors || []);
    const [rooms, setRooms] = useState<Room[]>(props.rooms || []);
    const [loading, setLoading] = useState(false);
    const [showCreateModal, setShowCreateModal] = useState(false);
    const [showEditModal, setShowEditModal] = useState(false);
    const [selectedSchedule, setSelectedSchedule] = useState<Schedule | null>(null);

    // Fetch schedules data when filters change
    useEffect(() => {
        const fetchSchedules = async () => {
            setLoading(true);
            try {
                const params = new URLSearchParams();
                if (searchTerm) params.append('search', searchTerm);
                if (statusFilter !== 'all') params.append('status', statusFilter);
                if (dayFilter !== 'all') params.append('day', dayFilter);
                if (doctorFilter !== 'all') params.append('doctor', doctorFilter);

                const response = await fetch(`/admin/schedules?${params.toString()}`, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (response.ok) {
                    const data = await response.json();
                    setSchedules(data.schedules || []);
                } else {
                    console.error('Failed to fetch schedules');
                }
            } catch (error) {
                console.error('Error fetching schedules:', error);
            } finally {
                setLoading(false);
            }
        };

        fetchSchedules();
    }, [searchTerm, statusFilter, dayFilter, doctorFilter]);

    // Helper functions
    const handleEditSchedule = (schedule: Schedule) => {
        setSelectedSchedule(schedule);
        setShowEditModal(true);
    };

    const handleDeleteSchedule = async (scheduleId: number) => {
        if (!confirm('Are you sure you want to delete this schedule?')) return;

        try {
            const response = await fetch(`/admin/schedules/${scheduleId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (response.ok) {
                setSchedules(schedules.filter(s => s.id !== scheduleId));
            } else {
                alert('Failed to delete schedule');
            }
        } catch (error) {
            console.error('Error deleting schedule:', error);
            alert('Failed to delete schedule');
        }
    };

    const formatTime = (time: string) => {
        return new Date(`2000-01-01T${time}`).toLocaleTimeString('en-US', {
            hour: '2-digit',
            minute: '2-digit',
            hour12: true
        });
    };

    const getAppointmentsCount = (schedule: Schedule) => {
        return schedule.appointments?.length || 0;
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Schedule Management - Medinext">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&family=instrument-sans:400,500,600" rel="stylesheet" />
            </Head>

            <div className="min-h-screen bg-gradient-to-br from-slate-50 via-white to-blue-50 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900">
                <div className="space-y-6 p-6">

                    <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                        <CardHeader>
                            <div className="flex items-center justify-between">
                                <div>
                                    <CardTitle className="text-xl font-semibold text-slate-900 dark:text-white">Doctor Schedules</CardTitle>
                                    <CardDescription className="text-slate-600 dark:text-slate-300">
                                        View and manage all doctor schedules
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
                                    {props.security.can_create_schedules && (
                                        <Button
                                            onClick={() => setShowCreateModal(true)}
                                            className="bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white shadow-lg"
                                        >
                                            <Plus className="mr-2 h-4 w-4" />
                                            Add Schedule
                                        </Button>
                                    )}
                                </div>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div className="flex items-center space-x-3 mb-6">
                                <div className="relative flex-1">
                                    <Search className="absolute left-3 top-3 h-4 w-4 text-slate-400" />
                                    <Input
                                        placeholder="Search schedules..."
                                        className="pl-10 h-11 border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-700 dark:text-white"
                                        value={searchTerm}
                                        onChange={(e) => setSearchTerm(e.target.value)}
                                    />
                                </div>
                                <Select value={statusFilter} onValueChange={setStatusFilter}>
                                    <SelectTrigger className="w-[140px] h-11 border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500">
                                        <SelectValue placeholder="Status" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">All Status</SelectItem>
                                        <SelectItem value="Active">Active</SelectItem>
                                        <SelectItem value="On Leave">On Leave</SelectItem>
                                    </SelectContent>
                                </Select>
                                <Select value={doctorFilter} onValueChange={setDoctorFilter}>
                                    <SelectTrigger className="w-[160px] h-11 border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500">
                                        <SelectValue placeholder="Doctor" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">All Doctors</SelectItem>
                                        {doctors.map((doctor) => (
                                            <SelectItem key={doctor.id} value={doctor.id.toString()}>
                                                {doctor.name}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                <Select value={dayFilter} onValueChange={setDayFilter}>
                                    <SelectTrigger className="w-[140px] h-11 border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500">
                                        <SelectValue placeholder="Day" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">All Days</SelectItem>
                                        <SelectItem value="Monday">Monday</SelectItem>
                                        <SelectItem value="Tuesday">Tuesday</SelectItem>
                                        <SelectItem value="Wednesday">Wednesday</SelectItem>
                                        <SelectItem value="Thursday">Thursday</SelectItem>
                                        <SelectItem value="Friday">Friday</SelectItem>
                                        <SelectItem value="Saturday">Saturday</SelectItem>
                                        <SelectItem value="Sunday">Sunday</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <div className="rounded-lg border border-slate-200 dark:border-slate-700 overflow-hidden">
                                <Table>
                                    <TableHeader className="bg-slate-50 dark:bg-slate-800/50">
                                        <TableRow className="border-slate-200 dark:border-slate-700">
                                            <TableHead className="font-semibold text-slate-700 dark:text-slate-300">Doctor</TableHead>
                                            <TableHead className="font-semibold text-slate-700 dark:text-slate-300">Day</TableHead>
                                            <TableHead className="font-semibold text-slate-700 dark:text-slate-300">Time</TableHead>
                                            <TableHead className="font-semibold text-slate-700 dark:text-slate-300">Room</TableHead>
                                            <TableHead className="font-semibold text-slate-700 dark:text-slate-300">Appointments</TableHead>
                                            <TableHead className="font-semibold text-slate-700 dark:text-slate-300">Status</TableHead>
                                            <TableHead className="text-right font-semibold text-slate-700 dark:text-slate-300">Actions</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {loading ? (
                                            <TableRow>
                                                <TableCell colSpan={7} className="text-center py-8">
                                                    <div className="flex items-center justify-center">
                                                        <div className="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
                                                        <span className="ml-2 text-slate-600 dark:text-slate-400">Loading schedules...</span>
                                                    </div>
                                                </TableCell>
                                            </TableRow>
                                        ) : schedules.length === 0 ? (
                                            <TableRow>
                                                <TableCell colSpan={7} className="text-center py-8">
                                                    <div className="text-slate-500 dark:text-slate-400">No schedules found</div>
                                                </TableCell>
                                            </TableRow>
                                        ) : (
                                            schedules.map((schedule) => (
                                                <TableRow key={schedule.id} className="border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors duration-200">
                                                    <TableCell>
                                                        <div className="flex items-center space-x-3">
                                                            <div className="p-2 bg-slate-100 dark:bg-slate-700 rounded-lg">
                                                                <Stethoscope className="h-4 w-4 text-slate-600 dark:text-slate-400" />
                                                            </div>
                                                            <div>
                                                                <div className="font-semibold text-slate-900 dark:text-white">
                                                                    {schedule.doctor?.name || 'Unknown Doctor'}
                                                                </div>
                                                                <div className="text-sm text-slate-500 dark:text-slate-400">
                                                                    {schedule.doctor?.email || ''}
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </TableCell>
                                                    <TableCell>
                                                        <Badge className="font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400">
                                                            {schedule.day_of_week}
                                                        </Badge>
                                                    </TableCell>
                                                    <TableCell>
                                                        <div className="flex items-center text-sm">
                                                            <Clock className="mr-2 h-4 w-4 text-slate-400" />
                                                            <span className="text-slate-700 dark:text-slate-300">
                                                                {formatTime(schedule.start_time)} - {formatTime(schedule.end_time)}
                                                            </span>
                                                        </div>
                                                    </TableCell>
                                                    <TableCell>
                                                        <div className="flex items-center text-sm">
                                                            <Building2 className="mr-2 h-4 w-4 text-slate-400" />
                                                            <span className="text-slate-700 dark:text-slate-300">
                                                                {schedule.room?.name || 'No Room'}
                                                            </span>
                                                        </div>
                                                    </TableCell>
                                                    <TableCell>
                                                        <div className="flex items-center">
                                                            <Activity className="mr-2 h-4 w-4 text-slate-400" />
                                                            <span className="font-medium text-slate-900 dark:text-white">
                                                                {getAppointmentsCount(schedule)}
                                                            </span>
                                                        </div>
                                                    </TableCell>
                                                    <TableCell>
                                                        <Badge
                                                            className={`font-medium ${
                                                                schedule.status === 'Active'
                                                                    ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400'
                                                                    : schedule.status === 'On Leave'
                                                                    ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400'
                                                                    : 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400'
                                                            }`}
                                                        >
                                                            {schedule.status}
                                                        </Badge>
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
                                                            {props.security.can_edit_schedules && (
                                                                <Button
                                                                    variant="ghost"
                                                                    size="sm"
                                                                    title="Edit Schedule"
                                                                    onClick={() => handleEditSchedule(schedule)}
                                                                    className="h-8 w-8 p-0 hover:bg-green-50 dark:hover:bg-green-900/20 hover:text-green-600 dark:hover:text-green-400"
                                                                >
                                                                    <Edit className="h-4 w-4" />
                                                                </Button>
                                                            )}
                                                            {props.security.can_delete_schedules && (
                                                                <Button
                                                                    variant="ghost"
                                                                    size="sm"
                                                                    title="Delete Schedule"
                                                                    onClick={() => handleDeleteSchedule(schedule.id)}
                                                                    className="h-8 w-8 p-0 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-600 dark:hover:text-red-400"
                                                                >
                                                                    <MoreHorizontal className="h-4 w-4" />
                                                                </Button>
                                                            )}
                                                        </div>
                                                    </TableCell>
                                                </TableRow>
                                            ))
                                        )}
                                    </TableBody>
                                </Table>
                            </div>

                            {schedules.length === 0 && !loading && (
                                <div className="text-center py-12">
                                    <div className="mx-auto w-16 h-16 bg-slate-100 dark:bg-slate-700 rounded-full flex items-center justify-center mb-4">
                                        <Calendar className="h-8 w-8 text-slate-400" />
                                    </div>
                                    <h3 className="text-lg font-semibold text-slate-900 dark:text-white mb-2">No schedules found</h3>
                                    <p className="text-slate-500 dark:text-slate-400 mb-4">
                                        {searchTerm || statusFilter !== 'all' || dayFilter !== 'all' || doctorFilter !== 'all'
                                            ? 'Try adjusting your search or filter criteria.'
                                            : 'Get started by creating your first doctor schedule.'}
                                    </p>
                                    {props.security.can_create_schedules && (
                                        <Button 
                                            onClick={() => setShowCreateModal(true)}
                                            className="bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white"
                                        >
                                            <Plus className="mr-2 h-4 w-4" />
                                            Add Schedule
                                        </Button>
                                    )}
                                </div>
                            )}
                        </CardContent>
                    </Card>
                </div>
            </div>

            {/* Create Schedule Modal */}
            {showCreateModal && (
                <CreateScheduleModal
                    doctors={doctors}
                    rooms={rooms}
                    onClose={() => setShowCreateModal(false)}
                    onSuccess={(newSchedule) => {
                        setSchedules([...schedules, newSchedule]);
                        setShowCreateModal(false);
                    }}
                />
            )}

            {/* Edit Schedule Modal */}
            {showEditModal && selectedSchedule && (
                <EditScheduleModal
                    schedule={selectedSchedule}
                    doctors={doctors}
                    rooms={rooms}
                    onClose={() => {
                        setShowEditModal(false);
                        setSelectedSchedule(null);
                    }}
                    onSuccess={(updatedSchedule) => {
                        setSchedules(schedules.map(s => s.id === updatedSchedule.id ? updatedSchedule : s));
                        setShowEditModal(false);
                        setSelectedSchedule(null);
                    }}
                />
            )}
        </AppLayout>
    );
}

// Create Schedule Modal Component
function CreateScheduleModal({ doctors, rooms, onClose, onSuccess }: {
    doctors: Doctor[];
    rooms: Room[];
    onClose: () => void;
    onSuccess: (schedule: Schedule) => void;
}) {
    const [formData, setFormData] = useState({
        doctor_id: '',
        room_id: '',
        day_of_week: '',
        start_time: '',
        end_time: '',
        status: 'Active',
        is_recurring: false,
        recurring_type: 'none',
        recurring_interval: 1,
        recurring_end_date: '',
        notes: '',
        max_appointments: 10,
        appointment_duration: 30,
        break_duration: 0,
        is_active: true,
    });
    const [loading, setLoading] = useState(false);

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setLoading(true);

        try {
            const response = await fetch('/admin/schedules', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify(formData),
            });

            if (response.ok) {
                const data = await response.json();
                onSuccess(data.schedule);
            } else {
                alert('Failed to create schedule');
            }
        } catch (error) {
            console.error('Error creating schedule:', error);
            alert('Failed to create schedule');
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div className="bg-white dark:bg-slate-800 rounded-lg p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto">
                <h2 className="text-xl font-semibold mb-4">Create New Schedule</h2>
                <form onSubmit={handleSubmit} className="space-y-4">
                    <div className="grid grid-cols-2 gap-4">
                        <div>
                            <label className="block text-sm font-medium mb-1">Doctor</label>
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
                        <div>
                            <label className="block text-sm font-medium mb-1">Room</label>
                            <Select value={formData.room_id} onValueChange={(value) => setFormData({...formData, room_id: value})}>
                                <SelectTrigger>
                                    <SelectValue placeholder="Select room" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">No Room</SelectItem>
                                    {rooms.map((room) => (
                                        <SelectItem key={room.id} value={room.id.toString()}>
                                            {room.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>
                    </div>

                    <div className="grid grid-cols-2 gap-4">
                        <div>
                            <label className="block text-sm font-medium mb-1">Day of Week</label>
                            <Select value={formData.day_of_week} onValueChange={(value) => setFormData({...formData, day_of_week: value})}>
                                <SelectTrigger>
                                    <SelectValue placeholder="Select day" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="Monday">Monday</SelectItem>
                                    <SelectItem value="Tuesday">Tuesday</SelectItem>
                                    <SelectItem value="Wednesday">Wednesday</SelectItem>
                                    <SelectItem value="Thursday">Thursday</SelectItem>
                                    <SelectItem value="Friday">Friday</SelectItem>
                                    <SelectItem value="Saturday">Saturday</SelectItem>
                                    <SelectItem value="Sunday">Sunday</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div>
                            <label className="block text-sm font-medium mb-1">Status</label>
                            <Select value={formData.status} onValueChange={(value) => setFormData({...formData, status: value})}>
                                <SelectTrigger>
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="Active">Active</SelectItem>
                                    <SelectItem value="Inactive">Inactive</SelectItem>
                                    <SelectItem value="On Leave">On Leave</SelectItem>
                                    <SelectItem value="Vacation">Vacation</SelectItem>
                                    <SelectItem value="Sick Leave">Sick Leave</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>

                    <div className="grid grid-cols-2 gap-4">
                        <div>
                            <label className="block text-sm font-medium mb-1">Start Time</label>
                            <Input
                                type="time"
                                value={formData.start_time}
                                onChange={(e) => setFormData({...formData, start_time: e.target.value})}
                                required
                            />
                        </div>
                        <div>
                            <label className="block text-sm font-medium mb-1">End Time</label>
                            <Input
                                type="time"
                                value={formData.end_time}
                                onChange={(e) => setFormData({...formData, end_time: e.target.value})}
                                required
                            />
                        </div>
                    </div>

                    <div className="flex justify-end space-x-2">
                        <Button type="button" variant="outline" onClick={onClose}>
                            Cancel
                        </Button>
                        <Button type="submit" disabled={loading}>
                            {loading ? 'Creating...' : 'Create Schedule'}
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    );
}

// Edit Schedule Modal Component
function EditScheduleModal({ schedule, doctors, rooms, onClose, onSuccess }: {
    schedule: Schedule;
    doctors: Doctor[];
    rooms: Room[];
    onClose: () => void;
    onSuccess: (schedule: Schedule) => void;
}) {
    const [formData, setFormData] = useState({
        doctor_id: schedule.doctor_id.toString(),
        room_id: schedule.room_id?.toString() || '',
        day_of_week: schedule.day_of_week,
        start_time: schedule.start_time,
        end_time: schedule.end_time,
        status: schedule.status,
        is_recurring: schedule.is_recurring,
        recurring_type: schedule.recurring_type,
        recurring_interval: schedule.recurring_interval,
        recurring_end_date: schedule.recurring_end_date || '',
        notes: schedule.notes || '',
        max_appointments: schedule.max_appointments,
        appointment_duration: schedule.appointment_duration,
        break_duration: schedule.break_duration,
        is_active: schedule.is_active,
    });
    const [loading, setLoading] = useState(false);

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setLoading(true);

        try {
            const response = await fetch(`/admin/schedules/${schedule.id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify(formData),
            });

            if (response.ok) {
                const data = await response.json();
                onSuccess(data.schedule);
            } else {
                alert('Failed to update schedule');
            }
        } catch (error) {
            console.error('Error updating schedule:', error);
            alert('Failed to update schedule');
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div className="bg-white dark:bg-slate-800 rounded-lg p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto">
                <h2 className="text-xl font-semibold mb-4">Edit Schedule</h2>
                <form onSubmit={handleSubmit} className="space-y-4">
                    <div className="grid grid-cols-2 gap-4">
                        <div>
                            <label className="block text-sm font-medium mb-1">Doctor</label>
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
                        <div>
                            <label className="block text-sm font-medium mb-1">Room</label>
                            <Select value={formData.room_id} onValueChange={(value) => setFormData({...formData, room_id: value})}>
                                <SelectTrigger>
                                    <SelectValue placeholder="Select room" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">No Room</SelectItem>
                                    {rooms.map((room) => (
                                        <SelectItem key={room.id} value={room.id.toString()}>
                                            {room.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>
                    </div>

                    <div className="grid grid-cols-2 gap-4">
                        <div>
                            <label className="block text-sm font-medium mb-1">Day of Week</label>
                            <Select value={formData.day_of_week} onValueChange={(value) => setFormData({...formData, day_of_week: value})}>
                                <SelectTrigger>
                                    <SelectValue placeholder="Select day" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="Monday">Monday</SelectItem>
                                    <SelectItem value="Tuesday">Tuesday</SelectItem>
                                    <SelectItem value="Wednesday">Wednesday</SelectItem>
                                    <SelectItem value="Thursday">Thursday</SelectItem>
                                    <SelectItem value="Friday">Friday</SelectItem>
                                    <SelectItem value="Saturday">Saturday</SelectItem>
                                    <SelectItem value="Sunday">Sunday</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div>
                            <label className="block text-sm font-medium mb-1">Status</label>
                            <Select value={formData.status} onValueChange={(value) => setFormData({...formData, status: value})}>
                                <SelectTrigger>
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="Active">Active</SelectItem>
                                    <SelectItem value="Inactive">Inactive</SelectItem>
                                    <SelectItem value="On Leave">On Leave</SelectItem>
                                    <SelectItem value="Vacation">Vacation</SelectItem>
                                    <SelectItem value="Sick Leave">Sick Leave</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>

                    <div className="grid grid-cols-2 gap-4">
                        <div>
                            <label className="block text-sm font-medium mb-1">Start Time</label>
                            <Input
                                type="time"
                                value={formData.start_time}
                                onChange={(e) => setFormData({...formData, start_time: e.target.value})}
                                required
                            />
                        </div>
                        <div>
                            <label className="block text-sm font-medium mb-1">End Time</label>
                            <Input
                                type="time"
                                value={formData.end_time}
                                onChange={(e) => setFormData({...formData, end_time: e.target.value})}
                                required
                            />
                        </div>
                    </div>

                    <div className="flex justify-end space-x-2">
                        <Button type="button" variant="outline" onClick={onClose}>
                            Cancel
                        </Button>
                        <Button type="submit" disabled={loading}>
                            {loading ? 'Updating...' : 'Update Schedule'}
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    );
}
