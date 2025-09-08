import React from 'react';
import { Head, Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { CheckCircle, AlertCircle, Clock, Key, Users, Building, UserCheck, Calendar } from 'lucide-react';

interface LicenseInfo {
    has_license: boolean;
    license_type?: string;
    customer_name?: string;
    expires_at?: string;
    days_until_expiration?: number;
    features?: string[];
    usage?: {
        users: { current: number; limit: number; percentage: number };
        clinics: { current: number; limit: number; percentage: number };
        patients: { current: number; limit: number; percentage: number };
        appointments: { current: number; limit: number; percentage: number };
    };
}

interface AccessStatus {
    type: 'trial' | 'licensed' | 'none';
    status: 'active' | 'expired' | 'inactive';
    message: string;
    expires_at?: string;
    days_remaining?: number;
    days_expired?: number;
}

interface User {
    id: number;
    name: string;
    email: string;
    license_key?: string;
}

interface Props {
    user: User;
    license_info: LicenseInfo;
    access_status: AccessStatus;
}

export default function LicenseStatus({ user, license_info, access_status }: Props) {
    const getStatusIcon = () => {
        switch (access_status.status) {
            case 'active':
                return <CheckCircle className="h-5 w-5 text-green-500" />;
            case 'expired':
                return <AlertCircle className="h-5 w-5 text-red-500" />;
            default:
                return <Clock className="h-5 w-5 text-yellow-500" />;
        }
    };

    const getStatusColor = () => {
        switch (access_status.status) {
            case 'active':
                return 'text-green-600';
            case 'expired':
                return 'text-red-600';
            default:
                return 'text-yellow-600';
        }
    };

    const getStatusBadge = () => {
        switch (access_status.status) {
            case 'active':
                return <Badge className="bg-green-100 text-green-800">Active</Badge>;
            case 'expired':
                return <Badge className="bg-red-100 text-red-800">Expired</Badge>;
            default:
                return <Badge className="bg-yellow-100 text-yellow-800">Inactive</Badge>;
        }
    };

    const formatDate = (dateString: string) => {
        return new Date(dateString).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
        });
    };

    return (
        <>
            <Head title="License Status" />

            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold text-gray-900">License Status</h1>
                        <p className="text-gray-600">View your current license and usage information</p>
                    </div>
                    {access_status.status === 'expired' && (
                        <Link href={"/license/activate"}>
                            <Button>
                                <Key className="h-4 w-4 mr-2" />
                                Activate License
                            </Button>
                        </Link>
                    )}
                </div>

                {/* License Status Card */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            {getStatusIcon()}
                            <span className={getStatusColor()}>
                                {access_status.message}
                            </span>
                            {getStatusBadge()}
                        </CardTitle>
                        <CardDescription>
                            {access_status.type === 'licensed' && access_status.expires_at && (
                                <>
                                    License expires on {formatDate(access_status.expires_at)}
                                    {access_status.days_remaining && access_status.days_remaining > 0 && (
                                        <span className="ml-2">
                                            ({access_status.days_remaining} days remaining)
                                        </span>
                                    )}
                                </>
                            )}
                            {access_status.type === 'trial' && access_status.expires_at && (
                                <>
                                    Trial expires on {formatDate(access_status.expires_at)}
                                    {access_status.days_remaining && access_status.days_remaining > 0 && (
                                        <span className="ml-2">
                                            ({access_status.days_remaining} days remaining)
                                        </span>
                                    )}
                                </>
                            )}
                        </CardDescription>
                    </CardHeader>
                </Card>

                {license_info.has_license && (
                    <>
                        {/* License Information */}
                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <Card>
                                <CardHeader className="pb-3">
                                    <CardTitle className="text-sm font-medium text-gray-600">
                                        License Type
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="text-2xl font-bold capitalize">
                                        {license_info.license_type}
                                    </div>
                                </CardContent>
                            </Card>

                            <Card>
                                <CardHeader className="pb-3">
                                    <CardTitle className="text-sm font-medium text-gray-600">
                                        Customer
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="text-lg font-semibold">
                                        {license_info.customer_name}
                                    </div>
                                </CardContent>
                            </Card>

                            <Card>
                                <CardHeader className="pb-3">
                                    <CardTitle className="text-sm font-medium text-gray-600">
                                        License Key
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="text-sm font-mono bg-gray-100 p-2 rounded">
                                        {user.license_key}
                                    </div>
                                </CardContent>
                            </Card>
                        </div>

                        {/* Usage Statistics */}
                        {license_info.usage && (
                            <Card>
                                <CardHeader>
                                    <CardTitle>Usage Statistics</CardTitle>
                                    <CardDescription>
                                        Current usage against your license limits
                                    </CardDescription>
                                </CardHeader>
                                <CardContent className="space-y-6">
                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div className="space-y-2">
                                            <div className="flex items-center gap-2">
                                                <Users className="h-4 w-4 text-blue-600" />
                                                <span className="text-sm font-medium">Users</span>
                                            </div>
                                            <div className="space-y-1">
                                                <div className="flex justify-between text-sm">
                                                    <span>{license_info.usage.users.current}</span>
                                                    <span>{license_info.usage.users.limit}</span>
                                                </div>
                                                <div className="w-full bg-gray-200 rounded-full h-2">
                                                    <div
                                                        className="bg-blue-600 h-2 rounded-full"
                                                        style={{ width: `${license_info.usage.users.percentage}%` }}
                                                    ></div>
                                                </div>
                                            </div>
                                        </div>

                                        <div className="space-y-2">
                                            <div className="flex items-center gap-2">
                                                <Building className="h-4 w-4 text-green-600" />
                                                <span className="text-sm font-medium">Clinics</span>
                                            </div>
                                            <div className="space-y-1">
                                                <div className="flex justify-between text-sm">
                                                    <span>{license_info.usage.clinics.current}</span>
                                                    <span>{license_info.usage.clinics.limit}</span>
                                                </div>
                                                <div className="w-full bg-gray-200 rounded-full h-2">
                                                    <div
                                                        className="bg-green-600 h-2 rounded-full"
                                                        style={{ width: `${license_info.usage.clinics.percentage}%` }}
                                                    ></div>
                                                </div>
                                            </div>
                                        </div>

                                        <div className="space-y-2">
                                            <div className="flex items-center gap-2">
                                                <UserCheck className="h-4 w-4 text-purple-600" />
                                                <span className="text-sm font-medium">Patients</span>
                                            </div>
                                            <div className="space-y-1">
                                                <div className="flex justify-between text-sm">
                                                    <span>{license_info.usage.patients.current}</span>
                                                    <span>{license_info.usage.patients.limit}</span>
                                                </div>
                                                <div className="w-full bg-gray-200 rounded-full h-2">
                                                    <div
                                                        className="bg-purple-600 h-2 rounded-full"
                                                        style={{ width: `${license_info.usage.patients.percentage}%` }}
                                                    ></div>
                                                </div>
                                            </div>
                                        </div>

                                        <div className="space-y-2">
                                            <div className="flex items-center gap-2">
                                                <Calendar className="h-4 w-4 text-orange-600" />
                                                <span className="text-sm font-medium">Appointments (This Month)</span>
                                            </div>
                                            <div className="space-y-1">
                                                <div className="flex justify-between text-sm">
                                                    <span>{license_info.usage.appointments.current}</span>
                                                    <span>{license_info.usage.appointments.limit}</span>
                                                </div>
                                                <div className="w-full bg-gray-200 rounded-full h-2">
                                                    <div
                                                        className="bg-orange-600 h-2 rounded-full"
                                                        style={{ width: `${license_info.usage.appointments.percentage}%` }}
                                                    ></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        )}

                        {/* Features */}
                        {license_info.features && license_info.features.length > 0 && (
                            <Card>
                                <CardHeader>
                                    <CardTitle>Available Features</CardTitle>
                                    <CardDescription>
                                        Features included in your license
                                    </CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <div className="flex flex-wrap gap-2">
                                        {license_info.features.map((feature, index) => (
                                            <Badge key={index} variant="secondary">
                                                {feature}
                                            </Badge>
                                        ))}
                                    </div>
                                </CardContent>
                            </Card>
                        )}
                    </>
                )}

                {!license_info.has_license && access_status.type === 'trial' && (
                    <Card>
                        <CardHeader>
                            <CardTitle>Free Trial</CardTitle>
                            <CardDescription>
                                You are currently using the free trial version
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="text-center py-6">
                                <Clock className="h-12 w-12 text-blue-600 mx-auto mb-4" />
                                <h3 className="text-lg font-semibold mb-2">Free Trial Active</h3>
                                <p className="text-gray-600 mb-4">
                                    You have {access_status.days_remaining} days remaining in your free trial.
                                </p>
                                <Link href={"/license/activate"}>
                                    <Button>
                                        <Key className="h-4 w-4 mr-2" />
                                        Activate License
                                    </Button>
                                </Link>
                            </div>
                        </CardContent>
                    </Card>
                )}
            </div>
        </>
    );
}
