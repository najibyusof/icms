<?php

namespace Modules\Course\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseClo extends Model
{
    use HasFactory;

    protected $table = 'course_clos';

    protected $fillable = [
        'course_id',
        'clo_no',
        'statement',
        'bloom_level',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}
