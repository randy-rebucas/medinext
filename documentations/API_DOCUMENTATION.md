# MediNext API Documentation

## Overview

The MediNext API provides comprehensive endpoints for managing a medical EMR (Electronic Medical Records) system. The API supports both mobile and web applications with full CRUD operations for patients, doctors, appointments, encounters, prescriptions, lab results, and more.

## Base URL

```
https://your-domain.com/api/v1
```

## Authentication & Authorization

The API uses Laravel Sanctum for authentication with comprehensive permission-based access control. All protected endpoints require:

1. **Authentication**: Valid API token via Bearer authentication
2. **Permission**: Specific permission based on user role and clinic access
3. **Clinic Access**: Multi-clinic isolation for data security

### Authentication Header
```
Authorization: Bearer {your-token}
```

### Permission System
- **130+ Permissions** covering all application functionality
- **6 Role Types** with specific permission sets
- **Multi-Clinic Support** with clinic-specific access control
- **Granular Access Control** for every API endpoint

### Role-Based Access
- **Superadmin**: Full system access (100+ permissions)
- **Admin**: Clinic management (80+ permissions)
- **Doctor**: Clinical operations (30+ permissions)
- **Receptionist**: Front desk operations (25+ permissions)
- **Patient**: Self-service access (15+ permissions)
- **Medrep**: Medical representative features (20+ permissions)

## Response Format

All API responses follow a consistent format:

### Success Response
```json
{
  "success": true,
  "message": "Success message",
  "data": { ... },
  "timestamp": "2024-01-01T00:00:00.000000Z"
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error message",
  "errors": { ... },
  "timestamp": "2024-01-01T00:00:00.000000Z"
}
```

### Permission Denied Response (403)
```json
{
  "success": false,
  "message": "Insufficient permissions",
  "error_code": "INSUFFICIENT_PERMISSIONS",
  "required_permission": "patients.create",
  "timestamp": "2024-01-01T00:00:00.000000Z"
}
```

### Unauthenticated Response (401)
```json
{
  "success": false,
  "message": "Unauthenticated",
  "error_code": "UNAUTHENTICATED",
  "timestamp": "2024-01-01T00:00:00.000000Z"
}
```

### Clinic Access Denied Response (403)
```json
{
  "success": false,
  "message": "No access to this clinic",
  "error_code": "CLINIC_ACCESS_DENIED",
  "timestamp": "2024-01-01T00:00:00.000000Z"
}
```

### Paginated Response
```json
{
  "success": true,
  "message": "Success message",
  "data": {
    "items": [ ... ],
    "pagination": {
      "current_page": 1,
      "per_page": 15,
      "total": 100,
      "last_page": 7,
      "from": 1,
      "to": 15,
      "has_more_pages": true
    }
  },
  "timestamp": "2024-01-01T00:00:00.000000Z"
}
```

## Endpoints

### Authentication

#### POST /auth/login
Login user and get access token.

**Request Body:**
```json
{
  "email": "user@example.com",
  "password": "password",
  "clinic_id": 1,
  "remember": false
}
```

**Response:**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": { ... },
    "token": "1|abc123...",
    "token_type": "Bearer",
    "expires_at": "2024-01-31T00:00:00.000000Z",
    "clinic_access": [ ... ]
  }
}
```

#### POST /auth/register
Register a new user.

**Request Body:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password",
  "password_confirmation": "password",
  "phone": "+1234567890",
  "clinic_id": 1,
  "role_id": 2
}
```

#### POST /auth/logout
Logout user and revoke token.

#### GET /auth/me
Get authenticated user information.

#### PUT /auth/profile
Update user profile.

#### PUT /auth/password
Update user password.

### Dashboard

#### GET /dashboard
Get dashboard data with statistics and recent activities.

#### GET /dashboard/stats
Get dashboard statistics.

#### GET /dashboard/notifications
Get user notifications.

#### GET /mobile/dashboard
Get mobile-optimized dashboard data.

#### GET /web/dashboard
Get web-optimized dashboard data.

### Clinics

#### GET /clinics
Get list of clinics (public endpoint).

#### GET /clinics/{id}
Get clinic details (public endpoint).

#### GET /clinics (authenticated)
Get user's accessible clinics.

#### POST /clinics
Create a new clinic.

#### PUT /clinics/{id}
Update clinic information.

#### DELETE /clinics/{id}
Delete clinic.

#### GET /clinics/{id}/users
Get clinic users.

#### GET /clinics/{id}/doctors
Get clinic doctors.

#### GET /clinics/{id}/patients
Get clinic patients.

#### GET /clinics/{id}/appointments
Get clinic appointments.

#### GET /clinics/{id}/statistics
Get clinic statistics.

### Patients

#### GET /patients
Get list of patients with filtering and pagination.

