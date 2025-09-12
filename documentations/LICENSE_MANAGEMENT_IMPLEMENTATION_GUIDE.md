# License Management System Implementation Guide

## Overview

This document provides a comprehensive guide to the license management system implemented in MediNext. The system provides robust license validation, usage tracking, feature management, and administrative controls through Nova.

## System Architecture

### Core Components

1. **License Model** (`app/Models/License.php`)
   - Comprehensive license data structure
   - Usage tracking and validation logic
   - Feature management capabilities
   - Audit trail functionality

2. **License Service** (`app/Services/LicenseService.php`)
   - Business logic for license operations
   - Validation and activation processes
   - Usage tracking and limits
   - Statistics and reporting

3. **License Validation Middleware** (`app/Http/Middleware/LicenseValidation.php`)
   - Request-level license validation
   - Feature-based access control
   - Graceful error handling

4. **Nova Integration**
   - License management interface
   - Dashboard metrics and monitoring
   - Administrative controls

5. **API Endpoints** (`app/Http/Controllers/Api/LicenseController.php`)
   - RESTful license management
   - Usage tracking endpoints
   - Status and validation APIs

## Database Schema

### Licenses Table

The `licenses` table includes comprehensive fields for:

- **License Information**: Key, type, status, name, description
- **Customer Information**: Name, email, company, phone
- **Usage Limits**: Users, clinics, patients, appointments
- **Validity Period**: Start date, expiration, grace period
- **Server Information**: Domain, IP, fingerprint for security
- **Billing Information**: Fees, cycles, payment tracking
- **Usage Tracking**: Current usage counters
- **Security**: Activation codes, validation tracking
- **Support**: Support level and agent assignment
- **Audit Trail**: Complete change history

## License Types and Features

### Standard License
- Basic appointments
- Patient management
- Prescription management
- Basic reporting

### Premium License
- All Standard features
- Advanced reporting
- Lab results
- Medrep management
- Multi-clinic support
- Email notifications

### Enterprise License
- All Premium features
- SMS notifications
- API access
- Custom branding
- Priority support
- Advanced analytics
- Backup/restore

## Usage Tracking

The system tracks usage for:
- **Users**: Current active users vs. limit
- **Clinics**: Number of clinics vs. limit
- **Patients**: Total patients vs. limit
- **Appointments**: Monthly appointments vs. limit

Usage is automatically incremented/decremented through the service methods and can be monitored through the Nova dashboard.

## API Endpoints

### License Status and Information
```
GET /api/v1/license/status - Get current license status
GET /api/v1/license/info - Get detailed license information
```

### License Validation and Activation
```
POST /api/v1/license/validate - Validate license key
POST /api/v1/license/activate - Activate license with code
```

### Feature Management
```
GET /api/v1/license/feature/{feature} - Check feature availability
```

### Usage Management
```
GET /api/v1/license/usage/check - Check usage limits
GET /api/v1/license/usage - Get usage statistics
POST /api/v1/license/usage/increment - Increment usage counter
POST /api/v1/license/usage/decrement - Decrement usage counter
POST /api/v1/license/usage/reset-monthly - Reset monthly counters
```

### Administrative
```
GET /api/v1/license/statistics - Get license statistics (admin)
GET /api/v1/license/expiring - Get expiring licenses (admin)
```

## Nova Integration

### License Resource
- Complete CRUD operations for licenses
- Advanced filtering and search
- Bulk operations support
- Audit trail viewing

### Dashboard Metrics
- **License Status**: Current license status with color coding
- **User Usage**: Usage percentage with warning thresholds
- **Days Until Expiration**: Expiration countdown with alerts

### Menu Integration
- License management in System Management section
- Easy access to all license operations

## Middleware Usage

### Basic License Validation
```php
Route::middleware(['license'])->group(function () {
    // Routes that require valid license
});
```

### Feature-Specific Validation
```php
Route::middleware(['license:advanced_reporting'])->group(function () {
    // Routes that require specific feature
});
```

## Service Usage Examples

