<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;
use Modules\Course\Models\Course;
use Modules\Course\Models\CourseClo;
use Modules\Jsu\Models\Jsu;

class JsuSampleSeeder extends Seeder
{
    public function run(): void
    {
        $lecturer = User::query()->where('email', 'lecturer.one@academic.local')->first();
        $reviewer = User::query()->where('email', 'reviewer@academic.local')->first();
        $approver = User::query()->where('email', 'approver@academic.local')->first();

        if (! $lecturer || ! $reviewer || ! $approver) {
            // ScenarioSeeder owns canonical sample identities used across modules.
            return;
        }

        $draftCourse = $this->pickCourse(['CSE1013', 'CSC1013']);
        $submittedCourse = $this->pickCourse(['CSE2023', 'CSC2013']);
        $activeCourse = $this->pickCourse(['CSE4014', 'CSC2023']);

        if (! $draftCourse || ! $submittedCourse || ! $activeCourse) {
            return;
        }

        $this->ensureClos($draftCourse);
        $this->ensureClos($submittedCourse);
        $this->ensureClos($activeCourse);

        $draft = Jsu::query()->updateOrCreate(
            ['title' => 'JSU Sample Draft - Programming Fundamentals'],
            [
                'course_id' => $draftCourse->id,
                'created_by' => $lecturer->id,
                'approved_by' => null,
                'activated_by' => null,
                'academic_session' => '2025/2026-1',
                'exam_type' => 'midterm',
                'total_questions' => 0,
                'total_marks' => 40,
                'duration_minutes' => 90,
                'status' => 'draft',
                'difficulty_config' => null,
                'notes' => 'Draft JSU prepared for internal moderation.',
                'approved_at' => null,
                'activated_at' => null,
            ]
        );

        $this->syncBlueprints($draft, $draftCourse->clos()->orderBy('clo_no')->get(), [
            ['topic' => 'Programming Basics', 'bloom_level' => 2, 'marks' => 10],
            ['topic' => 'Control Structures', 'bloom_level' => 3, 'marks' => 15],
            ['topic' => 'Code Improvement', 'bloom_level' => 5, 'marks' => 15],
        ]);

        $this->syncLogs($draft, [
            ['user_id' => $lecturer->id, 'action' => 'created', 'comment' => 'Draft JSU seeded for demo purposes.'],
        ]);

        $submitted = Jsu::query()->updateOrCreate(
            ['title' => 'JSU Sample Submitted - Data Structures'],
            [
                'course_id' => $submittedCourse->id,
                'created_by' => $lecturer->id,
                'approved_by' => null,
                'activated_by' => null,
                'academic_session' => '2025/2026-2',
                'exam_type' => 'final',
                'total_questions' => 0,
                'total_marks' => 60,
                'duration_minutes' => 120,
                'status' => 'submitted',
                'difficulty_config' => null,
                'notes' => 'Submitted JSU awaiting quality review.',
                'approved_at' => null,
                'activated_at' => null,
            ]
        );

        $this->syncBlueprints($submitted, $submittedCourse->clos()->orderBy('clo_no')->get(), [
            ['topic' => 'Linear Structures', 'bloom_level' => 2, 'marks' => 15],
            ['topic' => 'Tree Traversal', 'bloom_level' => 3, 'marks' => 20],
            ['topic' => 'Complexity Analysis', 'bloom_level' => 4, 'marks' => 25],
        ]);

        $this->syncLogs($submitted, [
            ['user_id' => $lecturer->id, 'action' => 'created', 'comment' => 'JSU drafted and completed.'],
            ['user_id' => $lecturer->id, 'action' => 'submitted', 'comment' => 'Submitted for reviewer action.'],
        ]);

        $active = Jsu::query()->updateOrCreate(
            ['title' => 'JSU Sample Active - Software Engineering'],
            [
                'course_id' => $activeCourse->id,
                'created_by' => $lecturer->id,
                'approved_by' => $approver->id,
                'activated_by' => $approver->id,
                'academic_session' => '2025/2026-2',
                'exam_type' => 'final',
                'total_questions' => 0,
                'total_marks' => 80,
                'duration_minutes' => 150,
                'status' => 'active',
                'difficulty_config' => null,
                'notes' => 'Approved and activated as the official assessment blueprint.',
                'approved_at' => Carbon::now()->subDays(8),
                'activated_at' => Carbon::now()->subDays(5),
            ]
        );

        $this->syncBlueprints($active, $activeCourse->clos()->orderBy('clo_no')->get(), [
            ['topic' => 'Process Models', 'bloom_level' => 2, 'marks' => 20],
            ['topic' => 'Architecture Design', 'bloom_level' => 4, 'marks' => 25],
            ['topic' => 'Quality Evaluation', 'bloom_level' => 5, 'marks' => 35],
        ]);

        $this->syncLogs($active, [
            ['user_id' => $lecturer->id, 'action' => 'created', 'comment' => 'Draft prepared by lecturer.'],
            ['user_id' => $lecturer->id, 'action' => 'submitted', 'comment' => 'Submitted into approval workflow.'],
            ['user_id' => $reviewer->id, 'action' => 'approved', 'comment' => 'Reviewer approved stage one.'],
            ['user_id' => $approver->id, 'action' => 'approved', 'comment' => 'Final approval granted.'],
            ['user_id' => $approver->id, 'action' => 'activated', 'comment' => 'Activated for examination delivery.'],
        ]);
    }

