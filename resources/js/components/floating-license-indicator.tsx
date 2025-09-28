import { LicenseIndicatorCompact } from '@/components/license-indicator';
import { LicenseActivationModal } from '@/components/license-activation-modal';
import { Button } from '@/components/ui/button';
import { Key, ChevronUp, ChevronDown } from 'lucide-react';
import { useUserAccessStatus } from '@/hooks/use-user-access-status';
import { useState } from 'react';

export function FloatingLicenseIndicator() {
    const { accessStatus } = useUserAccessStatus();
    const [isExpanded, setIsExpanded] = useState(false);

    if (!accessStatus) {
        return null;
    }

    return (
        <div className="fixed bottom-4 right-4 z-10">
            <div className="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg shadow-lg min-w-[200px]">
                {/* Toggle Button */}
                <div className="p-2 border-b border-slate-200 dark:border-slate-700">
                    <Button
                        variant="ghost"
                        size="sm"
                        onClick={() => setIsExpanded(!isExpanded)}
                        className="w-full justify-between h-8 text-xs hover:bg-slate-50 dark:hover:bg-slate-700"
                    >
                        <span className="font-medium">License Status</span>
                        {isExpanded ? (
                            <ChevronDown className="h-3 w-3" />
                        ) : (
                            <ChevronUp className="h-3 w-3" />
                        )}
                    </Button>
                </div>
                
                {/* Expandable Content */}
                {isExpanded && (
                    <div className="p-3 space-y-2">
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
                )}
            </div>
        </div>
    );
}
