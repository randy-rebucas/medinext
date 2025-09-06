import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { 
    FileText, 
    Download,
    Eye,
    Calendar,
    Stethoscope,
    TestTube,
    Pill,
    User
} from 'lucide-react';

export default function MedicalRecords() {
    const medicalRecords = [
        {
            id: 1,
            date: '2024-01-18',
            doctor: 'Dr. Michael Brown',
            type: 'Follow-up',
            diagnosis: 'Hypertension management',
            treatment: 'Medication adjustment',
            status: 'Completed'
        },
        {
            id: 2,
            date: '2024-01-15',
            doctor: 'Dr. Emily Davis',
            type: 'Check-up',
            diagnosis: 'Annual physical examination',
            treatment: 'Routine check-up',
            status: 'Completed'
        },
        {
            id: 3,
            date: '2024-01-12',
            doctor: 'Dr. James Wilson',
            type: 'Consultation',
            diagnosis: 'Knee pain evaluation',
            treatment: 'Physical therapy recommended',
            status: 'Completed'
        }
    ];

    const labResults = [
        {
            id: 1,
            testName: 'Complete Blood Count',
            date: '2024-01-15',
            results: 'Normal',
            status: 'Completed'
        },
        {
            id: 2,
            testName: 'Lipid Panel',
            date: '2024-01-15',
            results: 'Normal',
            status: 'Completed'
        },
        {
            id: 3,
            testName: 'Blood Glucose',
            date: '2024-01-15',
            results: 'Normal',
            status: 'Completed'
        }
    ];

    const prescriptions = [
        {
            id: 1,
            medication: 'Lisinopril 10mg',
            dosage: 'Once daily',
            prescribedBy: 'Dr. Michael Brown',
            date: '2024-01-18',
            status: 'Active'
        },
        {
            id: 2,
            medication: 'Metformin 500mg',
            dosage: 'Twice daily',
            prescribedBy: 'Dr. Sarah Johnson',
            date: '2024-01-10',
            status: 'Active'
        }
    ];

    return (
        <AppLayout>
            <Head title="Medical Records" />
            
            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Medical Records</h1>
                        <p className="text-muted-foreground">
                            View your complete medical history and records
                        </p>
                    </div>
                    <Button>
                        <Download className="mr-2 h-4 w-4" />
                        Download Records
                    </Button>
                </div>

                <Tabs defaultValue="records" className="space-y-4">
                    <TabsList>
                        <TabsTrigger value="records">Medical Records</TabsTrigger>
                        <TabsTrigger value="lab-results">Lab Results</TabsTrigger>
                        <TabsTrigger value="prescriptions">Prescriptions</TabsTrigger>
                    </TabsList>

                    <TabsContent value="records" className="space-y-4">
                        <Card>
                            <CardHeader>
                                <CardTitle>Medical Records</CardTitle>
                                <CardDescription>
                                    Your complete medical history and visit records
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-4">
                                    {medicalRecords.map((record) => (
                                        <div key={record.id} className="flex items-center justify-between p-4 border rounded-lg">
                                            <div className="flex items-center space-x-4">
                                                <div className="flex-shrink-0">
                                                    <FileText className="h-5 w-5 text-blue-600" />
                                                </div>
                                                <div>
                                                    <h3 className="font-medium">{record.type}</h3>
                                                    <p className="text-sm text-muted-foreground">{record.diagnosis}</p>
                                                    <p className="text-sm text-muted-foreground">{record.treatment}</p>
                                                </div>
                                            </div>
                                            <div className="text-right">
                                                <div className="text-sm font-medium">{record.date}</div>
                                                <div className="text-xs text-muted-foreground">{record.doctor}</div>
                                                <Badge variant="default" className="mt-1">{record.status}</Badge>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </CardContent>
                        </Card>
                    </TabsContent>

                    <TabsContent value="lab-results" className="space-y-4">
                        <Card>
                            <CardHeader>
                                <CardTitle>Lab Results</CardTitle>
                                <CardDescription>
                                    Your laboratory test results and analysis
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-4">
                                    {labResults.map((result) => (
                                        <div key={result.id} className="flex items-center justify-between p-4 border rounded-lg">
                                            <div className="flex items-center space-x-4">
                                                <div className="flex-shrink-0">
                                                    <TestTube className="h-5 w-5 text-green-600" />
                                                </div>
                                                <div>
                                                    <h3 className="font-medium">{result.testName}</h3>
                                                    <p className="text-sm text-muted-foreground">Results: {result.results}</p>
                                                </div>
                                            </div>
                                            <div className="text-right">
                                                <div className="text-sm font-medium">{result.date}</div>
                                                <Badge variant="default" className="mt-1">{result.status}</Badge>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </CardContent>
                        </Card>
                    </TabsContent>

                    <TabsContent value="prescriptions" className="space-y-4">
                        <Card>
                            <CardHeader>
                                <CardTitle>Prescriptions</CardTitle>
                                <CardDescription>
                                    Your current and past medications
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
                                                    <p className="text-sm text-muted-foreground">Prescribed by: {prescription.prescribedBy}</p>
                                                </div>
                                            </div>
                                            <div className="text-right">
                                                <div className="text-sm font-medium">{prescription.date}</div>
                                                <Badge variant="default" className="mt-1">{prescription.status}</Badge>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </CardContent>
                        </Card>
                    </TabsContent>
                </Tabs>
            </div>
        </AppLayout>
    );
}
