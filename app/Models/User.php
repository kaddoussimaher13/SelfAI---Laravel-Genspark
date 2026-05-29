<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Crypt;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'genspark_api_key',     // legacy single-key (still encrypted)
        'gsk_project_id',       // legacy single-project
        'preferred_model',
        'system_prompt',
        'temperature',
        'max_tokens',
        'tenant_db',
        'locale',
        'auto_failover',
        'default_project_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'genspark_api_key',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'auto_failover'     => 'bool',
        ];
    }

    // ─────────── Relationships (multi-key / multi-project SaaS model) ───────────

    public function apiKeys(): HasMany
    {
        return $this->hasMany(ApiKey::class)->orderByDesc('is_primary')->orderBy('id');
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class)->orderByDesc('is_default')->orderBy('label');
    }

    public function usageStats(): HasMany
    {
        return $this->hasMany(UsageStat::class);
    }

    /**
     * The currently-preferred key the dispatcher should try first.
     */
    public function primaryApiKey(): ?ApiKey
    {
        return $this->apiKeys()->where('is_primary', true)->where('is_active', true)->first()
            ?? $this->apiKeys()->where('is_active', true)->first();
    }

    /**
     * Whether the user finished onboarding (i.e. has at least one active key).
     */
    public function hasApiKey(): bool
    {
        // Either a row in api_keys, or the legacy single-key column.
        if ($this->apiKeys()->where('is_active', true)->exists()) return true;
        return ! empty($this->getRawOriginal('genspark_api_key'));
    }

    /**
     * The user's default Project (matches default_project_id on the user row).
     */
    public function defaultProject(): ?Project
    {
        if (! $this->default_project_id) return $this->projects()->where('is_default', true)->first();
        return $this->projects()->where('gsk_id', $this->default_project_id)->first()
            ?? $this->projects()->where('is_default', true)->first();
    }

    // ─────────── Legacy encrypted single-key accessor (kept for old data) ───────────

    protected function gensparkApiKey(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if (! $value) return null;
                try { return Crypt::decryptString($value); }
                catch (\Throwable $e) { return null; }
            },
            set: fn ($value) => $value ? Crypt::encryptString($value) : null,
        );
    }

    // ─────────── Tenant DB ───────────

    public function tenantDbName(): string
    {
        if (! $this->tenant_db) {
            $this->tenant_db = 'tenant_' . $this->id . '_' . bin2hex(random_bytes(4)) . '.sqlite';
            $this->saveQuietly();
        }
        return $this->tenant_db;
    }

    public function tenantDbPath(): string
    {
        return storage_path('app/tenants/' . $this->tenantDbName());
    }
}
