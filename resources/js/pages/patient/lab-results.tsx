import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { 
    TestTube, 
    Download,
    Eye,
    Calendar,
    Stethoscope,
    AlertCircle,
    CheckCircle
} from 'lucide-react';

export default function PatientLabResults() {
    const labResults = [
        {
            id: 1,
            testName: 'Complete Blood Count',
            date: '2024-01-15',
            results: 'Normal',
            status: 'Completed',
            doctor: 'Dr. Michael Brown',
            notes: 'All values within normal range'
        },
        {
            id: 2,
            testName: 'Lipid Panel',
            date: '2024-01-15',
            results: 'Normal',
            status: 'Completed',
            doctor: 'Dr. Michael Brown',
            notes: 'Cholesterol levels are good'
        },
        {
            id: 3,
            testName: 'Blood Glucose',
            date: '2024-01-15',
            results: 'Normal',
            status: 'Completed',
            doctor: 'Dr. Michael Brown',
            notes: 'Blood sugar levels are within normal range'
        },
        {
            id: 4,
            testName: 'Thyroid Function Test',
            date: '2024-01-10',
            results: 'Abnormal',
            status: 'Completed',
            doctor: 'Dr. Sarah Johnson',
            notes: 'TSH levels slightly elevated, follow-up recommended'
        }
    ];

    const getStatusColor = (status: string) => {
        switch (status) {
            case 'Completed': return 'default';
            case 'Pending': return 'secondary';
            case 'Abnormal': return 'destructive';
            default: return 'secondary';
        }
    };

    const getResultsColor = (results: string) => {
        switch (results) {
            case 'Normal': return 'text-green-600';
            case 'Abnormal': return 'text-red-600';
            case 'Pending': return 'text-yellow-600';
            default: return 'text-gray-600';
        }
    };

    return (
        <AppLayout>
            <Head title="Lab Results" />
            
            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Lab Results</h1>
                        <p className="text-muted-foreground">
                            View your laboratory test results and analysis
                        </p>
                    </div>
                    <Button>
                        <Download className="mr-2 h-4 w-4" />
                        Download Results
                    </Button>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Laboratory Results</CardTitle>
                        <CardDescription>
                            Your complete lab test results and analysis
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-4">
                            {labResults.map((result) => (
                                <div key={result.id} className="flex items-center justify-between p-4 border rounded-lg">
                                    <div className="flex items-center space-x-4">
                                        <div className="flex-shrink-0">
                                            <TestTube className="h-5 w-5 text-blue-600" />
                                        </div>
                                        <div>
                                            <h3 className="font-medium">{result.testName}</h3>
                                            <p className="text-sm text-muted-foreground">{result.notes}</p>
                                            <div className="flex items-center space-x-4 mt-1">
                                                <span className="text-xs text-muted-foreground flex items-center">
                                                    <Calendar className="mr-1 h-3 w-3" />
                                                    {result.date}
                                                </span>
                                                <span className="text-xs text-muted-foreground flex items-center">
                                                    <Stethoscope className="mr-1 h-3 w-3" />
                                                    {result.doctor}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div className="flex items-center space-x-4">
                                        <div className="text-right">
                                            <div className={`text-sm font-medium ${getResultsColor(result.results)}`}>
                                                {result.results}
                                            </div>
                                            <Badge variant={getStatusColor(result.status)} className="mt-1">
                                                {result.status}
                                            </Badge>
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

                {/* Lab Results Summary */}
                <div className="grid gap-4 md:grid-cols-3">
                    <Card>
                        <CardContent className="p-6">
                            <div className="flex items-center">
                                <CheckCircle className="h-8 w-8 text-green-600" />
                                <div className="ml-4">
                                    <p className="text-sm font-medium text-muted-foreground">Normal Results</p>
                                    <p className="text-2xl font-bold">3</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-6">
                            <div className="flex items-center">
                                <AlertCircle className="h-8 w-8 text-yellow-600" />
                                <div className="ml-4">
                                    <p className="text-sm font-medium text-muted-foreground">Abnormal Results</p>
                                    <p className="text-2xl font-bold">1</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-6">
                            <div className="flex items-center">
                                <TestTube className="h-8 w-8 text-blue-600" />
                                <div className="ml-4">
                                    <p className="text-sm font-medium text-muted-foreground">Total Tests</p>
                                    <p className="text-2xl font-bold">4</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Important Notes */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center">
                            <AlertCircle className="mr-2 h-5 w-5" />
                            Important Notes
                        </CardTitle>
                        <CardDescription>
                            Important information about your lab results
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-4">
                            <div className="flex items-start p-4 border rounded-lg bg-yellow-50 dark:bg-yellow-900/20">
                                <AlertCircle className="h-5 w-5 text-yellow-600 mr-3 mt-0.5" />
                                <div>
                                    <h3 className="font-medium">Thyroid Function Test</h3>
                                    <p className="text-sm text-muted-foreground">
                                        Your TSH levels are slightly elevated. Please schedule a follow-up appointment with Dr. Sarah Johnson to discuss further evaluation and potential treatment options.
                                    </p>
                                </div>
                            </div>
                            <div className="flex items-start p-4 border rounded-lg bg-blue-50 dark:bg-blue-900/20">
                                <CheckCircle className="h-5 w-5 text-blue-600 mr-3 mt-0.5" />
                                <div>
                                    <h3 className="font-medium">General Health</h3>
                                    <p className="text-sm text-muted-foreground">
                                        Your overall lab results are good. Continue with your current medication regimen and maintain a healthy lifestyle.
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
