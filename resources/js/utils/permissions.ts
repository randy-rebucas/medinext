import React from 'react';
import { usePage } from '@inertiajs/react';

export interface User {
  id: number;
  name: string;
  email: string;
  phone?: string;
  is_active: boolean;
  primary_role?: string;
  is_super_admin: boolean;
  is_admin: boolean;
  is_doctor: boolean;
  is_patient: boolean;
  is_receptionist: boolean;
  is_medrep: boolean;
  current_clinic_id?: number;
  permissions: string[];
  roles: Array<{
    id: number;
    name: string;
    description: string;
    permissions: string[];
  }>;
}

export interface AuthData {
  user: User | null;
}

export interface PageProps {
  auth: AuthData;
  [key: string]: unknown;
}

/**
 * Hook to get current user data
 */
export function useAuth(): User | null {
  const { auth } = usePage<PageProps>().props;
  return auth.user;
}

/**
 * Check if user has a specific permission
 */
export function hasPermission(user: User | null, permission: string): boolean {
  if (!user) return false;

  // Super admin has all permissions
  if (user.is_super_admin) return true;

  return user.permissions.includes(permission);
}

/**
 * Check if user has any of the given permissions
 */
export function hasAnyPermission(user: User | null, permissions: string[]): boolean {
  if (!user) return false;

  // Super admin has all permissions
  if (user.is_super_admin) return true;

  return permissions.some(permission => user.permissions.includes(permission));
}

/**
 * Check if user has all of the given permissions
 */
export function hasAllPermissions(user: User | null, permissions: string[]): boolean {
  if (!user) return false;

  // Super admin has all permissions
  if (user.is_super_admin) return true;

  return permissions.every(permission => user.permissions.includes(permission));
}

/**
 * Check if user has a specific role
 */
export function hasRole(user: User | null, role: string): boolean {
  if (!user) return false;

  return user.primary_role === role || user.roles.some(r => r.name === role);
}

/**
 * Check if user has any of the given roles
 */
export function hasAnyRole(user: User | null, roles: string[]): boolean {
  if (!user) return false;

  return roles.some(role => hasRole(user, role));
}

/**
 * Check if user is a specific role type
 */
export function isSuperAdmin(user: User | null): boolean {
  return user?.is_super_admin ?? false;
}

export function isAdmin(user: User | null): boolean {
  return user?.is_admin ?? false;
}

export function isDoctor(user: User | null): boolean {
  return user?.is_doctor ?? false;
}

export function isPatient(user: User | null): boolean {
  return user?.is_patient ?? false;
}

export function isReceptionist(user: User | null): boolean {
  return user?.is_receptionist ?? false;
}

export function isMedRep(user: User | null): boolean {
  return user?.is_medrep ?? false;
}

/**
 * Get user's display name with role prefix
 */
export function getUserDisplayName(user: User | null): string {
  if (!user) return 'Guest';

  const rolePrefix = getRolePrefix(user.primary_role);
  return rolePrefix ? `${rolePrefix} ${user.name}` : user.name;
}

/**
 * Get role prefix for display
 */
export function getRolePrefix(role?: string): string {
  const prefixes: Record<string, string> = {
    'superadmin': 'Super Admin',
    'admin': 'Admin',
    'doctor': 'Dr.',
    'receptionist': 'Receptionist',
    'medrep': 'MedRep',
    'patient': 'Patient',
  };

  return prefixes[role || ''] || '';
}

/**
 * Get role-specific color scheme
 */
export function getRoleColorScheme(role?: string): {
  primary: string;
  secondary: string;
  accent: string;
  background: string;
} {
  const schemes: Record<string, {
    primary: string;
    secondary: string;
    accent: string;
    background: string;
  }> = {
    'superadmin': {
      primary: 'from-purple-600 to-purple-700',
      secondary: 'from-purple-500 to-purple-600',
      accent: 'purple',
      background: 'from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20',
    },
    'admin': {
      primary: 'from-blue-600 to-blue-700',
      secondary: 'from-blue-500 to-blue-600',
      accent: 'blue',
      background: 'from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20',
    },
    'doctor': {
      primary: 'from-emerald-600 to-emerald-700',
      secondary: 'from-emerald-500 to-emerald-600',
      accent: 'emerald',
      background: 'from-emerald-50 to-emerald-100 dark:from-emerald-900/20 dark:to-emerald-800/20',
    },
    'receptionist': {
      primary: 'from-orange-600 to-orange-700',
      secondary: 'from-orange-500 to-orange-600',
      accent: 'orange',
      background: 'from-orange-50 to-orange-100 dark:from-orange-900/20 dark:to-orange-800/20',
    },
    'medrep': {
      primary: 'from-cyan-600 to-cyan-700',
      secondary: 'from-cyan-500 to-cyan-600',
      accent: 'cyan',
      background: 'from-cyan-50 to-cyan-100 dark:from-cyan-900/20 dark:to-cyan-800/20',
    },
    'patient': {
      primary: 'from-indigo-600 to-indigo-700',
      secondary: 'from-indigo-500 to-indigo-600',
      accent: 'indigo',
      background: 'from-indigo-50 to-indigo-100 dark:from-indigo-900/20 dark:to-indigo-800/20',
    },
  };

  return schemes[role || ''] || schemes['patient'];
}

/**
 * Permission-based component wrapper
 */
export function withPermission<T extends object>(
  Component: React.ComponentType<T>,
  requiredPermissions: string[],
  fallback?: React.ComponentType<T>
) {
  return function PermissionWrappedComponent(props: T) {
    const user = useAuth();
    if (hasAnyPermission(user, requiredPermissions)) {
      return React.createElement(Component, props);
    }

    if (fallback) {
      return React.createElement(fallback, props);
    }

    return null;
  };
}

/**
 * Role-based component wrapper
 */
export function withRole<T extends object>(
  Component: React.ComponentType<T>,
  requiredRoles: string[],
  fallback?: React.ComponentType<T>
) {
  return function RoleWrappedComponent(props: T) {
    const user = useAuth();
    if (hasAnyRole(user, requiredRoles)) {
      return React.createElement(Component, props);
    }

    if (fallback) {
      return React.createElement(fallback, props);
    }

    return null;
  };
}
