<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Modules\Programme\Models\CLOPLOMapping;
use Modules\Programme\Models\Programme;
use Modules\Programme\Models\ProgrammeCourse;
use Modules\Programme\Models\ProgrammePEO;
use Modules\Programme\Models\ProgrammePLO;
use Modules\Programme\Models\StudyPlan;
use Modules\Programme\Models\StudyPlanCourse;
use Modules\Course\Models\Course;

class ProgrammeManagementSeeder extends Seeder
{
    public function run(): void
    {
        $programmeChair = User::query()->where('email', 'coordinator@academic.local')->first();

        // Create a sample programme
        $programme = Programme::updateOrCreate(
            ['code' => 'CS-UG-001'],
            [
                'name' => 'Bachelor of Science in Computer Science',
                'level' => 'Bachelor',
                'description' => 'A comprehensive 4-year undergraduate programme providing foundational knowledge and practical skills in computer science.',
                'accreditation_body' => 'Malaysian Qualifications Agency',
                'duration_semesters' => 8,
                'is_active' => true,
                'programme_chair_id' => $programmeChair?->id,
                'status' => 'approved',
            ]
        );

        // Create PLOs
        $plos = [
            ['code' => 'PLO-1', 'description' => 'Demonstrate knowledge of fundamental computer science concepts and theories', 'sequence_order' => 1],
            ['code' => 'PLO-2', 'description' => 'Apply programming skills to solve real-world problems', 'sequence_order' => 2],
            ['code' => 'PLO-3', 'description' => 'Design and develop software solutions using modern software engineering practices', 'sequence_order' => 3],
            ['code' => 'PLO-4', 'description' => 'Analyze and evaluate system performance and security implications', 'sequence_order' => 4],
            ['code' => 'PLO-5', 'description' => 'Communicate effectively and work in collaborative team environments', 'sequence_order' => 5],
        ];

        foreach ($plos as $plo) {
            ProgrammePLO::updateOrCreate(
                ['programme_id' => $programme->id, 'code' => $plo['code']],
                $plo
            );
        }

        // Create PEOs
        $peos = [
            ['code' => 'PEO-1', 'description' => 'Graduates will pursue professional careers in software development, IT management, and related fields', 'sequence_order' => 1],
            ['code' => 'PEO-2', 'description' => 'Graduates will pursue further studies at postgraduate level', 'sequence_order' => 2],
            ['code' => 'PEO-3', 'description' => 'Graduates will engage in lifelong learning and professional development', 'sequence_order' => 3],
        ];

        foreach ($peos as $peo) {
            ProgrammePEO::updateOrCreate(
                ['programme_id' => $programme->id, 'code' => $peo['code']],
                $peo
            );
        }

        $courses = $this->seedCourses($programme);

        // Create Sample Study Plan
        $studyPlan = StudyPlan::updateOrCreate(
            ['programme_id' => $programme->id, 'name' => 'Standard 4-Year Plan'],
            [
                'description' => 'Standard curriculum spread over 8 semesters with core and elective courses',
                'total_years' => 4,
                'semesters_per_year' => 2,
                'semesters_data' => ['duration_semesters' => 8],
                'is_active' => true,
            ]
        );

        if ($courses->isNotEmpty()) {
            $courseData = [
                ['course_id' => $courses[0]->id ?? null, 'year' => 1, 'semester' => 1, 'is_mandatory' => true],
                ['course_id' => $courses[1]->id ?? null, 'year' => 1, 'semester' => 2, 'is_mandatory' => true],
                ['course_id' => $courses[2]->id ?? null, 'year' => 2, 'semester' => 1, 'is_mandatory' => true],
                ['course_id' => $courses[3]->id ?? null, 'year' => 2, 'semester' => 2, 'is_mandatory' => true],
                ['course_id' => $courses[4]->id ?? null, 'year' => 3, 'semester' => 1, 'is_mandatory' => false],
            ];

            foreach ($courseData as $data) {
                if ($data['course_id']) {
                    ProgrammeCourse::updateOrCreate(
                        [
                            'programme_id' => $programme->id,
                            'course_id' => $data['course_id'],
                            'year' => $data['year'],
                            'semester' => $data['semester'],
                        ],
                        ['is_mandatory' => $data['is_mandatory']]
                    );

                    StudyPlanCourse::updateOrCreate(
                        [
                            'study_plan_id' => $studyPlan->id,
                            'course_id' => $data['course_id'],
                            'year' => $data['year'],
                            'semester' => $data['semester'],
                        ],
                        ['is_mandatory' => $data['is_mandatory']]
                    );
                }
            }

            $programmePlos = $programme->programmePLOs()->get();
            if ($courses->isNotEmpty() && $programmePlos->isNotEmpty()) {
                foreach ($courses as $courseIndex => $course) {
                    $coursePlos = $programmePlos->slice($courseIndex % max(1, $programmePlos->count()), 3)->values();

                    if ($coursePlos->count() < 3) {
                        $coursePlos = $programmePlos->take(3);
                    }

                    foreach ($course->clos()->get() as $cloIndex => $clo) {
                        $targetPlo = $coursePlos[$cloIndex] ?? $programmePlos->first();

                        if (! $targetPlo) {
                            continue;
                        }

                        CLOPLOMapping::updateOrCreate(
                            [
                                'course_id' => $course->id,
                                'programme_plo_id' => $targetPlo->id,
                                'clo_code' => 'CLO-' . $clo->clo_no,
                            ],
                            [
                                'bloom_level' => min(6, max(1, $cloIndex + 2)),
                                'alignment_notes' => 'Seeded mapping linking course learning outcomes to programme learning outcomes for curriculum coverage review.',
                            ]
                        );
                    }
                }
            }
        }

        $this->command->info('Programme Management seeder completed successfully!');
    }

