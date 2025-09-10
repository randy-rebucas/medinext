// Shared data types
export interface SharedData {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    sidebarOpen: boolean;
    [key: string]: unknown;
}

export interface Auth {
    user: User;
}

// User types
export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at?: string;
    role: string;
    clinic_id?: number;
    clinic?: Clinic;
    created_at: string;
    updated_at: string;
}

// Clinic types
export interface Clinic {
    id: number;
    name: string;
    slug?: string;
    timezone?: string;
    logo_url?: string;
    address: {
        street?: string;
        city?: string;
        state?: string;
        postal_code?: string;
        country?: string;
    } | null;
    phone?: string;
    email?: string;
    website?: string;
    description?: string;
    settings?: Record<string, any>;
    created_at: string;
    updated_at: string;
}

// Patient types
export interface Patient {
    id: number;
    patient_id: string;
    name: string;
    first_name: string;
    last_name: string;
    email: string;
    phone: string;
    dob: string;
    age: number;
    sex: string;
    address: string;
    city: string;
    state: string;
    zip_code: string;
    emergency_contact: {
        name: string;
        phone: string;
        relationship: string;
    };
    insurance: {
        provider: string;
        policy_number: string;
        group_number: string;
    };
    allergies: string[];
    medical_history: string;
    medications: string;
    notes: string;
    last_visit: string | null;
    next_appointment: string | null;
    total_visits: number;
    total_encounters: number;
    total_prescriptions: number;
    status: string;
    created_at: string;
    updated_at: string;
}

// Encounter types
export interface Encounter {
    id: number;
    patient_id: number;
    patient_name: string;
    encounter_number: string;
    visit_type: string;
    reason_for_visit: string;
    status: string;
    soap_notes?: {
        subjective: string;
        objective: string;
        assessment: string;
        plan: string;
    };
    vital_signs?: {
        blood_pressure: string;
        heart_rate: number;
        temperature: number;
        weight: number;
        height: number;
    };
    diagnosis?: string[];
    treatment_plan?: string;
    follow_up_date?: string;
    payment_status: string;
    icd_codes?: string[];
    created_at: string;
    updated_at: string;
}

// Appointment types
export interface Appointment {
    id: number;
    patient_id: number;
    patient_name: string;
    doctor_id: number;
    doctor_name: string;
    start_at: string;
    end_at: string;
    status: string;
    type: string;
    reason: string;
    room_id?: number;
    room_name?: string;
    priority: string;
    notes?: string;
    created_at: string;
    updated_at: string;
}

// Prescription types
export interface Prescription {
    id: number;
    prescription_number: string;
    patient_id: number;
    patient_name: string;
    doctor_id: number;
    doctor_name: string;
    encounter_id?: number;
    status: string;
    prescription_type: string;
    diagnosis: string;
    instructions: string;
    issued_at: string;
    expiry_date: string;
    refills_allowed: number;
    refills_remaining: number;
    total_cost: number;
    verification_status: boolean | null;
    items: PrescriptionItem[];
    created_at: string;
    updated_at: string;
}

export interface PrescriptionItem {
    id: number;
    medication_name: string;
    dosage: string;
    frequency: string;
    duration: string;
    quantity: number;
    instructions: string;
    cost: number;
}

// Queue types
export interface QueueItem {
    id: number;
    encounter_id: number;
    patient_id: number;
    patient_name: string;
    patient_dob: string;
    patient_sex: string;
    encounter_number: string;
    visit_type: string;
    reason_for_visit: string;
    queue_position: number;
    estimated_wait_time: string;
    status: string;
    created_at: string;
    patient: Patient;
    encounter: Encounter;
}

// Doctor types
export interface Doctor {
    id: number;
    name: string;
    email: string;
    phone?: string;
    specialization: string;
    license_number: string;
    license?: string;
    clinic_id: number;
    clinic?: Clinic;
    availability?: DoctorAvailability[] | {
        monday: { start: string; end: string; available: boolean };
        tuesday: { start: string; end: string; available: boolean };
        wednesday: { start: string; end: string; available: boolean };
        thursday: { start: string; end: string; available: boolean };
        friday: { start: string; end: string; available: boolean };
        saturday: { start: string; end: string; available: boolean };
        sunday: { start: string; end: string; available: boolean };
    };
    status?: string;
    experience?: string;
    education?: string;
    certifications?: string;
    address?: string;
    emergency_contact?: string;
    emergency_phone?: string;
    notes?: string;
    consultation_fee?: number;
    patients?: number;
    next_appointment?: string;
    nextAppointment?: string;
    rating?: number;
    created_at: string;
    updated_at: string;
}

export interface DoctorAvailability {
    id: number;
    doctor_id: number;
    day_of_week: number;
    start_time: string;
    end_time: string;
    is_available: boolean;
}

