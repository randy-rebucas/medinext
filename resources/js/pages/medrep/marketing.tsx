import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { 
    FileText, 
    Download,
    Eye,
    Calendar,
    Users,
    Target,
    TrendingUp,
    Package
} from 'lucide-react';

export default function MarketingMaterials() {
    const materials = [
        {
            id: 1,
            name: 'CardioMax Product Brochure',
            type: 'Brochure',
            category: 'Cardiology',
            lastUpdated: '2024-01-15',
            downloads: 45,
            status: 'Active'
        },
        {
            id: 2,
            name: 'DermaCare Clinical Study',
            type: 'Clinical Study',
            category: 'Dermatology',
            lastUpdated: '2024-01-14',
            downloads: 32,
            status: 'Active'
        },
        {
            id: 3,
            name: 'NeuroCalm Presentation',
            type: 'Presentation',
            category: 'Neurology',
            lastUpdated: '2024-01-13',
            downloads: 28,
            status: 'Active'
        },
        {
            id: 4,
            name: 'OrthoFlex Sample Kit',
            type: 'Sample Kit',
            category: 'Orthopedics',
            lastUpdated: '2024-01-12',
            downloads: 15,
            status: 'Active'
        }
    ];

    const campaigns = [
        {
            id: 1,
            name: 'Q1 Cardiology Campaign',
            target: 'Cardiologists',
            startDate: '2024-01-01',
            endDate: '2024-03-31',
            status: 'Active',
            reach: 150,
            engagement: 23
        },
        {
            id: 2,
            name: 'Dermatology Awareness',
            target: 'Dermatologists',
            startDate: '2024-01-15',
            endDate: '2024-02-15',
            status: 'Active',
            reach: 89,
            engagement: 18
        },
        {
            id: 3,
            name: 'Pediatric Product Launch',
            target: 'Pediatricians',
            startDate: '2024-02-01',
            endDate: '2024-04-30',
            status: 'Scheduled',
            reach: 0,
            engagement: 0
        }
    ];

    return (
        <AppLayout>
            <Head title="Marketing Materials" />
            
            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Marketing Materials</h1>
                        <p className="text-muted-foreground">
                            Access and manage your marketing materials and campaigns
                        </p>
                    </div>
                    <Button>
                        <FileText className="mr-2 h-4 w-4" />
                        Add Material
                    </Button>
                </div>

                {/* Marketing Overview */}
                <div className="grid gap-4 md:grid-cols-4">
                    <Card>
                        <CardContent className="p-6">
                            <div className="flex items-center">
                                <FileText className="h-8 w-8 text-blue-600" />
                                <div className="ml-4">
                                    <p className="text-sm font-medium text-muted-foreground">Total Materials</p>
                                    <p className="text-2xl font-bold">24</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-6">
                            <div className="flex items-center">
                                <Download className="h-8 w-8 text-green-600" />
                                <div className="ml-4">
                                    <p className="text-sm font-medium text-muted-foreground">Downloads</p>
                                    <p className="text-2xl font-bold">156</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-6">
                            <div className="flex items-center">
                                <Target className="h-8 w-8 text-purple-600" />
                                <div className="ml-4">
                                    <p className="text-sm font-medium text-muted-foreground">Active Campaigns</p>
                                    <p className="text-2xl font-bold">2</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-6">
                            <div className="flex items-center">
                                <TrendingUp className="h-8 w-8 text-orange-600" />
                                <div className="ml-4">
                                    <p className="text-sm font-medium text-muted-foreground">Engagement</p>
                                    <p className="text-2xl font-bold">41%</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Marketing Materials */}
                <Card>
                    <CardHeader>
                        <CardTitle>Marketing Materials</CardTitle>
                        <CardDescription>
                            Access and download your marketing materials
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-4">
                            {materials.map((material) => (
                                <div key={material.id} className="flex items-center justify-between p-4 border rounded-lg">
                                    <div className="flex items-center space-x-4">
                                        <div className="flex-shrink-0">
                                            <FileText className="h-5 w-5 text-blue-600" />
                                        </div>
                                        <div>
                                            <h3 className="font-medium">{material.name}</h3>
                                            <p className="text-sm text-muted-foreground">{material.type} • {material.category}</p>
                                            <div className="flex items-center space-x-4 mt-1">
                                                <span className="text-xs text-muted-foreground">
                                                    Updated: {material.lastUpdated}
                                                </span>
                                                <span className="text-xs text-muted-foreground">
                                                    Downloads: {material.downloads}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div className="flex items-center space-x-2">
                                        <Badge variant="default">{material.status}</Badge>
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

                {/* Marketing Campaigns */}
                <Card>
                    <CardHeader>
                        <CardTitle>Marketing Campaigns</CardTitle>
                        <CardDescription>
                            Track and manage your marketing campaigns
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-4">
                            {campaigns.map((campaign) => (
                                <div key={campaign.id} className="flex items-center justify-between p-4 border rounded-lg">
                                    <div className="flex items-center space-x-4">
                                        <div className="flex-shrink-0">
                                            <Target className="h-5 w-5 text-purple-600" />
                                        </div>
                                        <div>
                                            <h3 className="font-medium">{campaign.name}</h3>
                                            <p className="text-sm text-muted-foreground">Target: {campaign.target}</p>
                                            <div className="flex items-center space-x-4 mt-1">
                                                <span className="text-xs text-muted-foreground">
                                                    Start: {campaign.startDate}
                                                </span>
                                                <span className="text-xs text-muted-foreground">
                                                    End: {campaign.endDate}
                                                </span>
                                                <span className="text-xs text-muted-foreground">
                                                    Reach: {campaign.reach}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div className="flex items-center space-x-4">
                                        <div className="text-right">
                                            <div className="text-sm font-medium">Engagement: {campaign.engagement}%</div>
                                            <Badge variant={campaign.status === 'Active' ? 'default' : 'secondary'}>
                                                {campaign.status}
                                            </Badge>
                                        </div>
                                        <Button variant="outline" size="sm">
                                            <Eye className="h-4 w-4" />
                                        </Button>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
