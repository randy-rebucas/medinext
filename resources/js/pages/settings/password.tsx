import PasswordController from '@/actions/App/Http/Controllers/Settings/PasswordController';
import InputError from '@/components/input-error';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import { type BreadcrumbItem } from '@/types';
import { Transition } from '@headlessui/react';
import { Form, Head } from '@inertiajs/react';
import { useRef, useState } from 'react';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { settings } from '@/routes';
import { Lock, Eye, EyeOff, Shield, CheckCircle, Save } from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Password settings',
        href: settings.password(),
    },
];

export default function Password() {
    const passwordInput = useRef<HTMLInputElement>(null);
    const currentPasswordInput = useRef<HTMLInputElement>(null);
    const [showCurrentPassword, setShowCurrentPassword] = useState(false);
    const [showNewPassword, setShowNewPassword] = useState(false);
    const [showConfirmPassword, setShowConfirmPassword] = useState(false);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Password Settings - Medinext">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&family=instrument-sans:400,500,600" rel="stylesheet" />
            </Head>

            <SettingsLayout>
                <div className="space-y-6">
                    {/* Modern Header */}
                    <div className="relative overflow-hidden rounded-2xl bg-gradient-to-r from-blue-600 to-purple-600 p-8 text-white shadow-xl">
                        <div className="absolute inset-0 bg-black/10"></div>
                        <div className="relative">
                            <h1 className="text-3xl font-bold tracking-tight">Password Settings</h1>
                            <p className="mt-2 text-blue-100">
                                Update your password to keep your account secure
                            </p>
                        </div>
                        {/* Decorative elements */}
                        <div className="absolute -top-4 -right-4 w-24 h-24 bg-white/10 rounded-full"></div>
                        <div className="absolute -bottom-2 -left-2 w-16 h-16 bg-white/5 rounded-full"></div>
                    </div>

                    {/* Password Update Card */}
                        <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                            <CardHeader>
                                <CardTitle className="flex items-center text-slate-900 dark:text-white">
                                    <div className="p-2 bg-green-500 rounded-lg mr-3">
                                        <Shield className="h-5 w-5 text-white" />
                                    </div>
                                    Update Password
                                </CardTitle>
                                <CardDescription className="text-slate-600 dark:text-slate-300">
                                    Ensure your account is using a long, random password to stay secure
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <Form
                                    {...PasswordController.update.form()}
                                    options={{
                                        preserveScroll: true,
                                    }}
                                    resetOnError={['password', 'password_confirmation', 'current_password']}
                                    resetOnSuccess
                                    onError={(errors) => {
                                        if (errors.password) {
                                            passwordInput.current?.focus();
                                        }

                                        if (errors.current_password) {
                                            currentPasswordInput.current?.focus();
                                        }
                                    }}
                                    className="space-y-6"
                                >
                                    {({ errors, processing, recentlySuccessful }) => (
                                        <>
                                            <div className="space-y-2">
                                                <Label htmlFor="current_password" className="text-slate-700 dark:text-slate-300 font-medium">Current Password</Label>
                                                <div className="relative">
                                                    <Lock className="absolute left-3 top-3 h-4 w-4 text-slate-400" />
                                                    <Input
                                                        id="current_password"
                                                        ref={currentPasswordInput}
                                                        name="current_password"
                                                        type={showCurrentPassword ? "text" : "password"}
                                                        className="pl-10 pr-10 h-11 border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-700 dark:text-white"
                                                        autoComplete="current-password"
                                                        placeholder="Enter your current password"
                                                    />
                                                    <button
                                                        type="button"
                                                        className="absolute right-3 top-3 h-4 w-4 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300"
                                                        onClick={() => setShowCurrentPassword(!showCurrentPassword)}
                                                    >
                                                        {showCurrentPassword ? <EyeOff className="h-4 w-4" /> : <Eye className="h-4 w-4" />}
                                                    </button>
                                                </div>
                                                <InputError message={errors.current_password} />
                                            </div>

                                            <div className="space-y-2">
                                                <Label htmlFor="password" className="text-slate-700 dark:text-slate-300 font-medium">New Password</Label>
                                                <div className="relative">
                                                    <Lock className="absolute left-3 top-3 h-4 w-4 text-slate-400" />
                                                    <Input
                                                        id="password"
                                                        ref={passwordInput}
                                                        name="password"
                                                        type={showNewPassword ? "text" : "password"}
                                                        className="pl-10 pr-10 h-11 border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-700 dark:text-white"
                                                        autoComplete="new-password"
                                                        placeholder="Enter your new password"
                                                    />
                                                    <button
                                                        type="button"
                                                        className="absolute right-3 top-3 h-4 w-4 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300"
                                                        onClick={() => setShowNewPassword(!showNewPassword)}
                                                    >
                                                        {showNewPassword ? <EyeOff className="h-4 w-4" /> : <Eye className="h-4 w-4" />}
                                                    </button>
                                                </div>
                                                <InputError message={errors.password} />
                                            </div>

                                            <div className="space-y-2">
                                                <Label htmlFor="password_confirmation" className="text-slate-700 dark:text-slate-300 font-medium">Confirm New Password</Label>
                                                <div className="relative">
                                                    <Lock className="absolute left-3 top-3 h-4 w-4 text-slate-400" />
                                                    <Input
                                                        id="password_confirmation"
                                                        name="password_confirmation"
                                                        type={showConfirmPassword ? "text" : "password"}
                                                        className="pl-10 pr-10 h-11 border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-700 dark:text-white"
                                                        autoComplete="new-password"
                                                        placeholder="Confirm your new password"
                                                    />
                                                    <button
                                                        type="button"
                                                        className="absolute right-3 top-3 h-4 w-4 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300"
                                                        onClick={() => setShowConfirmPassword(!showConfirmPassword)}
                                                    >
                                                        {showConfirmPassword ? <EyeOff className="h-4 w-4" /> : <Eye className="h-4 w-4" />}
                                                    </button>
                                                </div>
                                                <InputError message={errors.password_confirmation} />
                                            </div>

                                            <div className="flex items-center justify-between pt-4 border-t border-slate-200 dark:border-slate-700">
                                                <div className="flex items-center space-x-3">
                                                    <Button
                                                        disabled={processing}
                                                        className="bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white shadow-lg min-w-[140px]"
                                                    >
                                                        {processing ? (
                                                            <>
                                                                <div className="mr-2 h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent" />
                                                                Updating...
                                                            </>
                                                        ) : (
                                                            <>
                                                                <Save className="mr-2 h-4 w-4" />
                                                                Update Password
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
                                                            Password updated successfully
                                                        </div>
                                                    </Transition>
                                                </div>
                                            </div>
                                        </>
                                    )}
                                </Form>
                            </CardContent>
                    </Card>
                </div>
            </SettingsLayout>
        </AppLayout>
    );
}
