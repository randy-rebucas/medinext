# Application Settings & Activity Log System - Implementation Guide

## Overview

This document provides a comprehensive guide to the Application Settings and Activity Log systems implemented in MediNext. These systems provide customizable system configuration and comprehensive activity tracking for healthcare operations.

## System Architecture

### Core Components

1. **Application Settings** - Configurable system settings with clinic-specific customization
2. **Activity Logging** - Comprehensive tracking of all system activities
3. **Settings Management** - Grouped settings with validation and caching
4. **Activity Monitoring** - Real-time activity tracking with categorization and severity

### Database Schema

```
settings (id, clinic_id, key, value, type, group, description, is_public)
└── activity_logs (id, clinic_id, actor_user_id, entity, entity_id, action, at, ip, meta, before_hash, after_hash)
```

## Application Settings System

### Overview

The Application Settings system provides a flexible, hierarchical configuration management system that allows clinics to customize their operations, branding, and system behavior.

### Setting Categories

#### 1. **Clinic Information**
- **Purpose**: Basic clinic details and contact information
- **Settings**:
  - `clinic.name` - Official clinic name
  - `clinic.phone` - Primary contact phone
  - `clinic.email` - Primary contact email
  - `clinic.address` - Complete clinic address
  - `clinic.website` - Clinic website URL
  - `clinic.description` - Clinic service description

#### 2. **Working Hours**
- **Purpose**: Clinic operating hours and availability
- **Settings**:
  - `working_hours.monday` - Monday hours (start, end, closed)
  - `working_hours.tuesday` - Tuesday hours (start, end, closed)
  - `working_hours.wednesday` - Wednesday hours (start, end, closed)
  - `working_hours.thursday` - Thursday hours (start, end, closed)
  - `working_hours.friday` - Friday hours (start, end, closed)
  - `working_hours.saturday` - Saturday hours (start, end, closed)
  - `working_hours.sunday` - Sunday hours (start, end, closed)

#### 3. **Notifications**
- **Purpose**: Communication and reminder settings
- **Settings**:
  - `notifications.email_enabled` - Enable email notifications
  - `notifications.sms_enabled` - Enable SMS notifications
  - `notifications.appointment_reminder_hours` - Appointment reminder timing
  - `notifications.follow_up_days` - Follow-up message timing

#### 4. **Branding**
- **Purpose**: Visual identity and customization
- **Settings**:
  - `branding.primary_color` - Primary brand color (hex)
  - `branding.secondary_color` - Secondary brand color (hex)
  - `branding.logo_url` - Clinic logo URL
  - `branding.favicon_url` - Clinic favicon URL

#### 5. **System Settings**
- **Purpose**: Core system configuration
- **Settings**:
  - `system.timezone` - Default timezone
  - `system.date_format` - Date display format
  - `system.time_format` - Time display format
  - `system.currency` - Default currency

### Setting Types

#### Data Types
- **string** - Text values (e.g., clinic name, phone number)
- **integer** - Numeric values (e.g., reminder hours, follow-up days)
- **boolean** - True/false values (e.g., notification enabled flags)
- **array** - List values (e.g., working hours structure)
- **object** - Complex structured data
- **json** - JSON-formatted data

#### Access Control
- **Public Settings** - Accessible to all users and patients
- **Private Settings** - Restricted to authorized staff only

### Implementation Details

#### 1. Setting Model (`app/Models/Setting.php`)

##### Key Features
- **Caching**: Automatic caching with cache invalidation
- **Validation**: Type-based value validation
- **Grouping**: Logical organization by functional area
- **Clinic Scoping**: Clinic-specific or global settings

##### Enhanced Methods
```php
// Get setting value with caching
$clinicName = Setting::getValue('clinic.name', 'Default Clinic', $clinicId);

// Set setting value with validation
Setting::setValue('clinic.phone', '+63 123 456 7890', $clinicId, 'string', 'clinic', 'Primary contact phone');

// Get multiple settings by group
$clinicSettings = Setting::getGroup('clinic', $clinicId);

// Get all settings for clinic
$allSettings = Setting::getAllForClinic($clinicId);
```

##### Scopes
```php
// Filter by group
Setting::byGroup('clinic')->get();

// Filter by type
Setting::byType('boolean')->get();

// Filter public/private settings
Setting::public()->get();
Setting::private()->get();

// Filter by clinic
Setting::byClinic($clinicId)->get();
```

#### 2. Setting Validation

##### Automatic Validation
```php
// Type-based validation
Setting::validateValue('test@email.com', 'string'); // true
Setting::validateValue('invalid-email', 'email'); // false

// Required settings validation
$setting = Setting::where('key', 'clinic.name')->first();
if ($setting->isRequired()) {
    // Ensure value is set
}

// Editable settings check
if ($setting->isEditable()) {
    // Allow modification
}
```

