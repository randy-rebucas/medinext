# Role-Based Access Control (RBAC) System

This document describes the comprehensive role and permission system implemented in MyClinicSoft API, providing granular access control for multi-clinic operations.

## Overview

The RBAC system implements a flexible, configurable permission model that ensures users can only access features and data they're authorized to use within their assigned clinics.

## Core Components

### 1. Roles
Each role represents a set of responsibilities and permissions within the system:

- **Superadmin**: Full system access and management
- **Admin**: Full clinic access and management
- **Doctor**: Medical professional operations
- **Receptionist**: Front desk operations
- **Patient**: Patient-specific operations
- **Medical Representative**: Product and meeting management

### 2. Permissions
Granular permissions are organized by module and action:

#### Module Structure
- `clinics.*` - Clinic management operations
- `doctors.*` - Doctor management operations
- `patients.*` - Patient management operations
- `appointments.*` - Appointment operations
- `prescriptions.*` - Prescription management
- `medical_records.*` - Medical record operations
- `users.*` - User management
- `roles.*` - Role and permission management
- `billing.*` - Billing operations
- `reports.*` - Reporting and analytics
- `settings.*` - System settings
- `products.*` - Product management (for medreps)
- `meetings.*` - Meeting management (for medreps)
- `interactions.*` - Interaction tracking (for medreps)

#### Action Types
- `view` - Read-only access
- `create` - Create new records
- `edit` - Modify existing records
- `delete` - Remove records
- `manage` - Full control over the module
- `cancel` - Cancel appointments
- `checkin` - Patient check-in
- `download` - Download documents
- `export` - Export data

## Database Schema

### Tables
1. **`roles`** - Role definitions
   - `name` - Role identifier
   - `description` - Human-readable description
   - `is_system_role` - Whether it's a protected system role
   - `permissions_config` - JSON configuration

2. **`permissions`** - Individual permissions
   - `name` - Human-readable permission name
   - `slug` - Unique identifier
   - `description` - Permission description
   - `module` - Module this permission belongs to
   - `action` - Action this permission grants

3. **`role_permissions`** - Role-permission relationships
   - `role_id` - Reference to role
   - `permission_id` - Reference to permission

4. **`user_clinic_roles`** - User assignments to clinics with roles
   - `user_id` - Reference to user
   - `clinic_id` - Reference to clinic
   - `role_id` - Reference to role

## Default Role Permissions

### Superadmin
- **Full Access**: All permissions across all clinics
- **System Management**: Can manage system settings and global configurations
- **Role Management**: Can create, edit, and delete roles

### Admin
- **Clinic Management**: Full control over assigned clinics
- **Staff Management**: Can manage doctors, receptionists, and other staff
- **Patient Management**: Full patient record access
- **Billing**: Complete billing operations
- **Reports**: Access to all clinic reports

### Doctor
- **Patient Care**: View and edit patient records
- **Appointments**: Manage their own appointments
- **Prescriptions**: Create and manage prescriptions
- **Medical Records**: Access to patient medical history
- **Schedule**: View and manage their schedule

### Receptionist
- **Patient Management**: Create and edit patient records
- **Appointment Scheduling**: Book and modify appointments
- **Check-ins**: Handle patient check-ins
- **Billing Support**: Basic billing operations
- **Schedule View**: Access to appointment schedules

### Patient
- **Self-Service**: Book and cancel appointments
- **Record Access**: View their own medical records
- **Prescriptions**: Download prescriptions
- **Profile Management**: Edit personal information

### Medical Representative
- **Product Management**: Manage product information
- **Meeting Scheduling**: Schedule meetings with doctors
- **Interaction Tracking**: Record and track interactions
- **Schedule Access**: View doctor schedules

## Implementation Details

### Permission Checking

#### In Controllers
```php
// Check single permission
if (!$user->hasPermissionInClinic('patients.create', $clinicId)) {
    abort(403, 'Insufficient permissions.');
}

// Check multiple permissions (any)
if (!$user->hasAnyPermissionInClinic(['patients.edit', 'patients.manage'], $clinicId)) {
    abort(403, 'Insufficient permissions.');
}

// Check multiple permissions (all)
if (!$user->hasAllPermissionsInClinic(['patients.view', 'patients.edit'], $clinicId)) {
    abort(403, 'Insufficient permissions.');
}
```

#### In Middleware
```php
// Route-level permission checking
Route::middleware('permission:patients.create')->group(function () {
    Route::post('/patients', [PatientController::class, 'store']);
});
```

