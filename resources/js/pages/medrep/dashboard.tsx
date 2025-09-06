import { useState } from 'react';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { 
    Package, 
    Calendar, 
    Users, 
    FileText, 
    Plus,
    Edit,
    Eye,
    Search,
    Filter,
    Clock,
    CheckCircle,
    AlertCircle,
    DollarSign,
    TrendingUp
} from 'lucide-react';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Medical Representative Dashboard',
        href: '/medrep/dashboard',
    },
];

interface Product {
    id: number;
    name: string;
    dosage: string;
    indications: string[];
    pricing: number;
    marketing_material: string;
    status: string;
    created_at: string;
}

interface Doctor {
    id: number;
    name: string;
    specialization: string;
    clinic_name: string;
    contact: {
        phone?: string;
        email?: string;
    };
}

interface Meeting {
    id: number;
    doctor_id: number;
    doctor_name: string;
    date: string;
    time: string;
    purpose: string;
    status: string;
    notes?: string;
    samples_provided?: string[];
    commitments?: string[];
    created_at: string;
}

interface Interaction {
    id: number;
    doctor_id: number;
    doctor_name: string;
    meeting_id: number;
    notes: string;
    samples_provided: string[];
    commitments: string[];
    follow_up_date?: string;
    created_at: string;
}

interface MedrepDashboardProps {
    stats: {
        totalProducts: number;
        totalDoctors: number;
        scheduledMeetings: number;
        completedInteractions: number;
    };
    products: Product[];
    doctors: Doctor[];
    upcomingMeetings: Meeting[];
    recentInteractions: Interaction[];
}

