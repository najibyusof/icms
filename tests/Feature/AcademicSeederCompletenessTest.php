<?php

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Course\Models\Course;
use Modules\Programme\Models\Programme;
use Tests\TestCase;

class AcademicSeederCompletenessTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_database_seeders_create_complete_programme_and_course_data(): void
    {
        $this->seed(DatabaseSeeder::class);

        $dcs = Programme::query()->where('code', 'DCS')->firstOrFail();
        $dis = Programme::query()->where('code', 'DIS')->firstOrFail();
        $csug = Programme::query()->where('code', 'CS-UG-001')->firstOrFail();

        $this->assertNotEmpty($dcs->description);
        $this->assertNotEmpty($dcs->accreditation_body);
        $this->assertGreaterThanOrEqual(1, $dcs->programmePLOs()->count());
        $this->assertGreaterThanOrEqual(1, $dcs->programmePEOs()->count());
        $this->assertGreaterThanOrEqual(1, $dcs->studyPlans()->count());

        $this->assertNotEmpty($dis->description);
        $this->assertNotEmpty($dis->accreditation_body);
        $this->assertGreaterThanOrEqual(1, $dis->programmePLOs()->count());
        $this->assertGreaterThanOrEqual(1, $dis->programmePEOs()->count());
        $this->assertGreaterThanOrEqual(1, $dis->studyPlans()->count());

        $this->assertNotEmpty($csug->description);
        $this->assertNotEmpty($csug->accreditation_body);
        $this->assertGreaterThanOrEqual(5, $csug->courses()->count());
        $this->assertGreaterThanOrEqual(1, $csug->programmePLOs()->count());
        $this->assertGreaterThanOrEqual(1, $csug->programmePEOs()->count());
        $this->assertGreaterThanOrEqual(1, $csug->studyPlans()->count());

        $this->assertSeededCourseGraphIsComplete('CSE1013');
        $this->assertSeededCourseGraphIsComplete('CSE2023');
        $this->assertSeededCourseGraphIsComplete('CSE3053');
        $this->assertSeededCourseGraphIsComplete('CSC1013');
        $this->assertSeededCourseGraphIsComplete('CSC2023');
    }

    private function assertSeededCourseGraphIsComplete(string $courseCode): void
    {
        $course = Course::query()->where('code', $courseCode)->firstOrFail();

        $this->assertNotNull($course->programme_id, "Course {$courseCode} should belong to a programme.");
        $this->assertGreaterThanOrEqual(3, $course->clos()->count(), "Course {$courseCode} should have CLO rows.");
        $this->assertGreaterThanOrEqual(2, $course->requisites()->count(), "Course {$courseCode} should have requisite rows.");
        $this->assertGreaterThanOrEqual(3, $course->assessments()->count(), "Course {$courseCode} should have assessment rows.");
        $this->assertGreaterThanOrEqual(3, $course->sltItems()->count(), "Course {$courseCode} should have SLT rows.");
        $this->assertGreaterThanOrEqual(1, $course->topics()->count(), "Course {$courseCode} should have topic rows.");
        $this->assertGreaterThanOrEqual(1, $course->groups()->count() + $course->programme?->programmeCourses()->where('course_id', $course->id)->count(), "Course {$courseCode} should be placed in an academic structure.");
        $this->assertGreaterThanOrEqual(1, $course->programme?->programmePLOs()->count() ?? 0, "Course {$courseCode} programme should have PLO rows.");
        $this->assertGreaterThanOrEqual(1, $course->programme?->studyPlans()->count() ?? 0, "Course {$courseCode} programme should have study plans.");

        $mappingCount = \Modules\Programme\Models\CLOPLOMapping::query()
            ->where('course_id', $course->id)
            ->count();

        $this->assertGreaterThanOrEqual(1, $mappingCount, "Course {$courseCode} should have CLO-PLO mappings.");
    }
}
