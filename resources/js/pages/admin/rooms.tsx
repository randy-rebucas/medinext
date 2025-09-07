import { Head } from '@inertiajs/react';
import { useState } from 'react';
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

export default function RoomManagement() {
    const [searchTerm, setSearchTerm] = useState('');
    const [statusFilter, setStatusFilter] = useState('all');
    const [typeFilter, setTypeFilter] = useState('all');
    const [isAddModalOpen, setIsAddModalOpen] = useState(false);
    const [isEditModalOpen, setIsEditModalOpen] = useState(false);
    const [editingRoom, setEditingRoom] = useState<{
        id: number;
        name: string;
        type: string;
        capacity: number;
        status: string;
        equipment: string[];
        nextAppointment: string;
        doctor: string;
    } | null>(null);
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

    const rooms = [
        {
            id: 1,
            name: 'Room 101',
            type: 'Consultation',
            capacity: 1,
            status: 'Available',
            equipment: ['Examination Table', 'Computer', 'Printer'],
            nextAppointment: '2024-01-15 10:00',
            doctor: 'Dr. Sarah Johnson'
        },
        {
            id: 2,
            name: 'Room 102',
            type: 'Examination',
            capacity: 2,
            status: 'Occupied',
            equipment: ['Examination Table', 'Medical Equipment', 'Computer'],
            nextAppointment: '2024-01-15 11:30',
            doctor: 'Dr. Michael Brown'
        },
        {
            id: 3,
            name: 'Room 103',
            type: 'Procedure',
            capacity: 1,
            status: 'Maintenance',
            equipment: ['Surgical Table', 'Anesthesia Machine', 'Monitor'],
            nextAppointment: '2024-01-16 09:00',
            doctor: 'Dr. Emily Davis'
        },
        {
            id: 4,
            name: 'Room 104',
            type: 'Consultation',
            capacity: 1,
            status: 'Available',
            equipment: ['Examination Table', 'Computer'],
            nextAppointment: '2024-01-15 14:00',
            doctor: 'Dr. James Wilson'
        }
    ];

    const getStatusColor = (status: string) => {
        switch (status) {
            case 'Available': return 'default';
            case 'Occupied': return 'secondary';
            case 'Maintenance': return 'destructive';
            default: return 'secondary';
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

    const handleEditRoom = (room: {
        id: number;
        name: string;
        type: string;
        capacity: number;
        status: string;
        equipment: string[];
        nextAppointment: string;
        doctor: string;
    }) => {
        setEditingRoom(room);
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

    const handleSaveRoom = () => {
        // Here you would typically make an API call to save the room
        console.log('Saving room:', formData);
        setIsAddModalOpen(false);
        setIsEditModalOpen(false);
        setEditingRoom(null);
    };

    const handleCancel = () => {
        setIsAddModalOpen(false);
        setIsEditModalOpen(false);
        setEditingRoom(null);
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

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Room Management - Medinext">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&family=instrument-sans:400,500,600" rel="stylesheet" />
            </Head>

            <div className="min-h-screen bg-gradient-to-br from-slate-50 via-white to-blue-50 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900">
                <div className="space-y-6 p-6">

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
                                        className="border-slate-300 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-700"
                                    >
                                        <Activity className="mr-2 h-4 w-4" />
                                        Room Status
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
                                        {filteredRooms.map((room) => (
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
                                                    <Badge
                                                        className={`font-medium ${
                                                            room.status === 'Available'
                                                                ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400'
                                                                : room.status === 'Occupied'
                                                                ? 'bg-orange-100 text-orange-800 dark:bg-orange-900/20 dark:text-orange-400'
                                                                : 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400'
                                                        }`}
                                                    >
                                                        {room.status}
                                                    </Badge>
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

                            {filteredRooms.length === 0 && (
                                <div className="text-center py-12">
                                    <div className="mx-auto w-16 h-16 bg-slate-100 dark:bg-slate-700 rounded-full flex items-center justify-center mb-4">
                                        <Building2 className="h-8 w-8 text-slate-400" />
                                    </div>
                                    <h3 className="text-lg font-semibold text-slate-900 dark:text-white mb-2">No rooms found</h3>
                                    <p className="text-slate-500 dark:text-slate-400 mb-4">
                                        Try adjusting your search or filter criteria.
                                    </p>
                                    <Button className="bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white">
                                        <Plus className="mr-2 h-4 w-4" />
                                        Add Room
                                    </Button>
                                </div>
                            )}
                        </CardContent>
                    </Card>
                </div>
            </div>

            {/* Add Room Modal */}
            <Dialog open={isAddModalOpen} onOpenChange={setIsAddModalOpen}>
                <DialogContent className="max-w-4xl max-h-[90vh] overflow-y-auto">
                    <DialogHeader>
                        <DialogTitle>Add New Room</DialogTitle>
                        <DialogDescription>
                            Create a new room in your clinic.
                        </DialogDescription>
                    </DialogHeader>
                    <div className="grid gap-4 py-4">
                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="name">Room Name *</Label>
                                <Input
                                    id="name"
                                    value={formData.name}
                                    onChange={(e) => setFormData({...formData, name: e.target.value})}
                                    placeholder="Room 101"
                                />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="type">Room Type *</Label>
                                <Select value={formData.type} onValueChange={(value) => setFormData({...formData, type: value})}>
                                    <SelectTrigger>
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
                            </div>
                        </div>
                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="capacity">Capacity *</Label>
                                <Select value={formData.capacity} onValueChange={(value) => setFormData({...formData, capacity: value})}>
                                    <SelectTrigger>
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
                    <DialogFooter>
                        <Button variant="outline" onClick={handleCancel}>
                            <X className="mr-2 h-4 w-4" />
                            Cancel
                        </Button>
                        <Button onClick={handleSaveRoom}>
                            <Save className="mr-2 h-4 w-4" />
                            Add Room
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            {/* Edit Room Modal */}
            <Dialog open={isEditModalOpen} onOpenChange={setIsEditModalOpen}>
                <DialogContent className="max-w-4xl max-h-[90vh] overflow-y-auto">
                    <DialogHeader>
                        <DialogTitle>Edit Room</DialogTitle>
                        <DialogDescription>
                            Update the room information for {editingRoom?.name}.
                        </DialogDescription>
                    </DialogHeader>
                    <div className="grid gap-4 py-4">
                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="edit-name">Room Name *</Label>
                                <Input
                                    id="edit-name"
                                    value={formData.name}
                                    onChange={(e) => setFormData({...formData, name: e.target.value})}
                                    placeholder="Room 101"
                                />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="edit-type">Room Type *</Label>
                                <Select value={formData.type} onValueChange={(value) => setFormData({...formData, type: value})}>
                                    <SelectTrigger>
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
                            </div>
                        </div>
                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="edit-capacity">Capacity *</Label>
                                <Select value={formData.capacity} onValueChange={(value) => setFormData({...formData, capacity: value})}>
                                    <SelectTrigger>
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
                    <DialogFooter>
                        <Button variant="outline" onClick={handleCancel}>
                            <X className="mr-2 h-4 w-4" />
                            Cancel
                        </Button>
                        <Button onClick={handleSaveRoom}>
                            <Save className="mr-2 h-4 w-4" />
                            Update Room
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </AppLayout>
    );
}
