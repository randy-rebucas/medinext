# Multi-Clinic & Multi-Doctor Support - Implementation Guide

## Overview

This document provides a comprehensive guide to the multi-clinic and multi-doctor support system implemented in MediNext. The system allows healthcare organizations to manage multiple clinics, each with multiple doctors, while maintaining proper data isolation and role-based access control.

## System Architecture

### Core Components

1. **Clinic Management** - Centralized clinic administration
2. **Doctor Management** - Doctor profiles and clinic assignments
3. **User Role Management** - Clinic-specific user roles and permissions
4. **Data Isolation** - Automatic clinic-scoping for all clinical data
5. **Access Control** - Role-based permissions per clinic

### Database Schema

```
clinics (id, name, slug, timezone, logo_url, phone, email, website, description, address, settings)
├── doctors (id, user_id, clinic_id, specialty, license_no, is_active, consultation_fee, availability_schedule)
├── patients (id, clinic_id, ...)
├── appointments (id, clinic_id, doctor_id, patient_id, ...)
├── encounters (id, clinic_id, doctor_id, patient_id, ...)
└── user_clinic_roles (id, user_id, clinic_id, role_id)
```

## Implementation Details

### 1. Clinic Model (`app/Models/Clinic.php`)

#### Key Features
- **Multi-clinic Support**: Each clinic operates independently
- **Timezone Management**: Clinic-specific timezone settings
- **Address Management**: Structured address storage (JSON)
- **Settings Management**: Flexible clinic-specific configuration
- **Statistics**: Real-time clinic metrics

#### Enhanced Methods
```php
// Get clinic statistics
$clinic->statistics; // Returns array with counts

// Check clinic availability
$clinic->isOpen('monday', '14:00'); // Returns boolean

// Get working hours
$clinic->working_hours; // Returns structured schedule

// Get formatted address
$clinic->formatted_address; // Returns readable address string
```

#### Scopes
```php
// Filter active clinics
Clinic::active()->get();

// Filter by timezone
Clinic::byTimezone('Asia/Manila')->get();
```

### 2. Doctor Model (`app/Models/Doctor.php`)

#### Key Features
- **Clinic Assignment**: Doctors are assigned to specific clinics
- **Specialty Management**: Track medical specialties
- **Availability Scheduling**: JSON-based availability management
- **Consultation Fees**: Clinic-specific pricing
- **Active Status**: Track doctor availability

#### Enhanced Methods
```php
// Check doctor availability
$doctor->isAvailable('2024-01-15', '14:00'); // Returns boolean

// Get availability for specific date
$doctor->getAvailabilityForDate('2024-01-15'); // Returns time slots

// Get doctor statistics
$doctor->statistics; // Returns appointment/patient counts

// Get recent patients
$doctor->recent_patients; // Returns recent patient list
```

#### Scopes
```php
// Filter active doctors
Doctor::active()->get();

// Filter by specialty
Doctor::bySpecialty('Cardiology')->get();

// Filter by clinic
Doctor::byClinic($clinicId)->get();
```

### 3. User-Clinic-Role Management

#### Key Features
- **Multi-clinic Users**: Users can belong to multiple clinics
- **Role-based Access**: Different roles per clinic
- **Permission Management**: Granular permissions per clinic
- **Access Control**: Middleware-based security

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

### 1. Clinic Resource (`app/Nova/Clinic.php`)

#### Enhanced Fields
- **Basic Information**: Name, slug, timezone
- **Contact Details**: Phone, email, website
- **Media**: Logo upload with proper storage
- **Address**: JSON-based structured address
- **Settings**: Flexible configuration storage
- **Relationships**: Doctors, patients, appointments

#### Features
- **Timezone Selection**: Predefined timezone options
- **Image Management**: Logo upload and storage
- **JSON Fields**: Structured data for address and settings
- **Relationship Display**: Show related entities

### 2. Doctor Resource (`app/Nova/Doctor.php`)

#### Enhanced Fields
- **User Assignment**: Link to user account
- **Clinic Assignment**: Select clinic
- **Medical Information**: Specialty, license, fees
- **Availability**: JSON-based schedule management
- **Status Management**: Active/inactive status
- **Media**: Digital signature upload

