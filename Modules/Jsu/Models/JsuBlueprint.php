<?php

namespace Modules\Jsu\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Course\Models\CourseClo;

class JsuBlueprint extends Model
{
    protected $table = 'jsu_blueprints';

    protected $fillable = [
        'jsu_id',
        'clo_id',
        'question_no',
        'topic',
        'bloom_level',
        'marks',
        'weight_percentage',
        'notes',
    ];

    protected $casts = [
        'bloom_level'       => 'integer',
        'marks'             => 'float',
        'weight_percentage' => 'float',
        'question_no'       => 'integer',
    ];

    // ── Relations ────────────────────────────────────────────────────────────

    public function jsu(): BelongsTo
    {
        return $this->belongsTo(Jsu::class);
    }

    public function clo(): BelongsTo
    {
        return $this->belongsTo(CourseClo::class, 'clo_id');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function bloomLabel(): string
    {
        return config('jsu.bloom_levels')[$this->bloom_level] ?? "Level {$this->bloom_level}";
    }
}
