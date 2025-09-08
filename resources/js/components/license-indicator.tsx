import React from 'react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { AlertCircle, Clock, Key, Crown, Zap } from 'lucide-react';
import { Link } from '@inertiajs/react';
import { LicenseActivationModal } from './license-activation-modal';

interface AccessStatus {
    type: 'trial' | 'licensed' | 'none';
    status: 'active' | 'expired' | 'inactive';
    message: string;
    expires_at?: string;
    days_remaining?: number;
    days_expired?: number;
}

interface LicenseIndicatorProps {
    accessStatus: AccessStatus;
    compact?: boolean;
}

export function LicenseIndicator({ accessStatus, compact = false }: LicenseIndicatorProps) {
    const getStatusIcon = () => {
        switch (accessStatus.status) {
            case 'active':
                if (accessStatus.type === 'licensed') {
                    return <Crown className="h-4 w-4 text-yellow-500" />;
                }
                return <Zap className="h-4 w-4 text-blue-500" />;
            case 'expired':
                return <AlertCircle className="h-4 w-4 text-red-500" />;
            default:
                return <Clock className="h-4 w-4 text-gray-500" />;
        }
    };

    const getStatusColor = () => {
        switch (accessStatus.status) {
            case 'active':
                if (accessStatus.type === 'licensed') {
                    return 'bg-yellow-100 text-yellow-800 border-yellow-200';
                }
                return 'bg-blue-100 text-blue-800 border-blue-200';
            case 'expired':
                return 'bg-red-100 text-red-800 border-red-200';
            default:
                return 'bg-gray-100 text-gray-800 border-gray-200';
        }
    };

    const getStatusText = () => {
        if (accessStatus.type === 'licensed') {
            return 'Licensed';
        }
        if (accessStatus.type === 'trial') {
            return 'Trial';
        }
        return 'No Access';
    };

    const getTooltipContent = () => {
        if (accessStatus.type === 'licensed' && accessStatus.expires_at) {
            const expiresDate = new Date(accessStatus.expires_at).toLocaleDateString();
            return `License expires on ${expiresDate}`;
        }
        if (accessStatus.type === 'trial' && accessStatus.expires_at) {
            const expiresDate = new Date(accessStatus.expires_at).toLocaleDateString();
            if (accessStatus.status === 'active' && accessStatus.days_remaining) {
                return `Trial expires on ${expiresDate} (${accessStatus.days_remaining} days remaining)`;
            }
            if (accessStatus.status === 'expired' && accessStatus.days_expired) {
                return `Trial expired ${accessStatus.days_expired} days ago`;
            }
            return `Trial expires on ${expiresDate}`;
        }
        return accessStatus.message;
    };

    const getDaysDisplay = () => {
        if (accessStatus.type === 'licensed' && accessStatus.expires_at) {
            const expiresDate = new Date(accessStatus.expires_at);
            const today = new Date();
            const diffTime = expiresDate.getTime() - today.getTime();
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

            if (diffDays > 0) {
                return `${diffDays} days left`;
            } else if (diffDays === 0) {
                return 'Expires today';
            } else {
                return `Expired ${Math.abs(diffDays)} days ago`;
            }
        }

        if (accessStatus.type === 'trial') {
            if (accessStatus.status === 'active' && accessStatus.days_remaining) {
                return `${accessStatus.days_remaining} days left`;
            }
            if (accessStatus.status === 'expired' && accessStatus.days_expired) {
                return `Expired ${accessStatus.days_expired} days ago`;
            }
        }

        return null;
    };

    if (compact) {
        const daysDisplay = getDaysDisplay();

        return (
            <TooltipProvider>
                <Tooltip>
                    <TooltipTrigger asChild>
                        <Link
                            href="/license/activate"
                            className="flex items-center gap-1 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-md px-2 py-1 transition-colors cursor-pointer"
                        >
                            {getStatusIcon()}
                            <Badge
                                variant="outline"
                                className={`text-xs ${getStatusColor()}`}
                            >
                                {getStatusText()}
                            </Badge>
                            {daysDisplay && (
                                <span className="text-xs text-muted-foreground">
                                    {daysDisplay}
                                </span>
                            )}
                        </Link>
                    </TooltipTrigger>
                    <TooltipContent>
                        <p>{getTooltipContent()}</p>
                        <p className="text-xs text-muted-foreground mt-1">Click to activate license</p>
                    </TooltipContent>
                </Tooltip>
            </TooltipProvider>
        );
    }

    const daysDisplay = getDaysDisplay();

    return (
        <Card className="border-l-4 border-l-blue-500 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-950/20 dark:to-indigo-950/20">
            <CardContent className="p-4">
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-3">
                        {getStatusIcon()}
                        <div>
                            <div className="flex items-center gap-2">
                                <span className="font-medium text-sm">
                                    {accessStatus.type === 'licensed' ? 'Licensed User' : 'Free Trial'}
                                </span>
                                <Badge
                                    variant="outline"
                                    className={`text-xs ${getStatusColor()}`}
                                >
                                    {getStatusText()}
                                </Badge>
                                {daysDisplay && (
                                    <Badge
                                        variant="secondary"
                                        className="text-xs bg-orange-100 text-orange-800 border-orange-200"
                                    >
                                        {daysDisplay}
                                    </Badge>
                                )}
                            </div>
                            <p className="text-xs text-muted-foreground mt-1">
                                {getTooltipContent()}
                            </p>
                        </div>
                    </div>
                    <div className="flex gap-2">
                        {accessStatus.status === 'expired' && (
                            <LicenseActivationModal
                                trigger={
                                    <Button size="sm" variant="outline" className="gap-2">
                                        <Key className="h-3 w-3" />
                                        Activate
                                    </Button>
                                }
                            />
                        )}
                        {accessStatus.status === 'active' && accessStatus.type === 'trial' && (
                            <LicenseActivationModal
                                trigger={
                                    <Button size="sm" variant="default" className="gap-2">
                                        <Key className="h-3 w-3" />
                                        Upgrade
                                    </Button>
                                }
                            />
                        )}
                    </div>
                </div>
            </CardContent>
        </Card>
    );
}

// Compact version for header/sidebar
export function LicenseIndicatorCompact({ accessStatus }: { accessStatus: AccessStatus }) {
    return <LicenseIndicator accessStatus={accessStatus} compact={true} />;
}
