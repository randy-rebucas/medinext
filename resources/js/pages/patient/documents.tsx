import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { 
    Download, 
    FileText,
    Calendar,
    Stethoscope,
    Eye,
    File,
    Image,
    FileSpreadsheet
} from 'lucide-react';

export default function PatientDocuments() {
    const documents = [
        {
            id: 1,
            name: 'Medical Report - January 2024',
            type: 'Medical Report',
            date: '2024-01-18',
            doctor: 'Dr. Michael Brown',
            size: '2.3 MB',
            format: 'PDF'
        },
        {
            id: 2,
            name: 'Lab Results - Blood Test',
            type: 'Lab Results',
            date: '2024-01-15',
            doctor: 'Dr. Michael Brown',
            size: '1.8 MB',
            format: 'PDF'
        },
        {
            id: 3,
            name: 'Prescription - Lisinopril',
            type: 'Prescription',
            date: '2024-01-18',
            doctor: 'Dr. Michael Brown',
            size: '0.5 MB',
            format: 'PDF'
        },
        {
            id: 4,
            name: 'Insurance Card',
            type: 'Insurance',
            date: '2024-01-01',
            doctor: 'N/A',
            size: '1.2 MB',
            format: 'Image'
        },
        {
            id: 5,
            name: 'Appointment Summary',
            type: 'Appointment',
            date: '2024-01-15',
            doctor: 'Dr. Emily Davis',
            size: '0.8 MB',
            format: 'PDF'
        }
    ];

    const getFileIcon = (format: string) => {
        switch (format) {
            case 'PDF': return <FileText className="h-5 w-5 text-red-600" />;
            case 'Image': return <Image className="h-5 w-5 text-green-600" />;
            case 'Excel': return <FileSpreadsheet className="h-5 w-5 text-green-600" />;
            default: return <File className="h-5 w-5 text-gray-600" />;
        }
    };

    return (
        <AppLayout>
            <Head title="Download Documents" />
            
            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Download Documents</h1>
                        <p className="text-muted-foreground">
                            Access and download your medical documents and reports
                        </p>
                    </div>
                    <Button>
                        <Download className="mr-2 h-4 w-4" />
                        Download All
                    </Button>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Available Documents</CardTitle>
                        <CardDescription>
                            Your medical documents, reports, and records
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-4">
                            {documents.map((document) => (
                                <div key={document.id} className="flex items-center justify-between p-4 border rounded-lg">
                                    <div className="flex items-center space-x-4">
                                        <div className="flex-shrink-0">
                                            {getFileIcon(document.format)}
                                        </div>
                                        <div>
                                            <h3 className="font-medium">{document.name}</h3>
                                            <p className="text-sm text-muted-foreground">{document.type}</p>
                                            <div className="flex items-center space-x-4 mt-1">
                                                <span className="text-xs text-muted-foreground flex items-center">
                                                    <Calendar className="mr-1 h-3 w-3" />
                                                    {document.date}
                                                </span>
                                                <span className="text-xs text-muted-foreground flex items-center">
                                                    <Stethoscope className="mr-1 h-3 w-3" />
                                                    {document.doctor}
                                                </span>
                                                <span className="text-xs text-muted-foreground">
                                                    {document.size}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div className="flex items-center space-x-2">
                                        <Badge variant="outline">{document.format}</Badge>
                                        <Button variant="outline" size="sm">
                                            <Eye className="h-4 w-4" />
                                        </Button>
                                        <Button variant="outline" size="sm">
                                            <Download className="h-4 w-4" />
                                        </Button>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </CardContent>
                </Card>

                {/* Document Categories */}
                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <Card>
                        <CardContent className="p-6">
                            <div className="flex items-center">
                                <FileText className="h-8 w-8 text-blue-600" />
                                <div className="ml-4">
                                    <p className="text-sm font-medium text-muted-foreground">Medical Reports</p>
                                    <p className="text-2xl font-bold">2</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-6">
                            <div className="flex items-center">
                                <FileText className="h-8 w-8 text-green-600" />
                                <div className="ml-4">
                                    <p className="text-sm font-medium text-muted-foreground">Lab Results</p>
                                    <p className="text-2xl font-bold">1</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-6">
                            <div className="flex items-center">
                                <FileText className="h-8 w-8 text-purple-600" />
                                <div className="ml-4">
                                    <p className="text-sm font-medium text-muted-foreground">Prescriptions</p>
                                    <p className="text-2xl font-bold">1</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-6">
                            <div className="flex items-center">
                                <FileText className="h-8 w-8 text-orange-600" />
                                <div className="ml-4">
                                    <p className="text-sm font-medium text-muted-foreground">Other</p>
                                    <p className="text-2xl font-bold">2</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AppLayout>
    );
}
