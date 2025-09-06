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
    CheckCircle
} from 'lucide-react';

export default function TerritoryManagement() {
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
        <AppLayout>
            <Head title="Territory Management" />
            
            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Territory Management</h1>
                        <p className="text-muted-foreground">
                            Manage your sales territory and coverage areas
                        </p>
                    </div>
                    <Button>
                        <MapPin className="mr-2 h-4 w-4" />
                        Add Territory
                    </Button>
                </div>

                {/* Territory Overview */}
                <div className="grid gap-4 md:grid-cols-4">
                    <Card>
                        <CardContent className="p-6">
                            <div className="flex items-center">
                                <Building2 className="h-8 w-8 text-blue-600" />
                                <div className="ml-4">
                                    <p className="text-sm font-medium text-muted-foreground">Total Territories</p>
                                    <p className="text-2xl font-bold">3</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-6">
                            <div className="flex items-center">
                                <Users className="h-8 w-8 text-green-600" />
                                <div className="ml-4">
                                    <p className="text-sm font-medium text-muted-foreground">Total Doctors</p>
                                    <p className="text-2xl font-bold">24</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-6">
                            <div className="flex items-center">
                                <Target className="h-8 w-8 text-purple-600" />
                                <div className="ml-4">
                                    <p className="text-sm font-medium text-muted-foreground">Total Clinics</p>
                                    <p className="text-2xl font-bold">16</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-6">
                            <div className="flex items-center">
                                <TrendingUp className="h-8 w-8 text-orange-600" />
                                <div className="ml-4">
                                    <p className="text-sm font-medium text-muted-foreground">Coverage</p>
                                    <p className="text-2xl font-bold">85%</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Territories */}
                <Card>
                    <CardHeader>
                        <CardTitle>Territory Overview</CardTitle>
                        <CardDescription>
                            Manage your sales territories and coverage areas
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-4">
                            {territories.map((territory) => (
                                <div key={territory.id} className="flex items-center justify-between p-4 border rounded-lg">
                                    <div className="flex items-center space-x-4">
                                        <div className="flex-shrink-0">
                                            <Building2 className="h-8 w-8 text-blue-600" />
                                        </div>
                                        <div>
                                            <h3 className="font-medium">{territory.name}</h3>
                                            <p className="text-sm text-muted-foreground">{territory.area}</p>
                                            <div className="flex items-center space-x-4 mt-1">
                                                <span className="text-xs text-muted-foreground">
                                                    Doctors: {territory.doctors}
                                                </span>
                                                <span className="text-xs text-muted-foreground">
                                                    Clinics: {territory.clinics}
                                                </span>
                                                <span className="text-xs text-muted-foreground">
                                                    Last Visit: {territory.lastVisit}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div className="flex items-center space-x-4">
                                        <div className="text-right">
                                            <div className="text-sm font-medium">Coverage: {territory.coverage}</div>
                                            <Badge variant="default" className="mt-1">{territory.status}</Badge>
                                        </div>
                                        <Button variant="outline" size="sm">
                                            <MapPin className="h-4 w-4" />
                                        </Button>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </CardContent>
                </Card>

                {/* Clinics */}
                <Card>
                    <CardHeader>
                        <CardTitle>Clinic Directory</CardTitle>
                        <CardDescription>
                            View and manage clinics in your territory
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-4">
                            {clinics.map((clinic) => (
                                <div key={clinic.id} className="flex items-center justify-between p-4 border rounded-lg">
                                    <div className="flex items-center space-x-4">
                                        <div className="flex-shrink-0">
                                            <Building2 className="h-8 w-8 text-green-600" />
                                        </div>
                                        <div>
                                            <h3 className="font-medium">{clinic.name}</h3>
                                            <p className="text-sm text-muted-foreground">{clinic.address}</p>
                                            <div className="flex items-center space-x-4 mt-1">
                                                <span className="text-xs text-muted-foreground">
                                                    Doctors: {clinic.doctors}
                                                </span>
                                                <span className="text-xs text-muted-foreground">
                                                    Territory: {clinic.territory}
                                                </span>
                                                <span className="text-xs text-muted-foreground">
                                                    Last Visit: {clinic.lastVisit}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div className="flex items-center space-x-2">
                                        <Badge variant="default">{clinic.status}</Badge>
                                        <Button variant="outline" size="sm">
                                            <Calendar className="h-4 w-4" />
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
