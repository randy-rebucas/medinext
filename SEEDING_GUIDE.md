# MediNext Database Seeding Guide

This guide explains the different seeding options available for the MediNext application and when to use each one.

## Overview

The MediNext application provides several seeding options to accommodate different deployment scenarios:

1. **Initial Seeder** - For fresh production deployments
2. **Deployment Seeder** - Explicit deployment seeding
3. **Database Seeder** - Smart seeding that detects fresh vs existing installations
4. **Demo Seeders** - For development and demonstration purposes

## Seeding Options

### 1. Initial Seeder (Recommended for Production)

**Purpose**: Creates only essential data needed for a fresh deployment.

**Command**: 
```bash
php artisan db:seed --class=InitialSeeder
```

**What it creates**:
- Core permissions and roles (superadmin, admin, doctor, receptionist, patient, medrep)
- Default application settings
- Nova super admin user

**Users created**:
- **Nova Super Admin**: nova@medinext.com / nova123

### 2. Deployment Seeder

**Purpose**: Explicit deployment seeding that always runs the initial seeder.

**Command**:
```bash
php artisan db:seed --class=DeploymentSeeder
```

**What it does**:
- Runs the InitialSeeder
- Provides clear deployment completion message
- Suggests next steps for adding demo data if needed

### 3. Database Seeder (Smart Seeding)

**Purpose**: Automatically detects if this is a fresh installation or existing data.

**Command**:
```bash
php artisan db:seed
```

**Behavior**:
- **Fresh installation** (no users exist): Runs InitialSeeder
- **Existing data**: Runs full development seeders with demo data

### 4. Demo Seeders (Development Only)

**Purpose**: Creates comprehensive demo data for development and testing.

**Available demo seeders**:
- `DemoAccountSeeder` - Complete demo account with sample data
- `SimpleDemoSeeder` - Minimal demo data
- `MinimalDemoSeeder` - Very basic demo data
- `EMRSeeder` - Comprehensive EMR data

**Commands**:
```bash
# Full demo account
php artisan db:seed --class=DemoAccountSeeder

# Simple demo
php artisan db:seed --class=SimpleDemoSeeder

# Minimal demo
php artisan db:seed --class=MinimalDemoSeeder

# EMR data only
php artisan db:seed --class=EMRSeeder
```

## Deployment Workflow

### Fresh Production Deployment

1. **Run migrations**:
   ```bash
   php artisan migrate
   ```

2. **Seed initial data**:
   ```bash
   php artisan db:seed --class=InitialSeeder
   ```

3. **Configure your system**:
   - Log in with nova@medinext.com
   - Create your first clinic through Nova
   - Configure settings
   - Add users and staff as needed

### Development Setup

1. **Run migrations**:
   ```bash
   php artisan migrate
   ```

2. **Seed with demo data**:
   ```bash
   php artisan db:seed
   ```

   Or for specific demo data:
   ```bash
   php artisan db:seed --class=DemoAccountSeeder
   ```

## What Each Seeder Creates

### InitialSeeder
- ✅ Permissions and roles
- ✅ Default settings
- ✅ Nova super admin user
- ❌ No clinic data
- ❌ No demo patients
- ❌ No demo appointments
- ❌ No demo prescriptions
- ❌ No activity logs

### DemoAccountSeeder
- ✅ Everything from InitialSeeder
- ✅ Sample patients
- ✅ Sample appointments
- ✅ Sample prescriptions
- ✅ Sample lab results
- ✅ Sample medical representatives
- ✅ Activity logs
- ✅ File assets

### EMRSeeder
- ✅ Comprehensive EMR data
- ✅ Multiple clinics
- ✅ Multiple doctors
- ✅ Patient encounters
- ✅ Medical records
- ✅ Lab results
- ✅ Prescriptions

## Security Considerations

### Production Deployment
- **Change default passwords** immediately after deployment
- **Update clinic information** with real data
- **Configure proper license** information
- **Review and adjust permissions** as needed

### Default Passwords
- Nova Super Admin: `nova123`

**⚠️ IMPORTANT**: This password should be changed immediately in production!

## Troubleshooting

### Common Issues

1. **Seeder fails with foreign key errors**:
   - Ensure migrations are run first: `php artisan migrate`
   - Check that dependencies are seeded in correct order

2. **Duplicate data errors**:
   - Use `firstOrCreate()` methods in seeders
   - Check for existing data before seeding

3. **Permission errors**:
   - Ensure database user has proper permissions
   - Check file permissions for storage directories

### Resetting Database

To completely reset and reseed:

```bash
# Drop all tables and re-run migrations
php artisan migrate:fresh

# Seed with initial data
php artisan db:seed --class=InitialSeeder
```

## Best Practices

1. **Production**: Always use `InitialSeeder` for fresh deployments
2. **Development**: Use `DatabaseSeeder` or specific demo seeders
3. **Testing**: Use `MinimalDemoSeeder` for quick test setups
4. **Documentation**: Use `DemoAccountSeeder` for comprehensive demos

## File Structure

```
database/seeders/
├── InitialSeeder.php          # Essential deployment data
├── DeploymentSeeder.php       # Explicit deployment seeder
├── DatabaseSeeder.php         # Smart seeder with detection
├── DemoAccountSeeder.php      # Complete demo account
├── SimpleDemoSeeder.php       # Basic demo data
├── MinimalDemoSeeder.php      # Minimal demo data
├── EMRSeeder.php             # Comprehensive EMR data
├── PermissionSeeder.php      # Permissions and roles
├── ClinicSeeder.php          # Clinic data
├── UserRoleSeeder.php        # User and role assignments
├── NovaUserSeeder.php        # Nova admin user
├── SettingsSeeder.php        # Application settings
├── LicenseSeeder.php         # License configuration
├── RoomSeeder.php            # Room data
├── DoctorSeeder.php          # Doctor data
├── PatientSeeder.php         # Patient data
├── AppointmentSeeder.php     # Appointment data
├── PrescriptionSeeder.php    # Prescription data
├── LabResultSeeder.php       # Lab result data
├── MedrepSeeder.php          # Medical representative data
├── ActivityLogSeeder.php     # Activity log data
└── FileAssetSeeder.php       # File asset data
```

## Support

For issues with seeding:
1. Check the console output for specific error messages
2. Verify database connection and permissions
3. Ensure all migrations have been run
4. Check the application logs in `storage/logs/`
