<?php

/*
|--------------------------------------------------------------------------
| Genspark AI Configuration
|--------------------------------------------------------------------------
|
| All upstream endpoints, model catalogues and tool definitions live here.
| In SaaS mode each tenant overrides `api_key` at runtime via
| GensparkService::setApiKey() — but the base URL, timeouts, and the
| catalogues below stay constant for the whole installation.
|
| Model & tool lists are reverse-engineered from @genspark/cli v1.0.18
| (see https://www.npmjs.com/package/@genspark/cli).
|
*/

return [

    // ───────── HTTP transport ─────────
    'api_key'   => env('GENSPARK_API_KEY'),
    'base_url'  => env('GENSPARK_BASE_URL', 'https://www.genspark.ai'),
    'timeout'   => (int) env('GENSPARK_TIMEOUT', 120),

    // ───────── Default chat parameters ─────────
    'default_model' => env('GENSPARK_DEFAULT_MODEL', 'claude-sonnet-4-6'),
    'max_tokens'    => (int)   env('GENSPARK_MAX_TOKENS', 2048),
    'temperature'   => (float) env('GENSPARK_TEMPERATURE', 0.7),

    // ───────── LLM (text) models offered in the chat composer ─────────
    // The keys are the Genspark model ids; values are pretty labels.
    // All of these are OpenAI-compatible via /api/llm_proxy/v1/chat/completions.
    'models' => [
        // Anthropic Claude family
        'claude-opus-4-7'      => 'Claude Opus 4.7  · 1M ctx · vision',
        'claude-opus-4-6-1m'   => 'Claude Opus 4.6 (1M) · vision',
        'claude-sonnet-4-6-1m' => 'Claude Sonnet 4.6 (1M) · vision',
        'claude-opus-4-6'      => 'Claude Opus 4.6 · 200k · vision',
        'claude-sonnet-4-6'    => 'Claude Sonnet 4.6 · 200k · vision',
        'claude-haiku-4-5'     => 'Claude Haiku 4.5 · fast · vision',

        // OpenAI GPT family
        'gpt-5.5'              => 'GPT-5.5 · 1M ctx · vision',
        'gpt-5.4'              => 'GPT-5.4 · 1M ctx · vision',
        'gpt-5.2'              => 'GPT-5.2 · 400k · vision',
        'gpt-5.4-mini'         => 'GPT-5.4 Mini · 400k · vision',
        'gpt-5.4-nano'         => 'GPT-5.4 Nano · cheap · vision',

        // Open / specialty
        'deep-seek-v4-pro-baseten' => 'DeepSeek V4 Pro · reasoning · 1M',
        'deep-seek-v4-flash'   => 'DeepSeek V4 Flash · fast · 1M',
        'trinity-large-thinking' => 'Trinity Large Thinking · 512k',
        'kimi-k2p6'            => 'Kimi K2.6 · 256k · vision',
        'minimax-m2p7'         => 'MiniMax M2.7 · 192k',
    ],

    // ───────── Image-generation models (POST /api/tool_cli/image_generation) ─────────
    'image_models' => [
        'nano-banana-2'                       => 'Nano-Banana 2 · Gemini 3.1 Flash Image (default)',
        'nano-banana-pro'                     => 'Nano-Banana Pro · SOTA multi-image fusion · 4K',
        'gpt-image-2'                         => 'GPT Image 2 · superior text rendering',
        'fal-ai/flux-2-pro'                   => 'Flux 2 Pro · pro-grade realism',
        'fal-ai/z-image/turbo'                => 'Z-Image Turbo · fast & cheap',
        'fal-ai/bytedance/seedream/v5/lite'   => 'Seedream v5 Lite · 2K text layout',
        'fal-bria-rmbg'                       => 'Bria RMBG · remove background',
        'fal-ai/recraft-clarity-upscale'      => 'Recraft Clarity · upscale image',
        'fal-ai/image-editing/text-removal'   => 'Text/Watermark Removal',
        'flux-pro/outpaint'                   => 'Flux Pro Outpaint · expand canvas',
        'bbox-segment'                        => 'BBox Segment · extract subject by box',
    ],
    'image_aspect_ratios' => ['auto', '1:1', '4:3', '16:9', '9:16', '3:4', '2:3', '3:2'],
    'image_sizes'         => ['auto', '0.5k', '1k', '2k', '3k', '4k'],

    // ───────── Video-generation models (POST /api/tool_cli/video_generation) ─────────
    'video_models' => [
        'kling/v3'                                  => 'Kling V3 · w/ audio · pro/std',
        'gemini/veo3.1'                             => 'Gemini Veo 3.1 · 8s · HD',
        'gemini/veo3.1/reference-to-video'          => 'Veo 3.1 · reference-to-video',
        'gemini/veo3.1/first-last-frame-to-video'   => 'Veo 3.1 · first-last frame',
        'minimax/hailuo-2.3/standard'               => 'Hailuo 2.3 Standard · 6/10s',
        'wan/v2.7'                                  => 'Wan v2.7 · 5s · 480/720p',
        'vidu/q3'                                   => 'Vidu Q3 · w/ audio · 1–16s',
        'runway/gen4_turbo'                         => 'Runway Gen-4 Turbo · 5/10s',
        'pixverse/v6'                               => 'PixVerse V6 · cinematic',
        'fal-ai/bytedance/seedance-2.0'             => 'Seedance 2.0 · w/ audio · lip-sync',
        'sora-2'                                    => 'OpenAI Sora 2 · 4/8/12s',
        'sora-2-pro'                                => 'Sora 2 Pro · 1080p · cinematic',
        'xai/grok-imagine-video'                    => 'xAI Grok Imagine · 720p · 1–15s',
        'xai/grok-imagine-video/video-extension'    => 'Grok Imagine · extend video',
        'alibaba/happy-horse'                       => 'Alibaba Happy Horse · 720/1080p',
        'alibaba/happy-horse/reference-to-video'    => 'Happy Horse · ref-to-video',
        'alibaba/happy-horse/video-edit'            => 'Happy Horse · video-edit',
        'fal-ai/bytedance-upscaler/upscale/video'   => 'Video Upscaler · → 2K',
    ],
    'video_aspect_ratios' => ['16:9', '9:16', '4:3', '1:1', '9:21'],

    // ───────── Audio-generation models (POST /api/tool_cli/audio_generation) ─────────
    'audio_models' => [
        // Text-to-speech
        'google/gemini-3.1-flash-tts-preview' => 'Gemini 3.1 Flash TTS · best expressivity',
        'elevenlabs/v3-tts'                   => 'ElevenLabs v3 · multi-speaker',
        'fal-ai/elevenlabs/tts/multilingual-v2' => 'ElevenLabs Multilingual v2',
        'fal-ai/minimax/speech-2.8-hd'        => 'MiniMax Speech 2.8 HD · CN/JP/KR',

        // Music / sound
        'elevenlabs/music'                    => 'ElevenLabs Music · up to 5min',
        'elevenlabs/sound-effects'            => 'ElevenLabs Sound Effects · ≤22s',
        'CassetteAI/music-generator'          => 'Cassette AI · BG music · ≤3min',
        'fal-ai/minimax-music/v2.6'           => 'MiniMax Music 2.6 · song w/ lyrics',
        'mureka/song-generator'               => 'Mureka Song Generator · ≤3min',
        'mureka/instrumental-generator'       => 'Mureka Instrumental · ≤3min',
        'google/lyria-music'                  => 'Google Lyria · 44.1kHz · vocals',

        // Voice
        'elevenlabs/voice-clone'              => 'ElevenLabs Voice Clone',
        'elevenlabs/voice-changer'            => 'ElevenLabs Voice Changer',
    ],

    // ───────── Curated Tool catalogue (a sub-set of the 171 available) ─────────
    // Tools are POSTed to /api/tool_cli/<name> with these arg keys.
    'tools' => [
        'web_search' => [
            'label'       => 'Web Search',
            'description' => 'Real-time Google-style web search.',
            'icon'        => '🔎',
            'fields'      => ['q' => ['type'=>'text','required'=>true,'label'=>'Query']],
        ],
        'image_search' => [
            'label'       => 'Image Search',
            'description' => 'Creative-Commons-filtered photo search.',
            'icon'        => '🖼️',
            'fields'      => ['query' => ['type'=>'text','required'=>true,'label'=>'Query']],
        ],
        'crawler' => [
            'label'       => 'URL Reader',
            'description' => 'Fetch any URL as Markdown (with optional JS rendering).',
            'icon'        => '🌐',
            'fields'      => ['url' => ['type'=>'url','required'=>true,'label'=>'URL']],
        ],
        'summarize_large_document' => [
            'label'       => 'Summarize Document',
            'description' => 'Answer specific questions about very long PDFs / web pages.',
            'icon'        => '📄',
            'fields'      => [
                'url'      => ['type'=>'url','required'=>true,'label'=>'Document URL'],
                'question' => ['type'=>'text','required'=>true,'label'=>'Question'],
            ],
            'requires_project' => true,
        ],
        'understand_images' => [
            'label'       => 'Understand Images',
            'description' => 'Multi-modal vision — describe / analyze images.',
            'icon'        => '👁️',
            'fields'      => [
                'image_urls'  => ['type'=>'url_list','required'=>true,'label'=>'Image URLs (one per line)'],
                'instruction' => ['type'=>'textarea','required'=>true,'label'=>'What should I analyze?'],
            ],
        ],
        'image_generation' => [
            'label'       => 'Image Generation',
            'description' => 'Text-to-image (11 specialised models).',
            'icon'        => '🎨',
            'requires_project' => true,
        ],
        'video_generation' => [
            'label'       => 'Video Generation',
            'description' => 'Text/image-to-video (18 models inc. Sora & Veo).',
            'icon'        => '🎬',
            'requires_project' => true,
        ],
        'audio_generation' => [
            'label'       => 'Audio / Music / TTS',
            'description' => 'Generate TTS, music, sound effects, voice clones.',
            'icon'        => '🎵',
            'requires_project' => true,
        ],
        'audio_transcribe' => [
            'label'       => 'Audio Transcribe',
            'description' => 'Speech-to-text with word-level timestamps.',
            'icon'        => '📝',
            'requires_project' => true,
        ],
        'create_task' => [
            'label'       => 'Specialist Agents',
            'description' => 'Spawn deep-research, docs, slides, sheets, podcasts agents.',
            'icon'        => '🤖',
            'requires_project' => true,
        ],
        'stock_price' => [
            'label'       => 'Stock Price',
            'description' => 'Live ticker quotes & charts.',
            'icon'        => '📈',
            'fields'      => ['symbol' => ['type'=>'text','required'=>true,'label'=>'Symbol']],
        ],
    ],
];
