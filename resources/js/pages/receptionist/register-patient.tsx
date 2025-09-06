import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { 
    UserPlus, 
    Save,
    User,
    Mail,
    Phone,
    MapPin,
    Calendar,
    CreditCard,
    FileText
} from 'lucide-react';

export default function RegisterPatient() {
    return (
        <AppLayout>
            <Head title="Register Patient" />
            
            <div className="space-y-6">
                <div>
                    <h1 className="text-3xl font-bold tracking-tight">Register New Patient</h1>
                    <p className="text-muted-foreground">
                        Add a new patient to the clinic system
                    </p>
                </div>

                <div className="grid gap-6">
                    {/* Personal Information */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center">
                                <User className="mr-2 h-5 w-5" />
                                Personal Information
                            </CardTitle>
                            <CardDescription>
                                Basic patient information and demographics
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid gap-4 md:grid-cols-2">
                                <div className="space-y-2">
                                    <Label htmlFor="first-name">First Name</Label>
                                    <Input id="first-name" placeholder="Enter first name" />
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="last-name">Last Name</Label>
                                    <Input id="last-name" placeholder="Enter last name" />
                                </div>
                            </div>
                            <div className="grid gap-4 md:grid-cols-2">
                                <div className="space-y-2">
                                    <Label htmlFor="date-of-birth">Date of Birth</Label>
                                    <Input id="date-of-birth" type="date" />
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="gender">Gender</Label>
                                    <Input id="gender" placeholder="Select gender" />
                                </div>
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="ssn">Social Security Number</Label>
                                <Input id="ssn" placeholder="XXX-XX-XXXX" />
                            </div>
                        </CardContent>
                    </Card>

                    {/* Contact Information */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center">
                                <Phone className="mr-2 h-5 w-5" />
                                Contact Information
                            </CardTitle>
                            <CardDescription>
                                Patient contact details and address
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid gap-4 md:grid-cols-2">
                                <div className="space-y-2">
                                    <Label htmlFor="phone">Phone Number</Label>
                                    <Input id="phone" placeholder="+1 (555) 123-4567" />
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="email">Email Address</Label>
                                    <Input id="email" type="email" placeholder="patient@email.com" />
                                </div>
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="address">Address</Label>
                                <Textarea 
                                    id="address" 
                                    placeholder="Enter full address"
                                    rows={3}
                                />
                            </div>
                            <div className="grid gap-4 md:grid-cols-3">
                                <div className="space-y-2">
                                    <Label htmlFor="city">City</Label>
                                    <Input id="city" placeholder="Enter city" />
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="state">State</Label>
                                    <Input id="state" placeholder="Enter state" />
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="zip">ZIP Code</Label>
                                    <Input id="zip" placeholder="Enter ZIP code" />
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Emergency Contact */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center">
                                <User className="mr-2 h-5 w-5" />
                                Emergency Contact
                            </CardTitle>
                            <CardDescription>
                                Emergency contact information
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid gap-4 md:grid-cols-2">
                                <div className="space-y-2">
                                    <Label htmlFor="emergency-name">Emergency Contact Name</Label>
                                    <Input id="emergency-name" placeholder="Enter emergency contact name" />
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="emergency-phone">Emergency Contact Phone</Label>
                                    <Input id="emergency-phone" placeholder="+1 (555) 123-4567" />
                                </div>
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="relationship">Relationship</Label>
                                <Input id="relationship" placeholder="e.g., Spouse, Parent, Sibling" />
                            </div>
                        </CardContent>
                    </Card>

                    {/* Insurance Information */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center">
                                <CreditCard className="mr-2 h-5 w-5" />
                                Insurance Information
                            </CardTitle>
                            <CardDescription>
                                Primary insurance details
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid gap-4 md:grid-cols-2">
                                <div className="space-y-2">
                                    <Label htmlFor="insurance-provider">Insurance Provider</Label>
                                    <Input id="insurance-provider" placeholder="e.g., Blue Cross, Aetna" />
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="policy-number">Policy Number</Label>
                                    <Input id="policy-number" placeholder="Enter policy number" />
                                </div>
                            </div>
                            <div className="grid gap-4 md:grid-cols-2">
                                <div className="space-y-2">
                                    <Label htmlFor="group-number">Group Number</Label>
                                    <Input id="group-number" placeholder="Enter group number" />
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="subscriber-id">Subscriber ID</Label>
                                    <Input id="subscriber-id" placeholder="Enter subscriber ID" />
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Medical Information */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center">
                                <FileText className="mr-2 h-5 w-5" />
                                Medical Information
                            </CardTitle>
                            <CardDescription>
                                Initial medical information and notes
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="space-y-2">
                                <Label htmlFor="medical-conditions">Known Medical Conditions</Label>
                                <Textarea 
                                    id="medical-conditions" 
                                    placeholder="List any known medical conditions, allergies, or medications"
                                    rows={4}
                                />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="notes">Additional Notes</Label>
                                <Textarea 
                                    id="notes" 
                                    placeholder="Any additional information about the patient"
                                    rows={3}
                                />
                            </div>
                        </CardContent>
                    </Card>

                    {/* Save Button */}
                    <div className="flex justify-end space-x-2">
                        <Button variant="outline">
                            Cancel
                        </Button>
                        <Button>
                            <Save className="mr-2 h-4 w-4" />
                            Register Patient
                        </Button>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
