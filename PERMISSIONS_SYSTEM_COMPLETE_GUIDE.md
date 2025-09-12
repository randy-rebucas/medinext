# Complete Permissions System Guide

## Overview

The MediNext application implements a comprehensive role-based access control (RBAC) system with granular permissions for all use cases. This system ensures proper security and access control across all modules and features.

## System Architecture

### Core Components

1. **Permissions** - Granular access control units
2. **Roles** - Collections of permissions for specific user types
3. **User-Clinic-Role** - Multi-clinic role assignments
4. **Middleware** - Permission validation in requests
5. **Controllers** - Permission checks in business logic

### Database Schema

```
permissions (id, name, slug, description, module, action)
├── role_permissions (role_id, permission_id)
roles (id, name, description, is_system_role, permissions_config)
├── user_clinic_roles (user_id, clinic_id, role_id)
users (id, name, email, ...)
```

## Permission Structure

### Format: `{module}.{action}`

#### Modules (25 total)
- **system** - System administration
- **clinics** - Clinic management
- **users** - User account management
- **roles** - Role management
- **permissions** - Permission management
- **doctors** - Doctor management
- **patients** - Patient management
- **appointments** - Appointment scheduling
- **encounters** - Medical encounters
- **prescriptions** - Prescription management
- **medical_records** - Medical records
- **queue** - Patient queue management
- **lab_results** - Laboratory results
- **file_assets** - File management
- **rooms** - Room management
- **insurance** - Insurance management
- **notifications** - Notification system
- **activity_logs** - Activity logging
- **schedule** - Scheduling system
- **billing** - Billing and payments
- **reports** - Reporting system
- **settings** - System settings
- **profile** - User profiles
- **products** - Product management (medrep)
- **meetings** - Meeting management (medrep)
- **interactions** - Interaction tracking (medrep)
- **medrep_visits** - Medrep visit management
- **dashboard** - Dashboard access
- **search** - Search functionality

#### Actions (8 total)
- **view** - Read-only access (Low risk)
- **create** - Create new records (Medium risk)
- **edit** - Modify existing records (Medium risk)
- **delete** - Remove records (High risk)
- **manage** - Full control (High risk)
- **export** - Export data (Medium risk)
- **download** - Download files (Low risk)
- **upload** - Upload files (Medium risk)
- **activate/deactivate** - User status management (High risk)
- **complete** - Complete processes (Medium risk)
- **process** - Process items (Medium risk)
- **add/remove** - Add/remove items (Medium risk)
- **checkin** - Check-in operations (Low risk)
- **cancel** - Cancel operations (Low risk)
- **generate** - Generate reports (Medium risk)
- **admin** - Administrative access (Critical risk)
- **info** - View information (Low risk)
- **licenses** - License management (Critical risk)
- **global** - Global access (High risk)
- **stats** - View statistics (Low risk)

## Role Definitions

### 1. Superadmin (System Role)
**Description**: Full system access and management
**Security Level**: Critical
**Permissions**: All permissions (100+ permissions)

**Key Capabilities**:
- System administration and configuration
- Multi-clinic management
- User and role management
- License management
- Full access to all modules

### 2. Admin (Clinic Role)
**Description**: Full clinic access and management
**Security Level**: High
**Permissions**: 80+ permissions

**Key Capabilities**:
- Clinic operations management
- Staff management (within clinic)
- Patient and doctor management
- Clinical operations oversight
- Billing and financial management
- Reporting and analytics

### 3. Doctor (Clinical Role)
**Description**: Medical professional with clinical access
**Security Level**: Medium-High
**Permissions**: 30+ permissions

**Key Capabilities**:
- Patient care and treatment
- Medical record management
- Prescription management
- Appointment management
- Clinical documentation
- Personal schedule management

### 4. Receptionist (Front Desk Role)
**Description**: Front desk staff with scheduling access
**Security Level**: Medium
**Permissions**: 25+ permissions

**Key Capabilities**:
- Patient registration and check-in
- Appointment scheduling
- Queue management
- Basic billing operations
- Patient information management
- Schedule coordination

### 5. Patient (Self-Service Role)
**Description**: Patient with self-service access
**Security Level**: Low
**Permissions**: 15+ permissions

**Key Capabilities**:
- View own medical records
- Book and manage appointments
- Download prescriptions and reports
- View clinic and doctor information
- Manage personal profile

### 6. Medrep (Medical Representative Role)
**Description**: Medical representative with product management access
**Security Level**: Medium
**Permissions**: 20+ permissions

**Key Capabilities**:
- Product catalog management
- Doctor meeting scheduling
- Interaction tracking
- Visit management
- Reporting and analytics

## Use Case Implementation

### Receptionist Use Cases
- **Search Patient**: `patients.view`, `search.patients`
- **Add New Patient**: `patients.create`
- **Add New Encounter**: `encounters.create`
- **Add to Queue**: `queue.add`, `queue.view`

### Doctor Use Cases
- **Work on Queue**: `queue.view`, `queue.process`
- **Clinical Documentation**: `encounters.edit`, `medical_records.create`, `prescriptions.create`
- **Complete Encounter**: `encounters.complete`, `prescriptions.download`

### Patient Use Cases
- **Book Appointment**: `appointments.create`, `appointments.view`
- **View Records**: `medical_records.view`, `encounters.view`
- **Download Prescriptions**: `prescriptions.download`, `file_assets.download`

### Medical Representative Use Cases
- **Manage Products**: `products.manage`, `products.create`, `products.edit`
- **Schedule Meetings**: `meetings.create`, `meetings.manage`
- **Track Interactions**: `interactions.create`, `interactions.manage`

## Permission Validation

