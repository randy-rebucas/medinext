import EmailVerificationNotificationController from '@/actions/App/Http/Controllers/Auth/EmailVerificationNotificationController';
import { logout } from '@/routes';
import { Form, Head, useForm, Link } from '@inertiajs/react';
import { LoaderCircle, Mail, CheckCircle, ArrowLeft, LogOut } from 'lucide-react';

import { Button } from '@/components/ui/button';

export default function VerifyEmail({ status }: { status?: string }) {
    const { post: logoutPost, processing: logoutProcessing } = useForm();

    const handleLogout = () => {
        logoutPost(logout().url);
    };

    return (
        <>
            <Head title="Verify Email - Medinext">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&family=instrument-sans:400,500,600" rel="stylesheet" />
            </Head>

            {/* Background with gradient */}
            <div className="min-h-screen bg-gradient-to-br from-slate-50 via-white to-blue-50 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900 flex items-center justify-center px-4 py-12 sm:px-6 lg:px-8">
                <div className="w-full max-w-md">
                    {/* Logo and Header */}
                    <div className="text-center mb-8">
                        <div className="flex items-center justify-center space-x-2 mb-4">
                            <div className="h-10 w-10 rounded-lg bg-gradient-to-r from-blue-600 to-purple-600 flex items-center justify-center">
                                <span className="text-white font-bold text-lg">M</span>
                            </div>
                            <span className="text-2xl font-bold text-slate-900 dark:text-white">Medinext</span>
                        </div>
                        <div className="flex items-center justify-center mb-4">
                            <div className="flex items-center justify-center w-16 h-16 bg-blue-100 dark:bg-blue-900 rounded-full">
                                <Mail className="w-8 h-8 text-blue-600 dark:text-blue-400" />
                            </div>
                        </div>
                        <h1 className="text-2xl font-bold text-slate-900 dark:text-white">
                            Verify your email
                        </h1>
                        <p className="mt-2 text-sm text-slate-600 dark:text-slate-300">
                            We've sent a verification link to your email address
                        </p>
                    </div>

                    {/* Verification Card */}
                    <div className="bg-white dark:bg-slate-800 rounded-2xl shadow-xl p-8 border border-slate-200 dark:border-slate-700">
                        {/* Status Message */}
                        {status === 'verification-link-sent' && (
                            <div className="mb-6 p-4 text-center text-sm font-medium text-green-600 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-200 dark:border-green-800">
                                <div className="flex items-center justify-center mb-2">
                                    <CheckCircle className="w-5 h-5 text-green-600" />
                                </div>
                                A new verification link has been sent to your email address.
                            </div>
                        )}

                        <div className="text-center space-y-6">
                            {/* Instructions */}
                            <div className="space-y-3">
                                <p className="text-sm text-slate-600 dark:text-slate-300">
                                    Please check your email and click the verification link to activate your account.
                                </p>
                                <p className="text-xs text-slate-500 dark:text-slate-400">
                                    Didn't receive the email? Check your spam folder or request a new one.
                                </p>
                            </div>

                            {/* Resend Button */}
                            <Form {...EmailVerificationNotificationController.store.form()} className="space-y-4">
                                {({ processing }) => (
                                    <Button
                                        type="submit"
                                        disabled={processing}
                                        className="w-full h-12 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-medium rounded-lg transition-all duration-200 shadow-lg hover:shadow-xl"
                                    >
                                        {processing ? (
                                            <>
                                                <LoaderCircle className="h-4 w-4 animate-spin mr-2" />
                                                Sending...
                                            </>
                                        ) : (
                                            <>
                                                <Mail className="w-4 h-4 mr-2" />
                                                Resend verification email
                                            </>
                                        )}
                                    </Button>
                                )}
                            </Form>

                            {/* Logout Button */}
                            <button
                                onClick={handleLogout}
                                disabled={logoutProcessing}
                                className="w-full inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors duration-200 disabled:opacity-50"
                            >
                                {logoutProcessing ? (
                                    <>
                                        <LoaderCircle className="h-4 w-4 animate-spin mr-2" />
                                        Signing out...
                                    </>
                                ) : (
                                    <>
                                        <LogOut className="w-4 h-4 mr-2" />
                                        Sign out
                                    </>
                                )}
                            </button>
                        </div>
                    </div>

                    {/* Back to Home Link */}
                    <div className="mt-6 text-center">
                        <Link
                            href="/"
                            className="inline-flex items-center text-sm text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white transition-colors duration-200"
                        >
                            <ArrowLeft className="w-4 h-4 mr-1" />
                            Back to home
                        </Link>
                    </div>
                </div>
            </div>
        </>
    );
}
