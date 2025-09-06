import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { patientPrescriptions } from '@/routes';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Patient Portal',
        href: '/patient/dashboard',
    },
    {
        title: 'My Prescriptions',
        href: patientPrescriptions(),
    },
];
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { 
    Pill, 
    Download,
    Eye,
    Calendar,
    Stethoscope,
    Clock,
    AlertCircle
} from 'lucide-react';

export default function PatientPrescriptions() {
    const prescriptions = [
        {
            id: 1,
            medication: 'Lisinopril 10mg',
            dosage: 'Once daily',
            prescribedBy: 'Dr. Michael Brown',
            date: '2024-01-18',
            status: 'Active',
            refills: 3,
            nextRefill: '2024-02-18',
            instructions: 'Take with food, preferably in the morning'
        },
        {
            id: 2,
            medication: 'Metformin 500mg',
            dosage: 'Twice daily',
            prescribedBy: 'Dr. Sarah Johnson',
            date: '2024-01-10',
            status: 'Active',
            refills: 2,
            nextRefill: '2024-02-10',
            instructions: 'Take with meals to reduce stomach upset'
        },
        {
            id: 3,
            medication: 'Atorvastatin 20mg',
            dosage: 'Once daily at bedtime',
            prescribedBy: 'Dr. Emily Davis',
            date: '2024-01-15',
            status: 'Active',
            refills: 1,
            nextRefill: '2024-02-15',
            instructions: 'Take at bedtime, avoid alcohol'
        },
        {
            id: 4,
            medication: 'Ibuprofen 400mg',
            dosage: 'As needed for pain',
            prescribedBy: 'Dr. James Wilson',
            date: '2024-01-12',
            status: 'Completed',
            refills: 0,
            nextRefill: 'N/A',
            instructions: 'Take with food, maximum 3 times daily'
        }
    ];

    const getStatusColor = (status: string) => {
        switch (status) {
            case 'Active': return 'default';
            case 'Completed': return 'secondary';
            case 'Expired': return 'destructive';
            default: return 'secondary';
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="My Prescriptions" />
            
            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">My Prescriptions</h1>
                        <p className="text-muted-foreground">
                            View and manage your current medications
                        </p>
                    </div>
                    <Button>
                        <Download className="mr-2 h-4 w-4" />
                        Download Prescriptions
                    </Button>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Current Prescriptions</CardTitle>
                        <CardDescription>
                            Your active and past medication prescriptions
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-4">
                            {prescriptions.map((prescription) => (
                                <div key={prescription.id} className="flex items-center justify-between p-4 border rounded-lg">
                                    <div className="flex items-center space-x-4">
                                        <div className="flex-shrink-0">
                                            <Pill className="h-5 w-5 text-purple-600" />
                                        </div>
                                        <div>
                                            <h3 className="font-medium">{prescription.medication}</h3>
                                            <p className="text-sm text-muted-foreground">Dosage: {prescription.dosage}</p>
                                            <p className="text-sm text-muted-foreground">Instructions: {prescription.instructions}</p>
                                            <div className="flex items-center space-x-4 mt-1">
                                                <span className="text-xs text-muted-foreground flex items-center">
                                                    <Stethoscope className="mr-1 h-3 w-3" />
                                                    {prescription.prescribedBy}
                                                </span>
                                                <span className="text-xs text-muted-foreground flex items-center">
                                                    <Calendar className="mr-1 h-3 w-3" />
                                                    {prescription.date}
                                                </span>
                                                <span className="text-xs text-muted-foreground">
                                                    Refills: {prescription.refills}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div className="flex items-center space-x-4">
                                        <div className="text-right">
                                            <Badge variant={getStatusColor(prescription.status)} className="mb-1">
                                                {prescription.status}
                                            </Badge>
                                            <div className="text-sm font-medium">Next Refill</div>
                                            <div className="text-xs text-muted-foreground">{prescription.nextRefill}</div>
                                        </div>
                                        <div className="flex items-center space-x-2">
                                            <Button variant="outline" size="sm">
                                                <Eye className="h-4 w-4" />
                                            </Button>
                                            <Button variant="outline" size="sm">
                                                <Download className="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </CardContent>
                </Card>

                {/* Prescription Alerts */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center">
                            <AlertCircle className="mr-2 h-5 w-5" />
                            Prescription Alerts
                        </CardTitle>
                        <CardDescription>
                            Important reminders about your medications
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-4">
                            <div className="flex items-center p-4 border rounded-lg bg-yellow-50 dark:bg-yellow-900/20">
                                <AlertCircle className="h-5 w-5 text-yellow-600 mr-3" />
                                <div>
                                    <h3 className="font-medium">Refill Reminder</h3>
                                    <p className="text-sm text-muted-foreground">
                                        Your Lisinopril prescription will need a refill in 5 days.
                                    </p>
                                </div>
                            </div>
                            <div className="flex items-center p-4 border rounded-lg bg-blue-50 dark:bg-blue-900/20">
                                <Clock className="h-5 w-5 text-blue-600 mr-3" />
                                <div>
                                    <h3 className="font-medium">Medication Reminder</h3>
                                    <p className="text-sm text-muted-foreground">
                                        Remember to take your Metformin with meals.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
