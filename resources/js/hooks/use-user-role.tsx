import { useState, useEffect } from 'react';
import { usePage } from '@inertiajs/react';

interface Clinic {
    id: number;
    name: string;
    address?: string;
    phone?: string;
    email?: string;
}

interface User {
    id: number;
    name: string;
    email: string;
    role: string;
    clinic_id?: number;
    clinic?: Clinic;
}

interface PageProps {
    auth?: {
        user?: User;
    };
}

const ROLE_STORAGE_KEY = 'medinext_user_role';

export function useUserRole() {
    const pageProps = usePage().props as PageProps;
    const [userRole, setUserRole] = useState<string>('');

    useEffect(() => {
        // First, try to get role from current page props
        const currentRole = pageProps?.auth?.user?.role;

        if (currentRole) {
            // Update localStorage with current role
            localStorage.setItem(ROLE_STORAGE_KEY, currentRole);
            setUserRole(currentRole);
        } else {
            // If no role in props, try to get from localStorage
            const storedRole = localStorage.getItem(ROLE_STORAGE_KEY);
            if (storedRole) {
                setUserRole(storedRole);
            }
        }
    }, [pageProps?.auth?.user?.role]);

    // Function to update role (useful for role switching)
    const updateRole = (role: string) => {
        localStorage.setItem(ROLE_STORAGE_KEY, role);
        setUserRole(role);
    };

    // Function to clear role (useful for logout)
    const clearRole = () => {
        localStorage.removeItem(ROLE_STORAGE_KEY);
        setUserRole('');
    };

    return {
        userRole,
        updateRole,
        clearRole,
        isRoleLoaded: !!userRole
    };
}
