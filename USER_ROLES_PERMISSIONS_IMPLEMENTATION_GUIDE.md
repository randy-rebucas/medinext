# User Roles & Permissions System - Implementation Guide

## Overview

This document provides a comprehensive guide to the user roles and permissions system implemented in MediNext. The system provides granular, configurable access control with predefined roles for different user types in the healthcare system.

## System Architecture

### Core Components

1. **Role Management** - Define and manage user roles
2. **Permission Management** - Granular permission system
3. **User-Role Assignment** - Clinic-specific role assignments
4. **Access Control** - Permission-based authorization
5. **Security Validation** - Permission conflict detection

### Database Schema

```
roles (id, name, description, is_system_role, permissions_config)
├── permissions (id, name, slug, description, module, action)
├── role_permissions (role_id, permission_id)
└── user_clinic_roles (user_id, clinic_id, role_id)
```

## Predefined Roles

### 1. **Admin** - Full System Access and Management
- **Description**: Full clinic access and management. Can manage clinic operations, staff, and patients.
- **Security Level**: High
- **Key Capabilities**:
  - Manage all clinics and their operations
  - Full user management and role assignment
  - Complete patient and doctor management
  - Billing and financial oversight
  - System settings and configuration
  - Report generation and export

### 2. **Doctor** - Clinical Workflow Management
- **Description**: Medical professional who can manage appointments, medical records, and prescriptions.
- **Security Level**: Medium-High
- **Key Capabilities**:
  - View and manage patient appointments
  - Create and edit medical records
  - Write and modify prescriptions
  - Manage personal schedule
  - View patient information
  - Access clinical reports

### 3. **Patient** - Self-Service Access
- **Description**: Patient who can book appointments, view records, and download prescriptions.
- **Security Level**: Low
- **Key Capabilities**:
  - Book and manage appointments
  - View medical records and history
  - Download prescription documents
  - Edit personal profile
  - View clinic and doctor information

### 4. **Receptionist** - Front Desk Operations
- **Description**: Front desk staff who can schedule appointments, manage patient check-ins, and handle billing support.
- **Security Level**: Medium
- **Key Capabilities**:
  - Schedule and manage appointments
  - Patient check-in and registration
  - Basic billing operations
  - Patient record management
  - Schedule viewing and coordination

### 5. **Medical Representative** - Product and Meeting Management
- **Description**: Medical representative who can manage product details, schedule doctor meetings, and track interactions.
- **Security Level**: Medium
- **Key Capabilities**:
  - Manage product information
  - Schedule and manage meetings with doctors
  - Track interaction history
  - View clinic and doctor information
  - Generate interaction reports

## Permission System

### Permission Structure

Permissions follow the format: `{module}.{action}`

#### Modules
- **clinics** - Clinic management operations
- **doctors** - Doctor management operations
- **patients** - Patient management operations
- **appointments** - Appointment scheduling and management
- **prescriptions** - Prescription management
- **medical_records** - Medical record operations
- **users** - User account management
- **roles** - Role and permission management
- **billing** - Billing and financial operations
- **reports** - Reporting and analytics
- **settings** - System configuration
- **schedule** - Scheduling operations
- **products** - Product management
- **meetings** - Meeting management
- **interactions** - Interaction tracking
- **profile** - Profile management

#### Actions
- **view** - Read-only access (Low risk)
- **create** - Create new records (Medium risk)
- **edit** - Modify existing records (Medium risk)
- **delete** - Remove records (High risk)
- **manage** - Full control (High risk)
- **export** - Export data (Medium risk)
- **download** - Download files (Low risk)
- **checkin** - Patient check-in (Low risk)
- **cancel** - Cancel operations (Low risk)

### Risk Levels

- **High Risk**: `delete`, `manage` - Critical operations requiring careful oversight
- **Medium Risk**: `create`, `edit`, `export` - Operations that modify data
- **Low Risk**: `view`, `download`, `checkin`, `cancel` - Safe read operations

## Implementation Details

### 1. Role Model (`app/Models/Role.php`)

#### Key Features
- **System Role Protection**: System roles cannot be modified or deleted
- **Permission Management**: Comprehensive permission assignment and validation
- **Security Levels**: Risk-based security classification
- **Usage Statistics**: Track role usage across clinics

