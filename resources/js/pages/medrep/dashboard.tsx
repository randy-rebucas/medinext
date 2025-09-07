import { useState } from 'react';
import AppLayout from '@/layouts/app-layout';
import { medrepDashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Package,
    Calendar,
    Users,
    FileText,
    Clock,
    CheckCircle,
    AlertCircle,
    TrendingUp,
    Activity,
    ArrowUpRight,
    UserCheck,
    Building2,
    Shield
} from 'lucide-react';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Link } from '@inertiajs/react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Medical Representative Dashboard',
        href: medrepDashboard(),
    },
];


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
    user?: {
        id: number;
        name: string;
        email: string;
        role: string;
        company_id?: number;
        company?: {
            id: number;
            name: string;
        };
    };
    stats: {
        totalProducts: number;
        totalDoctors: number;
        scheduledMeetings: number;
        completedInteractions: number;
    };
    doctors: Doctor[];
    upcomingMeetings: Meeting[];
    recentInteractions: Interaction[];
}

export default function MedrepDashboard({
    user,
    stats,
    doctors,
    upcomingMeetings,
    recentInteractions
}: MedrepDashboardProps) {
    const [isProductDialogOpen, setIsProductDialogOpen] = useState(false);
    const [isMeetingDialogOpen, setIsMeetingDialogOpen] = useState(false);
    const [isInteractionDialogOpen, setIsInteractionDialogOpen] = useState(false);

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
            <Head title="Medical Representative Dashboard - Medinext">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&family=instrument-sans:400,500,600" rel="stylesheet" />
            </Head>
            <div className="min-h-screen bg-gradient-to-br from-slate-50 via-white to-blue-50 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900">
                <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
                    {/* Modern Header */}
                    <div className="relative overflow-hidden rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 p-8 text-white shadow-xl">
                        <div className="absolute inset-0 bg-black/10"></div>
                        <div className="relative flex items-center justify-between">
                            <div>
                                <h1 className="text-3xl font-bold tracking-tight">
                                    Welcome back, {user?.name || 'Medical Representative'}
                                </h1>
                                <p className="mt-2 text-emerald-100">
                                    {user?.company?.name || 'No Company'} • Medical Representative Dashboard
                                </p>
                            </div>
                            <div className="flex items-center gap-3">
                                <Badge variant="secondary" className="flex items-center gap-1 bg-white/20 text-white border-white/30 hover:bg-white/30">
                                    <Shield className="h-3 w-3" />
                                    Medical Rep
                                </Badge>
                                {user?.company && (
                                    <Badge variant="secondary" className="flex items-center gap-1 bg-white/20 text-white border-white/30 hover:bg-white/30">
                                        <Building2 className="h-3 w-3" />
                                        {user.company.name}
                                    </Badge>
                                )}
                            </div>
                        </div>
                        {/* Decorative elements */}
                        <div className="absolute -top-4 -right-4 w-24 h-24 bg-white/10 rounded-full"></div>
                        <div className="absolute -bottom-2 -left-2 w-16 h-16 bg-white/5 rounded-full"></div>
                    </div>

                    {/* Medrep Stats */}
                    <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                        <Card className="relative overflow-hidden border-0 shadow-lg hover:shadow-xl transition-all duration-300 bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20">
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium text-slate-700 dark:text-slate-300">Total Products</CardTitle>
                                <div className="p-2 bg-blue-500 rounded-lg">
                                    <Package className="h-4 w-4 text-white" />
                                </div>
                            </CardHeader>
                            <CardContent>
                                <div className="text-3xl font-bold text-slate-900 dark:text-white">{stats.totalProducts}</div>
                                <div className="flex items-center mt-2">
                                    <TrendingUp className="h-3 w-3 text-blue-500 mr-1" />
                                    <p className="text-xs text-slate-600 dark:text-slate-400">
                                        In your portfolio
                                    </p>
                                </div>
                            </CardContent>
                        </Card>
                        <Card className="relative overflow-hidden border-0 shadow-lg hover:shadow-xl transition-all duration-300 bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20">
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium text-slate-700 dark:text-slate-300">Doctor Network</CardTitle>
                                <div className="p-2 bg-green-500 rounded-lg">
                                    <Users className="h-4 w-4 text-white" />
                                </div>
                            </CardHeader>
                            <CardContent>
                                <div className="text-3xl font-bold text-slate-900 dark:text-white">{stats.totalDoctors}</div>
                                <div className="flex items-center mt-2">
                                    <UserCheck className="h-3 w-3 text-green-500 mr-1" />
                                    <p className="text-xs text-slate-600 dark:text-slate-400">
                                        Active contacts
                                    </p>
                                </div>
                            </CardContent>
                        </Card>
                        <Card className="relative overflow-hidden border-0 shadow-lg hover:shadow-xl transition-all duration-300 bg-gradient-to-br from-orange-50 to-orange-100 dark:from-orange-900/20 dark:to-orange-800/20">
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium text-slate-700 dark:text-slate-300">Scheduled Meetings</CardTitle>
                                <div className="p-2 bg-orange-500 rounded-lg">
                                    <Calendar className="h-4 w-4 text-white" />
                                </div>
                            </CardHeader>
                            <CardContent>
                                <div className="text-3xl font-bold text-slate-900 dark:text-white">{stats.scheduledMeetings}</div>
                                <div className="flex items-center mt-2">
                                    <Clock className="h-3 w-3 text-orange-500 mr-1" />
                                    <p className="text-xs text-slate-600 dark:text-slate-400">
                                        Upcoming meetings
                                    </p>
                                </div>
                            </CardContent>
                        </Card>
                        <Card className="relative overflow-hidden border-0 shadow-lg hover:shadow-xl transition-all duration-300 bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20">
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium text-slate-700 dark:text-slate-300">Completed Interactions</CardTitle>
                                <div className="p-2 bg-purple-500 rounded-lg">
                                    <CheckCircle className="h-4 w-4 text-white" />
                                </div>
                            </CardHeader>
                            <CardContent>
                                <div className="text-3xl font-bold text-slate-900 dark:text-white">{stats.completedInteractions}</div>
                                <div className="flex items-center mt-2">
                                    <Activity className="h-3 w-3 text-purple-500 mr-1" />
                                    <p className="text-xs text-slate-600 dark:text-slate-400">
                                        This month
                                    </p>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    {/* Quick Actions */}
                    <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                        <CardHeader>
                            <CardTitle className="text-xl font-semibold text-slate-900 dark:text-white">Medical Representative Tools</CardTitle>
                            <CardDescription className="text-slate-600 dark:text-slate-300">Access your sales tools and doctor management</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="grid gap-4 md:grid-cols-3">
                                <Dialog open={isProductDialogOpen} onOpenChange={setIsProductDialogOpen}>
                                    <DialogTrigger asChild>
                                        <Button variant="outline" className="w-full justify-start h-12 border-slate-200 dark:border-slate-700 hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:border-blue-300 dark:hover:border-blue-600 transition-all duration-200 group">
                                            <div className="p-1 bg-blue-100 dark:bg-blue-900 rounded-md mr-3 group-hover:bg-blue-200 dark:group-hover:bg-blue-800 transition-colors">
                                                <Package className="h-4 w-4 text-blue-600 dark:text-blue-400" />
                                            </div>
                                            <span className="font-medium">Product Catalog</span>
                                            <ArrowUpRight className="h-4 w-4 ml-auto text-slate-400 group-hover:text-blue-500 transition-colors" />
                                        </Button>
                                    </DialogTrigger>
                                </Dialog>

                                <Dialog open={isMeetingDialogOpen} onOpenChange={setIsMeetingDialogOpen}>
                                    <DialogTrigger asChild>
                                        <Button variant="outline" className="w-full justify-start h-12 border-slate-200 dark:border-slate-700 hover:bg-orange-50 dark:hover:bg-orange-900/20 hover:border-orange-300 dark:hover:border-orange-600 transition-all duration-200 group">
                                            <div className="p-1 bg-orange-100 dark:bg-orange-900 rounded-md mr-3 group-hover:bg-orange-200 dark:group-hover:bg-orange-800 transition-colors">
                                                <Calendar className="h-4 w-4 text-orange-600 dark:text-orange-400" />
                                            </div>
                                            <span className="font-medium">Schedule Meeting</span>
                                            <ArrowUpRight className="h-4 w-4 ml-auto text-slate-400 group-hover:text-orange-500 transition-colors" />
                                        </Button>
                                    </DialogTrigger>
                                </Dialog>

                                <Dialog open={isInteractionDialogOpen} onOpenChange={setIsInteractionDialogOpen}>
                                    <DialogTrigger asChild>
                                        <Button variant="outline" className="w-full justify-start h-12 border-slate-200 dark:border-slate-700 hover:bg-purple-50 dark:hover:bg-purple-900/20 hover:border-purple-300 dark:hover:border-purple-600 transition-all duration-200 group">
                                            <div className="p-1 bg-purple-100 dark:bg-purple-900 rounded-md mr-3 group-hover:bg-purple-200 dark:group-hover:bg-purple-800 transition-colors">
                                                <FileText className="h-4 w-4 text-purple-600 dark:text-purple-400" />
                                            </div>
                                            <span className="font-medium">Log Interaction</span>
                                            <ArrowUpRight className="h-4 w-4 ml-auto text-slate-400 group-hover:text-purple-500 transition-colors" />
                                        </Button>
                                    </DialogTrigger>
                                </Dialog>
                            </div>
                        </CardContent>
                    </Card>

                    <div className="grid gap-6 md:grid-cols-2">
                        {/* Recent Meetings */}
                        <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                            <CardHeader>
                                <CardTitle className="text-lg font-semibold text-slate-900 dark:text-white">Upcoming Meetings</CardTitle>
                                <CardDescription className="text-slate-600 dark:text-slate-300">
                                    Your scheduled doctor meetings
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-4">
                                    {upcomingMeetings.length > 0 ? (
                                        upcomingMeetings.slice(0, 3).map((meeting) => (
                                            <div key={meeting.id} className="flex items-center justify-between p-3 rounded-lg bg-slate-50 dark:bg-slate-700/50 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                                                <div className="space-y-1">
                                                    <p className="text-sm font-medium leading-none text-slate-900 dark:text-white">
                                                        {meeting.doctor_name}
                                                    </p>
                                                    <p className="text-sm text-slate-600 dark:text-slate-400">
                                                        {new Date(meeting.date).toLocaleDateString()} at {meeting.time}
                                                    </p>
                                                    <p className="text-xs text-slate-500 dark:text-slate-500">
                                                        {meeting.purpose}
                                                    </p>
                                                </div>
                                                <Badge className={`${getStatusColor(meeting.status)} border-0`}>
                                                    {meeting.status}
                                                </Badge>
                                            </div>
                                        ))
                                    ) : (
                                        <div className="text-center py-8">
                                            <div className="p-3 bg-orange-100 dark:bg-orange-900/20 rounded-full w-fit mx-auto mb-3">
                                                <Calendar className="h-8 w-8 text-orange-600 dark:text-orange-400" />
                                            </div>
                                            <p className="text-sm text-slate-600 dark:text-slate-400">No upcoming meetings</p>
                                        </div>
                                    )}
                                </div>
                                <div className="mt-4">
                                    <Button asChild variant="outline" size="sm" className="w-full border-slate-200 dark:border-slate-700 hover:bg-orange-50 dark:hover:bg-orange-900/20 hover:border-orange-300 dark:hover:border-orange-600 transition-all duration-200">
                                        <Link href="/medrep/meetings">
                                            View All Meetings
                                        </Link>
                                    </Button>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Recent Interactions */}
                        <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                            <CardHeader>
                                <CardTitle className="text-lg font-semibold text-slate-900 dark:text-white">Recent Interactions</CardTitle>
                                <CardDescription className="text-slate-600 dark:text-slate-300">
                                    Your latest doctor interactions
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-4">
                                    {recentInteractions.length > 0 ? (
                                        recentInteractions.slice(0, 3).map((interaction) => (
                                            <div key={interaction.id} className="flex items-center justify-between p-3 rounded-lg bg-slate-50 dark:bg-slate-700/50 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                                                <div className="space-y-1">
                                                    <p className="text-sm font-medium leading-none text-slate-900 dark:text-white">
                                                        {interaction.doctor_name}
                                                    </p>
                                                    <p className="text-sm text-slate-600 dark:text-slate-400 line-clamp-2">
                                                        {interaction.notes}
                                                    </p>
                                                    <p className="text-xs text-slate-500 dark:text-slate-500">
                                                        {new Date(interaction.created_at).toLocaleDateString()}
                                                    </p>
                                                </div>
                                                <div className="flex flex-col gap-1">
                                                    {interaction.samples_provided.length > 0 && (
                                                        <Badge variant="outline" className="text-xs">
                                                            {interaction.samples_provided.length} samples
                                                        </Badge>
                                                    )}
                                                    {interaction.commitments.length > 0 && (
                                                        <Badge variant="secondary" className="text-xs">
                                                            {interaction.commitments.length} commitments
                                                        </Badge>
                                                    )}
                                                </div>
                                            </div>
                                        ))
                                    ) : (
                                        <div className="text-center py-8">
                                            <div className="p-3 bg-purple-100 dark:bg-purple-900/20 rounded-full w-fit mx-auto mb-3">
                                                <FileText className="h-8 w-8 text-purple-600 dark:text-purple-400" />
                                            </div>
                                            <p className="text-sm text-slate-600 dark:text-slate-400">No recent interactions</p>
                                        </div>
                                    )}
                                </div>
                                <div className="mt-4">
                                    <Button asChild variant="outline" size="sm" className="w-full border-slate-200 dark:border-slate-700 hover:bg-purple-50 dark:hover:bg-purple-900/20 hover:border-purple-300 dark:hover:border-purple-600 transition-all duration-200">
                                        <Link href="/medrep/interactions">
                                            View All Interactions
                                        </Link>
                                    </Button>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    {/* Alerts and Notifications */}
                    <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2 text-lg font-semibold text-slate-900 dark:text-white">
                                <div className="p-1 bg-orange-100 dark:bg-orange-900/20 rounded-md">
                                    <AlertCircle className="h-5 w-5 text-orange-600 dark:text-orange-400" />
                                </div>
                                Important Alerts
                            </CardTitle>
                            <CardDescription className="text-slate-600 dark:text-slate-300">
                                Items requiring your attention
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                {stats.scheduledMeetings > 0 && (
                                    <div className="flex items-center gap-4 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/10 dark:to-indigo-900/10 border border-blue-200 dark:border-blue-800/30 rounded-xl hover:shadow-md transition-all duration-200">
                                        <div className="p-2 bg-blue-100 dark:bg-blue-900/20 rounded-lg">
                                            <Calendar className="h-5 w-5 text-blue-600 dark:text-blue-400" />
                                        </div>
                                        <div className="flex-1">
                                            <p className="text-sm font-semibold text-blue-800 dark:text-blue-200">
                                                {stats.scheduledMeetings} meeting(s) scheduled
                                            </p>
                                            <p className="text-xs text-blue-600 dark:text-blue-400 mt-1">
                                                Review your upcoming meetings and prepare accordingly
                                            </p>
                                        </div>
                                        <Button asChild size="sm" variant="outline" className="border-blue-300 dark:border-blue-700 hover:bg-blue-100 dark:hover:bg-blue-900/20 hover:border-blue-400 dark:hover:border-blue-600 transition-all duration-200">
                                            <Link href="/medrep/meetings">
                                                View Schedule
                                            </Link>
                                        </Button>
                                    </div>
                                )}

                                {stats.totalProducts === 0 && (
                                    <div className="flex items-center gap-4 p-4 bg-gradient-to-r from-yellow-50 to-orange-50 dark:from-yellow-900/10 dark:to-orange-900/10 border border-yellow-200 dark:border-yellow-800/30 rounded-xl hover:shadow-md transition-all duration-200">
                                        <div className="p-2 bg-yellow-100 dark:bg-yellow-900/20 rounded-lg">
                                            <Package className="h-5 w-5 text-yellow-600 dark:text-yellow-400" />
                                        </div>
                                        <div className="flex-1">
                                            <p className="text-sm font-semibold text-yellow-800 dark:text-yellow-200">
                                                No products in your catalog
                                            </p>
                                            <p className="text-xs text-yellow-600 dark:text-yellow-400 mt-1">
                                                Add products to start building your portfolio
                                            </p>
                                        </div>
                                        <Button size="sm" variant="outline" className="border-yellow-300 dark:border-yellow-700 hover:bg-yellow-100 dark:hover:bg-yellow-900/20 hover:border-yellow-400 dark:hover:border-yellow-600 transition-all duration-200" onClick={() => setIsProductDialogOpen(true)}>
                                            Add Product
                                        </Button>
                                    </div>
                                )}

                                {stats.scheduledMeetings === 0 && stats.totalProducts > 0 && (
                                    <div className="text-center py-8">
                                        <div className="p-4 bg-green-100 dark:bg-green-900/20 rounded-full w-fit mx-auto mb-4">
                                            <CheckCircle className="h-12 w-12 text-green-600 dark:text-green-400" />
                                        </div>
                                        <h3 className="text-lg font-semibold text-slate-900 dark:text-white mb-2">All Caught Up!</h3>
                                        <p className="text-sm text-slate-600 dark:text-slate-400">No urgent items requiring your attention at this time.</p>
                                    </div>
                                )}
                            </div>
                        </CardContent>
                    </Card>

                    {/* Dialog Forms */}
                    <Dialog open={isProductDialogOpen} onOpenChange={setIsProductDialogOpen}>
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

                    <Dialog open={isMeetingDialogOpen} onOpenChange={setIsMeetingDialogOpen}>
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

                    <Dialog open={isInteractionDialogOpen} onOpenChange={setIsInteractionDialogOpen}>
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
