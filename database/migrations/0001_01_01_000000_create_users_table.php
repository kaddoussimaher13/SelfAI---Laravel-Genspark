<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');

            // Legacy fields, kept for backwards compat. The active key now lives
            // in the api_keys table (one user → many keys, one marked primary).
            $table->text('genspark_api_key')->nullable();    // encrypted (legacy single-key)
            $table->string('gsk_project_id')->nullable();    // legacy single-project (kept for migration)

            // User preferences
            $table->string('preferred_model')->nullable();
            $table->text('system_prompt')->nullable();
            $table->float('temperature')->nullable();
            $table->unsignedInteger('max_tokens')->nullable();
            $table->string('tenant_db')->nullable()->unique();
            $table->string('locale', 8)->default('en');

            // SaaS features
            $table->boolean('auto_failover')->default(true);     // auto-switch keys on quota error
            $table->string('default_project_id')->nullable();    // FK to projects.gsk_id (string, not FK to id)

            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        // ─────────── Genspark API Keys (one user → many keys) ───────────
        Schema::create('api_keys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('label')->default('My Genspark Key');
            $table->text('encrypted_key');                        // gsk-… encrypted
            $table->string('plan')->nullable();                   // free / pro / org
            $table->string('account_email')->nullable();          // from /me
            $table->boolean('is_primary')->default(false);
            $table->boolean('is_active')->default(true);          // disabled keys are skipped during failover
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('disabled_until')->nullable();      // temp-disabled on 429 etc.
            $table->text('last_error')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'is_primary']);
            $table->index(['user_id', 'is_active']);
        });

        // ─────────── Genspark Project IDs (one user → many projects) ───────────
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('gsk_id');             // the actual Genspark project id (e.g. proj-…)
            $table->string('label');
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->unique(['user_id', 'gsk_id']);
        });

        // ─────────── Usage statistics (per user, per key, per day, per tool) ───────────
        Schema::create('usage_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('api_key_id')->nullable()->constrained('api_keys')->nullOnDelete();
            $table->date('date');
            $table->string('tool', 64);                     // 'chat', 'image_generation', …
            $table->string('model', 96)->nullable();
            $table->unsignedInteger('calls')->default(0);
            $table->unsignedBigInteger('tokens')->default(0);
            $table->unsignedInteger('errors')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'api_key_id', 'date', 'tool', 'model'], 'usage_unique');
            $table->index(['user_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usage_stats');
        Schema::dropIfExists('projects');
        Schema::dropIfExists('api_keys');
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