#### Enhanced Methods
```php
// Get role capabilities
$role->capabilities_description;

// Get security level
$role->security_level;

// Check if role can be modified
$role->canBeModified();

// Validate permissions
$errors = $role->validatePermissions();

// Get usage statistics
$stats = $role->usage_statistics;
```

#### Scopes
```php
// Filter system roles
Role::systemRoles()->get();

// Filter non-system roles
Role::nonSystemRoles()->get();

// Filter by name
Role::byName('admin')->get();
```

### 2. Permission Model (`app/Models/Permission.php`)

#### Key Features
- **Categorization**: Group permissions by functional area
- **Risk Assessment**: Automatic risk level classification
- **Dependency Management**: Track permission dependencies
- **Conflict Detection**: Identify conflicting permissions

#### Enhanced Methods
```php
// Get permission category
$permission->category;

// Get risk level
$permission->risk_level;

// Check if critical
$permission->isCritical();

// Get dependencies
$deps = $permission->dependencies;

// Check conflicts
$conflicts = $permission->conflictsWith($otherPermission);
```

#### Scopes
```php
// Filter by module
Permission::byModule('clinics')->get();

// Filter by action
Permission::byAction('view')->get();

// Filter by type
Permission::byType('manage')->get();
```

### 3. User-Clinic-Role Management

#### Key Features
- **Multi-clinic Support**: Users can have different roles in different clinics
- **Clinic-specific Access**: Permissions are scoped to specific clinics
- **Role Validation**: Ensure roles have minimum required permissions

#### Usage Examples
```php
// Check user role in specific clinic
if ($user->hasRoleInClinic('admin', $clinicId)) {
    // User is admin in this clinic
}

// Check specific permission
if ($user->hasPermissionInClinic('patients.create', $clinicId)) {
    // User can create patients in this clinic
}

// Check if user is doctor in clinic
if ($user->isDoctorInClinic($clinicId)) {
    // User is a doctor in this clinic
}
```

## Nova Admin Interface

### 1. Role Resource (`app/Nova/Role.php`)

#### Enhanced Fields
- **Basic Information**: Name, description, system role flag
- **Security Classification**: Security level, capabilities description
- **Permission Management**: Permission assignment and configuration
- **Usage Statistics**: User and clinic usage tracking
- **Validation**: Permission validation and minimum requirements

#### Features
- **Security Level Badges**: Color-coded risk indicators
- **Capability Descriptions**: Clear role purpose explanation
- **Usage Tracking**: Monitor role usage across system
- **Validation Checks**: Ensure proper permission configuration

### 2. Permission Resource (`app/Nova/Permission.php`)

#### Enhanced Fields
- **Permission Structure**: Module and action selection
- **Classification**: Category and risk level indicators
- **Context**: Detailed permission descriptions
- **Dependencies**: Permission dependency tracking
- **Usage Statistics**: Role and user usage monitoring

#### Features
- **Structured Input**: Dropdown selections for modules and actions
- **Risk Level Badges**: Visual risk indication
- **Contextual Help**: Detailed permission explanations
- **Dependency Management**: Track permission relationships

## Security Features

### 1. Permission Validation

```php
// Validate role permissions
$errors = $role->validatePermissions();

// Check minimum requirements
if (!$role->hasMinimumPermissions()) {
    // Role lacks required permissions
}

// Check for conflicts
foreach ($role->permissions as $permission) {
    foreach ($role->permissions as $other) {
        if ($permission->conflictsWith($other)) {
            // Handle conflict
        }
    }
}
```

### 2. Access Control

```php
// Middleware ensures clinic access
Route::middleware(['auth', 'clinic.access'])->group(function () {
    // Clinic-specific routes
});

// Controller methods check permissions
public function store(Request $request)
{
    if (!$request->user()->hasPermissionInClinic('patients.create', $clinicId)) {
        abort(403, 'Unauthorized');
    }
    // Create patient logic
}
```

### 3. Data Isolation

```php
// All queries are clinic-scoped
$patients = Patient::where('clinic_id', $user->current_clinic_id)->get();

// Use relationships for automatic scoping
$clinic->patients; // Automatically scoped to clinic
```

## Usage Examples

### 1. Creating a New Role