#### In Blade Views
```php
@if($user->hasPermissionInClinic('patients.create', $clinic->id))
    <a href="{{ route('patients.create') }}" class="btn">Add Patient</a>
@endif
```

### Role Management

#### Creating Custom Roles
```php
$role = Role::create([
    'name' => 'senior_doctor',
    'description' => 'Senior doctor with additional privileges',
    'is_system_role' => false,
]);

// Assign permissions
$role->permissions()->attach([
    Permission::where('slug', 'patients.manage')->first()->id,
    Permission::where('slug', 'reports.view')->first()->id,
]);
```

#### Modifying Role Permissions
```php
$role = Role::find(1);
$permissionIds = Permission::whereIn('slug', [
    'patients.view',
    'patients.edit',
    'appointments.view'
])->pluck('id');

$role->permissions()->sync($permissionIds);
```

## Security Features

### 1. Clinic Isolation
- Users can only access clinics they're assigned to
- All data queries are automatically scoped to user's clinics
- Cross-clinic access is prevented by middleware

### 2. Permission Validation
- Server-side permission checking in all controllers
- Route-level permission middleware
- Client-side permission checking in views

### 3. Role Protection
- System roles cannot be modified or deleted
- Custom roles can be fully managed
- Role deletion prevents orphaned permissions

### 4. Audit Trail
- All permission checks are logged
- Role and permission changes are tracked
- User access patterns are monitored

## Usage Examples

### Adding a New Permission
```php
Permission::create([
    'name' => 'Manage Inventory',
    'slug' => 'inventory.manage',
    'description' => 'Full control over clinic inventory',
    'module' => 'inventory',
    'action' => 'manage',
]);
```

### Checking User Capabilities
```php
$user = auth()->user();
$clinicId = 1;

// Check if user can manage patients
if ($user->hasPermissionInClinic('patients.manage', $clinicId)) {
    // User can perform all patient operations
}

// Check if user is a doctor
if ($user->isDoctorInClinic($clinicId)) {
    // User has doctor role in this clinic
}

// Get all user permissions
$permissions = $user->getPermissionsInClinic($clinicId);
```

### Role Assignment
```php
// Assign user to clinic with role
UserClinicRole::create([
    'user_id' => $userId,
    'clinic_id' => $clinicId,
    'role_id' => $roleId,
]);
```

## Best Practices

### 1. Permission Design
- Use descriptive permission names
- Group related permissions by module
- Keep permissions granular for flexibility
- Use consistent naming conventions

### 2. Security Implementation
- Always check permissions server-side
- Use middleware for route protection
- Validate clinic access in all operations
- Log permission checks for audit

### 3. Performance Considerations
- Cache user permissions when possible
- Use eager loading for role relationships
- Optimize permission queries
- Consider permission inheritance

## Troubleshooting

### Common Issues

1. **Permission Denied Errors**
   - Check if user has the required role in the clinic
   - Verify permission is assigned to the role
   - Ensure clinic context is properly set

2. **Role Not Working**
   - Check role-permission relationships
   - Verify user-clinic-role assignment
   - Ensure role is not disabled

3. **Cross-Clinic Access**
   - Verify clinic isolation middleware
   - Check user's clinic assignments
   - Ensure proper clinic context in requests

### Debug Commands
```bash
# Check user's clinic access
php artisan tinker
$user = User::find(1);
$user->clinics; // Shows all clinics user belongs to
$user->getPermissionsInClinic(1); // Shows permissions in clinic 1

# Check role permissions
$role = Role::find(1);
$role->permissions; // Shows all permissions for role
```

## Future Enhancements

### Planned Features
- **Permission Inheritance**: Hierarchical permission structure
- **Dynamic Roles**: Role creation through admin interface
- **Permission Groups**: Bulk permission management
- **Advanced Auditing**: Detailed access logs
- **API Permissions**: REST API access control
- **Mobile Permissions**: Mobile app permission management

### Scalability Considerations
- **Permission Caching**: Redis-based permission caching
- **Role Templates**: Predefined role configurations
- **Bulk Operations**: Efficient bulk permission updates
- **Performance Monitoring**: Permission check performance metrics

## Support

For questions or issues related to the RBAC system:
1. Check user roles and clinic assignments
2. Verify permission configurations
3. Review middleware implementation
4. Check the audit logs for access patterns
5. Consult this documentation for implementation details

The RBAC system provides a robust foundation for secure, scalable multi-clinic operations while maintaining flexibility for future enhancements.
