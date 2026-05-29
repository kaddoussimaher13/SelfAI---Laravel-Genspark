<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A Genspark Project ID (e.g. "proj-abc123") that the user has saved to their
 * SelfAI account. Required by tools like image / video / audio generation.
 *
 * Genspark does not expose a public list-my-projects endpoint, so users must
 * add the project IDs they want to use here themselves — afterwards they
 * become a single-click select-box choice on every tool.
 */
class Project extends Model
{
    protected $fillable = [
        'user_id',
        'gsk_id',
        'label',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'bool',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
