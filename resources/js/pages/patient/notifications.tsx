import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    Bell,
    Calendar,
    CreditCard,
    TestTube,
    Pill,
    CheckCircle,
    AlertCircle,
    Info,
    TrendingUp,
    Shield,
    User
} from 'lucide-react';
import { patientDashboard, patientNotifications } from '@/routes';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Patient Portal',
        href: patientDashboard(),
    },
    {
        title: 'Notifications',
        href: patientNotifications(),
    },
];

interface PatientNotificationsProps {
    user?: {
        id: number;
        name: string;
        email: string;
        patient_id: string;
        sex: string;
    };
    permissions?: string[];
}

export default function PatientNotifications({ user, permissions }: PatientNotificationsProps) {
    const hasPermission = (permission: string) => {
        return permissions?.includes(permission) ?? true;
    };
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
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Notifications - Medinext">
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
                                <h1 className="text-3xl font-bold tracking-tight">Notifications</h1>
                                <p className="mt-2 text-rose-100">
                                    Stay updated with your healthcare information
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
                                {hasPermission('notifications.mark_all_read') && (
                                    <Button className="bg-white/20 hover:bg-white/30 text-white border-white/30 hover:border-white/40 transition-all duration-200">
                                        <Bell className="mr-2 h-4 w-4" />
                                        Mark All Read
                                    </Button>
                                )}
                            </div>
                        </div>
                        {/* Decorative elements */}
                        <div className="absolute -top-4 -right-4 w-24 h-24 bg-white/10 rounded-full"></div>
                        <div className="absolute -bottom-2 -left-2 w-16 h-16 bg-white/5 rounded-full"></div>
                    </div>

                    {/* Notification Summary */}
                    <div className="grid gap-6 md:grid-cols-4">
                        <Card className="relative overflow-hidden border-0 shadow-lg hover:shadow-xl transition-all duration-300 bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20">
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium text-slate-700 dark:text-slate-300">Total</CardTitle>
                                <div className="p-2 bg-blue-500 rounded-lg">
                                    <Bell className="h-4 w-4 text-white" />
                                </div>
                            </CardHeader>
                            <CardContent>
                                <div className="text-3xl font-bold text-slate-900 dark:text-white">5</div>
                                <div className="flex items-center mt-2">
                                    <TrendingUp className="h-3 w-3 text-blue-500 mr-1" />
                                    <p className="text-xs text-slate-600 dark:text-slate-400">
                                        All notifications
                                    </p>
                                </div>
                            </CardContent>
                        </Card>
                        <Card className="relative overflow-hidden border-0 shadow-lg hover:shadow-xl transition-all duration-300 bg-gradient-to-br from-red-50 to-red-100 dark:from-red-900/20 dark:to-red-800/20">
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium text-slate-700 dark:text-slate-300">Unread</CardTitle>
                                <div className="p-2 bg-red-500 rounded-lg">
                                    <AlertCircle className="h-4 w-4 text-white" />
                                </div>
                            </CardHeader>
                            <CardContent>
                                <div className="text-3xl font-bold text-slate-900 dark:text-white">2</div>
                                <div className="flex items-center mt-2">
                                    <AlertCircle className="h-3 w-3 text-red-500 mr-1" />
                                    <p className="text-xs text-slate-600 dark:text-slate-400">
                                        New notifications
                                    </p>
                                </div>
                            </CardContent>
                        </Card>
                        <Card className="relative overflow-hidden border-0 shadow-lg hover:shadow-xl transition-all duration-300 bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20">
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium text-slate-700 dark:text-slate-300">Read</CardTitle>
                                <div className="p-2 bg-green-500 rounded-lg">
                                    <CheckCircle className="h-4 w-4 text-white" />
                                </div>
                            </CardHeader>
                            <CardContent>
                                <div className="text-3xl font-bold text-slate-900 dark:text-white">3</div>
                                <div className="flex items-center mt-2">
                                    <CheckCircle className="h-3 w-3 text-green-500 mr-1" />
                                    <p className="text-xs text-slate-600 dark:text-slate-400">
                                        Viewed notifications
                                    </p>
                                </div>
                            </CardContent>
                        </Card>
                        <Card className="relative overflow-hidden border-0 shadow-lg hover:shadow-xl transition-all duration-300 bg-gradient-to-br from-yellow-50 to-yellow-100 dark:from-yellow-900/20 dark:to-yellow-800/20">
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium text-slate-700 dark:text-slate-300">High Priority</CardTitle>
                                <div className="p-2 bg-yellow-500 rounded-lg">
                                    <Info className="h-4 w-4 text-white" />
                                </div>
                            </CardHeader>
                            <CardContent>
                                <div className="text-3xl font-bold text-slate-900 dark:text-white">2</div>
                                <div className="flex items-center mt-2">
                                    <Info className="h-3 w-3 text-yellow-500 mr-1" />
                                    <p className="text-xs text-slate-600 dark:text-slate-400">
                                        Urgent notifications
                                    </p>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    {/* Recent Notifications */}
                    <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                        <CardHeader>
                            <CardTitle className="text-lg font-semibold text-slate-900 dark:text-white">Recent Notifications</CardTitle>
                            <CardDescription className="text-slate-600 dark:text-slate-300">
                                Your latest healthcare notifications and updates
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                {notifications.map((notification) => (
                                    <div key={notification.id} className={`flex items-center justify-between p-4 border border-slate-200 dark:border-slate-700 rounded-lg hover:shadow-md transition-all duration-200 ${notification.status === 'unread' ? 'bg-blue-50 dark:bg-blue-900/20' : 'bg-slate-50 dark:bg-slate-700/50'}`}>
                                        <div className="flex items-center space-x-4">
                                            <div className="flex-shrink-0">
                                                <div className="p-2 bg-rose-100 dark:bg-rose-900/20 rounded-full">
                                                    {getNotificationIcon(notification.type)}
                                                </div>
                                            </div>
                                            <div>
                                                <h3 className="font-medium text-slate-900 dark:text-white">{notification.title}</h3>
                                                <p className="text-sm text-slate-600 dark:text-slate-400">{notification.message}</p>
                                                <div className="flex items-center space-x-4 mt-1">
                                                    <span className="text-xs text-slate-600 dark:text-slate-400">
                                                        {notification.date}
                                                    </span>
                                                    <span className="text-xs text-slate-600 dark:text-slate-400">
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
                                                <div className="w-2 h-2 bg-rose-600 rounded-full"></div>
                                            )}
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </CardContent>
                    </Card>

                    {/* Notification Settings */}
                    <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                        <CardHeader>
                            <CardTitle className="text-lg font-semibold text-slate-900 dark:text-white">Notification Settings</CardTitle>
                            <CardDescription className="text-slate-600 dark:text-slate-300">
                                Manage your notification preferences
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                <div className="flex items-center justify-between p-4 border border-slate-200 dark:border-slate-700 rounded-lg hover:shadow-md transition-all duration-200 bg-slate-50 dark:bg-slate-700/50">
                                    <div>
                                        <h3 className="font-medium text-slate-900 dark:text-white">Appointment Reminders</h3>
                                        <p className="text-sm text-slate-600 dark:text-slate-400">Get reminded about upcoming appointments</p>
                                    </div>
                                    <Button variant="outline" size="sm" className="border-slate-200 dark:border-slate-700 hover:bg-rose-50 dark:hover:bg-rose-900/20 hover:border-rose-300 dark:hover:border-rose-600 transition-all duration-200">Enabled</Button>
                                </div>
                                <div className="flex items-center justify-between p-4 border border-slate-200 dark:border-slate-700 rounded-lg hover:shadow-md transition-all duration-200 bg-slate-50 dark:bg-slate-700/50">
                                    <div>
                                        <h3 className="font-medium text-slate-900 dark:text-white">Prescription Reminders</h3>
                                        <p className="text-sm text-slate-600 dark:text-slate-400">Get notified about prescription refills</p>
                                    </div>
                                    <Button variant="outline" size="sm" className="border-slate-200 dark:border-slate-700 hover:bg-rose-50 dark:hover:bg-rose-900/20 hover:border-rose-300 dark:hover:border-rose-600 transition-all duration-200">Enabled</Button>
                                </div>
                                <div className="flex items-center justify-between p-4 border border-slate-200 dark:border-slate-700 rounded-lg hover:shadow-md transition-all duration-200 bg-slate-50 dark:bg-slate-700/50">
                                    <div>
                                        <h3 className="font-medium text-slate-900 dark:text-white">Lab Results</h3>
                                        <p className="text-sm text-slate-600 dark:text-slate-400">Get notified when lab results are available</p>
                                    </div>
                                    <Button variant="outline" size="sm" className="border-slate-200 dark:border-slate-700 hover:bg-rose-50 dark:hover:bg-rose-900/20 hover:border-rose-300 dark:hover:border-rose-600 transition-all duration-200">Enabled</Button>
                                </div>
                                <div className="flex items-center justify-between p-4 border border-slate-200 dark:border-slate-700 rounded-lg hover:shadow-md transition-all duration-200 bg-slate-50 dark:bg-slate-700/50">
                                    <div>
                                        <h3 className="font-medium text-slate-900 dark:text-white">Billing Notifications</h3>
                                        <p className="text-sm text-slate-600 dark:text-slate-400">Get notified about bills and payments</p>
                                    </div>
                                    <Button variant="outline" size="sm" className="border-slate-200 dark:border-slate-700 hover:bg-rose-50 dark:hover:bg-rose-900/20 hover:border-rose-300 dark:hover:border-rose-600 transition-all duration-200">Enabled</Button>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AppLayout>
    );
}
