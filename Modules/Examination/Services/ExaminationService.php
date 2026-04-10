<?php

namespace Modules\Examination\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Examination\DTOs\SubmitExaminationDTO;
use Modules\Examination\Models\Examination;
use Modules\Workflow\Events\WorkflowSubmitted;
use Modules\Workflow\Models\WorkflowApproval;
use Modules\Workflow\Models\WorkflowInstance;

class ExaminationService
{
    /**
     * @return Collection<int, Examination>
     */
    public function list(): Collection
    {
        return Examination::query()
            ->with(['course.programme', 'group', 'submitter', 'workflow.approvals'])
            ->latest('id')
            ->get();
    }

    public function submit(SubmitExaminationDTO $dto): Examination
    {
        return DB::transaction(function () use ($dto): Examination {
            $reviewer = User::role('reviewer')->firstOrFail();
            $approver = User::role('approver')->firstOrFail();

            $examination = Examination::query()->create([
                'course_id' => $dto->courseId,
                'group_id' => $dto->groupId,
                'submitted_by' => $dto->submittedBy,
                'title' => $dto->title,
                'exam_date' => $dto->examDate,
                'status' => 'submitted',
                'metadata' => $dto->metadata,
            ]);

            $workflow = WorkflowInstance::query()->create([
                'workflowable_type' => Examination::class,
                'workflowable_id' => $examination->id,
                'initiated_by' => $dto->submittedBy,
                'status' => 'in_review',
                'current_stage' => 1,
            ]);

            WorkflowApproval::query()->create([
                'workflow_instance_id' => $workflow->id,
                'reviewer_id' => $reviewer->id,
                'role_name' => 'reviewer',
                'stage' => 1,
                'status' => 'pending',
            ]);

            WorkflowApproval::query()->create([
                'workflow_instance_id' => $workflow->id,
                'reviewer_id' => $approver->id,
                'role_name' => 'approver',
                'stage' => 2,
                'status' => 'queued',
            ]);

            event(new WorkflowSubmitted($workflow->fresh('workflowable')));

            return $examination->fresh(['course.programme', 'group', 'submitter', 'workflow.approvals']);
        });
    }
}
