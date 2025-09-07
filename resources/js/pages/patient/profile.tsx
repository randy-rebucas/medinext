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
    Phone,
    CreditCard,
    FileText,
    Shield
} from 'lucide-react';
import { patientDashboard, patientProfile } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Badge } from '@/components/ui/badge';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Patient Portal',
        href: patientDashboard(),
    },
    {
        title: 'My Profile',
        href: patientProfile(),
    },
];


interface PatientProfileProps {
    user?: {
        id: number;
        name: string;
        email: string;
        patient_id: string;
        sex: string;
    };
    permissions?: string[];
}

export default function PatientProfile({ user, permissions }: PatientProfileProps) {
    const hasPermission = (permission: string) => {
        return permissions?.includes(permission) ?? true;
    };
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="My Profile - Medinext">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&family=instrument-sans:400,500,600" rel="stylesheet" />
            </Head>
            <div className="min-h-screen bg-gradient-to-br from-slate-50 via-white to-blue-50 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900">
                <div className="space-y-6 p-6">
                    {/* Modern Header */}
                    <div className="relative overflow-hidden rounded-2xl bg-gradient-to-r from-rose-600 to-pink-600 p-8 text-white shadow-xl">
                        <div className="absolute inset-0 bg-black/10"></div>
                        <div className="relative flex items-center justify-between">
                            <div>
                                <h1 className="text-3xl font-bold tracking-tight">My Profile</h1>
                                <p className="mt-2 text-rose-100">
                                    Manage your personal information and preferences
                                </p>
                            </div>
                            <div className="flex items-center gap-3">
                                <Badge variant="secondary" className="flex items-center gap-1 bg-white/20 text-white border-white/30 hover:bg-white/30">
                                    <Shield className="h-3 w-3" />
                                    Patient
                                </Badge>
                                {user && (
                                    <Badge variant="secondary" className="flex items-center gap-1 bg-white/20 text-white border-white/30 hover:bg-white/30">
                                        <User className="h-3 w-3" />
                                        {user.sex}
                                    </Badge>
                                )}
                            </div>
                        </div>
                        {/* Decorative elements */}
                        <div className="absolute -top-4 -right-4 w-24 h-24 bg-white/10 rounded-full"></div>
                        <div className="absolute -bottom-2 -left-2 w-16 h-16 bg-white/5 rounded-full"></div>
                    </div>

                    <div className="grid gap-6">
                        {/* Personal Information */}
                        <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                            <CardHeader>
                                <CardTitle className="flex items-center text-lg font-semibold text-slate-900 dark:text-white">
                                    <User className="mr-2 h-5 w-5 text-rose-600 dark:text-rose-400" />
                                    Personal Information
                                </CardTitle>
                                <CardDescription className="text-slate-600 dark:text-slate-300">
                                    Update your personal details and demographics
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="grid gap-4 md:grid-cols-2">
                                    <div className="space-y-2">
                                        <Label htmlFor="first-name" className="text-slate-700 dark:text-slate-300">First Name</Label>
                                        <Input id="first-name" defaultValue="John" className="border-slate-200 dark:border-slate-700 focus:border-rose-500 dark:focus:border-rose-400" />
                                    </div>
                                    <div className="space-y-2">
                                        <Label htmlFor="last-name" className="text-slate-700 dark:text-slate-300">Last Name</Label>
                                        <Input id="last-name" defaultValue="Doe" className="border-slate-200 dark:border-slate-700 focus:border-rose-500 dark:focus:border-rose-400" />
                                    </div>
                                </div>
                                <div className="grid gap-4 md:grid-cols-2">
                                    <div className="space-y-2">
                                        <Label htmlFor="date-of-birth" className="text-slate-700 dark:text-slate-300">Date of Birth</Label>
                                        <Input id="date-of-birth" type="date" defaultValue="1989-05-15" className="border-slate-200 dark:border-slate-700 focus:border-rose-500 dark:focus:border-rose-400" />
                                    </div>
                                    <div className="space-y-2">
                                        <Label htmlFor="gender" className="text-slate-700 dark:text-slate-300">Gender</Label>
                                        <Input id="gender" defaultValue="Male" className="border-slate-200 dark:border-slate-700 focus:border-rose-500 dark:focus:border-rose-400" />
                                    </div>
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="ssn" className="text-slate-700 dark:text-slate-300">Social Security Number</Label>
                                    <Input id="ssn" defaultValue="XXX-XX-1234" className="border-slate-200 dark:border-slate-700 focus:border-rose-500 dark:focus:border-rose-400" />
                                </div>
                            </CardContent>
                        </Card>

                        {/* Contact Information */}
                        <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                            <CardHeader>
                                <CardTitle className="flex items-center text-lg font-semibold text-slate-900 dark:text-white">
                                    <Phone className="mr-2 h-5 w-5 text-rose-600 dark:text-rose-400" />
                                    Contact Information
                                </CardTitle>
                                <CardDescription className="text-slate-600 dark:text-slate-300">
                                    Update your contact details and address
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="grid gap-4 md:grid-cols-2">
                                    <div className="space-y-2">
                                        <Label htmlFor="phone" className="text-slate-700 dark:text-slate-300">Phone Number</Label>
                                        <Input id="phone" defaultValue="+1 (555) 123-4567" className="border-slate-200 dark:border-slate-700 focus:border-rose-500 dark:focus:border-rose-400" />
                                    </div>
                                    <div className="space-y-2">
                                        <Label htmlFor="email" className="text-slate-700 dark:text-slate-300">Email Address</Label>
                                        <Input id="email" type="email" defaultValue="john.doe@email.com" className="border-slate-200 dark:border-slate-700 focus:border-rose-500 dark:focus:border-rose-400" />
                                    </div>
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="address" className="text-slate-700 dark:text-slate-300">Address</Label>
                                    <Textarea
                                        id="address"
                                        defaultValue="123 Main Street, Apt 4B, New York, NY 10001"
                                        rows={3}
                                        className="border-slate-200 dark:border-slate-700 focus:border-rose-500 dark:focus:border-rose-400"
                                    />
                                </div>
                                <div className="grid gap-4 md:grid-cols-3">
                                    <div className="space-y-2">
                                        <Label htmlFor="city" className="text-slate-700 dark:text-slate-300">City</Label>
                                        <Input id="city" defaultValue="New York" className="border-slate-200 dark:border-slate-700 focus:border-rose-500 dark:focus:border-rose-400" />
                                    </div>
                                    <div className="space-y-2">
                                        <Label htmlFor="state" className="text-slate-700 dark:text-slate-300">State</Label>
                                        <Input id="state" defaultValue="NY" className="border-slate-200 dark:border-slate-700 focus:border-rose-500 dark:focus:border-rose-400" />
                                    </div>
                                    <div className="space-y-2">
                                        <Label htmlFor="zip" className="text-slate-700 dark:text-slate-300">ZIP Code</Label>
                                        <Input id="zip" defaultValue="10001" className="border-slate-200 dark:border-slate-700 focus:border-rose-500 dark:focus:border-rose-400" />
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Emergency Contact */}
                        <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                            <CardHeader>
                                <CardTitle className="flex items-center text-lg font-semibold text-slate-900 dark:text-white">
                                    <User className="mr-2 h-5 w-5 text-rose-600 dark:text-rose-400" />
                                    Emergency Contact
                                </CardTitle>
                                <CardDescription className="text-slate-600 dark:text-slate-300">
                                    Emergency contact information
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="grid gap-4 md:grid-cols-2">
                                    <div className="space-y-2">
                                        <Label htmlFor="emergency-name" className="text-slate-700 dark:text-slate-300">Emergency Contact Name</Label>
                                        <Input id="emergency-name" defaultValue="Jane Doe" className="border-slate-200 dark:border-slate-700 focus:border-rose-500 dark:focus:border-rose-400" />
                                    </div>
                                    <div className="space-y-2">
                                        <Label htmlFor="emergency-phone" className="text-slate-700 dark:text-slate-300">Emergency Contact Phone</Label>
                                        <Input id="emergency-phone" defaultValue="+1 (555) 987-6543" className="border-slate-200 dark:border-slate-700 focus:border-rose-500 dark:focus:border-rose-400" />
                                    </div>
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="relationship" className="text-slate-700 dark:text-slate-300">Relationship</Label>
                                    <Input id="relationship" defaultValue="Spouse" className="border-slate-200 dark:border-slate-700 focus:border-rose-500 dark:focus:border-rose-400" />
                                </div>
                            </CardContent>
                        </Card>

                        {/* Insurance Information */}
                        <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                            <CardHeader>
                                <CardTitle className="flex items-center text-lg font-semibold text-slate-900 dark:text-white">
                                    <CreditCard className="mr-2 h-5 w-5 text-rose-600 dark:text-rose-400" />
                                    Insurance Information
                                </CardTitle>
                                <CardDescription className="text-slate-600 dark:text-slate-300">
                                    Primary insurance details
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="grid gap-4 md:grid-cols-2">
                                    <div className="space-y-2">
                                        <Label htmlFor="insurance-provider" className="text-slate-700 dark:text-slate-300">Insurance Provider</Label>
                                        <Input id="insurance-provider" defaultValue="Blue Cross Blue Shield" className="border-slate-200 dark:border-slate-700 focus:border-rose-500 dark:focus:border-rose-400" />
                                    </div>
                                    <div className="space-y-2">
                                        <Label htmlFor="policy-number" className="text-slate-700 dark:text-slate-300">Policy Number</Label>
                                        <Input id="policy-number" defaultValue="BC123456789" className="border-slate-200 dark:border-slate-700 focus:border-rose-500 dark:focus:border-rose-400" />
                                    </div>
                                </div>
                                <div className="grid gap-4 md:grid-cols-2">
                                    <div className="space-y-2">
                                        <Label htmlFor="group-number" className="text-slate-700 dark:text-slate-300">Group Number</Label>
                                        <Input id="group-number" defaultValue="GRP001" className="border-slate-200 dark:border-slate-700 focus:border-rose-500 dark:focus:border-rose-400" />
                                    </div>
                                    <div className="space-y-2">
                                        <Label htmlFor="subscriber-id" className="text-slate-700 dark:text-slate-300">Subscriber ID</Label>
                                        <Input id="subscriber-id" defaultValue="SUB123" className="border-slate-200 dark:border-slate-700 focus:border-rose-500 dark:focus:border-rose-400" />
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Medical Information */}
                        <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                            <CardHeader>
                                <CardTitle className="flex items-center text-lg font-semibold text-slate-900 dark:text-white">
                                    <FileText className="mr-2 h-5 w-5 text-rose-600 dark:text-rose-400" />
                                    Medical Information
                                </CardTitle>
                                <CardDescription className="text-slate-600 dark:text-slate-300">
                                    Medical conditions, allergies, and medications
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="space-y-2">
                                    <Label htmlFor="medical-conditions" className="text-slate-700 dark:text-slate-300">Known Medical Conditions</Label>
                                    <Textarea
                                        id="medical-conditions"
                                        defaultValue="Hypertension, Diabetes Type 2"
                                        rows={3}
                                        className="border-slate-200 dark:border-slate-700 focus:border-rose-500 dark:focus:border-rose-400"
                                    />
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="allergies" className="text-slate-700 dark:text-slate-300">Allergies</Label>
                                    <Textarea
                                        id="allergies"
                                        defaultValue="Penicillin, Shellfish"
                                        rows={2}
                                        className="border-slate-200 dark:border-slate-700 focus:border-rose-500 dark:focus:border-rose-400"
                                    />
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="current-medications" className="text-slate-700 dark:text-slate-300">Current Medications</Label>
                                    <Textarea
                                        id="current-medications"
                                        defaultValue="Lisinopril 10mg daily, Metformin 500mg twice daily"
                                        rows={2}
                                        className="border-slate-200 dark:border-slate-700 focus:border-rose-500 dark:focus:border-rose-400"
                                    />
                                </div>
                            </CardContent>
                        </Card>

                        {/* Save Button */}
                        {hasPermission('profile.update') && (
                            <div className="flex justify-end">
                                <Button className="hover:bg-rose-600 hover:border-rose-600 transition-all duration-200">
                                    <Save className="mr-2 h-4 w-4" />
                                    Save Changes
                                </Button>
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
