import { Head, router } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem, type StaffManagementData, type StaffMember, type StaffFormData } from '@/types';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import {
    Plus,
    Search,
    Edit,
    Eye,
    UserPlus,
    Mail,
    Phone,
    Calendar,
    Building2,
    Shield,
    Clock,
    Save,
    X
} from 'lucide-react';

interface StaffManagementProps {
    staff: StaffManagementData['staff'];
    roles: StaffManagementData['roles'];
    departments: StaffManagementData['departments'];
    permissions: StaffManagementData['permissions'];
}

export default function StaffManagement({ staff, roles, departments }: StaffManagementProps) {
    const [searchTerm, setSearchTerm] = useState('');
    const [roleFilter, setRoleFilter] = useState('all');
    const [statusFilter, setStatusFilter] = useState('all');
    const [isAddModalOpen, setIsAddModalOpen] = useState(false);
    const [isEditModalOpen, setIsEditModalOpen] = useState(false);
    const [isViewModalOpen, setIsViewModalOpen] = useState(false);
    const [editingStaff, setEditingStaff] = useState<StaffMember | null>(null);
    const [viewingStaff, setViewingStaff] = useState<StaffMember | null>(null);
    const [isLoading, setIsLoading] = useState(false);
    const [formErrors, setFormErrors] = useState<Record<string, string>>({});
    const [formData, setFormData] = useState<StaffFormData>({
        name: '',
        email: '',
        phone: '',
        role: '',
        department: '',
        status: 'Active',
        address: '',
        emergency_contact: '',
        emergency_phone: '',
        notes: ''
    });

    const filteredStaff = staff.filter(member => {
        const matchesSearch = member.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
                            member.email.toLowerCase().includes(searchTerm.toLowerCase()) ||
                            member.department.toLowerCase().includes(searchTerm.toLowerCase());
        const matchesRole = roleFilter === 'all' || member.role === roleFilter;
        const matchesStatus = statusFilter === 'all' || member.status === statusFilter;

        return matchesSearch && matchesRole && matchesStatus;
    });

    const getStatusColor = (status: string) => {
        switch (status) {
            case 'Active': return 'default';
            case 'On Leave': return 'secondary';
            case 'Inactive': return 'destructive';
            default: return 'secondary';
        }
    };

    const getRoleIcon = (role: string) => {
        switch (role) {
            case 'Doctor': return <Shield className="h-4 w-4" />;
            case 'Nurse': return <UserPlus className="h-4 w-4" />;
            case 'Receptionist': return <Building2 className="h-4 w-4" />;
            case 'Administrator': return <Shield className="h-4 w-4" />;
            default: return <UserPlus className="h-4 w-4" />;
        }
    };

    const handleAddStaff = () => {
        setIsAddModalOpen(true);
        setFormData({
            name: '',
            email: '',
            phone: '',
            role: '',
            department: '',
            status: 'Active',
            address: '',
            emergency_contact: '',
            emergency_phone: '',
            notes: ''
        });
        // Reset any previous errors
        setFormErrors({});
    };

    const handleViewStaff = (staffMember: StaffMember) => {
        setViewingStaff(staffMember);
        setIsViewModalOpen(true);
    };

    const handleEditStaff = (staffMember: StaffMember) => {
        setEditingStaff(staffMember);
        setFormData({
            name: staffMember.name,
            email: staffMember.email,
            phone: staffMember.phone,
            role: staffMember.role,
            department: staffMember.department,
            status: staffMember.status,
            address: staffMember.address || '',
            emergency_contact: staffMember.emergency_contact || '',
            emergency_phone: staffMember.emergency_phone || '',
            notes: staffMember.notes || ''
        });
        setFormErrors({});
        setIsEditModalOpen(true);
    };

    const handleSaveStaff = async () => {
        // Clear previous errors
        setFormErrors({});

        // Basic client-side validation
        const errors: Record<string, string> = {};

        if (!formData.name.trim()) {
            errors.name = 'Name is required';
        }
        if (!formData.email.trim()) {
            errors.email = 'Email is required';
        } else if (!/\S+@\S+\.\S+/.test(formData.email)) {
            errors.email = 'Please enter a valid email address';
        }
        if (!formData.phone.trim()) {
            errors.phone = 'Phone number is required';
        }
        if (!formData.role) {
            errors.role = 'Role is required';
        }
        if (!formData.department) {
            errors.department = 'Department is required';
        }

        if (Object.keys(errors).length > 0) {
            setFormErrors(errors);
            return;
        }

        setIsLoading(true);
        try {
            const url = editingStaff ? `/admin/staff/${editingStaff.id}` : '/admin/staff';
            const method = editingStaff ? 'POST' : 'POST'; // Use POST for both create and update

            const requestData = editingStaff ? { ...formData, _method: 'PUT' } : formData;

            // Create FormData for Laravel compatibility
            const formDataToSend = new FormData();
            Object.keys(requestData).forEach(key => {
                formDataToSend.append(key, String((requestData as Record<string, unknown>)[key]));
            });

            const response = await fetch(url, {
                method,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                body: formDataToSend,
            });

            const result = await response.json();

            if (result.success) {
                // Show success message
                const action = editingStaff ? 'updated' : 'added';
                alert(`Staff member ${action} successfully!`);

                // Reset form and close modals
                setFormData({
                    name: '',
                    email: '',
                    phone: '',
                    role: '',
                    department: '',
                    status: 'Active',
                    address: '',
                    emergency_contact: '',
                    emergency_phone: '',
                    notes: ''
                });
                setFormErrors({});
                setIsAddModalOpen(false);
                setIsEditModalOpen(false);
                setEditingStaff(null);

                // Refresh the page to get updated data
                router.reload();
            } else {
                console.error('Error saving staff:', result.message);

                // Handle validation errors from server
                if (result.errors) {
                    setFormErrors(result.errors);
                } else {
                    alert(`Failed to save staff member: ${result.message}`);
                }
            }
        } catch (error) {
            console.error('Error saving staff:', error);
            alert('An unexpected error occurred. Please try again.');
        } finally {
            setIsLoading(false);
        }
    };

    const handleDeleteStaff = async (staffId: number, staffName: string) => {
        if (!confirm(`Are you sure you want to deactivate ${staffName}? This action can be reversed by editing the staff member.`)) {
            return;
        }

        setIsLoading(true);
        try {
            const response = await fetch(`/admin/staff/${staffId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                body: (() => {
                    const formData = new FormData();
                    formData.append('_method', 'DELETE');
                    return formData;
                })(),
            });

            const result = await response.json();

            if (result.success) {
                // Show success message (you could add a toast notification here)
                alert(`${staffName} has been deactivated successfully.`);
                router.reload();
            } else {
                console.error('Error deleting staff:', result.message);
                alert(`Failed to deactivate ${staffName}: ${result.message}`);
            }
        } catch (error) {
            console.error('Error deleting staff:', error);
            alert(`Failed to deactivate ${staffName}. Please try again.`);
        } finally {
            setIsLoading(false);
        }
    };

    const handleCancel = () => {
        setIsAddModalOpen(false);
        setIsEditModalOpen(false);
        setIsViewModalOpen(false);
        setEditingStaff(null);
        setViewingStaff(null);
        setFormErrors({});
        setFormData({
            name: '',
            email: '',
            phone: '',
            role: '',
            department: '',
            status: 'Active',
            address: '',
            emergency_contact: '',
            emergency_phone: '',
            notes: ''
        });
    };
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Staff Management',
            href: '/admin/dashboard',
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Staff Management - Medinext">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&family=instrument-sans:400,500,600" rel="stylesheet" />
            </Head>

            <div className="min-h-screen bg-gradient-to-br from-slate-50 via-white to-blue-50 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900">
                <div className="space-y-6 p-6">

                    <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                        <CardHeader>
                            <div className="flex items-center justify-between">
                                <div>
                                    <CardTitle className="text-xl font-semibold text-slate-900 dark:text-white">Staff Directory</CardTitle>
                                    <CardDescription className="text-slate-600 dark:text-slate-300">
                                        View and manage all staff members in your clinic
                                    </CardDescription>
                                </div>
                                <div className="flex space-x-3">
                                    <Button
                                        variant="outline"
                                        className="border-slate-300 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-700"
                                    >
                                        <UserPlus className="mr-2 h-4 w-4" />
                                        Import Staff
                                    </Button>
                                    <Button
                                        onClick={handleAddStaff}
                                        className="bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white shadow-lg"
                                    >
                                        <Plus className="mr-2 h-4 w-4" />
                                        Add Staff Member
                                    </Button>
                                </div>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div className="flex items-center space-x-3 mb-6">
                                <div className="relative flex-1">
                                    <Search className="absolute left-3 top-3 h-4 w-4 text-slate-400" />
                                    <Input
                                        placeholder="Search staff members..."
                                        className="pl-10 h-11 border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-700 dark:text-white"
                                        value={searchTerm}
                                        onChange={(e) => setSearchTerm(e.target.value)}
                                    />
                                </div>
                                <Select value={roleFilter} onValueChange={setRoleFilter}>
                                    <SelectTrigger className="w-[160px] h-11 border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500">
                                        <SelectValue placeholder="Role" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">All Roles</SelectItem>
                                        {roles.map((role) => (
                                            <SelectItem key={role.id} value={role.name}>
                                                {role.name}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                <Select value={statusFilter} onValueChange={setStatusFilter}>
                                    <SelectTrigger className="w-[160px] h-11 border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500">
                                        <SelectValue placeholder="Status" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">All Status</SelectItem>
                                        <SelectItem value="Active">Active</SelectItem>
                                        <SelectItem value="On Leave">On Leave</SelectItem>
                                        <SelectItem value="Inactive">Inactive</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <div className="rounded-lg border border-slate-200 dark:border-slate-700 overflow-hidden">
                                <Table>
                                    <TableHeader className="bg-slate-50 dark:bg-slate-800/50">
                                        <TableRow className="border-slate-200 dark:border-slate-700">
                                            <TableHead className="font-semibold text-slate-700 dark:text-slate-300">Staff Member</TableHead>
                                            <TableHead className="font-semibold text-slate-700 dark:text-slate-300">Contact</TableHead>
                                            <TableHead className="font-semibold text-slate-700 dark:text-slate-300">Role & Department</TableHead>
                                            <TableHead className="font-semibold text-slate-700 dark:text-slate-300">Status</TableHead>
                                            <TableHead className="font-semibold text-slate-700 dark:text-slate-300">Join Date</TableHead>
                                            <TableHead className="font-semibold text-slate-700 dark:text-slate-300">Last Active</TableHead>
                                            <TableHead className="text-right font-semibold text-slate-700 dark:text-slate-300">Actions</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {filteredStaff.map((member) => (
                                            <TableRow key={member.id} className="border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors duration-200">
                                                <TableCell>
                                                    <div className="flex items-center space-x-3">
                                                        <div className="h-12 w-12 rounded-full bg-gradient-to-r from-blue-500 to-purple-500 flex items-center justify-center shadow-md">
                                                            <span className="text-sm font-bold text-white">
                                                                {member.name.split(' ').map(n => n[0]).join('')}
                                                            </span>
                                                        </div>
                                                        <div>
                                                            <div className="font-semibold text-slate-900 dark:text-white">{member.name}</div>
                                                            <div className="text-sm text-slate-500 dark:text-slate-400">ID: {member.id}</div>
                                                        </div>
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    <div className="space-y-2">
                                                        <div className="flex items-center text-sm">
                                                            <Mail className="mr-2 h-4 w-4 text-slate-400" />
                                                            <span className="text-slate-700 dark:text-slate-300">{member.email}</span>
                                                        </div>
                                                        <div className="flex items-center text-sm">
                                                            <Phone className="mr-2 h-4 w-4 text-slate-400" />
                                                            <span className="text-slate-500 dark:text-slate-400">{member.phone}</span>
                                                        </div>
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    <div className="flex items-center space-x-3">
                                                        <div className="p-2 bg-slate-100 dark:bg-slate-700 rounded-lg">
                                                            {getRoleIcon(member.role)}
                                                        </div>
                                                        <div>
                                                            <div className="font-medium text-slate-900 dark:text-white">{member.role}</div>
                                                            <div className="text-sm text-slate-500 dark:text-slate-400">{member.department}</div>
                                                        </div>
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    <Badge
                                                        variant={getStatusColor(member.status)}
                                                        className={`font-medium ${
                                                            member.status === 'Active'
                                                                ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400'
                                                                : member.status === 'On Leave'
                                                                ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400'
                                                                : 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400'
                                                        }`}
                                                    >
                                                        {member.status}
                                                    </Badge>
                                                </TableCell>
                                                <TableCell>
                                                    <div className="flex items-center text-sm">
                                                        <Calendar className="mr-2 h-4 w-4 text-slate-400" />
                                                        <span className="text-slate-700 dark:text-slate-300">{member.join_date}</span>
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    <div className="flex items-center text-sm">
                                                        <Clock className="mr-2 h-4 w-4 text-slate-400" />
                                                        <span className="text-slate-500 dark:text-slate-400">{member.last_active}</span>
                                                    </div>
                                                </TableCell>
                                                <TableCell className="text-right">
                                                    <div className="flex items-center justify-end space-x-2">
                                                        <Button
                                                            variant="ghost"
                                                            size="sm"
                                                            title="View Details"
                                                            onClick={() => handleViewStaff(member)}
                                                            className="h-8 w-8 p-0 hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:text-blue-600 dark:hover:text-blue-400"
                                                        >
                                                            <Eye className="h-4 w-4" />
                                                        </Button>
                                                        <Button
                                                            variant="ghost"
                                                            size="sm"
                                                            title="Edit Staff"
                                                            onClick={() => handleEditStaff(member)}
                                                            className="h-8 w-8 p-0 hover:bg-green-50 dark:hover:bg-green-900/20 hover:text-green-600 dark:hover:text-green-400"
                                                        >
                                                            <Edit className="h-4 w-4" />
                                                        </Button>
                                                        <Button
                                                            variant="ghost"
                                                            size="sm"
                                                            title="Deactivate Staff"
                                                            onClick={() => handleDeleteStaff(member.id, member.name)}
                                                            disabled={isLoading}
                                                            className="h-8 w-8 p-0 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-600 dark:hover:text-red-400"
                                                        >
                                                            <X className="h-4 w-4" />
                                                        </Button>
                                                    </div>
                                                </TableCell>
                                            </TableRow>
                                        ))}
                                    </TableBody>
                                </Table>
                            </div>

                            {filteredStaff.length === 0 && (
                                <div className="text-center py-12">
                                    <div className="mx-auto w-16 h-16 bg-slate-100 dark:bg-slate-700 rounded-full flex items-center justify-center mb-4">
                                        <UserPlus className="h-8 w-8 text-slate-400" />
                                    </div>
                                    <h3 className="text-lg font-semibold text-slate-900 dark:text-white mb-2">No staff members found</h3>
                                    <p className="text-slate-500 dark:text-slate-400 mb-4">
                                        Try adjusting your search or filter criteria.
                                    </p>
                                    <Button className="bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white">
                                        <Plus className="mr-2 h-4 w-4" />
                                        Add Staff Member
                                    </Button>
                                </div>
                            )}
                        </CardContent>
                    </Card>
                </div>
            </div>

            {/* Add Staff Modal */}
            <Dialog open={isAddModalOpen} onOpenChange={setIsAddModalOpen}>
                <DialogContent className="max-w-2xl max-h-[90vh] overflow-y-auto">
                    <DialogHeader>
                        <DialogTitle>Add New Staff Member</DialogTitle>
                        <DialogDescription>
                            Enter the details for the new staff member.
                        </DialogDescription>
                    </DialogHeader>
                    <div className="grid gap-4 py-4">
                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="name">Full Name *</Label>
                                <Input
                                    id="name"
                                    value={formData.name}
                                    onChange={(e) => setFormData({...formData, name: e.target.value})}
                                    placeholder="Enter full name"
                                    className={formErrors.name ? "border-red-500" : ""}
                                />
                                {formErrors.name && (
                                    <p className="text-sm text-red-500">{formErrors.name}</p>
                                )}
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="email">Email *</Label>
                                <Input
                                    id="email"
                                    type="email"
                                    value={formData.email}
                                    onChange={(e) => setFormData({...formData, email: e.target.value})}
                                    placeholder="Enter email address"
                                    className={formErrors.email ? "border-red-500" : ""}
                                />
                                {formErrors.email && (
                                    <p className="text-sm text-red-500">{formErrors.email}</p>
                                )}
                            </div>
                        </div>
                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="phone">Phone Number *</Label>
                                <Input
                                    id="phone"
                                    value={formData.phone}
                                    onChange={(e) => setFormData({...formData, phone: e.target.value})}
                                    placeholder="Enter phone number"
                                    className={formErrors.phone ? "border-red-500" : ""}
                                />
                                {formErrors.phone && (
                                    <p className="text-sm text-red-500">{formErrors.phone}</p>
                                )}
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="role">Role *</Label>
                                <Select value={formData.role} onValueChange={(value) => setFormData({...formData, role: value})}>
                                    <SelectTrigger className={formErrors.role ? "border-red-500" : ""}>
                                        <SelectValue placeholder="Select role" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {roles.map((role) => (
                                            <SelectItem key={role.id} value={role.name}>
                                                {role.name}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                {formErrors.role && (
                                    <p className="text-sm text-red-500">{formErrors.role}</p>
                                )}
                            </div>
                        </div>
                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="department">Department *</Label>
                                <Select value={formData.department} onValueChange={(value) => setFormData({...formData, department: value})}>
                                    <SelectTrigger className={formErrors.department ? "border-red-500" : ""}>
                                        <SelectValue placeholder="Select department" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {departments.map((department) => (
                                            <SelectItem key={department} value={department}>
                                                {department}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                {formErrors.department && (
                                    <p className="text-sm text-red-500">{formErrors.department}</p>
                                )}
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="status">Status</Label>
                                <Select value={formData.status} onValueChange={(value) => setFormData({...formData, status: value as 'Active' | 'On Leave' | 'Inactive'})}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select status" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="Active">Active</SelectItem>
                                        <SelectItem value="On Leave">On Leave</SelectItem>
                                        <SelectItem value="Inactive">Inactive</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="address">Address</Label>
                            <Textarea
                                id="address"
                                value={formData.address}
                                onChange={(e) => setFormData({...formData, address: e.target.value})}
                                placeholder="Enter address"
                                rows={2}
                            />
                        </div>
                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="emergencyContact">Emergency Contact</Label>
                                <Input
                                    id="emergencyContact"
                                    value={formData.emergency_contact}
                                    onChange={(e) => setFormData({...formData, emergency_contact: e.target.value})}
                                    placeholder="Emergency contact name"
                                />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="emergencyPhone">Emergency Phone</Label>
                                <Input
                                    id="emergencyPhone"
                                    value={formData.emergency_phone}
                                    onChange={(e) => setFormData({...formData, emergency_phone: e.target.value})}
                                    placeholder="Emergency contact phone"
                                />
                            </div>
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="notes">Notes</Label>
                            <Textarea
                                id="notes"
                                value={formData.notes}
                                onChange={(e) => setFormData({...formData, notes: e.target.value})}
                                placeholder="Additional notes"
                                rows={3}
                            />
                        </div>
                    </div>
                    <DialogFooter>
                        <Button variant="outline" onClick={handleCancel}>
                            <X className="mr-2 h-4 w-4" />
                            Cancel
                        </Button>
                        <Button onClick={handleSaveStaff} disabled={isLoading}>
                            <Save className="mr-2 h-4 w-4" />
                            {isLoading ? 'Adding...' : 'Add Staff Member'}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            {/* Edit Staff Modal */}
            <Dialog open={isEditModalOpen} onOpenChange={setIsEditModalOpen}>
                <DialogContent className="max-w-2xl max-h-[90vh] overflow-y-auto">
                    <DialogHeader>
                        <DialogTitle>Edit Staff Member</DialogTitle>
                        <DialogDescription>
                            Update the details for {editingStaff?.name}.
                        </DialogDescription>
                    </DialogHeader>
                    <div className="grid gap-4 py-4">
                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="edit-name">Full Name *</Label>
                                <Input
                                    id="edit-name"
                                    value={formData.name}
                                    onChange={(e) => setFormData({...formData, name: e.target.value})}
                                    placeholder="Enter full name"
                                    className={formErrors.name ? "border-red-500" : ""}
                                />
                                {formErrors.name && (
                                    <p className="text-sm text-red-500">{formErrors.name}</p>
                                )}
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="edit-email">Email *</Label>
                                <Input
                                    id="edit-email"
                                    type="email"
                                    value={formData.email}
                                    onChange={(e) => setFormData({...formData, email: e.target.value})}
                                    placeholder="Enter email address"
                                    className={formErrors.email ? "border-red-500" : ""}
                                />
                                {formErrors.email && (
                                    <p className="text-sm text-red-500">{formErrors.email}</p>
                                )}
                            </div>
                        </div>
                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="edit-phone">Phone Number *</Label>
                                <Input
                                    id="edit-phone"
                                    value={formData.phone}
                                    onChange={(e) => setFormData({...formData, phone: e.target.value})}
                                    placeholder="Enter phone number"
                                    className={formErrors.phone ? "border-red-500" : ""}
                                />
                                {formErrors.phone && (
                                    <p className="text-sm text-red-500">{formErrors.phone}</p>
                                )}
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="edit-role">Role *</Label>
                                <Select value={formData.role} onValueChange={(value) => setFormData({...formData, role: value})}>
                                    <SelectTrigger className={formErrors.role ? "border-red-500" : ""}>
                                        <SelectValue placeholder="Select role" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {roles.map((role) => (
                                            <SelectItem key={role.id} value={role.name}>
                                                {role.name}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                {formErrors.role && (
                                    <p className="text-sm text-red-500">{formErrors.role}</p>
                                )}
                            </div>
                        </div>
                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="edit-department">Department *</Label>
                                <Select value={formData.department} onValueChange={(value) => setFormData({...formData, department: value})}>
                                    <SelectTrigger className={formErrors.department ? "border-red-500" : ""}>
                                        <SelectValue placeholder="Select department" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {departments.map((department) => (
                                            <SelectItem key={department} value={department}>
                                                {department}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                {formErrors.department && (
                                    <p className="text-sm text-red-500">{formErrors.department}</p>
                                )}
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="edit-status">Status</Label>
                                <Select value={formData.status} onValueChange={(value) => setFormData({...formData, status: value as 'Active' | 'On Leave' | 'Inactive'})}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select status" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="Active">Active</SelectItem>
                                        <SelectItem value="On Leave">On Leave</SelectItem>
                                        <SelectItem value="Inactive">Inactive</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="edit-address">Address</Label>
                            <Textarea
                                id="edit-address"
                                value={formData.address}
                                onChange={(e) => setFormData({...formData, address: e.target.value})}
                                placeholder="Enter address"
                                rows={2}
                            />
                        </div>
                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="edit-emergencyContact">Emergency Contact</Label>
                                <Input
                                    id="edit-emergencyContact"
                                    value={formData.emergency_contact}
                                    onChange={(e) => setFormData({...formData, emergency_contact: e.target.value})}
                                    placeholder="Emergency contact name"
                                />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="edit-emergencyPhone">Emergency Phone</Label>
                                <Input
                                    id="edit-emergencyPhone"
                                    value={formData.emergency_phone}
                                    onChange={(e) => setFormData({...formData, emergency_phone: e.target.value})}
                                    placeholder="Emergency contact phone"
                                />
                            </div>
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="edit-notes">Notes</Label>
                            <Textarea
                                id="edit-notes"
                                value={formData.notes}
                                onChange={(e) => setFormData({...formData, notes: e.target.value})}
                                placeholder="Additional notes"
                                rows={3}
                            />
                        </div>
                    </div>
                    <DialogFooter>
                        <Button variant="outline" onClick={handleCancel}>
                            <X className="mr-2 h-4 w-4" />
                            Cancel
                        </Button>
                        <Button onClick={handleSaveStaff} disabled={isLoading}>
                            <Save className="mr-2 h-4 w-4" />
                            {isLoading ? 'Updating...' : 'Update Staff Member'}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            {/* View Staff Modal */}
            <Dialog open={isViewModalOpen} onOpenChange={setIsViewModalOpen}>
                <DialogContent className="max-w-2xl max-h-[90vh] overflow-y-auto">
                    <DialogHeader>
                        <DialogTitle>Staff Member Details</DialogTitle>
                        <DialogDescription>
                            View details for {viewingStaff?.name}.
                        </DialogDescription>
                    </DialogHeader>
                    {viewingStaff && (
                        <div className="grid gap-6 py-4">
                            {/* Basic Information */}
                            <div className="space-y-4">
                                <h3 className="text-lg font-semibold text-slate-900 dark:text-white">Basic Information</h3>
                                <div className="grid grid-cols-2 gap-4">
                                    <div>
                                        <Label className="text-sm font-medium text-slate-600 dark:text-slate-400">Full Name</Label>
                                        <p className="text-slate-900 dark:text-white">{viewingStaff.name}</p>
                                    </div>
                                    <div>
                                        <Label className="text-sm font-medium text-slate-600 dark:text-slate-400">Email</Label>
                                        <p className="text-slate-900 dark:text-white">{viewingStaff.email}</p>
                                    </div>
                                    <div>
                                        <Label className="text-sm font-medium text-slate-600 dark:text-slate-400">Phone</Label>
                                        <p className="text-slate-900 dark:text-white">{viewingStaff.phone || 'Not provided'}</p>
                                    </div>
                                    <div>
                                        <Label className="text-sm font-medium text-slate-600 dark:text-slate-400">Status</Label>
                                        <Badge
                                            variant={getStatusColor(viewingStaff.status)}
                                            className={`font-medium ${
                                                viewingStaff.status === 'Active'
                                                    ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400'
                                                    : viewingStaff.status === 'On Leave'
                                                    ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400'
                                                    : 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400'
                                            }`}
                                        >
                                            {viewingStaff.status}
                                        </Badge>
                                    </div>
                                </div>
                            </div>

                            {/* Role & Department */}
                            <div className="space-y-4">
                                <h3 className="text-lg font-semibold text-slate-900 dark:text-white">Role & Department</h3>
                                <div className="grid grid-cols-2 gap-4">
                                    <div>
                                        <Label className="text-sm font-medium text-slate-600 dark:text-slate-400">Role</Label>
                                        <div className="flex items-center space-x-2 mt-1">
                                            <div className="p-2 bg-slate-100 dark:bg-slate-700 rounded-lg">
                                                {getRoleIcon(viewingStaff.role)}
                                            </div>
                                            <p className="text-slate-900 dark:text-white">{viewingStaff.role}</p>
                                        </div>
                                    </div>
                                    <div>
                                        <Label className="text-sm font-medium text-slate-600 dark:text-slate-400">Department</Label>
                                        <p className="text-slate-900 dark:text-white">{viewingStaff.department}</p>
                                    </div>
                                </div>
                            </div>

                            {/* Contact Information */}
                            <div className="space-y-4">
                                <h3 className="text-lg font-semibold text-slate-900 dark:text-white">Contact Information</h3>
                                <div className="space-y-3">
                                    <div>
                                        <Label className="text-sm font-medium text-slate-600 dark:text-slate-400">Address</Label>
                                        <p className="text-slate-900 dark:text-white">{viewingStaff.address || 'Not provided'}</p>
                                    </div>
                                    <div className="grid grid-cols-2 gap-4">
                                        <div>
                                            <Label className="text-sm font-medium text-slate-600 dark:text-slate-400">Emergency Contact</Label>
                                            <p className="text-slate-900 dark:text-white">{viewingStaff.emergency_contact || 'Not provided'}</p>
                                        </div>
                                        <div>
                                            <Label className="text-sm font-medium text-slate-600 dark:text-slate-400">Emergency Phone</Label>
                                            <p className="text-slate-900 dark:text-white">{viewingStaff.emergency_phone || 'Not provided'}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {/* Additional Information */}
                            <div className="space-y-4">
                                <h3 className="text-lg font-semibold text-slate-900 dark:text-white">Additional Information</h3>
                                <div className="grid grid-cols-2 gap-4">
                                    <div>
                                        <Label className="text-sm font-medium text-slate-600 dark:text-slate-400">Join Date</Label>
                                        <p className="text-slate-900 dark:text-white">{viewingStaff.join_date || 'Not provided'}</p>
                                    </div>
                                    <div>
                                        <Label className="text-sm font-medium text-slate-600 dark:text-slate-400">Last Active</Label>
                                        <p className="text-slate-900 dark:text-white">{viewingStaff.last_active}</p>
                                    </div>
                                </div>
                                <div>
                                    <Label className="text-sm font-medium text-slate-600 dark:text-slate-400">Notes</Label>
                                    <p className="text-slate-900 dark:text-white">{viewingStaff.notes || 'No notes available'}</p>
                                </div>
                            </div>
                        </div>
                    )}
                    <DialogFooter>
                        <Button variant="outline" onClick={handleCancel}>
                            <X className="mr-2 h-4 w-4" />
                            Close
                        </Button>
                        <Button onClick={() => {
                            setIsViewModalOpen(false);
                            handleEditStaff(viewingStaff!);
                        }}>
                            <Edit className="mr-2 h-4 w-4" />
                            Edit Staff Member
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </AppLayout>
    );
}
