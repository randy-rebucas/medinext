<?php

namespace App\Http\Controllers\Api;

use OpenApi\Annotations as OA;

/**
 * @OA\OpenApi(
 *     @OA\Info(
 *         version="1.0.0",
 *         title="MediNext EMR API",
 *         description="Comprehensive Electronic Medical Records (EMR) API for healthcare management",
 *         @OA\Contact(
 *             email="support@medinext.com",
 *             name="MediNext Support Team"
 *         ),
 *         @OA\License(
 *             name="MIT",
 *             url="https://opensource.org/licenses/MIT"
 *         )
 *     ),
 *     @OA\Server(
 *         url=L5_SWAGGER_CONST_HOST,
 *         description="MediNext EMR API Server"
 *     ),
 *     @OA\SecurityScheme(
 *         securityScheme="sanctum",
 *         type="http",
 *         scheme="bearer",
 *         bearerFormat="JWT",
 *         description="Enter token in format (Bearer <token>)"
 *     )
 * )
 */

/**
 * @OA\Schema(
 *     schema="User",
 *     type="object",
 *     title="User",
 *     description="User model",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Dr. John Smith"),
 *     @OA\Property(property="email", type="string", format="email", example="doctor@clinic.com"),
 *     @OA\Property(property="phone", type="string", example="+1234567890"),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="email_verified_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(
 *         property="clinics",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/Clinic")
 *     ),
 *     @OA\Property(
 *         property="roles",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/Role")
 *     )
 * )
 */

/**
 * @OA\Schema(
 *     schema="Clinic",
 *     type="object",
 *     title="Clinic",
 *     description="Clinic model",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Downtown Medical Center"),
 *     @OA\Property(property="slug", type="string", example="downtown-medical-center"),
 *     @OA\Property(property="address", type="string", example="123 Main St, City, State 12345"),
 *     @OA\Property(property="phone", type="string", example="+1234567890"),
 *     @OA\Property(property="email", type="string", format="email", example="info@downtownmedical.com"),
 *     @OA\Property(property="website", type="string", example="https://downtownmedical.com"),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */

/**
 * @OA\Schema(
 *     schema="Role",
 *     type="object",
 *     title="Role",
 *     description="Role model",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="doctor"),
 *     @OA\Property(property="description", type="string", example="Medical doctor with full patient access"),
 *     @OA\Property(property="is_system_role", type="boolean", example=false),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(
 *         property="permissions",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/Permission")
 *     )
 * )
 */

/**
 * @OA\Schema(
 *     schema="Permission",
 *     type="object",
 *     title="Permission",
 *     description="Permission model",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="manage_patients"),
 *     @OA\Property(property="module", type="string", example="patients"),
 *     @OA\Property(property="action", type="string", example="manage"),
 *     @OA\Property(property="description", type="string", example="Manage patient records"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */

/**
 * @OA\Schema(
 *     schema="Patient",
 *     type="object",
 *     title="Patient",
 *     description="Patient model",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="first_name", type="string", example="John"),
 *     @OA\Property(property="last_name", type="string", example="Doe"),
 *     @OA\Property(property="date_of_birth", type="string", format="date", example="1990-01-15"),
 *     @OA\Property(property="gender", type="string", enum={"male", "female", "other"}, example="male"),
 *     @OA\Property(property="phone", type="string", example="+1234567890"),
 *     @OA\Property(property="email", type="string", format="email", example="john.doe@email.com"),
 *     @OA\Property(property="address", type="string", example="123 Main St, City, State 12345"),
 *     @OA\Property(property="emergency_contact", type="string", example="Jane Doe"),
 *     @OA\Property(property="emergency_phone", type="string", example="+1234567891"),
 *     @OA\Property(property="medical_record_number", type="string", example="MRN123456"),
 *     @OA\Property(property="insurance_provider", type="string", example="Blue Cross Blue Shield"),
 *     @OA\Property(property="insurance_number", type="string", example="INS123456789"),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */

