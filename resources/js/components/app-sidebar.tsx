import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import { LicenseIndicatorCompact } from '@/components/license-indicator';
import { LicenseActivationModal } from '@/components/license-activation-modal';
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
    medrepDashboard,
    adminDoctors,
    adminAppointments,
    adminPatients,
    adminReports,
    adminAnalytics,
    adminClinicSettings,
    adminRooms,
    adminSchedules,
    adminDashboard,
    adminStaff,
    doctorQueue,
    doctorLabResults,
    doctorPatientHistory,
    receptionistPatientSearch,
    receptionistAppointments,
    receptionistRegisterPatient,
    receptionistQueue,
    receptionistEncounters,
    receptionistCheckIn,
    receptionistReports,
    receptionistPatientHistory,
    receptionistInsurance,
    patientBookAppointment,
    patientAppointments,
    patientMedicalRecords,
    patientPrescriptions,
    patientLabResults,
    patientProfile,
    patientDocuments,
    patientBilling,
    patientNotifications,
    patientInsurance,
    patientFollowUps,
    medrepProducts,
    medrepScheduleMeeting,
    medrepInteractions,
    medrepDoctors,
    medrepAnalytics,
    medrepSamples,
    medrepMeetingHistory,
    medrepTerritory,
    medrepPerformance,
    medrepMarketing,
    medrepCommitments
} from '@/routes';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/react';
import { useUserRole } from '@/hooks/use-user-role';
import { useUserAccessStatus } from '@/hooks/use-user-access-status';
import { Button } from '@/components/ui/button';
import { Key } from 'lucide-react';
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

