import { Head, Link, useForm } from '@inertiajs/react';
import { Key, ArrowRight, ArrowLeft, CheckCircle, AlertCircle, Clock } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Progress } from '@/components/ui/progress';
import InputError from '@/components/input-error';

interface LicenseProps {
    user: {
        id: number;
        name: string;
        email: string;
        trial_status: {
            type: string;
            status: string;
            message: string;
            days_remaining?: number;
        };
    };
    license_info: {
        total_licenses: number;
        active_licenses: number;
        available_licenses: number;
    };
}

export default function License({ user, license_info, trial_status }: LicenseProps) {
    const { data, setData, post, processing, errors } = useForm({
        license_key: '',
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/onboarding/license');
    };

    return (
        <>
            <Head title="Activate License - Medinext" />
            
            <div className="min-h-screen bg-gradient-to-br from-slate-50 via-white to-blue-50 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900">
                <div className="container mx-auto px-4 py-8">
                    {/* Header */}
                    <div className="text-center mb-8">
                        <div className="flex items-center justify-center space-x-2 mb-4">
                            <div className="h-12 w-12 rounded-lg bg-gradient-to-r from-blue-600 to-purple-600 flex items-center justify-center">
                                <span className="text-white font-bold text-xl">M</span>
                            </div>
                            <span className="text-3xl font-bold text-slate-900 dark:text-white">Medinext</span>
                        </div>
                        <h1 className="text-4xl font-bold text-slate-900 dark:text-white mb-4">
                            Activate Your License
                        </h1>
                        <p className="text-xl text-slate-600 dark:text-slate-300 mb-6">
                            Enter your license key to unlock all features
                        </p>
                        
                        {/* Trial Status */}
                        <div className="inline-flex items-center px-4 py-2 rounded-full bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200">
                            <Clock className="h-5 w-5 mr-2" />
                            <span className="font-medium">
                                {trial_status.type === 'trial' && trial_status.days_remaining 
                                    ? `${trial_status.days_remaining} days remaining in your free trial`
                                    : trial_status.message
                                }
                            </span>
                        </div>
                    </div>

                    {/* Progress */}
                    <div className="max-w-2xl mx-auto mb-8">
                        <div className="flex items-center justify-between mb-2">
                            <span className="text-sm font-medium text-slate-700 dark:text-slate-300">Setup Progress</span>
                            <span className="text-sm text-slate-500 dark:text-slate-400">Step 2 of 4</span>
                        </div>
                        <Progress value={50} className="h-2" />
                    </div>

                    <div className="max-w-2xl mx-auto">
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center">
                                    <Key className="h-5 w-5 mr-2" />
                                    License Activation
                                </CardTitle>
                                <CardDescription>
                                    Enter your license key to activate full access to all Medinext features
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <form onSubmit={handleSubmit} className="space-y-6">
                                    <div className="space-y-2">
                                        <Label htmlFor="license_key" className="text-sm font-medium text-slate-700 dark:text-slate-300">
                                            License Key
                                        </Label>
                                        <div className="relative">
                                            <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <Key className="h-5 w-5 text-slate-400" />
                                            </div>
                                            <Input
                                                id="license_key"
                                                type="text"
                                                required
                                                autoFocus
                                                value={data.license_key}
                                                onChange={(e) => setData('license_key', e.target.value)}
                                                placeholder="Enter your license key (e.g., MEDI-XXXX-XXXX-XXXX-XXXX)"
                                                className="pl-10 h-12 border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-700 dark:text-white"
                                            />
                                        </div>
                                        <InputError message={errors.license_key} />
                                        <p className="text-sm text-slate-500 dark:text-slate-400">
                                            Your license key should be in the format: MEDI-XXXX-XXXX-XXXX-XXXX
                                        </p>
                                    </div>

                                    <Button 
                                        type="submit" 
                                        className="w-full h-12 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700" 
                                        disabled={processing}
                                    >
                                        {processing ? (
                                            <>
                                                <div className="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
                                                Activating License...
                                            </>
                                        ) : (
                                            <>
                                                <CheckCircle className="h-4 w-4 mr-2" />
                                                Activate License
                                            </>
                                        )}
                                    </Button>
                                </form>
                            </CardContent>
                        </Card>

                        {/* License Benefits */}
                        <div className="mt-8">
                            <h3 className="text-lg font-semibold text-slate-900 dark:text-white mb-4">
                                What you get with a license:
                            </h3>
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div className="flex items-start space-x-3">
                                    <CheckCircle className="h-5 w-5 text-green-500 mt-0.5" />
                                    <div>
                                        <h4 className="font-medium text-slate-900 dark:text-white">Unlimited Users</h4>
                                        <p className="text-sm text-slate-600 dark:text-slate-300">Add as many staff members as needed</p>
                                    </div>
                                </div>
                                <div className="flex items-start space-x-3">
                                    <CheckCircle className="h-5 w-5 text-green-500 mt-0.5" />
                                    <div>
                                        <h4 className="font-medium text-slate-900 dark:text-white">Advanced Features</h4>
                                        <p className="text-sm text-slate-600 dark:text-slate-300">Access to all premium features</p>
                                    </div>
                                </div>
                                <div className="flex items-start space-x-3">
                                    <CheckCircle className="h-5 w-5 text-green-500 mt-0.5" />
                                    <div>
                                        <h4 className="font-medium text-slate-900 dark:text-white">Priority Support</h4>
                                        <p className="text-sm text-slate-600 dark:text-slate-300">24/7 technical support</p>
                                    </div>
                                </div>
                                <div className="flex items-start space-x-3">
                                    <CheckCircle className="h-5 w-5 text-green-500 mt-0.5" />
                                    <div>
                                        <h4 className="font-medium text-slate-900 dark:text-white">Data Backup</h4>
                                        <p className="text-sm text-slate-600 dark:text-slate-300">Automated daily backups</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {/* License Info */}
                        {license_info && (
                            <div className="mt-8">
                                <Card>
                                    <CardHeader>
                                        <CardTitle className="text-base">License Information</CardTitle>
                                    </CardHeader>
                                    <CardContent>
                                        <div className="grid grid-cols-3 gap-4 text-center">
                                            <div>
                                                <div className="text-2xl font-bold text-slate-900 dark:text-white">
                                                    {license_info.total_licenses}
                                                </div>
                                                <div className="text-sm text-slate-600 dark:text-slate-300">
                                                    Total Licenses
                                                </div>
                                            </div>
                                            <div>
                                                <div className="text-2xl font-bold text-green-600 dark:text-green-400">
                                                    {license_info.active_licenses}
                                                </div>
                                                <div className="text-sm text-slate-600 dark:text-slate-300">
                                                    Active
                                                </div>
                                            </div>
                                            <div>
                                                <div className="text-2xl font-bold text-blue-600 dark:text-blue-400">
                                                    {license_info.available_licenses}
                                                </div>
                                                <div className="text-sm text-slate-600 dark:text-slate-300">
                                                    Available
                                                </div>
                                            </div>
                                        </div>
                                    </CardContent>
                                </Card>
                            </div>
                        )}

                        {/* Actions */}
                        <div className="flex flex-col sm:flex-row gap-4 mt-8">
                            <Button 
                                asChild
                                variant="outline"
                                className="flex-1 h-12"
                            >
                                <Link href="/onboarding/welcome">
                                    <ArrowLeft className="mr-2 h-4 w-4" />
                                    Back
                                </Link>
                            </Button>
                            
                            <Button 
                                asChild
                                variant="outline"
                                className="flex-1 h-12"
                            >
                                <Link href="/onboarding/clinic-setup">
                                    Skip License Activation
                                    <ArrowRight className="ml-2 h-4 w-4" />
                                </Link>
                            </Button>
                        </div>

                        {/* Help */}
                        <div className="text-center mt-6">
                            <p className="text-sm text-slate-500 dark:text-slate-400">
                                Don't have a license key?{' '}
                                <Link 
                                    href="/contact"
                                    className="text-blue-600 dark:text-blue-400 hover:underline"
                                >
                                    Contact us
                                </Link>
                                {' '}to get one.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}