/**
 * @OA\Schema(
 *     schema="Doctor",
 *     type="object",
 *     title="Doctor",
 *     description="Doctor model",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(property="specialization", type="string", example="Cardiology"),
 *     @OA\Property(property="license_number", type="string", example="MD123456"),
 *     @OA\Property(property="years_of_experience", type="integer", example=10),
 *     @OA\Property(property="education", type="string", example="MD, Harvard Medical School"),
 *     @OA\Property(property="certifications", type="array", @OA\Items(type="string"), example={"Board Certified Cardiologist", "ACLS Certified"}),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(property="user", ref="#/components/schemas/User")
 * )
 */

/**
 * @OA\Schema(
 *     schema="Appointment",
 *     type="object",
 *     title="Appointment",
 *     description="Appointment model",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="patient_id", type="integer", example=1),
 *     @OA\Property(property="doctor_id", type="integer", example=1),
 *     @OA\Property(property="clinic_id", type="integer", example=1),
 *     @OA\Property(property="appointment_date", type="string", format="date", example="2024-01-15"),
 *     @OA\Property(property="appointment_time", type="string", format="time", example="10:30:00"),
 *     @OA\Property(property="duration", type="integer", example=30, description="Duration in minutes"),
 *     @OA\Property(property="status", type="string", enum={"scheduled", "confirmed", "in_progress", "completed", "cancelled", "no_show"}, example="scheduled"),
 *     @OA\Property(property="type", type="string", example="consultation"),
 *     @OA\Property(property="notes", type="string", example="Regular checkup"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(property="patient", ref="#/components/schemas/Patient"),
 *     @OA\Property(property="doctor", ref="#/components/schemas/Doctor")
 * )
 */

/**
 * @OA\Schema(
 *     schema="Prescription",
 *     type="object",
 *     title="Prescription",
 *     description="Prescription model",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="patient_id", type="integer", example=1),
 *     @OA\Property(property="doctor_id", type="integer", example=1),
 *     @OA\Property(property="encounter_id", type="integer", example=1),
 *     @OA\Property(property="prescription_date", type="string", format="date", example="2024-01-15"),
 *     @OA\Property(property="status", type="string", enum={"draft", "active", "completed", "cancelled"}, example="active"),
 *     @OA\Property(property="notes", type="string", example="Take with food"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(property="patient", ref="#/components/schemas/Patient"),
 *     @OA\Property(property="doctor", ref="#/components/schemas/Doctor"),
 *     @OA\Property(
 *         property="items",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/PrescriptionItem")
 *     )
 * )
 */

/**
 * @OA\Schema(
 *     schema="PrescriptionItem",
 *     type="object",
 *     title="Prescription Item",
 *     description="Prescription item model",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="prescription_id", type="integer", example=1),
 *     @OA\Property(property="medication_name", type="string", example="Lisinopril"),
 *     @OA\Property(property="dosage", type="string", example="10mg"),
 *     @OA\Property(property="frequency", type="string", example="Once daily"),
 *     @OA\Property(property="quantity", type="integer", example=30),
 *     @OA\Property(property="instructions", type="string", example="Take with food"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */

/**
 * @OA\Schema(
 *     schema="Encounter",
 *     type="object",
 *     title="Encounter",
 *     description="Medical encounter model",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="patient_id", type="integer", example=1),
 *     @OA\Property(property="doctor_id", type="integer", example=1),
 *     @OA\Property(property="appointment_id", type="integer", example=1),
 *     @OA\Property(property="encounter_date", type="string", format="date-time", example="2024-01-15T10:30:00Z"),
 *     @OA\Property(property="chief_complaint", type="string", example="Chest pain"),
 *     @OA\Property(property="history_of_present_illness", type="string", example="Patient reports chest pain for 2 days"),
 *     @OA\Property(property="physical_examination", type="string", example="Normal vital signs"),
 *     @OA\Property(property="assessment", type="string", example="Possible angina"),
 *     @OA\Property(property="plan", type="string", example="Order EKG and blood work"),
 *     @OA\Property(property="status", type="string", enum={"in_progress", "completed", "cancelled"}, example="completed"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(property="patient", ref="#/components/schemas/Patient"),
 *     @OA\Property(property="doctor", ref="#/components/schemas/Doctor")
 * )
 */

