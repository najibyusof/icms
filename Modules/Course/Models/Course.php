<?php

namespace Modules\Course\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Modules\Group\Models\AcademicGroup;
use Modules\Programme\Models\Programme;

class Course extends Model
{
    use HasFactory;

    protected $table = 'courses';

    protected $fillable = [
        'programme_id',
        'code',
        'name',
        'credit_hours',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function programme(): BelongsTo
    {
        return $this->belongsTo(Programme::class);
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(AcademicGroup::class, 'group_courses', 'course_id', 'group_id');
    }
}
