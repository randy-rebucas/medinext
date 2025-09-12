# Demo Account Setup Guide

This guide explains how to create and manage demo accounts with comprehensive sample data for fresh user onboarding and testing purposes.

## Overview

The demo account system creates a complete medical practice environment with:
- **Demo Clinic**: "Demo Medical Center" with realistic settings
- **Demo Users**: Admin, doctors, and receptionist accounts
- **Sample Data**: Patients, appointments, prescriptions, lab results, bills, and more
- **Activity Logs**: Realistic user activity for the past 30 days

## Quick Start

### Method 1: Artisan Command (Recommended)

```bash
# Create demo account with fresh database
php artisan demo:create --fresh

# Create demo account with existing data
php artisan demo:create

# Create with custom settings
php artisan demo:create --clinic-name="My Demo Clinic" --admin-email="admin@mydemo.com" --admin-password="mypassword"
```

### Method 2: API Endpoints

```bash
# Create demo account
curl -X POST http://localhost:8000/api/v1/demo/create \
  -H "Content-Type: application/json" \
  -d '{"fresh": true}'

# Get demo account info
curl -X GET http://localhost:8000/api/v1/demo/info

# Reset demo data
curl -X POST http://localhost:8000/api/v1/demo/reset

# Delete demo account
curl -X DELETE http://localhost:8000/api/v1/demo/delete
```

### Method 3: Database Seeder

```bash
# Run the demo seeder directly
php artisan db:seed --class=DemoAccountSeeder
```

## Demo Account Details

### Login Credentials

#### Admin Account
- **Email**: `demo@medinext.com`
- **Password**: `demo123`
- **Role**: Admin (full system access)

#### Staff Accounts
- **Doctor 1**: `doctor1@demomedical.com` (demo123)
- **Doctor 2**: `doctor2@demomedical.com` (demo123)
- **Doctor 3**: `doctor3@demomedical.com` (demo123)
- **Doctor 4**: `doctor4@demomedical.com` (demo123)
- **Doctor 5**: `doctor5@demomedical.com` (demo123)
- **Receptionist**: `receptionist@demomedical.com` (demo123)

### Demo Data Created

| Data Type | Count | Description |
|-----------|-------|-------------|
| **Patients** | 8 | Complete patient profiles with contact info, allergies, consents |
| **Doctors** | 5 | Different specialties (Cardiology, Pediatrics, etc.) |
| **Rooms** | 7 | Consultation, examination, procedure, and waiting rooms |
| **Appointments** | 60+ | Past and future appointments with various statuses |
| **Encounters** | 30+ | Completed patient encounters with medical notes |
| **Prescriptions** | 15+ | Active and completed prescriptions with QR codes |
| **Lab Results** | 20+ | Various test types with normal/abnormal results |
| **Bills** | 25+ | Patient bills with items and payment status |
| **Insurance** | 12+ | Insurance records for patients |
| **Activity Logs** | 500+ | User activity for the past 30 days |
| **Notifications** | 4 | System notifications and reminders |

## Features Demonstrated

### 1. Patient Management
- Complete patient profiles with demographics
- Medical history and allergies
- Contact information and addresses
- Consent management

### 2. Appointment Scheduling
- Multi-doctor scheduling
- Room assignments
- Various appointment statuses
- Different appointment sources (walk-in, phone, online)

### 3. Medical Records
- Patient encounters with detailed notes
- Vital signs recording
- Chief complaints and assessments
- Treatment plans

### 4. Prescription Management
- Digital prescriptions with QR codes
- Prescription status tracking
- Doctor signatures and notes

### 5. Lab Results
- Various test types (CBC, Chemistry, etc.)
- Normal and abnormal results
- Reference ranges
- Result interpretation

### 6. Billing System
- Patient bills with line items
- Payment tracking
- Insurance integration
- Multiple payment methods

### 7. Queue Management
- Patient queue system
- Priority levels
- Status tracking
- Queue notes

