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
        setLoading(true);
        setError(null);

        try {
            const response = await fetch('/license/user-access-status', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const result = await response.json();
            
            if (result.success) {
                setAccessStatus(result.data);
            } else {
                setError(result.message || 'Failed to fetch access status');
            }
        } catch (err) {
            setError('Failed to fetch access status');
            console.error('Error fetching access status:', err);
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