// Get navigation items based on user role
function getMainNavItems(userRole: string): NavItem[] {
    switch (userRole) {
        case 'admin':
            return [
                {
                    title: 'Dashboard',
                    href: adminDashboard(),
                    icon: LayoutGrid,
                },
                {
                    title: 'Staff Management',
                    href: adminStaff(), 
                    icon: Users,
                },
                {
                    title: 'Doctor Management',
                    href: adminDoctors(),
                    icon: Stethoscope,
                },
                {
                    title: 'Appointments',
                    href: adminAppointments(),
                    icon: Calendar,
                },
                {
                    title: 'Patient Management',
                    href: adminPatients(),
                    icon: User,
                },
                {
                    title: 'Reports',
                    href: adminReports(),
                    icon: FileText,
                },
                {
                    title: 'Analytics',
                    href: adminAnalytics(),
                    icon: BarChart3,
                },
                {
                    title: 'Clinic Settings',
                    href: adminClinicSettings(),
                    icon: Settings,
                },
                {
                    title: 'Room Management',
                    href: adminRooms(),
                    icon: Building2,
                },
                {
                    title: 'Schedule Management',
                    href: adminSchedules(),
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
                    href: doctorQueue(),
                    icon: Clock,
                },
                {
                    title: 'Lab Results',
                    href: doctorLabResults(),
                    icon: TestTube,
                },
                {
                    title: 'Patient History',
                    href: doctorPatientHistory(),
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
                    href: receptionistPatientSearch(),
                    icon: Search,
                },
                {
                    title: 'Appointments',
                    href: receptionistAppointments(),
                    icon: Calendar,
                },
                {
                    title: 'Patient Registration',
                    href: receptionistRegisterPatient(),
                    icon: UserPlus,
                },
                {
                    title: 'Queue Management',
                    href: receptionistQueue(),
                    icon: Clock,
                },
                {
                    title: 'Encounters',
                    href: receptionistEncounters(),
                    icon: FileText,
                },
                {
                    title: 'Check-in/Check-out',
                    href: receptionistCheckIn(),
                    icon: ClipboardList,
                },
                {
                    title: 'Reports',
                    href: receptionistReports(),
                    icon: BarChart3,
                },
                {
                    title: 'Patient History',
                    href: receptionistPatientHistory(),
                    icon: Eye,
                },
                {
                    title: 'Insurance Management',
                    href: receptionistInsurance(),
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
                    href: patientBookAppointment(),
                    icon: Calendar,
                },
                {
                    title: 'My Appointments',
                    href: patientAppointments(),
                    icon: Clock,
                },
                {
                    title: 'Medical Records',
                    href: patientMedicalRecords(),
                    icon: FileText,
                },
                {
                    title: 'Prescriptions',
                    href: patientPrescriptions(),
                    icon: Pill,
                },
                {
                    title: 'Lab Results',
                    href: patientLabResults(),
                    icon: TestTube,
                },
                {
                    title: 'My Profile',
                    href: patientProfile(),
                    icon: User,
                },
                {
                    title: 'Download Documents',
                    href: patientDocuments(),
                    icon: Download,
                },
                {
                    title: 'Billing',
                    href: patientBilling(),
                    icon: CreditCard,
                },
                {
                    title: 'Notifications',
                    href: patientNotifications(),
                    icon: Bell,
                },
                {
                    title: 'Insurance',
                    href: patientInsurance(),
                    icon: Shield,
                },
                {
                    title: 'Follow-ups',
                    href: patientFollowUps(),
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
                    href: medrepProducts(),
                    icon: Package,
                },
                {
                    title: 'Schedule Meetings',
                    href: medrepScheduleMeeting(),
                    icon: Calendar,
                },
                {
                    title: 'Doctor Interactions',
                    href: medrepInteractions(),
                    icon: MessageSquare,
                },
                {
                    title: 'Doctor Management',
                    href: medrepDoctors(),
                    icon: Users,
                },
                {
                    title: 'Analytics',
                    href: medrepAnalytics(),
                    icon: BarChart3,
                },
                {
                    title: 'Sample Management',
                    href: medrepSamples(),
                    icon: Target,
                },
                {
                    title: 'Meeting History',
                    href: medrepMeetingHistory(),
                    icon: Clock,
                },
                {
                    title: 'Territory Management',
                    href: medrepTerritory(),
                    icon: Building2,
                },
                {
                    title: 'Performance Metrics',
                    href: medrepPerformance(),
                    icon: TrendingUp,
                },
                {
                    title: 'Marketing Materials',
                    href: medrepMarketing(),
                    icon: FileText,
                },
                {
                    title: 'Commitment Tracking',
                    href: medrepCommitments(),
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
        href: 'https://github.com/randy-rebucas/medinext',
        icon: Folder,
    },
    {
        title: 'API Documentation',
        href: '/api/documentation',
        icon: BookOpen,
    },
];

export function AppSidebar() {
    const { userRole } = useUserRole();
    const { accessStatus } = useUserAccessStatus();
    const mainNavItems = getMainNavItems(userRole);

    return (
        <Sidebar
            collapsible="icon"
            variant="inset"
            className="border-r border-slate-200 dark:border-slate-800 bg-gradient-to-b from-slate-50 to-white dark:from-slate-900 dark:to-slate-800"
        >
            <SidebarHeader className="border-b border-slate-200 dark:border-slate-800 bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton
                            size="lg"
                            asChild
                            className="hover:bg-gradient-to-r hover:from-blue-50 hover:to-purple-50 dark:hover:from-blue-900/20 dark:hover:to-purple-900/20 transition-all duration-200"
                        >
                            <Link href={dashboard()} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                    {accessStatus && (
                        <SidebarMenuItem>
                            <div className="px-2 py-1 space-y-2">
                                <LicenseIndicatorCompact accessStatus={accessStatus} />
                                {(accessStatus.status === 'expired' || (accessStatus.status === 'active' && accessStatus.type === 'trial')) && (
                                    <LicenseActivationModal
                                        trigger={
                                            <Button size="sm" variant="outline" className="w-full gap-2 text-xs">
                                                <Key className="h-3 w-3" />
                                                {accessStatus.status === 'expired' ? 'Activate License' : 'Upgrade to License'}
                                            </Button>
                                        }
                                    />
                                )}
                            </div>
                        </SidebarMenuItem>
                    )}
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent className="bg-transparent">
                <NavMain items={mainNavItems} />
            </SidebarContent>

            <SidebarFooter className="border-t border-slate-200 dark:border-slate-800 bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                <NavFooter items={footerNavItems} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
