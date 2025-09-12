# Licensing Routes and Middleware Fine-Tuning Guide

## Overview

This document provides a comprehensive guide to the fine-tuned licensing routes and middleware implementation for the MediNext application. The system now includes specialized middleware for different types of license validation and organized route structures.

## Middleware Architecture

### 1. Core License Middleware

#### `license` - Basic License Validation
- **Purpose**: Validates basic license status (active, expired, grace period)
- **Usage**: Applied to routes that require a valid license
- **Features**:
  - License status validation
  - Expiration checking
  - Grace period handling
  - Automatic error responses

```php
Route::middleware(['license'])->group(function () {
    Route::get('/reports/appointments', [AppointmentController::class, 'reports']);
    Route::get('/reports/prescriptions', [PrescriptionController::class, 'reports']);
});
```

#### `license.feature` - Feature-Specific Validation
- **Purpose**: Validates access to specific license features
- **Usage**: Applied to routes that require specific features
- **Features**:
  - Feature availability checking
  - User-friendly error messages
  - Upgrade prompts
  - Feature display names

```php
Route::middleware(['license.feature:advanced_reporting'])->group(function () {
    Route::get('/reports/advanced', [AppointmentController::class, 'advancedReports']);
    Route::get('/reports/analytics', [DashboardController::class, 'analytics']);
});
```

#### `license.usage` - Usage Limit Validation
- **Purpose**: Validates usage limits before creating resources
- **Usage**: Applied to resource creation routes
- **Features**:
  - Usage limit checking
  - Real-time validation
  - Detailed error messages
  - Usage statistics

```php
Route::middleware(['license.usage:users'])->group(function () {
    Route::post('/doctors', [DoctorController::class, 'store']);
});
```

## Route Organization

### 1. License API Routes (`routes/license.php`)

```php
Route::prefix('v1/license')->group(function () {
    
    // Public routes (no authentication required)
    Route::post('/activate', [LicenseController::class, 'activate']);
    Route::post('/validate', [LicenseController::class, 'validate']);

    // Protected routes (authentication required)
    Route::middleware(['api.auth'])->group(function () {
        
        // License status and information
        Route::get('/status', [LicenseController::class, 'status']);
        Route::get('/info', [LicenseController::class, 'info']);

        // Feature management
        Route::get('/feature/{feature}', [LicenseController::class, 'hasFeature']);

        // Usage management
        Route::prefix('usage')->group(function () {
            Route::get('/check', [LicenseController::class, 'checkUsage']);
            Route::get('/', [LicenseController::class, 'usage']);
            Route::post('/increment', [LicenseController::class, 'incrementUsage']);
            Route::post('/decrement', [LicenseController::class, 'decrementUsage']);
            Route::post('/reset-monthly', [LicenseController::class, 'resetMonthlyUsage']);
        });

        // Administrative routes (admin only)
        Route::middleware(['api.permission:admin'])->group(function () {
            Route::get('/statistics', [LicenseController::class, 'statistics']);
            Route::get('/expiring', [LicenseController::class, 'expiring']);
        });
    });
});
```

### 2. Main API Routes with License Integration

#### Resource Creation with Usage Validation

```php
// Patient management (with usage validation)
Route::middleware(['license.usage:patients'])->group(function () {
    Route::post('/patients', [PatientController::class, 'store']);
});

// Doctor management (with usage validation)
Route::middleware(['license.usage:users'])->group(function () {
    Route::post('/doctors', [DoctorController::class, 'store']);
});

// Clinic management (with usage validation)
Route::middleware(['license.usage:clinics'])->group(function () {
    Route::post('/clinics', [ClinicController::class, 'store']);
});

// Appointment management (with usage validation)
Route::middleware(['license.usage:appointments'])->group(function () {
    Route::post('/appointments', [AppointmentController::class, 'store']);
});
```

#### Feature-Specific Routes

```php
// Lab result management (requires lab_results feature)
Route::middleware(['license.feature:lab_results'])->group(function () {
    Route::apiResource('lab-results', LabResultController::class);
    Route::get('/lab-results/{labResult}/file-assets', [LabResultController::class, 'fileAssets']);
    Route::post('/lab-results/{labResult}/file-assets', [LabResultController::class, 'uploadFile']);
    Route::post('/lab-results/{labResult}/review', [LabResultController::class, 'review']);
    Route::get('/lab-results/pending', [LabResultController::class, 'pending']);
    Route::get('/lab-results/abnormal', [LabResultController::class, 'abnormal']);
});

// Medrep management (requires medrep_management feature)
Route::middleware(['license.feature:medrep_management'])->group(function () {
    Route::apiResource('medreps', MedrepController::class);
    Route::get('/medreps/{medrep}/visits', [MedrepController::class, 'visits']);
    Route::post('/medreps/{medrep}/visits', [MedrepController::class, 'scheduleVisit']);
    Route::put('/medreps/{medrep}/visits/{visit}', [MedrepController::class, 'updateVisit']);
    Route::delete('/medreps/{medrep}/visits/{visit}', [MedrepController::class, 'cancelVisit']);
    Route::get('/medrep-visits', [MedrepController::class, 'allVisits']);
    Route::get('/medrep-visits/upcoming', [MedrepController::class, 'upcomingVisits']);
});

// Advanced reporting (requires advanced_reporting feature)
Route::middleware(['license.feature:advanced_reporting'])->group(function () {
    Route::get('/reports/advanced', [AppointmentController::class, 'advancedReports']);
    Route::get('/reports/analytics', [DashboardController::class, 'analytics']);
    Route::get('/reports/export', [AppointmentController::class, 'exportReports']);
});
```

