<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Workflow\Services\WorkflowService;

class WorkflowDefinitionSeeder extends Seeder
{
    public function run(): void
    {
        $service = app(WorkflowService::class);

        $definitions = [
            [
                'name' => 'Course Approval Workflow',
                'description' => 'Two-step approval for course outlines and readiness.',
                'entity_type' => 'course',
                'is_active' => true,
                'config' => ['version' => 1, 'ui' => 'timeline', 'template_key' => 'course.standard.v1'],
                'steps' => [
                    [
                        'title' => 'Reviewer Assessment',
                        'description' => 'Academic reviewer checks course quality and completeness.',
                        'roles_required' => ['Reviewer', 'reviewer'],
                        'approval_level' => 1,
                        'action_type' => 'review',
                        'allow_rejection' => true,
                        'requires_comment' => false,
                    ],
                    [
                        'title' => 'Final Approval',
                        'description' => 'Approver confirms course is ready for delivery.',
                        'roles_required' => ['Approver', 'approver'],
                        'approval_level' => 2,
                        'action_type' => 'approve',
                        'allow_rejection' => true,
                        'requires_comment' => false,
                    ],
                ],
            ],
            [
                'name' => 'Course Approval Workflow v2',
                'description' => 'Three-level course workflow with coordinator gate before final approval.',
                'entity_type' => 'course',
                'is_active' => true,
                'config' => ['version' => 2, 'ui' => 'timeline', 'template_key' => 'course.extended.v2'],
                'steps' => [
                    [
                        'title' => 'Reviewer Assessment',
                        'description' => 'Reviewer validates CLO, assessment, and SLT completeness.',
                        'roles_required' => ['Reviewer', 'reviewer'],
                        'approval_level' => 1,
                        'action_type' => 'review',
                        'allow_rejection' => true,
                        'requires_comment' => false,
                    ],
                    [
                        'title' => 'Programme Coordinator Endorsement',
                        'description' => 'Coordinator checks programme-level alignment and teaching readiness.',
                        'roles_required' => ['Programme Coordinator', 'coordinator'],
                        'approval_level' => 2,
                        'action_type' => 'review',
                        'allow_rejection' => true,
                        'requires_comment' => true,
                    ],
                    [
                        'title' => 'Final Academic Approval',
                        'description' => 'Approver grants final publication approval for delivery.',
                        'roles_required' => ['Approver', 'approver'],
                        'approval_level' => 3,
                        'action_type' => 'approve',
                        'allow_rejection' => true,
                        'requires_comment' => false,
                    ],
                ],
            ],
            [
                'name' => 'Course Approval Workflow v3',
                'description' => 'Four-level governance workflow for high-assurance course releases.',
                'entity_type' => 'course',
                'is_active' => true,
                'config' => ['version' => 3, 'ui' => 'timeline', 'template_key' => 'course.governance.v3'],
                'steps' => [
                    [
                        'title' => 'Reviewer Assessment',
                        'description' => 'Initial academic quality review.',
                        'roles_required' => ['Reviewer', 'reviewer'],
                        'approval_level' => 1,
                        'action_type' => 'review',
                        'allow_rejection' => true,
                        'requires_comment' => false,
                    ],
                    [
                        'title' => 'Programme Coordinator Endorsement',
                        'description' => 'Programme fit and sequence validation.',
                        'roles_required' => ['Programme Coordinator', 'coordinator'],
                        'approval_level' => 2,
                        'action_type' => 'review',
                        'allow_rejection' => true,
                        'requires_comment' => true,
                    ],
                    [
                        'title' => 'Faculty Academic Panel Review',
                        'description' => 'Faculty panel validates governance and compliance controls.',
                        'roles_required' => ['Approver', 'approver'],
                        'approval_level' => 3,
                        'action_type' => 'review',
                        'allow_rejection' => true,
                        'requires_comment' => true,
                    ],
                    [
                        'title' => 'Final Academic Approval',
                        'description' => 'Final sign-off for curriculum publication.',
                        'roles_required' => ['Approver', 'approver'],
                        'approval_level' => 4,
                        'action_type' => 'approve',
                        'allow_rejection' => true,
                        'requires_comment' => false,
                    ],
                ],
            ],
            [
                'name' => 'Programme Approval Workflow',
                'description' => 'Coordinator and approver workflow for programme publication.',
                'entity_type' => 'programme',
                'is_active' => true,
                'config' => ['version' => 1, 'ui' => 'timeline', 'template_key' => 'programme.standard.v1'],
                'steps' => [
                    [
                        'title' => 'Programme Coordination Review',
                        'description' => 'Programme coordinator validates structure and outcomes.',
                        'roles_required' => ['Programme Coordinator', 'coordinator'],
                        'approval_level' => 1,
                        'action_type' => 'review',
                        'allow_rejection' => true,
                        'requires_comment' => false,
                    ],
                    [
                        'title' => 'Academic Approval',
                        'description' => 'Approver finalizes the programme workflow.',
                        'roles_required' => ['Approver', 'approver'],
                        'approval_level' => 2,
                        'action_type' => 'approve',
                        'allow_rejection' => true,
                        'requires_comment' => false,
                    ],
                ],
            ],
            [
                'name' => 'Programme Approval Workflow v2',
                'description' => 'Three-level programme workflow with quality-assurance checkpoint.',
                'entity_type' => 'programme',
                'is_active' => true,
                'config' => ['version' => 2, 'ui' => 'timeline', 'template_key' => 'programme.extended.v2'],
                'steps' => [
                    [
                        'title' => 'Programme Coordination Review',
                        'description' => 'Coordinator validates structure and outcomes.',
                        'roles_required' => ['Programme Coordinator', 'coordinator'],
                        'approval_level' => 1,
                        'action_type' => 'review',
                        'allow_rejection' => true,
                        'requires_comment' => false,
                    ],
                    [
                        'title' => 'Quality Assurance Review',
                        'description' => 'Reviewer checks evidence, mapping, and standards.',
                        'roles_required' => ['Reviewer', 'reviewer'],
                        'approval_level' => 2,
                        'action_type' => 'review',
                        'allow_rejection' => true,
                        'requires_comment' => true,
                    ],
                    [
                        'title' => 'Academic Approval',
                        'description' => 'Approver finalizes programme publication.',
                        'roles_required' => ['Approver', 'approver'],
                        'approval_level' => 3,
                        'action_type' => 'approve',
                        'allow_rejection' => true,
                        'requires_comment' => false,
                    ],
                ],
            ],
            [
                'name' => 'Programme Approval Workflow v3',
                'description' => 'Four-level programme governance workflow for regulated programmes.',
                'entity_type' => 'programme',
                'is_active' => true,
                'config' => ['version' => 3, 'ui' => 'timeline', 'template_key' => 'programme.governance.v3'],
                'steps' => [
                    [
                        'title' => 'Programme Coordination Review',
                        'description' => 'Coordinator checks academic framework readiness.',
                        'roles_required' => ['Programme Coordinator', 'coordinator'],
                        'approval_level' => 1,
                        'action_type' => 'review',
                        'allow_rejection' => true,
                        'requires_comment' => false,
                    ],
                    [
                        'title' => 'Quality Assurance Review',
                        'description' => 'Reviewer validates compliance and evidence pack completeness.',
                        'roles_required' => ['Reviewer', 'reviewer'],
                        'approval_level' => 2,
                        'action_type' => 'review',
                        'allow_rejection' => true,
                        'requires_comment' => true,
                    ],
                    [
                        'title' => 'Faculty Committee Review',
                        'description' => 'Approver-led committee reviews strategic and resource impact.',
                        'roles_required' => ['Approver', 'approver'],
                        'approval_level' => 3,
                        'action_type' => 'review',
                        'allow_rejection' => true,
                        'requires_comment' => true,
                    ],
                    [
                        'title' => 'Academic Senate Approval',
                        'description' => 'Final approval for institutional rollout.',
                        'roles_required' => ['Approver', 'approver'],
                        'approval_level' => 4,
                        'action_type' => 'approve',
                        'allow_rejection' => true,
                        'requires_comment' => false,
                    ],
                ],
            ],
            // ── JSU (Jadual Spesifikasi Ujian) ─────────────────────────────────
            [
                'name' => 'JSU Approval Workflow',
                'description' => 'Two-step quality review and approval for examination blueprints (JSU).',
                'entity_type' => 'jsu',
                'is_active' => true,
                'config' => ['version' => 1, 'ui' => 'timeline', 'template_key' => 'jsu.standard.v1'],
                'steps' => [
                    [
                        'title' => 'Quality Review',
                        'description' => 'Reviewer verifies CLO mapping, bloom distribution, and mark allocation.',
                        'roles_required' => ['Reviewer', 'reviewer'],
                        'approval_level' => 1,
                        'action_type' => 'review',
                        'allow_rejection' => true,
                        'requires_comment' => false,
                    ],
                    [
                        'title' => 'Final Approval',
                        'description' => 'Approver grants final sign-off for JSU publication.',
                        'roles_required' => ['Approver', 'approver'],
                        'approval_level' => 2,
                        'action_type' => 'approve',
                        'allow_rejection' => true,
                        'requires_comment' => false,
                    ],
                ],
            ],
            [
                'name' => 'JSU Approval Workflow v2',
                'description' => 'Three-step JSU workflow with programme coordinator gate.',
                'entity_type' => 'jsu',
                'is_active' => true,
                'config' => ['version' => 2, 'ui' => 'timeline', 'template_key' => 'jsu.extended.v2'],
                'steps' => [
                    [
                        'title' => 'Quality Review',
                        'description' => 'Reviewer checks bloom distribution and blueprint completeness.',
                        'roles_required' => ['Reviewer', 'reviewer'],
                        'approval_level' => 1,
                        'action_type' => 'review',
                        'allow_rejection' => true,
                        'requires_comment' => false,
                    ],
                    [
                        'title' => 'Programme Coordinator Endorsement',
                        'description' => 'Coordinator verifies JSU alignment with programme assessment strategy.',
                        'roles_required' => ['Programme Coordinator', 'coordinator'],
                        'approval_level' => 2,
                        'action_type' => 'review',
                        'allow_rejection' => true,
                        'requires_comment' => true,
                    ],
                    [
                        'title' => 'Final Approval',
                        'description' => 'Approver grants final publication sign-off.',
                        'roles_required' => ['Approver', 'approver'],
                        'approval_level' => 3,
                        'action_type' => 'approve',
                        'allow_rejection' => true,
                        'requires_comment' => false,
                    ],
                ],
            ],
        ];

        foreach ($definitions as $definition) {
            $existing = $service->listDefinitions($definition['entity_type'])
                ->firstWhere('name', $definition['name']);

            if (! $existing) {
                $service->createWorkflow($definition);
            }
        }
    }
}
