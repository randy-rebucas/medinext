import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { 
    User, 
    Save,
    Mail,
    Phone,
    MapPin,
    Calendar,
    CreditCard,
    FileText
} from 'lucide-react';

export default function PatientProfile() {
    return (
        <AppLayout>
            <Head title="My Profile" />
            
            <div className="space-y-6">
                <div>
                    <h1 className="text-3xl font-bold tracking-tight">My Profile</h1>
                    <p className="text-muted-foreground">
                        Manage your personal information and preferences
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
                                Update your personal details and demographics
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid gap-4 md:grid-cols-2">
                                <div className="space-y-2">
                                    <Label htmlFor="first-name">First Name</Label>
                                    <Input id="first-name" defaultValue="John" />
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="last-name">Last Name</Label>
                                    <Input id="last-name" defaultValue="Doe" />
                                </div>
                            </div>
                            <div className="grid gap-4 md:grid-cols-2">
                                <div className="space-y-2">
                                    <Label htmlFor="date-of-birth">Date of Birth</Label>
                                    <Input id="date-of-birth" type="date" defaultValue="1989-05-15" />
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="gender">Gender</Label>
                                    <Input id="gender" defaultValue="Male" />
                                </div>
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="ssn">Social Security Number</Label>
                                <Input id="ssn" defaultValue="XXX-XX-1234" />
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
                                Update your contact details and address
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid gap-4 md:grid-cols-2">
                                <div className="space-y-2">
                                    <Label htmlFor="phone">Phone Number</Label>
                                    <Input id="phone" defaultValue="+1 (555) 123-4567" />
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="email">Email Address</Label>
                                    <Input id="email" type="email" defaultValue="john.doe@email.com" />
                                </div>
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="address">Address</Label>
                                <Textarea 
                                    id="address" 
                                    defaultValue="123 Main Street, Apt 4B, New York, NY 10001"
                                    rows={3}
                                />
                            </div>
                            <div className="grid gap-4 md:grid-cols-3">
                                <div className="space-y-2">
                                    <Label htmlFor="city">City</Label>
                                    <Input id="city" defaultValue="New York" />
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="state">State</Label>
                                    <Input id="state" defaultValue="NY" />
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="zip">ZIP Code</Label>
                                    <Input id="zip" defaultValue="10001" />
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
                                    <Input id="emergency-name" defaultValue="Jane Doe" />
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="emergency-phone">Emergency Contact Phone</Label>
                                    <Input id="emergency-phone" defaultValue="+1 (555) 987-6543" />
                                </div>
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="relationship">Relationship</Label>
                                <Input id="relationship" defaultValue="Spouse" />
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
                                    <Input id="insurance-provider" defaultValue="Blue Cross Blue Shield" />
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="policy-number">Policy Number</Label>
                                    <Input id="policy-number" defaultValue="BC123456789" />
                                </div>
                            </div>
                            <div className="grid gap-4 md:grid-cols-2">
                                <div className="space-y-2">
                                    <Label htmlFor="group-number">Group Number</Label>
                                    <Input id="group-number" defaultValue="GRP001" />
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="subscriber-id">Subscriber ID</Label>
                                    <Input id="subscriber-id" defaultValue="SUB123" />
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
                                Medical conditions, allergies, and medications
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="space-y-2">
                                <Label htmlFor="medical-conditions">Known Medical Conditions</Label>
                                <Textarea 
                                    id="medical-conditions" 
                                    defaultValue="Hypertension, Diabetes Type 2"
                                    rows={3}
                                />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="allergies">Allergies</Label>
                                <Textarea 
                                    id="allergies" 
                                    defaultValue="Penicillin, Shellfish"
                                    rows={2}
                                />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="current-medications">Current Medications</Label>
                                <Textarea 
                                    id="current-medications" 
                                    defaultValue="Lisinopril 10mg daily, Metformin 500mg twice daily"
                                    rows={2}
                                />
                            </div>
                        </CardContent>
                    </Card>

                    {/* Save Button */}
                    <div className="flex justify-end">
                        <Button>
                            <Save className="mr-2 h-4 w-4" />
                            Save Changes
                        </Button>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
