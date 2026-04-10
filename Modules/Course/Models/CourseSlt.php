<?php

namespace Modules\Course\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseSlt extends Model
{
    use HasFactory;

    protected $table = 'course_slt';

    protected $fillable = [
        'course_id',
        'activity',
        'f2f_hours',
        'non_f2f_hours',
        'independent_hours',
        'total_hours',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}
