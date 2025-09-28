import { Head } from '@inertiajs/react';
import { useState, useEffect } from 'react';
import AppLayout from '@/layouts/app-layout';
import { adminRooms } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Checkbox } from '@/components/ui/checkbox';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Admin Dashboard',
        href: '/admin/dashboard',
    },
    {
        title: 'Room Management',
        href: adminRooms(),
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
    Building2,
    Plus,
    Search,
    Edit,
    Eye,
    Clock,
    Stethoscope,
    Calendar,
    MoreHorizontal,
    Activity,
    Wrench,
    Users,
    Save,
    X,
    MapPin,
    Monitor,
    Thermometer,
    Syringe
} from 'lucide-react';

// Simple toast implementation
const toast = {
    success: (message: string) => {
        const notification = document.createElement('div');
        notification.className = 'fixed top-4 right-4 z-50 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg transform transition-all duration-300 ease-in-out';
        notification.textContent = message;
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 100);
        
        setTimeout(() => {
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => {
                document.body.removeChild(notification);
            }, 300);
        }, 3000);
    },
    error: (message: string) => {
        const notification = document.createElement('div');
        notification.className = 'fixed top-4 right-4 z-50 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg transform transition-all duration-300 ease-in-out';
        notification.textContent = message;
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 100);
        
        setTimeout(() => {
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => {
                document.body.removeChild(notification);
            }, 300);
        }, 3000);
    },
};

interface Room {
    id: number;
    name: string;
    type: string;
    capacity: number;
    status: string;
    equipment: string[];
    nextAppointment?: string;
    doctor?: string;
}

