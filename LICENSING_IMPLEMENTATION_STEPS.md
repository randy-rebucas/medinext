# Licensing System Implementation Steps

## Quick Start Guide

This guide provides step-by-step instructions for implementing and using the licensing system in your MediNext application.

## 1. System Setup (Already Completed)

### ✅ Database Migration
```bash
php artisan migrate
```
The `licenses` table has been created with all necessary fields.

### ✅ Demo License Created
```bash
php artisan db:seed --class=LicenseSeeder
```
A demo license has been created with:
- **License Key**: `MEDI-VN1G-AFW8-GEQP-MIKD`
- **Activation Code**: `KS0NMWVN`
- **Type**: Premium
- **Expires**: 1 year from creation

## 2. License Activation Process

### Step 1: Access License Activation
Navigate to your application and look for the license activation section, or use the API directly.

### Step 2: Activate License via API
```bash
curl -X POST http://localhost:8000/api/v1/license/activate \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "license_key": "MEDI-VN1G-AFW8-GEQP-MIKD",
    "activation_code": "KS0NMWVN"
  }'
```

### Step 3: Verify Activation
```bash
curl -X GET http://localhost:8000/api/v1/license/status \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## 3. Nova Admin Setup

### Step 1: Access Nova Dashboard
Navigate to `/nova` in your browser and log in with admin credentials.

### Step 2: View License Management
1. Go to **System Management** → **Licenses**
2. You should see the demo license created
3. Click on the license to view details

### Step 3: Monitor License Metrics
1. Go to the main dashboard
2. View the license status, usage, and expiration metrics
3. These update in real-time

## 4. Implementing License Validation

### Step 1: Add Middleware to Routes

```php
// routes/web.php
Route::middleware(['license'])->group(function () {
    // Protected routes that require valid license
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/patients', [PatientController::class, 'index']);
    Route::get('/appointments', [AppointmentController::class, 'index']);
});

// Feature-specific validation
Route::middleware(['license:advanced_reporting'])->group(function () {
    Route::get('/reports/advanced', [ReportController::class, 'advanced']);
    Route::get('/reports/analytics', [ReportController::class, 'analytics']);
});
```

### Step 2: Register Middleware

```php
// app/Http/Kernel.php
protected $middlewareAliases = [
    // ... existing middleware
    'license' => \App\Http\Middleware\LicenseValidation::class,
];
```

### Step 3: Test License Validation
1. Try accessing protected routes
2. Check that license validation works
3. Verify error messages for invalid licenses

## 5. Usage Tracking Implementation

### Step 1: Track User Creation

```php
// app/Http/Controllers/UserController.php
public function store(Request $request)
{
    $licenseService = app(LicenseService::class);
    
    // Check usage limits
    $usage = $licenseService->checkUsageLimit('users');
    if (!$usage['allowed']) {
        return response()->json([
            'error' => 'User limit exceeded',
            'message' => $usage['message']
        ], 403);
    }
    
    // Create user
    $user = User::create($request->validated());
    
    // Increment usage
    $licenseService->incrementUsage('users', 1);
    
    return response()->json($user, 201);
}
```

### Step 2: Track Other Resources

```php
// For clinics
$licenseService->incrementUsage('clinics', 1);

// For patients
$licenseService->incrementUsage('patients', 1);

// For appointments
$licenseService->incrementUsage('appointments', 1);
```

### Step 3: Monitor Usage in Nova
1. Go to License details in Nova
2. View usage progress bars
3. Monitor percentage usage

## 6. Feature-Based Access Control

### Step 1: Check Features in Controllers

```php
// app/Http/Controllers/ReportController.php
public function advanced()
{
    $licenseService = app(LicenseService::class);
    
    if (!$licenseService->hasFeature('advanced_reporting')) {
        return response()->json([
            'error' => 'Feature not available',
            'message' => 'Advanced reporting is not available in your license'
        ], 403);
    }
    
    // Generate advanced reports
    return $this->generateAdvancedReports();
}
```

### Step 2: Frontend Feature Checks

```javascript
// Check if feature is available
const hasAdvancedReporting = await fetch('/api/v1/license/feature/advanced_reporting')
    .then(response => response.json())
    .then(data => data.data.available);

