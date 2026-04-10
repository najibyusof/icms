<?php

namespace Modules\Programme\Services;

use Illuminate\Database\Eloquent\Collection;
use Modules\Programme\Models\CLOPLOMapping;
use Modules\Programme\Models\ProgrammePLO;
use Modules\Programme\Models\Programme;
use Modules\Course\Models\Course;

class MappingService
{
    /**
     * Create or update a CLO-PLO mapping
     */
    public function createOrUpdateMapping(array $data): CLOPLOMapping
    {
        return CLOPLOMapping::updateOrCreate(
            [
                'course_id' => $data['course_id'],
                'programme_plo_id' => $data['programme_plo_id'],
                'clo_code' => $data['clo_code'],
            ],
            [
                'alignment_notes' => $data['alignment_notes'] ?? null,
                'bloom_level' => $data['bloom_level'],
            ]
        );
    }

    /**
     * Get all mappings for a course
     */
    public function getMappingsByCourse(Course $course): Collection
    {
        return CLOPLOMapping::where('course_id', $course->id)
            ->with(['programmePLO.programme:id,name,code'])
            ->orderBy('clo_code')
            ->get();
    }

    /**
     * Get all mappings for a programme
     */
    public function getMappingsByProgramme(Programme $programme): Collection
    {
        return CLOPLOMapping::whereIn(
            'course_id',
            $programme->courses()->pluck('id')
        )
            ->with([
                'course:id,code,name',
                'programmePLO:id,code,description',
            ])
            ->orderBy(function ($q) {
                $q->selectRaw('courses.code')
                    ->from('courses')
                    ->whereColumn('courses.id', 'clo_plo_mappings.course_id');
            })
            ->orderBy('clo_code')
            ->get();
    }

    /**
     * Get mapping matrix for a programme (PLO x Course grid)
     */
    public function getMappingMatrix(Programme $programme): array
    {
        $courses = $programme->courses()
            ->with('cloDevelopment')
            ->orderBy('code')
            ->get();

        $plos = $programme->programmePLOs()
            ->orderBy('sequence_order')
            ->get();

        $matrix = [];

        foreach ($courses as $course) {
            foreach ($plos as $plo) {
                $mapping = CLOPLOMapping::where('course_id', $course->id)
                    ->where('programme_plo_id', $plo->id)
                    ->first();

                $matrix[$course->id][$plo->id] = [
                    'has_mapping' => $mapping !== null,
                    'bloom_level' => $mapping?->bloom_level,
                ];
            }
        }

        return [
            'courses' => $courses,
            'plos' => $plos,
            'matrix' => $matrix,
        ];
    }

    /**
     * Delete a mapping
     */
    public function deleteMapping(CLOPLOMapping $mapping): bool
    {
        return $mapping->delete();
    }

    /**
     * Get CLO coverage percentage per programme
     */
    public function getCLOCoveragePercentage(Programme $programme): float
    {
        $totalCLOs = $programme->courses()
            ->sum(\Illuminate\Database\Query\Expression::raw('COALESCE(clo_count, 0)'));

        if ($totalCLOs === 0) {
            return 0;
        }

        $mappedCLOs = CLOPLOMapping::whereIn(
            'course_id',
            $programme->courses()->pluck('id')
        )
            ->distinct('course_id', 'programme_plo_id', 'clo_code')
            ->count();

        return round(($mappedCLOs / $totalCLOs) * 100, 2);
    }

    /**
     * Get bloom level distribution for a course
     */
    public function getBloomLevelDistribution(Course $course): array
    {
        $distribution = [];

        foreach (CLOPLOMapping::BLOOM_LEVELS as $level => $name) {
            $count = CLOPLOMapping::where('course_id', $course->id)
                ->where('bloom_level', $level)
                ->count();
            $distribution[$level] = [
                'label' => $name,
                'count' => $count,
            ];
        }

        return $distribution;
    }

    /**
     * Get PLO achievement summary
     */
    public function getPLOAchievementSummary(ProgrammePLO $plo): array
    {
        $mappings = $plo->cloMappings()
            ->with('course:id,code,name')
            ->get();

        $bloomDistribution = [];
        foreach (CLOPLOMapping::BLOOM_LEVELS as $level => $name) {
            $bloomDistribution[$level] = $mappings->where('bloom_level', $level)->count();
        }

        return [
            'plo' => $plo,
            'total_mapped_clos' => $mappings->count(),
            'covered_courses' => $mappings->pluck('course_id')->unique()->count(),
            'bloom_distribution' => $bloomDistribution,
            'mappings' => $mappings,
        ];
    }
}
