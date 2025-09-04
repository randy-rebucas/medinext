# Multi-Clinic & Multi-Doctor Support - Quick Reference

## Quick Start

### 1. Basic Usage

```php
// Get user's clinics
$user = auth()->user();
$clinics = $user->clinics;

// Check user role in clinic
if ($user->hasRoleInClinic('admin', $clinicId)) {
    // User is admin in this clinic
}

// Get clinic doctors
$clinic = Clinic::find($clinicId);
$doctors = $clinic->activeDoctors;

// Check doctor availability
$doctor = Doctor::find($doctorId);
$isAvailable = $doctor->isAvailable('2024-01-15', '14:00');
```

### 2. Common Scopes

```php
// Filter clinics
Clinic::active()->get();
Clinic::byTimezone('Asia/Manila')->get();

// Filter doctors
Doctor::active()->get();
Doctor::bySpecialty('Cardiology')->get();
Doctor::byClinic($clinicId)->get();
```

### 3. Data Relationships

```php
// Clinic -> Doctors
$clinic->doctors; // All doctors
$clinic->activeDoctors; // Only active doctors

// Doctor -> Clinic
$doctor->clinic; // Doctor's clinic
$doctor->appointments; // Doctor's appointments

// User -> Clinics
$user->clinics; // All user's clinics
$user->userClinicRoles; // User's roles in clinics
```

## Key Models

### Clinic Model
- **Fields**: name, slug, timezone, phone, email, website, description, address, settings
- **Key Methods**: `statistics`, `isOpen()`, `working_hours`, `formatted_address`
- **Scopes**: `active()`, `byTimezone()`

### Doctor Model
- **Fields**: user_id, clinic_id, specialty, license_no, is_active, consultation_fee, availability_schedule
- **Key Methods**: `isAvailable()`, `getAvailabilityForDate()`, `statistics`, `recent_patients`
- **Scopes**: `active()`, `bySpecialty()`, `byClinic()`

### User Model
- **Key Methods**: `hasRoleInClinic()`, `hasPermissionInClinic()`, `isDoctorInClinic()`, `getCurrentClinic()`

## Database Schema

### Core Tables
```sql
clinics (id, name, slug, timezone, logo_url, phone, email, website, description, address, settings)
doctors (id, user_id, clinic_id, specialty, license_no, is_active, consultation_fee, availability_schedule)
user_clinic_roles (id, user_id, clinic_id, role_id)
```

### Relationships
- `clinics` 1:N `doctors`
- `clinics` 1:N `patients`
- `clinics` 1:N `appointments`
- `users` N:N `clinics` (via `user_clinic_roles`)

## Security & Access Control

### Permission Checking
```php
// Check role
$user->hasRoleInClinic('admin', $clinicId);

// Check permission
$user->hasPermissionInClinic('patients.create', $clinicId);

// Check if doctor
$user->isDoctorInClinic($clinicId);
```

### Data Scoping
```php
// Always scope by clinic_id
$patients = Patient::where('clinic_id', $clinicId)->get();

// Use relationships for automatic scoping
$clinic->patients; // Automatically scoped to clinic
```

## Nova Admin Interface

### Clinic Resource
- **Fields**: Basic info, contact details, media, address, settings
- **Features**: Timezone selection, logo upload, JSON editors
- **Relationships**: Doctors, patients, appointments, rooms

### Doctor Resource
- **Fields**: User assignment, clinic assignment, medical info, availability
- **Features**: Specialty selection, availability management, signature upload
- **Relationships**: Appointments, encounters, prescriptions

## Common Patterns

### 1. Creating Clinic Data
```php
$clinic = Clinic::create([
    'name' => 'Clinic Name',
    'timezone' => 'Asia/Manila',
    'phone' => '+63 2 1234 5678',
    'email' => 'info@clinic.com',
    'address' => ['street' => '123 Main St', 'city' => 'Manila'],
    'settings' => ['working_hours' => ['monday' => ['09:00', '17:00']]]
]);
```

