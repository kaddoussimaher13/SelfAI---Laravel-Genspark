<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Multi-tenant database manager.
 *
 * Strategy: every user has their own SQLite file at
 *   storage/app/tenants/tenant_<id>_<random>.sqlite
 *
 * The main DB ("default" connection) holds only the `users` table.
 * All conversations/messages live in the per-user tenant DB on the
 * dynamic "tenant" connection.
 *
 * Usage:
 *   app(TenantManager::class)->bootForUser(auth()->user());
 *   Conversation::on('tenant')->...   // or just Conversation::... if you call setConnectionOnModels
 */
class TenantManager
{
    public const CONNECTION = 'tenant';

    protected ?int $bootedFor = null;

    /**
     * Make sure the tenant DB exists and the schema is up to date,
     * then point the "tenant" connection at it.
     */
    public function bootForUser(User $user): void
    {
        // Avoid re-booting on every call within the same request
        if ($this->bootedFor === $user->id) {
            return;
        }

        $path = $user->tenantDbPath();
        $dir  = dirname($path);

        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        if (! file_exists($path)) {
            touch($path);
        }

        // Wire up / re-wire the "tenant" connection
        Config::set('database.connections.' . self::CONNECTION, [
            'driver'                  => 'sqlite',
            'database'                => $path,
            'prefix'                  => '',
            'foreign_key_constraints' => true,
        ]);

        DB::purge(self::CONNECTION);
        DB::reconnect(self::CONNECTION);

        $this->ensureSchema();

        $this->bootedFor = $user->id;
    }

    /**
     * Create the conversations + messages tables in the tenant DB
     * if they don't exist yet. Runs once per user, then is a no-op.
     */
    protected function ensureSchema(): void
    {
        $schema = Schema::connection(self::CONNECTION);

        if (! $schema->hasTable('conversations')) {
            $schema->create('conversations', function ($table) {
                $table->id();
                $table->string('title')->default('New chat');
                $table->string('model')->nullable();
                $table->text('system_prompt')->nullable();
                $table->timestamps();
                $table->index('updated_at');
            });
        }

        if (! $schema->hasTable('messages')) {
            $schema->create('messages', function ($table) {
                $table->id();
                $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
                $table->string('role', 20);
                $table->longText('content');
                $table->unsignedInteger('tokens')->nullable();
                $table->string('model')->nullable();
                $table->timestamps();
                $table->index(['conversation_id', 'id']);
            });
        }
    }

    /**
     * Reset the booted state (useful in tests / console commands).
     */
    public function reset(): void
    {
        $this->bootedFor = null;
        DB::purge(self::CONNECTION);
    }
}
