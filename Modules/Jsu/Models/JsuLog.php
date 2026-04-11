<?php

namespace Modules\Jsu\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JsuLog extends Model
{
    protected $table = 'jsu_logs';

    public $timestamps = false;

    protected $fillable = [
        'jsu_id',
        'user_id',
        'action',
        'comment',
        'metadata',
        'created_at',
    ];

    protected $casts = [
        'metadata'   => 'array',
        'created_at' => 'datetime',
    ];

    public function jsu(): BelongsTo
    {
        return $this->belongsTo(Jsu::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