### Check License Status
```php
$licenseService = app(LicenseService::class);
$status = $licenseService->getLicenseStatus();
```

### Validate Feature Access
```php
if ($licenseService->hasFeature('advanced_reporting')) {
    // Show advanced reporting features
}
```

### Check Usage Limits
```php
$usage = $licenseService->checkUsageLimit('users');
if (!$usage['allowed']) {
    // Handle usage limit exceeded
}
```

### Increment Usage
```php
$licenseService->incrementUsage('users', 1);
```

## Security Features

### Server Fingerprinting
- Unique server identification
- Prevents license sharing
- Automatic fingerprint generation

### Activation Codes
- Unique activation codes per license
- One-time activation process
- Server binding on activation

### Audit Trail
- Complete change history
- User attribution
- Timestamp tracking
- Action logging

## Grace Period Management

- Configurable grace period after expiration
- Graceful degradation during grace period
- Clear expiration warnings
- Automatic status updates

## Error Handling

### License Validation Failures
- Clear error messages
- Appropriate HTTP status codes
- Graceful fallbacks
- User-friendly notifications

### Usage Limit Exceeded
- Real-time limit checking
- Proactive warnings
- Usage statistics display
- Upgrade prompts

## Monitoring and Alerts

### Nova Dashboard
- Real-time license status
- Usage monitoring
- Expiration alerts
- Performance metrics

### API Monitoring
- License validation attempts
- Usage tracking
- Error logging
- Performance metrics

## Best Practices

### License Management
1. Always validate licenses before critical operations
2. Use feature flags for license-specific functionality
3. Implement graceful degradation for expired licenses
4. Monitor usage patterns and limits

### Security
1. Never expose license keys in client-side code
2. Use server-side validation for all license checks
3. Implement proper audit logging
4. Regular security reviews

### Performance
1. Cache license information appropriately
2. Use efficient database queries
3. Implement proper indexing
4. Monitor performance metrics

## Troubleshooting

### Common Issues

1. **License Not Found**
   - Check license key format
   - Verify database connection
   - Check cache status

2. **Activation Failed**
   - Verify activation code
   - Check server fingerprint
   - Review activation logs

3. **Usage Limit Exceeded**
   - Check current usage counters
   - Verify limit configuration
   - Review usage increment logic

4. **Feature Not Available**
   - Check license type
   - Verify feature configuration
   - Review license status

### Debug Commands
```bash
# Check license status
php artisan tinker
>>> app(\App\Services\LicenseService::class)->getLicenseStatus()

# Validate license
>>> app(\App\Services\LicenseService::class)->validateLicense('LICENSE_KEY')

# Check feature availability
>>> app(\App\Services\LicenseService::class)->hasFeature('feature_name')
```

## Demo License

A demo license has been created with the following details:
- **License Key**: MEDI-VN1G-AFW8-GEQP-MIKD
- **Activation Code**: KS0NMWVN
- **Type**: Premium
- **Expires**: 1 year from creation
- **Features**: All premium features enabled

## Future Enhancements

### Planned Features
1. **License Renewal Automation**
   - Automatic renewal processes
   - Payment integration
   - Renewal notifications

2. **Advanced Analytics**
   - Usage pattern analysis
   - Performance metrics
   - Predictive analytics

3. **Multi-Tenant Support**
   - Multiple license management
   - Tenant isolation
   - Resource allocation

4. **API Rate Limiting**
   - License-based rate limits
   - Usage-based throttling
   - Fair usage policies

## Support and Maintenance

### Regular Tasks
1. Monitor license expirations
2. Review usage patterns
3. Update feature configurations
4. Perform security audits

### Maintenance Commands
```bash
# Reset monthly usage counters
php artisan license:reset-usage

# Check expiring licenses
php artisan license:check-expiring

# Validate all licenses
php artisan license:validate-all
```

## Conclusion

The license management system provides a robust, scalable solution for managing software licenses in the MediNext application. It includes comprehensive validation, usage tracking, feature management, and administrative controls through Nova.

The system is designed to be secure, performant, and user-friendly while providing the flexibility needed for different license types and usage patterns.

