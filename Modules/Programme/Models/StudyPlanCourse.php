<?php

namespace Modules\Programme\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Course\Models\Course;

class StudyPlanCourse extends Model
{
    use HasFactory;

    protected $table = 'study_plan_courses';

    protected $fillable = [
        'study_plan_id',
        'course_id',
        'year',
        'semester',
        'is_mandatory',
    ];

    protected $casts = [
        'year' => 'integer',
        'semester' => 'integer',
        'is_mandatory' => 'boolean',
    ];

    public function studyPlan(): BelongsTo
    {
        return $this->belongsTo(StudyPlan::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}
