<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RbacSeeder extends Seeder
{
    /**
     * @var array<int, string>
     */
    private array $defaultRoles = [
        'Admin',
        'Lecturer',
        'Programme Coordinator',
        'Reviewer',
        'Approver',
    ];

    /**
     * Legacy aliases kept to avoid breaking existing module tests and role lookups.
     *
     * @var array<int, string>
     */
    private array $legacyRoles = [
        'admin',
        'lecturer',
        'coordinator',
        'reviewer',
        'approver',
    ];

    /**
     * @var array<int, string>
     */
    private array $permissions = [
        'user.view',
        'user.create',
        'user.update',
        'user.delete',
        'programme.view',
        'programme.create',
        'course.view',
        'course.create',
        'course.update',
        'course.submit',
        'group.view',
        'group.create',
        'workflow.view',
        'workflow.review',
        'examination.view',
        'examination.submit',
        'notification.view',
        'integration.sso.validate',
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach ($this->permissions as $permission) {
            Permission::query()->firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        foreach (array_merge($this->defaultRoles, $this->legacyRoles) as $roleName) {
            $role = Role::query()->firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);

            if (in_array($roleName, ['admin', 'Admin'], true)) {
                $role->syncPermissions($this->permissions);
                continue;
            }

            $role->syncPermissions(match ($roleName) {
                'lecturer', 'Lecturer' => ['programme.view', 'course.view', 'group.view', 'examination.view', 'examination.submit', 'notification.view'],
                'coordinator', 'Programme Coordinator' => ['programme.view', 'course.view', 'group.view', 'group.create', 'workflow.view', 'notification.view'],
                'reviewer', 'Reviewer' => ['workflow.view', 'workflow.review', 'examination.view', 'notification.view'],
                'approver', 'Approver' => ['workflow.view', 'workflow.review', 'examination.view', 'notification.view'],
                default => [],
            });
        }

        $admin = User::query()->firstOrCreate(
            ['email' => 'admin@academic.local'],
            [
                'name' => 'System Admin',
                'staff_id' => 'ADM0001',
                'password' => Hash::make('password'),
            ]
        );

        if (! $admin->staff_id) {
            $admin->forceFill(['staff_id' => 'ADM0001'])->save();
        }

        $admin->syncRoles(['Admin', 'admin']);
    }
}
