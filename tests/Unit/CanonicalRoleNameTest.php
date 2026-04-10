<?php

namespace Tests\Unit;

use App\Support\CanonicalRoleName;
use PHPUnit\Framework\TestCase;

class CanonicalRoleNameTest extends TestCase
{
    public function test_it_normalizes_legacy_aliases_to_canonical_labels(): void
    {
        $this->assertSame('Admin', CanonicalRoleName::normalize('admin'));
        $this->assertSame('Lecturer', CanonicalRoleName::normalize('lecturer'));
        $this->assertSame('Programme Coordinator', CanonicalRoleName::normalize('coordinator'));
        $this->assertSame('Reviewer', CanonicalRoleName::normalize('reviewer'));
        $this->assertSame('Approver', CanonicalRoleName::normalize('approver'));
    }

    public function test_it_keeps_canonical_labels_and_headlines_unknown_labels(): void
    {
        $this->assertSame('Programme Coordinator', CanonicalRoleName::normalize('Programme Coordinator'));
        $this->assertSame('External Auditor', CanonicalRoleName::normalize('external auditor'));
    }

    public function test_it_exposes_stable_sort_order_for_canonical_roles(): void
    {
        $this->assertSame(1, CanonicalRoleName::sortOrder('Admin'));
        $this->assertSame(2, CanonicalRoleName::sortOrder('coordinator'));
        $this->assertSame(3, CanonicalRoleName::sortOrder('Lecturer'));
        $this->assertSame(4, CanonicalRoleName::sortOrder('reviewer'));
        $this->assertSame(5, CanonicalRoleName::sortOrder('Approver'));
        $this->assertSame(99, CanonicalRoleName::sortOrder('external auditor'));
    }
}
