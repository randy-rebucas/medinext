# MediNext Licensing Flow Documentation

## Overview

This document outlines the complete licensing flow for the MediNext application, from license creation to validation and usage tracking. The flow is designed to be secure, scalable, and user-friendly.

## 1. License Creation Flow

### 1.1 Admin License Creation (Nova)

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Admin User    │───▶│   Nova Admin    │───▶│ License Created │
│                 │    │   Interface     │    │                 │
└─────────────────┘    └─────────────────┘    └─────────────────┘
                              │
                              ▼
                       ┌─────────────────┐
                       │ License Service │
                       │   - Generate    │
                       │   - Validate    │
                       │   - Store       │
                       └─────────────────┘
```

**Process:**
1. Admin accesses Nova License Resource
2. Creates new license with customer details
3. System auto-generates license key and activation code
4. License stored in database with 'active' status
5. Customer receives license details via email

**Generated Data:**
- License Key: `MEDI-XXXX-XXXX-XXXX-XXXX`
- Activation Code: `XXXXXXXX`
- UUID for internal tracking
- Server fingerprint (generated on activation)

### 1.2 License Types and Features

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Standard      │    │    Premium      │    │   Enterprise    │
│                 │    │                 │    │                 │
│ • Basic Apps    │    │ • All Standard  │    │ • All Premium   │
│ • Patient Mgmt  │    │ • Lab Results   │    │ • SMS Notify    │
│ • Prescriptions │    │ • Multi-Clinic  │    │ • API Access    │
│ • Basic Reports │    │ • Email Notify  │    │ • Custom Brand  │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

## 2. License Activation Flow

### 2.1 Customer Activation Process

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Customer      │───▶│  Activation     │───▶│ License Service │
│   Receives      │    │  Request        │    │                 │
│   License       │    │                 │    │                 │
└─────────────────┘    └─────────────────┘    └─────────────────┘
                              │
                              ▼
                       ┌─────────────────┐
                       │ Validation      │
                       │ • Key Valid     │
                       │ • Code Correct  │
                       │ • Not Activated │
                       └─────────────────┘
                              │
                              ▼
                       ┌─────────────────┐
                       │ Server Binding  │
                       │ • Domain        │
                       │ • IP Address    │
                       │ • Fingerprint   │
                       └─────────────────┘
```

**API Endpoint:** `POST /api/v1/license/activate`

**Request:**
```json
{
    "license_key": "MEDI-XXXX-XXXX-XXXX-XXXX",
    "activation_code": "XXXXXXXX"
}
```

**Response (Success):**
```json
{
    "success": true,
    "message": "License activated successfully",
    "data": {
        "license": {
            "id": 1,
            "license_key": "MEDI-XXXX-XXXX-XXXX-XXXX",
            "status": "active",
            "activated_at": "2025-01-06T10:30:00Z"
        }
    }
}
```

**Response (Error):**
```json
{
    "success": false,
    "message": "Invalid activation code",
    "error_code": "INVALID_ACTIVATION_CODE"
}
```

## 3. License Validation Flow

### 3.1 Request-Level Validation

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   User Request  │───▶│ License         │───▶│ License Service │
│                 │    │ Middleware      │    │                 │
└─────────────────┘    └─────────────────┘    └─────────────────┘
                              │
                              ▼
                       ┌─────────────────┐
                       │ Validation      │
                       │ • Status Check  │
                       │ • Expiry Check  │
                       │ • Grace Period  │
                       └─────────────────┘
                              │
                              ▼
                       ┌─────────────────┐
                       │ Feature Check   │
                       │ (if specified)  │
                       └─────────────────┘
```

**Middleware Usage:**
```php
// Basic license validation
Route::middleware(['license'])->group(function () {
    // Protected routes
});

// Feature-specific validation
Route::middleware(['license:advanced_reporting'])->group(function () {
    // Advanced reporting routes
});
```

### 3.2 Validation Logic

```
┌─────────────────┐
│ License Status  │
│ Check           │
└─────────────────┘
         │
         ▼
┌─────────────────┐    ┌─────────────────┐
│ Status = Active │    │ Status ≠ Active │
└─────────────────┘    └─────────────────┘
         │                       │
         ▼                       ▼
┌─────────────────┐    ┌─────────────────┐
│ Expiry Check    │    │ Return Error    │
└─────────────────┘    └─────────────────┘
         │
         ▼
┌─────────────────┐    ┌─────────────────┐
│ Not Expired     │    │ Expired         │
└─────────────────┘    └─────────────────┘
         │                       │
         ▼                       ▼
┌─────────────────┐    ┌─────────────────┐
│ Grace Period    │    │ Check Grace     │
│ Check           │    │ Period          │
└─────────────────┘    └─────────────────┘
         │                       │
         ▼                       ▼
