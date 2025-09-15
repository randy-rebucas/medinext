# MediNext EMR Installation Guide

## Overview

MediNext EMR includes a comprehensive installation system similar to WordPress that guides you through the complete setup process. This installation wizard handles database configuration, user creation, application settings, and core seeding automatically.

## Installation Process

### 1. System Requirements

Before starting the installation, ensure your system meets the following requirements:

#### PHP Requirements
- **PHP Version**: 8.1.0 or higher
- **Extensions Required**:
  - OpenSSL
  - PDO
  - Mbstring
  - Tokenizer
  - XML
  - Ctype
  - JSON
  - BCMath
  - Fileinfo
  - cURL
  - GD

#### File Permissions
- `storage/` directory must be writable
- `bootstrap/cache/` directory must be writable

#### Database
- MySQL 5.7+ or MariaDB 10.2+
- Database server must be running and accessible

### 2. Installation Steps

#### Step 1: Access Installation Wizard
1. Navigate to your MediNext installation URL
2. If the system is not installed, you'll be automatically redirected to the installation wizard
3. The installation URL is: `http://your-domain.com/install`

#### Step 2: System Requirements Check
The installation wizard will automatically check:
- PHP version compatibility
- Required PHP extensions
- File permissions
- Database connectivity

If any requirements are not met, you'll see specific error messages with instructions on how to fix them.

#### Step 3: Database Configuration
1. Enter your database connection details:
   - **Database Host**: Usually `localhost` or your database server IP
   - **Database Port**: Default is `3306` for MySQL
   - **Database Name**: The name of your database (must exist)
   - **Database Username**: Your database username
   - **Database Password**: Your database password

2. Click "Test & Continue" to verify the connection

#### Step 4: Admin Account Setup
1. **Administrator Information**:
   - Full Name
   - Email Address
   - Password (minimum 8 characters)
   - Password Confirmation

2. **Clinic Information**:
   - Clinic Name
   - Phone Number
   - Email Address
   - Complete Address

3. Click "Complete Installation" to proceed

#### Step 5: Installation Complete
The system will:
- Run database migrations
- Create system permissions and roles
- Create your admin account with superadmin privileges
- Set up your clinic with default settings
- Run initial data seeding
- Mark the installation as complete

### 3. Post-Installation

#### Login to Your System
1. Use the admin credentials you created during installation
2. You'll have full superadmin access to the system

#### Initial Configuration
1. **Review Clinic Settings**: Update clinic information, working hours, and branding
2. **Add Staff Members**: Create user accounts for doctors, receptionists, and other staff
3. **Configure Appointments**: Set up appointment types, durations, and scheduling rules
4. **Set Up Notifications**: Configure email and SMS notification settings
5. **Review Security Settings**: Adjust password policies and session timeouts

#### Security Cleanup
After successful installation, remove the installation files for security:

```bash
php artisan install:cleanup
```

This command will:
- Remove installation controller, middleware, and service files
- Remove installation routes
- Remove installation views
- Clear route and config caches
- Update bootstrap configuration

## Installation Features

### Automatic Setup
- **Database Migration**: Automatically runs all database migrations
- **Permission System**: Creates comprehensive role-based permissions
- **User Roles**: Sets up default roles (superadmin, admin, doctor, receptionist, patient)
- **Default Settings**: Applies sensible default settings for clinic operations
- **Core Seeding**: Runs initial data seeding for system functionality

### Error Handling
- **Validation**: Comprehensive form validation with clear error messages
- **Database Testing**: Tests database connection before proceeding
- **Rollback Support**: Database transactions ensure rollback on errors
- **Logging**: Detailed logging for troubleshooting

### Security Features
- **Installation Check**: Prevents access to installation after completion
- **Secure Defaults**: Applies security best practices by default
- **Cleanup Command**: Removes installation files after setup
- **Admin Creation**: Creates secure admin account with proper permissions

## Troubleshooting

### Common Issues

#### Database Connection Failed
- Verify database server is running
- Check database credentials
- Ensure database exists
- Verify network connectivity

#### File Permission Errors
- Set proper permissions on `storage/` directory: `chmod -R 775 storage/`
- Set proper permissions on `bootstrap/cache/` directory: `chmod -R 775 bootstrap/cache/`

#### PHP Extension Missing
- Install missing PHP extensions using your system's package manager
- Restart web server after installing extensions

#### Installation Already Complete
- If you see this message, the system is already installed
- Use the login page to access your admin account
- If you need to reinstall, delete the installation flag file: `storage/app/installation_complete.flag`

### Manual Installation

If the web-based installation fails, you can use the command-line installation:

```bash
# Run migrations
php artisan migrate

# Run seeding
php artisan db:seed --class=BaseSeeder

# Create admin user manually
php artisan tinker
```

Then create an admin user in tinker:
```php
$user = \App\Models\User::create([
    'name' => 'Admin User',
    'email' => 'admin@clinic.com',
    'password' => \Illuminate\Support\Facades\Hash::make('password'),
    'is_active' => true,
    'email_verified_at' => now(),
]);

$superadminRole = \App\Models\Role::where('name', 'superadmin')->first();
$user->roles()->attach($superadminRole->id);
```

## Installation Files

The installation system includes the following files:

### Controllers
- `app/Http/Controllers/InstallationController.php` - Main installation controller

### Middleware
- `app/Http/Middleware/CheckInstallationStatus.php` - Checks if installation is complete

### Services
- `app/Services/InstallationService.php` - Handles installation logic

### Commands
- `app/Console/Commands/RemoveInstallationFiles.php` - Removes installation files

### Routes
- `routes/installation.php` - Installation routes

### Views
- `resources/js/Pages/installation/Welcome.tsx` - System requirements check
- `resources/js/Pages/installation/Database.tsx` - Database configuration
- `resources/js/Pages/installation/Admin.tsx` - Admin account setup
- `resources/js/Pages/installation/Complete.tsx` - Installation complete

## Support

If you encounter issues during installation:

1. Check the system requirements
2. Review error messages carefully
3. Check Laravel logs in `storage/logs/`
4. Verify database connectivity
5. Ensure proper file permissions

For additional support, refer to the MediNext documentation or contact support.

## Security Notes

- Always remove installation files after successful installation
- Use strong passwords for admin accounts
- Keep your system updated
- Regularly backup your database
- Monitor system logs for security issues

The installation system is designed to be secure and user-friendly, providing a smooth setup experience while maintaining security best practices.
