-- Initial base data for MediNext EMR System
-- This script creates essential permissions, roles, and settings

-- Insert all system permissions
INSERT INTO `permissions` (`name`, `slug`, `description`, `module`, `action`, `created_at`, `updated_at`) VALUES
-- System permissions
('System Admin', 'system.admin', 'System administration access', 'system', 'admin', NOW(), NOW()),
('System Info', 'system.info', 'View system information', 'system', 'info', NOW(), NOW()),
('View System Status', 'system.status', 'View system status and monitoring', 'system', 'status', NOW(), NOW()),
('System Monitor', 'system.monitor', 'Monitor system performance and health', 'system', 'monitor', NOW(), NOW()),

-- Clinic permissions
('Manage Clinics', 'clinics.manage', 'Full control over clinic operations', 'clinics', 'manage', NOW(), NOW()),
('View Clinics', 'clinics.view', 'View clinic information', 'clinics', 'view', NOW(), NOW()),
('Create Clinics', 'clinics.create', 'Create new clinics', 'clinics', 'create', NOW(), NOW()),
('Edit Clinics', 'clinics.edit', 'Edit clinic information', 'clinics', 'edit', NOW(), NOW()),
('Delete Clinics', 'clinics.delete', 'Delete clinics', 'clinics', 'delete', NOW(), NOW()),

-- Doctor permissions
('Manage Doctors', 'doctors.manage', 'Full control over doctor operations', 'doctors', 'manage', NOW(), NOW()),
('View Doctors', 'doctors.view', 'View doctor information', 'doctors', 'view', NOW(), NOW()),
('Create Doctors', 'doctors.create', 'Add new doctors', 'doctors', 'create', NOW(), NOW()),
('Edit Doctors', 'doctors.edit', 'Edit doctor information', 'doctors', 'edit', NOW(), NOW()),
('Delete Doctors', 'doctors.delete', 'Remove doctors', 'doctors', 'delete', NOW(), NOW()),

-- Staff permissions
('Manage Staff', 'staff.manage', 'Full control over staff operations', 'staff', 'manage', NOW(), NOW()),
('View Staff', 'staff.view', 'View staff information', 'staff', 'view', NOW(), NOW()),
('Create Staff', 'staff.create', 'Add new staff members', 'staff', 'create', NOW(), NOW()),
('Edit Staff', 'staff.edit', 'Edit staff information', 'staff', 'edit', NOW(), NOW()),
('Delete Staff', 'staff.delete', 'Remove staff members', 'staff', 'delete', NOW(), NOW()),

-- Patient permissions
('Manage Patients', 'patients.manage', 'Full control over patient operations', 'patients', 'manage', NOW(), NOW()),
('View Patients', 'patients.view', 'View patient information', 'patients', 'view', NOW(), NOW()),
('Create Patients', 'patients.create', 'Add new patients', 'patients', 'create', NOW(), NOW()),
('Edit Patients', 'patients.edit', 'Edit patient information', 'patients', 'edit', NOW(), NOW()),
('Delete Patients', 'patients.delete', 'Remove patients', 'patients', 'delete', NOW(), NOW()),

-- Appointment permissions
('Manage Appointments', 'appointments.manage', 'Full control over appointment operations', 'appointments', 'manage', NOW(), NOW()),
('View Appointments', 'appointments.view', 'View appointment information', 'appointments', 'view', NOW(), NOW()),
('Create Appointments', 'appointments.create', 'Schedule new appointments', 'appointments', 'create', NOW(), NOW()),
('Edit Appointments', 'appointments.edit', 'Modify appointments', 'appointments', 'edit', NOW(), NOW()),
('Cancel Appointments', 'appointments.cancel', 'Cancel appointments', 'appointments', 'cancel', NOW(), NOW()),
('Delete Appointments', 'appointments.delete', 'Remove appointments', 'appointments', 'delete', NOW(), NOW()),
('Check-in Patients', 'appointments.checkin', 'Check-in patients for appointments', 'appointments', 'checkin', NOW(), NOW()),

