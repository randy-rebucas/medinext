import RegisteredUserController from '@/actions/App/Http/Controllers/Auth/RegisteredUserController';
import { login } from '@/routes';
import { Form, Head, Link } from '@inertiajs/react';
import { LoaderCircle, User, Mail, Lock, Eye, EyeOff, Phone } from 'lucide-react';
import { useState } from 'react';

import InputError from '@/components/input-error';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

export default function Register() {
    const [showPassword, setShowPassword] = useState(false);
    const [showConfirmPassword, setShowConfirmPassword] = useState(false);

    return (
        <>
            <Head title="Create Account - Medinext">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&family=instrument-sans:400,500,600" rel="stylesheet" />
            </Head>

            {/* Background with gradient */}
            <div className="min-h-screen bg-gradient-to-br from-slate-50 via-white to-blue-50 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900 flex items-center justify-center px-4 py-12 sm:px-6 lg:px-8">
                <div className="w-full max-w-2xl">
                    {/* Logo and Header */}
                    <div className="text-center mb-8">
                        <div className="flex items-center justify-center space-x-2 mb-4">
                            <div className="h-10 w-10 rounded-lg bg-gradient-to-r from-blue-600 to-purple-600 flex items-center justify-center">
                                <span className="text-white font-bold text-lg">M</span>
                            </div>
                            <span className="text-2xl font-bold text-slate-900 dark:text-white">Medinext</span>
                        </div>
                        <h1 className="text-2xl font-bold text-slate-900 dark:text-white">
                            Create your account
                        </h1>
                        <p className="mt-2 text-sm text-slate-600 dark:text-slate-300">
                            Join thousands of healthcare professionals
                        </p>
                    </div>

                    {/* Registration Form Card */}
                    <div className="bg-white dark:bg-slate-800 rounded-2xl shadow-xl p-8 border border-slate-200 dark:border-slate-700">
                        <div className="space-y-6">
                                <div className="text-center mb-6">
                                    <h3 className="text-lg font-semibold text-slate-900 dark:text-white">Create Your Account</h3>
                                    <p className="text-sm text-slate-600 dark:text-slate-300">Start your 14-day free trial</p>
                                </div>

                                <Form
                                    {...RegisteredUserController.store.form()}
                                    resetOnSuccess={['password', 'password_confirmation']}
                                    disableWhileProcessing
                                    className="space-y-6"
                                >
                                    {({ processing, errors }) => (
                                        <>
                                            {/* Name Field */}
                                            <div className="space-y-2">
                                                <Label htmlFor="name" className="text-sm font-medium text-slate-700 dark:text-slate-300">
                                                    Full name
                                                </Label>
                                                <div className="relative">
                                                    <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                        <User className="h-5 w-5 text-slate-400" />
                                                    </div>
                                                    <Input
                                                        id="name"
                                                        type="text"
                                                        required
                                                        autoFocus
                                                        tabIndex={1}
                                                        autoComplete="name"
                                                        name="name"
                                                        placeholder="Enter your full name"
                                                        className="pl-10 h-12 border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-700 dark:text-white"
                                                    />
                                                </div>
                                                <InputError message={errors.name} />
                                            </div>

                                            {/* Email Field */}
                                            <div className="space-y-2">
                                                <Label htmlFor="email" className="text-sm font-medium text-slate-700 dark:text-slate-300">
                                                    Email address
                                                </Label>
                                                <div className="relative">
                                                    <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                        <Mail className="h-5 w-5 text-slate-400" />
                                                    </div>
                                                    <Input
                                                        id="email"
                                                        type="email"
                                                        required
                                                        tabIndex={2}
                                                        autoComplete="email"
                                                        name="email"
                                                        placeholder="email@example.com"
                                                        className="pl-10 h-12 border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-700 dark:text-white"
                                                    />
                                                </div>
                                                <InputError message={errors.email} />
                                            </div>

                                            {/* Phone Field */}
                                            <div className="space-y-2">
                                                <Label htmlFor="phone" className="text-sm font-medium text-slate-700 dark:text-slate-300">
                                                    Phone number
                                                </Label>
                                                <div className="relative">
                                                    <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                        <Phone className="h-5 w-5 text-slate-400" />
                                                    </div>
                                                    <Input
                                                        id="phone"
                                                        type="tel"
                                                        tabIndex={3}
                                                        autoComplete="tel"
                                                        name="phone"
                                                        placeholder="+1 (555) 123-4567"
                                                        className="pl-10 h-12 border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-700 dark:text-white"
                                                    />
                                                </div>
                                                <InputError message={errors.phone} />
                                            </div>

                                            {/* Password Field */}
                                            <div className="space-y-2">
                                                <Label htmlFor="password" className="text-sm font-medium text-slate-700 dark:text-slate-300">
                                                    Password
                                                </Label>
                                                <div className="relative">
                                                    <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                        <Lock className="h-5 w-5 text-slate-400" />
                                                    </div>
                                                    <Input
                                                        id="password"
                                                        type={showPassword ? "text" : "password"}
                                                        required
                                                        tabIndex={4}
                                                        autoComplete="new-password"
                                                        name="password"
                                                        placeholder="Create a strong password"
                                                        className="pl-10 pr-10 h-12 border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-700 dark:text-white"
                                                    />
                                                    <button
                                                        type="button"
                                                        className="absolute inset-y-0 right-0 pr-3 flex items-center"
                                                        onClick={() => setShowPassword(!showPassword)}
                                                    >
                                                        {showPassword ? (
                                                            <EyeOff className="h-5 w-5 text-slate-400 hover:text-slate-600" />
                                                        ) : (
                                                            <Eye className="h-5 w-5 text-slate-400 hover:text-slate-600" />
                                                        )}
                                                    </button>
                                                </div>
                                                <InputError message={errors.password} />
                                            </div>

                                            {/* Confirm Password Field */}
                                            <div className="space-y-2">
                                                <Label htmlFor="password_confirmation" className="text-sm font-medium text-slate-700 dark:text-slate-300">
                                                    Confirm password
                                                </Label>
                                                <div className="relative">
                                                    <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                        <Lock className="h-5 w-5 text-slate-400" />
                                                    </div>
                                                    <Input
                                                        id="password_confirmation"
                                                        type={showConfirmPassword ? "text" : "password"}
                                                        required
                                                        tabIndex={5}
                                                        autoComplete="new-password"
                                                        name="password_confirmation"
                                                        placeholder="Confirm your password"
                                                        className="pl-10 pr-10 h-12 border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-700 dark:text-white"
                                                    />
                                                    <button
                                                        type="button"
                                                        className="absolute inset-y-0 right-0 pr-3 flex items-center"
                                                        onClick={() => setShowConfirmPassword(!showConfirmPassword)}
                                                    >
                                                        {showConfirmPassword ? (
                                                            <EyeOff className="h-5 w-5 text-slate-400 hover:text-slate-600" />
                                                        ) : (
                                                            <Eye className="h-5 w-5 text-slate-400 hover:text-slate-600" />
                                                        )}
                                                    </button>
                                                </div>
                                                <InputError message={errors.password_confirmation} />
                                            </div>

                                            {/* Hidden role field - always clinic administrator */}
                                            <input type="hidden" name="role" value="admin" />

                                            {/* Submit Button */}
                                            <Button
                                                type="submit"
                                                className="w-full h-12 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-medium rounded-lg transition-all duration-200 shadow-lg hover:shadow-xl"
                                                tabIndex={6}
                                                disabled={processing}
                                            >
                                                {processing ? (
                                                    <>
                                                        <LoaderCircle className="h-4 w-4 animate-spin mr-2" />
                                                        Creating account...
                                                    </>
                                                ) : (
                                                    <>
                                                        <svg className="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                                        </svg>
                                                        Start Free Trial
                                                    </>
                                                )}
                                            </Button>
                                        </>
                                    )}
                                </Form>
                        </div>

                        {/* Sign In Link */}
                        <div className="mt-6 text-center">
                            <p className="text-sm text-slate-600 dark:text-slate-300">
                                Already have an account?{' '}
                                <TextLink
                                    href={login()}
                                    className="font-medium text-blue-600 hover:text-blue-500 dark:text-blue-400 dark:hover:text-blue-300"
                                    tabIndex={11}
                                >
                                    Sign in
                                </TextLink>
                            </p>
                        </div>
                    </div>

                    {/* Back to Home Link */}
                    <div className="mt-6 text-center">
                        <Link
                            href="/"
                            className="inline-flex items-center text-sm text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white transition-colors duration-200"
                        >
                            <svg className="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            Back to home
                        </Link>
                    </div>
                </div>
            </div>
        </>
    );
}
