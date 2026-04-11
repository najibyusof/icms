<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Modules\Course\Models\Course;
use Modules\Examination\Models\Examination;
use Modules\Group\Models\AcademicGroup;
use Modules\Programme\Models\Programme;
use Modules\Workflow\Models\Workflow;
use Modules\Workflow\Models\WorkflowInstance;
use Modules\Workflow\Models\WorkflowLog;
use Modules\Workflow\Models\WorkflowStep;

class ScenarioSeeder extends Seeder
{
    public function run(): void
    {
        $users = $this->seedUsers();

        $programmeDcs = Programme::query()->updateOrCreate(
            ['code' => 'DCS'],
            [
                'name' => 'Diploma in Computer Science',
                'level' => 'Diploma',
                'duration_semesters' => 6,
                'is_active' => true,
            ]
        );

        $programmeDis = Programme::query()->updateOrCreate(
            ['code' => 'DIS'],
            [
                'name' => 'Diploma in Information Systems',
                'level' => 'Diploma',
                'duration_semesters' => 6,
                'is_active' => true,
            ]
        );

        $groupDcs1 = AcademicGroup::query()->updateOrCreate(
            [
                'programme_id' => $programmeDcs->id,
                'name' => 'DCS-A',
                'intake_year' => 2025,
                'semester' => 1,
            ],
            [
                'coordinator_id' => $users['coordinator']->id,
                'is_active' => true,
            ]
        );

        $groupDcs2 = AcademicGroup::query()->updateOrCreate(
            [
                'programme_id' => $programmeDcs->id,
                'name' => 'DCS-B',
                'intake_year' => 2025,
                'semester' => 2,
            ],
            [
                'coordinator_id' => $users['coordinator']->id,
                'is_active' => true,
            ]
        );

        $groupDis1 = AcademicGroup::query()->updateOrCreate(
            [
                'programme_id' => $programmeDis->id,
                'name' => 'DIS-A',
                'intake_year' => 2025,
                'semester' => 1,
            ],
            [
                'coordinator_id' => $users['coordinator']->id,
                'is_active' => true,
            ]
        );

        $courseDraft = Course::query()->updateOrCreate(
            ['code' => 'CSE1013'],
            [
                'programme_id' => $programmeDcs->id,
                'lecturer_id' => $users['lecturer1']->id,
                'resource_person_id' => $users['lecturer2']->id,
                'vetter_id' => $users['reviewer']->id,
                'name' => 'Programming Fundamentals',
                'credit_hours' => 3,
                'is_active' => true,
                'status' => 'draft',
                'submitted_at' => null,
            ]
        );

        $courseStage1Pending = Course::query()->updateOrCreate(
            ['code' => 'CSE2023'],
            [
                'programme_id' => $programmeDcs->id,
                'lecturer_id' => $users['lecturer1']->id,
                'resource_person_id' => $users['lecturer2']->id,
                'vetter_id' => $users['reviewer']->id,
                'name' => 'Data Structures',
                'credit_hours' => 3,
                'is_active' => true,
                'status' => 'submitted',
                'submitted_at' => Carbon::now()->subDays(2),
            ]
        );

        $courseStage2Pending = Course::query()->updateOrCreate(
            ['code' => 'CSE3053'],
            [
                'programme_id' => $programmeDis->id,
                'lecturer_id' => $users['lecturer2']->id,
                'resource_person_id' => $users['lecturer1']->id,
                'vetter_id' => $users['reviewer']->id,
                'name' => 'Database Systems',
                'credit_hours' => 3,
                'is_active' => true,
                'status' => 'in_review',
                'submitted_at' => Carbon::now()->subDays(4),
            ]
        );

        $courseApproved = Course::query()->updateOrCreate(
            ['code' => 'CSE4014'],
            [
                'programme_id' => $programmeDcs->id,
                'lecturer_id' => $users['lecturer1']->id,
                'resource_person_id' => $users['lecturer2']->id,
                'vetter_id' => $users['reviewer']->id,
                'name' => 'Software Engineering',
                'credit_hours' => 4,
                'is_active' => true,
                'status' => 'approved',
                'submitted_at' => Carbon::now()->subDays(10),
            ]
        );

        $courseRejected = Course::query()->updateOrCreate(
            ['code' => 'CSE4023'],
            [
                'programme_id' => $programmeDis->id,
                'lecturer_id' => $users['lecturer2']->id,
                'resource_person_id' => $users['lecturer1']->id,
                'vetter_id' => $users['reviewer']->id,
                'name' => 'Computer Networks',
                'credit_hours' => 3,
                'is_active' => true,
                'status' => 'rejected',
                'submitted_at' => Carbon::now()->subDays(8),
            ]
        );

        $courseDraft->groups()->syncWithoutDetaching([$groupDcs1->id]);
        $courseStage1Pending->groups()->syncWithoutDetaching([$groupDcs1->id]);
        $courseStage2Pending->groups()->syncWithoutDetaching([$groupDis1->id]);
        $courseApproved->groups()->syncWithoutDetaching([$groupDcs2->id]);
        $courseRejected->groups()->syncWithoutDetaching([$groupDis1->id]);

        foreach ([$courseDraft, $courseStage1Pending, $courseStage2Pending, $courseApproved, $courseRejected] as $course) {
            $this->seedCourseDetailRows($course);
        }

        $this->seedCourseWorkflow($courseStage1Pending, $users['lecturer1']->id, 'in_progress', 1, [
            ['action' => 'submitted', 'user_id' => $users['lecturer1']->id, 'comment' => 'Submitted for review.', 'step_number' => 1, 'created_at' => Carbon::now()->subDays(2)],
        ]);

        $this->seedCourseWorkflow($courseStage2Pending, $users['lecturer2']->id, 'in_progress', 2, [
            ['action' => 'submitted', 'user_id' => $users['lecturer2']->id, 'comment' => 'Submitted for review.', 'step_number' => 1, 'created_at' => Carbon::now()->subDays(4)],
            ['action' => 'approved', 'user_id' => $users['reviewer']->id, 'comment' => 'CLO mapping is complete.', 'step_number' => 1, 'created_at' => Carbon::now()->subDays(3)],
        ]);

        $this->seedCourseWorkflow($courseApproved, $users['lecturer1']->id, 'approved', null, [
            ['action' => 'submitted', 'user_id' => $users['lecturer1']->id, 'comment' => 'Submitted for review.', 'step_number' => 1, 'created_at' => Carbon::now()->subDays(10)],
            ['action' => 'approved', 'user_id' => $users['reviewer']->id, 'comment' => 'Meets curriculum standards.', 'step_number' => 1, 'created_at' => Carbon::now()->subDays(9)],
            ['action' => 'approved', 'user_id' => $users['approver']->id, 'comment' => 'Approved for delivery.', 'step_number' => 2, 'created_at' => Carbon::now()->subDays(8)],
        ]);

        $this->seedCourseWorkflow($courseRejected, $users['lecturer2']->id, 'rejected', null, [
            ['action' => 'submitted', 'user_id' => $users['lecturer2']->id, 'comment' => 'Submitted for review.', 'step_number' => 1, 'created_at' => Carbon::now()->subDays(8)],
            ['action' => 'rejected', 'user_id' => $users['reviewer']->id, 'comment' => 'Assessment weightage does not align with CLO coverage.', 'step_number' => 1, 'created_at' => Carbon::now()->subDays(7)],
        ]);

        $examPending = Examination::query()->updateOrCreate(
            ['title' => 'Data Structures Midterm'],
            [
                'course_id' => $courseStage1Pending->id,
                'group_id' => $groupDcs1->id,
                'submitted_by' => $users['lecturer1']->id,
                'exam_date' => Carbon::now()->addWeeks(3)->toDateString(),
                'status' => 'submitted',
                'metadata' => ['type' => 'written', 'duration_minutes' => 120],
            ]
        );

        $examApproved = Examination::query()->updateOrCreate(
            ['title' => 'Software Engineering Final'],
            [
                'course_id' => $courseApproved->id,
                'group_id' => $groupDcs2->id,
                'submitted_by' => $users['lecturer1']->id,
                'exam_date' => Carbon::now()->addWeeks(5)->toDateString(),
                'status' => 'approved',
                'metadata' => ['type' => 'project', 'duration_minutes' => 180],
            ]
        );

        $examRejected = Examination::query()->updateOrCreate(
            ['title' => 'Computer Networks Quiz'],
            [
                'course_id' => $courseRejected->id,
                'group_id' => $groupDis1->id,
                'submitted_by' => $users['lecturer2']->id,
                'exam_date' => Carbon::now()->addWeeks(2)->toDateString(),
                'status' => 'rejected',
                'metadata' => ['type' => 'quiz', 'duration_minutes' => 60],
            ]
        );

    }

