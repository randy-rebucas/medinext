import { Head, router, useForm, usePage } from '@inertiajs/react';
import { useState, useEffect } from 'react';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem, type StaffManagementData, type StaffMember, type StaffFormData } from '@/types';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
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
    X,
    Upload,
    Download,
    FileText,
    AlertCircle,
    CheckCircle
} from 'lucide-react';

// Simple toast implementation using browser notifications
const toast = {
    success: (message: string) => {
        // Create a temporary success notification
        const notification = document.createElement('div');
        notification.className = 'fixed top-4 right-4 z-50 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg transform transition-all duration-300 ease-in-out';
        notification.textContent = message;
        document.body.appendChild(notification);
        
        // Animate in
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 100);
        
        // Remove after 3 seconds
        setTimeout(() => {
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => {
                document.body.removeChild(notification);
            }, 300);
        }, 3000);
    },
    error: (message: string) => {
        // Create a temporary error notification
        const notification = document.createElement('div');
        notification.className = 'fixed top-4 right-4 z-50 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg transform transition-all duration-300 ease-in-out';
        notification.textContent = message;
        document.body.appendChild(notification);
        
        // Animate in
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 100);
        
        // Remove after 3 seconds
        setTimeout(() => {
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => {
                document.body.removeChild(notification);
            }, 300);
        }, 3000);
    },
};

interface StaffManagementProps {
    staff: StaffManagementData['staff'];
    roles: StaffManagementData['roles'];
    departments: StaffManagementData['departments'];
    permissions: StaffManagementData['permissions'];
}

