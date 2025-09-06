import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { 
    BarChart3, 
    TrendingUp, 
    TrendingDown,
    Users,
    Calendar,
    MessageSquare,
    Package,
    Target
} from 'lucide-react';

export default function MedrepAnalytics() {
    const analytics = [
        {
            title: 'Total Interactions',
            value: '156',
            change: '+12.5%',
            trend: 'up',
            icon: MessageSquare,
            color: 'text-blue-600'
        },
        {
            title: 'Active Doctors',
            value: '24',
            change: '+2',
            trend: 'up',
            icon: Users,
            color: 'text-green-600'
        },
        {
            title: 'Meetings Scheduled',
            value: '45',
            change: '+8.7%',
            trend: 'up',
            icon: Calendar,
            color: 'text-purple-600'
        },
        {
            title: 'Product Samples',
            value: '89',
            change: '-3.2%',
            trend: 'down',
            icon: Package,
            color: 'text-orange-600'
        }
    ];

    const topPerformers = [
        {
            name: 'Dr. Sarah Johnson',
            specialty: 'Cardiology',
            interactions: 25,
            rating: 4.9,
            samples: 12
        },
        {
            name: 'Dr. Emily Davis',
            specialty: 'Dermatology',
            interactions: 22,
            rating: 4.8,
            samples: 15
        },
        {
            name: 'Dr. Michael Brown',
            specialty: 'Pediatrics',
            interactions: 18,
            rating: 4.7,
            samples: 8
        }
    ];

    return (
        <AppLayout>
            <Head title="Analytics" />
            
            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Analytics Dashboard</h1>
                        <p className="text-muted-foreground">
                            Comprehensive insights into your medical representative performance
                        </p>
                    </div>
                    <div className="flex space-x-2">
                        <Button variant="outline">
                            <BarChart3 className="mr-2 h-4 w-4" />
                            Export Report
                        </Button>
                        <Button>
                            <Target className="mr-2 h-4 w-4" />
                            View Details
                        </Button>
                    </div>
                </div>

                {/* Key Metrics */}
                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    {analytics.map((metric) => (
                        <Card key={metric.title}>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">
                                    {metric.title}
                                </CardTitle>
                                <metric.icon className={`h-4 w-4 ${metric.color}`} />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold">{metric.value}</div>
                                <div className="flex items-center text-xs text-muted-foreground">
                                    {metric.trend === 'up' ? (
                                        <TrendingUp className="mr-1 h-3 w-3 text-green-600" />
                                    ) : (
                                        <TrendingDown className="mr-1 h-3 w-3 text-red-600" />
                                    )}
                                    {metric.change} from last month
                                </div>
                            </CardContent>
                        </Card>
                    ))}
                </div>

                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    {/* Top Performing Doctors */}
                    <Card className="col-span-2">
                        <CardHeader>
                            <CardTitle>Top Performing Doctors</CardTitle>
                            <CardDescription>
                                Doctors with highest interaction rates and engagement
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                {topPerformers.map((doctor, index) => (
                                    <div key={doctor.name} className="flex items-center justify-between p-4 border rounded-lg">
                                        <div className="flex items-center space-x-4">
                                            <div className="flex-shrink-0 w-8 h-8 bg-primary text-primary-foreground rounded-full flex items-center justify-center text-sm font-medium">
                                                {index + 1}
                                            </div>
                                            <div>
                                                <h3 className="font-medium">{doctor.name}</h3>
                                                <p className="text-sm text-muted-foreground">{doctor.specialty}</p>
                                            </div>
                                        </div>
                                        <div className="flex items-center space-x-4 text-sm">
                                            <div className="text-center">
                                                <div className="font-medium">{doctor.interactions}</div>
                                                <div className="text-muted-foreground">Interactions</div>
                                            </div>
                                            <div className="text-center">
                                                <div className="font-medium">{doctor.rating}</div>
                                                <div className="text-muted-foreground">Rating</div>
                                            </div>
                                            <div className="text-center">
                                                <div className="font-medium">{doctor.samples}</div>
                                                <div className="text-muted-foreground">Samples</div>
                                            </div>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </CardContent>
                    </Card>

                    {/* Quick Insights */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Quick Insights</CardTitle>
                            <CardDescription>
                                Key performance indicators
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="space-y-2">
                                <div className="flex items-center justify-between">
                                    <span className="text-sm">Meeting Success Rate</span>
                                    <Badge variant="default">87%</Badge>
                                </div>
                                <div className="flex items-center justify-between">
                                    <span className="text-sm">Sample Conversion</span>
                                    <Badge variant="default">23%</Badge>
                                </div>
                                <div className="flex items-center justify-between">
                                    <span className="text-sm">Average Response Time</span>
                                    <Badge variant="default">2.3 days</Badge>
                                </div>
                                <div className="flex items-center justify-between">
                                    <span className="text-sm">Territory Coverage</span>
                                    <Badge variant="default">94%</Badge>
                                </div>
                            </div>
                            
                            <div className="pt-4 border-t">
                                <h4 className="font-medium mb-2">Trends</h4>
                                <div className="space-y-2 text-sm">
                                    <div className="flex items-center justify-between">
                                        <span>Interaction Growth</span>
                                        <div className="flex items-center text-green-600">
                                            <TrendingUp className="mr-1 h-3 w-3" />
                                            +12.5%
                                        </div>
                                    </div>
                                    <div className="flex items-center justify-between">
                                        <span>Meeting Growth</span>
                                        <div className="flex items-center text-green-600">
                                            <TrendingUp className="mr-1 h-3 w-3" />
                                            +8.7%
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AppLayout>
    );
}
