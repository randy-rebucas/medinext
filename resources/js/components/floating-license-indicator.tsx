import { LicenseIndicatorCompact } from '@/components/license-indicator';
import { LicenseActivationModal } from '@/components/license-activation-modal';
import { Button } from '@/components/ui/button';
import { Key } from 'lucide-react';
import { useUserAccessStatus } from '@/hooks/use-user-access-status';

export function FloatingLicenseIndicator() {
    const { accessStatus } = useUserAccessStatus();

    if (!accessStatus) {
        return null;
    }

    return (
        <div className="fixed bottom-4 right-4 z-50">
            <div className="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg shadow-lg p-3 space-y-2 min-w-[200px]">
                <LicenseIndicatorCompact accessStatus={accessStatus} />
                {(accessStatus.status === 'expired' || (accessStatus.status === 'active' && accessStatus.type === 'trial')) && (
                    <LicenseActivationModal
                        trigger={
                            <Button size="sm" variant="outline" className="w-full gap-2 text-xs">
                                <Key className="h-3 w-3" />
                                {accessStatus.status === 'expired' ? 'Activate License' : 'Upgrade to License'}
                            </Button>
                        }
                    />
                )}
            </div>
        </div>
    );
}