**Query Parameters:**
- `search`: Search by name or code
- `sex`: Filter by sex (male, female, other)
- `age_min`: Minimum age
- `age_max`: Maximum age
- `date_from`: Filter by creation date from
- `date_to`: Filter by creation date to
- `per_page`: Items per page (default: 15, max: 100)
- `page`: Page number
- `sort`: Sort field
- `direction`: Sort direction (asc, desc)

#### POST /patients
Create a new patient.

**Request Body:**
```json
{
  "first_name": "John",
  "last_name": "Doe",
  "dob": "1990-01-01",
  "sex": "male",
  "contact": {
    "phone": "+1234567890",
    "email": "john@example.com",
    "address": "123 Main St"
  },
  "allergies": ["penicillin"],
  "consents": ["treatment", "data_sharing"]
}
```

#### GET /patients/{id}
Get patient details with medical history.

#### PUT /patients/{id}
Update patient information.

#### DELETE /patients/{id}
Delete patient.

#### GET /patients/{id}/appointments
Get patient appointments.

#### GET /patients/{id}/encounters
Get patient encounters.

#### GET /patients/{id}/prescriptions
Get patient prescriptions.

#### GET /patients/{id}/lab-results
Get patient lab results.

#### GET /patients/{id}/medical-history
Get complete medical history.

#### GET /patients/{id}/file-assets
Get patient files.

#### POST /patients/{id}/file-assets
Upload file for patient.

#### GET /patients/search
Search patients.

#### GET /patients/recent
Get recent patients.

### Doctors

#### GET /doctors
Get list of doctors.

#### POST /doctors
Create a new doctor.

**Request Body:**
```json
{
  "name": "Dr. Jane Smith",
  "email": "jane@example.com",
  "password": "password",
  "password_confirmation": "password",
  "phone": "+1234567890",
  "specialty": "Cardiology",
  "license_no": "MD123456",
  "consultation_fee": 150.00,
  "availability_schedule": {
    "monday": ["09:00", "10:00", "11:00"],
    "tuesday": ["09:00", "10:00", "11:00"],
    "wednesday": ["09:00", "10:00", "11:00"],
    "thursday": ["09:00", "10:00", "11:00"],
    "friday": ["09:00", "10:00", "11:00"],
    "saturday": [],
    "sunday": []
  }
}
```

#### GET /doctors/{id}
Get doctor details.

#### PUT /doctors/{id}
Update doctor information.

#### DELETE /doctors/{id}
Delete doctor.

#### GET /doctors/{id}/appointments
Get doctor appointments.

#### GET /doctors/{id}/encounters
Get doctor encounters.

#### GET /doctors/{id}/patients
Get doctor patients.

#### GET /doctors/{id}/availability
Get doctor availability for a date.

#### POST /doctors/{id}/availability
Update doctor availability schedule.

#### GET /doctors/{id}/statistics
Get doctor statistics.

### Appointments

#### GET /appointments
Get list of appointments.

**Query Parameters:**
- `date_from`: Filter by date from
- `date_to`: Filter by date to
- `status`: Filter by status
- `doctor_id`: Filter by doctor
- `patient_id`: Filter by patient
- `appointment_type`: Filter by type

#### POST /appointments
Create a new appointment.

**Request Body:**
```json
{
  "patient_id": 1,
  "doctor_id": 1,
  "start_at": "2024-01-15 10:00:00",
  "duration": 30,
  "appointment_type": "consultation",
  "reason": "Annual checkup",
  "room_id": 1,
  "priority": "normal",
  "notes": "Patient prefers morning appointments",
  "insurance_info": {
    "provider": "Blue Cross",
    "policy_number": "BC123456"
  },
  "copay_amount": 25.00,
  "total_amount": 150.00
}
```

#### GET /appointments/{id}
Get appointment details.

#### PUT /appointments/{id}
Update appointment.

#### DELETE /appointments/{id}
Delete appointment.

#### POST /appointments/{id}/check-in
Check in patient.

#### POST /appointments/{id}/check-out
Check out patient.

#### POST /appointments/{id}/cancel
Cancel appointment.

#### POST /appointments/{id}/reschedule
Reschedule appointment.

#### POST /appointments/{id}/reminder
Send appointment reminder.

#### GET /appointments/available-slots
Get available time slots for a doctor.

#### GET /appointments/conflicts
Check for appointment conflicts.

#### GET /appointments/today
Get today's appointments.

#### GET /appointments/upcoming
Get upcoming appointments.

#### GET /appointments/calendar/events
Get calendar events.

### Encounters

#### GET /encounters
Get list of encounters.

#### POST /encounters
Create a new encounter.