┌─────────────────┐    ┌─────────────────┐
│ Allow Access    │    │ Allow/Deny      │
└─────────────────┘    └─────────────────┘
```

## 4. Usage Tracking Flow

### 4.1 Usage Increment Flow

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   User Action   │───▶│ License Service │───▶│ Usage Check     │
│   (Create User) │    │                 │    │                 │
└─────────────────┘    └─────────────────┘    └─────────────────┘
                              │
                              ▼
                       ┌─────────────────┐
                       │ Limit Check     │
                       │ • Current < Max │
                       │ • Allow Action  │
                       └─────────────────┘
                              │
                              ▼
                       ┌─────────────────┐
                       │ Increment       │
                       │ Usage Counter   │
                       └─────────────────┘
```

**API Endpoint:** `POST /api/v1/license/usage/increment`

**Request:**
```json
{
    "type": "users",
    "amount": 1
}
```

**Usage Types:**
- `users` - Active users
- `clinics` - Number of clinics
- `patients` - Total patients
- `appointments` - Monthly appointments

### 4.2 Usage Monitoring

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Nova          │───▶│ License Service │───▶│ Usage Data      │
│   Dashboard     │    │                 │    │                 │
└─────────────────┘    └─────────────────┘    └─────────────────┘
                              │
                              ▼
                       ┌─────────────────┐
                       │ Calculate       │
                       │ Percentages     │
                       └─────────────────┘
                              │
                              ▼
                       ┌─────────────────┐
                       │ Display Metrics │
                       │ • Progress Bars │
                       │ • Color Coding  │
                       └─────────────────┘
```

## 5. Feature Access Flow

### 5.1 Feature Validation

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Feature       │───▶│ License Service │───▶│ Feature Check   │
│   Request       │    │                 │    │                 │
└─────────────────┘    └─────────────────┘    └─────────────────┘
                              │
                              ▼
                       ┌─────────────────┐
                       │ License Type    │
                       │ Feature List    │
                       └─────────────────┘
                              │
                              ▼
                       ┌─────────────────┐    ┌─────────────────┐
                       │ Feature         │    │ Feature         │
                       │ Available       │    │ Not Available   │
                       └─────────────────┘    └─────────────────┘
                              │                       │
                              ▼                       ▼
                       ┌─────────────────┐    ┌─────────────────┐
                       │ Allow Access    │    │ Return Error    │
                       └─────────────────┘    └─────────────────┘
```

**API Endpoint:** `GET /api/v1/license/feature/{feature}`

**Response:**
```json
{
    "success": true,
    "data": {
        "feature": "advanced_reporting",
        "available": true
    }
}
```

## 6. License Expiration Flow

### 6.1 Expiration Monitoring

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Daily         │───▶│ License Service │───▶│ Check Expiring  │
│   Cron Job      │    │                 │    │ Licenses        │
└─────────────────┘    └─────────────────┘    └─────────────────┘
                              │
                              ▼
                       ┌─────────────────┐
                       │ Expiry Status   │
                       │ • Active        │
                       │ • Expiring Soon │
                       │ • Expired       │
                       │ • Grace Period  │
                       └─────────────────┘
```

### 6.2 Grace Period Handling

```
┌─────────────────┐
│ License Expired │
└─────────────────┘
         │
         ▼
┌─────────────────┐    ┌─────────────────┐
│ Within Grace    │    │ Beyond Grace    │
│ Period          │    │ Period          │
└─────────────────┘    └─────────────────┘
         │                       │
         ▼                       ▼
┌─────────────────┐    ┌─────────────────┐
│ Allow Access    │    │ Deny Access     │
│ with Warning    │    │ Completely      │
└─────────────────┘    └─────────────────┘
```

## 7. License Renewal Flow

### 7.1 Renewal Process

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Admin         │───▶│   Nova          │───▶│ License Service │
│   Initiates     │    │   Interface     │    │                 │
│   Renewal       │    │                 │    │                 │
└─────────────────┘    └─────────────────┘    └─────────────────┘
                              │
                              ▼
                       ┌─────────────────┐
                       │ Update Expiry   │
                       │ Date            │
                       └─────────────────┘
                              │
                              ▼
                       ┌─────────────────┐
                       │ Add Audit Log   │
                       │ Entry           │
                       └─────────────────┘
```

**Renewal API:**
```php
$licenseService->renewLicense($license, 12); // 12 months
```

## 8. Error Handling Flow

### 8.1 License Validation Errors

```
┌─────────────────┐
│ Validation      │
│ Error           │
└─────────────────┘
         │
         ▼
┌─────────────────┐
│ Error Type      │
│ Detection       │
└─────────────────┘
         │
         ▼
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│ License Not     │    │ License         │    │ Feature Not     │
│ Found           │    │ Expired         │    │ Available       │
└─────────────────┘    └─────────────────┘    └─────────────────┘
         │                       │                       │
         ▼                       ▼                       ▼
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│ 404 Error       │    │ 403 Error       │    │ 403 Error       │
│ + Message       │    │ + Grace Info    │    │ + Feature Info  │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

### 8.2 Usage Limit Errors

```
┌─────────────────┐
│ Usage Limit     │
│ Exceeded        │
└─────────────────┘
         │
         ▼
