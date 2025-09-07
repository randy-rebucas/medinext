import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    FileText,
    Download,
    Eye,
    Target,
    TrendingUp,
    Building2,
    Shield
} from 'lucide-react';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Marketing Materials',
        href: '/medrep/marketing',
    },
    {
        title: 'Marketing Materials',
        href: '/medrep/marketing',
    },
];

interface MarketingMaterialsProps {
    user?: {
        id: number;
        name: string;
        email: string;
        role: string;
        company_id?: number;
        company?: {
            id: number;
            name: string;
        };
    };
}

export default function MarketingMaterials({ user }: MarketingMaterialsProps = {}) {
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
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Marketing Materials - Medinext">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&family=instrument-sans:400,500,600" rel="stylesheet" />
            </Head>
            <div className="min-h-screen bg-gradient-to-br from-slate-50 via-white to-blue-50 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900">
                <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
                    {/* Modern Header */}
                    <div className="relative overflow-hidden rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 p-8 text-white shadow-xl">
                        <div className="absolute inset-0 bg-black/10"></div>
                        <div className="relative flex items-center justify-between">
                            <div>
                                <h1 className="text-3xl font-bold tracking-tight">
                                    Marketing Materials
                                </h1>
                                <p className="mt-2 text-emerald-100">
                                    Access and manage your marketing materials and campaigns
                                </p>
                            </div>
                            <div className="flex items-center gap-3">
                                <Badge variant="secondary" className="flex items-center gap-1 bg-white/20 text-white border-white/30 hover:bg-white/30">
                                    <Shield className="h-3 w-3" />
                                    Medical Rep
                                </Badge>
                                {user?.company && (
                                    <Badge variant="secondary" className="flex items-center gap-1 bg-white/20 text-white border-white/30 hover:bg-white/30">
                                        <Building2 className="h-3 w-3" />
                                        {user.company.name}
                                    </Badge>
                                )}
                            </div>
                        </div>
                        {/* Decorative elements */}
                        <div className="absolute -top-4 -right-4 w-24 h-24 bg-white/10 rounded-full"></div>
                        <div className="absolute -bottom-2 -left-2 w-16 h-16 bg-white/5 rounded-full"></div>
                    </div>

                    {/* Action Button */}
                    <div className="flex justify-end">
                        <Button className="bg-emerald-600 hover:bg-emerald-700 text-white">
                            <FileText className="mr-2 h-4 w-4" />
                            Add Material
                        </Button>
                    </div>

                    {/* Marketing Overview */}
                    <div className="grid gap-6 md:grid-cols-4">
                        <Card className="relative overflow-hidden border-0 shadow-lg hover:shadow-xl transition-all duration-300 bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20">
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium text-slate-700 dark:text-slate-300">Total Materials</CardTitle>
                                <div className="p-2 bg-blue-500 rounded-lg">
                                    <FileText className="h-4 w-4 text-white" />
                                </div>
                            </CardHeader>
                            <CardContent>
                                <div className="text-3xl font-bold text-slate-900 dark:text-white">24</div>
                                <div className="flex items-center mt-2">
                                    <TrendingUp className="h-3 w-3 text-blue-500 mr-1" />
                                    <p className="text-xs text-slate-600 dark:text-slate-400">
                                        Available materials
                                    </p>
                                </div>
                            </CardContent>
                        </Card>
                        <Card className="relative overflow-hidden border-0 shadow-lg hover:shadow-xl transition-all duration-300 bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20">
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium text-slate-700 dark:text-slate-300">Downloads</CardTitle>
                                <div className="p-2 bg-green-500 rounded-lg">
                                    <Download className="h-4 w-4 text-white" />
                                </div>
                            </CardHeader>
                            <CardContent>
                                <div className="text-3xl font-bold text-slate-900 dark:text-white">156</div>
                                <div className="flex items-center mt-2">
                                    <TrendingUp className="h-3 w-3 text-green-500 mr-1" />
                                    <p className="text-xs text-slate-600 dark:text-slate-400">
                                        This month
                                    </p>
                                </div>
                            </CardContent>
                        </Card>
                        <Card className="relative overflow-hidden border-0 shadow-lg hover:shadow-xl transition-all duration-300 bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20">
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium text-slate-700 dark:text-slate-300">Active Campaigns</CardTitle>
                                <div className="p-2 bg-purple-500 rounded-lg">
                                    <Target className="h-4 w-4 text-white" />
                                </div>
                            </CardHeader>
                            <CardContent>
                                <div className="text-3xl font-bold text-slate-900 dark:text-white">2</div>
                                <div className="flex items-center mt-2">
                                    <Target className="h-3 w-3 text-purple-500 mr-1" />
                                    <p className="text-xs text-slate-600 dark:text-slate-400">
                                        Running campaigns
                                    </p>
                                </div>
                            </CardContent>
                        </Card>
                        <Card className="relative overflow-hidden border-0 shadow-lg hover:shadow-xl transition-all duration-300 bg-gradient-to-br from-orange-50 to-orange-100 dark:from-orange-900/20 dark:to-orange-800/20">
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium text-slate-700 dark:text-slate-300">Engagement</CardTitle>
                                <div className="p-2 bg-orange-500 rounded-lg">
                                    <TrendingUp className="h-4 w-4 text-white" />
                                </div>
                            </CardHeader>
                            <CardContent>
                                <div className="text-3xl font-bold text-slate-900 dark:text-white">41%</div>
                                <div className="flex items-center mt-2">
                                    <TrendingUp className="h-3 w-3 text-orange-500 mr-1" />
                                    <p className="text-xs text-slate-600 dark:text-slate-400">
                                        Average engagement
                                    </p>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    {/* Marketing Materials */}
                    <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                        <CardHeader>
                            <CardTitle className="text-lg font-semibold text-slate-900 dark:text-white">Marketing Materials</CardTitle>
                            <CardDescription className="text-slate-600 dark:text-slate-300">
                                Access and download your marketing materials
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                {materials.map((material) => (
                                    <div key={material.id} className="flex items-center justify-between p-4 rounded-lg bg-slate-50 dark:bg-slate-700/50 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                                        <div className="flex items-center space-x-4">
                                            <div className="flex-shrink-0">
                                                <FileText className="h-5 w-5 text-blue-600 dark:text-blue-400" />
                                            </div>
                                            <div>
                                                <h3 className="font-medium text-slate-900 dark:text-white">{material.name}</h3>
                                                <p className="text-sm text-slate-600 dark:text-slate-400">{material.type} • {material.category}</p>
                                                <div className="flex items-center space-x-4 mt-1">
                                                    <span className="text-xs text-slate-500 dark:text-slate-500">
                                                        Updated: {material.lastUpdated}
                                                    </span>
                                                    <span className="text-xs text-slate-500 dark:text-slate-500">
                                                        Downloads: {material.downloads}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div className="flex items-center space-x-2">
                                            <Badge className="bg-emerald-100 text-emerald-800 border-0">{material.status}</Badge>
                                            <Button variant="outline" size="sm" className="border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700">
                                                <Eye className="h-4 w-4" />
                                            </Button>
                                            <Button variant="outline" size="sm" className="border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700">
                                                <Download className="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </CardContent>
                    </Card>

                    {/* Marketing Campaigns */}
                    <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                        <CardHeader>
                            <CardTitle className="text-lg font-semibold text-slate-900 dark:text-white">Marketing Campaigns</CardTitle>
                            <CardDescription className="text-slate-600 dark:text-slate-300">
                                Track and manage your marketing campaigns
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                {campaigns.map((campaign) => (
                                    <div key={campaign.id} className="flex items-center justify-between p-4 rounded-lg bg-slate-50 dark:bg-slate-700/50 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                                        <div className="flex items-center space-x-4">
                                            <div className="flex-shrink-0">
                                                <Target className="h-5 w-5 text-purple-600 dark:text-purple-400" />
                                            </div>
                                            <div>
                                                <h3 className="font-medium text-slate-900 dark:text-white">{campaign.name}</h3>
                                                <p className="text-sm text-slate-600 dark:text-slate-400">Target: {campaign.target}</p>
                                                <div className="flex items-center space-x-4 mt-1">
                                                    <span className="text-xs text-slate-500 dark:text-slate-500">
                                                        Start: {campaign.startDate}
                                                    </span>
                                                    <span className="text-xs text-slate-500 dark:text-slate-500">
                                                        End: {campaign.endDate}
                                                    </span>
                                                    <span className="text-xs text-slate-500 dark:text-slate-500">
                                                        Reach: {campaign.reach}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div className="flex items-center space-x-4">
                                            <div className="text-right">
                                                <div className="text-sm font-medium text-slate-900 dark:text-white">Engagement: {campaign.engagement}%</div>
                                                <Badge variant={campaign.status === 'Active' ? 'default' : 'secondary'} className="border-0">
                                                    {campaign.status}
                                                </Badge>
                                            </div>
                                            <Button variant="outline" size="sm" className="border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700">
                                                <Eye className="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AppLayout>
    );
}
