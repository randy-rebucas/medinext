import { Head, Link } from '@inertiajs/react';
import { CheckCircle, Clock, Star, ArrowRight, Building2, Users, Shield, Zap } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Progress } from '@/components/ui/progress';

interface WelcomeProps {
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
    clinic: {
        id: number;
        name: string;
        address: string;
    } | null;
}

export default function Welcome({ user, clinic, trial_status }: WelcomeProps) {
    const features = [
        {
            icon: Building2,
            title: 'Clinic Management',
            description: 'Manage your clinic information, staff, and operations'
        },
        {
            icon: Users,
            title: 'Patient Records',
            description: 'Comprehensive patient management and medical records'
        },
        {
            icon: Shield,
            title: 'Secure & Compliant',
            description: 'HIPAA compliant with enterprise-grade security'
        },
        {
            icon: Zap,
            title: 'Easy to Use',
            description: 'Intuitive interface designed for healthcare professionals'
        }
    ];

    return (
        <>
            <Head title="Welcome to Medinext" />
            
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
                            Welcome, {user.name}!
                        </h1>
                        <p className="text-xl text-slate-600 dark:text-slate-300 mb-6">
                            Let's get your account set up and ready to use
                        </p>
                        
                        {/* Trial Status */}
                        <div className="inline-flex items-center px-4 py-2 rounded-full bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200">
                            <CheckCircle className="h-5 w-5 mr-2" />
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
                            <span className="text-sm text-slate-500 dark:text-slate-400">Step 1 of 4</span>
                        </div>
                        <Progress value={25} className="h-2" />
                    </div>

                    {/* Clinic Info */}
                    {clinic && (
                        <div className="max-w-2xl mx-auto mb-8">
                            <Card>
                                <CardHeader>
                                    <CardTitle className="flex items-center">
                                        <Building2 className="h-5 w-5 mr-2" />
                                        Your Clinic
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="space-y-2">
                                        <h3 className="font-semibold text-slate-900 dark:text-white">{clinic.name}</h3>
                                        <p className="text-slate-600 dark:text-slate-300">{clinic.address}</p>
                                    </div>
                                </CardContent>
                            </Card>
                        </div>
                    )}

                    {/* Features */}
                    <div className="max-w-4xl mx-auto mb-8">
                        <h2 className="text-2xl font-bold text-center text-slate-900 dark:text-white mb-6">
                            What you can do with Medinext
                        </h2>
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {features.map((feature, index) => (
                                <Card key={index} className="hover:shadow-lg transition-shadow">
                                    <CardContent className="p-6">
                                        <div className="flex items-start space-x-4">
                                            <div className="flex-shrink-0">
                                                <div className="h-12 w-12 rounded-lg bg-blue-100 dark:bg-blue-900 flex items-center justify-center">
                                                    <feature.icon className="h-6 w-6 text-blue-600 dark:text-blue-400" />
                                                </div>
                                            </div>
                                            <div>
                                                <h3 className="font-semibold text-slate-900 dark:text-white mb-2">
                                                    {feature.title}
                                                </h3>
                                                <p className="text-slate-600 dark:text-slate-300">
                                                    {feature.description}
                                                </p>
                                            </div>
                                        </div>
                                    </CardContent>
                                </Card>
                            ))}
                        </div>
                    </div>

                    {/* Next Steps */}
                    <div className="max-w-2xl mx-auto">
                        <Card>
                            <CardHeader>
                                <CardTitle>Next Steps</CardTitle>
                                <CardDescription>
                                    Complete these steps to get the most out of your Medinext account
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="flex items-center space-x-3">
                                    <div className="h-8 w-8 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center">
                                        <span className="text-sm font-medium text-blue-600 dark:text-blue-400">1</span>
                                    </div>
                                    <div className="flex-1">
                                        <h4 className="font-medium text-slate-900 dark:text-white">Activate License (Optional)</h4>
                                        <p className="text-sm text-slate-600 dark:text-slate-300">
                                            Enter your license key to unlock all features
                                        </p>
                                    </div>
                                </div>
                                
                                <div className="flex items-center space-x-3">
                                    <div className="h-8 w-8 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center">
                                        <span className="text-sm font-medium text-slate-500 dark:text-slate-400">2</span>
                                    </div>
                                    <div className="flex-1">
                                        <h4 className="font-medium text-slate-500 dark:text-slate-400">Complete Clinic Setup</h4>
                                        <p className="text-sm text-slate-500 dark:text-slate-400">
                                            Add detailed clinic information and settings
                                        </p>
                                    </div>
                                </div>
                                
                                <div className="flex items-center space-x-3">
                                    <div className="h-8 w-8 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center">
                                        <span className="text-sm font-medium text-slate-500 dark:text-slate-400">3</span>
                                    </div>
                                    <div className="flex-1">
                                        <h4 className="font-medium text-slate-500 dark:text-slate-400">Add Team Members</h4>
                                        <p className="text-sm text-slate-500 dark:text-slate-400">
                                            Invite staff and assign roles
                                        </p>
                                    </div>
                                </div>
                                
                                <div className="flex items-center space-x-3">
                                    <div className="h-8 w-8 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center">
                                        <span className="text-sm font-medium text-slate-500 dark:text-slate-400">4</span>
                                    </div>
                                    <div className="flex-1">
                                        <h4 className="font-medium text-slate-500 dark:text-slate-400">Start Using Medinext</h4>
                                        <p className="text-sm text-slate-500 dark:text-slate-400">
                                            Begin managing patients and appointments
                                        </p>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    {/* Actions */}
                    <div className="max-w-2xl mx-auto mt-8 flex flex-col sm:flex-row gap-4">
                        <Button 
                            asChild
                            className="flex-1 h-12 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700"
                        >
                            <Link href="/onboarding/license">
                                Activate License
                                <ArrowRight className="ml-2 h-4 w-4" />
                            </Link>
                        </Button>
                        
                        <Button 
                            asChild
                            variant="outline"
                            className="flex-1 h-12"
                        >
                            <Link href="/onboarding/clinic-setup">
                                Skip & Continue Setup
                                <ArrowRight className="ml-2 h-4 w-4" />
                            </Link>
                        </Button>
                    </div>

                    {/* Skip to Dashboard */}
                    <div className="text-center mt-6">
                        <Link 
                            href="/dashboard"
                            className="text-sm text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200"
                        >
                            Skip setup and go to dashboard
                        </Link>
                    </div>
                </div>
            </div>
        </>
    );
}