```php
$role = Role::create([
    'name' => 'nurse',
    'description' => 'Nursing staff with limited patient management access',
    'is_system_role' => false,
    'permissions_config' => [
        'patients.view',
        'patients.edit',
        'appointments.view',
        'appointments.create',
        'medical_records.view',
        'medical_records.edit'
    ]
]);

// Assign permissions
$permissionIds = Permission::whereIn('slug', $role->permissions_config)->pluck('id');
$role->permissions()->sync($permissionIds);
```

### 2. Assigning User to Clinic with Role

```php
UserClinicRole::create([
    'user_id' => $user->id,
    'clinic_id' => $clinic->id,
    'role_id' => $role->id
]);
```

### 3. Checking User Permissions

```php
// Check role
if ($user->hasRoleInClinic('admin', $clinicId)) {
    // User is admin in this clinic
}

// Check permission
if ($user->hasPermissionInClinic('patients.create', $clinicId)) {
    // User can create patients in this clinic
}

// Check multiple permissions
if ($user->hasAllPermissions(['patients.view', 'patients.edit'], $clinicId)) {
    // User has both view and edit permissions
}
```

## Testing

### 1. Database Seeding

```bash
# Run permission seeder
php artisan db:seed --class=PermissionSeeder

# Run user role seeder
php artisan db:seed --class=UserRoleSeeder
```

### 2. Testing Role Functionality

```php
// Test role permissions
$role = Role::where('name', 'doctor')->first();
$this->assertTrue($role->hasPermission('patients.view'));
$this->assertFalse($role->hasPermission('patients.delete'));

// Test user clinic roles
$user = User::factory()->create();
$clinic = Clinic::factory()->create();
$role = Role::where('name', 'receptionist')->first();

UserClinicRole::create([
    'user_id' => $user->id,
    'clinic_id' => $clinic->id,
    'role_id' => $role->id
]);

$this->assertTrue($user->hasRoleInClinic('receptionist', $clinic->id));
```

## Performance Considerations

### 1. Permission Caching

```php
// Cache user permissions
$permissions = Cache::remember("user_permissions_{$userId}_{$clinicId}", 3600, function () use ($userId, $clinicId) {
    return User::find($userId)->getPermissionsInClinic($clinicId);
});
```

### 2. Query Optimization

```php
// Use eager loading for permissions
$roles = Role::with(['permissions', 'userClinicRoles'])->get();

// Use scopes for filtering
$adminRoles = Role::byName('admin')->get();
```

## Troubleshooting

### Common Issues

1. **Permission Denied**
   - Check if user has proper role in clinic
   - Verify permission is assigned to role
   - Check clinic_id is set in session/request

2. **Role Not Working**
   - Ensure role has minimum required permissions
   - Check for permission conflicts
   - Verify role is properly assigned to user

3. **System Role Errors**
   - System roles cannot be modified or deleted
   - Check `is_system_role` flag
   - Use `canBeModified()` and `canBeDeleted()` methods

### Debug Commands

```bash
# Check user permissions
php artisan tinker
$user = User::find(1);
$user->permissions; // Shows all permissions
$user->hasPermissionInClinic('patients.create', 1); // Check specific permission

# Check role configuration
$role = Role::where('name', 'admin')->first();
$role->permissions; // Shows role permissions
$role->validatePermissions(); // Check for issues
```

## Future Enhancements

### Planned Features

1. **Advanced Permission Groups**: Group permissions for easier management
2. **Temporary Permissions**: Time-limited permission grants
3. **Permission Auditing**: Track permission changes and usage
4. **Role Templates**: Predefined role configurations
5. **Dynamic Permissions**: Context-aware permission checking

### Scalability Considerations

1. **Permission Caching**: Redis-based permission caching
2. **Role Hierarchy**: Support for role inheritance
3. **Permission Delegation**: Temporary permission delegation
4. **Multi-tenant Support**: Enhanced clinic isolation

## Conclusion

The user roles and permissions system provides a robust, secure, and flexible access control mechanism for the MediNext healthcare system. With predefined roles for different user types and granular permission management, the system ensures proper data access while maintaining security and compliance.

The system is designed to be:
- **Secure**: Role-based access control with permission validation
- **Flexible**: Configurable permissions and custom roles
- **Scalable**: Efficient permission checking and caching
- **Maintainable**: Clear separation of concerns and validation

For additional support or questions about the roles and permissions system, please refer to the development team or create an issue in the project repository.