/**
 * @OA\Schema(
 *     schema="LabResult",
 *     type="object",
 *     title="Lab Result",
 *     description="Laboratory result model",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="patient_id", type="integer", example=1),
 *     @OA\Property(property="doctor_id", type="integer", example=1),
 *     @OA\Property(property="encounter_id", type="integer", example=1),
 *     @OA\Property(property="test_name", type="string", example="Complete Blood Count"),
 *     @OA\Property(property="test_date", type="string", format="date", example="2024-01-15"),
 *     @OA\Property(property="result_date", type="string", format="date", example="2024-01-16"),
 *     @OA\Property(property="status", type="string", enum={"pending", "completed", "abnormal", "critical"}, example="completed"),
 *     @OA\Property(property="results", type="object", example={"hemoglobin": "14.2 g/dL", "hematocrit": "42%"}),
 *     @OA\Property(property="reference_range", type="string", example="Normal range: 12-16 g/dL"),
 *     @OA\Property(property="notes", type="string", example="Results within normal limits"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(property="patient", ref="#/components/schemas/Patient"),
 *     @OA\Property(property="doctor", ref="#/components/schemas/Doctor")
 * )
 */

/**
 * @OA\Schema(
 *     schema="FileAsset",
 *     type="object",
 *     title="File Asset",
 *     description="File asset model",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="patient_id", type="integer", example=1, nullable=true),
 *     @OA\Property(property="encounter_id", type="integer", example=1, nullable=true),
 *     @OA\Property(property="lab_result_id", type="integer", example=1, nullable=true),
 *     @OA\Property(property="file_name", type="string", example="lab_report.pdf"),
 *     @OA\Property(property="original_name", type="string", example="Lab Report - John Doe.pdf"),
 *     @OA\Property(property="file_path", type="string", example="uploads/patients/1/lab_report.pdf"),
 *     @OA\Property(property="file_size", type="integer", example=1024000),
 *     @OA\Property(property="mime_type", type="string", example="application/pdf"),
 *     @OA\Property(property="category", type="string", example="lab_results"),
 *     @OA\Property(property="description", type="string", example="Blood test results"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */

/**
 * @OA\Schema(
 *     schema="Room",
 *     type="object",
 *     title="Room",
 *     description="Room model",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="clinic_id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Room 101"),
 *     @OA\Property(property="room_number", type="string", example="101"),
 *     @OA\Property(property="room_type", type="string", enum={"consultation", "examination", "procedure", "waiting", "office"}, example="consultation"),
 *     @OA\Property(property="capacity", type="integer", example=4),
 *     @OA\Property(property="equipment", type="array", @OA\Items(type="string"), example={"Examination table", "Computer", "Blood pressure monitor"}),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */

/**
 * @OA\Schema(
 *     schema="Notification",
 *     type="object",
 *     title="Notification",
 *     description="Notification model",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(property="title", type="string", example="New Appointment"),
 *     @OA\Property(property="message", type="string", example="You have a new appointment scheduled for tomorrow"),
 *     @OA\Property(property="type", type="string", enum={"info", "warning", "error", "success"}, example="info"),
 *     @OA\Property(property="is_read", type="boolean", example=false),
 *     @OA\Property(property="data", type="object", example={"appointment_id": 1, "patient_name": "John Doe"}),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */

