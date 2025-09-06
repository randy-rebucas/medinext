import { type ReactNode } from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { AlertCircle, Lock } from 'lucide-react';
import { type PermissionName } from '@/types';

interface PermissionGuardProps {
    permission: PermissionName;
    permissions: string[];
    children: ReactNode;
    fallback?: ReactNode;
    showFallback?: boolean;
}

export function PermissionGuard({ 
    permission, 
    permissions, 
    children, 
    fallback,
    showFallback = true 
}: PermissionGuardProps) {
    const hasPermission = permissions.includes(permission);

    if (hasPermission) {
        return <>{children}</>;
    }

    if (!showFallback) {
        return null;
    }

    if (fallback) {
        return <>{fallback}</>;
    }

    return (
        <Card>
            <CardContent className="flex flex-col items-center justify-center py-8">
                <Lock className="h-12 w-12 text-muted-foreground mb-4" />
                <h3 className="text-lg font-semibold mb-2">Access Restricted</h3>
                <p className="text-muted-foreground text-center">
                    You don't have permission to access this feature.
                </p>
            </CardContent>
        </Card>
    );
}

// Role-based permission guard
interface RoleGuardProps {
    allowedRoles: string[];
    userRole: string;
    children: ReactNode;
    fallback?: ReactNode;
    showFallback?: boolean;
}

export function RoleGuard({ 
    allowedRoles, 
    userRole, 
    children, 
    fallback,
    showFallback = true 
}: RoleGuardProps) {
    const hasRole = allowedRoles.includes(userRole);

    if (hasRole) {
        return <>{children}</>;
    }

    if (!showFallback) {
        return null;
    }

    if (fallback) {
        return <>{fallback}</>;
    }

    return (
        <Card>
            <CardContent className="flex flex-col items-center justify-center py-8">
                <AlertCircle className="h-12 w-12 text-muted-foreground mb-4" />
                <h3 className="text-lg font-semibold mb-2">Role Access Required</h3>
                <p className="text-muted-foreground text-center">
                    This feature requires one of the following roles: {allowedRoles.join(', ')}
                </p>
            </CardContent>
        </Card>
    );
}

// Permission-based button component
interface PermissionButtonProps {
    permission: PermissionName;
    permissions: string[];
    children: ReactNode;
    disabled?: boolean;
    className?: string;
    onClick?: () => void;
    variant?: 'default' | 'destructive' | 'outline' | 'secondary' | 'ghost' | 'link';
    size?: 'default' | 'sm' | 'lg' | 'icon';
}

export function PermissionButton({ 
    permission, 
    permissions, 
    children, 
    disabled = false,
    className = '',
    onClick,
    variant = 'default',
    size = 'default'
}: PermissionButtonProps) {
    const hasPermission = permissions.includes(permission);

    if (!hasPermission) {
        return null;
    }

    return (
        <button
            className={className}
            disabled={disabled}
            onClick={onClick}
            data-variant={variant}
            data-size={size}
        >
            {children}
        </button>
    );
}

// Permission-based link component
interface PermissionLinkProps {
    permission: PermissionName;
    permissions: string[];
    href: string;
    children: ReactNode;
    className?: string;
}

export function PermissionLink({ 
    permission, 
    permissions, 
    href, 
    children, 
    className = '' 
}: PermissionLinkProps) {
    const hasPermission = permissions.includes(permission);

    if (!hasPermission) {
        return null;
    }

    return (
        <a href={href} className={className}>
            {children}
        </a>
    );
}

// Hook for checking permissions
export function usePermissions(permissions: string[]) {
    const hasPermission = (permission: PermissionName) => {
        return permissions.includes(permission);
    };

    const hasAnyPermission = (permissionList: PermissionName[]) => {
        return permissionList.some(permission => permissions.includes(permission));
    };

    const hasAllPermissions = (permissionList: PermissionName[]) => {
        return permissionList.every(permission => permissions.includes(permission));
    };

    return {
        hasPermission,
        hasAnyPermission,
        hasAllPermissions,
        permissions
    };
}

// Hook for role checking
export function useRole(userRole: string) {
    const isRole = (role: string) => {
        return userRole === role;
    };

    const isAnyRole = (roleList: string[]) => {
        return roleList.includes(userRole);
    };

    const isAdmin = () => {
        return ['superadmin', 'admin'].includes(userRole);
    };

    const isStaff = () => {
        return ['superadmin', 'admin', 'doctor', 'receptionist'].includes(userRole);
    };

    return {
        isRole,
        isAnyRole,
        isAdmin,
        isStaff,
        role: userRole
    };
}
