<?php

namespace Modules\Examination\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Modules\Course\Models\Course;
use Modules\Group\Models\AcademicGroup;
use Modules\Workflow\Models\WorkflowInstance;

class Examination extends Model
{
    use HasFactory;

    protected $table = 'examinations';

    protected $fillable = [
        'course_id',
        'group_id',
        'submitted_by',
        'title',
        'exam_date',
        'status',
        'metadata',
    ];

    protected $casts = [
        'exam_date' => 'date',
        'metadata' => 'array',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(AcademicGroup::class, 'group_id');
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function workflow(): MorphOne
    {
        return $this->morphOne(WorkflowInstance::class, 'workflowable');
    }
}
