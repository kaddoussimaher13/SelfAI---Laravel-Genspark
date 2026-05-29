<?php

namespace App\Models;

use App\Services\TenantManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    /** Use the per-tenant SQLite connection. */
    protected $connection = TenantManager::CONNECTION;

    protected $fillable = [
        'conversation_id',
        'role',     // user | assistant | system
        'content',
        'tokens',
        'model',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }
}
