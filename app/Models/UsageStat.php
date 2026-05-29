<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Aggregated daily usage statistics — one row per
 * (user, api_key, date, tool, model).
 *
 * Updated atomically via UsageStat::record() so the SaaS dashboard can
 * report "how many credits did Genspark cost me yesterday" without
 * scanning a huge per-call log.
 */
class UsageStat extends Model
{
    protected $fillable = [
        'user_id',
        'api_key_id',
        'date',
        'tool',
        'model',
        'calls',
        'tokens',
        'errors',
    ];

    protected $casts = [
        'date'   => 'date',
        'calls'  => 'int',
        'tokens' => 'int',
        'errors' => 'int',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function apiKey(): BelongsTo
    {
        return $this->belongsTo(ApiKey::class, 'api_key_id');
    }

    /**
     * Increment the appropriate row for today.
     */
    public static function record(
        int     $userId,
        ?int    $apiKeyId,
        string  $tool,
        ?string $model = null,
        int     $tokens = 0,
        bool    $isError = false,
    ): void {
        $row = static::firstOrNew([
            'user_id'    => $userId,
            'api_key_id' => $apiKeyId,
            'date'       => Carbon::today(),
            'tool'       => $tool,
            'model'      => $model,
        ]);

        $row->calls  = ($row->calls  ?? 0) + 1;
        $row->tokens = ($row->tokens ?? 0) + $tokens;
        if ($isError) $row->errors = ($row->errors ?? 0) + 1;
        $row->save();
    }
}
