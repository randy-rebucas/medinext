import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { 
    TrendingUp, 
    TrendingDown,
    Target,
    Users,
    Calendar,
    MessageSquare,
    Package,
    Award,
    CheckCircle
} from 'lucide-react';

export default function PerformanceMetrics() {
    const metrics = [
        {
            title: 'Total Interactions',
            value: '156',
            change: '+12.5%',
            trend: 'up',
            icon: MessageSquare,
            color: 'text-blue-600'
        },
        {
            title: 'Meeting Success Rate',
            value: '87%',
            change: '+5.2%',
            trend: 'up',
            icon: Calendar,
            color: 'text-green-600'
        },
        {
            title: 'Sample Conversion',
            value: '23%',
            change: '+3.1%',
            trend: 'up',
            icon: Package,
            color: 'text-purple-600'
        },
        {
            title: 'Territory Coverage',
            value: '94%',
            change: '+2.8%',
            trend: 'up',
            icon: Target,
            color: 'text-orange-600'
        }
    ];

    const goals = [
        {
            id: 1,
            title: 'Monthly Interactions',
            target: 200,
            current: 156,
            progress: 78,
            status: 'On Track'
        },
        {
            id: 2,
            title: 'New Doctor Contacts',
            target: 10,
            current: 7,
            progress: 70,
            status: 'On Track'
        },
        {
            id: 3,
            title: 'Sample Deliveries',
            target: 50,
            current: 35,
            progress: 70,
            status: 'On Track'
        },
        {
            id: 4,
            title: 'Meeting Completion',
            target: 40,
            current: 32,
            progress: 80,
            status: 'On Track'
        }
    ];

    const achievements = [
        {
            id: 1,
            title: 'Top Performer',
            description: 'Highest interaction rate this month',
            date: '2024-01-15',
            icon: Award,
            color: 'text-yellow-600'
        },
        {
            id: 2,
            title: 'Territory Master',
            description: '100% territory coverage achieved',
            date: '2024-01-10',
            icon: Target,
            color: 'text-blue-600'
        },
        {
            id: 3,
            title: 'Sample Champion',
            description: 'Delivered 50+ samples this quarter',
            date: '2024-01-05',
            icon: Package,
            color: 'text-green-600'
        }
    ];

    return (
        <AppLayout>
            <Head title="Performance Metrics" />
            
            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Performance Metrics</h1>
                        <p className="text-muted-foreground">
                            Track your performance and achieve your goals
                        </p>
                    </div>
                    <Button>
                        <TrendingUp className="mr-2 h-4 w-4" />
                        View Report
                    </Button>
                </div>

                {/* Key Metrics */}
                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    {metrics.map((metric) => (
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
                    {/* Goals Progress */}
                    <Card className="col-span-2">
                        <CardHeader>
                            <CardTitle>Goals Progress</CardTitle>
                            <CardDescription>
                                Track your progress towards monthly and quarterly goals
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                {goals.map((goal) => (
                                    <div key={goal.id} className="space-y-2">
                                        <div className="flex items-center justify-between">
                                            <h3 className="font-medium">{goal.title}</h3>
                                            <Badge variant="default">{goal.status}</Badge>
                                        </div>
                                        <div className="flex items-center space-x-2">
                                            <div className="flex-1 bg-gray-200 rounded-full h-2">
                                                <div 
                                                    className="bg-primary h-2 rounded-full" 
                                                    style={{ width: `${goal.progress}%` }}
                                                ></div>
                                            </div>
                                            <span className="text-sm text-muted-foreground">
                                                {goal.current}/{goal.target}
                                            </span>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </CardContent>
                    </Card>

                    {/* Achievements */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Achievements</CardTitle>
                            <CardDescription>
                                Your recent accomplishments and milestones
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                {achievements.map((achievement) => (
                                    <div key={achievement.id} className="flex items-center space-x-3 p-3 border rounded-lg">
                                        <div className="flex-shrink-0">
                                            <achievement.icon className={`h-5 w-5 ${achievement.color}`} />
                                        </div>
                                        <div>
                                            <h3 className="font-medium text-sm">{achievement.title}</h3>
                                            <p className="text-xs text-muted-foreground">{achievement.description}</p>
                                            <p className="text-xs text-muted-foreground">{achievement.date}</p>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Performance Summary */}
                <Card>
                    <CardHeader>
                        <CardTitle>Performance Summary</CardTitle>
                        <CardDescription>
                            Overall performance metrics and insights
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="grid gap-4 md:grid-cols-3">
                            <div className="text-center p-4 border rounded-lg">
                                <div className="text-2xl font-bold text-green-600">A+</div>
                                <div className="text-sm text-muted-foreground">Overall Rating</div>
                            </div>
                            <div className="text-center p-4 border rounded-lg">
                                <div className="text-2xl font-bold text-blue-600">85%</div>
                                <div className="text-sm text-muted-foreground">Goal Achievement</div>
                            </div>
                            <div className="text-center p-4 border rounded-lg">
                                <div className="text-2xl font-bold text-purple-600">Top 10%</div>
                                <div className="text-sm text-muted-foreground">Team Ranking</div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