export default function MedrepDashboard({ 
    stats, 
    products, 
    doctors, 
    upcomingMeetings, 
    recentInteractions 
}: MedrepDashboardProps) {
    const [isProductDialogOpen, setIsProductDialogOpen] = useState(false);
    const [isMeetingDialogOpen, setIsMeetingDialogOpen] = useState(false);
    const [isInteractionDialogOpen, setIsInteractionDialogOpen] = useState(false);
    const [searchQuery, setSearchQuery] = useState('');
    const [selectedDoctor, setSelectedDoctor] = useState<Doctor | null>(null);

    const getStatusColor = (status: string) => {
        switch (status.toLowerCase()) {
            case 'active':
            case 'completed':
            case 'confirmed':
                return 'bg-green-100 text-green-800';
            case 'pending':
            case 'scheduled':
                return 'bg-yellow-100 text-yellow-800';
            case 'cancelled':
            case 'inactive':
                return 'bg-red-100 text-red-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Medical Representative Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Medical Representative Dashboard</h1>
                        <p className="text-muted-foreground">
                            Manage products, schedule meetings, and track doctor interactions
                        </p>
                    </div>
                </div>

                {/* Stats Cards */}
                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total Products</CardTitle>
                            <Package className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.totalProducts}</div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Doctors</CardTitle>
                            <Users className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.totalDoctors}</div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Scheduled Meetings</CardTitle>
                            <Calendar className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.scheduledMeetings}</div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Completed Interactions</CardTitle>
                            <CheckCircle className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.completedInteractions}</div>
                        </CardContent>
                    </Card>
                </div>

                {/* Main Content Tabs */}
                <Tabs defaultValue="products" className="space-y-4">
                    <TabsList>
                        <TabsTrigger value="products">Product Catalog</TabsTrigger>
                        <TabsTrigger value="meetings">Doctor Meetings</TabsTrigger>
                        <TabsTrigger value="interactions">Interaction History</TabsTrigger>
                        <TabsTrigger value="doctors">Doctor Directory</TabsTrigger>
                    </TabsList>

                    {/* Product Catalog Tab */}
                    <TabsContent value="products" className="space-y-4">
                        <Card>
                            <CardHeader>
                                <div className="flex items-center justify-between">
                                    <div>
                                        <CardTitle>Product Catalog</CardTitle>
                                        <CardDescription>Manage your product portfolio</CardDescription>
                                    </div>
                                    <Dialog open={isProductDialogOpen} onOpenChange={setIsProductDialogOpen}>
                                        <DialogTrigger asChild>
                                            <Button>
                                                <Plus className="h-4 w-4 mr-2" />
                                                Add Product
                                            </Button>
                                        </DialogTrigger>
                                        <DialogContent className="max-w-2xl">
                                            <DialogHeader>
                                                <DialogTitle>Add New Product</DialogTitle>
                                                <DialogDescription>
                                                    Add a new product to your catalog
                                                </DialogDescription>
                                            </DialogHeader>
                                            <ProductForm onSuccess={() => setIsProductDialogOpen(false)} />
                                        </DialogContent>
                                    </Dialog>
                                </div>
                            </CardHeader>
                            <CardContent>
                                <div className="flex gap-4 mb-4">
                                    <div className="flex-1">
                                        <Input
                                            placeholder="Search products..."
                                            value={searchQuery}
                                            onChange={(e) => setSearchQuery(e.target.value)}
                                        />
                                    </div>
                                </div>

                                {products.length > 0 ? (
                                    <div className="space-y-4">
                                        {products
                                            .filter(product => 
                                                product.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
                                                product.indications.some(ind => ind.toLowerCase().includes(searchQuery.toLowerCase()))
                                            )
                                            .map((product) => (
                                            <Card key={product.id} className="p-4">
                                                <div className="flex items-center justify-between">
                                                    <div className="space-y-2">
                                                        <div className="flex items-center gap-3">
                                                            <h4 className="font-semibold text-lg">{product.name}</h4>
                                                            <Badge className={getStatusColor(product.status)}>
                                                                {product.status}
                                                            </Badge>
                                                        </div>
                                                        <div className="grid grid-cols-2 gap-4 text-sm text-muted-foreground">
                                                            <div>
                                                                <p><strong>Dosage:</strong> {product.dosage}</p>
                                                                <p><strong>Pricing:</strong> ${product.pricing}</p>
                                                            </div>
                                                            <div>
                                                                <p><strong>Indications:</strong></p>
                                                                <div className="flex flex-wrap gap-1 mt-1">
                                                                    {product.indications.slice(0, 3).map((indication, index) => (
                                                                        <Badge key={index} variant="outline" className="text-xs">
                                                                            {indication}
                                                                        </Badge>
                                                                    ))}
                                                                    {product.indications.length > 3 && (
                                                                        <Badge variant="outline" className="text-xs">
                                                                            +{product.indications.length - 3} more
                                                                        </Badge>
                                                                    )}
                                                                </div>
                                                            </div>
                                                        </div>
                                                        {product.marketing_material && (
                                                            <p className="text-sm text-muted-foreground">
                                                                <strong>Marketing Material:</strong> {product.marketing_material}
                                                            </p>
                                                        )}
                                                    </div>
                                                    <div className="flex gap-2">
                                                        <Button size="sm" variant="outline">
                                                            <Eye className="h-4 w-4 mr-2" />
                                                            View
                                                        </Button>
                                                        <Button size="sm" variant="outline">
                                                            <Edit className="h-4 w-4 mr-2" />
                                                            Edit
                                                        </Button>
                                                    </div>
                                                </div>
                                            </Card>
                                        ))}
                                    </div>
                                ) : (
                                    <div className="text-center py-8">
                                        <Package className="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                                        <h3 className="text-lg font-semibold mb-2">No products in catalog</h3>
                                        <p className="text-muted-foreground mb-4">
                                            Add your first product to get started
                                        </p>
                                        <Button onClick={() => setIsProductDialogOpen(true)}>
                                            <Plus className="h-4 w-4 mr-2" />
                                            Add Product
                                        </Button>
                                    </div>
                                )}
                            </CardContent>
                        </Card>
                    </TabsContent>

                    {/* Doctor Meetings Tab */}
                    <TabsContent value="meetings" className="space-y-4">
                        <Card>
                            <CardHeader>
                                <div className="flex items-center justify-between">
                                    <div>
                                        <CardTitle>Doctor Meetings</CardTitle>
                                        <CardDescription>Schedule and manage doctor meetings</CardDescription>
                                    </div>
                                    <Dialog open={isMeetingDialogOpen} onOpenChange={setIsMeetingDialogOpen}>
                                        <DialogTrigger asChild>
                                            <Button>
                                                <Calendar className="h-4 w-4 mr-2" />
                                                Schedule Meeting
                                            </Button>
                                        </DialogTrigger>
                                        <DialogContent className="max-w-2xl">
                                            <DialogHeader>
                                                <DialogTitle>Schedule Doctor Meeting</DialogTitle>
                                                <DialogDescription>
                                                    Schedule a meeting with a doctor
                                                </DialogDescription>
                                            </DialogHeader>
                                            <MeetingForm 
                                                doctors={doctors}
                                                onSuccess={() => setIsMeetingDialogOpen(false)}
                                            />
                                        </DialogContent>
                                    </Dialog>
                                </div>
                            </CardHeader>
                            <CardContent>
                                {upcomingMeetings.length > 0 ? (
                                    <div className="space-y-4">
                                        {upcomingMeetings.map((meeting) => (
                                            <Card key={meeting.id} className="p-4">
                                                <div className="flex items-center justify-between">
                                                    <div className="space-y-2">
                                                        <div className="flex items-center gap-3">
                                                            <h4 className="font-semibold text-lg">{meeting.doctor_name}</h4>
                                                            <Badge className={getStatusColor(meeting.status)}>
                                                                {meeting.status}
                                                            </Badge>
                                                        </div>
                                                        <div className="grid grid-cols-2 gap-4 text-sm text-muted-foreground">
                                                            <div>
                                                                <p><strong>Date:</strong> {new Date(meeting.date).toLocaleDateString()}</p>
                                                                <p><strong>Time:</strong> {meeting.time}</p>
                                                            </div>
                                                            <div>
                                                                <p><strong>Purpose:</strong> {meeting.purpose}</p>
                                                                <p><strong>Scheduled:</strong> {new Date(meeting.created_at).toLocaleDateString()}</p>
                                                            </div>
                                                        </div>
                                                        {meeting.notes && (
                                                            <p className="text-sm text-muted-foreground">
                                                                <strong>Notes:</strong> {meeting.notes}
                                                            </p>
                                                        )}
                                                    </div>
                                                    <div className="flex gap-2">
                                                        <Button size="sm" variant="outline">
                                                            <Eye className="h-4 w-4 mr-2" />
                                                            View
                                                        </Button>
                                                        <Button size="sm" variant="outline">
                                                            <Edit className="h-4 w-4 mr-2" />
                                                            Edit
                                                        </Button>
                                                    </div>
                                                </div>
                                            </Card>
                                        ))}
                                    </div>
                                ) : (
                                    <div className="text-center py-8">
                                        <Calendar className="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                                        <h3 className="text-lg font-semibold mb-2">No meetings scheduled</h3>
                                        <p className="text-muted-foreground mb-4">
                                            Schedule your first meeting with a doctor
                                        </p>
                                        <Button onClick={() => setIsMeetingDialogOpen(true)}>
                                            <Calendar className="h-4 w-4 mr-2" />
                                            Schedule Meeting
                                        </Button>
                                    </div>
                                )}
                            </CardContent>
                        </Card>
                    </TabsContent>

                    {/* Interaction History Tab */}
                    <TabsContent value="interactions" className="space-y-4">
                        <Card>
                            <CardHeader>
                                <div className="flex items-center justify-between">
                                    <div>
                                        <CardTitle>Interaction History</CardTitle>
                                        <CardDescription>Track your interactions with doctors</CardDescription>
                                    </div>
                                    <Dialog open={isInteractionDialogOpen} onOpenChange={setIsInteractionDialogOpen}>
                                        <DialogTrigger asChild>
                                            <Button>
                                                <FileText className="h-4 w-4 mr-2" />
                                                Log Interaction
                                            </Button>
                                        </DialogTrigger>
                                        <DialogContent className="max-w-2xl">
                                            <DialogHeader>
                                                <DialogTitle>Log Doctor Interaction</DialogTitle>
                                                <DialogDescription>
                                                    Record details of your interaction with a doctor
                                                </DialogDescription>
                                            </DialogHeader>
                                            <InteractionForm 
                                                doctors={doctors}
                                                onSuccess={() => setIsInteractionDialogOpen(false)}
                                            />
                                        </DialogContent>
                                    </Dialog>
                                </div>
                            </CardHeader>
                            <CardContent>
                                {recentInteractions.length > 0 ? (
                                    <div className="space-y-4">
                                        {recentInteractions.map((interaction) => (
                                            <Card key={interaction.id} className="p-4">
                                                <div className="space-y-3">
                                                    <div className="flex items-center justify-between">
                                                        <h4 className="font-semibold">{interaction.doctor_name}</h4>
                                                        <span className="text-sm text-muted-foreground">
                                                            {new Date(interaction.created_at).toLocaleDateString()}
                                                        </span>
                                                    </div>
                                                    <p className="text-sm">{interaction.notes}</p>
                                                    
                                                    {interaction.samples_provided.length > 0 && (
                                                        <div>
                                                            <p className="text-sm font-medium">Samples Provided:</p>
                                                            <div className="flex flex-wrap gap-1 mt-1">
                                                                {interaction.samples_provided.map((sample, index) => (
                                                                    <Badge key={index} variant="outline" className="text-xs">
                                                                        {sample}
                                                                    </Badge>
                                                                ))}
                                                            </div>
                                                        </div>
                                                    )}

                                                    {interaction.commitments.length > 0 && (
                                                        <div>
                                                            <p className="text-sm font-medium">Commitments:</p>
                                                            <div className="flex flex-wrap gap-1 mt-1">
                                                                {interaction.commitments.map((commitment, index) => (
                                                                    <Badge key={index} variant="secondary" className="text-xs">
                                                                        {commitment}
                                                                    </Badge>
                                                                ))}
                                                            </div>
                                                        </div>
                                                    )}

                                                    {interaction.follow_up_date && (
                                                        <div className="flex items-center gap-2 text-sm text-muted-foreground">
                                                            <Clock className="h-4 w-4" />
                                                            <span>Follow-up: {new Date(interaction.follow_up_date).toLocaleDateString()}</span>
                                                        </div>
                                                    )}

                                                    <div className="flex gap-2">
                                                        <Button size="sm" variant="outline">
                                                            <Eye className="h-4 w-4 mr-2" />
                                                            View Details
                                                        </Button>
                                                        <Button size="sm" variant="outline">
                                                            <Edit className="h-4 w-4 mr-2" />
                                                            Edit
                                                        </Button>
                                                    </div>
                                                </div>
                                            </Card>
                                        ))}
                                    </div>
                                ) : (
                                    <div className="text-center py-8">
                                        <FileText className="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                                        <h3 className="text-lg font-semibold mb-2">No interactions recorded</h3>
                                        <p className="text-muted-foreground mb-4">
                                            Start logging your doctor interactions
                                        </p>
                                        <Button onClick={() => setIsInteractionDialogOpen(true)}>
                                            <FileText className="h-4 w-4 mr-2" />
                                            Log Interaction
                                        </Button>
                                    </div>
                                )}
                            </CardContent>
                        </Card>
                    </TabsContent>

                    {/* Doctor Directory Tab */}
                    <TabsContent value="doctors" className="space-y-4">
                        <Card>
                            <CardHeader>
                                <CardTitle>Doctor Directory</CardTitle>
                                <CardDescription>Browse and manage your doctor contacts</CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div className="flex gap-4 mb-4">
                                    <div className="flex-1">
                                        <Input
                                            placeholder="Search doctors..."
                                            value={searchQuery}
                                            onChange={(e) => setSearchQuery(e.target.value)}
                                        />
                                    </div>
                                </div>

                                {doctors.length > 0 ? (
                                    <div className="space-y-4">
                                        {doctors
                                            .filter(doctor => 
                                                doctor.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
                                                doctor.specialization.toLowerCase().includes(searchQuery.toLowerCase()) ||
                                                doctor.clinic_name.toLowerCase().includes(searchQuery.toLowerCase())
                                            )
                                            .map((doctor) => (
                                            <Card key={doctor.id} className="p-4">
                                                <div className="flex items-center justify-between">
                                                    <div className="space-y-2">
                                                        <div className="flex items-center gap-3">
                                                            <h4 className="font-semibold text-lg">{doctor.name}</h4>
                                                            <Badge variant="outline">{doctor.specialization}</Badge>
                                                        </div>
                                                        <div className="grid grid-cols-2 gap-4 text-sm text-muted-foreground">
                                                            <div>
                                                                <p><strong>Clinic:</strong> {doctor.clinic_name}</p>
                                                                {doctor.contact.phone && (
                                                                    <p><strong>Phone:</strong> {doctor.contact.phone}</p>
                                                                )}
                                                            </div>
                                                            <div>
                                                                {doctor.contact.email && (
                                                                    <p><strong>Email:</strong> {doctor.contact.email}</p>
                                                                )}
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div className="flex gap-2">
                                                        <Button size="sm" variant="outline">
                                                            <Eye className="h-4 w-4 mr-2" />
                                                            View
                                                        </Button>
                                                        <Button size="sm" onClick={() => setSelectedDoctor(doctor)}>
                                                            <Calendar className="h-4 w-4 mr-2" />
                                                            Schedule Meeting
                                                        </Button>
                                                    </div>
                                                </div>
                                            </Card>
                                        ))}
                                    </div>
                                ) : (
                                    <div className="text-center py-8">
                                        <Users className="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                                        <h3 className="text-lg font-semibold mb-2">No doctors in directory</h3>
                                        <p className="text-muted-foreground">
                                            Doctors will appear here as you add them to your network
                                        </p>
                                    </div>
                                )}
                            </CardContent>
                        </Card>
                    </TabsContent>
                </Tabs>
            </div>
        </AppLayout>
    );
}

