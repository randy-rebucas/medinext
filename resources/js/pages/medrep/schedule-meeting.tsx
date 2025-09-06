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
    Stethoscope
} from 'lucide-react';

export default function ScheduleMeeting() {
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
        <AppLayout>
            <Head title="Schedule Meeting" />
            
            <div className="space-y-6">
                <div>
                    <h1 className="text-3xl font-bold tracking-tight">Schedule Meeting</h1>
                    <p className="text-muted-foreground">
                        Schedule meetings with healthcare professionals
                    </p>
                </div>

                <div className="grid gap-6 lg:grid-cols-3">
                    {/* Meeting Form */}
                    <Card className="lg:col-span-2">
                        <CardHeader>
                            <CardTitle className="flex items-center">
                                <Calendar className="mr-2 h-5 w-5" />
                                Meeting Details
                            </CardTitle>
                            <CardDescription>
                                Fill in your meeting information
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid gap-4 md:grid-cols-2">
                                <div className="space-y-2">
                                    <Label htmlFor="meeting-date">Meeting Date</Label>
                                    <Input id="meeting-date" type="date" />
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="meeting-time">Meeting Time</Label>
                                    <Input id="meeting-time" type="time" />
                                </div>
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="meeting-type">Meeting Type</Label>
                                <Input id="meeting-type" placeholder="Select type" />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="meeting-location">Meeting Location</Label>
                                <Input id="meeting-location" placeholder="Enter meeting location" />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="meeting-purpose">Meeting Purpose</Label>
                                <Textarea 
                                    id="meeting-purpose" 
                                    placeholder="Describe the purpose of the meeting"
                                    rows={3}
                                />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="meeting-agenda">Meeting Agenda</Label>
                                <Textarea 
                                    id="meeting-agenda" 
                                    placeholder="Outline the meeting agenda"
                                    rows={3}
                                />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="meeting-notes">Additional Notes</Label>
                                <Textarea 
                                    id="meeting-notes" 
                                    placeholder="Any additional information"
                                    rows={2}
                                />
                            </div>
                        </CardContent>
                    </Card>

                    {/* Available Doctors */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center">
                                <Stethoscope className="mr-2 h-5 w-5" />
                                Available Doctors
                            </CardTitle>
                            <CardDescription>
                                Select your meeting participant
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            {doctors.map((doctor) => (
                                <div key={doctor.id} className="p-4 border rounded-lg">
                                    <div className="flex items-center justify-between mb-2">
                                        <h3 className="font-medium">{doctor.name}</h3>
                                        <Badge variant="outline">{doctor.rating} ⭐</Badge>
                                    </div>
                                    <p className="text-sm text-muted-foreground mb-3">{doctor.specialty}</p>
                                    <div className="space-y-2">
                                        <p className="text-sm font-medium">Available Times:</p>
                                        <div className="flex flex-wrap gap-2">
                                            {doctor.availableSlots.map((slot) => (
                                                <Button key={slot} variant="outline" size="sm">
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
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center">
                            <Calendar className="mr-2 h-5 w-5" />
                            Meeting Summary
                        </CardTitle>
                        <CardDescription>
                            Review your meeting details before confirming
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="grid gap-4 md:grid-cols-2">
                            <div className="space-y-2">
                                <div className="flex items-center">
                                    <User className="mr-2 h-4 w-4" />
                                    <span className="text-sm font-medium">MedRep:</span>
                                    <span className="ml-2 text-sm">John Smith</span>
                                </div>
                                <div className="flex items-center">
                                    <Stethoscope className="mr-2 h-4 w-4" />
                                    <span className="text-sm font-medium">Doctor:</span>
                                    <span className="ml-2 text-sm">Dr. Sarah Johnson</span>
                                </div>
                                <div className="flex items-center">
                                    <Calendar className="mr-2 h-4 w-4" />
                                    <span className="text-sm font-medium">Date:</span>
                                    <span className="ml-2 text-sm">January 25, 2024</span>
                                </div>
                                <div className="flex items-center">
                                    <Clock className="mr-2 h-4 w-4" />
                                    <span className="text-sm font-medium">Time:</span>
                                    <span className="ml-2 text-sm">10:30 AM</span>
                                </div>
                            </div>
                            <div className="space-y-2">
                                <div className="flex items-center">
                                    <MapPin className="mr-2 h-4 w-4" />
                                    <span className="text-sm font-medium">Location:</span>
                                    <span className="ml-2 text-sm">Conference Room A</span>
                                </div>
                                <div className="flex items-center">
                                    <FileText className="mr-2 h-4 w-4" />
                                    <span className="text-sm font-medium">Type:</span>
                                    <span className="ml-2 text-sm">Product Presentation</span>
                                </div>
                                <div className="flex items-center">
                                    <span className="text-sm font-medium">Duration:</span>
                                    <span className="ml-2 text-sm">30 minutes</span>
                                </div>
                                <div className="flex items-center">
                                    <span className="text-sm font-medium">Status:</span>
                                    <Badge variant="default" className="ml-2">Pending</Badge>
                                </div>
                            </div>
                        </div>
                        <div className="flex justify-end space-x-2 mt-6">
                            <Button variant="outline">
                                Cancel
                            </Button>
                            <Button>
                                <Save className="mr-2 h-4 w-4" />
                                Schedule Meeting
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