export default function StaffManagement({ staff, roles, departments }: StaffManagementProps) {
    const { props } = usePage();
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Staff Management',
            href: '/admin/dashboard',
        },
    ];
    
    // Inertia form for creating/updating staff
    const { data, setData, post, put, processing, errors: formErrors, reset } = useForm({
        name: '',
        email: '',
        phone: '',
        role: '',
        department: '',
        status: 'Active',
        join_date: '',
        address: '',
        emergency_contact: '',
        emergency_phone: '',
        notes: ''
    });
    
    const [searchTerm, setSearchTerm] = useState('');
    const [roleFilter, setRoleFilter] = useState('all');
    const [statusFilter, setStatusFilter] = useState('all');
    const [isAddModalOpen, setIsAddModalOpen] = useState(false);
    const [isEditModalOpen, setIsEditModalOpen] = useState(false);
    const [isViewModalOpen, setIsViewModalOpen] = useState(false);
    const [isDeleteModalOpen, setIsDeleteModalOpen] = useState(false);
    const [isImportModalOpen, setIsImportModalOpen] = useState(false);
    const [editingStaff, setEditingStaff] = useState<StaffMember | null>(null);
    const [viewingStaff, setViewingStaff] = useState<StaffMember | null>(null);
    const [deletingStaff, setDeletingStaff] = useState<StaffMember | null>(null);
    const [isImporting, setIsImporting] = useState(false);
    const [importErrors, setImportErrors] = useState<string[]>([]);
    const [importResult, setImportResult] = useState<{
        total_rows: number;
        successful_imports: number;
        failed_imports: number;
        errors: string[];
    } | null>(null);
    const [selectedFile, setSelectedFile] = useState<File | null>(null);
    
    // Additional state variables
    const [notification, setNotification] = useState<{ type: 'success' | 'error' | 'info'; message: string } | null>(null);
    const [formData, setFormData] = useState<StaffFormData>({
        name: '',
        email: '',
        phone: '',
        role: '',
        department: '',
        status: 'Active',
        join_date: '',
        address: '',
        emergency_contact: '',
        emergency_phone: '',
        notes: ''
    });
    const [isLoading, setIsLoading] = useState(false);
    const [validationErrors, setValidationErrors] = useState<Record<string, string>>({});

    // Handle flash messages
    useEffect(() => {
        const flash = props.flash as any;
        if (flash?.success) {
            toast.success(flash.success);
        }
        if (flash?.error) {
            toast.error(flash.error);
        }
    }, [props.flash]);

    // Ensure staff is an array
    const staffArray = Array.isArray(staff) ? staff : [];

    const filteredStaff = staffArray.filter(member => {
        if (!member) return false;
        
        const matchesSearch = (member.name || '').toLowerCase().includes(searchTerm.toLowerCase()) ||
                            (member.email || '').toLowerCase().includes(searchTerm.toLowerCase()) ||
                            (member.department || '').toLowerCase().includes(searchTerm.toLowerCase());
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
        reset();
    };

    const handleImportStaff = () => {
        setIsImportModalOpen(true);
        setSelectedFile(null);
        setImportErrors([]);
        setImportResult(null);
    };

    const handleFileSelect = (event: React.ChangeEvent<HTMLInputElement>) => {
        const file = event.target.files?.[0];
        if (file) {
            // Validate file type
            const allowedTypes = [
                'text/csv',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            ];
            
            if (!allowedTypes.includes(file.type) && !file.name.match(/\.(csv|xlsx|xls)$/i)) {
                setImportErrors(['Please select a valid CSV or Excel file.']);
                return;
            }

            // Validate file size (10MB max)
            if (file.size > 10 * 1024 * 1024) {
                setImportErrors(['File size must not exceed 10MB.']);
                return;
            }

            setSelectedFile(file);
            setImportErrors([]);
        }
    };

    const handleDownloadTemplate = async () => {
        try {
            const response = await fetch('/admin/staff/import/template', {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (response.ok) {
                const blob = await response.blob();
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'staff_import_template.csv';
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                document.body.removeChild(a);
                showNotification('success', 'Template downloaded successfully!');
            } else {
                showNotification('error', 'Failed to download template.');
            }
        } catch (error) {
            console.error('Error downloading template:', error);
            showNotification('error', 'Failed to download template.');
        }
    };

    const handleImportSubmit = async () => {
        if (!selectedFile) {
            setImportErrors(['Please select a file to import.']);
            return;
        }

        setIsImporting(true);
        setImportErrors([]);
        setImportResult(null);

        try {
            const formData = new FormData();
            formData.append('import_file', selectedFile);

            const response = await fetch('/admin/staff/import', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: formData,
            });

            const data = await response.json();

            if (response.ok && data.success) {
                setImportResult(data.data);
                showNotification('success', data.message);
                
                // Close modal and refresh page after a delay
                setTimeout(() => {
                    setIsImportModalOpen(false);
                    window.location.reload();
                }, 2000);
            } else {
                setImportErrors([data.message || 'Import failed. Please check your file and try again.']);
                if (data.data && data.data.errors) {
                    setImportErrors(data.data.errors);
                }
            }
        } catch (error) {
            console.error('Error importing staff:', error);
            setImportErrors(['Failed to import staff. Please check your connection and try again.']);
        } finally {
            setIsImporting(false);
        }
    };

    const handleViewStaff = (staffMember: StaffMember) => {
        setViewingStaff(staffMember);
        setIsViewModalOpen(true);
    };

    const handleEditStaff = (staffMember: StaffMember) => {
        setEditingStaff(staffMember);
        setData({
            name: staffMember.name,
            email: staffMember.email,
            phone: staffMember.phone,
            role: staffMember.role,
            department: staffMember.department,
            status: staffMember.status,
            join_date: staffMember.join_date || '',
            address: staffMember.address || '',
            emergency_contact: staffMember.emergency_contact || '',
            emergency_phone: staffMember.emergency_phone || '',
            notes: staffMember.notes || ''
        });
        setIsEditModalOpen(true);
    };

    const showNotification = (type: 'success' | 'error' | 'info', message: string) => {
        setNotification({ type, message });
        setTimeout(() => setNotification(null), 5000);
    };

    const handleSaveStaff = async () => {
        // Validate form data
        const formData = data;
        const errors: Record<string, string> = {};

        // Status validation
        if (!formData.status || !['Active', 'On Leave', 'Inactive'].includes(formData.status)) {
            errors.status = 'Status must be Active, On Leave, or Inactive';
        }

        // Address validation (optional)
        if (formData.address && formData.address.length > 500) {
            errors.address = 'Address must not exceed 500 characters';
        }

        // Emergency contact validation (optional)
        if (formData.emergency_contact) {
            if (formData.emergency_contact.length > 255) {
                errors.emergency_contact = 'Emergency contact name must not exceed 255 characters';
            } else if (!/^[a-zA-Z\s\-\.\']+$/.test(formData.emergency_contact.trim())) {
                errors.emergency_contact = 'Emergency contact name can only contain letters, spaces, hyphens, dots, and apostrophes';
            }
        }

        // Emergency phone validation (optional)
        if (formData.emergency_phone) {
            if (formData.emergency_phone.length > 20) {
                errors.emergency_phone = 'Emergency phone number must not exceed 20 characters';
            } else if (!/^[\+]?[0-9\s\-\(\)]{10,20}$/.test(formData.emergency_phone.trim())) {
                errors.emergency_phone = 'Please enter a valid emergency phone number (10-20 digits)';
            }
        }

        // Notes validation (optional)
        if (formData.notes && formData.notes.length > 1000) {
            errors.notes = 'Notes must not exceed 1000 characters';
        }

        if (Object.keys(errors).length > 0) {
            setValidationErrors(errors);
            showNotification('error', 'Please fix the validation errors before submitting.');
            return;
        }

        setIsLoading(true);

        try {
            // Create form data for submission
            const submitData = new FormData();
            
            // Add all form fields to FormData
            Object.entries(formData).forEach(([key, value]) => {
                if (value !== null && value !== undefined && value !== '') {
                    submitData.append(key, String(value));
                }
            });

            // Get CSRF token from multiple possible sources
            let csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
                           document.querySelector('meta[name="csrf-token"]')?.getAttribute('value') ||
                           (window as any).Laravel?.csrfToken ||
                           (window as any).csrfToken;
            
            // If still not found, try to get it from the form or make a request to get it
            if (!csrfToken) {
                try {
                    // Try to get CSRF token from a form on the page
                    const csrfInput = document.querySelector('input[name="_token"]') as HTMLInputElement;
                    if (csrfInput) {
                        csrfToken = csrfInput.value;
                    }
                } catch (e) {
                    console.warn('Could not find CSRF token from form input');
                }
            }
            
            if (!csrfToken) {
                console.error('CSRF token not found. Available meta tags:', 
                    Array.from(document.querySelectorAll('meta')).map(m => ({ name: m.getAttribute('name'), content: m.getAttribute('content') }))
                );
                
                // Try to fetch CSRF token from the server
                try {
                    const csrfResponse = await fetch('/csrf-token', {
                        method: 'GET',
                        credentials: 'same-origin',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    
                    if (csrfResponse.ok) {
                        const csrfData = await csrfResponse.json();
                        csrfToken = csrfData.csrf_token;
                        console.log('CSRF token fetched from server');
                    }
                } catch (csrfError) {
                    console.warn('Could not fetch CSRF token from server:', csrfError);
                }
                
                if (!csrfToken) {
                    showNotification('error', 'Security token not found. Please refresh the page and try again.');
                    return;
                }
            }

            const headers = {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            };

            console.log('Using CSRF token:', csrfToken ? 'Found' : 'Not found');

            let response: Response;
            let successMessage: string;

            if (editingStaff) {
                // Update existing staff member
                submitData.append('_method', 'PUT');
                console.log('Updating staff member:', editingStaff.id);
                console.log('Form data:', Object.fromEntries(submitData.entries()));
                
                response = await fetch(`/admin/staff/${editingStaff.id}`, {
                    method: 'POST',
                    headers,
                    body: submitData,
                });
                successMessage = 'Staff member updated successfully!';
            } else {
                // Create new staff member
                console.log('Creating new staff member');
                console.log('Form data:', Object.fromEntries(submitData.entries()));
                
                response = await fetch('/admin/staff', {
                    method: 'POST',
                    headers,
                    body: submitData,
                });
                successMessage = 'Staff member added successfully!';
            }

            console.log('Response status:', response.status);
            console.log('Response headers:', Object.fromEntries(response.headers.entries()));

            // Handle response based on content type
            const contentType = response.headers.get('content-type');
            console.log('Response content type:', contentType);
            
            if (response.ok) {
                // Success response
                if (contentType && contentType.includes('application/json')) {
                    // JSON response
                    const data = await response.json();
                    console.log('Success response data:', data);
                    
                    if (data.success) {
                        showNotification('success', data.message || successMessage);
                        
                        // Reset form and close modals
                        setFormData({
                            name: '',
                            email: '',
                            phone: '',
                            role: '',
                            department: '',
                            status: 'Active',
                            join_date: '',
                            address: '',
                            emergency_contact: '',
                            emergency_phone: '',
                            notes: ''
                        });
                        setValidationErrors({});
                        setIsAddModalOpen(false);
                        setIsEditModalOpen(false);
                        setEditingStaff(null);
                        
                        // Refresh the page to show updated data
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        showNotification('error', data.message || 'Operation failed');
                    }
                } else {
                    // HTML response (redirect)
                    showNotification('success', successMessage);
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                }
            } else {
                // Error response
                console.log('Error response status:', response.status);
                
                if (contentType && contentType.includes('application/json')) {
                    // JSON error response
                    const data = await response.json();
                    console.log('Error response data:', data);
                    
                    if (data.errors) {
                        setValidationErrors(data.errors);
                        showNotification('error', data.message || 'Please fix the validation errors.');
                    } else if (data.message) {
                        showNotification('error', data.message);
                    } else {
                        showNotification('error', `Failed to ${editingStaff ? 'update' : 'create'} staff member. Please try again.`);
                    }
                } else {
                    // HTML error response
                    const responseText = await response.text();
                    console.log('Error response text:', responseText);
                    
                    if (responseText.includes('<!DOCTYPE html>') || responseText.includes('<html')) {
                        // This might be a redirect response even with error status
                        showNotification('success', successMessage);
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        showNotification('error', `Failed to ${editingStaff ? 'update' : 'create'} staff member. Please try again.`);
                    }
                }
            }
        } catch (error) {
            console.error(`Error ${editingStaff ? 'updating' : 'creating'} staff:`, error);
            showNotification('error', `Failed to ${editingStaff ? 'update' : 'create'} staff member. Please check your connection and try again.`);
        } finally {
            setIsLoading(false);
        }
    };

    const handleDeleteStaff = (staff: StaffMember) => {
        setDeletingStaff(staff);
        setIsDeleteModalOpen(true);
    };

    const confirmDeleteStaff = () => {
        if (!deletingStaff) return;

        router.delete(`/admin/staff/${deletingStaff.id}`, {
            onSuccess: () => {
                setIsDeleteModalOpen(false);
                setDeletingStaff(null);
            }
        });
    };

    const handleCancel = () => {
        setIsAddModalOpen(false);
        setIsEditModalOpen(false);
        setIsViewModalOpen(false);
        setIsDeleteModalOpen(false);
        setIsImportModalOpen(false);
        setEditingStaff(null);
        setViewingStaff(null);
        setDeletingStaff(null);
        setSelectedFile(null);
        setImportErrors([]);
        reset();
        setImportResult(null);
    };

    // Show loading state if no data
    if (!staff && !roles && !departments) {
        return (
            <AppLayout breadcrumbs={breadcrumbs}>
                <Head title="Staff Management - Medinext" />
                <div className="flex items-center justify-center min-h-screen">
                    <div className="text-center">
                        <div className="animate-spin rounded-full h-32 w-32 border-b-2 border-blue-600 mx-auto"></div>
                        <p className="mt-4 text-gray-600">Loading staff management...</p>
                    </div>
                </div>
            </AppLayout>
        );
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Staff Management - Medinext">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&family=instrument-sans:400,500,600" rel="stylesheet" />
            </Head>

            <div className="min-h-screen bg-gradient-to-br from-slate-50 via-white to-blue-50 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900">
                {/* Notification Display */}
                {notification && (
                    <div className={`fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg max-w-md ${
                        notification.type === 'success' 
                            ? 'bg-green-100 border border-green-400 text-green-700 dark:bg-green-900/20 dark:border-green-600 dark:text-green-400'
                            : notification.type === 'error'
                            ? 'bg-red-100 border border-red-400 text-red-700 dark:bg-red-900/20 dark:border-red-600 dark:text-red-400'
                            : 'bg-blue-100 border border-blue-400 text-blue-700 dark:bg-blue-900/20 dark:border-blue-600 dark:text-blue-400'
                    }`}>
                        <div className="flex items-center justify-between">
                            <p className="font-medium">{notification.message}</p>
                            <button
                                onClick={() => setNotification(null)}
                                className="ml-4 text-current hover:opacity-70"
                            >
                                <X className="h-4 w-4" />
                            </button>
                        </div>
                    </div>
                )}
                
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
                                        onClick={handleImportStaff}
                                        className="border-slate-300 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-700"
                                    >
                                        <Upload className="mr-2 h-4 w-4" />
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
                                                            onClick={() => handleDeleteStaff(member)}
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
            {isAddModalOpen && (
                <div className="fixed inset-0 z-[9999]">
                    <div className="fixed inset-0 bg-black/50" onClick={() => setIsAddModalOpen(false)} />
                    <div className="fixed right-0 top-0 h-full w-[50vw] min-w-[600px] max-w-[800px] bg-white dark:bg-slate-800 border-l border-slate-200 dark:border-slate-700 rounded-l-lg shadow-2xl flex flex-col animate-in slide-in-from-right duration-300">
                    <div className="p-6 pb-4 border-b border-slate-200 dark:border-slate-700">
                        <h2 className="text-lg font-semibold text-slate-900 dark:text-white">Add New Staff Member</h2>
                        <p className="text-sm text-slate-600 dark:text-slate-400 mt-1">
                            Enter the details for the new staff member.
                        </p>
                    </div>
                    <div className="flex-1 overflow-y-auto p-6">
                    <div className="grid gap-4 py-4">
                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="name">Full Name *</Label>
                                <Input
                                    id="name"
                                    value={data.name}
                                    onChange={(e) => setData('name', e.target.value)}
                                    placeholder="Enter full name"
                                    className={formErrors.name ? "border-red-500" : ""}
                                    disabled={processing}
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
                                    value={data.email}
                                    onChange={(e) => setData('email', e.target.value)}
                                    placeholder="Enter email address"
                                    className={formErrors.email ? "border-red-500" : ""}
                                    disabled={processing}
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
                                    disabled={isLoading}
                                />
                                {formErrors.phone && (
                                    <p className="text-sm text-red-500">{formErrors.phone}</p>
                                )}
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="role">Role *</Label>
                                <Select value={formData.role} onValueChange={(value) => setFormData({...formData, role: value})} disabled={isLoading}>
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
                                <Select value={formData.department} onValueChange={(value) => setFormData({...formData, department: value})} disabled={isLoading}>
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
                                <Select value={formData.status} onValueChange={(value) => setFormData({...formData, status: value as 'Active' | 'On Leave' | 'Inactive'})} disabled={isLoading}>
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
                                className={formErrors.address ? "border-red-500" : ""}
                                disabled={isLoading}
                            />
                            {formErrors.address && (
                                <p className="text-sm text-red-500">{formErrors.address}</p>
                            )}
                        </div>
                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="emergencyContact">Emergency Contact</Label>
                                <Input
                                    id="emergencyContact"
                                    value={formData.emergency_contact}
                                    onChange={(e) => setFormData({...formData, emergency_contact: e.target.value})}
                                    placeholder="Emergency contact name"
                                    className={formErrors.emergency_contact ? "border-red-500" : ""}
                                    disabled={isLoading}
                                />
                                {formErrors.emergency_contact && (
                                    <p className="text-sm text-red-500">{formErrors.emergency_contact}</p>
                                )}
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="emergencyPhone">Emergency Phone</Label>
                                <Input
                                    id="emergencyPhone"
                                    value={formData.emergency_phone}
                                    onChange={(e) => setFormData({...formData, emergency_phone: e.target.value})}
                                    placeholder="Emergency contact phone"
                                    className={formErrors.emergency_phone ? "border-red-500" : ""}
                                    disabled={isLoading}
                                />
                                {formErrors.emergency_phone && (
                                    <p className="text-sm text-red-500">{formErrors.emergency_phone}</p>
                                )}
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
                                className={formErrors.notes ? "border-red-500" : ""}
                                disabled={isLoading}
                            />
                            {formErrors.notes && (
                                <p className="text-sm text-red-500">{formErrors.notes}</p>
                            )}
                        </div>
                    </div>
                    </div>
                    <div className="p-6 pt-4 border-t border-slate-200 dark:border-slate-700 flex justify-end space-x-3">
                        <Button variant="outline" onClick={handleCancel} disabled={processing}>
                            <X className="mr-2 h-4 w-4" />
                            Cancel
                        </Button>
                        <Button onClick={handleSaveStaff} disabled={processing}>
                            <Save className="mr-2 h-4 w-4" />
                            {processing ? 'Adding...' : 'Add Staff Member'}
                        </Button>
                    </div>
                    </div>
                </div>
            )}

            {/* Edit Staff Modal */}
            {isEditModalOpen && (
                <div className="fixed inset-0 z-[9999]">
                    <div className="fixed inset-0 bg-black/50" onClick={() => setIsEditModalOpen(false)} />
                    <div className="fixed right-0 top-0 h-full w-[50vw] min-w-[600px] max-w-[800px] bg-white dark:bg-slate-800 border-l border-slate-200 dark:border-slate-700 rounded-l-lg shadow-2xl flex flex-col animate-in slide-in-from-right duration-300">
                    <div className="p-6 pb-4 border-b border-slate-200 dark:border-slate-700">
                        <h2 className="text-lg font-semibold text-slate-900 dark:text-white">Edit Staff Member</h2>
                        <p className="text-sm text-slate-600 dark:text-slate-400 mt-1">
                            Update the details for {editingStaff?.name}.
                        </p>
                    </div>
                    <div className="flex-1 overflow-y-auto p-6">
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
                                <Select value={formData.role} onValueChange={(value) => setFormData({...formData, role: value})} disabled={isLoading}>
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
                                <Select value={formData.department} onValueChange={(value) => setFormData({...formData, department: value})} disabled={isLoading}>
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
                                <Select value={formData.status} onValueChange={(value) => setFormData({...formData, status: value as 'Active' | 'On Leave' | 'Inactive'})} disabled={isLoading}>
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
                                className={formErrors.address ? "border-red-500" : ""}
                                disabled={isLoading}
                            />
                            {formErrors.address && (
                                <p className="text-sm text-red-500">{formErrors.address}</p>
                            )}
                        </div>
                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="edit-emergencyContact">Emergency Contact</Label>
                                <Input
                                    id="edit-emergencyContact"
                                    value={formData.emergency_contact}
                                    onChange={(e) => setFormData({...formData, emergency_contact: e.target.value})}
                                    placeholder="Emergency contact name"
                                    className={formErrors.emergency_contact ? "border-red-500" : ""}
                                    disabled={isLoading}
                                />
                                {formErrors.emergency_contact && (
                                    <p className="text-sm text-red-500">{formErrors.emergency_contact}</p>
                                )}
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="edit-emergencyPhone">Emergency Phone</Label>
                                <Input
                                    id="edit-emergencyPhone"
                                    value={formData.emergency_phone}
                                    onChange={(e) => setFormData({...formData, emergency_phone: e.target.value})}
                                    placeholder="Emergency contact phone"
                                    className={formErrors.emergency_phone ? "border-red-500" : ""}
                                    disabled={isLoading}
                                />
                                {formErrors.emergency_phone && (
                                    <p className="text-sm text-red-500">{formErrors.emergency_phone}</p>
                                )}
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
                                className={formErrors.notes ? "border-red-500" : ""}
                                disabled={isLoading}
                            />
                            {formErrors.notes && (
                                <p className="text-sm text-red-500">{formErrors.notes}</p>
                            )}
                        </div>
                    </div>
                    </div>
                    <div className="p-6 pt-4 border-t border-slate-200 dark:border-slate-700 flex justify-end space-x-3">
                        <Button variant="outline" onClick={handleCancel} disabled={processing}>
                            <X className="mr-2 h-4 w-4" />
                            Cancel
                        </Button>
                        <Button onClick={handleSaveStaff} disabled={processing}>
                            <Save className="mr-2 h-4 w-4" />
                            {processing ? 'Updating...' : 'Update Staff Member'}
                        </Button>
                    </div>
                    </div>
                </div>
            )}

            {/* View Staff Modal */}
            {isViewModalOpen && (
                <div className="fixed inset-0 z-[9999]">
                    <div className="fixed inset-0 bg-black/50" onClick={() => setIsViewModalOpen(false)} />
                    <div className="fixed right-0 top-0 h-full w-[50vw] min-w-[600px] max-w-[800px] bg-white dark:bg-slate-800 border-l border-slate-200 dark:border-slate-700 rounded-l-lg shadow-2xl flex flex-col animate-in slide-in-from-right duration-300">
                    <div className="p-6 pb-4 border-b border-slate-200 dark:border-slate-700">
                        <h2 className="text-lg font-semibold text-slate-900 dark:text-white">Staff Member Details</h2>
                        <p className="text-sm text-slate-600 dark:text-slate-400 mt-1">
                            View details for {viewingStaff?.name}.
                        </p>
                    </div>
                    <div className="flex-1 overflow-y-auto p-6">
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
                    </div>
                    <div className="p-6 pt-4 border-t border-slate-200 dark:border-slate-700 flex justify-end space-x-3">
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
                    </div>
                    </div>
                </div>
            )}

            {/* Delete Confirmation Modal */}
            {isDeleteModalOpen && (
                <div className="fixed inset-0 z-[9999]">
                    <div className="fixed inset-0 bg-black/50" onClick={() => setIsDeleteModalOpen(false)} />
                    <div className="fixed right-0 top-0 h-full w-[40vw] min-w-[500px] max-w-[600px] bg-white dark:bg-slate-800 border-l border-slate-200 dark:border-slate-700 rounded-l-lg shadow-2xl flex flex-col animate-in slide-in-from-right duration-300">
                    <div className="p-6 pb-4 border-b border-slate-200 dark:border-slate-700">
                        <h2 className="text-lg font-semibold text-slate-900 dark:text-white">Confirm Deactivation</h2>
                        <p className="text-sm text-slate-600 dark:text-slate-400 mt-1">
                            Are you sure you want to deactivate {deletingStaff?.name}? This action can be reversed by editing the staff member.
                        </p>
                    </div>
                    <div className="flex-1 overflow-y-auto p-6">
                        <div className="flex items-center space-x-3 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                            <AlertCircle className="h-5 w-5 text-red-600 dark:text-red-400" />
                            <div>
                                <p className="text-sm font-medium text-red-800 dark:text-red-200">
                                    Warning: This action cannot be undone
                                </p>
                                <p className="text-sm text-red-600 dark:text-red-400">
                                    The staff member will be deactivated and lose access to the system.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div className="p-6 pt-4 border-t border-slate-200 dark:border-slate-700 flex justify-end space-x-3">
                        <Button variant="outline" onClick={handleCancel} disabled={processing}>
                            Cancel
                        </Button>
                        <Button 
                            variant="destructive" 
                            onClick={confirmDeleteStaff} 
                            disabled={processing}
                        >
                            {processing ? 'Deactivating...' : 'Deactivate Staff'}
                        </Button>
                    </div>
                    </div>
                </div>
            )}

            {/* Import Staff Modal */}
            {isImportModalOpen && (
                <div className="fixed inset-0 z-[9999]">
                    <div className="fixed inset-0 bg-black/50" onClick={() => setIsImportModalOpen(false)} />
                    <div className="fixed right-0 top-0 h-full w-[50vw] min-w-[600px] max-w-[800px] bg-white dark:bg-slate-800 border-l border-slate-200 dark:border-slate-700 rounded-l-lg shadow-2xl flex flex-col animate-in slide-in-from-right duration-300">
                    <div className="p-6 pb-4 border-b border-slate-200 dark:border-slate-700">
                        <h2 className="text-lg font-semibold text-slate-900 dark:text-white">Import Staff Members</h2>
                        <p className="text-sm text-slate-600 dark:text-slate-400 mt-1">
                            Upload a CSV or Excel file to import multiple staff members at once.
                        </p>
                    </div>
                    <div className="flex-1 overflow-y-auto p-6">
                    
                    <div className="space-y-6 py-4">
                        {/* Template Download Section */}
                        <div className="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                            <div className="flex items-start space-x-3">
                                <FileText className="h-5 w-5 text-blue-600 dark:text-blue-400 mt-0.5" />
                                <div className="flex-1">
                                    <h3 className="text-sm font-medium text-blue-900 dark:text-blue-100">
                                        Download Template
                                    </h3>
                                    <p className="text-sm text-blue-700 dark:text-blue-300 mt-1">
                                        Download our CSV template to ensure your file has the correct format and column headers.
                                    </p>
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        onClick={handleDownloadTemplate}
                                        className="mt-2 border-blue-300 text-blue-700 hover:bg-blue-100 dark:border-blue-600 dark:text-blue-300 dark:hover:bg-blue-800"
                                    >
                                        <Download className="mr-2 h-4 w-4" />
                                        Download Template
                                    </Button>
                                </div>
                            </div>
                        </div>

                        {/* File Upload Section */}
                        <div className="space-y-4">
                            <div>
                                <Label htmlFor="import-file" className="text-sm font-medium">
                                    Select File *
                                </Label>
                                <div className="mt-2">
                                    <Input
                                        id="import-file"
                                        type="file"
                                        accept=".csv,.xlsx,.xls"
                                        onChange={handleFileSelect}
                                        className="file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-blue-900/20 dark:file:text-blue-300"
                                        disabled={isImporting}
                                    />
                                </div>
                                <p className="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                    Supported formats: CSV, Excel (.xlsx, .xls). Maximum file size: 10MB.
                                </p>
                            </div>

                            {/* Selected File Display */}
                            {selectedFile && (
                                <div className="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-3">
                                    <div className="flex items-center space-x-2">
                                        <CheckCircle className="h-4 w-4 text-green-600 dark:text-green-400" />
                                        <span className="text-sm font-medium text-green-900 dark:text-green-100">
                                            Selected: {selectedFile.name}
                                        </span>
                                        <span className="text-xs text-green-700 dark:text-green-300">
                                            ({(selectedFile.size / 1024 / 1024).toFixed(2)} MB)
                                        </span>
                                    </div>
                                </div>
                            )}

                            {/* Import Errors */}
                            {importErrors.length > 0 && (
                                <div className="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                                    <div className="flex items-start space-x-2">
                                        <AlertCircle className="h-5 w-5 text-red-600 dark:text-red-400 mt-0.5" />
                                        <div className="flex-1">
                                            <h3 className="text-sm font-medium text-red-900 dark:text-red-100">
                                                Import Errors
                                            </h3>
                                            <ul className="text-sm text-red-700 dark:text-red-300 mt-1 space-y-1">
                                                {importErrors.map((error, index) => (
                                                    <li key={index}>• {error}</li>
                                                ))}
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            )}

                            {/* Import Results */}
                            {importResult && (
                                <div className="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                                    <div className="flex items-start space-x-2">
                                        <CheckCircle className="h-5 w-5 text-green-600 dark:text-green-400 mt-0.5" />
                                        <div className="flex-1">
                                            <h3 className="text-sm font-medium text-green-900 dark:text-green-100">
                                                Import Results
                                            </h3>
                                            <div className="text-sm text-green-700 dark:text-green-300 mt-1 space-y-1">
                                                <p>• Total rows processed: {importResult.total_rows}</p>
                                                <p>• Successfully imported: {importResult.successful_imports}</p>
                                                {importResult.failed_imports > 0 && (
                                                    <p>• Failed imports: {importResult.failed_imports}</p>
                                                )}
                                            </div>
                                            {importResult.errors.length > 0 && (
                                                <div className="mt-2">
                                                    <p className="text-xs font-medium text-red-700 dark:text-red-300">
                                                        Errors:
                                                    </p>
                                                    <ul className="text-xs text-red-600 dark:text-red-400 mt-1 space-y-1">
                                                        {importResult.errors.slice(0, 5).map((error, index) => (
                                                            <li key={index}>• {error}</li>
                                                        ))}
                                                        {importResult.errors.length > 5 && (
                                                            <li>• ... and {importResult.errors.length - 5} more errors</li>
                                                        )}
                                                    </ul>
                                                </div>
                                            )}
                                        </div>
                                    </div>
                                </div>
                            )}
                        </div>

                        {/* Required Fields Info */}
                        <div className="bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 rounded-lg p-4">
                            <h3 className="text-sm font-medium text-slate-900 dark:text-slate-100 mb-2">
                                Required Fields
                            </h3>
                            <div className="grid grid-cols-2 gap-2 text-xs text-slate-600 dark:text-slate-400">
                                <div>• Name (Full Name)</div>
                                <div>• Email Address</div>
                                <div>• Role</div>
                                <div>• Department</div>
                            </div>
                            <h3 className="text-sm font-medium text-slate-900 dark:text-slate-100 mb-2 mt-3">
                                Optional Fields
                            </h3>
                            <div className="grid grid-cols-2 gap-2 text-xs text-slate-600 dark:text-slate-400">
                                <div>• Phone Number</div>
                                <div>• Status (Active/On Leave/Inactive)</div>
                                <div>• Address</div>
                                <div>• Emergency Contact</div>
                                <div>• Emergency Phone</div>
                                <div>• Notes</div>
                            </div>
                        </div>
                    </div>

                    </div>
                    <div className="p-6 pt-4 border-t border-slate-200 dark:border-slate-700 flex justify-end space-x-3">
                        <Button variant="outline" onClick={handleCancel} disabled={isImporting}>
                            <X className="mr-2 h-4 w-4" />
                            Cancel
                        </Button>
                        <Button 
                            onClick={handleImportSubmit} 
                            disabled={!selectedFile || isImporting}
                            className="bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white"
                        >
                            <Upload className="mr-2 h-4 w-4" />
                            {isImporting ? 'Importing...' : 'Import Staff'}
                        </Button>
                    </div>
                    </div>
                </div>
            )}
        </AppLayout>
    );
}