### 8. Activity Logging
- Comprehensive audit trail
- User actions tracking
- System events logging
- Data change history

## Use Cases

### 1. Client Demonstrations
- Showcase all system features with realistic data
- Demonstrate different user roles and permissions
- Present real-world scenarios and workflows

### 2. Training and Onboarding
- Train new users with safe demo environment
- Practice common workflows without affecting real data
- Learn system features and capabilities

### 3. Testing and Development
- Test new features with comprehensive data
- Validate system performance with realistic data volumes
- Debug issues in a controlled environment

### 4. Sales and Marketing
- Provide prospects with hands-on experience
- Demonstrate ROI and efficiency gains
- Show integration capabilities

## Management Commands

### Create Demo Account
```bash
php artisan demo:create [options]

Options:
  --fresh              Drop all tables and recreate them
  --force              Force the operation without confirmation
  --clinic-name=NAME   Name of the demo clinic
  --admin-email=EMAIL  Email for the demo admin
  --admin-password=PWD Password for the demo admin
```

### Examples
```bash
# Basic demo account
php artisan demo:create

# Fresh installation with custom clinic name
php artisan demo:create --fresh --clinic-name="Acme Medical Center"

# Force creation without confirmation
php artisan demo:create --fresh --force
```

## API Endpoints

### Create Demo Account
```http
POST /api/v1/demo/create
Content-Type: application/json

{
  "clinic_name": "My Demo Clinic",
  "admin_email": "admin@mydemo.com",
  "admin_password": "mypassword",
  "fresh": true
}
```

### Get Demo Account Info
```http
GET /api/v1/demo/info
```

### Reset Demo Data
```http
POST /api/v1/demo/reset
```

### Delete Demo Account
```http
DELETE /api/v1/demo/delete
```

## Best Practices

### 1. Environment Setup
- Use demo accounts only in development/staging environments
- Never use demo data in production
- Regularly reset demo data to maintain consistency

### 2. Data Management
- Reset demo data before important demonstrations
- Customize demo data for specific client needs
- Keep demo data realistic and relevant

### 3. Security
- Change default passwords in production-like environments
- Monitor demo account usage
- Implement proper access controls

### 4. Maintenance
- Regularly update demo data to reflect new features
- Clean up old demo accounts periodically
- Monitor system performance with demo data

## Troubleshooting

### Common Issues

#### Demo Account Already Exists
```bash
# Delete existing demo account first
php artisan demo:create --fresh --force
```

#### Database Connection Issues
```bash
# Check database configuration
php artisan config:show database

# Test database connection
php artisan tinker
>>> DB::connection()->getPdo();
```

#### Permission Issues
```bash
# Ensure proper file permissions
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/
```

### Getting Help

1. Check the application logs: `storage/logs/laravel.log`
2. Run with verbose output: `php artisan demo:create -v`
3. Check database constraints and foreign keys
4. Verify all required models and relationships exist

## Customization

### Custom Demo Data
You can customize the demo data by modifying the `DemoAccountSeeder` class:

```php
// database/seeders/DemoAccountSeeder.php
private function createDemoPatients(): void
{
    // Add your custom patient data here
    $customPatients = [
        [
            'first_name' => 'Your',
            'last_name' => 'Patient',
            // ... other fields
        ]
    ];
    
    // Use your custom data instead of the default
}
```

### Custom Clinic Settings
```php
private function createDemoClinic(): void
{
    $this->demoClinic = Clinic::create([
        'name' => 'Your Custom Clinic Name',
        'settings' => [
            'working_hours' => [
                // Your custom working hours
            ],
            // Other custom settings
        ]
    ]);
}
```

## Conclusion

The demo account system provides a comprehensive solution for showcasing MediNext capabilities. It creates a realistic medical practice environment that allows users to explore all features safely and effectively.

For additional support or customization requests, please refer to the main documentation or contact the development team.