**Request Body:**
```json
{
  "patient_id": 1,
  "doctor_id": 1,
  "date": "2024-01-15",
  "type": "consultation",
  "chief_complaint": "Chest pain",
  "history_present_illness": "Patient reports chest pain for 2 days...",
  "physical_examination": "Vital signs stable...",
  "assessment": "Possible angina",
  "plan": "ECG, blood work, follow-up in 1 week",
  "vital_signs": {
    "blood_pressure": "120/80",
    "heart_rate": 72,
    "temperature": 98.6,
    "respiratory_rate": 16
  },
  "diagnosis": ["Chest pain", "Possible angina"],
  "treatment_plan": ["ECG", "Blood work", "Medication"]
}
```

#### GET /encounters/{id}
Get encounter details.

#### PUT /encounters/{id}
Update encounter.

#### DELETE /encounters/{id}
Delete encounter.

#### GET /encounters/{id}/prescriptions
Get encounter prescriptions.

#### GET /encounters/{id}/lab-results
Get encounter lab results.

#### GET /encounters/{id}/file-assets
Get encounter files.

#### POST /encounters/{id}/file-assets
Upload file for encounter.

#### PUT /encounters/{id}/soap-notes
Update SOAP notes.

#### POST /encounters/{id}/complete
Complete encounter.

### Prescriptions

#### GET /prescriptions
Get list of prescriptions.

#### POST /prescriptions
Create a new prescription.

**Request Body:**
```json
{
  "patient_id": 1,
  "doctor_id": 1,
  "encounter_id": 1,
  "prescription_type": "new",
  "diagnosis": "Hypertension",
  "instructions": "Take with food",
  "dispense_quantity": 30,
  "refills_allowed": 3,
  "expiry_date": "2024-12-31",
  "pharmacy_notes": "Generic substitution allowed",
  "patient_instructions": "Take once daily with breakfast",
  "total_cost": 45.00,
  "copay_amount": 10.00,
  "items": [
    {
      "medication_name": "Lisinopril",
      "dosage": "10mg",
      "frequency": "Once daily",
      "duration": "30 days",
      "quantity": 30,
      "instructions": "Take with food"
    }
  ]
}
```

#### GET /prescriptions/{id}
Get prescription details.

#### PUT /prescriptions/{id}
Update prescription.

#### DELETE /prescriptions/{id}
Delete prescription.

#### GET /prescriptions/{id}/items
Get prescription items.

#### POST /prescriptions/{id}/items
Add item to prescription.

#### PUT /prescriptions/{id}/items/{item_id}
Update prescription item.

#### DELETE /prescriptions/{id}/items/{item_id}
Remove item from prescription.

#### POST /prescriptions/{id}/verify
Verify prescription.

#### POST /prescriptions/{id}/dispense
Mark prescription as dispensed.

#### POST /prescriptions/{id}/refill
Process prescription refill.

#### GET /prescriptions/{id}/pdf
Download prescription PDF.

#### GET /prescriptions/{id}/qr
Get prescription QR code.

#### GET /prescriptions/active
Get active prescriptions.

#### GET /prescriptions/expired
Get expired prescriptions.

#### GET /prescriptions/needs-refill
Get prescriptions needing refill.

### Lab Results

#### GET /lab-results
Get list of lab results.

#### POST /lab-results
Create a new lab result.

**Request Body:**
```json
{
  "patient_id": 1,
  "encounter_id": 1,
  "ordered_by_doctor_id": 1,
  "test_type": "blood_work",
  "test_name": "Complete Blood Count",
  "result_value": "Normal",
  "unit": "cells/μL",
  "reference_range": "4.5-11.0",
  "status": "completed",
  "ordered_at": "2024-01-15 09:00:00",
  "completed_at": "2024-01-15 11:00:00",
  "notes": "All values within normal range"
}
```

#### GET /lab-results/{id}
Get lab result details.

#### PUT /lab-results/{id}
Update lab result.

#### DELETE /lab-results/{id}
Delete lab result.

#### GET /lab-results/{id}/file-assets
Get lab result files.

#### POST /lab-results/{id}/file-assets
Upload file for lab result.

#### POST /lab-results/{id}/review
Review lab result.

#### GET /lab-results/pending
Get pending lab results.

#### GET /lab-results/abnormal
Get abnormal lab results.

### File Assets

#### GET /file-assets
Get list of file assets.

#### POST /file-assets
Create a new file asset.

#### GET /file-assets/{id}
Get file asset details.

#### PUT /file-assets/{id}
Update file asset.

#### DELETE /file-assets/{id}
Delete file asset.

#### GET /file-assets/{id}/download
Download file.

#### GET /file-assets/{id}/preview
Preview file.

#### POST /file-assets/upload
Upload file.

#### GET /file-assets/categories
Get file categories.

### Medrep Management

#### GET /medreps
Get list of medreps.

#### POST /medreps
Create a new medrep.

#### GET /medreps/{id}
Get medrep details.

#### PUT /medreps/{id}
Update medrep.

