import React, { useState } from 'react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { AlertCircle, CheckCircle, Key, Loader2 } from 'lucide-react';
import { useForm, router } from '@inertiajs/react';

interface LicenseActivationModalProps {
    trigger?: React.ReactNode;
    onSuccess?: () => void;
}

export function LicenseActivationModal({ trigger, onSuccess }: LicenseActivationModalProps) {
    const [open, setOpen] = useState(false);
    const [isValidating, setIsValidating] = useState(false);
    const [validationResult, setValidationResult] = useState<{
        valid: boolean;
        message: string;
    } | null>(null);

    const { data, setData, post, processing, errors, reset } = useForm({
        license_key: '',
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/license/activate', {
            onSuccess: () => {
                reset();
                setValidationResult(null);
                setOpen(false);
                onSuccess?.();
            },
        });
    };

    const handleValidateLicense = () => {
        if (!data.license_key.trim()) return;

        setIsValidating(true);
        
        router.post('/license/validate', { license_key: data.license_key }, {
            onSuccess: (page) => {
                const result = page.props.validationResult;
                if (result && result.valid) {
                    setValidationResult({
                        valid: true,
                        message: result.message || 'License key is valid and available.',
                    });
                } else {
                    setValidationResult({
                        valid: false,
                        message: result?.message || 'License key validation failed.',
                        errorCode: result?.error_code
                    });
                }
                setIsValidating(false);
            },
            onError: (errors) => {
                setValidationResult({
                    valid: false,
                    message: errors.license_key?.[0] || 'License key validation failed.',
                });
                setIsValidating(false);
            }
        });
    };

    const handleOpenChange = (newOpen: boolean) => {
        setOpen(newOpen);
        if (!newOpen) {
            reset();
            setValidationResult(null);
        }
    };

    return (
        <Dialog open={open} onOpenChange={handleOpenChange}>
            <DialogTrigger asChild>
                {trigger || (
                    <Button variant="outline" size="sm" className="gap-2">
                        <Key className="h-4 w-4" />
                        Activate License
                    </Button>
                )}
            </DialogTrigger>
            <DialogContent className="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle className="flex items-center gap-2">
                        <Key className="h-5 w-5 text-blue-600" />
                        Activate License
                    </DialogTitle>
                    <DialogDescription>
                        Enter your license key to activate your MediNext license and unlock all features.
                    </DialogDescription>
                </DialogHeader>

                <form onSubmit={handleSubmit} className="space-y-4">
                    <div className="space-y-2">
                        <Label htmlFor="license_key">License Key</Label>
                        <div className="flex gap-2">
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
                                size="sm"
                            >
                                {isValidating ? (
                                    <Loader2 className="h-4 w-4 animate-spin" />
                                ) : (
                                    'Validate'
                                )}
                            </Button>
                        </div>
                        {errors.license_key && (
                            <p className="text-sm text-red-600">{errors.license_key}</p>
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

                    <div className="flex gap-2 pt-2">
                        <Button
                            type="button"
                            variant="outline"
                            onClick={() => setOpen(false)}
                            className="flex-1"
                        >
                            Cancel
                        </Button>
                        <Button
                            type="submit"
                            disabled={processing || !data.license_key.trim()}
                            className="flex-1"
                        >
                            {processing ? (
                                <>
                                    <Loader2 className="h-4 w-4 animate-spin mr-2" />
                                    Activating...
                                </>
                            ) : (
                                'Activate License'
                            )}
                        </Button>
                    </div>
                </form>

                <div className="text-center pt-2 border-t">
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
            </DialogContent>
        </Dialog>
    );
}
