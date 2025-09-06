import { cn } from '@/lib/utils';
import { settings } from '@/routes';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/react';
import { type PropsWithChildren } from 'react';
import { User, Lock, Palette, Settings, ChevronRight } from 'lucide-react';

const sidebarNavItems: NavItem[] = [
    {
        title: 'Profile',
        href: settings.profile(),
        icon: User,
    },
    {
        title: 'Password',
        href: settings.password(),
        icon: Lock,
    },
    {
        title: 'Appearance',
        href: settings.appearance(),
        icon: Palette,
    },
];

export default function SettingsLayout({ children }: PropsWithChildren) {
    // When server-side rendering, we only render the layout on the client...
    if (typeof window === 'undefined') {
        return null;
    }

    const currentPath = window.location.pathname;

    return (
        <div className="min-h-screen bg-gradient-to-br from-slate-50 via-white to-blue-50 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900">
            <div className="flex flex-col lg:flex-row">
                {/* Modern Sidebar */}
                <aside className="w-full lg:w-80 lg:min-h-screen bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm border-r border-slate-200 dark:border-slate-700">
                    <div className="p-6">
                        {/* Sidebar Header */}
                        <div className="flex items-center space-x-3 mb-8">
                            <div className="p-2 bg-gradient-to-r from-blue-500 to-purple-500 rounded-lg">
                                <Settings className="h-5 w-5 text-white" />
                            </div>
                            <div>
                                <h2 className="text-xl font-bold text-slate-900 dark:text-white">Settings</h2>
                                <p className="text-sm text-slate-600 dark:text-slate-400">Manage your account</p>
                            </div>
                        </div>

                        {/* Navigation */}
                        <nav className="space-y-2">
                            {sidebarNavItems.map((item, index) => {
                                const isActive = currentPath === item.href;
                                return (
                                    <Link
                                        key={`${item.href}-${index}`}
                                        href={item.href}
                                        prefetch
                                        className={cn(
                                            'flex items-center justify-between w-full px-4 py-3 rounded-xl text-sm font-medium transition-all duration-200 group',
                                            isActive
                                                ? 'bg-gradient-to-r from-blue-600 to-purple-600 text-white shadow-lg'
                                                : 'text-slate-700 dark:text-slate-300 hover:bg-gradient-to-r hover:from-blue-50 hover:to-purple-50 dark:hover:from-blue-900/20 dark:hover:to-purple-900/20 hover:text-slate-900 dark:hover:text-white'
                                        )}
                                    >
                                        <div className="flex items-center space-x-3">
                                            {item.icon && (
                                                <item.icon className={cn(
                                                    'h-4 w-4 transition-colors duration-200',
                                                    isActive
                                                        ? 'text-white'
                                                        : 'text-slate-500 dark:text-slate-400 group-hover:text-slate-700 dark:group-hover:text-slate-300'
                                                )} />
                                            )}
                                            <span>{item.title}</span>
                                        </div>
                                        {isActive && (
                                            <ChevronRight className="h-4 w-4 text-white" />
                                        )}
                                    </Link>
                                );
                            })}
                        </nav>
                    </div>
                </aside>

                {/* Main Content Area */}
                <div className="flex-1 lg:min-h-screen">
                    <div className="p-6">
                        {children}
                    </div>
                </div>
            </div>
        </div>
    );
}