#### DELETE /medreps/{id}
Delete medrep.

#### GET /medreps/{id}/visits
Get medrep visits.

#### POST /medreps/{id}/visits
Schedule medrep visit.

#### PUT /medreps/{id}/visits/{visit_id}
Update medrep visit.

#### DELETE /medreps/{id}/visits/{visit_id}
Cancel medrep visit.

#### GET /medrep-visits
Get all medrep visits.

#### GET /medrep-visits/upcoming
Get upcoming medrep visits.

### Settings

#### GET /settings
Get all settings.

#### PUT /settings
Update settings.

#### GET /settings/clinic
Get clinic settings.

#### PUT /settings/clinic
Update clinic settings.

#### GET /settings/user
Get user settings.

#### PUT /settings/user
Update user settings.

### Search and Filtering

#### GET /search/patients
Search patients.

#### GET /search/doctors
Search doctors.

#### GET /search/appointments
Search appointments.

#### GET /search/prescriptions
Search prescriptions.

### Reports and Analytics

#### GET /reports/appointments
Get appointment reports.

#### GET /reports/prescriptions
Get prescription reports.

#### GET /reports/patients
Get patient reports.

#### GET /reports/doctors
Get doctor reports.

#### GET /reports/lab-results
Get lab result reports.

### Mobile-Specific Endpoints

#### GET /mobile/dashboard
Get mobile dashboard.

#### GET /mobile/notifications
Get mobile notifications.

#### POST /mobile/notifications/mark-read
Mark notification as read.

#### GET /mobile/quick-actions
Get quick actions.

#### GET /mobile/recent-patients
Get recent patients.

#### GET /mobile/today-appointments
Get today's appointments.

#### GET /mobile/pending-tasks
Get pending tasks.

### Web-Specific Endpoints

#### GET /web/dashboard
Get web dashboard.

#### GET /web/calendar
Get calendar view.

#### GET /web/calendar/events
Get calendar events.

#### GET /web/analytics
Get analytics data.

#### GET /web/export/patients
Export patients.

#### GET /web/export/appointments
Export appointments.

#### GET /web/export/prescriptions
Export prescriptions.

### Webhooks

#### POST /webhooks/lab-results
Lab result webhook.

#### POST /webhooks/prescriptions
Prescription webhook.

#### POST /webhooks/appointments
Appointment webhook.

### System Endpoints

#### GET /health
Health check endpoint.

#### GET /version
Get API version information.

## Error Codes

- `UNAUTHENTICATED`: User is not authenticated
- `ACCOUNT_DEACTIVATED`: User account is deactivated
- `NO_CLINIC_ACCESS`: User has no clinic access
- `CLINIC_ACCESS_DENIED`: User has no access to specified clinic
- `INSUFFICIENT_PERMISSIONS`: User lacks required permissions

## Rate Limiting

The API implements rate limiting to prevent abuse:

- Authentication endpoints: 5 requests per minute
- General API endpoints: 60 requests per minute
- File upload endpoints: 10 requests per minute

## File Upload

File uploads are supported with the following limits:

- Maximum file size: 10MB
- Supported file types: Images, PDFs, Documents
- Storage: Private storage with temporary URLs for access

## Webhooks

Webhooks are available for external integrations:

- Lab result updates
- Prescription status changes
- Appointment modifications

Webhook payloads include signature verification for security.

## SDKs and Libraries

### JavaScript/TypeScript
```javascript
// Example API client usage
const api = new MediNextAPI({
  baseURL: 'https://your-domain.com/api/v1',
  token: 'your-access-token'
});

// Get patients
const patients = await api.patients.list({
  search: 'John',
  per_page: 20
});

// Create appointment
const appointment = await api.appointments.create({
  patient_id: 1,
  doctor_id: 1,
  start_at: '2024-01-15 10:00:00',
  duration: 30,
  appointment_type: 'consultation'
});
```

### PHP
```php
// Example API client usage
$api = new MediNextAPI([
    'base_url' => 'https://your-domain.com/api/v1',
    'token' => 'your-access-token'
]);

// Get patients
$patients = $api->patients->list([
    'search' => 'John',
    'per_page' => 20
]);

// Create appointment
$appointment = $api->appointments->create([
    'patient_id' => 1,
    'doctor_id' => 1,
    'start_at' => '2024-01-15 10:00:00',
    'duration' => 30,
    'appointment_type' => 'consultation'
]);
```

## Testing

The API includes comprehensive test coverage:

- Unit tests for all controllers
- Integration tests for API endpoints
- Authentication and authorization tests
- File upload tests
- Webhook tests

Run tests with:
```bash
php artisan test
```

## Support

For API support and questions:

- Email: api-support@medinext.com
- Documentation: https://docs.medinext.com
- Status Page: https://status.medinext.com
