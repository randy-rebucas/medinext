import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import { send } from '@/routes/verification';
import { type BreadcrumbItem } from '@/types';
import { Transition } from '@headlessui/react';
import { Form, Head, Link, usePage } from '@inertiajs/react';
import { PageProps as InertiaPageProps } from '@inertiajs/core';

import DeleteUser from '@/components/delete-user';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import { settings } from '@/routes';
import { User, Mail, Shield, CheckCircle, AlertCircle, Save } from 'lucide-react';

interface PageProps extends InertiaPageProps {
    auth: {
        user?: {
            id: number;
            name: string;
            email: string;
            email_verified_at?: string;
            role: string;
        };
    };
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Profile settings',
        href: settings.profile(),
    },
];

export default function Profile({ mustVerifyEmail, status }: { mustVerifyEmail: boolean; status?: string }) {
    const { auth } = usePage<PageProps>().props;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Profile Settings - Medinext">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&family=instrument-sans:400,500,600" rel="stylesheet" />
            </Head>

            <SettingsLayout>
                <div className="space-y-6">
                    {/* Modern Header */}
                    <div className="relative overflow-hidden rounded-2xl bg-gradient-to-r from-blue-600 to-purple-600 p-8 text-white shadow-xl">
                        <div className="absolute inset-0 bg-black/10"></div>
                        <div className="relative">
                            <h1 className="text-3xl font-bold tracking-tight">Profile Settings</h1>
                            <p className="mt-2 text-blue-100">
                                Update your personal information and account details
                            </p>
                        </div>
                        {/* Decorative elements */}
                        <div className="absolute -top-4 -right-4 w-24 h-24 bg-white/10 rounded-full"></div>
                        <div className="absolute -bottom-2 -left-2 w-16 h-16 bg-white/5 rounded-full"></div>
                    </div>

                    <div className="grid gap-6">
                            {/* Profile Information Card */}
                            <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                                <CardHeader>
                                    <CardTitle className="flex items-center text-slate-900 dark:text-white">
                                        <div className="p-2 bg-blue-500 rounded-lg mr-3">
                                            <User className="h-5 w-5 text-white" />
                                        </div>
                                        Profile Information
                                    </CardTitle>
                                    <CardDescription className="text-slate-600 dark:text-slate-300">
                                        Update your name and email address
                                    </CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <Form
                                        {...ProfileController.update.form()}
                                        options={{
                                            preserveScroll: true,
                                        }}
                                        className="space-y-6"
                                    >
                                        {({ processing, recentlySuccessful, errors }) => (
                                            <>
                                                <div className="grid gap-6 md:grid-cols-2">
                                                    <div className="space-y-2">
                                                        <Label htmlFor="name" className="text-slate-700 dark:text-slate-300 font-medium">Full Name</Label>
                                                        <div className="relative">
                                                            <User className="absolute left-3 top-3 h-4 w-4 text-slate-400" />
                                                            <Input
                                                                id="name"
                                                                className="pl-10 h-11 border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-700 dark:text-white"
                                                                defaultValue={auth.user?.name || ''}
                                                                name="name"
                                                                required
                                                                autoComplete="name"
                                                                placeholder="Enter your full name"
                                                            />
                                                        </div>
                                                        <InputError className="mt-2" message={errors.name} />
                                                    </div>

                                                    <div className="space-y-2">
                                                        <Label htmlFor="email" className="text-slate-700 dark:text-slate-300 font-medium">Email Address</Label>
                                                        <div className="relative">
                                                            <Mail className="absolute left-3 top-3 h-4 w-4 text-slate-400" />
                                                            <Input
                                                                id="email"
                                                                type="email"
                                                                className="pl-10 h-11 border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-700 dark:text-white"
                                                                defaultValue={auth.user?.email || ''}
                                                                name="email"
                                                                required
                                                                autoComplete="username"
                                                                placeholder="Enter your email address"
                                                            />
                                                        </div>
                                                        <InputError className="mt-2" message={errors.email} />
                                                    </div>
                                                </div>

                                                {mustVerifyEmail && auth.user?.email_verified_at === null && (
                                                    <div className="p-4 border border-yellow-200 dark:border-yellow-800 rounded-xl bg-yellow-50 dark:bg-yellow-900/20">
                                                        <div className="flex items-start space-x-3">
                                                            <AlertCircle className="h-5 w-5 text-yellow-600 dark:text-yellow-400 mt-0.5" />
                                                            <div className="flex-1">
                                                                <p className="text-sm text-yellow-800 dark:text-yellow-200">
                                                                    Your email address is unverified.{' '}
                                                                    <Link
                                                                        href={send()}
                                                                        as="button"
                                                                        className="font-medium underline decoration-yellow-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current dark:decoration-yellow-500"
                                                                    >
                                                                        Click here to resend the verification email.
                                                                    </Link>
                                                                </p>

                                                                {status === 'verification-link-sent' && (
                                                                    <div className="mt-2 flex items-center text-sm font-medium text-green-600 dark:text-green-400">
                                                                        <CheckCircle className="mr-2 h-4 w-4" />
                                                                        A new verification link has been sent to your email address.
                                                                    </div>
                                                                )}
                                                            </div>
                                                        </div>
                                                    </div>
                                                )}

                                                <div className="flex items-center justify-between pt-4 border-t border-slate-200 dark:border-slate-700">
                                                    <div className="flex items-center space-x-3">
                                                        <Button
                                                            disabled={processing}
                                                            className="bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white shadow-lg min-w-[120px]"
                                                        >
                                                            {processing ? (
                                                                <>
                                                                    <div className="mr-2 h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent" />
                                                                    Saving...
                                                                </>
                                                            ) : (
                                                                <>
                                                                    <Save className="mr-2 h-4 w-4" />
                                                                    Save Changes
                                                                </>
                                                            )}
                                                        </Button>

                                                        <Transition
                                                            show={recentlySuccessful}
                                                            enter="transition ease-in-out"
                                                            enterFrom="opacity-0"
                                                            leave="transition ease-in-out"
                                                            leaveTo="opacity-0"
                                                        >
                                                            <div className="flex items-center text-sm font-medium text-green-600 dark:text-green-400">
                                                                <CheckCircle className="mr-2 h-4 w-4" />
                                                                Profile updated successfully
                                                            </div>
                                                        </Transition>
                                                    </div>
                                                </div>
                                            </>
                                        )}
                                    </Form>
                                </CardContent>
                            </Card>

                            {/* Account Security Card */}
                            <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                                <CardHeader>
                                    <CardTitle className="flex items-center text-slate-900 dark:text-white">
                                        <div className="p-2 bg-red-500 rounded-lg mr-3">
                                            <Shield className="h-5 w-5 text-white" />
                                        </div>
                                        Account Security
                                    </CardTitle>
                                    <CardDescription className="text-slate-600 dark:text-slate-300">
                                        Manage your account security and data
                                    </CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <DeleteUser />
                                </CardContent>
                            </Card>
                    </div>
                </div>
            </SettingsLayout>
        </AppLayout>
    );
}
