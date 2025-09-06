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
    Clock,
    Stethoscope,
    User,
    FileText,
    CheckCircle
} from 'lucide-react';

export default function BookAppointment() {
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
            <Head title="Book Appointment" />
            
            <div className="space-y-6">
                <div>
                    <h1 className="text-3xl font-bold tracking-tight">Book Appointment</h1>
                    <p className="text-muted-foreground">
                        Schedule your appointment with our healthcare providers
                    </p>
                </div>

                <div className="grid gap-6 lg:grid-cols-3">
                    {/* Appointment Form */}
                    <Card className="lg:col-span-2">
                        <CardHeader>
                            <CardTitle className="flex items-center">
                                <Calendar className="mr-2 h-5 w-5" />
                                Appointment Details
                            </CardTitle>
                            <CardDescription>
                                Fill in your appointment information
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid gap-4 md:grid-cols-2">
                                <div className="space-y-2">
                                    <Label htmlFor="appointment-date">Preferred Date</Label>
                                    <Input id="appointment-date" type="date" />
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="appointment-type">Appointment Type</Label>
                                    <Input id="appointment-type" placeholder="Select type" />
                                </div>
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="reason">Reason for Visit</Label>
                                <Textarea 
                                    id="reason" 
                                    placeholder="Please describe the reason for your visit"
                                    rows={3}
                                />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="symptoms">Current Symptoms</Label>
                                <Textarea 
                                    id="symptoms" 
                                    placeholder="Describe any current symptoms"
                                    rows={3}
                                />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="notes">Additional Notes</Label>
                                <Textarea 
                                    id="notes" 
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
                                Select your preferred doctor
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

                {/* Appointment Summary */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center">
                            <CheckCircle className="mr-2 h-5 w-5" />
                            Appointment Summary
                        </CardTitle>
                        <CardDescription>
                            Review your appointment details before confirming
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="grid gap-4 md:grid-cols-2">
                            <div className="space-y-2">
                                <div className="flex items-center">
                                    <User className="mr-2 h-4 w-4" />
                                    <span className="text-sm font-medium">Patient:</span>
                                    <span className="ml-2 text-sm">John Doe</span>
                                </div>
                                <div className="flex items-center">
                                    <Stethoscope className="mr-2 h-4 w-4" />
                                    <span className="text-sm font-medium">Doctor:</span>
                                    <span className="ml-2 text-sm">Dr. Sarah Johnson</span>
                                </div>
                                <div className="flex items-center">
                                    <Calendar className="mr-2 h-4 w-4" />
                                    <span className="text-sm font-medium">Date:</span>
                                    <span className="ml-2 text-sm">January 20, 2024</span>
                                </div>
                                <div className="flex items-center">
                                    <Clock className="mr-2 h-4 w-4" />
                                    <span className="text-sm font-medium">Time:</span>
                                    <span className="ml-2 text-sm">09:00 AM</span>
                                </div>
                            </div>
                            <div className="space-y-2">
                                <div className="flex items-center">
                                    <FileText className="mr-2 h-4 w-4" />
                                    <span className="text-sm font-medium">Type:</span>
                                    <span className="ml-2 text-sm">Consultation</span>
                                </div>
                                <div className="flex items-center">
                                    <span className="text-sm font-medium">Room:</span>
                                    <span className="ml-2 text-sm">Room 101</span>
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
                                <CheckCircle className="mr-2 h-4 w-4" />
                                Confirm Appointment
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