┌─────────────────┐
│ Check Limit     │
│ Type            │
└─────────────────┘
         │
         ▼
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│ Users Limit     │    │ Clinics Limit   │    │ Patients Limit  │
└─────────────────┘    └─────────────────┘    └─────────────────┘
         │                       │                       │
         ▼                       ▼                       ▼
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│ 403 Error       │    │ 403 Error       │    │ 403 Error       │
│ + Usage Info    │    │ + Usage Info    │    │ + Usage Info    │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

## 9. Nova Dashboard Flow

### 9.1 License Management

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Admin         │───▶│   Nova          │───▶│ License         │
│   Access        │    │   Dashboard     │    │ Resource        │
└─────────────────┘    └─────────────────┘    └─────────────────┘
                              │
                              ▼
                       ┌─────────────────┐
                       │ License List    │
                       │ • Search        │
                       │ • Filter        │
                       │ • Sort          │
                       └─────────────────┘
                              │
                              ▼
                       ┌─────────────────┐
                       │ License Actions │
                       │ • Create        │
                       │ • Edit          │
                       │ • Suspend       │
                       │ • Renew         │
                       └─────────────────┘
```

### 9.2 Dashboard Metrics

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   License       │    │   Usage         │    │   Expiration    │
│   Status        │    │   Metrics       │    │   Countdown     │
└─────────────────┘    └─────────────────┘    └─────────────────┘
         │                       │                       │
         ▼                       ▼                       ▼
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│ Color-coded     │    │ Progress Bars   │    │ Days Remaining  │
│ Status Badge    │    │ with Percentages│    │ with Alerts     │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

## 10. API Integration Flow

### 10.1 External System Integration

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   External      │───▶│   API Gateway   │───▶│ License         │
│   System        │    │                 │    │ Controller      │
└─────────────────┘    └─────────────────┘    └─────────────────┘
                              │
                              ▼
                       ┌─────────────────┐
                       │ Authentication  │
                       │ & Authorization │
                       └─────────────────┘
                              │
                              ▼
                       ┌─────────────────┐
                       │ License Service │
                       │ Validation      │
                       └─────────────────┘
```

### 10.2 Webhook Integration

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   License       │───▶│   Webhook       │───▶│   External      │
│   Event         │    │   Endpoint      │    │   System        │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

**Webhook Events:**
- License activated
- License expired
- Usage limit exceeded
- License renewed
- License suspended

## 11. Security Flow

### 11.1 Server Fingerprinting

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Server        │───▶│   Fingerprint   │───▶│   License       │
│   Information   │    │   Generation    │    │   Binding       │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

**Fingerprint Components:**
- Server domain
- IP address
- User agent
- Server name
- Hardware identifiers

### 11.2 Activation Security

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   License Key   │───▶│   Validation    │───▶│   Activation    │
│   + Code        │    │   Process       │    │   Binding       │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

## 12. Monitoring and Alerting Flow

### 12.1 Real-time Monitoring

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   License       │───▶│   Monitoring    │───▶│   Alert         │
│   Events        │    │   Service       │    │   System        │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

**Alert Types:**
- License expiring soon (30, 7, 1 days)
- Usage approaching limits (90%, 95%)
- License expired
- Usage limit exceeded
- Suspicious activity

### 12.2 Reporting Flow

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   License       │───▶│   Report        │───▶│   Admin         │
│   Data          │    │   Generation    │    │   Dashboard     │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

## Implementation Examples

### Frontend Integration

```javascript
// Check license status
const checkLicense = async () => {
    const response = await fetch('/api/v1/license/status');
    const data = await response.json();
    
    if (!data.data.has_license) {
        showLicenseError('No valid license found');
        return false;
    }
    
    if (data.data.status === 'expired') {
        showLicenseError('License has expired');
        return false;
    }
    
    return true;
};

// Check feature availability
const hasFeature = async (feature) => {
    const response = await fetch(`/api/v1/license/feature/${feature}`);
    const data = await response.json();
    return data.data.available;
};

// Usage tracking
const trackUsage = async (type, amount = 1) => {
    await fetch('/api/v1/license/usage/increment', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ type, amount })
    });
};
```

### Backend Integration

```php
// In your controllers
public function createUser(Request $request)
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

// Feature checking
public function advancedReports()
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

## Conclusion

This licensing flow provides a comprehensive, secure, and scalable solution for managing software licenses in the MediNext application. The flow covers all aspects from license creation to usage tracking, with proper error handling and security measures throughout.

The system is designed to be:
- **Secure**: Server fingerprinting and activation codes
- **Scalable**: Efficient database design and caching
- **User-friendly**: Clear error messages and status indicators
- **Administrative**: Complete Nova integration for management
- **API-driven**: Full REST API for external integrations
- **Monitorable**: Real-time metrics and alerting

All components work together to provide a robust licensing system that can handle various license types, usage patterns, and business requirements.
