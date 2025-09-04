# MediNext API Quick Reference

## Base URL
```
https://your-domain.com/api/v1
```

## Authentication
```bash
# Login
curl -X POST https://your-domain.com/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"password","clinic_id":1}'

# Use token in subsequent requests
curl -H "Authorization: Bearer {token}" https://your-domain.com/api/v1/patients
```

## Common Endpoints

### Patients
```bash
# List patients
GET /patients?search=john&per_page=20&page=1

# Create patient
POST /patients
{
  "first_name": "John",
  "last_name": "Doe",
  "dob": "1990-01-01",
  "sex": "male",
  "contact": {"phone": "+1234567890"}
}

# Get patient
GET /patients/{id}

# Update patient
PUT /patients/{id}

# Delete patient
DELETE /patients/{id}
```

### Appointments
```bash
# List appointments
GET /appointments?date_from=2024-01-01&date_to=2024-01-31

# Create appointment
POST /appointments
{
  "patient_id": 1,
  "doctor_id": 1,
  "start_at": "2024-01-15 10:00:00",
  "duration": 30,
  "appointment_type": "consultation"
}

# Check in patient
POST /appointments/{id}/check-in

# Cancel appointment
POST /appointments/{id}/cancel
{
  "cancellation_reason": "Patient rescheduled"
}
```

### Doctors
```bash
# List doctors
GET /doctors?specialty=cardiology

# Create doctor
POST /doctors
{
  "name": "Dr. Jane Smith",
  "email": "jane@example.com",
  "password": "password",
  "specialty": "Cardiology",
  "license_no": "MD123456"
}

# Get doctor availability
GET /doctors/{id}/availability?date=2024-01-15
```

### Prescriptions
```bash
# List prescriptions
GET /prescriptions?status=active

# Create prescription
POST /prescriptions
{
  "patient_id": 1,
  "doctor_id": 1,
  "prescription_type": "new",
  "items": [{
    "medication_name": "Lisinopril",
    "dosage": "10mg",
    "frequency": "Once daily",
    "duration": "30 days"
  }]
}

# Verify prescription
POST /prescriptions/{id}/verify
{
  "status": true,
  "notes": "Verified and approved"
}
```

### Lab Results
```bash
# List lab results
GET /lab-results?status=pending

# Create lab result
POST /lab-results
{
  "patient_id": 1,
  "doctor_id": 1,
  "test_name": "Complete Blood Count",
  "result_value": "Normal",
  "status": "completed"
}

# Review lab result
POST /lab-results/{id}/review
{
  "reviewed_by_doctor_id": 1,
  "status": "completed"
}
```

### File Uploads
```bash
# Upload file
POST /file-assets/upload
Content-Type: multipart/form-data
{
  "file": [file],
  "category": "medical_record",
  "owner_type": "App\\Models\\Patient",
  "owner_id": 1
}

# Download file
GET /file-assets/{id}/download
```

## Mobile Endpoints

### Dashboard
```bash
# Mobile dashboard
GET /mobile/dashboard

# Quick actions
GET /mobile/quick-actions

# Today's appointments
GET /mobile/today-appointments

# Pending tasks
GET /mobile/pending-tasks
```

## Web Endpoints

### Dashboard
```bash
# Web dashboard
GET /web/dashboard

# Calendar events
GET /web/calendar/events?start=2024-01-01&end=2024-01-31

# Analytics
GET /web/analytics
```

## Search and Filtering

### Common Query Parameters
- `search`: Search term
- `per_page`: Items per page (default: 15, max: 100)
- `page`: Page number
- `sort`: Sort field
- `direction`: Sort direction (asc, desc)

### Date Filtering
- `date_from`: Filter from date
- `date_to`: Filter to date

### Status Filtering
- `status`: Filter by status
- `type`: Filter by type

## Error Handling

### Common HTTP Status Codes
- `200`: Success
- `201`: Created
- `400`: Bad Request
- `401`: Unauthorized
- `403`: Forbidden
- `404`: Not Found
- `422`: Validation Error
- `500`: Internal Server Error

### Error Response Format
```json
{
  "success": false,
  "message": "Error message",
  "errors": {
    "field": ["Error message"]
  },
  "timestamp": "2024-01-01T00:00:00.000000Z"
}
```

## Rate Limits
- Authentication: 5 requests/minute
- General API: 60 requests/minute
- File uploads: 10 requests/minute

## File Upload Limits
- Maximum size: 10MB
- Supported types: Images, PDFs, Documents
- Storage: Private with temporary URLs

## Webhooks
```bash
# Lab result webhook
POST /webhooks/lab-results
X-Webhook-Signature: {signature}
{
  "lab_result_id": 1,
  "result_value": "Normal",
  "status": "completed"
}
```

## Health Check
```bash
# System health
GET /health

# API version
GET /version
```

## SDK Examples

### JavaScript
```javascript
const api = new MediNextAPI({
  baseURL: 'https://your-domain.com/api/v1',
  token: 'your-token'
});

// Get patients
const patients = await api.patients.list({search: 'john'});

// Create appointment
const appointment = await api.appointments.create({
  patient_id: 1,
  doctor_id: 1,
  start_at: '2024-01-15 10:00:00',
  duration: 30
});
```

### PHP
```php
$api = new MediNextAPI([
    'base_url' => 'https://your-domain.com/api/v1',
    'token' => 'your-token'
]);

$patients = $api->patients->list(['search' => 'john']);
$appointment = $api->appointments->create([
    'patient_id' => 1,
    'doctor_id' => 1,
    'start_at' => '2024-01-15 10:00:00',
    'duration' => 30
]);
```

## Testing
```bash
# Run tests
php artisan test

# Test specific endpoint
php artisan test --filter=PatientControllerTest
```

## Support
- Email: api-support@medinext.com
- Documentation: https://docs.medinext.com
- Status: https://status.medinext.com
