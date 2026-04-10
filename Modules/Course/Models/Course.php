<?php

namespace Modules\Course\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Group\Models\AcademicGroup;
use Modules\Programme\Models\Programme;

class Course extends Model
{
    use HasFactory;

    protected $table = 'courses';

    protected $fillable = [
        'programme_id',
        'lecturer_id',
        'resource_person_id',
        'vetter_id',
        'code',
        'name',
        'credit_hours',
        'is_active',
        'status',
        'submitted_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'submitted_at' => 'datetime',
    ];

    public function programme(): BelongsTo
    {
        return $this->belongsTo(Programme::class);
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(AcademicGroup::class, 'group_courses', 'course_id', 'group_id');
    }

    public function lecturer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lecturer_id');
    }

    public function resourcePerson(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resource_person_id');
    }

    public function vetter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vetter_id');
    }

    public function clos(): HasMany
    {
        return $this->hasMany(CourseClo::class);
    }

    public function requisites(): HasMany
    {
        return $this->hasMany(CourseRequisite::class);
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(CourseAssessment::class);
    }

    public function topics(): HasMany
    {
        return $this->hasMany(CourseTopic::class);
    }

    public function sltItems(): HasMany
    {
        return $this->hasMany(CourseSlt::class);
    }
}