##### Validation Rules
```php
// Get validation rules for setting
$rules = $setting->validation_rules;
// Returns: ['required', 'string', 'max:255']

// Specific validation for certain keys
$emailRules = Setting::where('key', 'clinic.email')->first()->validation_rules;
// Returns: ['required', 'string', 'email']
```

#### 3. Setting Dependencies

##### Dependency Management
```php
// Check if setting has dependencies
if ($setting->hasDependencies()) {
    $deps = $setting->dependencies;
    // Handle dependent settings
}

// Example dependencies
'notifications.sms_enabled' => ['notifications.sms_provider', 'notifications.sms_api_key']
'branding.logo_url' => ['branding.logo_width', 'branding.logo_height']
```

### Usage Examples

#### 1. Clinic Configuration
```php
// Set clinic information
Setting::setValue('clinic.name', 'MediNext Clinic', $clinicId, 'string', 'clinic', 'Clinic name');
Setting::setValue('clinic.phone', '+63 123 456 7890', $clinicId, 'string', 'clinic', 'Contact phone');
Setting::setValue('clinic.address', [
    'street' => '123 Main Street',
    'city' => 'Manila',
    'province' => 'Metro Manila',
    'postal_code' => '1000',
    'country' => 'Philippines'
], $clinicId, 'json', 'clinic', 'Clinic address');
```

#### 2. Working Hours Configuration
```php
// Set working hours
Setting::setValue('working_hours.monday', [
    'start' => '08:00',
    'end' => '17:00',
    'closed' => false
], $clinicId, 'json', 'working_hours', 'Monday working hours');

Setting::setValue('working_hours.sunday', [
    'start' => '00:00',
    'end' => '00:00',
    'closed' => true
], $clinicId, 'json', 'working_hours', 'Sunday working hours');
```

#### 3. Notification Settings
```php
// Configure notifications
Setting::setValue('notifications.email_enabled', true, $clinicId, 'boolean', 'notifications', 'Enable email notifications');
Setting::setValue('notifications.appointment_reminder_hours', 24, $clinicId, 'integer', 'notifications', 'Appointment reminder timing');
```

## Activity Log System

### Overview

The Activity Log system provides comprehensive tracking of all system activities, including user actions, data changes, and system events. This ensures auditability, compliance, and operational transparency.

### Activity Categories

#### 1. **User Management**
- **Actions**: `logged_in`, `logged_out`, `password_changed`, `role_assigned`, `permission_granted`
- **Entities**: `User`, `Role`, `Permission`
- **Purpose**: Track user access and security changes

#### 2. **Patient Care**
- **Actions**: `created`, `updated`, `viewed`, `checkin`, `checkout`
- **Entities**: `Patient`, `Appointment`, `Prescription`, `LabResult`
- **Purpose**: Monitor patient care activities

#### 3. **Clinical Operations**
- **Actions**: `appointment_scheduled`, `prescription_issued`, `lab_result_ordered`
- **Entities**: `Appointment`, `Prescription`, `LabResult`
- **Purpose**: Track clinical workflow activities

#### 4. **Financial Operations**
- **Actions**: `payment_received`, `refund_issued`
- **Entities**: `Billing`, `Payment`
- **Purpose**: Monitor financial transactions

#### 5. **System Administration**
- **Actions**: `created`, `updated`, `deleted`, `exported`, `imported`
- **Entities**: `Clinic`, `Setting`, `Report`
- **Purpose**: Track system configuration changes

### Activity Severity Levels

#### High Severity
- **Actions**: `deleted`, `password_changed`
- **Description**: Critical operations requiring immediate attention
- **Color**: Red (danger)

#### Medium Severity
- **Actions**: `role_assigned`, `permission_granted`, `file_uploaded`, `payment_received`
- **Description**: Important operations requiring monitoring
- **Color**: Yellow (warning)

#### Low Severity
- **Actions**: `viewed`, `logged_in`, `appointment_scheduled`, `created`
- **Description**: Normal operations for audit purposes
- **Color**: Blue (info)

### Implementation Details

#### 1. ActivityLog Model (`app/Models/ActivityLog.php`)

##### Key Features
- **Comprehensive Tracking**: All system activities with metadata
- **Severity Classification**: Risk-based activity categorization
- **Metadata Storage**: Rich context information for each activity
- **Performance Optimization**: Efficient querying and caching