// Lab Result types
export interface LabResult {
    id: number;
    patient_id: number;
    patient_name: string;
    encounter_id?: number;
    test_name: string;
    test_type: string;
    result_value: string;
    reference_range: string;
    unit: string;
    status: string;
    ordered_by: string;
    performed_by?: string;
    ordered_at: string;
    completed_at?: string;
    notes?: string;
    file_path?: string;
    created_at: string;
    updated_at: string;
}

// File Asset types
export interface FileAsset {
    id: number;
    patient_id?: number;
    encounter_id?: number;
    file_name: string;
    original_name: string;
    file_path: string;
    file_size: number;
    mime_type: string;
    file_type: string;
    description?: string;
    uploaded_by: number;
    uploaded_by_name: string;
    created_at: string;
    updated_at: string;
}

// Room types
export interface Room {
    id: number;
    name: string;
    room_number: string;
    room_type: string;
    capacity: number;
    clinic_id: number;
    is_available: boolean;
    created_at: string;
    updated_at: string;
}

// Setting types
export interface Setting {
    id: number;
    key: string;
    value: string;
    type: string;
    description?: string;
    clinic_id?: number;
    created_at: string;
    updated_at: string;
}

// Dashboard stats types
export interface DashboardStats {
    todayAppointments: number;
    upcomingAppointments: number;
    totalPatients: number;
    pendingPrescriptions: number;
    activeQueue: number;
    completedEncounters: number;
    recentAppointments: Appointment[];
    recentPrescriptions: Prescription[];
    recentEncounters: Encounter[];
}

// Navigation types
export interface NavItem {
    title: string;
    href: string;
    icon?: React.ComponentType<any> | null;
}

// Breadcrumb types
export interface BreadcrumbItem {
    title: string;
    href: string;
}

// Form types
export interface PatientFormData {
    name: string;
    dob: string;
    sex: string;
    phone: string;
    email: string;
    address: string;
    emergency_contact: string;
    allergies: string;
}

export interface EncounterFormData {
    patient_id: number;
    visit_type: string;
    reason_for_visit: string;
    notes: string;
}

export interface SoapNotesFormData {
    subjective: string;
    objective: string;
    assessment: string;
    plan: string;
}

export interface VitalSignsFormData {
    blood_pressure: string;
    heart_rate: string;
    temperature: string;
    weight: string;
    height: string;
}

export interface PrescriptionFormData {
    patient_id: number;
    encounter_id?: number;
    diagnosis: string;
    instructions: string;
    items: PrescriptionItemFormData[];
}

export interface PrescriptionItemFormData {
    medication_name: string;
    dosage: string;
    frequency: string;
    duration: string;
    quantity: number;
    instructions: string;
}

// API Response types
export interface ApiResponse<T> {
    data: T;
    message?: string;
    status: string;
}

export interface PaginatedResponse<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
}

// Error types
export interface ValidationError {
    field: string;
    message: string;
}

export interface ApiError {
    message: string;
    errors?: Record<string, string[]>;
    status: number;
}

// Filter types
export interface PatientFilters {
    search?: string;
    sex?: string;
    age_range?: {
        min: number;
        max: number;
    };
    last_visit?: {
        from: string;
        to: string;
    };
}

export interface AppointmentFilters {
    date?: string;
    status?: string;
    type?: string;
    doctor_id?: number;
    patient_id?: number;
}

export interface EncounterFilters {
    status?: string;
    visit_type?: string;
    date?: string;
    patient_id?: number;
    doctor_id?: number;
}

// Search types
export interface SearchResult<T> {
    results: T[];
    total: number;
    page: number;
    per_page: number;
}

// Notification types
export interface Notification {
    id: number;
    type: string;
    title: string;
    message: string;
    data?: Record<string, any>;
    read_at?: string;
    created_at: string;
}

// License types
export interface License {
    id: number;
    clinic_id: number;
    license_key: string;
    license_type: string;
    status: string;
    features: string[];
    max_users: number;
    max_patients: number;
    max_clinics: number;
    expires_at: string;
    created_at: string;
    updated_at: string;
}

// Activity Log types
export interface ActivityLog {
    id: number;
    user_id: number;
    user_name: string;
    action: string;
    type: string;
    description: string;
    model_type: string;
    model_id: number;
    changes?: Record<string, any>;
    ip_address?: string;
    user_agent?: string;
    created_at: string;
}

// Medical Representative types
export interface MedrepProduct {
    id: number;
    name: string;
    dosage: string;
    indications: string[];
    pricing: number;
    marketing_material: string;
    status: string;
    created_at: string;
    updated_at: string;
}

export interface MedrepMeeting {
    id: number;
    doctor_id: number;
    doctor_name: string;
    date: string;
    time: string;
    purpose: string;
    status: string;
    notes?: string;
    samples_provided?: string[];
    commitments?: string[];
    created_at: string;
    updated_at: string;
}

export interface MedrepInteraction {
    id: number;
    doctor_id: number;
    doctor_name: string;
    meeting_id?: number;
    notes: string;
    samples_provided: string[];
    commitments: string[];
    follow_up_date?: string;
    created_at: string;
    updated_at: string;
}