export default function RoomManagement() {
    const [searchTerm, setSearchTerm] = useState('');
    const [statusFilter, setStatusFilter] = useState('all');
    const [typeFilter, setTypeFilter] = useState('all');
    const [isAddModalOpen, setIsAddModalOpen] = useState(false);
    const [isEditModalOpen, setIsEditModalOpen] = useState(false);
    const [isViewModalOpen, setIsViewModalOpen] = useState(false);
    const [editingRoom, setEditingRoom] = useState<Room | null>(null);
    const [viewingRoom, setViewingRoom] = useState<Room | null>(null);
    const [formData, setFormData] = useState({
        name: '',
        type: '',
        capacity: '1',
        status: 'Available',
        location: '',
        description: '',
        equipment: [] as string[],
        maintenanceNotes: '',
        specialRequirements: ''
    });

    const [rooms, setRooms] = useState<Room[]>([]);
    const [loading, setLoading] = useState(true);
    const [saving, setSaving] = useState(false);
    const [errors, setErrors] = useState<Record<string, string>>({});
    const [roomStats, setRoomStats] = useState({
        total_rooms: 0,
        available_rooms: 0,
        occupied_rooms: 0,
        maintenance_rooms: 0,
        rooms_by_type: {} as Record<string, number>,
        rooms_needing_maintenance: 0
    });

    // Fetch rooms data from database
    useEffect(() => {
        const fetchRooms = async () => {
            try {
                setLoading(true);
                const response = await fetch('/admin/rooms', {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (response.ok) {
                    const data = await response.json();
                    console.log('Fetched rooms data:', data);
                    setRooms(data.rooms || data.data || []);
                } else {
                    const errorData = await response.json().catch(() => ({}));
                    console.error('Failed to fetch rooms:', response.status, response.statusText, errorData);
                    toast.error(errorData.message || 'Failed to load rooms. Please try again.');
                    setRooms([]);
                }
            } catch (error) {
                console.error('Error fetching rooms:', error);
                toast.error('Network error. Please check your connection and try again.');
                setRooms([]);
            } finally {
                setLoading(false);
            }
        };

        fetchRooms();
        fetchRoomStats();
    }, []);

    // Auto-refresh rooms data every 30 seconds
    useEffect(() => {
        const interval = setInterval(() => {
            if (!loading && !saving) {
                fetchRooms();
            }
        }, 30000);

        return () => clearInterval(interval);
    }, [loading, saving]);

    const fetchRooms = async () => {
        try {
            const response = await fetch('/admin/rooms', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (response.ok) {
                const data = await response.json();
                setRooms(data.rooms || data.data || []);
            }
        } catch (error) {
            console.error('Error refreshing rooms:', error);
        }
    };

    const fetchRoomStats = async () => {
        try {
            const response = await fetch('/admin/rooms/statistics/overview', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (response.ok) {
                const data = await response.json();
                setRoomStats(data.data || roomStats);
            }
        } catch (error) {
            console.error('Error fetching room statistics:', error);
        }
    };


    const filteredRooms = rooms.filter(room => {
        const matchesSearch = room.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
                            room.type.toLowerCase().includes(searchTerm.toLowerCase());
        const matchesStatus = statusFilter === 'all' || room.status === statusFilter;
        const matchesType = typeFilter === 'all' || room.type === typeFilter;

        return matchesSearch && matchesStatus && matchesType;
    });

    const handleAddRoom = () => {
        setIsAddModalOpen(true);
        setErrors({});
        setFormData({
            name: '',
            type: '',
            capacity: '1',
            status: 'Available',
            location: '',
            description: '',
            equipment: [],
            maintenanceNotes: '',
            specialRequirements: ''
        });
    };

    const handleEditRoom = (room: Room) => {
        setEditingRoom(room);
        setErrors({});
        setFormData({
            name: room.name,
            type: room.type,
            capacity: room.capacity.toString(),
            status: room.status,
            location: '',
            description: '',
            equipment: room.equipment,
            maintenanceNotes: '',
            specialRequirements: ''
        });
        setIsEditModalOpen(true);
    };

    const handleViewRoom = (room: Room) => {
        setViewingRoom(room);
        setIsViewModalOpen(true);
    };

    const handleSaveRoom = async () => {
        setSaving(true);
        setErrors({});

        // Basic validation
        const validationErrors: Record<string, string> = {};
        if (!formData.name.trim()) validationErrors.name = 'Room name is required';
        if (!formData.type.trim()) validationErrors.type = 'Room type is required';
        if (!formData.capacity || isNaN(parseInt(formData.capacity))) {
            validationErrors.capacity = 'Capacity is required and must be a valid number';
        }

        if (Object.keys(validationErrors).length > 0) {
            setErrors(validationErrors);
            setSaving(false);
            return;
        }

        try {
            const url = editingRoom ? `/admin/rooms/${editingRoom.id}` : '/admin/rooms';
            
            // Prepare data with proper types
            const roomData = {
                name: formData.name.trim(),
                type: formData.type.trim(),
                capacity: parseInt(formData.capacity),
                status: formData.status,
                location: formData.location.trim(),
                description: formData.description.trim(),
                equipment: formData.equipment,
                maintenance_notes: formData.maintenanceNotes.trim(),
                special_requirements: formData.specialRequirements.trim(),
                _method: editingRoom ? 'PUT' : 'POST'
            };

            console.log('Sending room data:', roomData);
            
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify(roomData),
            });

            console.log('Response status:', response.status);
            console.log('Response ok:', response.ok);
            
            if (response.ok) {
                try {
                    const result = await response.json();
                    console.log('Success response:', result);
                    
                    // Refresh rooms data
                    const roomsResponse = await fetch('/admin/rooms', {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });
                    
                    if (roomsResponse.ok) {
                        const data = await roomsResponse.json();
                        console.log('Rooms data:', data);
                        setRooms(data.rooms || data.data || []);
                    } else {
                        console.warn('Failed to refresh rooms data, but room was saved');
                    }
                    
                    toast.success(editingRoom ? 'Room updated successfully!' : 'Room created successfully!');
                    handleCancel();
                } catch (parseError) {
                    console.error('Error parsing success response:', parseError);
                    toast.error('Room saved but failed to refresh data');
                    handleCancel();
                }
            } else {
                try {
                    const errorData = await response.json();
                    console.error('Error response:', errorData);
                    
                    if (errorData.errors) {
                        setErrors(errorData.errors);
                    } else if (errorData.message) {
                        toast.error(errorData.message);
                    } else {
                        toast.error(`Failed to save room (${response.status})`);
                    }
                } catch (parseError) {
                    console.error('Error parsing error response:', parseError);
                    toast.error(`Failed to save room (${response.status})`);
                }
            }
        } catch (error) {
            console.error('Error saving room:', error);
            toast.error('An error occurred while saving the room');
        } finally {
            setSaving(false);
        }
    };

    const handleCancel = () => {
        setIsAddModalOpen(false);
        setIsEditModalOpen(false);
        setIsViewModalOpen(false);
        setEditingRoom(null);
        setViewingRoom(null);
        setErrors({});
        setFormData({
            name: '',
            type: '',
            capacity: '1',
            status: 'Available',
            location: '',
            description: '',
            equipment: [],
            maintenanceNotes: '',
            specialRequirements: ''
        });
    };

    const handleEquipmentChange = (equipment: string, checked: boolean) => {
        if (checked) {
            setFormData(prev => ({
                ...prev,
                equipment: [...prev.equipment, equipment]
            }));
        } else {
            setFormData(prev => ({
                ...prev,
                equipment: prev.equipment.filter(item => item !== equipment)
            }));
        }
    };

    const handleStatusChange = async (roomId: number, newStatus: string, notes?: string) => {
        try {
            setSaving(true);
            const response = await fetch(`/admin/rooms/${roomId}/status`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({
                    status: newStatus,
                    notes: notes || ''
                }),
            });

            if (response.ok) {
                const result = await response.json();
                toast.success('Room status updated successfully!');
                // Refresh rooms data
                await fetchRooms();
            } else {
                const errorData = await response.json().catch(() => ({}));
                toast.error(errorData.message || 'Failed to update room status');
            }
        } catch (error) {
            console.error('Error updating room status:', error);
            toast.error('An error occurred while updating room status');
        } finally {
            setSaving(false);
        }
    };

    const handleDeleteRoom = async (roomId: number) => {
        if (!confirm('Are you sure you want to delete this room? This action cannot be undone.')) {
            return;
        }

        try {
            setSaving(true);
            const response = await fetch(`/admin/rooms/${roomId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (response.ok) {
                toast.success('Room deleted successfully!');
                // Refresh rooms data
                await fetchRooms();
            } else {
                const errorData = await response.json().catch(() => ({}));
                toast.error(errorData.message || 'Failed to delete room');
            }
        } catch (error) {
            console.error('Error deleting room:', error);
            toast.error('An error occurred while deleting the room');
        } finally {
            setSaving(false);
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Room Management - Medinext">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&family=instrument-sans:400,500,600" rel="stylesheet" />
            </Head>

            <div className="min-h-screen bg-gradient-to-br from-slate-50 via-white to-blue-50 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900">
                <div className="space-y-6 p-6">
                    
                    {/* Room Statistics */}
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                            <CardContent className="p-4">
                                <div className="flex items-center justify-between">
                                    <div>
                                        <p className="text-sm font-medium text-slate-600 dark:text-slate-400">Total Rooms</p>
                                        <p className="text-2xl font-bold text-slate-900 dark:text-white">{roomStats.total_rooms}</p>
                                    </div>
                                    <div className="h-10 w-10 rounded-lg bg-gradient-to-r from-blue-500 to-purple-500 flex items-center justify-center">
                                        <Building2 className="h-5 w-5 text-white" />
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                        
                        <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                            <CardContent className="p-4">
                                <div className="flex items-center justify-between">
                                    <div>
                                        <p className="text-sm font-medium text-slate-600 dark:text-slate-400">Available</p>
                                        <p className="text-2xl font-bold text-green-600 dark:text-green-400">{roomStats.available_rooms}</p>
                                    </div>
                                    <div className="h-10 w-10 rounded-lg bg-gradient-to-r from-green-500 to-emerald-500 flex items-center justify-center">
                                        <Activity className="h-5 w-5 text-white" />
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                        
                        <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                            <CardContent className="p-4">
                                <div className="flex items-center justify-between">
                                    <div>
                                        <p className="text-sm font-medium text-slate-600 dark:text-slate-400">Occupied</p>
                                        <p className="text-2xl font-bold text-orange-600 dark:text-orange-400">{roomStats.occupied_rooms}</p>
                                    </div>
                                    <div className="h-10 w-10 rounded-lg bg-gradient-to-r from-orange-500 to-red-500 flex items-center justify-center">
                                        <Users className="h-5 w-5 text-white" />
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                        
                        <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                            <CardContent className="p-4">
                                <div className="flex items-center justify-between">
                                    <div>
                                        <p className="text-sm font-medium text-slate-600 dark:text-slate-400">Maintenance</p>
                                        <p className="text-2xl font-bold text-red-600 dark:text-red-400">{roomStats.maintenance_rooms}</p>
                                    </div>
                                    <div className="h-10 w-10 rounded-lg bg-gradient-to-r from-red-500 to-pink-500 flex items-center justify-center">
                                        <Wrench className="h-5 w-5 text-white" />
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                        <CardHeader>
                            <div className="flex items-center justify-between">
                                <div>
                                    <CardTitle className="text-xl font-semibold text-slate-900 dark:text-white">Room Directory</CardTitle>
                                    <CardDescription className="text-slate-600 dark:text-slate-300">
                                        View and manage all rooms in your clinic
                                    </CardDescription>
                                </div>
                                <div className="flex space-x-3">
                                    <Button
                                        variant="outline"
                                        onClick={() => {
                                            fetchRooms();
                                            fetchRoomStats();
                                        }}
                                        disabled={loading || saving}
                                        className="border-slate-300 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-700"
                                    >
                                        {loading ? (
                                            <div className="mr-2 h-4 w-4 animate-spin rounded-full border-2 border-slate-400 border-t-transparent" />
                                        ) : (
                                            <Activity className="mr-2 h-4 w-4" />
                                        )}
                                        Refresh
                                    </Button>
                                    <Button
                                        onClick={handleAddRoom}
                                        className="bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white shadow-lg"
                                    >
                                        <Plus className="mr-2 h-4 w-4" />
                                        Add Room
                                    </Button>
                                </div>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div className="flex items-center space-x-3 mb-6">
                                <div className="relative flex-1">
                                    <Search className="absolute left-3 top-3 h-4 w-4 text-slate-400" />
                                    <Input
                                        placeholder="Search rooms..."
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
                                        <SelectItem value="Available">Available</SelectItem>
                                        <SelectItem value="Occupied">Occupied</SelectItem>
                                        <SelectItem value="Maintenance">Maintenance</SelectItem>
                                    </SelectContent>
                                </Select>
                                <Select value={typeFilter} onValueChange={setTypeFilter}>
                                    <SelectTrigger className="w-[160px] h-11 border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500">
                                        <SelectValue placeholder="Type" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">All Types</SelectItem>
                                        <SelectItem value="Consultation">Consultation</SelectItem>
                                        <SelectItem value="Examination">Examination</SelectItem>
                                        <SelectItem value="Procedure">Procedure</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <div className="rounded-lg border border-slate-200 dark:border-slate-700 overflow-hidden">
                                <Table>
                                    <TableHeader className="bg-slate-50 dark:bg-slate-800/50">
                                        <TableRow className="border-slate-200 dark:border-slate-700">
                                            <TableHead className="font-semibold text-slate-700 dark:text-slate-300">Room</TableHead>
                                            <TableHead className="font-semibold text-slate-700 dark:text-slate-300">Type</TableHead>
                                            <TableHead className="font-semibold text-slate-700 dark:text-slate-300">Capacity</TableHead>
                                            <TableHead className="font-semibold text-slate-700 dark:text-slate-300">Status</TableHead>
                                            <TableHead className="font-semibold text-slate-700 dark:text-slate-300">Equipment</TableHead>
                                            <TableHead className="font-semibold text-slate-700 dark:text-slate-300">Next Appointment</TableHead>
                                            <TableHead className="text-right font-semibold text-slate-700 dark:text-slate-300">Actions</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {loading ? (
                                            <TableRow>
                                                <TableCell colSpan={7} className="text-center py-12">
                                                    <div className="flex flex-col items-center justify-center space-y-4">
                                                        <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                                                        <div className="text-center">
                                                            <p className="text-slate-600 dark:text-slate-300 font-medium">Loading rooms...</p>
                                                            <p className="text-sm text-slate-500 dark:text-slate-400">Please wait while we fetch the latest data</p>
                                                        </div>
                                                    </div>
                                                </TableCell>
                                            </TableRow>
                                        ) : (
                                            filteredRooms.map((room) => (
                                            <TableRow key={room.id} className="border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors duration-200">
                                                <TableCell>
                                                    <div className="flex items-center space-x-3">
                                                        <div className="h-10 w-10 rounded-lg bg-gradient-to-r from-blue-500 to-purple-500 flex items-center justify-center shadow-md">
                                                            <Building2 className="h-5 w-5 text-white" />
                                                        </div>
                                                        <div>
                                                            <div className="font-semibold text-slate-900 dark:text-white">{room.name}</div>
                                                            <div className="text-sm text-slate-500 dark:text-slate-400">Room ID: {room.id}</div>
                                                        </div>
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    <Badge className="font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400">
                                                        {room.type}
                                                    </Badge>
                                                </TableCell>
                                                <TableCell>
                                                    <div className="flex items-center">
                                                        <Users className="mr-2 h-4 w-4 text-slate-400" />
                                                        <span className="font-medium text-slate-900 dark:text-white">{room.capacity}</span>
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    <div className="flex items-center space-x-2">
                                                        <Badge
                                                            className={`font-medium ${
                                                                room.status === 'Available'
                                                                    ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400'
                                                                    : room.status === 'Occupied'
                                                                    ? 'bg-orange-100 text-orange-800 dark:bg-orange-900/20 dark:text-orange-400'
                                                                    : room.status === 'Maintenance'
                                                                    ? 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400'
                                                                    : room.status === 'Cleaning'
                                                                    ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400'
                                                                    : 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400'
                                                            }`}
                                                        >
                                                            {room.status}
                                                        </Badge>
                                                        <Select 
                                                            value={room.status} 
                                                            onValueChange={(value) => handleStatusChange(room.id, value)}
                                                        >
                                                            <SelectTrigger className="w-32 h-6 text-xs">
                                                                <SelectValue />
                                                            </SelectTrigger>
                                                            <SelectContent>
                                                                <SelectItem value="Available">Available</SelectItem>
                                                                <SelectItem value="Occupied">Occupied</SelectItem>
                                                                <SelectItem value="Maintenance">Maintenance</SelectItem>
                                                                <SelectItem value="Cleaning">Cleaning</SelectItem>
                                                                <SelectItem value="Out of Service">Out of Service</SelectItem>
                                                            </SelectContent>
                                                        </Select>
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    <div className="text-sm text-slate-700 dark:text-slate-300">
                                                        {room.equipment.slice(0, 2).join(', ')}
                                                        {room.equipment.length > 2 && ` +${room.equipment.length - 2} more`}
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    <div className="space-y-1">
                                                        <div className="flex items-center text-sm">
                                                            <Calendar className="mr-2 h-4 w-4 text-slate-400" />
                                                            <span className="text-slate-700 dark:text-slate-300">{room.nextAppointment}</span>
                                                        </div>
                                                        <div className="flex items-center text-xs">
                                                            <Stethoscope className="mr-2 h-3 w-3 text-slate-400" />
                                                            <span className="text-slate-500 dark:text-slate-400">{room.doctor}</span>
                                                        </div>
                                                    </div>
                                                </TableCell>
                                                <TableCell className="text-right">
                                                    <div className="flex items-center justify-end space-x-2">
                                                        <Button
                                                            variant="ghost"
                                                            size="sm"
                                                            title="View Details"
                                                            onClick={() => handleViewRoom(room)}
                                                            className="h-8 w-8 p-0 hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:text-blue-600 dark:hover:text-blue-400"
                                                        >
                                                            <Eye className="h-4 w-4" />
                                                        </Button>
                                                        <Button
                                                            variant="ghost"
                                                            size="sm"
                                                            title="Edit Room"
                                                            onClick={() => handleEditRoom(room)}
                                                            className="h-8 w-8 p-0 hover:bg-green-50 dark:hover:bg-green-900/20 hover:text-green-600 dark:hover:text-green-400"
                                                        >
                                                            <Edit className="h-4 w-4" />
                                                        </Button>
                                                        <Button
                                                            variant="ghost"
                                                            size="sm"
                                                            title="Delete Room"
                                                            onClick={() => handleDeleteRoom(room.id)}
                                                            className="h-8 w-8 p-0 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-600 dark:hover:text-red-400"
                                                        >
                                                            <X className="h-4 w-4" />
                                                        </Button>
                                                    </div>
                                                </TableCell>
                                            </TableRow>
                                        ))
                                        )}
                                    </TableBody>
                                </Table>
                            </div>

                            {filteredRooms.length === 0 && !loading && (
                                <div className="text-center py-16">
                                    <div className="mx-auto w-20 h-20 bg-gradient-to-r from-blue-100 to-purple-100 dark:from-blue-900/20 dark:to-purple-900/20 rounded-full flex items-center justify-center mb-6">
                                        <Building2 className="h-10 w-10 text-blue-600 dark:text-blue-400" />
                                    </div>
                                    <h3 className="text-xl font-semibold text-slate-900 dark:text-white mb-3">
                                        {rooms.length === 0 ? 'No rooms yet' : 'No rooms found'}
                                    </h3>
                                    <p className="text-slate-500 dark:text-slate-400 mb-6 max-w-md mx-auto">
                                        {rooms.length === 0 
                                            ? 'Get started by adding your first room to organize your clinic space efficiently.'
                                            : 'Try adjusting your search or filter criteria to find the rooms you\'re looking for.'
                                        }
                                    </p>
                                    <div className="flex flex-col sm:flex-row gap-3 justify-center">
                                        <Button 
                                            onClick={handleAddRoom}
                                            className="bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white shadow-lg"
                                        >
                                            <Plus className="mr-2 h-4 w-4" />
                                            Add Room
                                        </Button>
                                        {rooms.length > 0 && (
                                            <Button 
                                                variant="outline"
                                                onClick={() => {
                                                    setSearchTerm('');
                                                    setStatusFilter('all');
                                                    setTypeFilter('all');
                                                }}
                                                className="border-slate-300 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-700"
                                            >
                                                Clear Filters
                                            </Button>
                                        )}
                                    </div>
                                </div>
                            )}
                        </CardContent>
                    </Card>
                </div>
            </div>

            {/* Add Room Modal */}
            {isAddModalOpen && (
                <div className="fixed inset-0 z-50">
                    <div className="fixed inset-0 bg-black/50" onClick={() => setIsAddModalOpen(false)} />
                    <div className="fixed right-0 top-0 h-full w-[50vw] min-w-[600px] max-w-[800px] bg-white dark:bg-slate-800 border-l border-slate-200 dark:border-slate-700 rounded-l-lg shadow-2xl flex flex-col animate-in slide-in-from-right duration-300">
                    <div className="p-6 pb-4 border-b border-slate-200 dark:border-slate-700">
                        <h2 className="text-lg font-semibold text-slate-900 dark:text-white">Add New Room</h2>
                        <p className="text-sm text-slate-600 dark:text-slate-400 mt-1">
                            Create a new room in your clinic.
                        </p>
                    </div>
                    <div className="flex-1 overflow-y-auto p-6">
                    <div className="grid gap-4 py-4">
                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="name">Room Name *</Label>
                                <Input
                                    id="name"
                                    value={formData.name}
                                    onChange={(e) => setFormData({...formData, name: e.target.value})}
                                    placeholder="Room 101"
                                    className={errors.name ? 'border-red-500' : ''}
                                />
                                {errors.name && <p className="text-sm text-red-500">{errors.name}</p>}
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="type">Room Type *</Label>
                                <Select value={formData.type} onValueChange={(value) => setFormData({...formData, type: value})}>
                                    <SelectTrigger className={errors.type ? 'border-red-500' : ''}>
                                        <SelectValue placeholder="Select room type" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="Consultation">Consultation</SelectItem>
                                        <SelectItem value="Examination">Examination</SelectItem>
                                        <SelectItem value="Procedure">Procedure</SelectItem>
                                        <SelectItem value="Surgery">Surgery</SelectItem>
                                        <SelectItem value="Recovery">Recovery</SelectItem>
                                        <SelectItem value="Emergency">Emergency</SelectItem>
                                    </SelectContent>
                                </Select>
                                {errors.type && <p className="text-sm text-red-500">{errors.type}</p>}
                            </div>
                        </div>
                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="capacity">Capacity *</Label>
                                <Select value={formData.capacity} onValueChange={(value) => setFormData({...formData, capacity: value})}>
                                    <SelectTrigger className={errors.capacity ? 'border-red-500' : ''}>
                                        <SelectValue placeholder="Select capacity" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="1">1 person</SelectItem>
                                        <SelectItem value="2">2 people</SelectItem>
                                        <SelectItem value="3">3 people</SelectItem>
                                        <SelectItem value="4">4 people</SelectItem>
                                        <SelectItem value="5">5+ people</SelectItem>
                                    </SelectContent>
                                </Select>
                                {errors.capacity && <p className="text-sm text-red-500">{errors.capacity}</p>}
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="status">Status</Label>
                                <Select value={formData.status} onValueChange={(value) => setFormData({...formData, status: value})}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select status" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="Available">Available</SelectItem>
                                        <SelectItem value="Occupied">Occupied</SelectItem>
                                        <SelectItem value="Maintenance">Maintenance</SelectItem>
                                        <SelectItem value="Out of Service">Out of Service</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="location">Location</Label>
                            <Input
                                id="location"
                                value={formData.location}
                                onChange={(e) => setFormData({...formData, location: e.target.value})}
                                placeholder="Floor 1, Wing A"
                            />
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="description">Description</Label>
                            <Textarea
                                id="description"
                                value={formData.description}
                                onChange={(e) => setFormData({...formData, description: e.target.value})}
                                placeholder="Room description and features"
                                rows={2}
                            />
                        </div>
                        <div className="space-y-2">
                            <Label>Equipment</Label>
                            <div className="grid grid-cols-2 gap-2">
                                {[
                                    'Examination Table',
                                    'Computer',
                                    'Printer',
                                    'Medical Equipment',
                                    'Surgical Table',
                                    'Anesthesia Machine',
                                    'Monitor',
                                    'Defibrillator',
                                    'X-Ray Machine',
                                    'Ultrasound Machine',
                                    'Blood Pressure Monitor',
                                    'Thermometer',
                                    'Stethoscope',
                                    'Syringe',
                                    'IV Stand'
                                ].map((equipment) => (
                                    <div key={equipment} className="flex items-center space-x-2">
                                        <Checkbox
                                            id={equipment}
                                            checked={formData.equipment.includes(equipment)}
                                            onCheckedChange={(checked) => handleEquipmentChange(equipment, checked as boolean)}
                                        />
                                        <Label htmlFor={equipment} className="text-sm">
                                            {equipment}
                                        </Label>
                                    </div>
                                ))}
                            </div>
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="maintenanceNotes">Maintenance Notes</Label>
                            <Textarea
                                id="maintenanceNotes"
                                value={formData.maintenanceNotes}
                                onChange={(e) => setFormData({...formData, maintenanceNotes: e.target.value})}
                                placeholder="Maintenance schedule and notes"
                                rows={2}
                            />
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="specialRequirements">Special Requirements</Label>
                            <Textarea
                                id="specialRequirements"
                                value={formData.specialRequirements}
                                onChange={(e) => setFormData({...formData, specialRequirements: e.target.value})}
                                placeholder="Special requirements or restrictions"
                                rows={2}
                            />
                        </div>
                    </div>
                    </div>
                    <div className="p-6 pt-4 border-t border-slate-200 dark:border-slate-700 flex justify-end space-x-3">
                        <Button variant="outline" onClick={handleCancel}>
                            <X className="mr-2 h-4 w-4" />
                            Cancel
                        </Button>
                        <Button onClick={handleSaveRoom} disabled={saving}>
                            {saving ? (
                                <>
                                    <div className="mr-2 h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent" />
                                    Adding...
                                </>
                            ) : (
                                <>
                                    <Save className="mr-2 h-4 w-4" />
                                    Add Room
                                </>
                            )}
                        </Button>
                    </div>
                    </div>
                </div>
            )}

            {/* Edit Room Modal */}
            {isEditModalOpen && (
                <div className="fixed inset-0 z-50">
                    <div className="fixed inset-0 bg-black/50" onClick={() => setIsEditModalOpen(false)} />
                    <div className="fixed right-0 top-0 h-full w-[50vw] min-w-[600px] max-w-[800px] bg-white dark:bg-slate-800 border-l border-slate-200 dark:border-slate-700 rounded-l-lg shadow-2xl flex flex-col animate-in slide-in-from-right duration-300">
                    <div className="p-6 pb-4 border-b border-slate-200 dark:border-slate-700">
                        <h2 className="text-lg font-semibold text-slate-900 dark:text-white">Edit Room</h2>
                        <p className="text-sm text-slate-600 dark:text-slate-400 mt-1">
                            Update the room information for {editingRoom?.name}.
                        </p>
                    </div>
                    <div className="flex-1 overflow-y-auto p-6">
                    <div className="grid gap-4 py-4">
                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="edit-name">Room Name *</Label>
                                <Input
                                    id="edit-name"
                                    value={formData.name}
                                    onChange={(e) => setFormData({...formData, name: e.target.value})}
                                    placeholder="Room 101"
                                    className={errors.name ? 'border-red-500' : ''}
                                />
                                {errors.name && <p className="text-sm text-red-500">{errors.name}</p>}
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="edit-type">Room Type *</Label>
                                <Select value={formData.type} onValueChange={(value) => setFormData({...formData, type: value})}>
                                    <SelectTrigger className={errors.type ? 'border-red-500' : ''}>
                                        <SelectValue placeholder="Select room type" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="Consultation">Consultation</SelectItem>
                                        <SelectItem value="Examination">Examination</SelectItem>
                                        <SelectItem value="Procedure">Procedure</SelectItem>
                                        <SelectItem value="Surgery">Surgery</SelectItem>
                                        <SelectItem value="Recovery">Recovery</SelectItem>
                                        <SelectItem value="Emergency">Emergency</SelectItem>
                                    </SelectContent>
                                </Select>
                                {errors.type && <p className="text-sm text-red-500">{errors.type}</p>}
                            </div>
                        </div>
                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="edit-capacity">Capacity *</Label>
                                <Select value={formData.capacity} onValueChange={(value) => setFormData({...formData, capacity: value})}>
                                    <SelectTrigger className={errors.capacity ? 'border-red-500' : ''}>
                                        <SelectValue placeholder="Select capacity" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="1">1 person</SelectItem>
                                        <SelectItem value="2">2 people</SelectItem>
                                        <SelectItem value="3">3 people</SelectItem>
                                        <SelectItem value="4">4 people</SelectItem>
                                        <SelectItem value="5">5+ people</SelectItem>
                                    </SelectContent>
                                </Select>
                                {errors.capacity && <p className="text-sm text-red-500">{errors.capacity}</p>}
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="edit-status">Status</Label>
                                <Select value={formData.status} onValueChange={(value) => setFormData({...formData, status: value})}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select status" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="Available">Available</SelectItem>
                                        <SelectItem value="Occupied">Occupied</SelectItem>
                                        <SelectItem value="Maintenance">Maintenance</SelectItem>
                                        <SelectItem value="Out of Service">Out of Service</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="edit-location">Location</Label>
                            <Input
                                id="edit-location"
                                value={formData.location}
                                onChange={(e) => setFormData({...formData, location: e.target.value})}
                                placeholder="Floor 1, Wing A"
                            />
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="edit-description">Description</Label>
                            <Textarea
                                id="edit-description"
                                value={formData.description}
                                onChange={(e) => setFormData({...formData, description: e.target.value})}
                                placeholder="Room description and features"
                                rows={2}
                            />
                        </div>
                        <div className="space-y-2">
                            <Label>Equipment</Label>
                            <div className="grid grid-cols-2 gap-2">
                                {[
                                    'Examination Table',
                                    'Computer',
                                    'Printer',
                                    'Medical Equipment',
                                    'Surgical Table',
                                    'Anesthesia Machine',
                                    'Monitor',
                                    'Defibrillator',
                                    'X-Ray Machine',
                                    'Ultrasound Machine',
                                    'Blood Pressure Monitor',
                                    'Thermometer',
                                    'Stethoscope',
                                    'Syringe',
                                    'IV Stand'
                                ].map((equipment) => (
                                    <div key={equipment} className="flex items-center space-x-2">
                                        <Checkbox
                                            id={`edit-${equipment}`}
                                            checked={formData.equipment.includes(equipment)}
                                            onCheckedChange={(checked) => handleEquipmentChange(equipment, checked as boolean)}
                                        />
                                        <Label htmlFor={`edit-${equipment}`} className="text-sm">
                                            {equipment}
                                        </Label>
                                    </div>
                                ))}
                            </div>
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="edit-maintenanceNotes">Maintenance Notes</Label>
                            <Textarea
                                id="edit-maintenanceNotes"
                                value={formData.maintenanceNotes}
                                onChange={(e) => setFormData({...formData, maintenanceNotes: e.target.value})}
                                placeholder="Maintenance schedule and notes"
                                rows={2}
                            />
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="edit-specialRequirements">Special Requirements</Label>
                            <Textarea
                                id="edit-specialRequirements"
                                value={formData.specialRequirements}
                                onChange={(e) => setFormData({...formData, specialRequirements: e.target.value})}
                                placeholder="Special requirements or restrictions"
                                rows={2}
                            />
                        </div>
                    </div>
                    </div>
                    <div className="p-6 pt-4 border-t border-slate-200 dark:border-slate-700 flex justify-end space-x-3">
                        <Button variant="outline" onClick={handleCancel}>
                            <X className="mr-2 h-4 w-4" />
                            Cancel
                        </Button>
                        <Button onClick={handleSaveRoom} disabled={saving}>
                            {saving ? (
                                <>
                                    <div className="mr-2 h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent" />
                                    Updating...
                                </>
                            ) : (
                                <>
                                    <Save className="mr-2 h-4 w-4" />
                                    Update Room
                                </>
                            )}
                        </Button>
                    </div>
                    </div>
                </div>
            )}

            {/* View Room Details Modal */}
            {isViewModalOpen && (
                <div className="fixed inset-0 z-50">
                    <div className="fixed inset-0 bg-black/50" onClick={() => setIsViewModalOpen(false)} />
                    <div className="fixed right-0 top-0 h-full w-[50vw] min-w-[600px] max-w-[800px] bg-white dark:bg-slate-800 border-l border-slate-200 dark:border-slate-700 rounded-l-lg shadow-2xl flex flex-col animate-in slide-in-from-right duration-300">
                    <div className="p-6 pb-4 border-b border-slate-200 dark:border-slate-700">
                        <h2 className="text-lg font-semibold text-slate-900 dark:text-white">Room Details</h2>
                        <p className="text-sm text-slate-600 dark:text-slate-400 mt-1">
                            Complete information about {viewingRoom?.name}
                        </p>
                    </div>
                    <div className="flex-1 overflow-y-auto p-6">
                    {viewingRoom && (
                        <div className="space-y-6">
                            <div className="grid grid-cols-2 gap-6">
                                <div className="space-y-4">
                                    <div className="flex items-center space-x-3">
                                        <div className="h-16 w-16 rounded-lg bg-gradient-to-r from-blue-500 to-purple-500 flex items-center justify-center shadow-md">
                                            <Building2 className="h-8 w-8 text-white" />
                                        </div>
                                        <div>
                                            <h3 className="text-xl font-semibold text-slate-900 dark:text-white">{viewingRoom.name}</h3>
                                            <p className="text-slate-600 dark:text-slate-400">Room ID: {viewingRoom.id}</p>
                                            <Badge
                                                className={`font-medium ${
                                                    viewingRoom.status === 'Available'
                                                        ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400'
                                                        : viewingRoom.status === 'Occupied'
                                                        ? 'bg-orange-100 text-orange-800 dark:bg-orange-900/20 dark:text-orange-400'
                                                        : 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400'
                                                }`}
                                            >
                                                {viewingRoom.status}
                                            </Badge>
                                        </div>
                                    </div>
                                </div>
                                <div className="space-y-4">
                                    <div className="flex items-center space-x-2">
                                        <Building2 className="h-4 w-4 text-slate-400" />
                                        <span className="text-slate-700 dark:text-slate-300">{viewingRoom.type}</span>
                                    </div>
                                    <div className="flex items-center space-x-2">
                                        <Users className="h-4 w-4 text-slate-400" />
                                        <span className="text-slate-700 dark:text-slate-300">Capacity: {viewingRoom.capacity} people</span>
                                    </div>
                                    <div className="flex items-center space-x-2">
                                        <Calendar className="h-4 w-4 text-slate-400" />
                                        <span className="text-slate-700 dark:text-slate-300">{viewingRoom.nextAppointment || 'No upcoming appointments'}</span>
                                    </div>
                                    {viewingRoom.doctor && (
                                        <div className="flex items-center space-x-2">
                                            <Stethoscope className="h-4 w-4 text-slate-400" />
                                            <span className="text-slate-700 dark:text-slate-300">{viewingRoom.doctor}</span>
                                        </div>
                                    )}
                                </div>
                            </div>

                            <div className="space-y-4">
                                <div>
                                    <Label className="text-sm font-medium text-slate-700 dark:text-slate-300">Equipment</Label>
                                    <div className="mt-2 flex flex-wrap gap-2">
                                        {viewingRoom.equipment.length > 0 ? (
                                            viewingRoom.equipment.map((equipment, index) => (
                                                <Badge key={index} variant="secondary" className="text-xs">
                                                    {equipment}
                                                </Badge>
                                            ))
                                        ) : (
                                            <span className="text-slate-500 dark:text-slate-400 text-sm">No equipment listed</span>
                                        )}
                                    </div>
                                </div>
                            </div>
                        </div>
                    )}
                    </div>
                    <div className="p-6 pt-4 border-t border-slate-200 dark:border-slate-700 flex justify-end space-x-3">
                        <Button variant="outline" onClick={() => setIsViewModalOpen(false)}>
                            Close
                        </Button>
                        <Button onClick={() => {
                            setIsViewModalOpen(false);
                            handleEditRoom(viewingRoom!);
                        }}>
                            <Edit className="mr-2 h-4 w-4" />
                            Edit Room
                        </Button>
                    </div>
                    </div>
                </div>
            )}
        </AppLayout>
    );
}
