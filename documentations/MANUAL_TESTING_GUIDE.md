# Manual Testing Guide for `/api/v1/license/user-access-status` Endpoint

## Overview
This guide provides instructions for manually testing the `/api/v1/license/user-access-status` endpoint to verify it works correctly in different scenarios.

## Prerequisites
- Laravel application running
- Database with users and licenses tables
- Sanctum authentication configured
- CSRF token available in meta tag

## Testing Scenarios

### 1. Test Authentication Requirements

#### Test 1.1: Unauthenticated Request
```bash
curl -X GET "http://your-app.test/api/v1/license/user-access-status" \
  -H "Content-Type: application/json"
```

**Expected Response:**
```json
{
  "success": false,
  "message": "Unauthenticated",
  "error_code": "UNAUTHENTICATED",
  "timestamp": "2025-09-08T20:59:50.000000Z"
}
```

#### Test 1.2: Inactive User
```bash
curl -X GET "http://your-app.test/api/v1/license/user-access-status" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**Expected Response:**
```json
{
  "success": false,
  "message": "Account is deactivated",
  "error_code": "ACCOUNT_DEACTIVATED",
  "timestamp": "2025-09-08T20:59:50.000000Z"
}
```

### 2. Test Licensed User

#### Setup
Create a user with an activated license:
```php
// In tinker or seeder
$license = License::create([
    'license_key' => 'TEST-LICENSE-123',
    'status' => 'active',
    'expires_at' => now()->addYear(),
    // ... other required fields
]);

$user = User::create([
    'name' => 'Test User',
    'email' => 'test@example.com',
    'license_key' => 'TEST-LICENSE-123',
    'has_activated_license' => true,
    'is_trial_user' => false,
    'is_active' => true,
    // ... other required fields
]);
```

#### Test Request
```bash
curl -X GET "http://your-app.test/api/v1/license/user-access-status" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**Expected Response:**
```json
{
  "success": true,
  "data": {
    "type": "licensed",
    "status": "active",
    "message": "Full access with license",
    "expires_at": "2026-09-08T20:59:50.000000Z"
  }
}
```

### 3. Test Trial User

#### Setup
Create a user with an active trial:
```php
$user = User::create([
    'name' => 'Trial User',
    'email' => 'trial@example.com',
    'trial_started_at' => now()->subDays(5),
    'trial_ends_at' => now()->addDays(9),
    'is_trial_user' => true,
    'has_activated_license' => false,
    'is_active' => true,
    // ... other required fields
]);
```

#### Test Request
```bash
curl -X GET "http://your-app.test/api/v1/license/user-access-status" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**Expected Response:**
```json
{
  "success": true,
  "data": {
    "type": "trial",
    "status": "active",
    "message": "Free trial active",
    "expires_at": "2025-09-17T20:59:50.000000Z",
    "days_remaining": 9
  }
}
```

### 4. Test Expired Trial User

#### Setup
Create a user with an expired trial:
```php
$user = User::create([
    'name' => 'Expired Trial User',
    'email' => 'expired@example.com',
    'trial_started_at' => now()->subDays(20),
    'trial_ends_at' => now()->subDays(6),
    'is_trial_user' => true,
    'has_activated_license' => false,
    'is_active' => true,
    // ... other required fields
]);
```

#### Test Request
```bash
curl -X GET "http://your-app.test/api/v1/license/user-access-status" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**Expected Response:**
```json
{
  "success": true,
  "data": {
    "type": "trial",
    "status": "expired",
    "message": "Free trial expired",
    "expires_at": "2025-09-02T20:59:50.000000Z",
    "days_expired": 6
  }
}
```

### 5. Test User with No Access

#### Setup
Create a user with no trial and no license:
```php
$user = User::create([
    'name' => 'No Access User',
    'email' => 'noaccess@example.com',
    'trial_started_at' => null,
    'trial_ends_at' => null,
    'is_trial_user' => false,
    'has_activated_license' => false,
    'is_active' => true,
    // ... other required fields
]);
```

#### Test Request
```bash
curl -X GET "http://your-app.test/api/v1/license/user-access-status" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**Expected Response:**
```json
{
  "success": true,
  "data": {
    "type": "none",
    "status": "inactive",
    "message": "No access"
  }
}
```

## Frontend Testing

### Using the React Hook
The endpoint is used by the `useUserAccessStatus` hook in the frontend:

```typescript
import { useUserAccessStatus } from './hooks/use-user-access-status';

function MyComponent() {
    const { accessStatus, loading, error, refetch } = useUserAccessStatus();

    if (loading) return <div>Loading...</div>;
    if (error) return <div>Error: {error}</div>;

    return (
        <div>
            <h3>Access Status</h3>
            <p>Type: {accessStatus?.type}</p>
            <p>Status: {accessStatus?.status}</p>
            <p>Message: {accessStatus?.message}</p>
            {accessStatus?.days_remaining && (
                <p>Days Remaining: {accessStatus.days_remaining}</p>
            )}
            {accessStatus?.days_expired && (
                <p>Days Expired: {accessStatus.days_expired}</p>
            )}
        </div>
    );
}
```

### Browser Testing
1. Open browser developer tools
2. Navigate to Network tab
3. Load a page that uses the hook
4. Check the API request to `/api/v1/license/user-access-status`
5. Verify the response matches expected format

## Error Scenarios

### Server Error
If the endpoint encounters an unexpected error, it should return:
```json
{
  "success": false,
  "message": "Failed to get user access status",
  "error": "Detailed error message"
}
```

### Network Error
If the request fails due to network issues, the frontend hook should handle it gracefully and show an error message.

## Validation Checklist

- [ ] Unauthenticated requests return 401
- [ ] Inactive users are rejected
- [ ] Licensed users get correct status
- [ ] Trial users get correct status with days remaining
- [ ] Expired trial users get correct status with days expired
- [ ] Users with no access get correct status
- [ ] All responses include proper success/error structure
- [ ] Frontend hook handles all scenarios correctly
- [ ] CSRF token is included in requests
- [ ] Proper HTTP status codes are returned

## Performance Testing

### Load Testing
```bash
# Using Apache Bench
ab -n 100 -c 10 -H "Authorization: Bearer YOUR_TOKEN" \
  "http://your-app.test/api/v1/license/user-access-status"
```

### Response Time
The endpoint should respond within 200ms for typical database queries.

## Security Testing

1. **CSRF Protection**: Verify CSRF token is required
2. **Authentication**: Ensure only authenticated users can access
3. **Authorization**: Verify users can only see their own status
4. **SQL Injection**: Test with malicious input (should be handled by Eloquent)
5. **Rate Limiting**: Test if rate limiting is applied

## Troubleshooting

### Common Issues

1. **401 Unauthorized**: Check if user is authenticated and active
2. **500 Server Error**: Check logs for database or application errors
3. **CSRF Token Mismatch**: Ensure CSRF token is included in request
4. **Incorrect Trial Days**: Verify trial dates are set correctly in database

### Debug Steps

1. Check Laravel logs: `storage/logs/laravel.log`
2. Verify database data: Check users and licenses tables
3. Test with tinker: `php artisan tinker`
4. Check middleware: Ensure ApiAuth middleware is working
5. Verify Sanctum configuration

## Conclusion

This endpoint is critical for the application's licensing system. It should be tested thoroughly in all scenarios to ensure proper access control and user experience.