if (hasAdvancedReporting) {
    // Show advanced reporting features
    showAdvancedReports();
} else {
    // Hide or disable features
    hideAdvancedReports();
}
```

## 7. License Management in Nova

### Step 1: Create New License

1. Go to **System Management** → **Licenses**
2. Click **Create License**
3. Fill in customer details:
   - Customer Name
   - Customer Email
   - License Type (Standard/Premium/Enterprise)
   - Usage Limits
   - Expiration Date
4. Click **Create**
5. Note the generated License Key and Activation Code

### Step 2: Manage Existing Licenses

1. **View License Details**: Click on any license to see full details
2. **Edit License**: Modify license settings, limits, or features
3. **Suspend License**: Temporarily disable a license
4. **Renew License**: Extend expiration date
5. **View Audit Log**: See all changes made to the license

### Step 3: Monitor License Health

1. **Dashboard Metrics**: View real-time license status
2. **Usage Monitoring**: Track usage percentages
3. **Expiration Alerts**: Monitor days until expiration
4. **Statistics**: View license statistics and revenue

## 8. API Integration

### Step 1: License Status API

```javascript
// Get current license status
const getLicenseStatus = async () => {
    const response = await fetch('/api/v1/license/status');
    const data = await response.json();
    return data.data;
};

// Usage example
const status = await getLicenseStatus();
console.log('License Status:', status.status);
console.log('Days until expiration:', status.license.days_until_expiration);
```

### Step 2: Usage Tracking API

```javascript
// Increment usage when creating resources
const trackUsage = async (type, amount = 1) => {
    await fetch('/api/v1/license/usage/increment', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ type, amount })
    });
};

// Usage example
await trackUsage('users', 1); // When creating a user
await trackUsage('patients', 1); // When creating a patient
```

### Step 3: Feature Checking API

```javascript
// Check if feature is available
const checkFeature = async (feature) => {
    const response = await fetch(`/api/v1/license/feature/${feature}`);
    const data = await response.json();
    return data.data.available;
};

// Usage example
const hasAdvancedReports = await checkFeature('advanced_reporting');
if (hasAdvancedReports) {
    // Enable advanced reporting features
}
```

## 9. Error Handling

### Step 1: Handle License Errors

```javascript
// Global license error handler
const handleLicenseError = (error) => {
    switch (error.error_code) {
        case 'LICENSE_NOT_FOUND':
            showError('No valid license found. Please contact support.');
            break;
        case 'LICENSE_EXPIRED':
            showError('Your license has expired. Please renew to continue.');
            break;
        case 'FEATURE_NOT_AVAILABLE':
            showError(`Feature '${error.feature}' is not available in your license.`);
            break;
        case 'USAGE_LIMIT_EXCEEDED':
            showError(`Usage limit exceeded for ${error.type}.`);
            break;
        default:
            showError('License validation failed. Please contact support.');
    }
};
```

### Step 2: Graceful Degradation

```javascript
// Check license before showing features
const initializeApp = async () => {
    try {
        const licenseStatus = await getLicenseStatus();
        
        if (!licenseStatus.has_license) {
            showLicenseError();
            return;
        }
        
        if (licenseStatus.status === 'expired') {
            showExpirationWarning(licenseStatus.license.days_until_expiration);
        }
        
        // Initialize app with available features
        initializeFeatures(licenseStatus.license.features);
        
    } catch (error) {
        console.error('Failed to check license:', error);
        showLicenseError();
    }
};
```

## 10. Monitoring and Maintenance

### Step 1: Set Up Monitoring

```bash
# Add to crontab for daily license checks
0 9 * * * cd /path/to/your/app && php artisan license:check-expiring