-- Prescription permissions
('Manage Prescriptions', 'prescriptions.manage', 'Full control over prescription operations', 'prescriptions', 'manage', NOW(), NOW()),
('View Prescriptions', 'prescriptions.view', 'View prescription information', 'prescriptions', 'view', NOW(), NOW()),
('Create Prescriptions', 'prescriptions.create', 'Write new prescriptions', 'prescriptions', 'create', NOW(), NOW()),
('Edit Prescriptions', 'prescriptions.edit', 'Modify prescriptions', 'prescriptions', 'edit', NOW(), NOW()),
('Delete Prescriptions', 'prescriptions.delete', 'Remove prescriptions', 'prescriptions', 'delete', NOW(), NOW()),
('Download Prescriptions', 'prescriptions.download', 'Download prescription PDFs', 'prescriptions', 'download', NOW(), NOW()),

-- Medical Records permissions
('Manage Medical Records', 'medical_records.manage', 'Full control over medical records', 'medical_records', 'manage', NOW(), NOW()),
('View Medical Records', 'medical_records.view', 'View patient medical records', 'medical_records', 'view', NOW(), NOW()),
('Create Medical Records', 'medical_records.create', 'Create new medical records', 'medical_records', 'create', NOW(), NOW()),
('Edit Medical Records', 'medical_records.edit', 'Modify medical records', 'medical_records', 'edit', NOW(), NOW()),
('Delete Medical Records', 'medical_records.delete', 'Delete medical records', 'medical_records', 'delete', NOW(), NOW()),

-- User management permissions
('Manage Users', 'users.manage', 'Full control over user operations', 'users', 'manage', NOW(), NOW()),
('View Users', 'users.view', 'View user information', 'users', 'view', NOW(), NOW()),
('Create Users', 'users.create', 'Create new users', 'users', 'create', NOW(), NOW()),
('Edit Users', 'users.edit', 'Edit user information', 'users', 'edit', NOW(), NOW()),
('Delete Users', 'users.delete', 'Remove users', 'users', 'delete', NOW(), NOW()),
('Activate Users', 'users.activate', 'Activate user accounts', 'users', 'activate', NOW(), NOW()),
('Deactivate Users', 'users.deactivate', 'Deactivate user accounts', 'users', 'deactivate', NOW(), NOW()),

-- Role management permissions
('Manage Roles', 'roles.manage', 'Full control over role operations', 'roles', 'manage', NOW(), NOW()),
('View Roles', 'roles.view', 'View role information', 'roles', 'view', NOW(), NOW()),
('Create Roles', 'roles.create', 'Create new roles', 'roles', 'create', NOW(), NOW()),
('Edit Roles', 'roles.edit', 'Edit role information', 'roles', 'edit', NOW(), NOW()),
('Delete Roles', 'roles.delete', 'Remove roles', 'roles', 'delete', NOW(), NOW()),

-- Permission management
('Manage Permissions', 'permissions.manage', 'Full control over permission operations', 'permissions', 'manage', NOW(), NOW()),
('View Permissions', 'permissions.view', 'View permission information', 'permissions', 'view', NOW(), NOW()),
('Create Permissions', 'permissions.create', 'Create new permissions', 'permissions', 'create', NOW(), NOW()),
('Edit Permissions', 'permissions.edit', 'Edit permission information', 'permissions', 'edit', NOW(), NOW()),
('Delete Permissions', 'permissions.delete', 'Delete permissions', 'permissions', 'delete', NOW(), NOW()),

-- Settings permissions
('Manage Settings', 'settings.manage', 'Manage system settings', 'settings', 'manage', NOW(), NOW()),
('View Settings', 'settings.view', 'View system settings', 'settings', 'view', NOW(), NOW()),

-- Dashboard permissions
('View Dashboard', 'dashboard.view', 'View dashboard', 'dashboard', 'view', NOW(), NOW()),
('View Dashboard Stats', 'dashboard.stats', 'View dashboard statistics', 'dashboard', 'stats', NOW(), NOW()),

-- MedRep permissions
('Manage MedRep Visits', 'medrep_visits.manage', 'Full control over medrep visit operations', 'medrep', 'manage', NOW(), NOW()),
('View MedRep Visits', 'medrep_visits.view', 'View medrep visit information', 'medrep', 'view', NOW(), NOW()),
('View Interactions', 'interactions.view', 'View interaction information', 'medrep', 'view', NOW(), NOW()),
('View Products', 'products.view', 'View product information', 'medrep', 'view', NOW(), NOW()),
('Create Meetings', 'meetings.create', 'Schedule new meetings', 'medrep', 'create', NOW(), NOW()),

