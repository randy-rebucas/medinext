import { useState, useEffect } from 'react';

interface AccessStatus {
    type: 'trial' | 'licensed' | 'none';
    status: 'active' | 'expired' | 'inactive';
    message: string;
    expires_at?: string;
    days_remaining?: number;
    days_expired?: number;
}

interface UseUserAccessStatusReturn {
    accessStatus: AccessStatus | null;
    loading: boolean;
    error: string | null;
    refetch: () => void;
}

export function useUserAccessStatus(): UseUserAccessStatusReturn {
    const [accessStatus, setAccessStatus] = useState<AccessStatus | null>(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);

    const fetchAccessStatus = async () => {
        try {
            setLoading(true);
            setError(null);

            const response = await fetch('/license/user-access-status', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                const errorData = await response.json().catch(() => ({}));
                throw new Error(`HTTP error! status: ${response.status} - ${errorData.message || 'Unknown error'}`);
            }

            const data = await response.json();
            if (data.success) {
                setAccessStatus(data.data);
            } else {
                throw new Error(data.message || 'Failed to fetch access status');
            }
        } catch (err) {
            const errorMessage = err instanceof Error ? err.message : 'Failed to fetch access status';
            setError(errorMessage);
            console.error('Error fetching user access status:', err);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchAccessStatus();
    }, []);

    return {
        accessStatus,
        loading,
        error,
        refetch: fetchAccessStatus,
    };
}
