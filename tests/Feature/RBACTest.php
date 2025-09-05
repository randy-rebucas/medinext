<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Clinic;
use App\Models\UserClinicRole;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RBACTest extends TestCase
{
    use RefreshDatabase;

    protected $clinic;
    protected $superAdmin;
    protected $admin;
    protected $doctor;
    protected $receptionist;
    protected $patient;
    protected $medrep;

    protected function setUp(): void
    {
        parent::setUp();

        // Use existing clinic or create one
        $this->clinic = Clinic::first() ?? Clinic::create([
            'name' => 'Test Clinic',
            'slug' => 'test-clinic',
            'timezone' => 'Asia/Manila',
            'address' => [
                'street' => '123 Test Street',
                'city' => 'Test City',
                'province' => 'Test Province',
                'postal_code' => '1234',
                'country' => 'Philippines'
            ],
            'settings' => [
                'appointment_duration' => 30,
                'working_hours' => [
                    'monday' => ['08:00', '17:00'],
                    'tuesday' => ['08:00', '17:00'],
                    'wednesday' => ['08:00', '17:00'],
                    'thursday' => ['08:00', '17:00'],
                    'friday' => ['08:00', '17:00'],
                    'saturday' => ['08:00', '12:00'],
                    'sunday' => []
                ]
            ]
        ]);

        // Create test users
        $this->superAdmin = User::factory()->create(['email' => 'superadmin@test.com']);
        $this->admin = User::factory()->create(['email' => 'admin@test.com']);
        $this->doctor = User::factory()->create(['email' => 'doctor@test.com']);
        $this->receptionist = User::factory()->create(['email' => 'receptionist@test.com']);
        $this->patient = User::factory()->create(['email' => 'patient@test.com']);
        $this->medrep = User::factory()->create(['email' => 'medrep@test.com']);

        // Create roles and permissions
        $this->createRolesAndPermissions();
        $this->assignRolesToUsers();
    }

    protected function createRolesAndPermissions()
    {
        // Create permissions
        $permissions = [
            'patient.read', 'patient.write', 'patient.delete', 'patient.manage',
            'emr.read', 'emr.write', 'emr.delete', 'emr.manage',
            'schedule.view', 'schedule.manage',
            'rx.issue', 'rx.view', 'rx.edit', 'rx.download',
            'billing.view', 'billing.create', 'billing.edit', 'billing.manage',
            'settings.manage', 'settings.view',
            'staff.manage', 'staff.view', 'staff.create', 'staff.edit',
            'clinical_notes.read', 'clinical_notes.write',
            'medrep.schedule', 'medrep.upload', 'medrep.view',
            'tenants.manage', 'plans.manage', 'global.settings',
        ];

        foreach ($permissions as $permission) {
            Permission::create([
                'name' => ucfirst(str_replace('.', ' ', $permission)),
                'slug' => $permission,
                'description' => "Permission for {$permission}",
                'module' => explode('.', $permission)[0],
                'action' => explode('.', $permission)[1],
            ]);
        }

        // Create roles
        $roles = [
            'superadmin' => [
                'description' => 'Platform administrator',
                'is_system_role' => true,
                'permissions' => $permissions, // All permissions
            ],
            'admin' => [
                'description' => 'Clinic administrator',
                'is_system_role' => false,
                'permissions' => [
                    'patient.manage', 'emr.manage', 'schedule.manage',
                    'rx.issue', 'rx.view', 'rx.edit', 'rx.download',
                    'billing.manage', 'settings.manage', 'staff.manage',
                    'clinical_notes.read', 'clinical_notes.write',
                ],
            ],
            'doctor' => [
                'description' => 'Medical professional',
                'is_system_role' => false,
                'permissions' => [
                    'patient.read', 'patient.write', 'emr.read', 'emr.write',
                    'schedule.view', 'schedule.manage', 'rx.issue', 'rx.view',
                    'rx.edit', 'rx.download', 'clinical_notes.read', 'clinical_notes.write',
                ],
            ],
            'receptionist' => [
                'description' => 'Front desk staff',
                'is_system_role' => false,
                'permissions' => [
                    'patient.read', 'patient.write', 'schedule.view', 'schedule.manage',
                    'billing.view', 'billing.create', 'billing.edit',
                ],
            ],
            'patient' => [
                'description' => 'Patient user',
                'is_system_role' => false,
                'permissions' => [
                    'patient.read', 'schedule.view', 'rx.view', 'rx.download',
                ],
            ],
            'medrep' => [
                'description' => 'Medical representative',
                'is_system_role' => false,
                'permissions' => [
                    'medrep.schedule', 'medrep.upload', 'medrep.view',
                    'schedule.view', 'schedule.manage',
                ],
            ],
        ];

        foreach ($roles as $roleName => $roleData) {
            $role = Role::create([
                'name' => $roleName,
                'description' => $roleData['description'],
                'is_system_role' => $roleData['is_system_role'],
            ]);

            // Assign permissions to role
            $permissionIds = Permission::whereIn('slug', $roleData['permissions'])->pluck('id');
            $role->permissions()->attach($permissionIds);
        }
    }

    protected function assignRolesToUsers()
    {
        $roles = Role::all()->keyBy('name');

        // Assign roles to users in the clinic
        UserClinicRole::create([
            'user_id' => $this->superAdmin->id,
            'clinic_id' => $this->clinic->id,
            'role_id' => $roles['superadmin']->id,
        ]);

        UserClinicRole::create([
            'user_id' => $this->admin->id,
            'clinic_id' => $this->clinic->id,
            'role_id' => $roles['admin']->id,
        ]);

        UserClinicRole::create([
            'user_id' => $this->doctor->id,
            'clinic_id' => $this->clinic->id,
            'role_id' => $roles['doctor']->id,
        ]);

        UserClinicRole::create([
            'user_id' => $this->receptionist->id,
            'clinic_id' => $this->clinic->id,
            'role_id' => $roles['receptionist']->id,
        ]);

        UserClinicRole::create([
            'user_id' => $this->patient->id,
            'clinic_id' => $this->clinic->id,
            'role_id' => $roles['patient']->id,
        ]);

        UserClinicRole::create([
            'user_id' => $this->medrep->id,
            'clinic_id' => $this->clinic->id,
            'role_id' => $roles['medrep']->id,
        ]);
    }

    /** @test */
    public function super_admin_has_all_permissions()
    {
        $this->actingAs($this->superAdmin);

        $this->assertTrue($this->superAdmin->isSuperAdmin());
        $this->assertTrue($this->superAdmin->hasPermissionInClinic('patient.manage', $this->clinic->id));
        $this->assertTrue($this->superAdmin->hasPermissionInClinic('emr.manage', $this->clinic->id));
        $this->assertTrue($this->superAdmin->hasPermissionInClinic('tenants.manage', $this->clinic->id));
        $this->assertTrue($this->superAdmin->hasPermissionInClinic('global.settings', $this->clinic->id));
    }

    /** @test */
    public function admin_has_clinic_management_permissions()
    {
        $this->actingAs($this->admin);

        $this->assertTrue($this->admin->isAdmin());
        $this->assertTrue($this->admin->hasPermissionInClinic('patient.manage', $this->clinic->id));
        $this->assertTrue($this->admin->hasPermissionInClinic('emr.manage', $this->clinic->id));
        $this->assertTrue($this->admin->hasPermissionInClinic('billing.manage', $this->clinic->id));
        $this->assertTrue($this->admin->hasPermissionInClinic('staff.manage', $this->clinic->id));
        $this->assertFalse($this->admin->hasPermissionInClinic('tenants.manage', $this->clinic->id));
    }

    /** @test */
    public function doctor_has_clinical_permissions()
    {
        $this->actingAs($this->doctor);

        $this->assertTrue($this->doctor->isDoctor());
        $this->assertTrue($this->doctor->hasPermissionInClinic('patient.read', $this->clinic->id));
        $this->assertTrue($this->doctor->hasPermissionInClinic('patient.write', $this->clinic->id));
        $this->assertTrue($this->doctor->hasPermissionInClinic('emr.read', $this->clinic->id));
        $this->assertTrue($this->doctor->hasPermissionInClinic('emr.write', $this->clinic->id));
        $this->assertTrue($this->doctor->hasPermissionInClinic('rx.issue', $this->clinic->id));
        $this->assertTrue($this->doctor->hasPermissionInClinic('clinical_notes.read', $this->clinic->id));
        $this->assertTrue($this->doctor->hasPermissionInClinic('clinical_notes.write', $this->clinic->id));
        $this->assertFalse($this->doctor->hasPermissionInClinic('billing.manage', $this->clinic->id));
    }

    /** @test */
    public function receptionist_has_limited_permissions()
    {
        $this->actingAs($this->receptionist);

        $this->assertTrue($this->receptionist->isReceptionist());
        $this->assertTrue($this->receptionist->hasPermissionInClinic('patient.read', $this->clinic->id));
        $this->assertTrue($this->receptionist->hasPermissionInClinic('patient.write', $this->clinic->id));
        $this->assertTrue($this->receptionist->hasPermissionInClinic('billing.view', $this->clinic->id));
        $this->assertTrue($this->receptionist->hasPermissionInClinic('schedule.manage', $this->clinic->id));
        $this->assertFalse($this->receptionist->hasPermissionInClinic('clinical_notes.read', $this->clinic->id));
        $this->assertFalse($this->receptionist->hasPermissionInClinic('emr.read', $this->clinic->id));
        $this->assertFalse($this->receptionist->hasPermissionInClinic('rx.issue', $this->clinic->id));
    }

    /** @test */
    public function patient_has_self_service_permissions()
    {
        $this->actingAs($this->patient);

        $this->assertTrue($this->patient->isPatient());
        $this->assertTrue($this->patient->hasPermissionInClinic('patient.read', $this->clinic->id));
        $this->assertTrue($this->patient->hasPermissionInClinic('schedule.view', $this->clinic->id));
        $this->assertTrue($this->patient->hasPermissionInClinic('rx.view', $this->clinic->id));
        $this->assertTrue($this->patient->hasPermissionInClinic('rx.download', $this->clinic->id));
        $this->assertFalse($this->patient->hasPermissionInClinic('patient.write', $this->clinic->id));
        $this->assertFalse($this->patient->hasPermissionInClinic('emr.read', $this->clinic->id));
        $this->assertFalse($this->patient->hasPermissionInClinic('billing.view', $this->clinic->id));
    }

    /** @test */
    public function medrep_has_limited_permissions()
    {
        $this->actingAs($this->medrep);

        $this->assertTrue($this->medrep->isMedRep());
        $this->assertTrue($this->medrep->hasPermissionInClinic('medrep.schedule', $this->clinic->id));
        $this->assertTrue($this->medrep->hasPermissionInClinic('medrep.upload', $this->clinic->id));
        $this->assertTrue($this->medrep->hasPermissionInClinic('schedule.view', $this->clinic->id));
        $this->assertFalse($this->medrep->hasPermissionInClinic('patient.read', $this->clinic->id));
        $this->assertFalse($this->medrep->hasPermissionInClinic('emr.read', $this->clinic->id));
        $this->assertFalse($this->medrep->hasPermissionInClinic('billing.view', $this->clinic->id));
    }

    /** @test */
    public function users_can_access_clinical_notes_appropriately()
    {
        // Super admin and admin can access clinical notes
        $this->assertTrue($this->superAdmin->canAccessClinicalNotes($this->clinic->id));
        $this->assertTrue($this->admin->canAccessClinicalNotes($this->clinic->id));
        $this->assertTrue($this->doctor->canAccessClinicalNotes($this->clinic->id));

        // Receptionist, patient, and medrep cannot access clinical notes
        $this->assertFalse($this->receptionist->canAccessClinicalNotes($this->clinic->id));
        $this->assertFalse($this->patient->canAccessClinicalNotes($this->clinic->id));
        $this->assertFalse($this->medrep->canAccessClinicalNotes($this->clinic->id));
    }

    /** @test */
    public function users_can_access_patient_data_appropriately()
    {
        // Super admin, admin, doctor, and receptionist can access patient data
        $this->assertTrue($this->superAdmin->canAccessPatientData($this->clinic->id));
        $this->assertTrue($this->admin->canAccessPatientData($this->clinic->id));
        $this->assertTrue($this->doctor->canAccessPatientData($this->clinic->id));
        $this->assertTrue($this->receptionist->canAccessPatientData($this->clinic->id));
        $this->assertTrue($this->patient->canAccessPatientData($this->clinic->id));

        // Medrep cannot access patient data
        $this->assertFalse($this->medrep->canAccessPatientData($this->clinic->id));
    }

    /** @test */
    public function role_hierarchy_works_correctly()
    {
        $this->assertEquals('superadmin', $this->superAdmin->getPrimaryRole());
        $this->assertEquals('admin', $this->admin->getPrimaryRole());
        $this->assertEquals('doctor', $this->doctor->getPrimaryRole());
        $this->assertEquals('receptionist', $this->receptionist->getPrimaryRole());
        $this->assertEquals('patient', $this->patient->getPrimaryRole());
        $this->assertEquals('medrep', $this->medrep->getPrimaryRole());
    }

    /** @test */
    public function permission_checking_works_with_multiple_permissions()
    {
        $this->actingAs($this->doctor);

        // Doctor should have any of these permissions
        $this->assertTrue($this->doctor->hasAnyPermissionInClinic([
            'patient.read', 'patient.write', 'emr.read', 'emr.write'
        ], $this->clinic->id));

        // Doctor should have all of these permissions
        $this->assertTrue($this->doctor->hasAllPermissionsInClinic([
            'patient.read', 'emr.read', 'rx.issue'
        ], $this->clinic->id));

        // Doctor should not have all of these permissions (missing billing.manage)
        $this->assertFalse($this->doctor->hasAllPermissionsInClinic([
            'patient.read', 'emr.read', 'billing.manage'
        ], $this->clinic->id));
    }

    /** @test */
    public function users_can_get_their_permissions()
    {
        $this->actingAs($this->doctor);

        $permissions = $this->doctor->getPermissionsInClinic($this->clinic->id);

        $this->assertContains('patient.read', $permissions);
        $this->assertContains('patient.write', $permissions);
        $this->assertContains('emr.read', $permissions);
        $this->assertContains('emr.write', $permissions);
        $this->assertContains('rx.issue', $permissions);
        $this->assertNotContains('billing.manage', $permissions);
        $this->assertNotContains('tenants.manage', $permissions);
    }

    /** @test */
    public function role_validation_works()
    {
        $adminRole = Role::where('name', 'admin')->first();
        $doctorRole = Role::where('name', 'doctor')->first();

        // Admin role should have minimum required permissions
        $this->assertTrue($adminRole->hasMinimumPermissions());

        // Doctor role should have minimum required permissions
        $this->assertTrue($doctorRole->hasMinimumPermissions());

        // Check role capabilities
        $this->assertStringContainsString('Clinic owner/manager', $adminRole->capabilities_description);
        $this->assertStringContainsString('Medical professional', $doctorRole->capabilities_description);

        // Check security levels
        $this->assertEquals('High', $adminRole->security_level);
        $this->assertEquals('Medium-High', $doctorRole->security_level);
    }

    /** @test */
    public function system_roles_cannot_be_modified()
    {
        $superAdminRole = Role::where('name', 'superadmin')->first();
        $adminRole = Role::where('name', 'admin')->first();

        $this->assertTrue($superAdminRole->isSystemRole());
        $this->assertFalse($superAdminRole->canBeModified());
        $this->assertFalse($superAdminRole->canBeDeleted());

        $this->assertFalse($adminRole->isSystemRole());
        $this->assertTrue($adminRole->canBeModified());
    }
}
