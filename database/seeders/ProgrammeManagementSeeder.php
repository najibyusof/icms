<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Programme\Models\CLOPLOMapping;
use Modules\Programme\Models\Programme;
use Modules\Programme\Models\ProgrammePEO;
use Modules\Programme\Models\ProgrammePLO;
use Modules\Programme\Models\StudyPlan;
use Modules\Programme\Models\StudyPlanCourse;
use Modules\Course\Models\Course;

class ProgrammeManagementSeeder extends Seeder
{
    public function run(): void
    {
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

        // Create Sample Study Plan
        $studyPlan = StudyPlan::updateOrCreate(
            ['programme_id' => $programme->id, 'name' => 'Standard 4-Year Plan'],
            [
                'description' => 'Standard curriculum spread over 8 semesters with core and elective courses',
                'total_years' => 4,
                'semesters_per_year' => 2,
                'is_active' => true,
            ]
        );

        // Get or create sample courses if they exist
        $courses = Course::where('programme_id', $programme->id)
            ->take(5)
            ->get();

        if ($courses->isNotEmpty()) {
            // Add courses to study plan (simplified - just first 4)
            $courseData = [
                ['course_id' => $courses[0]->id ?? null, 'year' => 1, 'semester' => 1, 'is_mandatory' => true],
                ['course_id' => $courses[1]->id ?? null, 'year' => 1, 'semester' => 1, 'is_mandatory' => true],
                ['course_id' => $courses[2]->id ?? null, 'year' => 1, 'semester' => 2, 'is_mandatory' => true],
            ];

            foreach ($courseData as $data) {
                if ($data['course_id']) {
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

            // Create sample CLO-PLO mappings
            $programmePlos = $programme->programmePLOs()->get();
            if ($courses->isNotEmpty() && $programmePlos->isNotEmpty()) {
                $courseSample = $courses->first();
                $ploSample = $programmePlos->first();

                CLOPLOMapping::updateOrCreate(
                    [
                        'course_id' => $courseSample->id,
                        'programme_plo_id' => $ploSample->id,
                        'clo_code' => 'CLO-1',
                    ],
                    [
                        'bloom_level' => 2, // Understand
                        'alignment_notes' => 'This CLO helps achieve the programme PLO through foundational understanding',
                    ]
                );
            }
        }

        $this->command->info('Programme Management seeder completed successfully!');
    }
}
