import React, { useState } from 'react';
import { Head, useForm } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { AlertCircle, CheckCircle, Clock, Key } from 'lucide-react';

interface TrialStatus {
    type: 'trial' | 'licensed' | 'none';
    status: 'active' | 'expired' | 'inactive';
    message: string;
    expires_at?: string;
    days_remaining?: number;
    days_expired?: number;
}

interface Props {
    trial_status: TrialStatus;
}

export default function LicenseActivation({ trial_status }: Props) {
    const { data, setData, post, processing, errors, reset } = useForm({
        license_key: '',
    });

    const [isValidating, setIsValidating] = useState(false);
    const [validationResult, setValidationResult] = useState<{
        valid: boolean;
        message: string;
    } | null>(null);

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/license/activate', {
            onSuccess: () => {
                reset();
                setValidationResult(null);
            },
        });
    };

    const handleValidateLicense = async () => {
        if (!data.license_key.trim()) return;

        setIsValidating(true);
        try {
            const response = await fetch('/api/v1/license/validate', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                body: JSON.stringify({ license_key: data.license_key }),
            });

            const result = await response.json();

            // Handle different response structures and add detailed information
            if (result.success) {
                setValidationResult({
                    valid: true,
                    message: result.data.message || 'License key is valid and available.',
                });
            } else {
                const errorMessage = result.data?.message || result.message || 'License key validation failed.';
                const errorCode = result.data?.error_code || result.error_code;

                let detailedMessage = errorMessage;

                // The backend now provides detailed messages, so we can use them directly
                // But we can still add some frontend-specific enhancements
                if (errorCode === 'LICENSE_ALREADY_IN_USE') {
                    // Backend already includes the assigned user in the message
                    detailedMessage = errorMessage;
                } else if (errorCode === 'LICENSE_EXPIRED') {
                    // Backend already includes the expiry date in the message
                    detailedMessage = errorMessage;
                } else if (errorCode === 'LICENSE_NOT_FOUND') {
                    // Backend already provides a helpful message
                    detailedMessage = errorMessage;
                } else if (errorCode === 'LICENSE_INACTIVE') {
                    detailedMessage = errorMessage;
                }

                setValidationResult({
                    valid: false,
                    message: detailedMessage,
                });
            }
        } catch {
            setValidationResult({
                valid: false,
                message: 'Failed to validate license key. Please try again.',
            });
        } finally {
            setIsValidating(false);
        }
    };

    const getStatusIcon = () => {
        switch (trial_status.status) {
            case 'active':
                return <CheckCircle className="h-5 w-5 text-green-500" />;
            case 'expired':
                return <AlertCircle className="h-5 w-5 text-red-500" />;
            default:
                return <Clock className="h-5 w-5 text-yellow-500" />;
        }
    };

    const getStatusColor = () => {
        switch (trial_status.status) {
            case 'active':
                return 'text-green-600';
            case 'expired':
                return 'text-red-600';
            default:
                return 'text-yellow-600';
        }
    };

    return (
        <>
            <Head title="License Activation" />

            <div className="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
                <div className="sm:mx-auto sm:w-full sm:max-w-md">
                    <div className="flex justify-center">
                        <Key className="h-12 w-12 text-blue-600" />
                    </div>
                    <h2 className="mt-6 text-center text-3xl font-extrabold text-gray-900">
                        License Activation
                    </h2>
                    <p className="mt-2 text-center text-sm text-gray-600">
                        Activate your license to continue using MediNext
                    </p>
                </div>

                <div className="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                {getStatusIcon()}
                                <span className={getStatusColor()}>
                                    {trial_status.message}
                                </span>
                            </CardTitle>
                            <CardDescription>
                                {trial_status.type === 'trial' && trial_status.status === 'active' && (
                                    <>
                                        Your free trial expires in {trial_status.days_remaining} days.
                                        <br />
                                        Activate a license to continue using all features.
                                    </>
                                )}
                                {trial_status.type === 'trial' && trial_status.status === 'expired' && (
                                    <>
                                        Your free trial expired {trial_status.days_expired} days ago.
                                        <br />
                                        Please activate a license to regain access.
                                    </>
                                )}
                            </CardDescription>
                        </CardHeader>

                        <CardContent className="space-y-6">
                            {trial_status.status === 'expired' && (
                                <Alert>
                                    <AlertCircle className="h-4 w-4" />
                                    <AlertDescription>
                                        Your free trial has expired. You need to activate a license to continue using the application.
                                    </AlertDescription>
                                </Alert>
                            )}

                            <form onSubmit={handleSubmit} className="space-y-4">
                                <div>
                                    <Label htmlFor="license_key">License Key</Label>
                                    <div className="mt-1 flex gap-2">
                                        <Input
                                            id="license_key"
                                            type="text"
                                            value={data.license_key}
                                            onChange={(e) => setData('license_key', e.target.value)}
                                            placeholder="Enter your license key (e.g., MEDI-XXXX-XXXX-XXXX-XXXX)"
                                            className="flex-1"
                                            required
                                        />
                                        <Button
                                            type="button"
                                            variant="outline"
                                            onClick={handleValidateLicense}
                                            disabled={isValidating || !data.license_key.trim()}
                                        >
                                            {isValidating ? 'Validating...' : 'Validate'}
                                        </Button>
                                    </div>
                                    {errors.license_key && (
                                        <p className="mt-1 text-sm text-red-600">{errors.license_key}</p>
                                    )}
                                </div>

                                {validationResult && (
                                    <Alert className={validationResult.valid ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50'}>
                                        {validationResult.valid ? (
                                            <CheckCircle className="h-4 w-4 text-green-600" />
                                        ) : (
                                            <AlertCircle className="h-4 w-4 text-red-600" />
                                        )}
                                        <AlertDescription className={validationResult.valid ? 'text-green-800' : 'text-red-800'}>
                                            {validationResult.message}
                                        </AlertDescription>
                                    </Alert>
                                )}

                                <Button
                                    type="submit"
                                    className="w-full"
                                    disabled={processing || !data.license_key.trim()}
                                >
                                    {processing ? 'Activating License...' : 'Activate License'}
                                </Button>
                            </form>

                            <div className="text-center">
                                <p className="text-sm text-gray-600">
                                    Don't have a license key?{' '}
                                    <a
                                        href="mailto:support@medinext.com"
                                        className="font-medium text-blue-600 hover:text-blue-500"
                                    >
                                        Contact Support
                                    </a>
                                </p>
                            </div>
                        </CardContent>
                    </Card>

                    <div className="mt-6 text-center">
                        <p className="text-sm text-gray-600">
                            Need help? Check our{' '}
                            <a href="#" className="font-medium text-blue-600 hover:text-blue-500">
                                License Activation Guide
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </>
    );
}
