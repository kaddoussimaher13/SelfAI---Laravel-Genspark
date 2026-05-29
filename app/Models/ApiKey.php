<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

/**
 * A Genspark API key (gsk-…) belonging to a single SelfAI user.
 *
 * One user can register many keys. The "primary" flag marks the preferred
 * one; if `auto_failover` is on in the user's preferences, calls that fail
 * with 429 / 5xx will roll over to the next active key in the user's pool.
 */
class ApiKey extends Model
{
    protected $fillable = [
        'user_id',
        'label',
        'encrypted_key',
        'plan',
        'account_email',
        'is_primary',
        'is_active',
        'last_used_at',
        'disabled_until',
        'last_error',
    ];

    protected $casts = [
        'is_primary'     => 'bool',
        'is_active'      => 'bool',
        'last_used_at'   => 'datetime',
        'disabled_until' => 'datetime',
    ];

    protected $hidden = [
        'encrypted_key',  // never serialise the raw blob
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Transparent encrypt/decrypt — set/get the plain `gsk-…` value via
     * `$apiKey->key` while the DB column stores only the encrypted blob.
     */
    protected function key(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (! $this->encrypted_key) return null;
                try { return Crypt::decryptString($this->encrypted_key); }
                catch (\Throwable $e) { return null; }
            },
            set: fn ($value) => ['encrypted_key' => $value ? Crypt::encryptString($value) : null],
        );
    }

    /**
     * A short safe preview, e.g. "gsk-eyJ…ssVs" — used in lists.
     */
    public function preview(): string
    {
        $k = $this->key;
        if (! $k) return '—';
        return mb_substr($k, 0, 6) . '…' . mb_substr($k, -4);
    }

    /**
     * Whether this key can be used right now (active + not temp-disabled).
     */
    public function isUsable(): bool
    {
        if (! $this->is_active) return false;
        if ($this->disabled_until && $this->disabled_until->isFuture()) return false;
        return true;
    }
}
