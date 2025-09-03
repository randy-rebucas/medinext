# Database Seeders

This directory contains seeders for populating the database with initial data.

## UserRoleSeeder

The `UserRoleSeeder` creates users for each role type in the system:

### Roles Created:
- **superadmin** - Super Admin User
- **admin** - Clinic Admin User  
- **doctor** - Dr. John Smith
- **receptionist** - Jane Receptionist
- **patient** - Patient User
- **medrep** - Medical Representative

### Default Clinic:
- **Main Clinic** - A sample clinic with complete address and working hours

### Login Credentials:
All users are created with the password: `password123`

### Email Addresses:
- superadmin@clinicflow.com
- admin@clinicflow.com
- doctor@clinicflow.com
- receptionist@clinicflow.com
- patient@clinicflow.com
- medrep@clinicflow.com

## Running the Seeders

### Run all seeders:
```bash
php artisan db:seed
```

### Run specific seeder:
```bash
php artisan db:seed --class=UserRoleSeeder
```

### Fresh database with seeding:
```bash
php artisan migrate:fresh --seed
```

## Database Structure

The seeder creates:
1. A default clinic with complete settings
2. All role types
3. One user for each role type
4. Proper role assignments in the clinic

This provides a complete starting point for testing and development.