    /**
     * @return array<string, User>
     */
    private function seedUsers(): array
    {
        $admin = $this->upsertUser(
            email: 'sample.admin@academic.local',
            name: 'Sample Admin',
            staffId: 'ADM1001',
            faculty: 'Academic Affairs',
            roles: ['Admin', 'admin']
        );

        $coordinator = $this->upsertUser(
            email: 'coordinator@academic.local',
            name: 'Programme Coordinator',
            staffId: 'COO1001',
            faculty: 'School of Computing',
            roles: ['Programme Coordinator', 'coordinator']
        );

        $lecturer1 = $this->upsertUser(
            email: 'lecturer.one@academic.local',
            name: 'Lecturer One',
            staffId: 'LEC1001',
            faculty: 'School of Computing',
            roles: ['Lecturer', 'lecturer']
        );

        $lecturer2 = $this->upsertUser(
            email: 'lecturer.two@academic.local',
            name: 'Lecturer Two',
            staffId: 'LEC1002',
            faculty: 'School of Computing',
            roles: ['Lecturer', 'lecturer']
        );

        $reviewer = $this->upsertUser(
            email: 'reviewer@academic.local',
            name: 'Workflow Reviewer',
            staffId: 'REV1001',
            faculty: 'Quality Assurance',
            roles: ['Reviewer', 'reviewer']
        );

        $approver = $this->upsertUser(
            email: 'approver@academic.local',
            name: 'Workflow Approver',
            staffId: 'APR1001',
            faculty: 'Academic Senate',
            roles: ['Approver', 'approver']
        );

        return [
            'admin' => $admin,
            'coordinator' => $coordinator,
            'lecturer1' => $lecturer1,
            'lecturer2' => $lecturer2,
            'reviewer' => $reviewer,
            'approver' => $approver,
        ];
    }