#### Features
- **Specialty Selection**: Predefined medical specialties
- **Clinic Filtering**: Show only relevant clinics
- **Availability Management**: JSON editor for schedules
- **Image Upload**: Signature management

## Data Isolation & Security

### 1. Automatic Clinic Scoping

All clinical data is automatically scoped to the user's authorized clinics:

```php
// In models, use clinic_id for relationships
public function patients(): HasMany
{
    return $this->hasMany(Patient::class);
}

// In queries, filter by clinic
$patients = Patient::where('clinic_id', $user->current_clinic_id)->get();
```

### 2. Role-based Access Control

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

### 3. Data Validation

```php
// Ensure clinic_id is set
$request->validate([
    'clinic_id' => 'required|exists:clinics,id',
    'doctor_id' => 'required|exists:doctors,id,clinic_id,' . $request->clinic_id,
]);
```

## API Endpoints

### Clinic Management
```
GET    /api/clinics                    # List user's clinics
POST   /api/clinics                    # Create new clinic
GET    /api/clinics/{id}              # Get clinic details
PUT    /api/clinics/{id}              # Update clinic
DELETE /api/clinics/{id}              # Delete clinic
GET    /api/clinics/{id}/statistics   # Get clinic statistics
```

### Doctor Management
```
GET    /api/clinics/{id}/doctors      # List clinic doctors
POST   /api/clinics/{id}/doctors      # Add doctor to clinic
GET    /api/doctors/{id}              # Get doctor details
PUT    /api/doctors/{id}              # Update doctor
DELETE /api/doctors/{id}              # Remove doctor
GET    /api/doctors/{id}/availability # Get doctor availability
```

### User Management
```
GET    /api/clinics/{id}/users        # List clinic users
POST   /api/clinics/{id}/users        # Add user to clinic
PUT    /api/clinics/{id}/users/{user} # Update user role
DELETE /api/clinics/{id}/users/{user} # Remove user from clinic
```

## Usage Examples

### 1. Creating a New Clinic

```php
$clinic = Clinic::create([
    'name' => 'Metro Medical Center',
    'slug' => 'metro-medical',
    'timezone' => 'Asia/Manila',
    'phone' => '+63 2 1234 5678',
    'email' => 'info@metromedical.com',
    'website' => 'https://metromedical.com',
    'description' => 'Leading medical center in Metro Manila',
    'address' => [
        'street' => '123 Medical Plaza',
        'city' => 'Manila',
        'state' => 'Metro Manila',
        'country' => 'Philippines'
    ],
    'settings' => [
        'working_hours' => [
            'monday' => ['09:00', '17:00'],
            'tuesday' => ['09:00', '17:00'],
            'wednesday' => ['09:00', '17:00'],
            'thursday' => ['09:00', '17:00'],
            'friday' => ['09:00', '17:00'],
            'saturday' => ['09:00', '12:00'],
            'sunday' => 'closed'
        ]
    ]
]);
```

### 2. Adding a Doctor to a Clinic

```php
$doctor = Doctor::create([
    'user_id' => $user->id,
    'clinic_id' => $clinic->id,
    'specialty' => 'Cardiology',
    'license_no' => 'MD-12345',
    'is_active' => true,
    'consultation_fee' => 1500.00,
    'availability_schedule' => [
        'monday' => ['09:00', '10:00', '11:00', '14:00', '15:00', '16:00'],
        'tuesday' => ['09:00', '10:00', '11:00', '14:00', '15:00', '16:00'],
        'wednesday' => ['09:00', '10:00', '11:00', '14:00', '15:00', '16:00'],
        'thursday' => ['09:00', '10:00', '11:00', '14:00', '15:00', '16:00'],
        'friday' => ['09:00', '10:00', '11:00', '14:00', '15:00', '16:00'],
        'saturday' => ['09:00', '10:00', '11:00'],
        'sunday' => 'closed'
    ]
]);
```

### 3. Managing User Roles

```php
// Add user to clinic with role
UserClinicRole::create([
    'user_id' => $user->id,
    'clinic_id' => $clinic->id,
    'role_id' => $role->id
]);

// Check user permissions
if ($user->hasPermissionInClinic('appointments.create', $clinicId)) {
    // User can create appointments in this clinic
}
```

