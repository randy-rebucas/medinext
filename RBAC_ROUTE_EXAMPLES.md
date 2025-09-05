# RBAC Route Examples

This document provides examples of how to implement the RBAC system in your routes.

## Route Protection Examples

### Basic Role Protection

```php
// routes/web.php

// Super admin only routes
Route::middleware(['role:superadmin'])->group(function () {
    Route::get('/admin/tenants', [TenantController::class, 'index']);
    Route::get('/admin/plans', [PlanController::class, 'index']);
    Route::get('/admin/global-settings', [GlobalSettingsController::class, 'index']);
});

// Admin and doctor routes
Route::middleware(['role:admin,doctor'])->group(function () {
    Route::get('/patients', [PatientController::class, 'index']);
    Route::get('/patients/{patient}', [PatientController::class, 'show']);
});

// Doctor only routes
Route::middleware(['role:doctor'])->group(function () {
    Route::post('/prescriptions', [PrescriptionController::class, 'store']);
    Route::put('/prescriptions/{prescription}', [PrescriptionController::class, 'update']);
    Route::get('/my-schedule', [ScheduleController::class, 'mySchedule']);
});

// Receptionist routes
Route::middleware(['role:receptionist'])->group(function () {
    Route::post('/appointments/checkin', [AppointmentController::class, 'checkin']);
    Route::get('/billing', [BillingController::class, 'index']);
});

// Patient routes
Route::middleware(['role:patient'])->group(function () {
    Route::get('/my-appointments', [AppointmentController::class, 'myAppointments']);
    Route::post('/appointments', [AppointmentController::class, 'store']);
    Route::get('/my-prescriptions', [PrescriptionController::class, 'myPrescriptions']);
});

// MedRep routes
Route::middleware(['role:medrep'])->group(function () {
    Route::get('/doctor-visits', [MedrepController::class, 'visits']);
    Route::post('/product-sheets', [MedrepController::class, 'uploadProductSheet']);
});
```

### Permission-Based Protection

```php
// routes/web.php

// Patient management routes
Route::middleware(['permission:patient.read'])->group(function () {
    Route::get('/patients', [PatientController::class, 'index']);
    Route::get('/patients/{patient}', [PatientController::class, 'show']);
});

Route::middleware(['permission:patient.write'])->group(function () {
    Route::get('/patients/create', [PatientController::class, 'create']);
    Route::post('/patients', [PatientController::class, 'store']);
    Route::get('/patients/{patient}/edit', [PatientController::class, 'edit']);
    Route::put('/patients/{patient}', [PatientController::class, 'update']);
});

Route::middleware(['permission:patient.delete'])->group(function () {
    Route::delete('/patients/{patient}', [PatientController::class, 'destroy']);
});

// EMR routes
Route::middleware(['permission:emr.read'])->group(function () {
    Route::get('/patients/{patient}/emr', [EMRController::class, 'show']);
});

Route::middleware(['permission:emr.write'])->group(function () {
    Route::post('/patients/{patient}/emr', [EMRController::class, 'store']);
    Route::put('/patients/{patient}/emr/{emr}', [EMRController::class, 'update']);
});

// Prescription routes
Route::middleware(['permission:rx.issue'])->group(function () {
    Route::post('/prescriptions', [PrescriptionController::class, 'store']);
});

Route::middleware(['permission:rx.view'])->group(function () {
    Route::get('/prescriptions', [PrescriptionController::class, 'index']);
    Route::get('/prescriptions/{prescription}', [PrescriptionController::class, 'show']);
});

Route::middleware(['permission:rx.download'])->group(function () {
    Route::get('/prescriptions/{prescription}/download', [PrescriptionController::class, 'download']);
});

// Clinical notes routes (restricted access)
Route::middleware(['clinical.access'])->group(function () {
    Route::get('/patients/{patient}/clinical-notes', [ClinicalNotesController::class, 'index']);
    Route::post('/patients/{patient}/clinical-notes', [ClinicalNotesController::class, 'store']);
});
```

### Multiple Permission Requirements

```php
// routes/web.php

// Routes requiring multiple permissions
Route::middleware(['permission.all:patient.read,patient.write'])->group(function () {
    Route::get('/patients/advanced-search', [PatientController::class, 'advancedSearch']);
    Route::post('/patients/bulk-update', [PatientController::class, 'bulkUpdate']);
});

// Routes requiring any of multiple permissions
Route::middleware(['permission:billing.view,billing.create,billing.edit'])->group(function () {
    Route::get('/billing/dashboard', [BillingController::class, 'dashboard']);
});
```

