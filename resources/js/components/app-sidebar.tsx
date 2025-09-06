import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import {
    dashboard,
    doctorDashboard,
    doctorAppointments,
    doctorMedicalRecords,
    doctorPrescriptions,
    doctorAdvice,
    receptionistDashboard,
    patientDashboard,
    medrepDashboard
} from '@/routes';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import {
    BookOpen,
    Folder,
    LayoutGrid,
    Calendar,
    FileText,
    Pill,
    Stethoscope,
    Users,
    UserPlus,
    Search,
    Clock,
    Package,
    TrendingUp,
    Shield,
    Settings,
    BarChart3,
    User,
    ClipboardList,
    Building2,
    CreditCard,
    Bell,
    Download,
    Eye,
    Heart,
    TestTube,
    MessageSquare,
    Target,
    Briefcase
} from 'lucide-react';
import AppLogo from './app-logo';

interface PageProps {
    auth?: {
        user?: {
            role: string;
        };
    };
}

// Get user role from page props
function getMainNavItems(userRole: string): NavItem[] {
    switch (userRole) {
        case 'admin':
            return [
                {
                    title: 'Dashboard',
                    href: '/admin/dashboard',
                    icon: LayoutGrid,
                },
                {
                    title: 'Staff Management',
                    href: '/admin/staff',
                    icon: Users,
                },
                {
                    title: 'Doctor Management',
                    href: '/admin/doctors',
                    icon: Stethoscope,
                },
                {
                    title: 'Appointments',
                    href: '/admin/appointments',
                    icon: Calendar,
                },
                {
                    title: 'Patient Management',
                    href: '/admin/patients',
                    icon: User,
                },
                {
                    title: 'Reports',
                    href: '/admin/reports',
                    icon: FileText,
                },
                {
                    title: 'Analytics',
                    href: '/admin/analytics',
                    icon: BarChart3,
                },
                {
                    title: 'Clinic Settings',
                    href: '/admin/clinic-settings',
                    icon: Settings,
                },
                {
                    title: 'Room Management',
                    href: '/admin/rooms',
                    icon: Building2,
                },
                {
                    title: 'Schedule Management',
                    href: '/admin/schedules',
                    icon: Clock,
                },
            ];

        case 'doctor':
            return [
                {
                    title: 'Dashboard',
                    href: doctorDashboard(),
                    icon: LayoutGrid,
                },
                {
                    title: 'Appointments',
                    href: doctorAppointments(),
                    icon: Calendar,
                },
                {
                    title: 'Medical Records',
                    href: doctorMedicalRecords(),
                    icon: FileText,
                },
                {
                    title: 'Prescriptions',
                    href: doctorPrescriptions(),
                    icon: Pill,
                },
                {
                    title: 'Medical Advice',
                    href: doctorAdvice(),
                    icon: Stethoscope,
                },
                {
                    title: 'Patient Queue',
                    href: '/doctor/queue',
                    icon: Clock,
                },
                {
                    title: 'Lab Results',
                    href: '/doctor/lab-results',
                    icon: TestTube,
                },
                {
                    title: 'Patient History',
                    href: '/doctor/patient-history',
                    icon: ClipboardList,
                },
            ];

        case 'receptionist':
            return [
                {
                    title: 'Dashboard',
                    href: receptionistDashboard(),
                    icon: LayoutGrid,
                },
                {
                    title: 'Patient Search',
                    href: '/receptionist/patient-search',
                    icon: Search,
                },
                {
                    title: 'Appointments',
                    href: '/receptionist/appointments',
                    icon: Calendar,
                },
                {
                    title: 'Patient Registration',
                    href: '/receptionist/register-patient',
                    icon: UserPlus,
                },
                {
                    title: 'Queue Management',
                    href: '/receptionist/queue',
                    icon: Clock,
                },
                {
                    title: 'Encounters',
                    href: '/receptionist/encounters',
                    icon: FileText,
                },
                {
                    title: 'Check-in/Check-out',
                    href: '/receptionist/check-in',
                    icon: ClipboardList,
                },
                {
                    title: 'Reports',
                    href: '/receptionist/reports',
                    icon: BarChart3,
                },
                {
                    title: 'Patient History',
                    href: '/receptionist/patient-history',
                    icon: Eye,
                },
                {
                    title: 'Insurance Management',
                    href: '/receptionist/insurance',
                    icon: CreditCard,
                },
            ];

        case 'patient':
            return [
                {
                    title: 'Dashboard',
                    href: patientDashboard(),
                    icon: LayoutGrid,
                },
                {
                    title: 'Book Appointment',
                    href: '/patient/book-appointment',
                    icon: Calendar,
                },
                {
                    title: 'My Appointments',
                    href: '/patient/appointments',
                    icon: Clock,
                },
                {
                    title: 'Medical Records',
                    href: '/patient/medical-records',
                    icon: FileText,
                },
                {
                    title: 'Prescriptions',
                    href: '/patient/prescriptions',
                    icon: Pill,
                },
                {
                    title: 'Lab Results',
                    href: '/patient/lab-results',
                    icon: TestTube,
                },
                {
                    title: 'My Profile',
                    href: '/patient/profile',
                    icon: User,
                },
                {
                    title: 'Download Documents',
                    href: '/patient/documents',
                    icon: Download,
                },
                {
                    title: 'Billing',
                    href: '/patient/billing',
                    icon: CreditCard,
                },
                {
                    title: 'Notifications',
                    href: '/patient/notifications',
                    icon: Bell,
                },
                {
                    title: 'Insurance',
                    href: '/patient/insurance',
                    icon: Shield,
                },
                {
                    title: 'Follow-ups',
                    href: '/patient/follow-ups',
                    icon: Heart,
                },
            ];

        case 'medrep':
            return [
                {
                    title: 'Dashboard',
                    href: medrepDashboard(),
                    icon: LayoutGrid,
                },
                {
                    title: 'Product Management',
                    href: '/medrep/products',
                    icon: Package,
                },
                {
                    title: 'Schedule Meetings',
                    href: '/medrep/schedule-meeting',
                    icon: Calendar,
                },
                {
                    title: 'Doctor Interactions',
                    href: '/medrep/interactions',
                    icon: MessageSquare,
                },
                {
                    title: 'Doctor Management',
                    href: '/medrep/doctors',
                    icon: Users,
                },
                {
                    title: 'Analytics',
                    href: '/medrep/analytics',
                    icon: BarChart3,
                },
                {
                    title: 'Sample Management',
                    href: '/medrep/samples',
                    icon: Target,
                },
                {
                    title: 'Meeting History',
                    href: '/medrep/meeting-history',
                    icon: Clock,
                },
                {
                    title: 'Territory Management',
                    href: '/medrep/territory',
                    icon: Building2,
                },
                {
                    title: 'Performance Metrics',
                    href: '/medrep/performance',
                    icon: TrendingUp,
                },
                {
                    title: 'Marketing Materials',
                    href: '/medrep/marketing',
                    icon: FileText,
                },
                {
                    title: 'Commitment Tracking',
                    href: '/medrep/commitments',
                    icon: Briefcase,
                },
            ];

        default:
            return [
                {
                    title: 'Dashboard',
                    href: dashboard(),
                    icon: LayoutGrid,
                },
            ];
    }
}

const footerNavItems: NavItem[] = [
    {
        title: 'Repository',
        href: 'https://github.com/laravel/react-starter-kit',
        icon: Folder,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#react',
        icon: BookOpen,
    },
];

export function AppSidebar() {
    const pageProps = usePage().props as PageProps;
    const userRole = pageProps?.auth?.user?.role || '';
    const mainNavItems = getMainNavItems(userRole);

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={dashboard()} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={mainNavItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavFooter items={footerNavItems} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