// Product Form Component
function ProductForm({ onSuccess }: { onSuccess: () => void }) {
    const [formData, setFormData] = useState({
        name: '',
        dosage: '',
        indications: '',
        pricing: '',
        marketing_material: '',
        status: 'active'
    });
    const [isSubmitting, setIsSubmitting] = useState(false);

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setIsSubmitting(true);

        try {
            const response = await fetch('/api/v1/medrep/products', {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('token')}`,
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    ...formData,
                    indications: formData.indications.split(',').map(ind => ind.trim()).filter(ind => ind),
                    pricing: parseFloat(formData.pricing)
                }),
            });

            if (response.ok) {
                onSuccess();
            }
        } catch (error) {
            console.error('Error creating product:', error);
        } finally {
            setIsSubmitting(false);
        }
    };

    return (
        <form onSubmit={handleSubmit} className="space-y-4">
            <div className="grid grid-cols-2 gap-4">
                <div>
                    <Label htmlFor="name">Product Name *</Label>
                    <Input
                        id="name"
                        value={formData.name}
                        onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                        required
                    />
                </div>
                <div>
                    <Label htmlFor="dosage">Dosage *</Label>
                    <Input
                        id="dosage"
                        value={formData.dosage}
                        onChange={(e) => setFormData({ ...formData, dosage: e.target.value })}
                        required
                    />
                </div>
            </div>

            <div>
                <Label htmlFor="indications">Indications *</Label>
                <Textarea
                    id="indications"
                    value={formData.indications}
                    onChange={(e) => setFormData({ ...formData, indications: e.target.value })}
                    placeholder="Enter indications separated by commas..."
                    required
                />
            </div>

            <div className="grid grid-cols-2 gap-4">
                <div>
                    <Label htmlFor="pricing">Pricing ($) *</Label>
                    <Input
                        id="pricing"
                        type="number"
                        step="0.01"
                        value={formData.pricing}
                        onChange={(e) => setFormData({ ...formData, pricing: e.target.value })}
                        required
                    />
                </div>
                <div>
                    <Label htmlFor="status">Status</Label>
                    <Select value={formData.status} onValueChange={(value) => setFormData({ ...formData, status: value })}>
                        <SelectTrigger>
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="active">Active</SelectItem>
                            <SelectItem value="inactive">Inactive</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </div>

            <div>
                <Label htmlFor="marketing_material">Marketing Material</Label>
                <Textarea
                    id="marketing_material"
                    value={formData.marketing_material}
                    onChange={(e) => setFormData({ ...formData, marketing_material: e.target.value })}
                    placeholder="Describe marketing materials available..."
                />
            </div>

            <div className="flex justify-end gap-2">
                <Button type="button" variant="outline" onClick={onSuccess}>
                    Cancel
                </Button>
                <Button type="submit" disabled={isSubmitting}>
                    {isSubmitting ? 'Creating...' : 'Create Product'}
                </Button>
            </div>
        </form>
    );
}

// Meeting Form Component
function MeetingForm({ 
    doctors, 
    onSuccess 
}: { 
    doctors: Doctor[];
    onSuccess: () => void;
}) {
    const [formData, setFormData] = useState({
        doctor_id: '',
        date: '',
        time: '',
        purpose: '',
        notes: ''
    });
    const [isSubmitting, setIsSubmitting] = useState(false);

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setIsSubmitting(true);

        try {
            const response = await fetch('/api/v1/medrep/meetings', {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('token')}`,
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    ...formData,
                    doctor_id: parseInt(formData.doctor_id)
                }),
            });

            if (response.ok) {
                onSuccess();
            }
        } catch (error) {
            console.error('Error scheduling meeting:', error);
        } finally {
            setIsSubmitting(false);
        }
    };

    return (
        <form onSubmit={handleSubmit} className="space-y-4">
            <div>
                <Label htmlFor="doctor_id">Select Doctor *</Label>
                <Select value={formData.doctor_id} onValueChange={(value) => setFormData({ ...formData, doctor_id: value })}>
                    <SelectTrigger>
                        <SelectValue placeholder="Choose a doctor" />
                    </SelectTrigger>
                    <SelectContent>
                        {doctors.map((doctor) => (
                            <SelectItem key={doctor.id} value={doctor.id.toString()}>
                                {doctor.name} - {doctor.specialization}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
            </div>

            <div className="grid grid-cols-2 gap-4">
                <div>
                    <Label htmlFor="date">Date *</Label>
                    <Input
                        id="date"
                        type="date"
                        value={formData.date}
                        onChange={(e) => setFormData({ ...formData, date: e.target.value })}
                        min={new Date().toISOString().split('T')[0]}
                        required
                    />
                </div>
                <div>
                    <Label htmlFor="time">Time *</Label>
                    <Input
                        id="time"
                        type="time"
                        value={formData.time}
                        onChange={(e) => setFormData({ ...formData, time: e.target.value })}
                        required
                    />
                </div>
            </div>

            <div>
                <Label htmlFor="purpose">Purpose *</Label>
                <Input
                    id="purpose"
                    value={formData.purpose}
                    onChange={(e) => setFormData({ ...formData, purpose: e.target.value })}
                    placeholder="e.g., Product presentation, Follow-up, Sample delivery..."
                    required
                />
            </div>

            <div>
                <Label htmlFor="notes">Notes</Label>
                <Textarea
                    id="notes"
                    value={formData.notes}
                    onChange={(e) => setFormData({ ...formData, notes: e.target.value })}
                    placeholder="Additional notes about the meeting..."
                />
            </div>

            <div className="flex justify-end gap-2">
                <Button type="button" variant="outline" onClick={onSuccess}>
                    Cancel
                </Button>
                <Button type="submit" disabled={isSubmitting}>
                    {isSubmitting ? 'Scheduling...' : 'Schedule Meeting'}
                </Button>
            </div>
        </form>
    );
}

// Interaction Form Component
function InteractionForm({ 
    doctors, 
    onSuccess 
}: { 
    doctors: Doctor[];
    onSuccess: () => void;
}) {
    const [formData, setFormData] = useState({
        doctor_id: '',
        notes: '',
        samples_provided: '',
        commitments: '',
        follow_up_date: ''
    });
    const [isSubmitting, setIsSubmitting] = useState(false);

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setIsSubmitting(true);

        try {
            const response = await fetch('/api/v1/medrep/interactions', {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('token')}`,
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    ...formData,
                    doctor_id: parseInt(formData.doctor_id),
                    samples_provided: formData.samples_provided.split(',').map(s => s.trim()).filter(s => s),
                    commitments: formData.commitments.split(',').map(c => c.trim()).filter(c => c)
                }),
            });

            if (response.ok) {
                onSuccess();
            }
        } catch (error) {
            console.error('Error logging interaction:', error);
        } finally {
            setIsSubmitting(false);
        }
    };

    return (
        <form onSubmit={handleSubmit} className="space-y-4">
            <div>
                <Label htmlFor="doctor_id">Select Doctor *</Label>
                <Select value={formData.doctor_id} onValueChange={(value) => setFormData({ ...formData, doctor_id: value })}>
                    <SelectTrigger>
                        <SelectValue placeholder="Choose a doctor" />
                    </SelectTrigger>
                    <SelectContent>
                        {doctors.map((doctor) => (
                            <SelectItem key={doctor.id} value={doctor.id.toString()}>
                                {doctor.name} - {doctor.specialization}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
            </div>

            <div>
                <Label htmlFor="notes">Meeting Notes *</Label>
                <Textarea
                    id="notes"
                    value={formData.notes}
                    onChange={(e) => setFormData({ ...formData, notes: e.target.value })}
                    placeholder="Describe what was discussed in the meeting..."
                    required
                />
            </div>

            <div>
                <Label htmlFor="samples_provided">Samples Provided</Label>
                <Textarea
                    id="samples_provided"
                    value={formData.samples_provided}
                    onChange={(e) => setFormData({ ...formData, samples_provided: e.target.value })}
                    placeholder="List samples provided, separated by commas..."
                />
            </div>

            <div>
                <Label htmlFor="commitments">Commitments Made</Label>
                <Textarea
                    id="commitments"
                    value={formData.commitments}
                    onChange={(e) => setFormData({ ...formData, commitments: e.target.value })}
                    placeholder="List any commitments made, separated by commas..."
                />
            </div>

            <div>
                <Label htmlFor="follow_up_date">Follow-up Date</Label>
                <Input
                    id="follow_up_date"
                    type="date"
                    value={formData.follow_up_date}
                    onChange={(e) => setFormData({ ...formData, follow_up_date: e.target.value })}
                />
            </div>

            <div className="flex justify-end gap-2">
                <Button type="button" variant="outline" onClick={onSuccess}>
                    Cancel
                </Button>
                <Button type="submit" disabled={isSubmitting}>
                    {isSubmitting ? 'Logging...' : 'Log Interaction'}
                </Button>
            </div>
        </form>
    );
}
