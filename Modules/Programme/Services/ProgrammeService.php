<?php

namespace Modules\Programme\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\Paginator;
use Modules\Programme\Models\Programme;
use Modules\Programme\Models\ProgrammeCourse;
use Modules\Programme\Models\ProgrammePEO;
use Modules\Programme\Models\ProgrammePLO;
use Modules\Programme\Models\StudyPlan;
use Modules\Programme\Models\StudyPlanCourse;
use Modules\Workflow\Models\WorkflowInstance;
use Modules\Workflow\Models\WorkflowSetting;
use Modules\Workflow\Services\WorkflowService;

class ProgrammeService
{
    /**
     * @return Collection<int, Programme>
     */
    public function list(): Collection
    {
        return Programme::query()
            ->withCount(['courses', 'groups', 'programmePLOs', 'programmePEOs', 'studyPlans'])
            ->orderBy('name')
            ->get();
    }

    /**
     * Get a single programme with full details
     */
    public function getWithDetails(Programme $programme): Programme
    {
        return Programme::with([
            'courses' => fn($q) => $q->orderBy('name'),
            'programmePLOs' => fn($q) => $q->orderBy('sequence_order'),
            'programmePEOs' => fn($q) => $q->orderBy('sequence_order'),
            'studyPlans' => fn($q) => $q->where('is_active', true),
            'programmeChair:id,name,email',
        ])->findOrFail($programme->id);
    }

    public function create(array $payload): Programme
    {
        return Programme::query()->create($payload);
    }

    public function update(Programme $programme, array $payload): Programme
    {
        $programme->update($payload);
        return $programme;
    }

    public function delete(Programme $programme): bool
    {
        return $programme->delete();
    }

    /**
     * Assign a programme chair to the programme
     */
    public function assignProgrammeChair(Programme $programme, User $user): Programme
    {
        $programme->update(['programme_chair_id' => $user->id]);
        return $programme;
    }

    /**
     * Submit programme for approval
     */
    public function submitForApproval(Programme $programme, User $user): Programme
    {
        $workflowService = app(WorkflowService::class);

        $instance = WorkflowInstance::query()
            ->where('entity_type', Programme::class)
            ->where('entity_id', $programme->id)
            ->latest('id')
            ->first();

        if (! $instance || in_array($instance->status, ['approved', 'rejected', 'withdrawn'], true)) {
            $instance = $workflowService->startWorkflowForEntityTypeAndVersion(
                $programme,
                $user,
                $this->defaultWorkflowTemplateVersion()
            );
        }

        if ($instance->status === 'draft') {
            $workflowService->submit($instance, $user);
        }

        $programme->refresh();

        return $programme;
    }

    /**
     * Create PLO for programme
     */
    public function createPLO(Programme $programme, array $data): ProgrammePLO
    {
        return $programme->programmePLOs()->create($data);
    }

    /**
     * Update PLO
     */
    public function updatePLO(ProgrammePLO $plo, array $data): ProgrammePLO
    {
        $plo->update($data);
        return $plo;
    }

    /**
     * Delete PLO
     */
    public function deletePLO(ProgrammePLO $plo): bool
    {
        return $plo->delete();
    }

    /**
     * Create PEO for programme
     */
    public function createPEO(Programme $programme, array $data): ProgrammePEO
    {
        return $programme->programmePEOs()->create($data);
    }

    /**
     * Update PEO
     */
    public function updatePEO(ProgrammePEO $peo, array $data): ProgrammePEO
    {
        $peo->update($data);
        return $peo;
    }

    /**
     * Delete PEO
     */
    public function deletePEO(ProgrammePEO $peo): bool
    {
        return $peo->delete();
    }

    /**
     * Create study plan
     */
    public function createStudyPlan(Programme $programme, array $data): StudyPlan
    {
        $courses = $data['courses'] ?? [];
        unset($data['courses']);

        $studyPlan = $programme->studyPlans()->create($data);

        if (!empty($courses)) {
            foreach ($courses as $course) {
                $studyPlan->courses()->create($course);
            }
        }

        return $studyPlan;
    }

    /**
     * Update study plan
     */
    public function updateStudyPlan(StudyPlan $studyPlan, array $data): StudyPlan
    {
        $courses = $data['courses'] ?? [];
        unset($data['courses']);

        $studyPlan->update($data);

        if (isset($data['courses'])) {
            $studyPlan->courses()->delete();
            foreach ($courses as $course) {
                $studyPlan->courses()->create($course);
            }
        }

        return $studyPlan;
    }

    /**
     * Delete study plan
     */
    public function deleteStudyPlan(StudyPlan $studyPlan): bool
    {
        return $studyPlan->delete();
    }

    /**
     * Add course to study plan
     */
    public function addCourseToStudyPlan(StudyPlan $studyPlan, array $data): StudyPlanCourse
    {
        return $studyPlan->courses()->create($data);
    }

    /**
     * Remove course from study plan
     */
    public function removeCourseFromStudyPlan(StudyPlanCourse $course): bool
    {
        return $course->delete();
    }

    /**
     * Get study plan courses grouped by semester
     */
    public function getStudyPlanCoursesBysemester(StudyPlan $studyPlan): array
    {
        $courses = $studyPlan->courses()
            ->with('course:id,code,name,credit_hours')
            ->orderBy('year')
            ->orderBy('semester')
            ->get();

        $grouped = [];
        foreach ($courses as $course) {
            $key = "Year {$course->year}, Semester {$course->semester}";
            if (!isset($grouped[$key])) {
                $grouped[$key] = [];
            }
            $grouped[$key][] = $course;
        }

        return $grouped;
    }

    /**
     * Get all available lecturers/users who can be programme chairs
     */
    public function getAvailableProgrammeChairs()
    {
        return User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['Lecturer', 'Programme Coordinator', 'Admin']);
        })
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
    }

    /**
     * Get programme statistics
     */
    public function getProgrammeStats(Programme $programme): array
    {
        return [
            'total_courses' => $programme->courses()->count(),
            'total_plos' => $programme->programmePLOs()->count(),
            'total_peos' => $programme->programmePEOs()->count(),
            'total_study_plans' => $programme->studyPlans()->count(),
            'mapped_clos' => $this->countMappedCLOs($programme),
        ];
    }

    /**
     * Count mapped CLOs
     */
    private function countMappedCLOs(Programme $programme): int
    {
        return \Modules\Programme\Models\CLOPLOMapping::whereIn(
            'course_id',
            $programme->courses()->pluck('id')
        )->distinct('course_id', 'programme_plo_id')->count();
    }

    private function defaultWorkflowTemplateVersion(): int
    {
        $dbVersion = WorkflowSetting::get('default_version.programme');

        if ($dbVersion !== null && is_numeric($dbVersion) && (int) $dbVersion > 0) {
            return (int) $dbVersion;
        }

        $version = config('workflow.templates.default_versions.programme', 1);

        return is_numeric($version) && (int) $version > 0 ? (int) $version : 1;
    }
}

