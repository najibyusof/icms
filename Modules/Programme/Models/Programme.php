<?php

namespace Modules\Programme\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'description',
        'accreditation_body',
        'duration_semesters',
        'is_active',
        'programme_chair_id',
        'status',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'duration_semesters' => 'integer',
    ];

    public const STATUS_DRAFT = 'draft';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_IN_REVIEW = 'in_review';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    public const STATUSES = [
        self::STATUS_DRAFT => 'Draft',
        self::STATUS_SUBMITTED => 'Submitted',
        self::STATUS_IN_REVIEW => 'In Review',
        self::STATUS_APPROVED => 'Approved',
        self::STATUS_REJECTED => 'Rejected',
    ];

    // Relationships
    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }

    public function groups(): HasMany
    {
        return $this->hasMany(AcademicGroup::class, 'programme_id');
    }

    public function programmePLOs(): HasMany
    {
        return $this->hasMany(ProgrammePLO::class);
    }

    public function programmePEOs(): HasMany
    {
        return $this->hasMany(ProgrammePEO::class);
    }

    public function programmeCourses(): HasMany
    {
        return $this->hasMany(ProgrammeCourse::class);
    }

    public function studyPlans(): HasMany
    {
        return $this->hasMany(StudyPlan::class);
    }

    public function programmeChair(): BelongsTo
    {
        return $this->belongsTo(User::class, 'programme_chair_id');
    }

    /**
     * Get all CLO-PLO mappings for this programme
     */
    public function getCLOPLOMappings()
    {
        return CLOPLOMapping::whereIn(
            'course_id',
            $this->courses()->pluck('id')
        );
    }

    /**
     * Check if programme is submitted for approval
     */
    public function isSubmitted(): bool
    {
        return $this->status !== self::STATUS_DRAFT;
    }

    /**
     * Check if programme is approved
     */
    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }
}
