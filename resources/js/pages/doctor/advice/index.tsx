import { useState } from 'react';
import AppLayout from '@/layouts/app-layout';
import { doctorAdvice } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import {
    Stethoscope,
    Search,
    Filter,
    Plus,
    Eye,
    Edit,
    Download,
    CheckCircle,
    XCircle,
    Clock,
    AlertTriangle,
    FileText,
    Calendar,
    User,
    Heart,
    Brain,
    Activity,
    Shield
} from 'lucide-react';
import { Link } from '@inertiajs/react';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Checkbox } from '@/components/ui/checkbox';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Doctor Dashboard',
        href: '/doctor/dashboard',
    },
    {
        title: 'Medical Advice',
        href: doctorAdvice().url,
    },
];

interface MedicalAdvice {
    id: number;
    patient_id: number;
    patient_name: string;
    category: string;
    title: string;
    content: string;
    priority: string;
    status: string;
    created_at: string;
    updated_at: string;
    tags: string[];
    attachments: string[];
    follow_up_date?: string;
    doctor_notes?: string;
}

interface Patient {
    id: number;
    name: string;
    dob: string;
    sex: string;
}

interface DoctorAdviceProps {
    advice: MedicalAdvice[];
    patients: Patient[];
    filters: {
        category: string;
        priority: string;
        status: string;
        patient_id: string;
        date_range: string;
    };
}