/**
 * @OA\Schema(
 *     schema="ActivityLog",
 *     type="object",
 *     title="Activity Log",
 *     description="Activity log model",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(property="module", type="string", example="patients"),
 *     @OA\Property(property="action", type="string", example="created"),
 *     @OA\Property(property="description", type="string", example="Created new patient record for John Doe"),
 *     @OA\Property(property="old_values", type="object", nullable=true),
 *     @OA\Property(property="new_values", type="object", nullable=true),
 *     @OA\Property(property="ip_address", type="string", example="192.168.1.1"),
 *     @OA\Property(property="user_agent", type="string", example="Mozilla/5.0..."),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(property="user", ref="#/components/schemas/User")
 * )
 */

/**
 * @OA\Schema(
 *     schema="License",
 *     type="object",
 *     title="License",
 *     description="License model",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="license_key", type="string", example="LIC-123456789"),
 *     @OA\Property(property="clinic_id", type="integer", example=1),
 *     @OA\Property(property="plan", type="string", example="professional"),
 *     @OA\Property(property="status", type="string", enum={"active", "expired", "suspended", "trial"}, example="active"),
 *     @OA\Property(property="expires_at", type="string", format="date-time", example="2024-12-31T23:59:59Z"),
 *     @OA\Property(property="features", type="array", @OA\Items(type="string"), example={"lab_results", "medrep_management", "advanced_reporting"}),
 *     @OA\Property(property="usage_limits", type="object", example={"users": 50, "patients": 1000, "appointments": 5000}),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */

/**
 * @OA\Schema(
 *     schema="Error",
 *     type="object",
 *     title="Error Response",
 *     description="Standard error response",
 *     @OA\Property(property="success", type="boolean", example=false),
 *     @OA\Property(property="message", type="string", example="An error occurred"),
     *     @OA\Property(property="errors", type="object", nullable=true, example={"field": "The field is required"}),
 *     @OA\Property(property="error_code", type="string", nullable=true, example="VALIDATION_ERROR")
 * )
 */

/**
 * @OA\Schema(
 *     schema="Success",
 *     type="object",
 *     title="Success Response",
 *     description="Standard success response",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="message", type="string", example="Operation completed successfully"),
 *     @OA\Property(property="data", type="object", nullable=true)
 * )
 */

/**
 * @OA\Schema(
 *     schema="Pagination",
 *     type="object",
 *     title="Pagination",
 *     description="Pagination metadata",
 *     @OA\Property(property="current_page", type="integer", example=1),
 *     @OA\Property(property="last_page", type="integer", example=10),
 *     @OA\Property(property="per_page", type="integer", example=15),
 *     @OA\Property(property="total", type="integer", example=150),
 *     @OA\Property(property="from", type="integer", example=1),
 *     @OA\Property(property="to", type="integer", example=15),
 *     @OA\Property(
 *         property="links",
 *         type="object",
 *         @OA\Property(property="first", type="string", example="http://api.example.com/users?page=1"),
 *         @OA\Property(property="last", type="string", example="http://api.example.com/users?page=10"),
 *         @OA\Property(property="prev", type="string", nullable=true),
 *         @OA\Property(property="next", type="string", example="http://api.example.com/users?page=2")
 *     )
 * )
 */

/**
 * @OA\Schema(
 *     schema="ValidationError",
 *     type="object",
 *     title="Validation Error",
 *     description="Validation error response",
 *     @OA\Property(property="success", type="boolean", example=false),
 *     @OA\Property(property="message", type="string", example="Validation failed"),
 *     @OA\Property(
 *         property="errors",
 *         type="object",
 *         @OA\Property(
 *             property="email",
 *             type="array",
 *             @OA\Items(type="string"),
 *             example={"The email field is required", "The email must be a valid email address"}
 *         ),
 *         @OA\Property(
 *             property="password",
 *             type="array",
 *             @OA\Items(type="string"),
 *             example={"The password field is required", "The password must be at least 8 characters"}
 *         )
 *     )
 * )
 */