-- Reports permissions
('View Reports', 'reports.view', 'View reports and analytics', 'reports', 'view', NOW(), NOW()),

-- Profile permissions
('View Profile', 'profile.view', 'View user profile information', 'profile', 'view', NOW(), NOW()),

-- Billing permissions
('View Billing', 'billing.view', 'View billing information', 'billing', 'view', NOW(), NOW()),

-- File Assets permissions
('Download File Assets', 'file_assets.download', 'Download file assets and documents', 'file_assets', 'download', NOW(), NOW()),
('Upload File Assets', 'file_assets.upload', 'Upload file assets and documents', 'file_assets', 'upload', NOW(), NOW()),

-- Encounters permissions
('View Encounters', 'encounters.view', 'View encounter information', 'encounters', 'view', NOW(), NOW()),
('Complete Encounters', 'encounters.complete', 'Complete encounter sessions', 'encounters', 'complete', NOW(), NOW()),

-- Insurance permissions
('View Insurance', 'insurance.view', 'View insurance information', 'insurance', 'view', NOW(), NOW()),

-- Lab Results permissions
('View Lab Results', 'lab_results.view', 'View lab results', 'lab_results', 'view', NOW(), NOW()),

-- Notifications permissions
('View Notifications', 'notifications.view', 'View notifications', 'notifications', 'view', NOW(), NOW()),

-- Queue permissions
('Manage Queue', 'queue.manage', 'Full control over queue operations', 'queue', 'manage', NOW(), NOW()),
('View Queue', 'queue.view', 'View queue information', 'queue', 'view', NOW(), NOW()),
('Add to Queue', 'queue.add', 'Add patients to queue', 'queue', 'add', NOW(), NOW()),
('Remove from Queue', 'queue.remove', 'Remove patients from queue', 'queue', 'remove', NOW(), NOW()),
('Process Queue', 'queue.process', 'Process queue items', 'queue', 'process', NOW(), NOW()),

-- Room permissions
('Manage Rooms', 'rooms.manage', 'Full control over room operations', 'rooms', 'manage', NOW(), NOW()),
('View Rooms', 'rooms.view', 'View room information', 'rooms', 'view', NOW(), NOW()),
('Create Rooms', 'rooms.create', 'Create new rooms', 'rooms', 'create', NOW(), NOW()),
('Edit Rooms', 'rooms.edit', 'Edit room information', 'rooms', 'edit', NOW(), NOW()),
('Delete Rooms', 'rooms.delete', 'Delete rooms', 'rooms', 'delete', NOW(), NOW()),

-- Schedule permissions
('Manage Schedules', 'schedules.manage', 'Full control over schedule operations', 'schedules', 'manage', NOW(), NOW()),
('View Schedules', 'schedules.view', 'View schedule information', 'schedules', 'view', NOW(), NOW()),
('Create Schedules', 'schedules.create', 'Create new schedules', 'schedules', 'create', NOW(), NOW()),
('Edit Schedules', 'schedules.edit', 'Edit schedule information', 'schedules', 'edit', NOW(), NOW()),
('Delete Schedules', 'schedules.delete', 'Delete schedules', 'schedules', 'delete', NOW(), NOW()),

-- System monitoring permissions
('View Activity Logs', 'activity_logs.view', 'View system activity logs', 'activity_logs', 'view', NOW(), NOW()),
('Export Activity Logs', 'activity_logs.export', 'Export system activity logs', 'activity_logs', 'export', NOW(), NOW()),
('Manage Backups', 'backups.manage', 'Create and manage system backups', 'backups', 'manage', NOW(), NOW()),

-- Search permissions
('Search Patients', 'search.patients', 'Search patient records', 'search', 'patients', NOW(), NOW()),
('Search Doctors', 'search.doctors', 'Search doctor records', 'search', 'doctors', NOW(), NOW()),
('Global Search', 'search.global', 'Perform global searches', 'search', 'global', NOW(), NOW());