### 3. Web Routes for License Management

```php
// License activation page
Route::get('/license/activate', [LicenseWebController::class, 'showActivationForm'])
    ->name('license.activate.form');

Route::post('/license/activate', [LicenseWebController::class, 'activate'])
    ->name('license.activate');

// License error page
Route::get('/license/error', [LicenseWebController::class, 'error'])
    ->name('license.error');

// License status page
Route::get('/license/status', [LicenseWebController::class, 'status'])
    ->name('license.status')
    ->middleware(['auth']);

// License management (admin only)
Route::middleware(['auth', 'license'])->group(function () {
    Route::get('/license/manage', [LicenseWebController::class, 'manage'])
        ->name('license.manage');
    
    Route::get('/license/usage', [LicenseWebController::class, 'usage'])
        ->name('license.usage');
});
```

## Middleware Configuration

### Bootstrap Configuration (`bootstrap/app.php`)

```php
$middleware->alias([
    'api.auth' => \App\Http\Middleware\ApiAuth::class,
    'api.clinic' => \App\Http\Middleware\ApiClinicAccess::class,
    'api.permission' => \App\Http\Middleware\ApiPermission::class,
    'license' => \App\Http\Middleware\LicenseValidation::class,
    'license.feature' => \App\Http\Middleware\LicenseFeatureValidation::class,
    'license.usage' => \App\Http\Middleware\LicenseUsageValidation::class,
]);
```

### Route Registration

```php
->withRouting(
    web: __DIR__.'/../routes/web.php',
    api: __DIR__.'/../routes/api.php',
    commands: __DIR__.'/../routes/console.php',
    health: '/up',
    then: function () {
        Route::middleware('api')
            ->prefix('api')
            ->group(base_path('routes/license.php'));
    },
)
```

## Usage Examples

### 1. Basic License Validation

```php
// Apply to any route that requires a valid license
Route::middleware(['license'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/patients', [PatientController::class, 'index']);
});
```

### 2. Feature-Specific Validation

```php
// Apply to routes that require specific features
Route::middleware(['license.feature:advanced_reporting'])->group(function () {
    Route::get('/reports/advanced', [ReportController::class, 'advanced']);
    Route::get('/reports/analytics', [ReportController::class, 'analytics']);
});

Route::middleware(['license.feature:lab_results'])->group(function () {
    Route::apiResource('lab-results', LabResultController::class);
});
```

### 3. Usage Limit Validation

```php
// Apply to resource creation routes
Route::middleware(['license.usage:users'])->group(function () {
    Route::post('/users', [UserController::class, 'store']);
    Route::post('/doctors', [DoctorController::class, 'store']);
});

Route::middleware(['license.usage:patients'])->group(function () {
    Route::post('/patients', [PatientController::class, 'store']);
});
```

### 4. Combined Middleware

```php
// Combine multiple middleware for comprehensive validation
Route::middleware(['api.auth', 'license', 'license.feature:advanced_reporting'])->group(function () {
    Route::get('/reports/advanced', [ReportController::class, 'advanced']);
});
```

## Error Handling

### 1. License Validation Errors

```json
{
    "error": "License validation failed",
    "message": "Your license has expired. Please renew your license to continue using the application.",
    "error_code": "LICENSE_VALIDATION_FAILED"
}
```

### 2. Feature Not Available Errors

```json
{
    "error": "Feature not available",
    "message": "The feature 'Advanced Reporting' is not available in your current license. Please upgrade your license to access this feature.",
    "error_code": "FEATURE_NOT_AVAILABLE",
    "feature": "advanced_reporting",
    "feature_display_name": "Advanced Reporting",
    "current_license_type": "standard",
    "upgrade_required": true
}
```

### 3. Usage Limit Exceeded Errors

