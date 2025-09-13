import { Head, Link, useForm } from '@inertiajs/react';
import { CheckCircle, ArrowRight, Users, Calendar, FileText, Settings, Star } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Progress } from '@/components/ui/progress';

interface CompleteProps {
    user: {
        id: number;
        name: string;
        email: string;
    };
    clinic: {
        id: number;
        name: string;
        address: string;
    } | null;
    trial_status: {
        type: string;
        status: string;
        message: string;
        days_remaining?: number;
    };
}

export default function Complete({ user, clinic, trial_status }: CompleteProps) {
    const { post, processing } = useForm();
    const { post: postWithRedirect, processing: processingRedirect } = useForm({
        redirect_to: '/admin/clinic-settings'
    });

    const handleFinish = () => {
        post('/onboarding/finish');
    };

    const handleConfigureSettings = () => {
        postWithRedirect('/onboarding/finish');
    };

    const nextSteps = [
        {
            icon: Users,
            title: 'Add Team Members',
            description: 'Invite doctors, nurses, and staff to your clinic',
            action: 'Add Staff',
            href: '/admin/staff'
        },
        {
            icon: Calendar,
            title: 'Set Up Appointments',
            description: 'Configure your schedule and appointment types',
            action: 'Configure Schedule',
            href: '/admin/schedule'
        },
        {
            icon: FileText,
            title: 'Create Patient Records',
            description: 'Start adding patients and their medical information',
            action: 'Add Patients',
            href: '/patients'
        },
        {
            icon: Settings,
            title: 'Customize Settings',
            description: 'Configure clinic preferences and system settings',
            action: 'Open Settings',
            href: '/admin/clinic-settings'
        }
    ];

    return (
        <>
            <Head title="Setup Complete - Medinext" />

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
                            🎉 Setup Complete!
                        </h1>
                        <p className="text-xl text-slate-600 dark:text-slate-300 mb-6">
                            Your Medinext account is ready to use
                        </p>

                        {/* Success Status */}
                        <div className="inline-flex items-center px-4 py-2 rounded-full bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200">
                            <CheckCircle className="h-5 w-5 mr-2" />
                            <span className="font-medium">
                                {trial_status.type === 'licensed'
                                    ? 'License activated - Full access enabled'
                                    : trial_status.type === 'trial' && trial_status.days_remaining
                                        ? `${trial_status.days_remaining} days remaining in your free trial`
                                        : 'Account setup complete'
                                }
                            </span>
                        </div>
                    </div>

                    {/* Progress */}
                    <div className="max-w-2xl mx-auto mb-8">
                        <div className="flex items-center justify-between mb-2">
                            <span className="text-sm font-medium text-slate-700 dark:text-slate-300">Setup Progress</span>
                            <span className="text-sm text-slate-500 dark:text-slate-400">Complete!</span>
                        </div>
                        <Progress value={100} className="h-2" />
                    </div>

                    {/* Summary */}
                    <div className="max-w-2xl mx-auto mb-8">
                        <Card>
                            <CardHeader>
                                <CardTitle>Setup Summary</CardTitle>
                                <CardDescription>
                                    Here's what we've set up for you
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-4">
                                    <div className="flex items-center space-x-3">
                                        <CheckCircle className="h-5 w-5 text-green-500" />
                                        <div>
                                            <h4 className="font-medium text-slate-900 dark:text-white">Account Created</h4>
                                            <p className="text-sm text-slate-600 dark:text-slate-300">
                                                {user.name} ({user.email})
                                            </p>
                                        </div>
                                    </div>

                                    {clinic && (
                                        <div className="flex items-center space-x-3">
                                            <CheckCircle className="h-5 w-5 text-green-500" />
                                            <div>
                                                <h4 className="font-medium text-slate-900 dark:text-white">Clinic Setup</h4>
                                                <p className="text-sm text-slate-600 dark:text-slate-300">
                                                    {clinic.name} - {clinic.address}
                                                </p>
                                            </div>
                                        </div>
                                    )}

                                    <div className="flex items-center space-x-3">
                                        <CheckCircle className="h-5 w-5 text-green-500" />
                                        <div>
                                            <h4 className="font-medium text-slate-900 dark:text-white">Access Level</h4>
                                            <p className="text-sm text-slate-600 dark:text-slate-300">
                                                {trial_status.type === 'licensed'
                                                    ? 'Full licensed access'
                                                    : 'Free trial access'
                                                }
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    {/* Next Steps */}
                    <div className="max-w-4xl mx-auto mb-8">
                        <h2 className="text-2xl font-bold text-center text-slate-900 dark:text-white mb-6">
                            What's Next?
                        </h2>
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {nextSteps.map((step, index) => (
                                <Card key={index} className="hover:shadow-lg transition-shadow">
                                    <CardContent className="p-6">
                                        <div className="flex items-start space-x-4">
                                            <div className="flex-shrink-0">
                                                <div className="h-12 w-12 rounded-lg bg-blue-100 dark:bg-blue-900 flex items-center justify-center">
                                                    <step.icon className="h-6 w-6 text-blue-600 dark:text-blue-400" />
                                                </div>
                                            </div>
                                            <div className="flex-1">
                                                <h3 className="font-semibold text-slate-900 dark:text-white mb-2">
                                                    {step.title}
                                                </h3>
                                                <p className="text-slate-600 dark:text-slate-300 mb-4">
                                                    {step.description}
                                                </p>
                                                <Button asChild variant="outline" size="sm">
                                                    <Link href={step.href}>
                                                        {step.action}
                                                        <ArrowRight className="ml-2 h-4 w-4" />
                                                    </Link>
                                                </Button>
                                            </div>
                                        </div>
                                    </CardContent>
                                </Card>
                            ))}
                        </div>
                    </div>

                    {/* Getting Started Guide */}
                    <div className="max-w-2xl mx-auto mb-8">
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center">
                                    <Star className="h-5 w-5 mr-2" />
                                    Quick Start Guide
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-3">
                                    <div className="flex items-start space-x-3">
                                        <div className="h-6 w-6 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center flex-shrink-0">
                                            <span className="text-xs font-medium text-blue-600 dark:text-blue-400">1</span>
                                        </div>
                                        <p className="text-sm text-slate-600 dark:text-slate-300">
                                            <strong>Add your first patient:</strong> Go to Patients → Add New Patient to start building your patient database.
                                        </p>
                                    </div>
                                    <div className="flex items-start space-x-3">
                                        <div className="h-6 w-6 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center flex-shrink-0">
                                            <span className="text-xs font-medium text-blue-600 dark:text-blue-400">2</span>
                                        </div>
                                        <p className="text-sm text-slate-600 dark:text-slate-300">
                                            <strong>Schedule appointments:</strong> Use the Calendar to set up your first appointment.
                                        </p>
                                    </div>
                                    <div className="flex items-start space-x-3">
                                        <div className="h-6 w-6 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center flex-shrink-0">
                                            <span className="text-xs font-medium text-blue-600 dark:text-blue-400">3</span>
                                        </div>
                                        <p className="text-sm text-slate-600 dark:text-slate-300">
                                            <strong>Invite team members:</strong> Add doctors, nurses, and staff to collaborate.
                                        </p>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    {/* Actions */}
                    <div className="max-w-2xl mx-auto">
                        <div className="flex flex-col sm:flex-row gap-4">
                            <Button
                                onClick={handleFinish}
                                className="flex-1 h-12 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700"
                                disabled={processing}
                            >
                                {processing ? (
                                    <>
                                        <div className="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
                                        Finishing Setup...
                                    </>
                                ) : (
                                    <>
                                        <CheckCircle className="h-4 w-4 mr-2" />
                                        Go to Dashboard
                                    </>
                                )}
                            </Button>

                            <Button
                                onClick={handleConfigureSettings}
                                variant="outline"
                                className="flex-1 h-12"
                                disabled={processing || processingRedirect}
                            >
                                <Settings className="mr-2 h-4 w-4" />
                                {(processing || processingRedirect) ? 'Completing Setup...' : 'Configure Settings'}
                            </Button>
                        </div>
                    </div>

                    {/* Support */}
                    <div className="text-center mt-8">
                        <p className="text-sm text-slate-500 dark:text-slate-400">
                            Need help getting started?{' '}
                            <Link
                                href="/support"
                                className="text-blue-600 dark:text-blue-400 hover:underline"
                            >
                                Contact our support team
                            </Link>
                        </p>
                    </div>
                </div>
            </div>
        </>
    );
}
