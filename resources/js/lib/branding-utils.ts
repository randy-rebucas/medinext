/**
 * Convert hex color to Tailwind CSS gradient classes
 */
export function hexToTailwindGradient(primaryColor: string, secondaryColor: string, fallback: string = 'from-emerald-600 to-teal-600'): string {
    if (!primaryColor || !secondaryColor) {
        return fallback;
    }

    // Convert hex to RGB
    const hexToRgb = (hex: string) => {
        const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
        return result ? {
            r: parseInt(result[1], 16),
            g: parseInt(result[2], 16),
            b: parseInt(result[3], 16)
        } : null;
    };

    const primaryRgb = hexToRgb(primaryColor);
    const secondaryRgb = hexToRgb(secondaryColor);

    if (!primaryRgb || !secondaryRgb) {
        return fallback;
    }

    // For now, return a custom gradient style
    // In a real implementation, you might want to map to closest Tailwind colors
    return `from-[${primaryColor}] to-[${secondaryColor}]`;
}

/**
 * Get CSS custom properties for branding colors
 */
export function getBrandingCSSProperties(primaryColor: string, secondaryColor: string): Record<string, string> {
    return {
        '--brand-primary': primaryColor || '#059669',
        '--brand-secondary': secondaryColor || '#0d9488',
        '--brand-primary-rgb': hexToRgb(primaryColor || '#059669'),
        '--brand-secondary-rgb': hexToRgb(secondaryColor || '#0d9488'),
    };
}

/**
 * Convert hex to RGB string
 */
function hexToRgb(hex: string): string {
    const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
    if (!result) return '59, 130, 246'; // Default blue

    const r = parseInt(result[1], 16);
    const g = parseInt(result[2], 16);
    const b = parseInt(result[3], 16);

    return `${r}, ${g}, ${b}`;
}

/**
 * Apply branding styles to document
 */
export function applyBrandingStyles(primaryColor: string, secondaryColor: string): void {
    const root = document.documentElement;
    const properties = getBrandingCSSProperties(primaryColor, secondaryColor);

    Object.entries(properties).forEach(([property, value]) => {
        root.style.setProperty(property, value);
    });
}
