import { useState, useEffect } from 'react';
import { usePage, router } from '@inertiajs/react';

interface Settings {
    branding: {
        primary_color: string;
        secondary_color: string;
        logo_url: string;
        favicon_url: string;
        clinic_name: string;
    };
    clinic: {
        name: string;
        phone: string;
        email: string;
        address: string;
    };
}

interface UseSettingsReturn {
    settings: Settings | null;
    loading: boolean;
    error: string | null;
    getBrandingColor: (type: 'primary' | 'secondary', fallback: string) => string;
    getClinicName: (fallback: string) => string;
}

export function useSettings(): UseSettingsReturn {
    const [settings, setSettings] = useState<Settings | null>(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);

    useEffect(() => {
        const fetchSettings = () => {
            router.get('/settings/clinic', {}, {
                onSuccess: (page) => {
                    setSettings(page.props.settings);
                    setLoading(false);
                },
                onError: (errors) => {
                    setError('Failed to fetch settings');
                    setLoading(false);
                }
            });
        };

        fetchSettings();
    }, []);

    const getBrandingColor = (type: 'primary' | 'secondary', fallback: string): string => {
        if (!settings?.branding) return fallback;

        const color = type === 'primary'
            ? settings.branding.primary_color
            : settings.branding.secondary_color;

        return color || fallback;
    };

    const getClinicName = (fallback: string): string => {
        return settings?.clinic?.name || settings?.branding?.clinic_name || fallback;
    };

    return {
        settings,
        loading,
        error,
        getBrandingColor,
        getClinicName,
    };
}
