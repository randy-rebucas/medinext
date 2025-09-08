import React from 'react';
import { Head, Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { AlertCircle, Key, Mail, ExternalLink } from 'lucide-react';

interface Props {
    message: string;
    trial_expired: boolean;
}

export default function LicenseError({ message, trial_expired }: Props) {
    return (
        <>
            <Head title="License Error" />

            <div className="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
                <div className="sm:mx-auto sm:w-full sm:max-w-md">
                    <div className="flex justify-center">
                        <AlertCircle className="h-12 w-12 text-red-600" />
                    </div>
                    <h2 className="mt-6 text-center text-3xl font-extrabold text-gray-900">
                        License Error
                    </h2>
                    <p className="mt-2 text-center text-sm text-gray-600">
                        {trial_expired ? 'Your free trial has expired' : 'License validation failed'}
                    </p>
                </div>

                <div className="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
                    <Card>
                        <CardHeader>
                            <CardTitle className="text-center text-red-600">
                                Access Restricted
                            </CardTitle>
                            <CardDescription className="text-center">
                                {message}
                            </CardDescription>
                        </CardHeader>

                        <CardContent className="space-y-6">
                            <Alert>
                                <AlertCircle className="h-4 w-4" />
                                <AlertDescription>
                                    {trial_expired ? (
                                        <>
                                            Your 14-day free trial has expired. To continue using MediNext,
                                            you need to activate a valid license key.
                                        </>
                                    ) : (
                                        <>
                                            There was an issue with your license. Please check your license key
                                            or contact support for assistance.
                                        </>
                                    )}
                                </AlertDescription>
                            </Alert>

                            <div className="space-y-4">
                                {trial_expired && (
                                    <Link href="/license/activate">
                                        <Button className="w-full">
                                            <Key className="h-4 w-4 mr-2" />
                                            Activate License
                                        </Button>
                                    </Link>
                                )}

                                <div className="text-center">
                                    <p className="text-sm text-gray-600 mb-4">
                                        Need help? Contact our support team:
                                    </p>

                                    <div className="space-y-2">
                                        <a
                                            href="mailto:support@medinext.com"
                                            className="inline-flex items-center text-sm text-blue-600 hover:text-blue-500"
                                        >
                                            <Mail className="h-4 w-4 mr-2" />
                                            support@medinext.com
                                        </a>

                                        <div className="text-sm text-gray-500">
                                            or visit our{' '}
                                            <a
                                                href="#"
                                                className="text-blue-600 hover:text-blue-500 inline-flex items-center"
                                            >
                                                Support Center
                                                <ExternalLink className="h-3 w-3 ml-1" />
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <div className="mt-6 text-center">
                        <p className="text-sm text-gray-600">
                            Having trouble? Check our{' '}
                            <a href="#" className="font-medium text-blue-600 hover:text-blue-500">
                                License Troubleshooting Guide
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </>
    );
}
