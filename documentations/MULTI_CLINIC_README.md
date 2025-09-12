# Multi-Clinic & Multi-Doctor Support

This document describes the implementation of multi-clinic and multi-doctor support in the MyClinicSoft API.

## Overview

The system now supports managing multiple clinics, each with multiple doctors, patients, and staff members. Each clinic operates independently with its own data, while maintaining centralized user management.

## Key Features

### 1. Multi-Clinic Support
- **Clinic Management**: Create, edit, and manage multiple clinics
- **Clinic Isolation**: Each clinic maintains separate data (patients, appointments, etc.)
- **Clinic Settings**: Customizable timezone, address, and working hours per clinic
- **Clinic Logo**: Support for clinic branding

### 2. Multi-Doctor Support
- **Doctor Registration**: Add doctors to specific clinics
- **Specialty Management**: Track doctor specialties and licenses
- **Doctor Scheduling**: Manage doctor availability and appointments
- **Cross-Clinic Doctors**: Doctors can work in multiple clinics if needed

### 3. Role-Based Access Control
- **User Roles**: superadmin, admin, doctor, receptionist, patient, medrep
- **Clinic-Specific Permissions**: Users can have different roles in different clinics
- **Access Control**: Middleware ensures users only access authorized clinics

## Database Schema

### Core Tables
- `clinics` - Clinic information and settings
- `users` - User accounts (can belong to multiple clinics)
- `user_clinic_roles` - User roles within specific clinics
- `doctors` - Doctor records linked to users and clinics
- `patients` - Patient records per clinic
- `appointments` - Appointments linked to clinics, doctors, and patients
- `encounters` - Patient encounters with doctors
- `prescriptions` - Medical prescriptions
- `rooms` - Clinic rooms for appointments

### Key Relationships
- Users can belong to multiple clinics with different roles
- Doctors are users with additional medical information
- All clinical data (patients, appointments, etc.) is clinic-scoped
- Foreign key constraints ensure data integrity

## API Endpoints

### Clinic Management
- `GET /clinics` - List all clinics
- `POST /clinics` - Create new clinic
- `GET /clinics/{id}` - View clinic details
- `PUT /clinics/{id}` - Update clinic
- `DELETE /clinics/{id}` - Delete clinic
- `GET /clinics/{id}/members` - Manage clinic members

### Doctor Management
- `GET /doctors` - List all doctors (with clinic filtering)
- `POST /doctors` - Add new doctor to clinic
- `GET /doctors/{id}` - View doctor details
- `PUT /doctors/{id}` - Update doctor information
- `DELETE /doctors/{id}` - Remove doctor
- `GET /doctors/{id}/schedule` - View doctor's schedule
- `GET /doctors/{id}/patients` - View doctor's patients

## Usage Examples

### Creating a New Clinic
```php
$clinic = Clinic::create([
    'name' => 'Metro Medical Center',
    'timezone' => 'Asia/Manila',
    'address' => [
        'street' => '123 Medical Plaza',
        'city' => 'Manila',
        'state' => 'Metro Manila',
        'country' => 'Philippines'
    ]
]);
```

### Adding a Doctor to a Clinic
```php
$doctor = Doctor::create([
    'user_id' => $user->id,
    'clinic_id' => $clinic->id,
    'specialty' => 'Cardiology',
    'license_no' => 'MD-12345'
]);
```

### Checking User Access
```php
if ($user->hasRoleInClinic('admin', $clinicId)) {
    // User is admin in this clinic
}

if ($user->isDoctorInClinic($clinicId)) {
    // User is a doctor in this clinic
}
```

## Security Features

### Access Control
- **Clinic Access Middleware**: Ensures users can only access authorized clinics
- **Role Validation**: Verifies user permissions before allowing actions
- **Data Isolation**: Clinic data is automatically scoped to user's authorized clinics

### Data Protection
- **Foreign Key Constraints**: Prevents orphaned data
- **Cascade Deletes**: Proper cleanup when clinics are removed
- **Input Validation**: Comprehensive validation for all clinic and doctor data

## Frontend Integration

### Navigation
- Added clinic and doctor management to main navigation
- Clinic-specific views and forms
- Responsive design for mobile and desktop

### Views Created
- `clinics/index.blade.php` - List all clinics
- `clinics/create.blade.php` - Create new clinic form
- `clinics/show.blade.php` - Clinic details and dashboard
- `doctors/index.blade.php` - List all doctors
- Additional views for editing and management

## Seeding and Testing

### Sample Data
The `ClinicSeeder` creates:
- 3 sample clinics with different configurations
- 6 user roles (superadmin, admin, doctor, etc.)
- 3-5 sample doctors per clinic
- A superadmin user with access to all clinics

### Running Seeds
```bash
php artisan db:seed --class=ClinicSeeder
```

## Future Enhancements

### Planned Features
- **Clinic Branches**: Support for multiple locations per clinic
- **Doctor Scheduling**: Advanced scheduling with availability management
- **Patient Portal**: Patient access to their clinic data
- **Reporting**: Clinic-specific analytics and reports
- **Integration**: API endpoints for external system integration

### Scalability Considerations
- **Database Indexing**: Optimized queries for large datasets
- **Caching**: Redis integration for frequently accessed data
- **API Rate Limiting**: Protection against abuse
- **Audit Logging**: Track all clinic and doctor changes

## Troubleshooting

### Common Issues
1. **User Access Denied**: Check if user has proper role in clinic
2. **Data Not Showing**: Verify clinic_id is properly set in queries
3. **Foreign Key Errors**: Ensure referenced records exist before deletion

### Debug Commands
```bash
# Check user's clinic access
php artisan tinker
$user = User::find(1);
$user->clinics; // Shows all clinics user belongs to
$user->hasRoleInClinic('admin', 1); // Check specific role
```

## Contributing

When adding new features:
1. Ensure clinic-scoping is maintained
2. Add proper role-based access control
3. Include validation and error handling
4. Update this documentation
5. Add appropriate tests

## Support

For questions or issues related to multi-clinic functionality:
1. Check the database schema and relationships
2. Verify user roles and permissions
3. Review the middleware implementation
4. Check the error logs for specific issues