-- Insert user roles
INSERT INTO `roles` (`name`, `slug`, `display_name`, `description`, `is_system_role`, `created_at`, `updated_at`) VALUES
('superadmin', 'superadmin', 'Super Admin', 'Full system access and management. Can manage all clinics, users, and system settings.', 1, NOW(), NOW()),
('admin', 'admin', 'Administrator', 'Clinic administrator with full access to clinic operations and user management.', 1, NOW(), NOW()),
('doctor', 'doctor', 'Doctor', 'Medical professional with access to patient records, appointments, and prescriptions.', 1, NOW(), NOW()),
('receptionist', 'receptionist', 'Receptionist', 'Front desk staff with access to appointments, patient check-in, and basic patient information.', 1, NOW(), NOW()),
('patient', 'patient', 'Patient', 'Patient access to view their own records and appointments.', 1, NOW(), NOW()),
('medrep', 'medrep', 'Medical Representative', 'Medical representative with access to visit management and product information.', 1, NOW(), NOW());

-- Assign permissions to superadmin role (all permissions)
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id
FROM `roles` r, `permissions` p
WHERE r.name = 'superadmin';

-- Assign permissions to admin role
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id
FROM `roles` r, `permissions` p
WHERE r.name = 'admin' 
AND p.slug IN (
    'clinics.manage', 'clinics.view', 'clinics.edit',
    'users.manage', 'users.view', 'users.create', 'users.edit', 'users.delete',
    'roles.view', 'roles.create', 'roles.edit',
    'doctors.manage', 'doctors.view', 'doctors.create', 'doctors.edit', 'doctors.delete',
    'staff.manage', 'staff.view', 'staff.create', 'staff.edit', 'staff.delete',
    'patients.manage', 'patients.view', 'patients.create', 'patients.edit', 'patients.delete',
    'appointments.manage', 'appointments.view', 'appointments.create', 'appointments.edit', 'appointments.cancel', 'appointments.delete', 'appointments.checkin',
    'prescriptions.manage', 'prescriptions.view', 'prescriptions.create', 'prescriptions.edit', 'prescriptions.delete', 'prescriptions.download',
    'medical_records.manage', 'medical_records.view', 'medical_records.create', 'medical_records.edit', 'medical_records.delete',
    'settings.manage', 'settings.view',
    'dashboard.view', 'dashboard.stats',
    'reports.view', 'profile.view', 'billing.view', 'file_assets.download',
    'encounters.view', 'insurance.view', 'lab_results.view', 'notifications.view',
    'queue.manage', 'queue.view', 'rooms.manage', 'rooms.view', 'rooms.create', 'rooms.edit', 'rooms.delete',
    'schedules.manage', 'schedules.view', 'schedules.create', 'schedules.edit', 'schedules.delete',
    'system.status', 'activity_logs.view', 'activity_logs.export', 'backups.manage', 'system.monitor',
    'users.activate', 'users.deactivate',
    'permissions.manage', 'permissions.view', 'permissions.create', 'permissions.edit', 'permissions.delete',
    'file_assets.upload', 'encounters.complete',
    'queue.add', 'queue.remove', 'queue.process',
    'search.patients', 'search.doctors', 'search.global'
);

-- Assign permissions to doctor role
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id
FROM `roles` r, `permissions` p
WHERE r.name = 'doctor' 
AND p.slug IN (
    'clinics.view', 'doctors.view',
    'patients.view', 'patients.edit',
    'appointments.view', 'appointments.create', 'appointments.edit', 'appointments.cancel',
    'prescriptions.view', 'prescriptions.create', 'prescriptions.edit', 'prescriptions.delete', 'prescriptions.download',
    'medical_records.view', 'medical_records.create', 'medical_records.edit',
    'dashboard.view', 'dashboard.stats',
    'profile.view', 'encounters.view', 'lab_results.view', 'queue.view'
);

-- Assign permissions to receptionist role
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id
FROM `roles` r, `permissions` p
WHERE r.name = 'receptionist' 
AND p.slug IN (
    'clinics.view', 'doctors.view',
    'patients.view', 'patients.create', 'patients.edit',
    'appointments.view', 'appointments.create', 'appointments.edit', 'appointments.cancel', 'appointments.checkin',
    'dashboard.view', 'dashboard.stats',
    'profile.view', 'encounters.view', 'insurance.view', 'reports.view',
    'queue.manage', 'queue.view', 'rooms.manage', 'rooms.view', 'rooms.create', 'rooms.edit', 'rooms.delete',
    'schedules.manage', 'schedules.view', 'schedules.create', 'schedules.edit', 'schedules.delete',
    'system.status', 'activity_logs.view', 'activity_logs.export', 'backups.manage', 'system.monitor',
    'users.activate', 'users.deactivate',
    'permissions.manage', 'permissions.view', 'permissions.create', 'permissions.edit', 'permissions.delete',
    'file_assets.upload', 'encounters.complete',
    'queue.add', 'queue.remove', 'queue.process',
    'search.patients', 'search.doctors', 'search.global'
);

