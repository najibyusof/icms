<?php

namespace Modules\Course\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseRequisite extends Model
{
    use HasFactory;

    protected $table = 'course_requisites';

    protected $fillable = [
        'course_id',
        'type',
        'course_code',
        'course_name',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}
