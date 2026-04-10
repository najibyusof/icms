<?php

namespace Modules\Programme\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Course\Models\Course;
use Modules\Group\Models\AcademicGroup;

class Programme extends Model
{
    use HasFactory;

    protected $table = 'programmes';

    protected $fillable = [
        'code',
        'name',
        'level',
        'duration_semesters',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }

    public function groups(): HasMany
    {
        return $this->hasMany(AcademicGroup::class, 'programme_id');
    }
}
