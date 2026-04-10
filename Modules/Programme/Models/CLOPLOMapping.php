<?php

namespace Modules\Programme\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Course\Models\Course;

class CLOPLOMapping extends Model
{
    use HasFactory;

    protected $table = 'clo_plo_mappings';

    protected $fillable = [
        'course_id',
        'programme_plo_id',
        'clo_code',
        'alignment_notes',
        'bloom_level',
    ];

    protected $casts = [
        'bloom_level' => 'integer',
    ];

    public const BLOOM_LEVELS = [
        1 => 'Remember',
        2 => 'Understand',
        3 => 'Apply',
        4 => 'Analyze',
        5 => 'Evaluate',
        6 => 'Create',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function programmePLO(): BelongsTo
    {
        return $this->belongsTo(ProgrammePLO::class, 'programme_plo_id');
    }

    /**
     * Get Bloom's level label
     */
    public function getBloomLevelLabel(): string
    {
        return self::BLOOM_LEVELS[$this->bloom_level] ?? 'Unknown';
    }
}
