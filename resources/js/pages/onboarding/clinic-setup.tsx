import { Head, Link, useForm } from '@inertiajs/react';
import { Building2, ArrowRight, ArrowLeft, MapPin, Phone, Mail, Globe, FileText } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Progress } from '@/components/ui/progress';
import InputError from '@/components/input-error';

interface ClinicSetupProps {
    user: {
        id: number;
        name: string;
        email: string;
    };
    clinic: {
        id: number;
        name: string;
        address: string;
        phone: string;
        email: string;
        website?: string;
        description?: string;
    } | null;
}

export default function ClinicSetup({ user, clinic }: ClinicSetupProps) {
    const { data, setData, post, processing, errors } = useForm({
        name: clinic?.name || '',
        address: clinic?.address || '',
        phone: clinic?.phone || '',
        email: clinic?.email || '',
        website: clinic?.website || '',
        description: clinic?.description || '',
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/onboarding/clinic-setup');
    };

    return (
        <>
            <Head title="Clinic Setup - Medinext" />
            
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
                            Complete Your Clinic Setup
                        </h1>
                        <p className="text-xl text-slate-600 dark:text-slate-300">
                            Add detailed information about your clinic
                        </p>
                    </div>

                    {/* Progress */}
                    <div className="max-w-2xl mx-auto mb-8">
                        <div className="flex items-center justify-between mb-2">
                            <span className="text-sm font-medium text-slate-700 dark:text-slate-300">Setup Progress</span>
                            <span className="text-sm text-slate-500 dark:text-slate-400">Step 3 of 4</span>
                        </div>
                        <Progress value={75} className="h-2" />
                    </div>

                    <div className="max-w-2xl mx-auto">
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center">
                                    <Building2 className="h-5 w-5 mr-2" />
                                    Clinic Information
                                </CardTitle>
                                <CardDescription>
                                    Provide detailed information about your clinic to help patients find and contact you
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <form onSubmit={handleSubmit} className="space-y-6">
                                    {/* Clinic Name */}
                                    <div className="space-y-2">
                                        <Label htmlFor="name" className="text-sm font-medium text-slate-700 dark:text-slate-300">
                                            Clinic Name *
                                        </Label>
                                        <div className="relative">
                                            <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <Building2 className="h-5 w-5 text-slate-400" />
                                            </div>
                                            <Input
                                                id="name"
                                                type="text"
                                                required
                                                autoFocus
                                                value={data.name}
                                                onChange={(e) => setData('name', e.target.value)}
                                                placeholder="Enter your clinic name"
                                                className="pl-10 h-12 border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-700 dark:text-white"
                                            />
                                        </div>
                                        <InputError message={errors.name} />
                                    </div>

                                    {/* Address */}
                                    <div className="space-y-2">
                                        <Label htmlFor="address" className="text-sm font-medium text-slate-700 dark:text-slate-300">
                                            Address *
                                        </Label>
                                        <div className="relative">
                                            <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <MapPin className="h-5 w-5 text-slate-400" />
                                            </div>
                                            <Input
                                                id="address"
                                                type="text"
                                                required
                                                value={data.address}
                                                onChange={(e) => setData('address', e.target.value)}
                                                placeholder="Enter your clinic address"
                                                className="pl-10 h-12 border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-700 dark:text-white"
                                            />
                                        </div>
                                        <InputError message={errors.address} />
                                    </div>

                                    {/* Phone and Email */}
                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div className="space-y-2">
                                            <Label htmlFor="phone" className="text-sm font-medium text-slate-700 dark:text-slate-300">
                                                Phone Number *
                                            </Label>
                                            <div className="relative">
                                                <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <Phone className="h-5 w-5 text-slate-400" />
                                                </div>
                                                <Input
                                                    id="phone"
                                                    type="tel"
                                                    required
                                                    value={data.phone}
                                                    onChange={(e) => setData('phone', e.target.value)}
                                                    placeholder="+1 (555) 123-4567"
                                                    className="pl-10 h-12 border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-700 dark:text-white"
                                                />
                                            </div>
                                            <InputError message={errors.phone} />
                                        </div>

                                        <div className="space-y-2">
                                            <Label htmlFor="email" className="text-sm font-medium text-slate-700 dark:text-slate-300">
                                                Email Address *
                                            </Label>
                                            <div className="relative">
                                                <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <Mail className="h-5 w-5 text-slate-400" />
                                                </div>
                                                <Input
                                                    id="email"
                                                    type="email"
                                                    required
                                                    value={data.email}
                                                    onChange={(e) => setData('email', e.target.value)}
                                                    placeholder="clinic@example.com"
                                                    className="pl-10 h-12 border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-700 dark:text-white"
                                                />
                                            </div>
                                            <InputError message={errors.email} />
                                        </div>
                                    </div>

                                    {/* Website */}
                                    <div className="space-y-2">
                                        <Label htmlFor="website" className="text-sm font-medium text-slate-700 dark:text-slate-300">
                                            Website
                                        </Label>
                                        <div className="relative">
                                            <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <Globe className="h-5 w-5 text-slate-400" />
                                            </div>
                                            <Input
                                                id="website"
                                                type="url"
                                                value={data.website}
                                                onChange={(e) => setData('website', e.target.value)}
                                                placeholder="https://your-clinic.com"
                                                className="pl-10 h-12 border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-700 dark:text-white"
                                            />
                                        </div>
                                        <InputError message={errors.website} />
                                    </div>

                                    {/* Description */}
                                    <div className="space-y-2">
                                        <Label htmlFor="description" className="text-sm font-medium text-slate-700 dark:text-slate-300">
                                            Description
                                        </Label>
                                        <div className="relative">
                                            <div className="absolute top-3 left-3 flex items-center pointer-events-none">
                                                <FileText className="h-5 w-5 text-slate-400" />
                                            </div>
                                            <Textarea
                                                id="description"
                                                value={data.description}
                                                onChange={(e) => setData('description', e.target.value)}
                                                placeholder="Describe your clinic, services, and specialties..."
                                                className="pl-10 min-h-[100px] border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-700 dark:text-white"
                                            />
                                        </div>
                                        <InputError message={errors.description} />
                                        <p className="text-sm text-slate-500 dark:text-slate-400">
                                            This will help patients understand what services you offer
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
                                                Updating Clinic...
                                            </>
                                        ) : (
                                            <>
                                                <Building2 className="h-4 w-4 mr-2" />
                                                Update Clinic Information
                                            </>
                                        )}
                                    </Button>
                                </form>
                            </CardContent>
                        </Card>

                        {/* Tips */}
                        <div className="mt-8">
                            <Card>
                                <CardHeader>
                                    <CardTitle className="text-base">Tips for Better Clinic Setup</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="space-y-3">
                                        <div className="flex items-start space-x-3">
                                            <div className="h-2 w-2 rounded-full bg-blue-500 mt-2"></div>
                                            <p className="text-sm text-slate-600 dark:text-slate-300">
                                                Use a professional email address for your clinic
                                            </p>
                                        </div>
                                        <div className="flex items-start space-x-3">
                                            <div className="h-2 w-2 rounded-full bg-blue-500 mt-2"></div>
                                            <p className="text-sm text-slate-600 dark:text-slate-300">
                                                Include your website if you have one to help with online presence
                                            </p>
                                        </div>
                                        <div className="flex items-start space-x-3">
                                            <div className="h-2 w-2 rounded-full bg-blue-500 mt-2"></div>
                                            <p className="text-sm text-slate-600 dark:text-slate-300">
                                                Write a clear description of your services and specialties
                                            </p>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        </div>

                        {/* Actions */}
                        <div className="flex flex-col sm:flex-row gap-4 mt-8">
                            <Button 
                                asChild
                                variant="outline"
                                className="flex-1 h-12"
                            >
                                <Link href="/onboarding/license">
                                    <ArrowLeft className="mr-2 h-4 w-4" />
                                    Back
                                </Link>
                            </Button>
                            
                            <Button 
                                asChild
                                className="flex-1 h-12 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700"
                            >
                                <Link href="/onboarding/complete">
                                    Continue to Team Setup
                                    <ArrowRight className="ml-2 h-4 w-4" />
                                </Link>
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}
