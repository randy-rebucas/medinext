import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { 
    Bell, 
    Calendar,
    Stethoscope,
    FileText,
    CreditCard,
    TestTube,
    Pill,
    CheckCircle,
    AlertCircle,
    Info
} from 'lucide-react';

export default function PatientNotifications() {
    const notifications = [
        {
            id: 1,
            type: 'appointment',
            title: 'Appointment Reminder',
            message: 'Your appointment with Dr. Sarah Johnson is scheduled for tomorrow at 09:00 AM.',
            date: '2024-01-19',
            time: '10:30 AM',
            status: 'unread',
            priority: 'high'
        },
        {
            id: 2,
            type: 'prescription',
            title: 'Prescription Refill Reminder',
            message: 'Your Lisinopril prescription will need a refill in 5 days.',
            date: '2024-01-18',
            time: '02:15 PM',
            status: 'unread',
            priority: 'medium'
        },
        {
            id: 3,
            type: 'lab',
            title: 'Lab Results Available',
            message: 'Your lab results from January 15th are now available for review.',
            date: '2024-01-17',
            time: '09:45 AM',
            status: 'read',
            priority: 'medium'
        },
        {
            id: 4,
            type: 'billing',
            title: 'Payment Reminder',
            message: 'Your payment of $120.00 is due on February 12th.',
            date: '2024-01-16',
            time: '11:20 AM',
            status: 'read',
            priority: 'high'
        },
        {
            id: 5,
            type: 'appointment',
            title: 'Appointment Confirmed',
            message: 'Your appointment with Dr. Michael Brown has been confirmed for January 20th.',
            date: '2024-01-15',
            time: '03:30 PM',
            status: 'read',
            priority: 'low'
        }
    ];

    const getNotificationIcon = (type: string) => {
        switch (type) {
            case 'appointment': return <Calendar className="h-5 w-5 text-blue-600" />;
            case 'prescription': return <Pill className="h-5 w-5 text-purple-600" />;
            case 'lab': return <TestTube className="h-5 w-5 text-green-600" />;
            case 'billing': return <CreditCard className="h-5 w-5 text-orange-600" />;
            default: return <Bell className="h-5 w-5 text-gray-600" />;
        }
    };

    const getPriorityColor = (priority: string) => {
        switch (priority) {
            case 'high': return 'destructive';
            case 'medium': return 'default';
            case 'low': return 'secondary';
            default: return 'secondary';
        }
    };

    return (
        <AppLayout>
            <Head title="Notifications" />
            
            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Notifications</h1>
                        <p className="text-muted-foreground">
                            Stay updated with your healthcare information
                        </p>
                    </div>
                    <Button>
                        <Bell className="mr-2 h-4 w-4" />
                        Mark All Read
                    </Button>
                </div>

                {/* Notification Summary */}
                <div className="grid gap-4 md:grid-cols-4">
                    <Card>
                        <CardContent className="p-6">
                            <div className="flex items-center">
                                <Bell className="h-8 w-8 text-blue-600" />
                                <div className="ml-4">
                                    <p className="text-sm font-medium text-muted-foreground">Total</p>
                                    <p className="text-2xl font-bold">5</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-6">
                            <div className="flex items-center">
                                <AlertCircle className="h-8 w-8 text-red-600" />
                                <div className="ml-4">
                                    <p className="text-sm font-medium text-muted-foreground">Unread</p>
                                    <p className="text-2xl font-bold">2</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-6">
                            <div className="flex items-center">
                                <CheckCircle className="h-8 w-8 text-green-600" />
                                <div className="ml-4">
                                    <p className="text-sm font-medium text-muted-foreground">Read</p>
                                    <p className="text-2xl font-bold">3</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-6">
                            <div className="flex items-center">
                                <Info className="h-8 w-8 text-yellow-600" />
                                <div className="ml-4">
                                    <p className="text-sm font-medium text-muted-foreground">High Priority</p>
                                    <p className="text-2xl font-bold">2</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Recent Notifications</CardTitle>
                        <CardDescription>
                            Your latest healthcare notifications and updates
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-4">
                            {notifications.map((notification) => (
                                <div key={notification.id} className={`flex items-center justify-between p-4 border rounded-lg ${notification.status === 'unread' ? 'bg-blue-50 dark:bg-blue-900/20' : ''}`}>
                                    <div className="flex items-center space-x-4">
                                        <div className="flex-shrink-0">
                                            {getNotificationIcon(notification.type)}
                                        </div>
                                        <div>
                                            <h3 className="font-medium">{notification.title}</h3>
                                            <p className="text-sm text-muted-foreground">{notification.message}</p>
                                            <div className="flex items-center space-x-4 mt-1">
                                                <span className="text-xs text-muted-foreground">
                                                    {notification.date}
                                                </span>
                                                <span className="text-xs text-muted-foreground">
                                                    {notification.time}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div className="flex items-center space-x-2">
                                        <Badge variant={getPriorityColor(notification.priority)}>
                                            {notification.priority}
                                        </Badge>
                                        {notification.status === 'unread' && (
                                            <div className="w-2 h-2 bg-blue-600 rounded-full"></div>
                                        )}
                                    </div>
                                </div>
                            ))}
                        </div>
                    </CardContent>
                </Card>

                {/* Notification Settings */}
                <Card>
                    <CardHeader>
                        <CardTitle>Notification Settings</CardTitle>
                        <CardDescription>
                            Manage your notification preferences
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-4">
                            <div className="flex items-center justify-between">
                                <div>
                                    <h3 className="font-medium">Appointment Reminders</h3>
                                    <p className="text-sm text-muted-foreground">Get reminded about upcoming appointments</p>
                                </div>
                                <Button variant="outline" size="sm">Enabled</Button>
                            </div>
                            <div className="flex items-center justify-between">
                                <div>
                                    <h3 className="font-medium">Prescription Reminders</h3>
                                    <p className="text-sm text-muted-foreground">Get notified about prescription refills</p>
                                </div>
                                <Button variant="outline" size="sm">Enabled</Button>
                            </div>
                            <div className="flex items-center justify-between">
                                <div>
                                    <h3 className="font-medium">Lab Results</h3>
                                    <p className="text-sm text-muted-foreground">Get notified when lab results are available</p>
                                </div>
                                <Button variant="outline" size="sm">Enabled</Button>
                            </div>
                            <div className="flex items-center justify-between">
                                <div>
                                    <h3 className="font-medium">Billing Notifications</h3>
                                    <p className="text-sm text-muted-foreground">Get notified about bills and payments</p>
                                </div>
                                <Button variant="outline" size="sm">Enabled</Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
