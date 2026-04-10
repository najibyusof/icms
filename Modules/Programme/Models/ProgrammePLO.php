<?php

namespace Modules\Programme\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProgrammePLO extends Model
{
    use HasFactory;

    protected $table = 'programme_plos';

    protected $fillable = [
        'programme_id',
        'code',
        'description',
        'sequence_order',
    ];

    protected $casts = [
        'sequence_order' => 'integer',
    ];

    public function programme(): BelongsTo
    {
        return $this->belongsTo(Programme::class);
    }

    public function cloMappings(): HasMany
    {
        return $this->hasMany(CLOPLOMapping::class, 'programme_plo_id');
    }
}
