import { router } from '@inertiajs/react';

// Base routes
export const home = () => '/';
export const dashboard = () => '/dashboard';

// Role-based dashboard routes
export const roleDashboard = (role: string) => {
    switch (role) {
        case 'superadmin':
            return '/admin/dashboard';
        case 'admin':
            return '/admin/dashboard';
        case 'doctor':
            return '/doctor/dashboard';
        case 'receptionist':
            return '/receptionist/dashboard';
        case 'patient':
            return '/patient/dashboard';
        case 'medrep':
            return '/medrep/dashboard';
        default:
            return '/dashboard';
    }
};

// Authentication routes
export const login = () => '/login';
export const register = () => '/register';
export const logout = () => router.post('/logout');
export const forgotPassword = () => '/forgot-password';
export const resetPassword = (token: string) => `/reset-password/${token}`;

// Profile routes
export const profile = {
    edit: () => '/profile',
    update: () => router.put('/profile'),
    password: () => '/profile/password',
    updatePassword: () => router.put('/profile/password'),
};

// Doctor routes
export const doctorDashboard = () => '/doctor/dashboard';
export const doctorAppointments = () => '/doctor/appointments';
export const doctorPrescriptions = () => '/doctor/prescriptions';
export const doctorMedicalRecords = () => '/doctor/medical-records';
export const doctorAdvice = () => '/doctor/advice';
export const doctorQueue = () => '/doctor/queue';
export const doctorPatientHistory = () => '/doctor/patient-history';
export const doctorLabResults = () => '/doctor/lab-results';

// Admin routes
export const adminDashboard = () => '/admin/dashboard';
export const adminDoctors = () => '/admin/doctors';
export const adminStaff = () => '/admin/staff';
export const adminPatients = () => '/admin/patients';
export const adminAppointments = () => '/admin/appointments';
export const adminReports = () => '/admin/reports';
export const adminAnalytics = () => '/admin/analytics';
export const adminClinicSettings = () => '/admin/clinic-settings';
export const adminRooms = () => '/admin/rooms';
export const adminSchedules = () => '/admin/schedules';

// Receptionist routes
export const receptionistDashboard = () => '/receptionist/dashboard';
export const receptionistAppointments = () => '/receptionist/appointments';
export const receptionistCheckIn = () => '/receptionist/check-in';
export const receptionistEncounters = () => '/receptionist/encounters';
export const receptionistInsurance = () => '/receptionist/insurance';
export const receptionistPatientHistory = () => '/receptionist/patient-history';
export const receptionistPatientSearch = () => '/receptionist/patient-search';
export const receptionistQueue = () => '/receptionist/queue';
export const receptionistRegisterPatient = () => '/receptionist/register-patient';
export const receptionistReports = () => '/receptionist/reports';

// Patient routes
export const patientDashboard = () => '/patient/dashboard';
export const patientAppointments = () => '/patient/appointments';
export const patientBilling = () => '/patient/billing';
export const patientBookAppointment = () => '/patient/book-appointment';
export const patientDocuments = () => '/patient/documents';
export const patientFollowUps = () => '/patient/follow-ups';
export const patientInsurance = () => '/patient/insurance';
export const patientLabResults = () => '/patient/lab-results';
export const patientMedicalRecords = () => '/patient/medical-records';
export const patientNotifications = () => '/patient/notifications';
export const patientPrescriptions = () => '/patient/prescriptions';
export const patientProfile = () => '/patient/profile';

// Medical Representative routes
export const medrepDashboard = () => '/medrep/dashboard';
export const medrepAnalytics = () => '/medrep/analytics';
export const medrepCommitments = () => '/medrep/commitments';
export const medrepDoctors = () => '/medrep/doctors';
export const medrepInteractions = () => '/medrep/interactions';
export const medrepMarketing = () => '/medrep/marketing';
export const medrepMeetingHistory = () => '/medrep/meeting-history';
export const medrepPerformance = () => '/medrep/performance';
export const medrepProducts = () => '/medrep/products';
export const medrepSamples = () => '/medrep/samples';
export const medrepScheduleMeeting = () => '/medrep/schedule-meeting';
export const medrepTerritory = () => '/medrep/territory';

// Settings routes
export const settings = {
    appearance: () => '/settings/appearance',
    profile: () => '/settings/profile',
    password: () => '/settings/password',
};

