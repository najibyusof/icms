<?php

namespace Modules\Course\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseAssessment extends Model
{
    use HasFactory;

    protected $table = 'course_assessments';

    protected $fillable = [
        'course_id',
        'component',
        'weightage',
        'remarks',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}
