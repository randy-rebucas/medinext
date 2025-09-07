import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    Building2,
    MapPin,
    Users,
    Calendar,
    TrendingUp,
    Target,
    CheckCircle,
    Shield
} from 'lucide-react';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Territory Management',
        href: '/medrep/territory',
    },
    {
        title: 'Territory Management',
        href: '/medrep/territory',
    },
];

interface TerritoryManagementProps {
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

export default function TerritoryManagement({ user }: TerritoryManagementProps = {}) {
    const territories = [
        {
            id: 1,
            name: 'Downtown Medical District',
            area: 'Central Business District',
            doctors: 12,
            clinics: 8,
            lastVisit: '2024-01-15',
            coverage: '95%',
            status: 'Active'
        },
        {
            id: 2,
            name: 'Suburban Healthcare Zone',
            area: 'North Suburbs',
            doctors: 8,
            clinics: 5,
            lastVisit: '2024-01-14',
            coverage: '87%',
            status: 'Active'
        },
        {
            id: 3,
            name: 'Rural Medical Network',
            area: 'Outer Regions',
            doctors: 4,
            clinics: 3,
            lastVisit: '2024-01-12',
            coverage: '72%',
            status: 'Active'
        }
    ];

    const clinics = [
        {
            id: 1,
            name: 'Heart Care Clinic',
            address: '123 Medical Center Dr',
            doctors: 3,
            lastVisit: '2024-01-15',
            status: 'Active',
            territory: 'Downtown Medical District'
        },
        {
            id: 2,
            name: 'Children\'s Medical Center',
            address: '456 Pediatric Ave',
            doctors: 2,
            lastVisit: '2024-01-14',
            status: 'Active',
            territory: 'Suburban Healthcare Zone'
        },
        {
            id: 3,
            name: 'Skin Health Clinic',
            address: '789 Dermatology Blvd',
            doctors: 1,
            lastVisit: '2024-01-13',
            status: 'Active',
            territory: 'Downtown Medical District'
        }
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Territory Management - Medinext">
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
                                    Territory Management
                                </h1>
                                <p className="mt-2 text-emerald-100">
                                    Manage your sales territory and coverage areas
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
                            <MapPin className="mr-2 h-4 w-4" />
                            Add Territory
                        </Button>
                    </div>

                    {/* Territory Overview */}
                    <div className="grid gap-6 md:grid-cols-4">
                        <Card className="relative overflow-hidden border-0 shadow-lg hover:shadow-xl transition-all duration-300 bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20">
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium text-slate-700 dark:text-slate-300">Total Territories</CardTitle>
                                <div className="p-2 bg-blue-500 rounded-lg">
                                    <Building2 className="h-4 w-4 text-white" />
                                </div>
                            </CardHeader>
                            <CardContent>
                                <div className="text-3xl font-bold text-slate-900 dark:text-white">3</div>
                                <div className="flex items-center mt-2">
                                    <TrendingUp className="h-3 w-3 text-blue-500 mr-1" />
                                    <p className="text-xs text-slate-600 dark:text-slate-400">
                                        Active territories
                                    </p>
                                </div>
                            </CardContent>
                        </Card>
                        <Card className="relative overflow-hidden border-0 shadow-lg hover:shadow-xl transition-all duration-300 bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20">
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium text-slate-700 dark:text-slate-300">Total Doctors</CardTitle>
                                <div className="p-2 bg-green-500 rounded-lg">
                                    <Users className="h-4 w-4 text-white" />
                                </div>
                            </CardHeader>
                            <CardContent>
                                <div className="text-3xl font-bold text-slate-900 dark:text-white">24</div>
                                <div className="flex items-center mt-2">
                                    <Users className="h-3 w-3 text-green-500 mr-1" />
                                    <p className="text-xs text-slate-600 dark:text-slate-400">
                                        Healthcare professionals
                                    </p>
                                </div>
                            </CardContent>
                        </Card>
                        <Card className="relative overflow-hidden border-0 shadow-lg hover:shadow-xl transition-all duration-300 bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20">
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium text-slate-700 dark:text-slate-300">Total Clinics</CardTitle>
                                <div className="p-2 bg-purple-500 rounded-lg">
                                    <Target className="h-4 w-4 text-white" />
                                </div>
                            </CardHeader>
                            <CardContent>
                                <div className="text-3xl font-bold text-slate-900 dark:text-white">16</div>
                                <div className="flex items-center mt-2">
                                    <Target className="h-3 w-3 text-purple-500 mr-1" />
                                    <p className="text-xs text-slate-600 dark:text-slate-400">
                                        Medical facilities
                                    </p>
                                </div>
                            </CardContent>
                        </Card>
                        <Card className="relative overflow-hidden border-0 shadow-lg hover:shadow-xl transition-all duration-300 bg-gradient-to-br from-orange-50 to-orange-100 dark:from-orange-900/20 dark:to-orange-800/20">
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium text-slate-700 dark:text-slate-300">Coverage</CardTitle>
                                <div className="p-2 bg-orange-500 rounded-lg">
                                    <TrendingUp className="h-4 w-4 text-white" />
                                </div>
                            </CardHeader>
                            <CardContent>
                                <div className="text-3xl font-bold text-slate-900 dark:text-white">85%</div>
                                <div className="flex items-center mt-2">
                                    <CheckCircle className="h-3 w-3 text-orange-500 mr-1" />
                                    <p className="text-xs text-slate-600 dark:text-slate-400">
                                        Territory coverage
                                    </p>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    {/* Territories */}
                    <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                        <CardHeader>
                            <CardTitle className="text-lg font-semibold text-slate-900 dark:text-white">Territory Overview</CardTitle>
                            <CardDescription className="text-slate-600 dark:text-slate-300">
                                Manage your sales territories and coverage areas
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                {territories.map((territory) => (
                                    <div key={territory.id} className="flex items-center justify-between p-4 rounded-lg bg-slate-50 dark:bg-slate-700/50 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                                        <div className="flex items-center space-x-4">
                                            <div className="flex-shrink-0">
                                                <Building2 className="h-8 w-8 text-blue-600 dark:text-blue-400" />
                                            </div>
                                            <div>
                                                <h3 className="font-medium text-slate-900 dark:text-white">{territory.name}</h3>
                                                <p className="text-sm text-slate-600 dark:text-slate-400">{territory.area}</p>
                                                <div className="flex items-center space-x-4 mt-1">
                                                    <span className="text-xs text-slate-500 dark:text-slate-500">
                                                        Doctors: {territory.doctors}
                                                    </span>
                                                    <span className="text-xs text-slate-500 dark:text-slate-500">
                                                        Clinics: {territory.clinics}
                                                    </span>
                                                    <span className="text-xs text-slate-500 dark:text-slate-500">
                                                        Last Visit: {territory.lastVisit}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div className="flex items-center space-x-4">
                                            <div className="text-right">
                                                <div className="text-sm font-medium text-slate-900 dark:text-white">Coverage: {territory.coverage}</div>
                                                <Badge className="mt-1 bg-emerald-100 text-emerald-800 border-0">{territory.status}</Badge>
                                            </div>
                                            <Button variant="outline" size="sm" className="border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700">
                                                <MapPin className="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </CardContent>
                    </Card>

                    {/* Clinics */}
                    <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                        <CardHeader>
                            <CardTitle className="text-lg font-semibold text-slate-900 dark:text-white">Clinic Directory</CardTitle>
                            <CardDescription className="text-slate-600 dark:text-slate-300">
                                View and manage clinics in your territory
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                {clinics.map((clinic) => (
                                    <div key={clinic.id} className="flex items-center justify-between p-4 rounded-lg bg-slate-50 dark:bg-slate-700/50 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                                        <div className="flex items-center space-x-4">
                                            <div className="flex-shrink-0">
                                                <Building2 className="h-8 w-8 text-green-600 dark:text-green-400" />
                                            </div>
                                            <div>
                                                <h3 className="font-medium text-slate-900 dark:text-white">{clinic.name}</h3>
                                                <p className="text-sm text-slate-600 dark:text-slate-400">{clinic.address}</p>
                                                <div className="flex items-center space-x-4 mt-1">
                                                    <span className="text-xs text-slate-500 dark:text-slate-500">
                                                        Doctors: {clinic.doctors}
                                                    </span>
                                                    <span className="text-xs text-slate-500 dark:text-slate-500">
                                                        Territory: {clinic.territory}
                                                    </span>
                                                    <span className="text-xs text-slate-500 dark:text-slate-500">
                                                        Last Visit: {clinic.lastVisit}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div className="flex items-center space-x-2">
                                            <Badge className="bg-emerald-100 text-emerald-800 border-0">{clinic.status}</Badge>
                                            <Button variant="outline" size="sm" className="border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700">
                                                <Calendar className="h-4 w-4" />
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