    /**
     * @return \Illuminate\Support\Collection<int, Course>
     */
    private function seedCourses(Programme $programme)
    {
        $catalogue = [
            [
                'code' => 'CSC1013',
                'name' => 'Computing Foundations',
                'credit_hours' => 3,
                'status' => 'approved',
                'detail_prefix' => 'foundation',
            ],
            [
                'code' => 'CSC1023',
                'name' => 'Object-Oriented Programming',
                'credit_hours' => 3,
                'status' => 'approved',
                'detail_prefix' => 'oop',
            ],
            [
                'code' => 'CSC2013',
                'name' => 'Data Structures and Algorithms',
                'credit_hours' => 3,
                'status' => 'in_review',
                'detail_prefix' => 'dsa',
            ],
            [
                'code' => 'CSC2023',
                'name' => 'Database Application Development',
                'credit_hours' => 3,
                'status' => 'submitted',
                'detail_prefix' => 'database',
            ],
            [
                'code' => 'CSC3013',
                'name' => 'Software Testing and Quality',
                'credit_hours' => 3,
                'status' => 'draft',
                'detail_prefix' => 'quality',
            ],
        ];

        return collect($catalogue)->map(function (array $row) use ($programme): Course {
            $course = Course::query()->updateOrCreate(
                ['code' => $row['code']],
                [
                    'programme_id' => $programme->id,
                    'name' => $row['name'],
                    'credit_hours' => $row['credit_hours'],
                    'is_active' => true,
                    'status' => $row['status'],
                ]
            );

            $this->seedCourseDetailRows($course, $row['detail_prefix'], $row['name']);

            return $course;
        });
    }

    private function seedCourseDetailRows(Course $course, string $detailPrefix, string $courseName): void
    {
        $course->clos()->delete();
        $course->requisites()->delete();
        $course->assessments()->delete();
        $course->topics()->delete();
        $course->sltItems()->delete();

        $course->clos()->createMany([
            ['clo_no' => 1, 'statement' => "Explain the core principles of {$courseName}.", 'bloom_level' => 'C2'],
            ['clo_no' => 2, 'statement' => "Apply {$detailPrefix}-related techniques in guided development tasks.", 'bloom_level' => 'C3'],
            ['clo_no' => 3, 'statement' => "Evaluate solution quality, trade-offs, and improvement options in {$courseName}.", 'bloom_level' => 'C5'],
        ]);

        $course->requisites()->createMany([
            ['type' => 'prerequisite', 'course_code' => 'MAT1013', 'course_name' => 'Computational Mathematics'],
            ['type' => 'corequisite', 'course_code' => 'COM1012', 'course_name' => 'Professional Communication'],
        ]);

        $course->assessments()->createMany([
            ['component' => 'Quiz', 'weightage' => 15, 'remarks' => 'Short individual knowledge checks'],
            ['component' => 'Assignment', 'weightage' => 25, 'remarks' => 'Applied coursework and lab deliverables'],
            ['component' => 'Project', 'weightage' => 20, 'remarks' => 'Team or individual development artefact'],
            ['component' => 'Final Examination', 'weightage' => 40, 'remarks' => 'Comprehensive summative assessment'],
        ]);

        $course->topics()->createMany([
            ['week_no' => 1, 'title' => 'Orientation and learning outcomes', 'learning_activity' => 'Lecture and guided discussion'],
            ['week_no' => 2, 'title' => 'Core concepts and models', 'learning_activity' => 'Lecture and worked examples'],
            ['week_no' => 3, 'title' => 'Applied workshop', 'learning_activity' => 'Hands-on lab and pair activity'],
            ['week_no' => 4, 'title' => 'Integrated case study', 'learning_activity' => 'Collaborative analysis and presentation'],
        ]);

        $course->sltItems()->createMany([
            ['activity' => 'Lecture', 'f2f_hours' => 14, 'non_f2f_hours' => 2, 'independent_hours' => 8, 'total_hours' => 24],
            ['activity' => 'Tutorial or Lab', 'f2f_hours' => 12, 'non_f2f_hours' => 4, 'independent_hours' => 10, 'total_hours' => 26],
            ['activity' => 'Assessment preparation', 'f2f_hours' => 0, 'non_f2f_hours' => 8, 'independent_hours' => 14, 'total_hours' => 22],
        ]);
    }
}