// API routes (for direct API calls)
export const api = {
    // Patient routes
    patients: {
        index: () => '/api/v1/patients',
        show: (id: number) => `/api/v1/patients/${id}`,
        store: () => '/api/v1/patients',
        update: (id: number) => `/api/v1/patients/${id}`,
        destroy: (id: number) => `/api/v1/patients/${id}`,
        search: (query: string) => `/api/v1/patients/search?q=${encodeURIComponent(query)}`,
        appointments: (id: number) => `/api/v1/patients/${id}/appointments`,
        encounters: (id: number) => `/api/v1/patients/${id}/encounters`,
        prescriptions: (id: number) => `/api/v1/patients/${id}/prescriptions`,
        labResults: (id: number) => `/api/v1/patients/${id}/lab-results`,
        medicalHistory: (id: number) => `/api/v1/patients/${id}/medical-history`,
        fileAssets: (id: number) => `/api/v1/patients/${id}/file-assets`,
        uploadFile: (id: number) => `/api/v1/patients/${id}/file-assets`,
    },

    // Encounter routes
    encounters: {
        index: () => '/api/v1/encounters',
        show: (id: number) => `/api/v1/encounters/${id}`,
        store: () => '/api/v1/encounters',
        update: (id: number) => `/api/v1/encounters/${id}`,
        destroy: (id: number) => `/api/v1/encounters/${id}`,
        prescriptions: (id: number) => `/api/v1/encounters/${id}/prescriptions`,
        labResults: (id: number) => `/api/v1/encounters/${id}/lab-results`,
        fileAssets: (id: number) => `/api/v1/encounters/${id}/file-assets`,
        uploadFile: (id: number) => `/api/v1/encounters/${id}/file-assets`,
        updateSoapNotes: (id: number) => `/api/v1/encounters/${id}/soap-notes`,
        complete: (id: number) => `/api/v1/encounters/${id}/complete`,
        addToQueue: (id: number) => `/api/v1/encounters/${id}/queue`,
    },

    // Appointment routes
    appointments: {
        index: () => '/api/v1/appointments',
        show: (id: number) => `/api/v1/appointments/${id}`,
        store: () => '/api/v1/appointments',
        update: (id: number) => `/api/v1/appointments/${id}`,
        destroy: (id: number) => `/api/v1/appointments/${id}`,
        checkIn: (id: number) => `/api/v1/appointments/${id}/check-in`,
        checkOut: (id: number) => `/api/v1/appointments/${id}/check-out`,
        cancel: (id: number) => `/api/v1/appointments/${id}/cancel`,
        reschedule: (id: number) => `/api/v1/appointments/${id}/reschedule`,
        sendReminder: (id: number) => `/api/v1/appointments/${id}/reminder`,
        availableSlots: () => '/api/v1/appointments/available-slots',
        checkConflicts: () => '/api/v1/appointments/conflicts',
        today: () => '/api/v1/appointments/today',
        upcoming: () => '/api/v1/appointments/upcoming',
    },

    // Prescription routes
    prescriptions: {
        index: () => '/api/v1/prescriptions',
        show: (id: number) => `/api/v1/prescriptions/${id}`,
        store: () => '/api/v1/prescriptions',
        update: (id: number) => `/api/v1/prescriptions/${id}`,
        destroy: (id: number) => `/api/v1/prescriptions/${id}`,
        items: (id: number) => `/api/v1/prescriptions/${id}/items`,
        addItem: (id: number) => `/api/v1/prescriptions/${id}/items`,
        updateItem: (id: number, itemId: number) => `/api/v1/prescriptions/${id}/items/${itemId}`,
        removeItem: (id: number, itemId: number) => `/api/v1/prescriptions/${id}/items/${itemId}`,
        verify: (id: number) => `/api/v1/prescriptions/${id}/verify`,
        dispense: (id: number) => `/api/v1/prescriptions/${id}/dispense`,
        refill: (id: number) => `/api/v1/prescriptions/${id}/refill`,
        downloadPdf: (id: number) => `/api/v1/prescriptions/${id}/pdf`,
    },

    // Medical Advice routes
    medicalAdvice: {
        index: () => '/api/v1/medical-advice',
        show: (id: number) => `/api/v1/medical-advice/${id}`,
        store: () => '/api/v1/medical-advice',
        update: (id: number) => `/api/v1/medical-advice/${id}`,
        destroy: (id: number) => `/api/v1/medical-advice/${id}`,
        byPatient: (patientId: number) => `/api/v1/medical-advice/patient/${patientId}`,
        byCategory: (category: string) => `/api/v1/medical-advice/category/${category}`,
        byPriority: (priority: string) => `/api/v1/medical-advice/priority/${priority}`,
        updateStatus: (id: number) => `/api/v1/medical-advice/${id}/status`,
    },

    // Doctor routes
    doctors: {
        index: () => '/api/v1/doctors',
        show: (id: number) => `/api/v1/doctors/${id}`,
        store: () => '/api/v1/doctors',
        update: (id: number) => `/api/v1/doctors/${id}`,
        destroy: (id: number) => `/api/v1/doctors/${id}`,
        appointments: (id: number) => `/api/v1/doctors/${id}/appointments`,
        encounters: (id: number) => `/api/v1/doctors/${id}/encounters`,
        patients: (id: number) => `/api/v1/doctors/${id}/patients`,
        availability: (id: number) => `/api/v1/doctors/${id}/availability`,
        statistics: (id: number) => `/api/v1/doctors/${id}/statistics`,
        updateAvailability: (id: number) => `/api/v1/doctors/${id}/availability`,
    },

    // Dashboard routes
    dashboard: {
        index: () => '/api/v1/dashboard',
        stats: () => '/api/v1/dashboard/stats',
        notifications: () => '/api/v1/dashboard/notifications',
        web: () => '/api/v1/web/dashboard',
        analytics: () => '/api/v1/web/analytics',
    },

    // Queue routes
    queue: {
        index: () => '/api/v1/queue',
        active: () => '/api/v1/queue/active',
        completed: () => '/api/v1/queue/completed',
        add: () => '/api/v1/queue',
        remove: (id: number) => `/api/v1/queue/${id}`,
        updatePosition: (id: number) => `/api/v1/queue/${id}/position`,
    },

    // File upload routes
    files: {
        upload: () => '/api/v1/files/upload',
        download: (id: number) => `/api/v1/files/${id}/download`,
        delete: (id: number) => `/api/v1/files/${id}`,
    },

    // Lab results routes
    labResults: {
        index: () => '/api/v1/lab-results',
        show: (id: number) => `/api/v1/lab-results/${id}`,
        store: () => '/api/v1/lab-results',
        update: (id: number) => `/api/v1/lab-results/${id}`,
        destroy: (id: number) => `/api/v1/lab-results/${id}`,
        upload: () => '/api/v1/lab-results/upload',
        download: (id: number) => `/api/v1/lab-results/${id}/download`,
    },

    // Settings routes
    settings: {
        index: () => '/api/v1/settings',
        update: () => '/api/v1/settings',
        clinic: () => '/api/v1/settings/clinic',
        updateClinic: () => '/api/v1/settings/clinic',
    },

    // Medical Representative routes
    medrep: {
        products: {
            index: () => '/api/v1/medrep/products',
            show: (id: number) => `/api/v1/medrep/products/${id}`,
            store: () => '/api/v1/medrep/products',
            update: (id: number) => `/api/v1/medrep/products/${id}`,
            destroy: (id: number) => `/api/v1/medrep/products/${id}`,
        },
        meetings: {
            index: () => '/api/v1/medrep/meetings',
            show: (id: number) => `/api/v1/medrep/meetings/${id}`,
            store: () => '/api/v1/medrep/meetings',
            update: (id: number) => `/api/v1/medrep/meetings/${id}`,
            destroy: (id: number) => `/api/v1/medrep/meetings/${id}`,
        },
        interactions: {
            index: () => '/api/v1/medrep/interactions',
            show: (id: number) => `/api/v1/medrep/interactions/${id}`,
            store: () => '/api/v1/medrep/interactions',
            update: (id: number) => `/api/v1/medrep/interactions/${id}`,
            destroy: (id: number) => `/api/v1/medrep/interactions/${id}`,
        },
        doctors: {
            index: () => '/api/v1/medrep/doctors',
            show: (id: number) => `/api/v1/medrep/doctors/${id}`,
        },
    },

    // PDF Generation routes
    pdf: {
        prescription: (id: number) => `/api/v1/prescriptions/${id}/pdf`,
        medicalReport: (id: number) => `/api/v1/encounters/${id}/medical-report`,
        labReport: (id: number) => `/api/v1/lab-results/${id}/pdf`,
    },
};
