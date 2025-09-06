import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { 
    CreditCard, 
    Download,
    Eye,
    Calendar,
    DollarSign,
    FileText,
    CheckCircle,
    Clock,
    AlertCircle
} from 'lucide-react';

export default function PatientBilling() {
    const bills = [
        {
            id: 1,
            date: '2024-01-18',
            description: 'Consultation - Dr. Michael Brown',
            amount: 150.00,
            status: 'Paid',
            dueDate: '2024-02-18',
            paymentMethod: 'Credit Card'
        },
        {
            id: 2,
            date: '2024-01-15',
            description: 'Lab Tests - Blood Work',
            amount: 85.50,
            status: 'Paid',
            dueDate: '2024-02-15',
            paymentMethod: 'Insurance'
        },
        {
            id: 3,
            date: '2024-01-12',
            description: 'Follow-up Visit - Dr. Emily Davis',
            amount: 120.00,
            status: 'Pending',
            dueDate: '2024-02-12',
            paymentMethod: 'Pending'
        },
        {
            id: 4,
            date: '2024-01-10',
            description: 'Prescription - Medication',
            amount: 25.00,
            status: 'Overdue',
            dueDate: '2024-01-25',
            paymentMethod: 'Pending'
        }
    ];

    const getStatusColor = (status: string) => {
        switch (status) {
            case 'Paid': return 'default';
            case 'Pending': return 'secondary';
            case 'Overdue': return 'destructive';
            default: return 'secondary';
        }
    };

    return (
        <AppLayout>
            <Head title="Billing" />
            
            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Billing</h1>
                        <p className="text-muted-foreground">
                            View your bills, payments, and insurance information
                        </p>
                    </div>
                    <Button>
                        <Download className="mr-2 h-4 w-4" />
                        Download Statement
                    </Button>
                </div>

                {/* Billing Summary */}
                <div className="grid gap-4 md:grid-cols-4">
                    <Card>
                        <CardContent className="p-6">
                            <div className="flex items-center">
                                <DollarSign className="h-8 w-8 text-green-600" />
                                <div className="ml-4">
                                    <p className="text-sm font-medium text-muted-foreground">Total Paid</p>
                                    <p className="text-2xl font-bold">$235.50</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-6">
                            <div className="flex items-center">
                                <Clock className="h-8 w-8 text-yellow-600" />
                                <div className="ml-4">
                                    <p className="text-sm font-medium text-muted-foreground">Pending</p>
                                    <p className="text-2xl font-bold">$120.00</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-6">
                            <div className="flex items-center">
                                <AlertCircle className="h-8 w-8 text-red-600" />
                                <div className="ml-4">
                                    <p className="text-sm font-medium text-muted-foreground">Overdue</p>
                                    <p className="text-2xl font-bold">$25.00</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-6">
                            <div className="flex items-center">
                                <CreditCard className="h-8 w-8 text-blue-600" />
                                <div className="ml-4">
                                    <p className="text-sm font-medium text-muted-foreground">Total Balance</p>
                                    <p className="text-2xl font-bold">$145.00</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Billing History</CardTitle>
                        <CardDescription>
                            Your complete billing history and payment records
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-4">
                            {bills.map((bill) => (
                                <div key={bill.id} className="flex items-center justify-between p-4 border rounded-lg">
                                    <div className="flex items-center space-x-4">
                                        <div className="flex-shrink-0">
                                            <FileText className="h-5 w-5 text-blue-600" />
                                        </div>
                                        <div>
                                            <h3 className="font-medium">{bill.description}</h3>
                                            <div className="flex items-center space-x-4 mt-1">
                                                <span className="text-xs text-muted-foreground flex items-center">
                                                    <Calendar className="mr-1 h-3 w-3" />
                                                    {bill.date}
                                                </span>
                                                <span className="text-xs text-muted-foreground">
                                                    Due: {bill.dueDate}
                                                </span>
                                                <span className="text-xs text-muted-foreground">
                                                    {bill.paymentMethod}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div className="flex items-center space-x-4">
                                        <div className="text-right">
                                            <div className="text-lg font-bold">${bill.amount.toFixed(2)}</div>
                                            <Badge variant={getStatusColor(bill.status)} className="mt-1">
                                                {bill.status}
                                            </Badge>
                                        </div>
                                        <div className="flex items-center space-x-2">
                                            <Button variant="outline" size="sm">
                                                <Eye className="h-4 w-4" />
                                            </Button>
                                            <Button variant="outline" size="sm">
                                                <Download className="h-4 w-4" />
                                            </Button>
                                            {bill.status !== 'Paid' && (
                                                <Button size="sm">
                                                    Pay Now
                                                </Button>
                                            )}
                                        </div>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </CardContent>
                </Card>

                {/* Payment Methods */}
                <Card>
                    <CardHeader>
                        <CardTitle>Payment Methods</CardTitle>
                        <CardDescription>
                            Manage your payment methods and preferences
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-4">
                            <div className="flex items-center justify-between p-4 border rounded-lg">
                                <div className="flex items-center space-x-4">
                                    <CreditCard className="h-5 w-5 text-blue-600" />
                                    <div>
                                        <h3 className="font-medium">Visa ending in 1234</h3>
                                        <p className="text-sm text-muted-foreground">Expires 12/25</p>
                                    </div>
                                </div>
                                <div className="flex items-center space-x-2">
                                    <Badge variant="default">Primary</Badge>
                                    <Button variant="outline" size="sm">Edit</Button>
                                </div>
                            </div>
                            <div className="flex items-center justify-between p-4 border rounded-lg">
                                <div className="flex items-center space-x-4">
                                    <CreditCard className="h-5 w-5 text-green-600" />
                                    <div>
                                        <h3 className="font-medium">Mastercard ending in 5678</h3>
                                        <p className="text-sm text-muted-foreground">Expires 08/26</p>
                                    </div>
                                </div>
                                <div className="flex items-center space-x-2">
                                    <Button variant="outline" size="sm">Edit</Button>
                                </div>
                            </div>
                        </div>
                        <div className="mt-4">
                            <Button variant="outline">
                                <CreditCard className="mr-2 h-4 w-4" />
                                Add Payment Method
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
