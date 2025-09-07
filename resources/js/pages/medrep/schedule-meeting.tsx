import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Badge } from '@/components/ui/badge';
import {
    Calendar,
    Save,
    Clock,
    User,
    MapPin,
    FileText,
    Stethoscope,
    Building2,
    Shield
} from 'lucide-react';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Schedule Meeting',
        href: '/medrep/schedule-meeting',
    },
    {
        title: 'Schedule Meeting',
        href: '/medrep/schedule-meeting',
    },
];

interface ScheduleMeetingProps {
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
}

export default function ScheduleMeeting({ user }: ScheduleMeetingProps = {}) {
    const doctors = [
        {
            id: 1,
            name: 'Dr. Sarah Johnson',
            specialty: 'Cardiology',
            availableSlots: ['09:00 AM', '10:30 AM', '02:00 PM'],
            rating: 4.9
        },
        {
            id: 2,
            name: 'Dr. Michael Brown',
            specialty: 'Pediatrics',
            availableSlots: ['08:30 AM', '11:00 AM', '03:30 PM'],
            rating: 4.8
        },
        {
            id: 3,
            name: 'Dr. Emily Davis',
            specialty: 'Dermatology',
            availableSlots: ['09:30 AM', '01:00 PM', '04:00 PM'],
            rating: 4.7
        }
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Schedule Meeting - Medinext">
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
                                    Schedule Meeting
                                </h1>
                                <p className="mt-2 text-emerald-100">
                                    Schedule meetings with healthcare professionals
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

                    <div className="grid gap-6 lg:grid-cols-3">
                        {/* Meeting Form */}
                        <Card className="lg:col-span-2 border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                            <CardHeader>
                                <CardTitle className="flex items-center text-slate-900 dark:text-white">
                                    <Calendar className="mr-2 h-5 w-5" />
                                    Meeting Details
                                </CardTitle>
                                <CardDescription className="text-slate-600 dark:text-slate-300">
                                    Fill in your meeting information
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="grid gap-4 md:grid-cols-2">
                                    <div className="space-y-2">
                                        <Label htmlFor="meeting-date" className="text-slate-700 dark:text-slate-300">Meeting Date</Label>
                                        <Input id="meeting-date" type="date" className="border-slate-200 dark:border-slate-700" />
                                    </div>
                                    <div className="space-y-2">
                                        <Label htmlFor="meeting-time" className="text-slate-700 dark:text-slate-300">Meeting Time</Label>
                                        <Input id="meeting-time" type="time" className="border-slate-200 dark:border-slate-700" />
                                    </div>
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="meeting-type" className="text-slate-700 dark:text-slate-300">Meeting Type</Label>
                                    <Input id="meeting-type" placeholder="Select type" className="border-slate-200 dark:border-slate-700" />
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="meeting-location" className="text-slate-700 dark:text-slate-300">Meeting Location</Label>
                                    <Input id="meeting-location" placeholder="Enter meeting location" className="border-slate-200 dark:border-slate-700" />
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="meeting-purpose" className="text-slate-700 dark:text-slate-300">Meeting Purpose</Label>
                                    <Textarea
                                        id="meeting-purpose"
                                        placeholder="Describe the purpose of the meeting"
                                        rows={3}
                                        className="border-slate-200 dark:border-slate-700"
                                    />
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="meeting-agenda" className="text-slate-700 dark:text-slate-300">Meeting Agenda</Label>
                                    <Textarea
                                        id="meeting-agenda"
                                        placeholder="Outline the meeting agenda"
                                        rows={3}
                                        className="border-slate-200 dark:border-slate-700"
                                    />
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="meeting-notes" className="text-slate-700 dark:text-slate-300">Additional Notes</Label>
                                    <Textarea
                                        id="meeting-notes"
                                        placeholder="Any additional information"
                                        rows={2}
                                        className="border-slate-200 dark:border-slate-700"
                                    />
                                </div>
                            </CardContent>
                        </Card>

                        {/* Available Doctors */}
                        <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                            <CardHeader>
                                <CardTitle className="flex items-center text-slate-900 dark:text-white">
                                    <Stethoscope className="mr-2 h-5 w-5" />
                                    Available Doctors
                                </CardTitle>
                                <CardDescription className="text-slate-600 dark:text-slate-300">
                                    Select your meeting participant
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                {doctors.map((doctor) => (
                                    <div key={doctor.id} className="p-4 rounded-lg bg-slate-50 dark:bg-slate-700/50 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                                        <div className="flex items-center justify-between mb-2">
                                            <h3 className="font-medium text-slate-900 dark:text-white">{doctor.name}</h3>
                                            <Badge variant="outline" className="border-slate-200 dark:border-slate-700">{doctor.rating} ⭐</Badge>
                                        </div>
                                        <p className="text-sm text-slate-600 dark:text-slate-400 mb-3">{doctor.specialty}</p>
                                        <div className="space-y-2">
                                            <p className="text-sm font-medium text-slate-700 dark:text-slate-300">Available Times:</p>
                                            <div className="flex flex-wrap gap-2">
                                                {doctor.availableSlots.map((slot) => (
                                                    <Button key={slot} variant="outline" size="sm" className="border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700">
                                                        {slot}
                                                    </Button>
                                                ))}
                                            </div>
                                        </div>
                                    </div>
                                ))}
                            </CardContent>
                        </Card>
                    </div>

                    {/* Meeting Summary */}
                    <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                        <CardHeader>
                            <CardTitle className="flex items-center text-slate-900 dark:text-white">
                                <Calendar className="mr-2 h-5 w-5" />
                                Meeting Summary
                            </CardTitle>
                            <CardDescription className="text-slate-600 dark:text-slate-300">
                                Review your meeting details before confirming
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="grid gap-4 md:grid-cols-2">
                                <div className="space-y-2">
                                    <div className="flex items-center">
                                        <User className="mr-2 h-4 w-4 text-slate-400" />
                                        <span className="text-sm font-medium text-slate-700 dark:text-slate-300">MedRep:</span>
                                        <span className="ml-2 text-sm text-slate-900 dark:text-white">John Smith</span>
                                    </div>
                                    <div className="flex items-center">
                                        <Stethoscope className="mr-2 h-4 w-4 text-slate-400" />
                                        <span className="text-sm font-medium text-slate-700 dark:text-slate-300">Doctor:</span>
                                        <span className="ml-2 text-sm text-slate-900 dark:text-white">Dr. Sarah Johnson</span>
                                    </div>
                                    <div className="flex items-center">
                                        <Calendar className="mr-2 h-4 w-4 text-slate-400" />
                                        <span className="text-sm font-medium text-slate-700 dark:text-slate-300">Date:</span>
                                        <span className="ml-2 text-sm text-slate-900 dark:text-white">January 25, 2024</span>
                                    </div>
                                    <div className="flex items-center">
                                        <Clock className="mr-2 h-4 w-4 text-slate-400" />
                                        <span className="text-sm font-medium text-slate-700 dark:text-slate-300">Time:</span>
                                        <span className="ml-2 text-sm text-slate-900 dark:text-white">10:30 AM</span>
                                    </div>
                                </div>
                                <div className="space-y-2">
                                    <div className="flex items-center">
                                        <MapPin className="mr-2 h-4 w-4 text-slate-400" />
                                        <span className="text-sm font-medium text-slate-700 dark:text-slate-300">Location:</span>
                                        <span className="ml-2 text-sm text-slate-900 dark:text-white">Conference Room A</span>
                                    </div>
                                    <div className="flex items-center">
                                        <FileText className="mr-2 h-4 w-4 text-slate-400" />
                                        <span className="text-sm font-medium text-slate-700 dark:text-slate-300">Type:</span>
                                        <span className="ml-2 text-sm text-slate-900 dark:text-white">Product Presentation</span>
                                    </div>
                                    <div className="flex items-center">
                                        <span className="text-sm font-medium text-slate-700 dark:text-slate-300">Duration:</span>
                                        <span className="ml-2 text-sm text-slate-900 dark:text-white">30 minutes</span>
                                    </div>
                                    <div className="flex items-center">
                                        <span className="text-sm font-medium text-slate-700 dark:text-slate-300">Status:</span>
                                        <Badge className="ml-2 bg-emerald-100 text-emerald-800 border-0">Pending</Badge>
                                    </div>
                                </div>
                            </div>
                            <div className="flex justify-end space-x-2 mt-6">
                                <Button variant="outline" className="border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800">
                                    Cancel
                                </Button>
                                <Button className="bg-emerald-600 hover:bg-emerald-700 text-white">
                                    <Save className="mr-2 h-4 w-4" />
                                    Schedule Meeting
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AppLayout>
    );
}
