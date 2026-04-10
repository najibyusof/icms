<?php

namespace Modules\Group\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Modules\Course\Models\Course;
use Modules\Programme\Models\Programme;

class AcademicGroup extends Model
{
    use HasFactory;

    protected $table = 'academic_groups';

    protected $fillable = [
        'programme_id',
        'coordinator_id',
        'name',
        'intake_year',
        'semester',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function programme(): BelongsTo
    {
        return $this->belongsTo(Programme::class);
    }

    public function coordinator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'coordinator_id');
    }

    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'group_courses', 'group_id', 'course_id');
    }
}
