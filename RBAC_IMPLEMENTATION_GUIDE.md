# RBAC (Role-Based Access Control) Implementation Guide

## Overview

This document outlines the comprehensive RBAC system implemented for the medical application, providing fine-grained permissions and role-based access control across all modules.

## Role Definitions

### 1. Super Admin (Platform)
- **Description**: Platform administrator with full system access. Manages tenants, clinics, plans, and global settings.
- **Key Capabilities**:
  - Manage platform tenants and clinics
  - Manage subscription plans
  - Configure global platform settings
  - Full access to all modules and data
- **Permissions**: All permissions across the system

### 2. Admin (Clinic Owner/Manager)
- **Description**: Clinic owner/manager with full access within clinic. Can manage staff, billing, and settings.
- **Key Capabilities**:
  - Full access within assigned clinic(s)
  - Manage clinic staff and roles
  - Handle billing and financial operations
  - Configure clinic settings
  - Access all patient and clinical data
- **Permissions**: 
  - `patient.manage`, `emr.manage`, `schedule.manage`
  - `rx.issue`, `rx.view`, `rx.edit`, `rx.download`
  - `billing.manage`, `settings.manage`, `staff.manage`
  - `clinical_notes.read`, `clinical_notes.write`

### 3. Doctor
- **Description**: Medical professional who can view own schedule, manage assigned patients' EMR, issue prescriptions, and view med samples.
- **Key Capabilities**:
  - View and manage own schedule
  - Access assigned patients' EMR
  - Issue and modify prescriptions
  - View medical samples from MedReps
  - Create and edit clinical notes
- **Permissions**:
  - `patient.read`, `patient.write`, `emr.read`, `emr.write`
  - `schedule.view`, `schedule.manage`
  - `rx.issue`, `rx.view`, `rx.edit`, `rx.download`
  - `clinical_notes.read`, `clinical_notes.write`

### 4. Receptionist
- **Description**: Front desk staff who can manage calendar, patients, visits, and billing support. No access to clinical notes by default.
- **Key Capabilities**:
  - Manage appointment calendar
  - Handle patient check-ins
  - Process billing support
  - Manage patient information (non-clinical)
  - **Restriction**: No access to clinical notes or EMR data
- **Permissions**:
  - `patient.read`, `patient.write`
  - `schedule.view`, `schedule.manage`
  - `billing.view`, `billing.create`, `billing.edit`

### 5. Patient
- **Description**: Self-service portal access. Can book, reschedule, cancel appointments, view summary, and download prescriptions.
- **Key Capabilities**:
  - Book, reschedule, and cancel appointments
  - View appointment summaries
  - Download prescriptions
  - Edit own profile information
- **Permissions**:
  - `patient.read`, `schedule.view`
  - `rx.view`, `rx.download`
  - `profile.edit`

### 6. Medical Representative
- **Description**: Medical representative who can schedule visits with doctors and upload product sheets. No access to patient data.
- **Key Capabilities**:
  - Schedule visits with doctors
  - Upload product sheets and materials
  - Track interactions with healthcare providers
  - **Restriction**: No access to patient data or clinical information
- **Permissions**:
  - `medrep.schedule`, `medrep.upload`, `medrep.view`
  - `schedule.view`, `schedule.manage`

## Permission Scopes

### Fine-Grained Permissions

#### Patient Management
- `patient.read` - Read patient information and records
- `patient.write` - Create and modify patient information
- `patient.delete` - Delete patient records
- `patient.manage` - Full patient management access

#### EMR (Electronic Medical Records)
- `emr.read` - Read electronic medical records
- `emr.write` - Create and modify medical records
- `emr.delete` - Delete medical records
- `emr.manage` - Full EMR management access

#### Scheduling
- `schedule.view` - View appointment schedules
- `schedule.manage` - Manage appointment schedules and availability

#### Prescriptions
- `rx.issue` - Issue prescriptions to patients
- `rx.view` - View prescription information
- `rx.edit` - Modify prescriptions
- `rx.download` - Download prescription documents

#### Billing
- `billing.view` - View billing information and invoices
- `billing.create` - Create billing records
- `billing.edit` - Modify billing information
- `billing.manage` - Full billing management access

#### Settings
- `settings.manage` - Manage system and clinic settings
- `settings.view` - View system settings

#### Staff Management
- `staff.manage` - Manage clinic staff and roles
- `staff.view` - View staff information
- `staff.create` - Add new staff members
- `staff.edit` - Edit staff information

#### Clinical Notes
- `clinical_notes.read` - Read clinical notes and documentation
- `clinical_notes.write` - Create and modify clinical notes

#### MedRep Specific
- `medrep.schedule` - Schedule visits with doctors
- `medrep.upload` - Upload product sheets and materials
- `medrep.view` - View medical representative information

#### Super Admin
- `tenants.manage` - Manage platform tenants and clinics
- `plans.manage` - Manage subscription plans
- `global.settings` - Manage global platform settings

## Implementation Details

### Middleware

#### CheckRole
```php
Route::middleware(['role:admin,doctor'])->group(function () {
    // Routes accessible by admin or doctor roles
});
```

