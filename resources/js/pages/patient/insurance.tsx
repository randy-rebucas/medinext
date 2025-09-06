import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { 
    Shield, 
    CreditCard,
    Calendar,
    CheckCircle,
    AlertCircle,
    FileText,
    Download
} from 'lucide-react';

export default function PatientInsurance() {
    const insuranceInfo = {
        primary: {
            provider: 'Blue Cross Blue Shield',
            policyNumber: 'BC123456789',
            groupNumber: 'GRP001',
            subscriberId: 'SUB123',
            effectiveDate: '2024-01-01',
            expiryDate: '2024-12-31',
            status: 'Active',
            coverage: '100%'
        },
        secondary: {
            provider: 'Medicare',
            policyNumber: 'MED123456789',
            groupNumber: 'N/A',
            subscriberId: 'SUB456',
            effectiveDate: '2024-01-01',
            expiryDate: '2024-12-31',
            status: 'Active',
            coverage: '80%'
        }
    };

    const claims = [
        {
            id: 1,
            date: '2024-01-18',
            description: 'Consultation - Dr. Michael Brown',
            amount: 150.00,
            covered: 150.00,
            status: 'Approved',
            claimNumber: 'CLM001234'
        },
        {
            id: 2,
            date: '2024-01-15',
            description: 'Lab Tests - Blood Work',
            amount: 85.50,
            covered: 85.50,
            status: 'Approved',
            claimNumber: 'CLM001235'
        },
        {
            id: 3,
            date: '2024-01-12',
            description: 'Follow-up Visit - Dr. Emily Davis',
            amount: 120.00,
            covered: 120.00,
            status: 'Pending',
            claimNumber: 'CLM001236'
        }
    ];

    const getStatusColor = (status: string) => {
        switch (status) {
            case 'Active': return 'default';
            case 'Approved': return 'default';
            case 'Pending': return 'secondary';
            case 'Denied': return 'destructive';
            default: return 'secondary';
        }
    };

    return (
        <AppLayout>
            <Head title="Insurance" />
            
            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Insurance</h1>
                        <p className="text-muted-foreground">
                            Manage your insurance information and claims
                        </p>
                    </div>
                    <Button>
                        <Download className="mr-2 h-4 w-4" />
                        Download Card
                    </Button>
                </div>

                {/* Insurance Cards */}
                <div className="grid gap-6 md:grid-cols-2">
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center">
                                <Shield className="mr-2 h-5 w-5" />
                                Primary Insurance
                            </CardTitle>
                            <CardDescription>
                                Your primary insurance coverage
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="space-y-2">
                                <div className="flex justify-between">
                                    <span className="text-sm font-medium">Provider:</span>
                                    <span className="text-sm">{insuranceInfo.primary.provider}</span>
                                </div>
                                <div className="flex justify-between">
                                    <span className="text-sm font-medium">Policy Number:</span>
                                    <span className="text-sm font-mono">{insuranceInfo.primary.policyNumber}</span>
                                </div>
                                <div className="flex justify-between">
                                    <span className="text-sm font-medium">Group Number:</span>
                                    <span className="text-sm font-mono">{insuranceInfo.primary.groupNumber}</span>
                                </div>
                                <div className="flex justify-between">
                                    <span className="text-sm font-medium">Coverage:</span>
                                    <Badge variant="outline">{insuranceInfo.primary.coverage}</Badge>
                                </div>
                                <div className="flex justify-between">
                                    <span className="text-sm font-medium">Status:</span>
                                    <Badge variant={getStatusColor(insuranceInfo.primary.status)}>
                                        {insuranceInfo.primary.status}
                                    </Badge>
                                </div>
                                <div className="flex justify-between">
                                    <span className="text-sm font-medium">Effective Date:</span>
                                    <span className="text-sm">{insuranceInfo.primary.effectiveDate}</span>
                                </div>
                                <div className="flex justify-between">
                                    <span className="text-sm font-medium">Expiry Date:</span>
                                    <span className="text-sm">{insuranceInfo.primary.expiryDate}</span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center">
                                <Shield className="mr-2 h-5 w-5" />
                                Secondary Insurance
                            </CardTitle>
                            <CardDescription>
                                Your secondary insurance coverage
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="space-y-2">
                                <div className="flex justify-between">
                                    <span className="text-sm font-medium">Provider:</span>
                                    <span className="text-sm">{insuranceInfo.secondary.provider}</span>
                                </div>
                                <div className="flex justify-between">
                                    <span className="text-sm font-medium">Policy Number:</span>
                                    <span className="text-sm font-mono">{insuranceInfo.secondary.policyNumber}</span>
                                </div>
                                <div className="flex justify-between">
                                    <span className="text-sm font-medium">Group Number:</span>
                                    <span className="text-sm font-mono">{insuranceInfo.secondary.groupNumber}</span>
                                </div>
                                <div className="flex justify-between">
                                    <span className="text-sm font-medium">Coverage:</span>
                                    <Badge variant="outline">{insuranceInfo.secondary.coverage}</Badge>
                                </div>
                                <div className="flex justify-between">
                                    <span className="text-sm font-medium">Status:</span>
                                    <Badge variant={getStatusColor(insuranceInfo.secondary.status)}>
                                        {insuranceInfo.secondary.status}
                                    </Badge>
                                </div>
                                <div className="flex justify-between">
                                    <span className="text-sm font-medium">Effective Date:</span>
                                    <span className="text-sm">{insuranceInfo.secondary.effectiveDate}</span>
                                </div>
                                <div className="flex justify-between">
                                    <span className="text-sm font-medium">Expiry Date:</span>
                                    <span className="text-sm">{insuranceInfo.secondary.expiryDate}</span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Claims History */}
                <Card>
                    <CardHeader>
                        <CardTitle>Claims History</CardTitle>
                        <CardDescription>
                            Your insurance claims and coverage details
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-4">
                            {claims.map((claim) => (
                                <div key={claim.id} className="flex items-center justify-between p-4 border rounded-lg">
                                    <div className="flex items-center space-x-4">
                                        <div className="flex-shrink-0">
                                            <FileText className="h-5 w-5 text-blue-600" />
                                        </div>
                                        <div>
                                            <h3 className="font-medium">{claim.description}</h3>
                                            <p className="text-sm text-muted-foreground">Claim #: {claim.claimNumber}</p>
                                            <div className="flex items-center space-x-4 mt-1">
                                                <span className="text-xs text-muted-foreground flex items-center">
                                                    <Calendar className="mr-1 h-3 w-3" />
                                                    {claim.date}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div className="flex items-center space-x-4">
                                        <div className="text-right">
                                            <div className="text-sm font-medium">${claim.amount.toFixed(2)}</div>
                                            <div className="text-xs text-muted-foreground">Covered: ${claim.covered.toFixed(2)}</div>
                                            <Badge variant={getStatusColor(claim.status)} className="mt-1">
                                                {claim.status}
                                            </Badge>
                                        </div>
                                        <Button variant="outline" size="sm">
                                            <Download className="h-4 w-4" />
                                        </Button>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </CardContent>
                </Card>

                {/* Insurance Summary */}
                <div className="grid gap-4 md:grid-cols-3">
                    <Card>
                        <CardContent className="p-6">
                            <div className="flex items-center">
                                <CheckCircle className="h-8 w-8 text-green-600" />
                                <div className="ml-4">
                                    <p className="text-sm font-medium text-muted-foreground">Approved Claims</p>
                                    <p className="text-2xl font-bold">2</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-6">
                            <div className="flex items-center">
                                <AlertCircle className="h-8 w-8 text-yellow-600" />
                                <div className="ml-4">
                                    <p className="text-sm font-medium text-muted-foreground">Pending Claims</p>
                                    <p className="text-2xl font-bold">1</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-6">
                            <div className="flex items-center">
                                <CreditCard className="h-8 w-8 text-blue-600" />
                                <div className="ml-4">
                                    <p className="text-sm font-medium text-muted-foreground">Total Covered</p>
                                    <p className="text-2xl font-bold">$355.50</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AppLayout>
    );
}