    /**
     * @param array<int, string> $preferredCodes
     */
    private function pickCourse(array $preferredCodes): ?Course
    {
        foreach ($preferredCodes as $code) {
            $match = Course::query()->where('code', $code)->first();

            if ($match) {
                return $match;
            }
        }

        return Course::query()->orderBy('id')->first();
    }

    private function ensureClos(Course $course): void
    {
        if ($course->clos()->exists()) {
            return;
        }

        $course->clos()->createMany([
            ['clo_no' => 1, 'statement' => 'Explain key concepts for '.$course->code.'.', 'bloom_level' => 'C2'],
            ['clo_no' => 2, 'statement' => 'Apply methods in guided tasks for '.$course->code.'.', 'bloom_level' => 'C3'],
            ['clo_no' => 3, 'statement' => 'Evaluate solution quality for '.$course->code.'.', 'bloom_level' => 'C5'],
        ]);
    }

    /**
     * @param Collection<int, CourseClo> $clos
     * @param array<int, array{topic: string, bloom_level: int, marks: int}> $rows
     */
    private function syncBlueprints(Jsu $jsu, Collection $clos, array $rows): void
    {
        $jsu->blueprints()->delete();

        foreach ($rows as $index => $row) {
            $jsu->blueprints()->create([
                'clo_id' => $clos[$index % max(1, $clos->count())]?->id,
                'question_no' => $index + 1,
                'topic' => $row['topic'],
                'bloom_level' => $row['bloom_level'],
                'marks' => $row['marks'],
                'weight_percentage' => null,
                'notes' => 'Seeded blueprint item for JSU demo data.',
            ]);
        }

        $totalMarks = (float) $jsu->blueprints()->sum('marks');

        if ($totalMarks > 0) {
            foreach ($jsu->blueprints as $blueprint) {
                $blueprint->update([
                    'weight_percentage' => round(((float) $blueprint->marks / $totalMarks) * 100, 2),
                ]);
            }
        }

        $jsu->update([
            'total_questions' => $jsu->blueprints()->count(),
            'total_marks' => (int) round($totalMarks),
        ]);
    }

    /**
     * @param array<int, array{user_id: int, action: string, comment: string}> $events
     */
    private function syncLogs(Jsu $jsu, array $events): void
    {
        $jsu->logs()->delete();

        foreach ($events as $offset => $event) {
            $jsu->logs()->create([
                'user_id' => $event['user_id'],
                'action' => $event['action'],
                'comment' => $event['comment'],
                'metadata' => null,
                'created_at' => Carbon::now()->subDays(max(0, count($events) - $offset)),
            ]);
        }
    }
}