### API Routes

```php
// routes/api.php

Route::middleware(['auth:sanctum'])->group(function () {
    
    // Patient API routes
    Route::middleware(['permission:patient.read'])->group(function () {
        Route::get('/patients', [Api\PatientController::class, 'index']);
        Route::get('/patients/{patient}', [Api\PatientController::class, 'show']);
    });

    Route::middleware(['permission:patient.write'])->group(function () {
        Route::post('/patients', [Api\PatientController::class, 'store']);
        Route::put('/patients/{patient}', [Api\PatientController::class, 'update']);
    });

    // EMR API routes
    Route::middleware(['clinical.access'])->group(function () {
        Route::get('/patients/{patient}/emr', [Api\EMRController::class, 'show']);
        Route::post('/patients/{patient}/emr', [Api\EMRController::class, 'store']);
    });

    // Prescription API routes
    Route::middleware(['permission:rx.issue'])->group(function () {
        Route::post('/prescriptions', [Api\PrescriptionController::class, 'store']);
    });

    Route::middleware(['permission:rx.view'])->group(function () {
        Route::get('/prescriptions', [Api\PrescriptionController::class, 'index']);
        Route::get('/prescriptions/{prescription}', [Api\PrescriptionController::class, 'show']);
    });

    // MedRep API routes
    Route::middleware(['role:medrep'])->group(function () {
        Route::get('/medrep/visits', [Api\MedrepController::class, 'visits']);
        Route::post('/medrep/visits', [Api\MedrepController::class, 'scheduleVisit']);
        Route::post('/medrep/product-sheets', [Api\MedrepController::class, 'uploadProductSheet']);
    });
});
```

### Clinic-Scoped Routes

```php
// routes/web.php

// Routes that require clinic context
Route::prefix('clinics/{clinic}')->middleware(['role:admin,doctor,receptionist'])->group(function () {
    
    // Patient management within clinic
    Route::middleware(['permission:patient.read'])->group(function () {
        Route::get('/patients', [PatientController::class, 'index']);
        Route::get('/patients/{patient}', [PatientController::class, 'show']);
    });

    Route::middleware(['permission:patient.write'])->group(function () {
        Route::get('/patients/create', [PatientController::class, 'create']);
        Route::post('/patients', [PatientController::class, 'store']);
        Route::get('/patients/{patient}/edit', [PatientController::class, 'edit']);
        Route::put('/patients/{patient}', [PatientController::class, 'update']);
    });

    // Schedule management
    Route::middleware(['permission:schedule.manage'])->group(function () {
        Route::get('/schedule', [ScheduleController::class, 'index']);
        Route::post('/schedule/availability', [ScheduleController::class, 'updateAvailability']);
    });

    // Billing management
    Route::middleware(['permission:billing.view'])->group(function () {
        Route::get('/billing', [BillingController::class, 'index']);
        Route::get('/billing/reports', [BillingController::class, 'reports']);
    });

    Route::middleware(['permission:billing.create'])->group(function () {
        Route::post('/billing/invoices', [BillingController::class, 'createInvoice']);
    });
});
```

### Conditional Route Access

```php
// routes/web.php

// Routes with conditional access based on user type
Route::middleware(['auth'])->group(function () {
    
    // Doctor-specific routes
    Route::middleware(['role:doctor'])->group(function () {
        Route::get('/doctor/dashboard', [DoctorController::class, 'dashboard']);
        Route::get('/doctor/my-patients', [DoctorController::class, 'myPatients']);
        Route::get('/doctor/schedule', [DoctorController::class, 'schedule']);
    });

    // Patient-specific routes
    Route::middleware(['role:patient'])->group(function () {
        Route::get('/patient/dashboard', [PatientController::class, 'dashboard']);
        Route::get('/patient/appointments', [PatientController::class, 'appointments']);
        Route::get('/patient/prescriptions', [PatientController::class, 'prescriptions']);
    });

    // Receptionist-specific routes
    Route::middleware(['role:receptionist'])->group(function () {
        Route::get('/receptionist/dashboard', [ReceptionistController::class, 'dashboard']);
        Route::get('/receptionist/checkin', [ReceptionistController::class, 'checkin']);
    });

    // MedRep-specific routes
    Route::middleware(['role:medrep'])->group(function () {
        Route::get('/medrep/dashboard', [MedrepController::class, 'dashboard']);
        Route::get('/medrep/visits', [MedrepController::class, 'visits']);
    });
});
```

