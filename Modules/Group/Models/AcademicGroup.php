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

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'group_users', 'group_id', 'user_id')
            ->withPivot(['role'])
            ->withTimestamps();
    }

    /**
     * Get all programmes affiliated with this group
     */
    public function getProgrammes()
    {
        return $this->programme;
    }

    /**
     * Check if user is member of this group
     */
    public function hasMember(User $user): bool
    {
        return $this->users()->where('user_id', $user->id)->exists();
    }

    /**
     * Get user role in this group
     */
    public function getUserRole(User $user): ?string
    {
        $pivot = $this->users()->where('user_id', $user->id)->first();
        return $pivot?->pivot?->role;
    }

    /**
     * Get members in specific role
     */
    public function getMembersByRole(string $role)
    {
        return $this->users()->where('role', $role)->get();
    }
}