#### CheckPermission
```php
Route::middleware(['permission:patient.read,patient.write'])->group(function () {
    // Routes accessible by users with patient.read OR patient.write permission
});
```

#### CheckAllPermissions
```php
Route::middleware(['permission.all:patient.read,patient.write'])->group(function () {
    // Routes accessible by users with BOTH patient.read AND patient.write permissions
});
```

#### CheckClinicalAccess
```php
Route::middleware(['clinical.access'])->group(function () {
    // Routes accessible by users who can access clinical data
});
```

### User Model Methods

#### Role Checking
```php
$user->isSuperAdmin();
$user->isAdmin();
$user->isDoctor();
$user->isPatient();
$user->isReceptionist();
$user->isMedRep();
$user->getPrimaryRole();
```

#### Permission Checking
```php
$user->hasPermissionInClinic('patient.read', $clinicId);
$user->hasAnyPermissionInClinic(['patient.read', 'patient.write'], $clinicId);
$user->hasAllPermissionsInClinic(['patient.read', 'patient.write'], $clinicId);
$user->getPermissionsInClinic($clinicId);
```

#### Specialized Access Checks
```php
$user->canAccessClinicalNotes($clinicId);
$user->canAccessPatientData($clinicId);
```

### Role Model Methods

#### Permission Management
```php
$role->hasPermission('patient.read');
$role->hasAnyPermission(['patient.read', 'patient.write']);
$role->hasAllPermissions(['patient.read', 'patient.write']);
$role->getDefaultPermissions();
```

#### Role Information
```php
$role->getCapabilitiesDescription();
$role->getSecurityLevel();
$role->isSystemRole();
$role->canBeModified();
$role->canBeDeleted();
```

## Security Considerations

### Access Control Levels
1. **Critical**: Super Admin - Full system access
2. **High**: Admin - Full clinic access
3. **Medium-High**: Doctor - Clinical data access
4. **Medium**: Receptionist, MedRep - Limited access
5. **Low**: Patient - Self-service only

### Data Protection
- **Clinical Notes**: Restricted to doctors, admins, and super admins
- **Patient Data**: Accessible by clinical staff, restricted for MedReps
- **Billing Data**: Accessible by admins, receptionists, and super admins
- **System Settings**: Restricted to admins and super admins

### Multi-Clinic Support
- All permissions are clinic-scoped
- Users can have different roles in different clinics
- Super admins have cross-clinic access
- Clinic context is required for all permission checks

## Usage Examples

### Controller Implementation
```php
class PatientController extends Controller
{
    public function index(Request $request)
    {
        $clinicId = $request->header('X-Clinic-ID');
        
        if (!$this->user->hasPermissionInClinic('patient.read', $clinicId)) {
            return response()->json(['error' => 'Insufficient permissions'], 403);
        }
        
        // Controller logic
    }
}
```

### Route Protection
```php
// Admin and doctor routes
Route::middleware(['role:admin,doctor'])->group(function () {
    Route::get('/patients', [PatientController::class, 'index']);
});

// Clinical data routes
Route::middleware(['clinical.access'])->group(function () {
    Route::get('/emr/{patient}', [EMRController::class, 'show']);
});

// Prescription routes
Route::middleware(['permission:rx.issue,rx.view'])->group(function () {
    Route::post('/prescriptions', [PrescriptionController::class, 'store']);
});
```

### Frontend Integration
```javascript
// Check user permissions in frontend
const userPermissions = await api.get('/user/permissions');
const canAccessClinical = userPermissions.includes('clinical_notes.read');
const canIssuePrescriptions = userPermissions.includes('rx.issue');
```

## Migration and Seeding

### Running Seeders
```bash
php artisan db:seed --class=PermissionSeeder
php artisan db:seed --class=UserRoleSeeder
```

### Database Structure
- `roles` - Role definitions with system flags
- `permissions` - Fine-grained permission scopes
- `role_permissions` - Many-to-many relationship
- `user_clinic_roles` - User roles within specific clinics

## Best Practices

1. **Always check clinic context** - All permission checks should include clinic ID
2. **Use appropriate middleware** - Apply role/permission middleware at route level
3. **Implement fail-safe defaults** - Deny access by default, grant explicitly
4. **Regular permission audits** - Review and update permissions regularly
5. **Document access patterns** - Keep clear documentation of who can access what
6. **Test permission boundaries** - Ensure users cannot access unauthorized data

## Troubleshooting

### Common Issues
1. **Missing clinic context** - Ensure clinic ID is provided in requests
2. **Permission not found** - Check if permission exists in database
3. **Role not assigned** - Verify user has role in specific clinic
4. **Middleware not applied** - Check route middleware configuration

### Debugging
```php
// Check user's current permissions
$permissions = $user->getPermissionsInClinic($clinicId);
$primaryRole = $user->getPrimaryRole();
$canAccessClinical = $user->canAccessClinicalNotes($clinicId);
```

This RBAC system provides comprehensive access control while maintaining flexibility for future enhancements and specific business requirements.
