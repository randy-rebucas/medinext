import { SidebarGroup, SidebarGroupLabel, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/react';

export function NavMain({ items = [] }: { items: NavItem[] }) {
    const page = usePage();
    return (
        <SidebarGroup className="px-3 py-2">
            <SidebarGroupLabel className="text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-3">
                Navigation
            </SidebarGroupLabel>
            <SidebarMenu className="space-y-1">
                {items.map((item) => (
                    <SidebarMenuItem key={item.title}>
                        <SidebarMenuButton
                            asChild
                            isActive={page.url.startsWith(item.href)}
                            tooltip={{ children: item.title }}
                            className={`
                                group relative w-full justify-start h-10 px-3 py-2 text-sm font-medium rounded-lg transition-all duration-200
                                ${page.url.startsWith(item.href)
                                    ? 'bg-gradient-to-r from-blue-600 to-purple-600 text-white shadow-lg'
                                    : 'text-slate-700 dark:text-slate-300 hover:bg-gradient-to-r hover:from-blue-50 hover:to-purple-50 dark:hover:from-blue-900/20 dark:hover:to-purple-900/20 hover:text-slate-900 dark:hover:text-white'
                                }
                            `}
                        >
                            <Link href={item.href} prefetch className="flex items-center gap-3">
                                {item.icon && (
                                    <item.icon className={`
                                        h-4 w-4 transition-colors duration-200
                                        ${page.url.startsWith(item.href)
                                            ? 'text-white'
                                            : 'text-slate-500 dark:text-slate-400 group-hover:text-slate-700 dark:group-hover:text-slate-300'
                                        }
                                    `} />
                                )}
                                <span className="truncate">{item.title}</span>
                                {page.url.startsWith(item.href) && (
                                    <div className="absolute right-2 w-1.5 h-1.5 bg-white rounded-full"></div>
                                )}
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                ))}
            </SidebarMenu>
        </SidebarGroup>
    );
}
