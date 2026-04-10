<?php

namespace Modules\Programme\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudyPlan extends Model
{
    use HasFactory;

    protected $table = 'study_plans';

    protected $fillable = [
        'programme_id',
        'name',
        'description',
        'total_years',
        'semesters_per_year',
        'semesters_data',
        'is_active',
    ];

    protected $casts = [
        'total_years' => 'integer',
        'semesters_per_year' => 'integer',
        'semesters_data' => 'json',
        'is_active' => 'boolean',
    ];

    public function programme(): BelongsTo
    {
        return $this->belongsTo(Programme::class);
    }

    public function courses(): HasMany
    {
        return $this->hasMany(StudyPlanCourse::class);
    }

    /**
     * Get semester label (e.g., "Year 1, Semester 1")
     */
    public function getSemesterLabel(int $year, int $semester): string
    {
        return "Year {$year}, Semester {$semester}";
    }

    /**
     * Get all semesters as array of [year, semester]
     */
    public function getSemesters(): array
    {
        $semesters = [];
        for ($year = 1; $year <= $this->total_years; $year++) {
            for ($semester = 1; $semester <= $this->semesters_per_year; $semester++) {
                $semesters[] = ['year' => $year, 'semester' => $semester];
            }
        }
        return $semesters;
    }
}
