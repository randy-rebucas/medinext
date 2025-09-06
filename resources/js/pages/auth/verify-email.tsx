// Components
import EmailVerificationNotificationController from '@/actions/App/Http/Controllers/Auth/EmailVerificationNotificationController';
import { logout } from '@/routes';
import { Form, Head, useForm } from '@inertiajs/react';
import { LoaderCircle } from 'lucide-react';

import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import AuthLayout from '@/layouts/auth-layout';

export default function VerifyEmail({ status }: { status?: string }) {
    const { post: logoutPost, processing: logoutProcessing } = useForm();

    const handleLogout = () => {
        logoutPost(logout().url);
    };

    return (
        <AuthLayout title="Verify email" description="Please verify your email address by clicking on the link we just emailed to you.">
            <Head title="Email verification" />

            {status === 'verification-link-sent' && (
                <div className="mb-4 text-center text-sm font-medium text-green-600">
                    A new verification link has been sent to the email address you provided during registration.
                </div>
            )}

            <Form {...EmailVerificationNotificationController.store.form()} className="space-y-6 text-center">
                {({ processing }) => (
                    <>
                        <Button disabled={processing} variant="secondary">
                            {processing && <LoaderCircle className="h-4 w-4 animate-spin" />}
                            Resend verification email
                        </Button>

                        <button
                            onClick={handleLogout}
                            disabled={logoutProcessing}
                            className="mx-auto block text-sm text-blue-600 hover:text-blue-800 underline disabled:opacity-50"
                        >
                            {logoutProcessing && <LoaderCircle className="h-4 w-4 animate-spin inline mr-1" />}
                            Log out
                        </button>
                    </>
                )}
            </Form>
        </AuthLayout>
    );
}
