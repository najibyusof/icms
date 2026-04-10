<?php

namespace Modules\Course\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseTopic extends Model
{
    use HasFactory;

    protected $table = 'course_topics';

    protected $fillable = [
        'course_id',
        'week_no',
        'title',
        'learning_activity',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}
