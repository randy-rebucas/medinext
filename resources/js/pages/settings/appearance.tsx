import { Head } from '@inertiajs/react';

import AppearanceToggleTab from '@/components/appearance-tabs';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';

import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import { settings } from '@/routes';
import { Palette } from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Appearance settings',
        href: settings.appearance(),
    },
];

export default function Appearance() {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Appearance Settings - Medinext">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&family=instrument-sans:400,500,600" rel="stylesheet" />
            </Head>

            <SettingsLayout>
                <div className="space-y-6">
                    {/* Modern Header */}
                    <div className="relative overflow-hidden rounded-2xl bg-gradient-to-r from-blue-600 to-purple-600 p-8 text-white shadow-xl">
                        <div className="absolute inset-0 bg-black/10"></div>
                        <div className="relative">
                            <h1 className="text-3xl font-bold tracking-tight">Appearance Settings</h1>
                            <p className="mt-2 text-blue-100">
                                Customize your application's appearance and theme preferences
                            </p>
                        </div>
                        {/* Decorative elements */}
                        <div className="absolute -top-4 -right-4 w-24 h-24 bg-white/10 rounded-full"></div>
                        <div className="absolute -bottom-2 -left-2 w-16 h-16 bg-white/5 rounded-full"></div>
                    </div>

                    {/* Appearance Settings Card */}
                        <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                            <CardHeader>
                                <CardTitle className="flex items-center text-slate-900 dark:text-white">
                                    <div className="p-2 bg-purple-500 rounded-lg mr-3">
                                        <Palette className="h-5 w-5 text-white" />
                                    </div>
                                    Theme & Appearance
                                </CardTitle>
                                <CardDescription className="text-slate-600 dark:text-slate-300">
                                    Choose your preferred theme, color scheme, and display settings
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <AppearanceToggleTab />
                            </CardContent>
                    </Card>
                </div>
            </SettingsLayout>
        </AppLayout>
    );
}