    /**
     * @param array<int, string> $roles
     */
    private function upsertUser(string $email, string $name, string $staffId, string $faculty, array $roles): User
    {
        $user = User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'staff_id' => $staffId,
                'faculty' => $faculty,
                'password' => Hash::make('password'),
            ]
        );

        $user->syncRoles($roles);

        return $user;
    }

    private function seedCourseDetailRows(Course $course): void
    {
        $course->clos()->delete();
        $course->requisites()->delete();
        $course->assessments()->delete();
        $course->topics()->delete();
        $course->sltItems()->delete();

        $course->clos()->createMany([
            ['clo_no' => 1, 'statement' => 'Explain core concepts related to '.$course->code.'.', 'bloom_level' => 'C2'],
            ['clo_no' => 2, 'statement' => 'Apply techniques to solve domain-specific problems.', 'bloom_level' => 'C3'],
            ['clo_no' => 3, 'statement' => 'Evaluate and improve proposed solutions.', 'bloom_level' => 'C5'],
        ]);

        $course->requisites()->createMany([
            ['type' => 'prerequisite', 'course_code' => 'MTH1013', 'course_name' => 'Discrete Mathematics'],
            ['type' => 'corequisite', 'course_code' => 'CSE1023', 'course_name' => 'Computer Architecture'],
        ]);

        $course->assessments()->createMany([
            ['component' => 'Quiz', 'weightage' => 20, 'remarks' => 'Continuous assessment'],
            ['component' => 'Assignment', 'weightage' => 30, 'remarks' => 'Individual and group tasks'],
            ['component' => 'Final Examination', 'weightage' => 50, 'remarks' => 'Comprehensive coverage'],
        ]);

        $course->topics()->createMany([
            ['week_no' => 1, 'title' => 'Course Introduction', 'learning_activity' => 'Lecture and guided discussion'],
            ['week_no' => 2, 'title' => 'Core Concepts', 'learning_activity' => 'Lecture and lab walkthrough'],
            ['week_no' => 3, 'title' => 'Applied Practice', 'learning_activity' => 'Hands-on lab exercise'],
            ['week_no' => 4, 'title' => 'Case Study', 'learning_activity' => 'Team activity and presentation'],
        ]);

        $course->sltItems()->createMany([
            ['activity' => 'Lecture', 'f2f_hours' => 14, 'non_f2f_hours' => 2, 'independent_hours' => 8, 'total_hours' => 24],
            ['activity' => 'Lab/Tutorial', 'f2f_hours' => 12, 'non_f2f_hours' => 4, 'independent_hours' => 10, 'total_hours' => 26],
            ['activity' => 'Assessment Preparation', 'f2f_hours' => 0, 'non_f2f_hours' => 8, 'independent_hours' => 12, 'total_hours' => 20],
        ]);
    }

    /**
     * @param array<int, array<string, mixed>> $logs
     */
    private function seedCourseWorkflow(Course $course, int $createdBy, string $workflowStatus, ?int $currentStage, array $logs): void
    {
        $workflow = Workflow::query()->where('name', 'Course Approval Workflow')->first();

        if (! $workflow) {
            return;
        }

        $currentStepId = null;
        if ($currentStage !== null) {
            $currentStepId = WorkflowStep::query()
                ->where('workflow_id', $workflow->id)
                ->where('step_number', $currentStage)
                ->value('id');
        }

        $workflow = WorkflowInstance::query()->updateOrCreate(
            [
                'workflow_id' => $workflow->id,
                'entity_type' => Course::class,
                'entity_id' => $course->id,
            ],
            [
                'created_by' => $createdBy,
                'submitted_by' => $createdBy,
                'submitted_at' => Carbon::now()->subDays(2),
                'status' => $workflowStatus,
                'current_step_id' => $currentStepId,
                'approved_at' => $workflowStatus === 'approved' ? Carbon::now()->subDays(8) : null,
                'rejected_at' => $workflowStatus === 'rejected' ? Carbon::now()->subDays(7) : null,
                'rejection_reason' => $workflowStatus === 'rejected' ? 'Seeded rejection scenario.' : null,
            ]
        );

        foreach ($logs as $log) {
            $stepId = null;
            if (isset($log['step_number'])) {
                $stepId = WorkflowStep::query()
                    ->where('workflow_id', $workflow->workflow_id)
                    ->where('step_number', $log['step_number'])
                    ->value('id');
            }

            WorkflowLog::query()->updateOrCreate(
                [
                    'workflow_instance_id' => $workflow->id,
                    'action' => $log['action'],
                    'user_id' => $log['user_id'],
                    'workflow_step_id' => $stepId,
                ],
                [
                    'comment' => $log['comment'] ?? null,
                    'created_at' => $log['created_at'] ?? now(),
                ]
            );
        }
    }
}
