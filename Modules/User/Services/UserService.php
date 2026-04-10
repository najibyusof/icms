<?php

namespace Modules\User\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function paginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = User::query()->with('roles')->orderBy('name');

        if (!empty($filters['search'])) {
            $s = $filters['search'];
            $query->where(fn ($q) => $q
                ->where('name', 'like', "%{$s}%")
                ->orWhere('email', 'like', "%{$s}%")
                ->orWhere('staff_id', 'like', "%{$s}%")
            );
        }

        if (!empty($filters['role'])) {
            $query->role($filters['role']);
        }

        if (!empty($filters['faculty'])) {
            $query->where('faculty', 'like', '%' . $filters['faculty'] . '%');
        }

        return $query->paginate($perPage);
    }

    public function create(array $data): User
    {
        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'staff_id' => $data['staff_id'] ?? null,
            'faculty'  => $data['faculty'] ?? null,
            'password' => Hash::make($data['password']),
        ]);

        if (!empty($data['role'])) {
            $user->syncRoles([$data['role']]);
        }

        return $user->load('roles');
    }

    public function update(User $user, array $data): User
    {
        $fields = [
            'name'     => $data['name'],
            'email'    => $data['email'],
            'staff_id' => $data['staff_id'] ?? null,
            'faculty'  => $data['faculty'] ?? null,
        ];

        if (!empty($data['password'])) {
            $fields['password'] = Hash::make($data['password']);
        }

        $user->update($fields);

        if (!empty($data['role'])) {
            $user->syncRoles([$data['role']]);
        }

        return $user->fresh('roles');
    }

    public function delete(User $user): void
    {
        $user->delete();
    }
}