```json
{
    "error": "Usage limit exceeded",
    "message": "You have reached the maximum limit for users. Please upgrade your license to add more users.",
    "error_code": "USAGE_LIMIT_EXCEEDED",
    "usage_type": "users",
    "usage_display_name": "users",
    "current": 50,
    "limit": 50,
    "percentage": 100.0,
    "upgrade_required": true
}
```

## Security Features

### 1. Route Protection

- **Authentication Required**: All license routes require proper authentication
- **Admin-Only Routes**: Administrative routes require admin permissions
- **Feature Validation**: Routes are protected by feature availability
- **Usage Limits**: Resource creation is protected by usage limits

### 2. Error Handling

- **Graceful Degradation**: Clear error messages for different scenarios
- **User-Friendly Messages**: Human-readable error descriptions
- **Upgrade Prompts**: Clear guidance on license upgrades
- **Audit Logging**: All license validation attempts are logged

### 3. Performance Optimization

- **Caching**: License data is cached for performance
- **Efficient Queries**: Optimized database queries for license validation
- **Middleware Optimization**: Minimal overhead for license checks
- **Route Grouping**: Organized routes for better performance

## Testing

### 1. Middleware Testing

```php
// Test license validation middleware
public function test_license_middleware_blocks_expired_license()
{
    $license = License::factory()->create([
        'status' => 'active',
        'expires_at' => now()->subDays(10)
    ]);

    $response = $this->get('/api/v1/patients');

    $response->assertStatus(403)
             ->assertJson([
                 'error' => 'License validation failed',
                 'error_code' => 'LICENSE_VALIDATION_FAILED'
             ]);
}

// Test feature validation middleware
public function test_feature_middleware_blocks_unavailable_feature()
{
    $license = License::factory()->create([
        'license_type' => 'standard',
        'features' => ['basic_appointments', 'patient_management']
    ]);

    $response = $this->get('/api/v1/reports/advanced');

    $response->assertStatus(403)
             ->assertJson([
                 'error' => 'Feature not available',
                 'error_code' => 'FEATURE_NOT_AVAILABLE',
                 'feature' => 'advanced_reporting'
             ]);
}

// Test usage validation middleware
public function test_usage_middleware_blocks_limit_exceeded()
{
    $license = License::factory()->create([
        'max_users' => 5,
        'current_users' => 5
    ]);

    $response = $this->post('/api/v1/doctors', [
        'name' => 'Test Doctor',
        'email' => 'doctor@test.com'
    ]);

    $response->assertStatus(403)
             ->assertJson([
                 'error' => 'Usage limit exceeded',
                 'error_code' => 'USAGE_LIMIT_EXCEEDED',
                 'usage_type' => 'users'
             ]);
}
```

### 2. Route Testing

```php
// Test license API routes
public function test_license_status_endpoint()
{
    $response = $this->get('/api/v1/license/status');

    $response->assertStatus(200)
             ->assertJsonStructure([
                 'success',
                 'data' => [
                     'has_license',
                     'status',
                     'license'
                 ]
             ]);
}

// Test feature checking endpoint
public function test_feature_availability_endpoint()
{
    $response = $this->get('/api/v1/license/feature/advanced_reporting');

    $response->assertStatus(200)
             ->assertJsonStructure([
                 'success',
                 'data' => [
                     'feature',
                     'available'
                 ]
             ]);
}
```

## Best Practices

### 1. Middleware Usage

- **Use Specific Middleware**: Choose the most specific middleware for your needs
- **Combine When Necessary**: Use multiple middleware for comprehensive validation
- **Group Related Routes**: Organize routes with similar license requirements
- **Test Thoroughly**: Test all middleware combinations

### 2. Route Organization

- **Separate License Routes**: Keep license routes in dedicated files
- **Use Route Groups**: Group routes with similar middleware requirements
- **Name Routes Consistently**: Use consistent naming conventions
- **Document Routes**: Document complex route configurations

### 3. Error Handling

- **Provide Clear Messages**: Use user-friendly error messages
- **Include Upgrade Information**: Guide users on license upgrades
- **Log All Attempts**: Log all license validation attempts
- **Handle Edge Cases**: Handle all possible error scenarios

### 4. Performance

- **Cache License Data**: Use caching for frequently accessed license data
- **Optimize Queries**: Use efficient database queries
- **Minimize Middleware Overhead**: Keep middleware lightweight
- **Monitor Performance**: Monitor license validation performance

## Conclusion

The fine-tuned licensing routes and middleware system provides:

- **Comprehensive Protection**: All routes are properly protected by license validation
- **Flexible Validation**: Different types of validation for different needs
- **Clear Error Handling**: User-friendly error messages and upgrade prompts
- **Performance Optimization**: Efficient validation with minimal overhead
- **Easy Maintenance**: Well-organized routes and middleware for easy updates

This system ensures that your MediNext application is properly licensed and protected while providing a smooth user experience for valid license holders.
