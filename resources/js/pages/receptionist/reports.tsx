import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { 
    BarChart3, 
    Download, 
    Calendar,
    Users,
    Clock,
    FileText,
    TrendingUp
} from 'lucide-react';

export default function ReceptionistReports() {
    const reports = [
        {
            id: 1,
            title: 'Daily Appointment Report',
            description: 'Summary of appointments for today',
            type: 'Appointment',
            lastGenerated: '2024-01-15',
            status: 'Ready'
        },
        {
            id: 2,
            title: 'Patient Check-in Report',
            description: 'Patient check-in and check-out statistics',
            type: 'Patient',
            lastGenerated: '2024-01-14',
            status: 'Ready'
        },
        {
            id: 3,
            title: 'Queue Management Report',
            description: 'Patient queue and waiting time analysis',
            type: 'Queue',
            lastGenerated: '2024-01-13',
            status: 'Ready'
        },
        {
            id: 4,
            title: 'Receptionist Activity Report',
            description: 'Daily receptionist activities and tasks',
            type: 'Activity',
            lastGenerated: '2024-01-12',
            status: 'Generating'
        }
    ];

    const quickStats = [
        {
            title: 'Today\'s Appointments',
            value: '23',
            change: '+3',
            icon: Calendar,
            color: 'text-blue-600'
        },
        {
            title: 'Checked In',
            value: '18',
            change: '+2',
            icon: Users,
            color: 'text-green-600'
        },
        {
            title: 'Average Wait Time',
            value: '12 min',
            change: '-2 min',
            icon: Clock,
            color: 'text-purple-600'
        },
        {
            title: 'Completed Today',
            value: '15',
            change: '+5',
            icon: FileText,
            color: 'text-orange-600'
        }
    ];

    return (
        <AppLayout>
            <Head title="Reports" />
            
            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Reports</h1>
                        <p className="text-muted-foreground">
                            Generate and view receptionist reports
                        </p>
                    </div>
                    <Button>
                        <BarChart3 className="mr-2 h-4 w-4" />
                        Generate Report
                    </Button>
                </div>

                {/* Quick Stats */}
                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    {quickStats.map((stat) => (
                        <Card key={stat.title}>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">
                                    {stat.title}
                                </CardTitle>
                                <stat.icon className={`h-4 w-4 ${stat.color}`} />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold">{stat.value}</div>
                                <p className="text-xs text-muted-foreground">
                                    {stat.change} from yesterday
                                </p>
                            </CardContent>
                        </Card>
                    ))}
                </div>

                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    {/* Available Reports */}
                    <Card className="col-span-2">
                        <CardHeader>
                            <CardTitle>Available Reports</CardTitle>
                            <CardDescription>
                                Generate and download receptionist reports
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                {reports.map((report) => (
                                    <div key={report.id} className="flex items-center justify-between p-4 border rounded-lg">
                                        <div className="flex items-center space-x-4">
                                            <div className="flex-shrink-0">
                                                {report.type === 'Appointment' && <Calendar className="h-5 w-5 text-blue-600" />}
                                                {report.type === 'Patient' && <Users className="h-5 w-5 text-green-600" />}
                                                {report.type === 'Queue' && <Clock className="h-5 w-5 text-purple-600" />}
                                                {report.type === 'Activity' && <FileText className="h-5 w-5 text-orange-600" />}
                                            </div>
                                            <div>
                                                <h3 className="font-medium">{report.title}</h3>
                                                <p className="text-sm text-muted-foreground">{report.description}</p>
                                                <p className="text-xs text-muted-foreground">
                                                    Last generated: {report.lastGenerated}
                                                </p>
                                            </div>
                                        </div>
                                        <div className="flex items-center space-x-2">
                                            <Badge variant={report.status === 'Ready' ? 'default' : 'secondary'}>
                                                {report.status}
                                            </Badge>
                                            <Button variant="outline" size="sm">
                                                <Download className="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </CardContent>
                    </Card>

                    {/* Quick Actions */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Quick Actions</CardTitle>
                            <CardDescription>
                                Common report tasks
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-2">
                            <Button className="w-full justify-start" variant="outline">
                                <Calendar className="mr-2 h-4 w-4" />
                                Daily Appointments
                            </Button>
                            <Button className="w-full justify-start" variant="outline">
                                <Users className="mr-2 h-4 w-4" />
                                Patient Check-ins
                            </Button>
                            <Button className="w-full justify-start" variant="outline">
                                <Clock className="mr-2 h-4 w-4" />
                                Queue Analysis
                            </Button>
                            <Button className="w-full justify-start" variant="outline">
                                <FileText className="mr-2 h-4 w-4" />
                                Activity Summary
                            </Button>
                            <Button className="w-full justify-start" variant="outline">
                                <TrendingUp className="mr-2 h-4 w-4" />
                                Performance Metrics
                            </Button>
                            <Button className="w-full justify-start" variant="outline">
                                <BarChart3 className="mr-2 h-4 w-4" />
                                Custom Report
                            </Button>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AppLayout>
    );
}
