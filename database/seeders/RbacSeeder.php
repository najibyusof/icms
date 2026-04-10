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
    private array $roles = [
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
        'programme.view',
        'programme.create',
        'course.view',
        'course.create',
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

        foreach ($this->roles as $roleName) {
            $role = Role::query()->firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);

            if ($roleName === 'admin') {
                $role->syncPermissions($this->permissions);
                continue;
            }

            $role->syncPermissions(match ($roleName) {
                'lecturer' => ['programme.view', 'course.view', 'group.view', 'examination.view', 'examination.submit', 'notification.view'],
                'coordinator' => ['programme.view', 'course.view', 'group.view', 'group.create', 'workflow.view', 'notification.view'],
                'reviewer' => ['workflow.view', 'workflow.review', 'examination.view', 'notification.view'],
                'approver' => ['workflow.view', 'workflow.review', 'examination.view', 'notification.view'],
                default => [],
            });
        }

        $admin = User::query()->firstOrCreate(
            ['email' => 'admin@academic.local'],
            [
                'name' => 'System Admin',
                'password' => Hash::make('password'),
            ]
        );

        $admin->assignRole('admin');
    }
}