// PDF Generation types
export interface PDFGenerationOptions {
    includePatientInfo: boolean;
    includeDoctorInfo: boolean;
    includeSignature: boolean;
    watermark?: string;
    customHeader?: string;
    customFooter?: string;
}

// Permission types
export interface Permission {
    id: number;
    name: string;
    description: string;
    module: string;
    action: string;
}

export interface Role {
    id: number;
    name: string;
    description: string;
    permissions: Permission[];
    created_at: string;
    updated_at: string;
}

// Dashboard types
export interface DashboardStats {
    totalUsers: number;
    totalPatients: number;
    totalAppointments: number;
    totalEncounters: number;
    totalPrescriptions: number;
    totalProducts: number;
    totalMeetings: number;
    totalInteractions: number;
    todayAppointments: number;
    activeQueue: number;
    completedEncounters: number;
    pendingPrescriptions: number;
    upcomingMeetings: number;
    recentActivity: Array<{
        id: number;
        type: string;
        description: string;
        user_name: string;
        created_at: string;
    }>;
}

// Admin Dashboard specific types
export interface AdminDashboardData {
    user: User;
    stats: AdminDashboardStats;
    permissions: string[];
}

export interface AdminDashboardStats {
    totalUsers: number;
    totalPatients: number;
    totalAppointments: number;
    totalEncounters: number;
    totalPrescriptions: number;
    totalProducts: number;
    totalMeetings: number;
    totalInteractions: number;
    todayAppointments: number;
    activeQueue: number;
    completedEncounters: number;
    pendingPrescriptions: number;
    upcomingMeetings: number;
    recentActivity: ActivityLog[];
    changes: {
        totalUsers: string;
        totalPatients: string;
        todayAppointments: string;
        activeQueue: string;
        totalAppointments: string;
        totalEncounters: string;
        pendingPrescriptions: string;
        completedEncounters: string;
    };
}

export interface StatCard {
    title: string;
    value: string | number;
    change: string;
    icon: React.ComponentType<any>;
    color: string;
}

export interface RecentActivity {
    id: number;
    type: 'appointment' | 'staff' | 'patient' | 'system';
    message: string;
    time: string;
    status: 'success' | 'info' | 'warning' | 'error';
}

// Staff Management types
export interface StaffMember {
    id: number;
    name: string;
    email: string;
    phone: string;
    role: string;
    department: string;
    status: 'Active' | 'On Leave' | 'Inactive';
    join_date: string;
    last_active: string;
    address?: string;
    emergency_contact?: string;
    emergency_phone?: string;
    notes?: string;
    is_active: boolean;
}

export interface StaffRole {
    id: number;
    name: string;
    description: string;
}

export interface StaffFormData {
    name: string;
    email: string;
    phone: string;
    role: string;
    department: string;
    status: 'Active' | 'On Leave' | 'Inactive';
    address: string;
    emergency_contact: string;
    emergency_phone: string;
    notes: string;
}

export interface StaffManagementData {
    staff: StaffMember[];
    roles: StaffRole[];
    departments: string[];
    permissions: string[];
}

// Role-based permissions
export const ROLE_PERMISSIONS = {
    superadmin: [
        'manage_users', 'manage_clinics', 'manage_licenses', 'view_analytics',
        'manage_settings', 'view_activity_logs', 'manage_roles', 'manage_permissions',
        'view_system_health', 'manage_backups', 'view_financial_reports'
    ],
    admin: [
        'manage_staff', 'manage_doctors', 'view_appointments', 'view_patients',
        'view_reports', 'manage_settings', 'view_analytics', 'manage_clinic_settings',
        'view_financial_reports', 'manage_rooms', 'manage_schedules'
    ],
    doctor: [
        'work_on_queue', 'view_appointments', 'manage_prescriptions', 'view_medical_records',
        'view_patients', 'view_analytics', 'manage_encounters', 'view_lab_results',
        'manage_treatment_plans', 'view_patient_history', 'manage_soap_notes'
    ],
    receptionist: [
        'search_patients', 'manage_appointments', 'manage_queue', 'register_patients',
        'view_encounters', 'view_reports', 'manage_patient_info', 'view_appointments',
        'manage_check_in', 'view_patient_history', 'manage_insurance'
    ],
    patient: [
        'book_appointments', 'view_medical_records', 'view_prescriptions', 'view_lab_results',
        'view_appointments', 'update_profile', 'download_documents', 'view_billing',
        'manage_notifications', 'view_insurance', 'schedule_follow_ups'
    ],
    medrep: [
        'manage_products', 'schedule_meetings', 'track_interactions', 'manage_doctors',
        'view_analytics', 'manage_samples', 'view_meeting_history', 'manage_territory',
        'view_performance_metrics', 'manage_marketing_materials', 'track_commitments'
    ]
} as const;

export type RoleName = keyof typeof ROLE_PERMISSIONS;
export type PermissionName = typeof ROLE_PERMISSIONS[RoleName][number];
