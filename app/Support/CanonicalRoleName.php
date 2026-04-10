<?php

namespace App\Support;

class CanonicalRoleName
{
    /**
     * @return array<int, string>
     */
    public static function all(): array
    {
        return [
            'Admin',
            'Lecturer',
            'Programme Coordinator',
            'Reviewer',
            'Approver',
        ];
    }

    public static function normalize(string $roleName): string
    {
        return match (strtolower($roleName)) {
            'admin' => 'Admin',
            'lecturer' => 'Lecturer',
            'coordinator', 'programme coordinator' => 'Programme Coordinator',
            'reviewer' => 'Reviewer',
            'approver' => 'Approver',
            default => str($roleName)->headline()->toString(),
        };
    }

    public static function sortOrder(string $roleName): int
    {
        return match (self::normalize($roleName)) {
            'Admin' => 1,
            'Programme Coordinator' => 2,
            'Lecturer' => 3,
            'Reviewer' => 4,
            'Approver' => 5,
            default => 99,
        };
    }
}