##### Enhanced Methods
```php
// Log activity with automatic metadata
ActivityLog::log('Patient', 'created', $patientId, $clinicId, $userId, [
    'patient_name' => $patient->name,
    'patient_id' => $patient->id
]);

// Get activity statistics
$stats = ActivityLog::getStatistics($clinicId, 30); // Last 30 days

// Get activity trends
$trends = ActivityLog::getTrends($clinicId, 30); // Last 30 days
```

##### Scopes
```php
// Filter by entity type
ActivityLog::byEntity('Patient')->get();

// Filter by action type
ActivityLog::byAction('created')->get();

// Filter by clinic
ActivityLog::byClinic($clinicId)->get();

// Filter by actor
ActivityLog::byActor($userId)->get();

// Filter by date range
ActivityLog::byDateRange($startDate, $endDate)->get();

// Filter recent activities
ActivityLog::recent(7)->get(); // Last 7 days
ActivityLog::today()->get();
ActivityLog::thisWeek()->get();
ActivityLog::thisMonth()->get();
```

#### 2. Activity Metadata

##### Rich Context Information
```php
// Patient activity metadata
$meta = [
    'patient_name' => 'John Doe',
    'patient_id' => 123,
    'user_agent' => 'Mozilla/5.0...',
    'session_id' => 'session_abc123',
    'request_method' => 'POST',
    'url' => '/patients/123'
];

// Appointment activity metadata
$meta = [
    'appointment_date' => '2025-01-15 10:00:00',
    'patient_name' => 'John Doe',
    'doctor_name' => 'Dr. Smith',
    'duration' => 30
];
```

#### 3. Activity Descriptions

##### Human-Readable Descriptions
```php
// Automatic description generation
$log = ActivityLog::where('action', 'appointment_scheduled')->first();
echo $log->description;
// Output: "Dr. Smith scheduled appointment for John Doe on Jan 15, 2025 at 10:00 AM"

$log = ActivityLog::where('action', 'prescription_issued')->first();
echo $log->description;
// Output: "Dr. Smith issued prescription for John Doe on Jan 15, 2025"
```

### Usage Examples

#### 1. Logging User Activities
```php
// Log user login
ActivityLog::log('User', 'logged_in', $userId, $clinicId, $userId, [
    'ip_address' => request()->ip(),
    'user_agent' => request()->userAgent(),
    'session_id' => session()->getId()
]);

// Log password change
ActivityLog::log('User', 'password_changed', $userId, $clinicId, $userId, [
    'changed_at' => now()->format('Y-m-d H:i:s'),
    'ip_address' => request()->ip()
]);
```

#### 2. Logging Patient Care Activities
```php
// Log patient creation
ActivityLog::log('Patient', 'created', $patient->id, $clinicId, $userId, [
    'patient_name' => $patient->name,
    'patient_id' => $patient->id,
    'created_by' => $user->name
]);

// Log appointment scheduling
ActivityLog::log('Appointment', 'appointment_scheduled', $appointment->id, $clinicId, $userId, [
    'appointment_date' => $appointment->start_at->format('Y-m-d H:i:s'),
    'patient_name' => $appointment->patient->name,
    'doctor_name' => $appointment->doctor->user->name,
    'duration' => $appointment->duration
]);
```

#### 3. Logging System Changes
```php
// Log setting changes
ActivityLog::log('Setting', 'updated', $setting->id, $clinicId, $userId, [
    'setting_key' => $setting->key,
    'old_value' => $oldValue,
    'new_value' => $setting->value,
    'group' => $setting->group
]);

// Log role assignment
ActivityLog::log('User', 'role_assigned', $user->id, $clinicId, $adminId, [
    'role_name' => $role->name,
    'assigned_by' => $admin->name,
    'clinic_name' => $clinic->name
]);
```

## Nova Admin Interface

### 1. Settings Resource (`app/Nova/Setting.php`)

#### Enhanced Fields
- **Setting Information**: Key, category, group display name, description
- **Configuration**: Type, type display name, group
- **Value Management**: Value, formatted value
- **Access Control**: Public/private flags, clinic association
- **Validation**: Required, editable, valid status indicators
- **Help & Context**: Help text, validation rules, dependencies
- **Usage Statistics**: Cache keys, validation status

#### Features
- **Logical Organization**: Grouped fields with clear headings
- **Visual Indicators**: Badges for status and requirements
- **Context Help**: Comprehensive help text and guidance
- **Validation Feedback**: Real-time validation status

### 2. ActivityLog Resource (`app/Nova/ActivityLog.php`)

#### Enhanced Fields
- **Activity Information**: Clinic, actor, entity, entity ID
- **Action Details**: Action, action display name
- **Classification**: Category, severity, color indicators
- **Timing**: Timestamp, formatted time, recent status
- **Technical Details**: IP address, icon
- **Metadata**: Meta data, formatted metadata
- **Activity Summary**: Description, critical status
- **Relationships**: Related activities count