-- Assign permissions to patient role
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id
FROM `roles` r, `permissions` p
WHERE r.name = 'patient' 
AND p.slug IN (
    'clinics.view', 'doctors.view',
    'appointments.view', 'appointments.create', 'appointments.cancel',
    'prescriptions.view', 'prescriptions.download',
    'medical_records.view', 'dashboard.view',
    'schedules.view', 'profile.view', 'billing.view', 'file_assets.download',
    'encounters.view', 'insurance.view', 'lab_results.view', 'notifications.view'
);

-- Assign permissions to medrep role
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id
FROM `roles` r, `permissions` p
WHERE r.name = 'medrep' 
AND p.slug IN (
    'dashboard.view', 'dashboard.stats',
    'medrep_visits.manage', 'medrep_visits.view', 'interactions.view', 'products.view', 'meetings.create',
    'doctors.view', 'schedules.view', 'reports.view', 'profile.view'
);

-- Insert comprehensive system settings
INSERT INTO `settings` (`key`, `value`, `type`, `group`, `description`, `is_public`, `clinic_id`, `created_at`, `updated_at`) VALUES
-- System Information
('system.name', 'MediNext EMR', 'string', 'system', 'System name', 1, NULL, NOW(), NOW()),
('system.version', '1.0.0', 'string', 'system', 'System version', 0, NULL, NOW(), NOW()),
('system.timezone', 'Asia/Manila', 'string', 'system', 'Default timezone for the clinic', 0, NULL, NOW(), NOW()),
('system.date_format', 'Y-m-d', 'string', 'system', 'Date format for display', 0, NULL, NOW(), NOW()),
('system.time_format', 'H:i', 'string', 'system', 'Time format for display', 0, NULL, NOW(), NOW()),
('system.currency', 'PHP', 'string', 'system', 'Default currency for the clinic', 0, NULL, NOW(), NOW()),

-- Clinic Information
('clinic.name', 'MediNext EMR Clinic', 'string', 'clinic', 'The name of your clinic', 1, NULL, NOW(), NOW()),
('clinic.phone', '+63 123 456 7890', 'string', 'clinic', 'Primary contact phone number', 1, NULL, NOW(), NOW()),
('clinic.email', 'info@yourclinic.com', 'string', 'clinic', 'Primary contact email', 1, NULL, NOW(), NOW()),
('clinic.address', '{"street":"123 Main Street","city":"Manila","state":"Metro Manila","postal_code":"1000","country":"Philippines"}', 'json', 'clinic', 'Clinic address information', 1, NULL, NOW(), NOW()),

-- Security Settings
('security.session_timeout', '120', 'integer', 'security', 'Session timeout in minutes', 0, NULL, NOW(), NOW()),

-- Appointment Settings
('appointments.default_duration', '30', 'integer', 'appointments', 'Default appointment duration in minutes', 1, NULL, NOW(), NOW()),

-- Notification Settings
('notifications.email_enabled', 'true', 'boolean', 'notifications', 'Enable email notifications', 0, NULL, NOW(), NOW()),
('notifications.sms_enabled', 'false', 'boolean', 'notifications', 'Enable SMS notifications', 0, NULL, NOW(), NOW()),
('notifications.appointment_reminder_hours', '24', 'integer', 'notifications', 'Hours before appointment to send reminder', 0, NULL, NOW(), NOW()),

-- Branding Settings
('branding.primary_color', '#3B82F6', 'string', 'branding', 'Primary brand color (hex)', 1, NULL, NOW(), NOW()),
('branding.secondary_color', '#1E40AF', 'string', 'branding', 'Secondary brand color (hex)', 1, NULL, NOW(), NOW());

-- Create initial activity log entry
INSERT INTO `activity_log` (`log_name`, `description`, `subject_type`, `subject_id`, `causer_type`, `causer_id`, `properties`, `created_at`, `updated_at`) VALUES
('system', 'System installation completed', 'system', 1, 'system', NULL, '{"version":"1.0.0","installation_date":"' + NOW() + '"}', NOW(), NOW());