## Testing

### 1. Database Seeding

```bash
# Run clinic seeder
php artisan db:seed --class=ClinicSeeder

# Run doctor seeder
php artisan db:seed --class=DoctorSeeder

# Run user role seeder
php artisan db:seed --class=UserRoleSeeder
```

### 2. Testing Multi-clinic Functionality

```php
// Test clinic isolation
$clinic1 = Clinic::factory()->create();
$clinic2 = Clinic::factory()->create();

$doctor1 = Doctor::factory()->create(['clinic_id' => $clinic1->id]);
$doctor2 = Doctor::factory()->create(['clinic_id' => $clinic2->id]);

// Verify doctors are in different clinics
$this->assertNotEquals($doctor1->clinic_id, $doctor2->clinic_id);
```

## Performance Considerations

### 1. Database Indexing

```sql
-- Add indexes for common queries
CREATE INDEX idx_clinics_timezone ON clinics(timezone);
CREATE INDEX idx_doctors_clinic_active ON doctors(clinic_id, is_active);
CREATE INDEX idx_user_clinic_roles_user_clinic ON user_clinic_roles(user_id, clinic_id);
```

### 2. Query Optimization

```php
// Use eager loading to avoid N+1 queries
$clinic = Clinic::with(['doctors.user', 'patients', 'appointments'])->find($id);

// Use scopes for common filters
$activeDoctors = Doctor::active()->byClinic($clinicId)->get();
```

### 3. Caching

```php
// Cache clinic data
$clinic = Cache::remember("clinic_{$id}", 3600, function () use ($id) {
    return Clinic::with(['doctors', 'statistics'])->find($id);
});
```

## Troubleshooting

### Common Issues

1. **User Access Denied**
   - Check if user has proper role in clinic
   - Verify clinic_id is set in session/request
   - Check middleware configuration

2. **Data Not Showing**
   - Verify clinic_id is properly set in queries
   - Check if user has access to the clinic
   - Verify relationships are properly defined

3. **Foreign Key Errors**
   - Ensure referenced records exist before deletion
   - Check cascade delete configuration
   - Verify clinic_id constraints

### Debug Commands

```bash
# Check user's clinic access
php artisan tinker
$user = User::find(1);
$user->clinics; // Shows all clinics user belongs to
$user->hasRoleInClinic('admin', 1); // Check specific role

# Check clinic data
$clinic = Clinic::find(1);
$clinic->doctors; // Shows all doctors in clinic
$clinic->statistics; // Shows clinic statistics
```

## Future Enhancements

### Planned Features

1. **Clinic Branches**: Support for multiple locations per clinic
2. **Advanced Scheduling**: Doctor availability management with conflicts
3. **Patient Portal**: Patient access to their clinic data
4. **Reporting**: Clinic-specific analytics and reports
5. **Integration**: API endpoints for external system integration
6. **Multi-language**: Support for multiple languages per clinic
7. **Custom Fields**: Clinic-specific custom data fields

### Scalability Considerations

1. **Database Partitioning**: Partition large tables by clinic_id
2. **Microservices**: Separate clinic management into microservice
3. **Load Balancing**: Distribute load across multiple servers
4. **CDN Integration**: Optimize media delivery for logos and signatures

## Support & Maintenance

### Regular Tasks

1. **Database Maintenance**: Regular index optimization
2. **Backup Verification**: Ensure clinic data is properly backed up
3. **Performance Monitoring**: Monitor query performance per clinic
4. **Security Audits**: Regular permission and access reviews

### Monitoring

1. **Error Logs**: Monitor for clinic-specific errors
2. **Performance Metrics**: Track response times per clinic
3. **User Activity**: Monitor user access patterns
4. **Data Growth**: Track data growth per clinic

## Conclusion

The multi-clinic and multi-doctor support system provides a robust foundation for healthcare organizations to manage multiple facilities while maintaining proper data isolation and security. The system is designed to be scalable, maintainable, and user-friendly.

For additional support or questions, please refer to the development team or create an issue in the project repository.