### Resource Routes with RBAC

```php
// routes/web.php

// Resource routes with role-based access
Route::middleware(['role:admin,doctor'])->group(function () {
    
    // Patient resource with permission checks
    Route::middleware(['permission:patient.read'])->group(function () {
        Route::get('/patients', [PatientController::class, 'index']);
        Route::get('/patients/{patient}', [PatientController::class, 'show']);
    });

    Route::middleware(['permission:patient.write'])->group(function () {
        Route::get('/patients/create', [PatientController::class, 'create']);
        Route::post('/patients', [PatientController::class, 'store']);
        Route::get('/patients/{patient}/edit', [PatientController::class, 'edit']);
        Route::put('/patients/{patient}', [PatientController::class, 'update']);
    });

    Route::middleware(['permission:patient.delete'])->group(function () {
        Route::delete('/patients/{patient}', [PatientController::class, 'destroy']);
    });
});

// Prescription resource with clinical access
Route::middleware(['clinical.access'])->group(function () {
    Route::middleware(['permission:rx.issue'])->group(function () {
        Route::post('/prescriptions', [PrescriptionController::class, 'store']);
    });

    Route::middleware(['permission:rx.view'])->group(function () {
        Route::get('/prescriptions', [PrescriptionController::class, 'index']);
        Route::get('/prescriptions/{prescription}', [PrescriptionController::class, 'show']);
    });

    Route::middleware(['permission:rx.edit'])->group(function () {
        Route::get('/prescriptions/{prescription}/edit', [PrescriptionController::class, 'edit']);
        Route::put('/prescriptions/{prescription}', [PrescriptionController::class, 'update']);
    });

    Route::middleware(['permission:rx.download'])->group(function () {
        Route::get('/prescriptions/{prescription}/download', [PrescriptionController::class, 'download']);
    });
});
```

## Frontend Integration

### JavaScript Permission Checking

```javascript
// Check user permissions in frontend
const checkPermission = async (permission) => {
    try {
        const response = await fetch('/api/user/permissions', {
            headers: {
                'Authorization': `Bearer ${token}`,
                'X-Clinic-ID': clinicId
            }
        });
        const permissions = await response.json();
        return permissions.includes(permission);
    } catch (error) {
        console.error('Error checking permission:', error);
        return false;
    }
};

// Usage examples
const canViewPatients = await checkPermission('patient.read');
const canCreatePatients = await checkPermission('patient.write');
const canAccessClinical = await checkPermission('clinical_notes.read');

// Show/hide UI elements based on permissions
if (canViewPatients) {
    document.getElementById('patients-menu').style.display = 'block';
}

if (canCreatePatients) {
    document.getElementById('create-patient-btn').style.display = 'block';
}
```

### Vue.js Permission Directive

```javascript
// Permission directive for Vue.js
Vue.directive('permission', {
    bind(el, binding) {
        const permission = binding.value;
        const userPermissions = this.$store.getters.userPermissions;
        
        if (!userPermissions.includes(permission)) {
            el.style.display = 'none';
        }
    }
});

// Usage in templates
<template>
    <div>
        <button v-permission="'patient.write'">Create Patient</button>
        <button v-permission="'patient.delete'">Delete Patient</button>
        <div v-permission="'clinical_notes.read'">Clinical Notes</div>
    </div>
</template>
```

## Best Practices

1. **Use appropriate middleware combinations** - Combine role and permission middleware as needed
2. **Always include clinic context** - Ensure clinic ID is available for permission checks
3. **Fail securely** - Default to denying access when in doubt
4. **Document route permissions** - Keep clear documentation of who can access what
5. **Test permission boundaries** - Ensure users cannot access unauthorized routes
6. **Use specific permissions** - Prefer specific permissions over broad role checks when possible
7. **Implement frontend checks** - Use frontend permission checks for better UX
8. **Regular audits** - Review and update route permissions regularly

This comprehensive RBAC system provides fine-grained control over access to different parts of your medical application while maintaining security and usability.