### Middleware Implementation

```php
// API Permission Middleware
Route::middleware(['api.permission:patients.create'])->group(function () {
    Route::post('/patients', [PatientController::class, 'store']);
});

// Clinic Access Middleware
Route::middleware(['api.clinic'])->group(function () {
    // Clinic-specific routes
});
```

### Controller Implementation

```php
public function store(Request $request)
{
    // Check permission
    if (!$request->user()->hasPermission('patients.create')) {
        abort(403, 'Insufficient permissions');
    }
    
    // Check clinic access
    if (!$request->user()->hasPermissionInClinic('patients.create', $clinicId)) {
        abort(403, 'No access to this clinic');
    }
    
    // Business logic
}
```

### User Model Methods

```php
// Basic permission checking
$user->hasPermission('patients.create');
$user->hasPermissionInClinic('patients.create', $clinicId);

// Multiple permission checking
$user->hasAnyPermission(['patients.create', 'patients.edit']);
$user->hasAllPermissions(['patients.view', 'patients.edit']);

// Role checking
$user->hasRole('doctor');
$user->hasRoleInClinic('admin', $clinicId);

// Specific role checking
$user->isDoctorInClinic($clinicId);
$user->isAdminInClinic($clinicId);

// Access validation
$user->hasValidAccess(); // Trial or license
```

## Security Features

### 1. Multi-Clinic Isolation
- Users can have different roles in different clinics
- Permissions are scoped to specific clinics
- Data access is automatically filtered by clinic

### 2. Permission Dependencies
- Some permissions require others (e.g., `delete` requires `view`)
- Automatic validation of permission conflicts
- Minimum permission requirements for roles

### 3. Risk-Based Classification
- **Critical**: System administration, license management
- **High**: User management, data deletion
- **Medium**: Data modification, reporting
- **Low**: View operations, downloads

### 4. System Role Protection
- System roles cannot be modified or deleted
- Critical permissions are protected
- Audit trail for permission changes

## Implementation Commands

### Seed Permissions and Roles
```bash
php artisan db:seed --class=PermissionSeeder
php artisan db:seed --class=UserRoleSeeder
```

### Validate System
```bash
php artisan permissions:validate
php artisan permissions:validate --fix
```

### Check User Permissions
```bash
php artisan tinker
$user = User::find(1);
$user->getAllPermissions();
$user->hasPermission('patients.create');
```

## API Endpoints

### Permission Management
- `GET /api/v1/permissions` - List permissions
- `POST /api/v1/permissions` - Create permission
- `GET /api/v1/permissions/{id}` - Get permission
- `PUT /api/v1/permissions/{id}` - Update permission
- `DELETE /api/v1/permissions/{id}` - Delete permission

### Role Management
- `GET /api/v1/roles` - List roles
- `POST /api/v1/roles` - Create role
- `GET /api/v1/roles/{id}` - Get role
- `PUT /api/v1/roles/{id}` - Update role
- `DELETE /api/v1/roles/{id}` - Delete role

### User Permission Management
- `GET /api/v1/users/{id}/permissions` - Get user permissions
- `POST /api/v1/users/{id}/permissions` - Assign permissions
- `GET /api/v1/users/{id}/roles` - Get user roles
- `POST /api/v1/users/{id}/roles` - Assign roles

## Testing

### Unit Tests
```php
// Test role permissions
$role = Role::where('name', 'doctor')->first();
$this->assertTrue($role->hasPermission('patients.view'));
$this->assertFalse($role->hasPermission('patients.delete'));

// Test user permissions
$user = User::factory()->create();
$clinic = Clinic::factory()->create();
$role = Role::where('name', 'receptionist')->first();

UserClinicRole::create([
    'user_id' => $user->id,
    'clinic_id' => $clinic->id,
    'role_id' => $role->id,
]);

$this->assertTrue($user->hasRoleInClinic('receptionist', $clinic->id));
$this->assertTrue($user->hasPermissionInClinic('patients.create', $clinic->id));
```

### Integration Tests
```php
// Test API endpoints with permissions
$user = User::factory()->create();
$user->assignRole('doctor');

$response = $this->actingAs($user)
    ->postJson('/api/v1/patients', $patientData);

$response->assertStatus(403); // Should fail - doctors can't create patients
```

## Performance Considerations

### Caching
```php
// Cache user permissions
$permissions = Cache::remember("user_permissions_{$userId}_{$clinicId}", 3600, function () use ($userId, $clinicId) {
    return User::find($userId)->getPermissionsInClinic($clinicId);
});
```

### Query Optimization
```php
// Use eager loading
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
1. **Advanced Permission Groups** - Group permissions for easier management
2. **Temporary Permissions** - Time-limited permission grants
3. **Permission Auditing** - Track permission changes and usage
4. **Role Templates** - Predefined role configurations
5. **Dynamic Permissions** - Context-aware permission checking

### Scalability Considerations
1. **Permission Caching** - Redis-based permission caching
2. **Role Hierarchy** - Support for role inheritance
3. **Permission Delegation** - Temporary permission delegation
4. **Multi-tenant Support** - Enhanced clinic isolation

## Conclusion

The MediNext permissions system provides a robust, secure, and flexible access control mechanism that:

- **Ensures Security**: Role-based access control with granular permissions
- **Supports Multi-Clinic**: Clinic-specific role assignments and data isolation
- **Enables Use Cases**: Complete coverage of all application use cases
- **Maintains Flexibility**: Configurable permissions and custom roles
- **Provides Scalability**: Efficient permission checking and caching
- **Offers Maintainability**: Clear separation of concerns and validation

The system is production-ready and follows industry best practices for healthcare application security and compliance.
