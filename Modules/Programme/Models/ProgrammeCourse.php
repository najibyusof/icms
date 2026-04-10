<?php

namespace Modules\Programme\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgrammeCourse extends Model
{
    use HasFactory;

    protected $table = 'programme_courses';

    protected $fillable = [
        'programme_id',
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

    public function programme(): BelongsTo
    {
        return $this->belongsTo(Programme::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(\Modules\Course\Models\Course::class);
    }
}