#### Features
- **Comprehensive Tracking**: All activity details with context
- **Severity Indicators**: Color-coded risk levels
- **Rich Metadata**: Detailed activity information
- **Performance Monitoring**: Activity trends and statistics

## Security Features

### 1. Settings Security

#### Access Control
- **Public Settings**: Accessible to all users
- **Private Settings**: Restricted to authorized staff
- **Clinic Scoping**: Settings isolated per clinic

#### Validation
- **Type Validation**: Automatic data type checking
- **Required Settings**: Essential settings validation
- **Dependency Management**: Related settings coordination

### 2. Activity Log Security

#### Data Protection
- **IP Address Tracking**: Monitor access locations
- **User Attribution**: All activities linked to users
- **Metadata Encryption**: Sensitive data protection

#### Audit Trail
- **Complete History**: All changes tracked
- **Before/After States**: Change tracking capability
- **Timestamp Accuracy**: Precise activity timing

## Performance & Scalability

### 1. Settings Optimization

#### Caching Strategy
```php
// Automatic cache management
$setting = Setting::getValue('clinic.name', 'Default', $clinicId);
// Cached for 1 hour, automatically invalidated on changes

// Batch settings retrieval
$clinicSettings = Setting::getGroup('clinic', $clinicId);
// Efficient group-based caching
```

#### Query Optimization
```php
// Use scopes for filtering
Setting::byGroup('clinic')->byClinic($clinicId)->get();

// Eager loading for relationships
Setting::with('clinic')->get();
```

### 2. Activity Log Optimization

#### Efficient Logging
```php
// Batch logging for multiple activities
foreach ($activities as $activity) {
    ActivityLog::log($activity['entity'], $activity['action'], 
                     $activity['entity_id'], $clinicId, $userId, $activity['meta']);
}

// Statistics caching
$stats = Cache::remember("activity_stats_{$clinicId}", 3600, function () use ($clinicId) {
    return ActivityLog::getStatistics($clinicId, 30);
});
```

## Testing & Quality Assurance

### 1. Settings Testing

#### Validation Testing
```php
// Test setting validation
$setting = new Setting(['key' => 'test', 'type' => 'email', 'value' => 'invalid-email']);
$this->assertFalse($setting->isValid());

// Test required settings
$requiredSetting = Setting::where('key', 'clinic.name')->first();
$this->assertTrue($requiredSetting->isRequired());
```

#### Caching Testing
```php
// Test cache invalidation
$setting = Setting::setValue('test.key', 'value', $clinicId);
$this->assertNull(Cache::get("setting_test.key_{$clinicId}"));
```

### 2. Activity Log Testing

#### Logging Testing
```php
// Test activity logging
$log = ActivityLog::log('Test', 'created', 1, $clinicId, $userId);
$this->assertNotNull($log->id);
$this->assertEquals('Test', $log->entity);

// Test metadata
$this->assertEquals($userId, $log->actor_user_id);
$this->assertEquals($clinicId, $log->clinic_id);
```

## Troubleshooting

### Common Issues

1. **Settings Not Updating**
   - Check cache invalidation
   - Verify clinic_id is set correctly
   - Check validation rules

2. **Activity Logs Missing**
   - Verify logging is enabled
   - Check database permissions
   - Monitor log file errors

3. **Performance Issues**
   - Review cache configuration
   - Check database indexing
   - Monitor query performance

### Debug Commands

```bash
# Check settings
php artisan tinker
$setting = Setting::getValue('clinic.name', null, 1);
echo $setting; // Should show clinic name

# Check activity logs
$logs = ActivityLog::byEntity('User')->recent(7)->get();
echo $logs->count(); // Should show recent user activities

# Clear settings cache
php artisan cache:clear
```

## Future Enhancements

### Planned Features

1. **Advanced Settings**
   - Setting templates
   - Bulk settings import/export
   - Setting versioning

2. **Enhanced Activity Logging**
   - Real-time activity monitoring
   - Activity alerts and notifications
   - Advanced analytics and reporting

3. **Integration Features**
   - Webhook notifications
   - Third-party integrations
   - API endpoints for external access

## Conclusion

The Application Settings and Activity Log systems provide a robust foundation for healthcare system configuration and monitoring. With comprehensive settings management, automatic activity tracking, and enhanced security features, these systems ensure operational efficiency, compliance, and transparency.

The systems are designed to be:
- **Flexible**: Easy configuration and customization
- **Secure**: Proper access control and validation
- **Scalable**: Efficient performance and caching
- **Comprehensive**: Complete activity tracking and audit trail

For additional support or questions about the settings and activity logging systems, please refer to the development team or create an issue in the project repository.