# Add to crontab for usage monitoring
0 */6 * * * cd /path/to/your/app && php artisan license:monitor-usage
```

### Step 2: Regular Maintenance Tasks

1. **Daily**: Check for expiring licenses
2. **Weekly**: Review usage patterns
3. **Monthly**: Reset usage counters
4. **Quarterly**: Review license performance

### Step 3: Backup and Recovery

```bash
# Backup license data
mysqldump -u username -p database_name licenses > licenses_backup.sql

# Restore license data
mysql -u username -p database_name < licenses_backup.sql
```

## 11. Testing the Implementation

### Step 1: Test License Activation

```bash
# Test with valid license
curl -X POST http://localhost:8000/api/v1/license/activate \
  -H "Content-Type: application/json" \
  -d '{
    "license_key": "MEDI-VN1G-AFW8-GEQP-MIKD",
    "activation_code": "KS0NMWVN"
  }'

# Expected: Success response with activated license
```

### Step 2: Test License Validation

```bash
# Test license status
curl -X GET http://localhost:8000/api/v1/license/status

# Expected: License status and usage information
```

### Step 3: Test Feature Access

```bash
# Test feature availability
curl -X GET http://localhost:8000/api/v1/license/feature/advanced_reporting

# Expected: Feature availability status
```

### Step 4: Test Usage Tracking

```bash
# Test usage increment
curl -X POST http://localhost:8000/api/v1/license/usage/increment \
  -H "Content-Type: application/json" \
  -d '{"type": "users", "amount": 1}'

# Expected: Usage incremented successfully
```

## 12. Troubleshooting

### Common Issues and Solutions

#### Issue: License Not Found
**Solution**: 
1. Check if license exists in database
2. Verify license key format
3. Check database connection

#### Issue: Activation Failed
**Solution**:
1. Verify activation code
2. Check if license already activated
3. Review server fingerprint

#### Issue: Feature Not Available
**Solution**:
1. Check license type
2. Verify feature configuration
3. Review license status

#### Issue: Usage Limit Exceeded
**Solution**:
1. Check current usage counters
2. Verify limit configuration
3. Review usage increment logic

### Debug Commands

```bash
# Check license status in tinker
php artisan tinker
>>> app(\App\Services\LicenseService::class)->getLicenseStatus()

# Validate specific license
>>> app(\App\Services\LicenseService::class)->validateLicense('LICENSE_KEY')

# Check feature availability
>>> app(\App\Services\LicenseService::class)->hasFeature('feature_name')
```

## 13. Production Deployment

### Step 1: Environment Configuration

```env
# .env
LICENSE_VALIDATION_ENABLED=true
LICENSE_CACHE_TTL=3600
LICENSE_GRACE_PERIOD_DAYS=7
```

### Step 2: Security Considerations

1. **Never expose license keys** in client-side code
2. **Use HTTPS** for all license API calls
3. **Implement rate limiting** on license endpoints
4. **Regular security audits** of license system

### Step 3: Performance Optimization

1. **Enable caching** for license data
2. **Use database indexes** for license queries
3. **Implement connection pooling** for high traffic
4. **Monitor performance metrics**

## 14. Support and Maintenance

### Regular Tasks

1. **Daily**: Monitor license expirations
2. **Weekly**: Review usage patterns and limits
3. **Monthly**: Reset usage counters and generate reports
4. **Quarterly**: Review license performance and optimization

### Support Resources

1. **Documentation**: LICENSE_MANAGEMENT_IMPLEMENTATION_GUIDE.md
2. **API Reference**: LICENSE_FLOW_DOCUMENTATION.md
3. **Examples**: LICENSE_FLOW_EXAMPLES.md
4. **Nova Interface**: Access through /nova

## Conclusion

The licensing system is now fully implemented and ready for use. Follow these steps to activate your license, implement validation, and start managing your software licenses effectively through Nova.

For additional support or customization, refer to the comprehensive documentation files created as part of this implementation.
