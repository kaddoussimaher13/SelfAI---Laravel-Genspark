<?php

namespace App\Models;

use App\Services\TenantManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    /** Use the per-tenant SQLite connection. */
    protected $connection = TenantManager::CONNECTION;

    protected $fillable = [
        'title',
        'model',
        'system_prompt',
    ];

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('id');
    }

    /**
     * Build the OpenAI-style messages array sent to Genspark,
     * prepended with the system prompt if set.
     */
    public function toApiMessages(): array
    {
        $payload = [];

        if (! empty($this->system_prompt)) {
            $payload[] = ['role' => 'system', 'content' => $this->system_prompt];
        }

        foreach ($this->messages as $m) {
            $payload[] = ['role' => $m->role, 'content' => $m->content];
        }

        return $payload;
    }
}