### 2. Adding Doctor to Clinic
```php
$doctor = Doctor::create([
    'user_id' => $user->id,
    'clinic_id' => $clinic->id,
    'specialty' => 'Cardiology',
    'license_no' => 'MD-12345',
    'is_active' => true,
    'consultation_fee' => 1500.00,
    'availability_schedule' => [
        'monday' => ['09:00', '10:00', '11:00'],
        'tuesday' => ['09:00', '10:00', '11:00']
    ]
]);
```

### 3. Managing User Roles
```php
UserClinicRole::create([
    'user_id' => $user->id,
    'clinic_id' => $clinic->id,
    'role_id' => $role->id
]);
```

## Validation Rules

### Clinic Validation
```php
'name' => 'required|max:255',
'timezone' => 'required|in:Asia/Manila,Asia/Tokyo,UTC',
'phone' => 'required|max:20',
'email' => 'required|email|max:254',
```

### Doctor Validation
```php
'user_id' => 'required|exists:users,id',
'clinic_id' => 'required|exists:clinics,id',
'specialty' => 'required|in:Cardiology,Dermatology,General Practice',
'license_no' => 'required|max:100|unique:doctors,license_no,{{resourceId}}',
```

## Error Handling

### Common Issues
1. **User Access Denied**: Check user role in clinic
2. **Data Not Showing**: Verify clinic_id is set
3. **Foreign Key Errors**: Ensure referenced records exist

### Debug Commands
```bash
# Check user access
php artisan tinker
$user = User::find(1);
$user->clinics;
$user->hasRoleInClinic('admin', 1);

# Check clinic data
$clinic = Clinic::find(1);
$clinic->doctors;
$clinic->statistics;
```

## Performance Tips

### 1. Use Eager Loading
```php
// Good
$clinic = Clinic::with(['doctors.user', 'patients'])->find($id);

// Avoid N+1 queries
$clinic->doctors; // This will be eager loaded
```

### 2. Use Scopes
```php
// Good
$activeDoctors = Doctor::active()->byClinic($clinicId)->get();

// Avoid
$doctors = Doctor::where('clinic_id', $clinicId)->where('is_active', true)->get();
```

### 3. Cache Frequently Used Data
```php
$clinic = Cache::remember("clinic_{$id}", 3600, function () use ($id) {
    return Clinic::with(['doctors', 'statistics'])->find($id);
});
```

## Testing

### Basic Tests
```php
// Test clinic isolation
$clinic1 = Clinic::factory()->create();
$clinic2 = Clinic::factory()->create();

$doctor1 = Doctor::factory()->create(['clinic_id' => $clinic1->id]);
$doctor2 = Doctor::factory()->create(['clinic_id' => $clinic2->id]);

$this->assertNotEquals($doctor1->clinic_id, $doctor2->clinic_id);
```

### Seeding
```bash
php artisan db:seed --class=ClinicSeeder
php artisan db:seed --class=DoctorSeeder
php artisan db:seed --class=UserRoleSeeder
```

## Migration Commands

### Add Missing Fields
```bash
# Add fields to clinics table
php artisan make:migration add_missing_fields_to_clinics_table --table=clinics

# Add fields to doctors table
php artisan make:migration add_missing_fields_to_doctors_table --table=doctors

# Run migrations
php artisan migrate
```

## File Structure

```
app/
├── Models/
│   ├── Clinic.php          # Enhanced with multi-clinic support
│   ├── Doctor.php          # Enhanced with clinic assignment
│   ├── User.php            # Multi-clinic user management
│   └── UserClinicRole.php  # User roles per clinic
├── Nova/
│   ├── Clinic.php          # Enhanced Nova resource
│   └── Doctor.php          # Enhanced Nova resource
└── Providers/
    └── NovaServiceProvider.php # Multi-clinic menu structure
```

## Support

- **Documentation**: See `MULTI_CLINIC_IMPLEMENTATION_GUIDE.md`
- **Issues**: Create issue in project repository
- **Development**: Contact development team

---

*This quick reference covers the essential aspects of the multi-clinic system. For detailed information, refer to the full implementation guide.*
