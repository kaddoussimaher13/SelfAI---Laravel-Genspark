<?php

return [
    // Hero
    'hero_eyebrow'    => 'Self-hosted • Multi-tenant • BYO Genspark key',
    'hero_title'      => 'Your private AI workspace, powered by Genspark.',
    'hero_subtitle'   => 'SelfAI is a fast, multi-user chat platform that runs on your own server. Bring your Genspark API keys, add as many as you want, and SelfAI will silently fail over when one is rate-limited.',
    'hero_cta_primary' => 'Get started — free',
    'hero_cta_secondary' => 'See pricing',
    'hero_dashboard_alt' => 'SelfAI dashboard preview',

    // Trust strip
    'trust_strip' => 'Trusted by builders, indie hackers and small teams',

    // Feature grid (12 features)
    'features_eyebrow' => 'Everything you need',
    'features_title'   => 'Built for power users — friendly for everyone',
    'features_subtitle'=> 'SelfAI bundles a polished chat UI, an AI-tools panel, multi-key management and a usage dashboard into a single self-hostable Laravel app.',

    'feat_chat_title'        => 'Multi-model chat',
    'feat_chat_desc'         => 'All 16 Genspark LLMs (Claude, GPT-5, DeepSeek, Gemini, Llama, Grok, Qwen, Kimi) in a familiar threaded UI with copy / edit / regenerate.',

    'feat_keys_title'        => 'Many keys, one click',
    'feat_keys_desc'         => 'Add as many gsk-… keys as you want. Auto-failover rolls over to the next active key on 401/429/5xx, with per-key cool-downs.',

    'feat_tools_title'       => 'Full AI-tools suite',
    'feat_tools_desc'        => 'Image generation, video generation, audio, transcription, web search, image search, document Q&A and 100+ other Genspark tools.',

    'feat_tenants_title'     => 'Multi-tenant isolation',
    'feat_tenants_desc'      => 'Every user gets their own encrypted SQLite database under storage/app/tenants/. Chats stay private; admins never see them.',

    'feat_stats_title'       => 'Usage dashboard',
    'feat_stats_desc'        => 'Daily charts, per-tool / per-model / per-key breakdowns and error counts — know exactly how many Genspark credits each feature burns.',

    'feat_i18n_title'        => 'Multi-language UI',
    'feat_i18n_desc'         => 'The entire interface (forms, errors, validation messages) is translated. Add more locales by dropping a folder under lang/.',

    'feat_security_title'    => 'Encrypted at rest',
    'feat_security_desc'     => 'API keys are encrypted with Laravel\'s APP_KEY before they hit the database. They are never logged in clear-text.',

    'feat_validation_title'  => 'Client + server validation',
    'feat_validation_desc'   => 'Forms validate in the browser FIRST, then on the server, then on Genspark. You get instant, friendly error messages.',

    'feat_subfolder_title'   => 'Deploys anywhere',
    'feat_subfolder_desc'    => 'Drop SelfAI in a sub-folder of any shared host with the included .htaccess — or run it on Forge, Vapor, Coolify, Docker.',

    'feat_ajax_title'        => 'No-reload UI',
    'feat_ajax_desc'         => 'Every form (chat, rename, delete, settings, keys, projects) submits through fetch() and updates inline — no page flicker.',

    'feat_models_title'      => '58+ models & tools',
    'feat_models_desc'       => '16 LLMs · 11 image · 18 video · 13 audio models plus the full 171-tool Genspark CLI catalogue, exposed through a single panel.',

    'feat_open_title'        => 'Open source',
    'feat_open_desc'         => 'PHP 8.2 + Laravel 12 + Breeze + Alpine.js + Tailwind. Forkable, auditable, no proprietary magic.',

    // Pricing
    'pricing_eyebrow'      => 'Pricing',
    'pricing_title'        => 'Simple plans that scale with you',
    'pricing_subtitle'     => 'Start free with your own Genspark key. Upgrade when you need more keys, more calls per day, or team features.',
    'pricing_most_popular' => 'Most popular',
    'pricing_per'          => 'per month',
    'pricing_billed_annually' => 'billed monthly',
    'pricing_faq_title'    => 'Frequently asked questions',
    'pricing_cta_title'    => 'Ready to host your own AI workspace?',
    'pricing_cta_desc'     => 'Start with the free tier — no credit card, just your Genspark API key.',
    'pricing_cta_button'   => 'Create your account',

    // About
    'about_title'       => 'About SelfAI',
    'about_subtitle'    => 'Why we built a self-hosted, BYO-key AI workspace.',
    'about_p1'          => 'SelfAI exists because the best AI tools are still locked inside a few closed dashboards. We wanted a single place where one developer (or a small team) could plug in their own Genspark API keys, switch between dozens of models, run image / video / audio tools and still own every byte of data.',
    'about_p2'          => 'The whole app is a vanilla Laravel 12 project. There is no proprietary runtime, no hidden middleware, no telemetry. You can read every line, fork it, and host it on any LAMP server in five minutes.',
    'about_p3'          => 'Multi-key auto-failover is the feature we missed most: when one of your keys is rate-limited, SelfAI silently retries with the next one, so your conversations never break mid-thought.',

    'about_stack_title' => 'The stack',
    'about_stack_php'   => 'PHP 8.2+ — Laravel 12.x',
    'about_stack_breeze'=> 'Breeze (Blade) — auth scaffolding',
    'about_stack_alpine'=> 'Alpine.js — tiny reactive sprinkles',
    'about_stack_tw'    => 'Tailwind CSS — styling',
    'about_stack_sqlite'=> 'SQLite — per-tenant database',
    'about_stack_gen'   => '@genspark/cli — endpoint discovery',

    // Footer
    'footer_tagline'    => 'Self-hosted AI chat, powered by Genspark.',
    'footer_product'    => 'Product',
    'footer_company'    => 'Company',
    'footer_resources'  => 'Resources',
    'footer_legal'      => 'Legal',
    'footer_terms'      => 'Terms',
    'footer_privacy'    => 'Privacy',
    'footer_github'     => 'GitHub',
    'footer_docs'       => 'Docs',
    'footer_copyright'  => '© :year SelfAI. All rights reserved.',
];
