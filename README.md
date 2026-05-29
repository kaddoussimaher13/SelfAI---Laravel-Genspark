# SelfAI

> A **self-hosted, multi-tenant SaaS AI chat platform** built on **Laravel 12** + **Breeze (Blade)** and powered by the **Genspark AI** API.
>
> Each user signs up, provides their own Genspark API key, and gets:
> * A **private SQLite database** for their conversations.
> * A **ChatGPT-style UI** with 16 LLM models (Claude, GPT-5, DeepSeek, Kimi, MiniMax, …).
> * A built-in **AI Tools panel** for image / video / audio generation, web search, document summarisation, transcription, and more (171 tools total available via the Genspark tool API).
> * Full **client-side + server-side** form validation.
> * **i18n** (every UI string lives in `lang/en/*.php`).
> * **Sub-folder deployment via `.htaccess`** — no SSH, no Node.js, no Docker required.

---

## Table of Contents

1. [Project Overview](#project-overview)
2. [What's New (v0.3 — SaaS / Tools update)](#whats-new-v03--saas--tools-update)
3. [Architecture](#architecture)
4. [Supported AI Models](#supported-ai-models)
5. [Supported AI Tools](#supported-ai-tools)
6. [Installation & Deployment](#installation--deployment)
7. [Configuration](#configuration)
8. [User Guide](#user-guide)
9. [Form Validation](#form-validation)
10. [Internationalisation](#internationalisation)
11. [API Routes](#api-routes)
12. [Troubleshooting](#troubleshooting)
13. [Roadmap](#roadmap)

---

## Project Overview

* **Name**: SelfAI
* **Goal**: A turn-key AI chat & content-generation SaaS that any agency, school or small team can drop on a cheap shared-hosting plan (no Node.js / no Docker / no SSH) and immediately have:
  * per-user accounts
  * per-user encrypted API keys
  * per-user isolated databases (multi-tenant)
  * an AI chat UI, plus image / video / audio / search tools
* **Stack**: Laravel 12 · Breeze (Blade) · Alpine.js · Tailwind CSS · PHP 8.2+ · SQLite (one DB per user)
* **Repository**: ships as a single tarball / zip that you upload and extract.

## What's New (v0.3 — SaaS / Tools update)

* **🔑 Correct Genspark endpoints** — the service now talks to the real `https://www.genspark.ai/api/llm_proxy/v1/chat/completions` (OpenAI-compatible) and `https://www.genspark.ai/api/tool_cli/*` (171 tools), reverse-engineered from the official [`@genspark/cli`](https://www.npmjs.com/package/@genspark/cli) v1.0.18 npm package.
* **📋 Detailed API-key errors** — the settings page now displays the *actual* upstream message (HTTP status, body, hint) instead of the generic "rejected" line.
* **🧠 16 real chat models** — Claude Opus / Sonnet 4.7/4.6 (200k & 1M ctx), GPT-5.5 / 5.4 / 5.2 (+ mini & nano), DeepSeek V4 Pro / Flash, Trinity Large Thinking, Kimi K2.6, MiniMax M2.7.
* **🎨 11 image-generation models** — Nano-Banana 2 / Pro, GPT Image 2, Flux 2 Pro, Z-Image Turbo, Seedream v5 Lite, Bria RMBG (background removal), Recraft Clarity (upscale), text/watermark removal, Flux Pro Outpaint, BBox Segment.
* **🎬 18 video-generation models** — Sora 2 / 2 Pro, Gemini Veo 3.1 (+ ref-to-video, first-last-frame), Kling V3, Hailuo 2.3, Wan v2.7, Vidu Q3, Runway Gen-4 Turbo, PixVerse V6, Seedance 2.0, Grok Imagine, Alibaba Happy Horse, …
* **🎵 13 audio models** — Gemini 3.1 Flash TTS, ElevenLabs v3 / Multilingual v2, MiniMax Speech 2.8 HD, ElevenLabs Music & Sound Effects, Cassette AI, MiniMax Music 2.6, Mureka Song / Instrumental, Google Lyria, Voice Clone, Voice Changer.
* **✅ Form validation everywhere** — every form (login, register, forgot password, reset password, settings, chat composer, every AI tool) now does **client-side validation first** (instant feedback, translated via `lang/en/*.php → js`), then **server-side validation** as the second line of defense.
* **🧰 `/tools` panel** — a brand-new Alpine.js UI for invoking any of the 11 curated Genspark tools with sensible per-tool forms (model picker, aspect ratio, image size, …) and a smart result viewer that auto-renders generated `.png` / `.mp4` / `.mp3` links.
* **🆔 Optional Genspark Project ID** — a new field in *Settings* enables tools that require a Genspark project (image / video / audio / docs / deep research).

## Architecture

```
┌─────────────────────────── Browser ───────────────────────────┐
│  Blade templates · Alpine.js · Tailwind CSS                   │
│  Client-side validation runs first (translated messages).     │
└──────────────────────────────┬────────────────────────────────┘
                               │ HTTPS (POST / PATCH / GET)
┌──────────────────────────────▼────────────────────────────────┐
│  Laravel 12  +  Breeze (Blade)                                │
│  ────────────────────────────────────────────────────────────  │
│  middleware: auth → tenant (BootTenant) → require.api_key     │
│                                                                │
│   ┌────────────────┐   ┌─────────────────────────────────────┐ │
│   │ Main SQLite DB │   │  Per-user tenant DBs                │ │
│   │ database.sqlite│   │  storage/app/tenants/tenant_*.sqlite│ │
│   │   • users      │   │   • conversations                   │ │
│   │   • sessions   │   │   • messages                        │ │
│   └────────────────┘   └─────────────────────────────────────┘ │
│                                                                │
│  GensparkService  ─── HTTP ──┐                                 │
└──────────────────────────────│─────────────────────────────────┘
                               │ Bearer / X-Api-Key
                               ▼
            https://www.genspark.ai
              ├─ /api/llm_proxy/v1/chat/completions    (LLM)
              └─ /api/tool_cli/<tool>                  (171 tools)
                  • image_generation, video_generation
                  • audio_generation, web_search, crawler
                  • understand_images, audio_transcribe
                  • summarize_large_document, create_task
                  • stock_price, social_*, github, gmail, …
```

### Multi-tenant data flow

1. User registers (main DB).
2. `RegisteredUserController` redirects to `/settings` with `onboarding=true`.
3. User saves their `gsk-…` key.
4. `SettingsController::update()` calls `GensparkService::validateApiKey()` → `GET /api/tool_cli/me` and saves the encrypted key if it passes (otherwise surfaces the detailed error).
5. On every subsequent request, `BootTenant` middleware:
   * applies the user's locale,
   * `TenantManager::bootForUser()` switches the `tenant` Eloquent connection to `storage/app/tenants/tenant_<id>_<rand>.sqlite`,
   * auto-creates the `conversations` + `messages` tables the first time.
6. `Conversation` / `Message` models use `protected $connection = 'tenant'`, so every read/write hits the user's private DB.
7. `RequireApiKey` middleware blocks `/chat` and `/tools` if the user hasn't saved a key yet.

### Key encryption

* Stored in `users.genspark_api_key` as an encrypted blob (`Crypt::encryptString`, using `APP_KEY`).
* Decrypted transparently via a Laravel `Attribute::make()` accessor on the `User` model.
* Never serialised to JSON (`$hidden` list).

## Supported AI Models

> All model lists are kept in `config/genspark.php`. Add or remove entries there to expose / hide models — no code changes required.

### Chat / LLM (16 models, OpenAI-compatible)

| ID | Name | Context | Vision |
|---|---|---|---|
| `claude-opus-4-7` | Claude Opus 4.7 | 1M | ✅ |
| `claude-opus-4-6-1m` | Claude Opus 4.6 (1M) | 1M | ✅ |
| `claude-sonnet-4-6-1m` | Claude Sonnet 4.6 (1M) | 1M | ✅ |
| `claude-opus-4-6` | Claude Opus 4.6 | 200k | ✅ |
| `claude-sonnet-4-6` | Claude Sonnet 4.6 (default) | 200k | ✅ |
| `claude-haiku-4-5` | Claude Haiku 4.5 | 200k | ✅ |
| `gpt-5.5` / `gpt-5.4` | GPT-5.5 / 5.4 | 1M | ✅ |
| `gpt-5.2` | GPT-5.2 | 400k | ✅ |
| `gpt-5.4-mini` / `gpt-5.4-nano` | GPT-5.4 Mini / Nano | 400k | ✅ |
| `deep-seek-v4-pro-baseten` | DeepSeek V4 Pro | 1M | – |
| `deep-seek-v4-flash` | DeepSeek V4 Flash | 1M | – |
| `trinity-large-thinking` | Trinity Large Thinking | 512k | – |
| `kimi-k2p6` | Kimi K2.6 | 256k | ✅ |
| `minimax-m2p7` | MiniMax M2.7 | 192k | – |

### Image generation (11 models · `tools.image_generation`)

`nano-banana-2`, `nano-banana-pro`, `gpt-image-2`, `fal-ai/flux-2-pro`, `fal-ai/z-image/turbo`, `fal-ai/bytedance/seedream/v5/lite`, `fal-bria-rmbg`, `fal-ai/recraft-clarity-upscale`, `fal-ai/image-editing/text-removal`, `flux-pro/outpaint`, `bbox-segment`.

### Video generation (18 models · `tools.video_generation`)

`kling/v3`, `gemini/veo3.1` (+ `reference-to-video`, `first-last-frame-to-video`), `minimax/hailuo-2.3/standard`, `wan/v2.7`, `vidu/q3`, `runway/gen4_turbo`, `pixverse/v6`, `fal-ai/bytedance/seedance-2.0`, `sora-2`, `sora-2-pro`, `xai/grok-imagine-video` (+ extension), `alibaba/happy-horse` (+ reference / edit), `fal-ai/bytedance-upscaler/upscale/video`.

### Audio / Music / TTS (13 models · `tools.audio_generation`)

`google/gemini-3.1-flash-tts-preview`, `elevenlabs/v3-tts`, `fal-ai/elevenlabs/tts/multilingual-v2`, `fal-ai/minimax/speech-2.8-hd`, `elevenlabs/music`, `elevenlabs/sound-effects`, `CassetteAI/music-generator`, `fal-ai/minimax-music/v2.6`, `mureka/song-generator`, `mureka/instrumental-generator`, `google/lyria-music`, `elevenlabs/voice-clone`, `elevenlabs/voice-changer`.

## Supported AI Tools

Each tool is exposed at `POST /tools/run/<name>` and forwarded to `POST https://www.genspark.ai/api/tool_cli/<name>`.

| Tool | Description | Requires Project ID |
|---|---|:---:|
| `web_search` | Real-time Google-style search. |  |
| `image_search` | Creative-Commons photo search. |  |
| `crawler` | Fetch any URL as Markdown (+ JS rendering). |  |
| `summarize_large_document` | Q&A on very long PDFs / web pages. | ✅ |
| `understand_images` | Multi-modal vision analysis. |  |
| `image_generation` | Text-to-image (11 models). | ✅ |
| `video_generation` | Text-/image-to-video (18 models). | ✅ |
| `audio_generation` | TTS, music, sound effects, voice. | ✅ |
| `audio_transcribe` | Speech-to-text w/ word-level timestamps. | ✅ |
| `create_task` | Spawn deep-research / docs / slides / sheets agents. | ✅ |
| `stock_price` | Live ticker quotes. |  |

> The full Genspark catalog has **171 tools** (gmail, calendar, GitHub, Slack, Notion, Salesforce, HubSpot, Pipedrive, phone-call, social media, …). The curated 11 above are the ones SelfAI ships with a built-in UI; you can call any of the remaining 160 directly via `GensparkService::callTool(...)`.

## Installation & Deployment

### 1. Drop-in (no SSH, no Composer) — *recommended for shared hosting*

1. Download the latest `selfai.tar.gz` / `selfai.zip` (see [Releases](#)).
2. Upload the archive to your hosting account via cPanel / Plesk / FTP.
3. Extract it into your web root (or any sub-folder).
4. Copy `.env.example` → `.env` and **edit** it (see [Configuration](#configuration)).
5. Visit `https://your-domain.com/selfai/` (or wherever you put it) — the front controller is `public/index.php` and the root `.htaccess` rewrites everything for sub-folder deployment.
6. The first request will:
   * generate an APP_KEY automatically (if missing — or run `php artisan key:generate` if you have CLI access),
   * create the main SQLite DB at `database/database.sqlite` and run the migrations on demand.

### 2. Standard install (with Composer / Node)

```bash
git clone <your-fork>.git selfai && cd selfai
composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
npm ci && npm run build
```

Point your web-server document-root at `selfai/public/`.

## Configuration

The whole app is controlled by `.env`:

```env
APP_NAME=SelfAI
APP_URL=https://your-domain.com
APP_KEY=base64:...                          # php artisan key:generate
APP_LOCALE=en

DB_CONNECTION=sqlite                        # main DB (users only)
DB_DATABASE=/full/path/to/database/database.sqlite

# Genspark (all of these can be overridden per-user via Settings)
GENSPARK_API_KEY=                           # fallback for dev / single-tenant
GENSPARK_BASE_URL=https://www.genspark.ai
GENSPARK_DEFAULT_MODEL=claude-sonnet-4-6
GENSPARK_TIMEOUT=120
GENSPARK_MAX_TOKENS=2048
GENSPARK_TEMPERATURE=0.7
```

> **In true SaaS mode, leave `GENSPARK_API_KEY` blank** — every user provides their own via the Settings page.

## User Guide

1. **Register** at `/register` (client-side validation runs first).
2. **Save your API key** at `/settings` — paste your `gsk-…` key.
   * If Genspark rejects it, you'll see the exact upstream error (HTTP status + body + hint).
   * On success, the page confirms with `Connected as you@example.com · free`.
3. **(Optional)** add your Genspark **Project ID** to unlock the media tools.
4. **Chat** at `/chat`:
   * Pick a model in the composer (defaults to your *Preferred Model*).
   * `Enter` to send, `Shift+Enter` for newline.
   * Hover any AI reply → **Copy**, **Regenerate**, or **Edit** then re-generate.
5. **AI Tools** at `/tools`:
   * Pick a tool on the left.
   * Fill the form (model picker, aspect ratio, size, …).
   * **Run** — generated images / videos / audio appear inline.
   * **Copy raw JSON** for use elsewhere.
6. **Conversations** at `/conversations` — list, rename, delete.

## Form Validation

Every form does **two passes**:

1. **Client-side** (`<script>` inside each Blade view).
   * Runs `on submit` *before* the network call.
   * All messages come from `lang/en/<file>.php → 'js'` arrays, so they translate automatically.
   * Cancels the submission and scrolls to the first error.
2. **Server-side** (Laravel `$request->validate(...)`) — the unconditional safety net (also returns translated messages via `lang/en/validation.php`).

| Form | Client checks | Server rules |
|---|---|---|
| Register | name required, valid email, password ≥ 8, password match | `required`, `email`, `confirmed`, `min:8`, `unique:users` |
| Login | valid email, password required | `email`, `required` |
| Forgot password | valid email | `email`, `required` |
| Reset password | valid email, password ≥ 8, match | `confirmed`, `min:8` |
| Settings | `gsk[-_]…` format, temp 0–2, max_tokens 64–8192, prompt ≤ 8000 | + live key check against Genspark `/me` |
| Chat composer | message required, ≤ 8000 chars | + content length |
| Tools | per-tool: query / URL required | + URL validity, max-length |

## Internationalisation

Every user-facing string lives under `lang/en/`:

| File | Used for |
|---|---|
| `lang/en/auth.php` | Built-in Laravel auth strings |
| `lang/en/auth_ui.php` | Auth Blade view labels + JS messages |
| `lang/en/chat.php` | Chat UI + Tools UI + JS validation messages |
| `lang/en/common.php` | Navigation, app name, flash messages |
| `lang/en/passwords.php` | Password reset emails |
| `lang/en/pagination.php` | Pagination links |
| `lang/en/profile.php` | Profile Blade view |
| `lang/en/settings.php` | Settings page + API-key error hints + JS messages |
| `lang/en/validation.php` | Server-side validation rules |
| `lang/en.json` | Free-form `__('foo bar')` strings |

To add a new locale (e.g. French), copy `lang/en/` → `lang/fr/`, translate each value, and set `APP_LOCALE=fr` (or let each user pick their own via the `locale` column on `users`).

## API Routes

```
GET   /                           welcome → /chat if auth, /login otherwise
GET   /register · POST /register  (Breeze)
GET   /login    · POST /login     (Breeze)
POST  /logout                     (Breeze)
GET|POST /forgot-password         (Breeze)
GET|POST /reset-password/{token}  (Breeze)
GET   /verify-email               (Breeze)

GET   /settings                   SettingsController@edit
PATCH /settings                   SettingsController@update      [validates key via /me]

GET   /profile                    ProfileController@edit
PATCH /profile · DELETE /profile

# require.api_key middleware:
GET   /chat                       ChatController@index
GET   /chat/{conversation}        ChatController@index
POST  /chat                       ChatController@send            (new conversation)
POST  /chat/{conversation}/send   ChatController@send
POST  /chat/{conversation}/regenerate
PATCH /chat/{conversation}/messages/{message}

GET   /conversations              ConversationController@index
PATCH /conversations/{id}         rename
DEL   /conversations/{id}         delete

GET   /tools                      ToolsController@index
POST  /tools/run/{tool}           ToolsController@run            ← proxies to /api/tool_cli/{tool}
```

## Troubleshooting

### "Genspark rejected your API key. [GET /me · HTTP 401] …"

The detailed message comes straight from the upstream API. Common causes:

| Status | Meaning | Fix |
|---|---|---|
| 401 | Bad / expired key | Re-copy the whole `gsk-…` key from Genspark. |
| 403 | Plan does not allow this | Upgrade your Genspark plan. |
| 404 | Endpoint mismatch | Update SelfAI — the integration may need a refresh. |
| 429 | Rate-limited | Wait a minute, retry. |
| 5xx | Genspark outage | Try again later. |

### Tool returns `tools_no_project`

Some tools (image / video / audio / docs / deep-research) require a **Genspark Project ID**. Set one in `/settings` → *Genspark Project ID*.

### "Unable to locate a class or view for component [guest-layout]"

Already fixed in v0.2 — make sure `app/View/Components/GuestLayout.php` and `AppLayout.php` exist (they are now shipped in the archive).

## Roadmap

* **Multi-AI agents** — chain tools together (research → write → image → publish).
* **Infinite memory** — vector embeddings of past conversations stored in the tenant DB (using Genspark's embeddings endpoint).
* **Per-user file uploads** that route through `tools.aidrive` for permanent storage.
* **Streaming responses** — switch `chat()` to `requestStreaming` SSE.
* **Self-hostable workers** — optional queue runner for long-running tools (video gen).
* **Stripe billing** — turn the per-user API key field into a managed proxy if the operator wants to resell access.

---

## Deployment Status

* **Platform**: Any PHP 8.2+ shared host with mod_rewrite (cPanel, Plesk, Hetzner, …) or Laravel Herd locally.
* **Status**: ✅ Active (v0.3 — SaaS + Tools update)
* **Tech Stack**: Laravel 12 · Breeze (Blade) · Alpine.js · Tailwind CSS · SQLite · Genspark API.
* **Last updated**: 2026-05-27