export default function DoctorAdvice({
    advice,
    patients,
    filters
}: DoctorAdviceProps) {
    const [searchTerm, setSearchTerm] = useState('');
    const [categoryFilter, setCategoryFilter] = useState(filters.category || '');
    const [priorityFilter, setPriorityFilter] = useState(filters.priority || '');
    const [statusFilter, setStatusFilter] = useState(filters.status || '');
    const [patientFilter, setPatientFilter] = useState(filters.patient_id || '');
    const [dateRange, setDateRange] = useState(filters.date_range || '');
    const [activeTab, setActiveTab] = useState('all');
    const [isCreateDialogOpen, setIsCreateDialogOpen] = useState(false);
    const [isViewDialogOpen, setIsViewDialogOpen] = useState(false);
    const [selectedAdvice, setSelectedAdvice] = useState<MedicalAdvice | null>(null);

    const getCategoryIcon = (category: string) => {
        switch (category) {
            case 'general':
                return <Stethoscope className="h-4 w-4" />;
            case 'cardiology':
                return <Heart className="h-4 w-4" />;
            case 'neurology':
                return <Brain className="h-4 w-4" />;
            case 'emergency':
                return <AlertTriangle className="h-4 w-4" />;
            case 'preventive':
                return <Shield className="h-4 w-4" />;
            case 'follow_up':
                return <Activity className="h-4 w-4" />;
            default:
                return <FileText className="h-4 w-4" />;
        }
    };

    const getCategoryColor = (category: string) => {
        switch (category) {
            case 'general':
                return 'bg-blue-100 text-blue-800';
            case 'cardiology':
                return 'bg-red-100 text-red-800';
            case 'neurology':
                return 'bg-purple-100 text-purple-800';
            case 'emergency':
                return 'bg-orange-100 text-orange-800';
            case 'preventive':
                return 'bg-green-100 text-green-800';
            case 'follow_up':
                return 'bg-yellow-100 text-yellow-800';
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

    const getStatusColor = (status: string) => {
        switch (status) {
            case 'active':
            case 'completed':
                return 'bg-green-100 text-green-800';
            case 'pending':
            case 'draft':
                return 'bg-yellow-100 text-yellow-800';
            case 'cancelled':
            case 'expired':
                return 'bg-red-100 text-red-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    };

    const filteredAdvice = advice.filter(item => {
        const matchesSearch = item.title.toLowerCase().includes(searchTerm.toLowerCase()) ||
                            item.content.toLowerCase().includes(searchTerm.toLowerCase()) ||
                            item.patient_name.toLowerCase().includes(searchTerm.toLowerCase());
        const matchesCategory = !categoryFilter || item.category === categoryFilter;
        const matchesPriority = !priorityFilter || item.priority === priorityFilter;
        const matchesStatus = !statusFilter || item.status === statusFilter;
        const matchesPatient = !patientFilter || item.patient_id.toString() === patientFilter;

        return matchesSearch && matchesCategory && matchesPriority && matchesStatus && matchesPatient;
    });

    const getTabCounts = () => {
        return {
            all: filteredAdvice.length,
            active: filteredAdvice.filter(a => a.status === 'active').length,
            pending: filteredAdvice.filter(a => a.status === 'pending').length,
            completed: filteredAdvice.filter(a => a.status === 'completed').length,
            urgent: filteredAdvice.filter(a => a.priority === 'urgent' || a.priority === 'emergency').length
        };
    };

    const tabCounts = getTabCounts();

    const handleViewAdvice = (adviceItem: MedicalAdvice) => {
        setSelectedAdvice(adviceItem);
        setIsViewDialogOpen(true);
    };

    const handleUpdateStatus = (adviceId: number, newStatus: string) => {
        // Handle status update via API
        console.log('Update advice status:', adviceId, newStatus);
    };

    const handleDeleteAdvice = (adviceId: number) => {
        if (confirm('Are you sure you want to delete this medical advice?')) {
            // Handle deletion via API
            console.log('Delete advice:', adviceId);
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Medical Advice" />
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Medical Advice</h1>
                        <p className="text-muted-foreground">
                            Manage medical advice, recommendations, and patient guidance
                        </p>
                    </div>
                    <div className="flex gap-2">
                        <Button variant="outline">
                            <Download className="h-4 w-4 mr-2" />
                            Export
                        </Button>
                        <Dialog open={isCreateDialogOpen} onOpenChange={setIsCreateDialogOpen}>
                            <DialogTrigger asChild>
                                <Button>
                                    <Plus className="h-4 w-4 mr-2" />
                                    New Advice
                                </Button>
                            </DialogTrigger>
                            <DialogContent className="max-w-4xl max-h-[90vh] overflow-y-auto">
                                <DialogHeader>
                                    <DialogTitle>Create Medical Advice</DialogTitle>
                                    <DialogDescription>
                                        Provide medical advice and recommendations for a patient
                                    </DialogDescription>
                                </DialogHeader>
                                <CreateAdviceForm
                                    patients={patients}
                                    onSuccess={() => setIsCreateDialogOpen(false)}
                                />
                            </DialogContent>
                        </Dialog>
                    </div>
                </div>

                {/* Filters */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <Filter className="h-5 w-5" />
                            Search & Filters
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-6">
                            <div className="space-y-2">
                                <Label htmlFor="search">Search</Label>
                                <div className="relative">
                                    <Search className="absolute left-2 top-2.5 h-4 w-4 text-muted-foreground" />
                                    <Input
                                        id="search"
                                        placeholder="Search advice..."
                                        value={searchTerm}
                                        onChange={(e) => setSearchTerm(e.target.value)}
                                        className="pl-8"
                                    />
                                </div>
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="category">Category</Label>
                                <Select value={categoryFilter} onValueChange={setCategoryFilter}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="All categories" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="">All categories</SelectItem>
                                        <SelectItem value="general">General</SelectItem>
                                        <SelectItem value="cardiology">Cardiology</SelectItem>
                                        <SelectItem value="neurology">Neurology</SelectItem>
                                        <SelectItem value="emergency">Emergency</SelectItem>
                                        <SelectItem value="preventive">Preventive</SelectItem>
                                        <SelectItem value="follow_up">Follow-up</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="priority">Priority</Label>
                                <Select value={priorityFilter} onValueChange={setPriorityFilter}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="All priorities" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="">All priorities</SelectItem>
                                        <SelectItem value="low">Low</SelectItem>
                                        <SelectItem value="normal">Normal</SelectItem>
                                        <SelectItem value="high">High</SelectItem>
                                        <SelectItem value="urgent">Urgent</SelectItem>
                                        <SelectItem value="emergency">Emergency</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="status">Status</Label>
                                <Select value={statusFilter} onValueChange={setStatusFilter}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="All statuses" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="">All statuses</SelectItem>
                                        <SelectItem value="draft">Draft</SelectItem>
                                        <SelectItem value="active">Active</SelectItem>
                                        <SelectItem value="pending">Pending</SelectItem>
                                        <SelectItem value="completed">Completed</SelectItem>
                                        <SelectItem value="cancelled">Cancelled</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="patient">Patient</Label>
                                <Select value={patientFilter} onValueChange={setPatientFilter}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="All patients" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="">All patients</SelectItem>
                                        {patients.map((patient) => (
                                            <SelectItem key={patient.id} value={patient.id.toString()}>
                                                {patient.name}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="date_range">Date Range</Label>
                                <Select value={dateRange} onValueChange={setDateRange}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="All dates" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="">All dates</SelectItem>
                                        <SelectItem value="today">Today</SelectItem>
                                        <SelectItem value="week">This week</SelectItem>
                                        <SelectItem value="month">This month</SelectItem>
                                        <SelectItem value="quarter">This quarter</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {/* Tabs */}
                <Tabs value={activeTab} onValueChange={setActiveTab} className="w-full">
                    <TabsList className="grid w-full grid-cols-5">
                        <TabsTrigger value="all" className="flex items-center gap-2">
                            <Stethoscope className="h-4 w-4" />
                            All ({tabCounts.all})
                        </TabsTrigger>
                        <TabsTrigger value="active" className="flex items-center gap-2">
                            <CheckCircle className="h-4 w-4" />
                            Active ({tabCounts.active})
                        </TabsTrigger>
                        <TabsTrigger value="pending" className="flex items-center gap-2">
                            <Clock className="h-4 w-4" />
                            Pending ({tabCounts.pending})
                        </TabsTrigger>
                        <TabsTrigger value="completed" className="flex items-center gap-2">
                            <CheckCircle className="h-4 w-4" />
                            Completed ({tabCounts.completed})
                        </TabsTrigger>
                        <TabsTrigger value="urgent" className="flex items-center gap-2">
                            <AlertTriangle className="h-4 w-4" />
                            Urgent ({tabCounts.urgent})
                        </TabsTrigger>
                    </TabsList>

                    <TabsContent value="all" className="space-y-4">
                        <AdviceList
                            advice={filteredAdvice}
                            onView={handleViewAdvice}
                            onUpdateStatus={handleUpdateStatus}
                            onDelete={handleDeleteAdvice}
                        />
                    </TabsContent>

                    <TabsContent value="active" className="space-y-4">
                        <AdviceList
                            advice={filteredAdvice.filter(a => a.status === 'active')}
                            onView={handleViewAdvice}
                            onUpdateStatus={handleUpdateStatus}
                            onDelete={handleDeleteAdvice}
                        />
                    </TabsContent>

                    <TabsContent value="pending" className="space-y-4">
                        <AdviceList
                            advice={filteredAdvice.filter(a => a.status === 'pending')}
                            onView={handleViewAdvice}
                            onUpdateStatus={handleUpdateStatus}
                            onDelete={handleDeleteAdvice}
                        />
                    </TabsContent>

                    <TabsContent value="completed" className="space-y-4">
                        <AdviceList
                            advice={filteredAdvice.filter(a => a.status === 'completed')}
                            onView={handleViewAdvice}
                            onUpdateStatus={handleUpdateStatus}
                            onDelete={handleDeleteAdvice}
                        />
                    </TabsContent>

                    <TabsContent value="urgent" className="space-y-4">
                        <AdviceList
                            advice={filteredAdvice.filter(a => a.priority === 'urgent' || a.priority === 'emergency')}
                            onView={handleViewAdvice}
                            onUpdateStatus={handleUpdateStatus}
                            onDelete={handleDeleteAdvice}
                        />
                    </TabsContent>
                </Tabs>

                {/* View Advice Dialog */}
                <Dialog open={isViewDialogOpen} onOpenChange={setIsViewDialogOpen}>
                    <DialogContent className="max-w-4xl max-h-[90vh] overflow-y-auto">
                        <DialogHeader>
                            <DialogTitle>Medical Advice Details</DialogTitle>
                            <DialogDescription>
                                Complete medical advice and recommendations
                            </DialogDescription>
                        </DialogHeader>
                        {selectedAdvice && (
                            <AdviceDetailsView advice={selectedAdvice} />
                        )}
                    </DialogContent>
                </Dialog>
            </div>
        </AppLayout>
    );
}

// Advice List Component
function AdviceList({
    advice,
    onView,
    onUpdateStatus,
    onDelete
}: {
    advice: MedicalAdvice[];
    onView: (advice: MedicalAdvice) => void;
    onUpdateStatus: (id: number, status: string) => void;
    onDelete: (id: number) => void;
}) {
    const getCategoryIcon = (category: string) => {
        switch (category) {
            case 'general':
                return <Stethoscope className="h-4 w-4" />;
            case 'cardiology':
                return <Heart className="h-4 w-4" />;
            case 'neurology':
                return <Brain className="h-4 w-4" />;
            case 'emergency':
                return <AlertTriangle className="h-4 w-4" />;
            case 'preventive':
                return <Shield className="h-4 w-4" />;
            case 'follow_up':
                return <Activity className="h-4 w-4" />;
            default:
                return <FileText className="h-4 w-4" />;
        }
    };

    const getCategoryColor = (category: string) => {
        switch (category) {
            case 'general':
                return 'bg-blue-100 text-blue-800';
            case 'cardiology':
                return 'bg-red-100 text-red-800';
            case 'neurology':
                return 'bg-purple-100 text-purple-800';
            case 'emergency':
                return 'bg-orange-100 text-orange-800';
            case 'preventive':
                return 'bg-green-100 text-green-800';
            case 'follow_up':
                return 'bg-yellow-100 text-yellow-800';
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

    const getStatusColor = (status: string) => {
        switch (status) {
            case 'active':
            case 'completed':
                return 'bg-green-100 text-green-800';
            case 'pending':
            case 'draft':
                return 'bg-yellow-100 text-yellow-800';
            case 'cancelled':
            case 'expired':
                return 'bg-red-100 text-red-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    };

    if (advice.length === 0) {
        return (
            <Card>
                <CardContent className="text-center py-12">
                    <Stethoscope className="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                    <h3 className="text-lg font-semibold mb-2">No medical advice found</h3>
                    <p className="text-muted-foreground">
                        No medical advice matches your current filters.
                    </p>
                </CardContent>
            </Card>
        );
    }

    return (
        <Card>
            <CardContent className="p-0">
                <div className="space-y-0">
                    {advice.map((adviceItem) => (
                        <div key={adviceItem.id} className="border-b p-4 hover:bg-muted/50 transition-colors last:border-b-0">
                            <div className="flex items-center justify-between">
                                <div className="space-y-2 flex-1">
                                    <div className="flex items-center gap-3">
                                        <div className="flex items-center gap-1">
                                            {getCategoryIcon(adviceItem.category)}
                                            <h3 className="font-semibold">{adviceItem.title}</h3>
                                        </div>
                                        <Badge className={getCategoryColor(adviceItem.category)}>
                                            {adviceItem.category.replace('_', ' ')}
                                        </Badge>
                                        <Badge variant="outline" className={getPriorityColor(adviceItem.priority)}>
                                            {adviceItem.priority}
                                        </Badge>
                                        <Badge variant="outline" className={getStatusColor(adviceItem.status)}>
                                            {adviceItem.status}
                                        </Badge>
                                    </div>
                                    <div className="flex items-center gap-4 text-sm text-muted-foreground">
                                        <div className="flex items-center gap-1">
                                            <User className="h-4 w-4" />
                                            {adviceItem.patient_name}
                                        </div>
                                        <div className="flex items-center gap-1">
                                            <Calendar className="h-4 w-4" />
                                            {new Date(adviceItem.created_at).toLocaleDateString()}
                                        </div>
                                        {adviceItem.follow_up_date && (
                                            <div className="flex items-center gap-1">
                                                <Clock className="h-4 w-4" />
                                                Follow-up: {new Date(adviceItem.follow_up_date).toLocaleDateString()}
                                            </div>
                                        )}
                                    </div>
                                    <p className="text-sm text-muted-foreground line-clamp-2">
                                        {adviceItem.content}
                                    </p>
                                    {adviceItem.tags.length > 0 && (
                                        <div className="flex flex-wrap gap-1">
                                            {adviceItem.tags.map((tag, index) => (
                                                <Badge key={index} variant="secondary" className="text-xs">
                                                    {tag}
                                                </Badge>
                                            ))}
                                        </div>
                                    )}
                                </div>
                                <div className="flex items-center gap-2">
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        onClick={() => onView(adviceItem)}
                                    >
                                        <Eye className="h-4 w-4" />
                                    </Button>
                                    <Select
                                        value={adviceItem.status}
                                        onValueChange={(value) => onUpdateStatus(adviceItem.id, value)}
                                    >
                                        <SelectTrigger className="w-32">
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="draft">Draft</SelectItem>
                                            <SelectItem value="active">Active</SelectItem>
                                            <SelectItem value="pending">Pending</SelectItem>
                                            <SelectItem value="completed">Completed</SelectItem>
                                            <SelectItem value="cancelled">Cancelled</SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        onClick={() => onDelete(adviceItem.id)}
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

// Create Advice Form Component
function CreateAdviceForm({ patients, onSuccess }: {
    patients: Patient[];
    onSuccess: () => void;
}) {
    const [formData, setFormData] = useState({
        patient_id: '',
        category: 'general',
        title: '',
        content: '',
        priority: 'normal',
        status: 'active',
        tags: [] as string[],
        follow_up_date: '',
        doctor_notes: ''
    });

    const [newTag, setNewTag] = useState('');

    const addTag = () => {
        if (newTag.trim() && !formData.tags.includes(newTag.trim())) {
            setFormData({
                ...formData,
                tags: [...formData.tags, newTag.trim()]
            });
            setNewTag('');
        }
    };

    const removeTag = (tagToRemove: string) => {
        setFormData({
            ...formData,
            tags: formData.tags.filter(tag => tag !== tagToRemove)
        });
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        // Handle form submission via API
        console.log('Create advice:', formData);
        onSuccess();
    };

    return (
        <form onSubmit={handleSubmit} className="space-y-6">
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
                    <Label htmlFor="category">Category *</Label>
                    <Select value={formData.category} onValueChange={(value) => setFormData({...formData, category: value})}>
                        <SelectTrigger>
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="general">General</SelectItem>
                            <SelectItem value="cardiology">Cardiology</SelectItem>
                            <SelectItem value="neurology">Neurology</SelectItem>
                            <SelectItem value="emergency">Emergency</SelectItem>
                            <SelectItem value="preventive">Preventive</SelectItem>
                            <SelectItem value="follow_up">Follow-up</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </div>

            <div className="space-y-2">
                <Label htmlFor="title">Title *</Label>
                <Input
                    id="title"
                    value={formData.title}
                    onChange={(e) => setFormData({...formData, title: e.target.value})}
                    placeholder="Brief title for the medical advice"
                    required
                />
            </div>

            <div className="space-y-2">
                <Label htmlFor="content">Medical Advice Content *</Label>
                <Textarea
                    id="content"
                    value={formData.content}
                    onChange={(e) => setFormData({...formData, content: e.target.value})}
                    placeholder="Detailed medical advice, recommendations, and instructions"
                    rows={8}
                    required
                />
            </div>

            <div className="grid gap-4 md:grid-cols-2">
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
                    <Label htmlFor="status">Status</Label>
                    <Select value={formData.status} onValueChange={(value) => setFormData({...formData, status: value})}>
                        <SelectTrigger>
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="draft">Draft</SelectItem>
                            <SelectItem value="active">Active</SelectItem>
                            <SelectItem value="pending">Pending</SelectItem>
                            <SelectItem value="completed">Completed</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </div>

            <div className="space-y-2">
                <Label htmlFor="follow_up_date">Follow-up Date</Label>
                <Input
                    id="follow_up_date"
                    type="date"
                    value={formData.follow_up_date}
                    onChange={(e) => setFormData({...formData, follow_up_date: e.target.value})}
                />
            </div>

            <div className="space-y-2">
                <Label>Tags</Label>
                <div className="flex gap-2">
                    <Input
                        value={newTag}
                        onChange={(e) => setNewTag(e.target.value)}
                        placeholder="Add a tag"
                        onKeyPress={(e) => e.key === 'Enter' && (e.preventDefault(), addTag())}
                    />
                    <Button type="button" variant="outline" onClick={addTag}>
                        Add
                    </Button>
                </div>
                {formData.tags.length > 0 && (
                    <div className="flex flex-wrap gap-2 mt-2">
                        {formData.tags.map((tag, index) => (
                            <Badge key={index} variant="secondary" className="flex items-center gap-1">
                                {tag}
                                <button
                                    type="button"
                                    onClick={() => removeTag(tag)}
                                    className="ml-1 hover:text-red-500"
                                >
                                    <XCircle className="h-3 w-3" />
                                </button>
                            </Badge>
                        ))}
                    </div>
                )}
            </div>

            <div className="space-y-2">
                <Label htmlFor="doctor_notes">Doctor Notes</Label>
                <Textarea
                    id="doctor_notes"
                    value={formData.doctor_notes}
                    onChange={(e) => setFormData({...formData, doctor_notes: e.target.value})}
                    placeholder="Additional notes for internal use"
                    rows={3}
                />
            </div>

            <div className="flex justify-end gap-2">
                <Button type="button" variant="outline" onClick={onSuccess}>
                    Cancel
                </Button>
                <Button type="submit">
                    Create Medical Advice
                </Button>
            </div>
        </form>
    );
}

// Advice Details View Component
function AdviceDetailsView({ advice }: { advice: MedicalAdvice }) {
    const getCategoryIcon = (category: string) => {
        switch (category) {
            case 'general':
                return <Stethoscope className="h-5 w-5" />;
            case 'cardiology':
                return <Heart className="h-5 w-5" />;
            case 'neurology':
                return <Brain className="h-5 w-5" />;
            case 'emergency':
                return <AlertTriangle className="h-5 w-5" />;
            case 'preventive':
                return <Shield className="h-5 w-5" />;
            case 'follow_up':
                return <Activity className="h-5 w-5" />;
            default:
                return <FileText className="h-5 w-5" />;
        }
    };

    const getCategoryColor = (category: string) => {
        switch (category) {
            case 'general':
                return 'bg-blue-100 text-blue-800';
            case 'cardiology':
                return 'bg-red-100 text-red-800';
            case 'neurology':
                return 'bg-purple-100 text-purple-800';
            case 'emergency':
                return 'bg-orange-100 text-orange-800';
            case 'preventive':
                return 'bg-green-100 text-green-800';
            case 'follow_up':
                return 'bg-yellow-100 text-yellow-800';
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

    const getStatusColor = (status: string) => {
        switch (status) {
            case 'active':
            case 'completed':
                return 'bg-green-100 text-green-800';
            case 'pending':
            case 'draft':
                return 'bg-yellow-100 text-yellow-800';
            case 'cancelled':
            case 'expired':
                return 'bg-red-100 text-red-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    };

    return (
        <div className="space-y-6">
            {/* Header */}
            <div className="flex items-center justify-between">
                <div className="flex items-center gap-3">
                    {getCategoryIcon(advice.category)}
                    <div>
                        <h2 className="text-2xl font-bold">{advice.title}</h2>
                        <p className="text-muted-foreground">Medical Advice Details</p>
                    </div>
                </div>
                <div className="flex gap-2">
                    <Badge className={getCategoryColor(advice.category)}>
                        {advice.category.replace('_', ' ')}
                    </Badge>
                    <Badge variant="outline" className={getPriorityColor(advice.priority)}>
                        {advice.priority}
                    </Badge>
                    <Badge variant="outline" className={getStatusColor(advice.status)}>
                        {advice.status}
                    </Badge>
                </div>
            </div>

            {/* Patient Info */}
            <Card>
                <CardHeader>
                    <CardTitle className="flex items-center gap-2">
                        <User className="h-5 w-5" />
                        Patient Information
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div className="grid gap-4 md:grid-cols-2">
                        <div>
                            <Label>Patient Name</Label>
                            <p className="text-sm font-medium">{advice.patient_name}</p>
                        </div>
                        <div>
                            <Label>Created Date</Label>
                            <p className="text-sm">{new Date(advice.created_at).toLocaleDateString()}</p>
                        </div>
                        <div>
                            <Label>Last Updated</Label>
                            <p className="text-sm">{new Date(advice.updated_at).toLocaleDateString()}</p>
                        </div>
                        {advice.follow_up_date && (
                            <div>
                                <Label>Follow-up Date</Label>
                                <p className="text-sm">{new Date(advice.follow_up_date).toLocaleDateString()}</p>
                            </div>
                        )}
                    </div>
                </CardContent>
            </Card>

            {/* Medical Advice Content */}
            <Card>
                <CardHeader>
                    <CardTitle>Medical Advice</CardTitle>
                </CardHeader>
                <CardContent>
                    <div className="prose max-w-none">
                        <p className="whitespace-pre-wrap">{advice.content}</p>
                    </div>
                </CardContent>
            </Card>

            {/* Tags */}
            {advice.tags.length > 0 && (
                <Card>
                    <CardHeader>
                        <CardTitle>Tags</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="flex flex-wrap gap-2">
                            {advice.tags.map((tag, index) => (
                                <Badge key={index} variant="secondary">
                                    {tag}
                                </Badge>
                            ))}
                        </div>
                    </CardContent>
                </Card>
            )}

            {/* Doctor Notes */}
            {advice.doctor_notes && (
                <Card>
                    <CardHeader>
                        <CardTitle>Doctor Notes</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p className="text-sm text-muted-foreground whitespace-pre-wrap">
                            {advice.doctor_notes}
                        </p>
                    </CardContent>
                </Card>
            )}

            {/* Attachments */}
            {advice.attachments.length > 0 && (
                <Card>
                    <CardHeader>
                        <CardTitle>Attachments</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-2">
                            {advice.attachments.map((attachment, index) => (
                                <div key={index} className="flex items-center gap-2 p-2 border rounded">
                                    <FileText className="h-4 w-4" />
                                    <span className="text-sm">{attachment}</span>
                                    <Button variant="outline" size="sm" className="ml-auto">
                                        <Download className="h-4 w-4" />
                                    </Button>
                                </div>
                            ))}
                        </div>
                    </CardContent>
                </Card>
            )}
        </div>
    );
}

