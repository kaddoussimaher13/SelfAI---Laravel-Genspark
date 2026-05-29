<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use App\Services\TenantManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * For every authenticated request:
 *   1. apply the user's preferred locale (i18n);
 *   2. boot the tenant SQLite connection so all Eloquent models use it;
 *   3. one-time migrate the legacy `users.genspark_api_key` column into a
 *      row of the api_keys table (so older accounts get multi-key + failover
 *      "for free" the first time they log into v0.4+).
 */
class BootTenant
{
    public function __construct(protected TenantManager $tenants) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($user = $request->user()) {
            if (! empty($user->locale)) {
                app()->setLocale($user->locale);
            }

            $this->migrateLegacyKey($user);
            $this->tenants->bootForUser($user);
        }

        return $next($request);
    }

    /**
     * If the user still has the old single-key column populated but no rows
     * in api_keys yet, copy it across so the dispatcher picks it up.
     * Idempotent — safe to run on every request.
     */
    protected function migrateLegacyKey($user): void
    {
        $legacyCipher = $user->getRawOriginal('genspark_api_key');
        if (! $legacyCipher) return;
        if ($user->apiKeys()->exists()) return;

        $plain = $user->genspark_api_key;     // decrypts via Attribute
        if (! $plain) return;

        try {
            $row = new ApiKey([
                'user_id'    => $user->id,
                'label'      => __('settings.api_key_default_label'),
                'is_primary' => true,
                'is_active'  => true,
            ]);
            $row->key = $plain;
            $row->save();
        } catch (\Throwable $e) {
            // Soft-fail — don't break the request just because migration tripped.
        }
    }
}

