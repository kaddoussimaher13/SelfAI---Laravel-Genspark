<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

/**
 * Public marketing pages — no auth required.
 *
 *   /              → index   (hero + features + pricing teaser)
 *   /features      → features (full feature matrix)
 *   /pricing       → pricing (4-tier comparison)
 *   /about         → about + tech stack + open source notice
 */
class LandingController extends Controller
{
    public function index(): View
    {
        return view('landing.index', [
            'plans' => $this->plans(),
        ]);
    }

    public function features(): View
    {
        return view('landing.features');
    }

    public function pricing(): View
    {
        return view('landing.pricing', [
            'plans' => $this->plans(),
            'faq'   => $this->faq(),
        ]);
    }

    public function about(): View
    {
        return view('landing.about');
    }

    /**
     * Pricing-tier definitions. Pure presentation data — no billing wired up
     * yet, but the cards drive the /pricing page and home-page teaser.
     */
    protected function plans(): array
    {
        return [
            [
                'id'         => 'free',
                'name'       => 'Free',
                'tagline'    => 'Try SelfAI with your own Genspark key',
                'price'      => '$0',
                'period'     => 'forever',
                'highlight'  => false,
                'icon'       => 'fa-solid fa-seedling',
                'icon_color' => 'text-emerald-500',
                'cta'        => 'Start free',
                'features'   => [
                    'BYO Genspark API key',
                    '1 active API key',
                    'Up to 100 chat calls / day',
                    'All 16 LLM models',
                    'Image, video, audio tools',
                    'SQLite per-user tenant DB',
                    'Self-hosted — your server',
                ],
            ],
            [
                'id'         => 'pro',
                'name'       => 'Pro',
                'tagline'    => 'For power users and freelancers',
                'price'      => '$19',
                'period'     => '/ month',
                'highlight'  => true,
                'icon'       => 'fa-solid fa-bolt',
                'icon_color' => 'text-indigo-500',
                'cta'        => 'Go Pro',
                'features'   => [
                    'Everything in Free, plus:',
                    'Up to 5 API keys with auto-failover',
                    '5 000 chat calls / day',
                    'Usage statistics dashboard',
                    'Multi-project switching',
                    'Custom system prompts',
                    'Priority email support',
                    'Multi-language UI',
                ],
            ],
            [
                'id'         => 'team',
                'name'       => 'Team',
                'tagline'    => 'Shared keys, shared bills',
                'price'      => '$49',
                'period'     => '/ month',
                'highlight'  => false,
                'icon'       => 'fa-solid fa-people-group',
                'icon_color' => 'text-violet-500',
                'cta'        => 'Try Team',
                'features'   => [
                    'Everything in Pro, plus:',
                    'Up to 10 team members',
                    'Unlimited API keys per user',
                    '50 000 chat calls / day',
                    'Shared project library',
                    'Per-seat usage reports',
                    'Slack / webhook alerts',
                    'SSO via Google + GitHub',
                ],
            ],
            [
                'id'         => 'enterprise',
                'name'       => 'Enterprise',
                'tagline'    => 'White-label and on-prem',
                'price'      => 'Custom',
                'period'     => '',
                'highlight'  => false,
                'icon'       => 'fa-solid fa-building-shield',
                'icon_color' => 'text-amber-500',
                'cta'        => 'Contact sales',
                'features'   => [
                    'Everything in Team, plus:',
                    'Unlimited users + keys',
                    'White-label branding',
                    'On-premise install',
                    'Audit logs + SOC 2',
                    'SAML / LDAP SSO',
                    'Dedicated account manager',
                    '99.9% uptime SLA',
                ],
            ],
        ];
    }

    protected function faq(): array
    {
        return [
            [
                'q' => 'Do I need a Genspark account?',
                'a' => 'Yes — SelfAI is a BYO-key product. You bring your own Genspark API key (gsk-…), we provide the chat UI, multi-tenant storage, key fail-over and the AI-tools panel. Your key is encrypted at rest with Laravel\'s APP_KEY.',
            ],
            [
                'q' => 'How does auto-failover work?',
                'a' => 'When you add several keys and toggle "auto-failover" on, every Genspark request first goes to your primary key. If it returns 429 (rate-limit), 401/403 (revoked) or a 5xx, we silently retry with the next active key and temp-disable the failing one for a cool-down period.',
            ],
            [
                'q' => 'Is my data ever sent to SelfAI servers?',
                'a' => 'No. SelfAI is self-hosted — you run it on your own server (the SaaS-hosted plan offers a managed runtime, but the same isolation rules apply: every tenant has its own SQLite database under storage/app/tenants/).',
            ],
            [
                'q' => 'Can I switch plans later?',
                'a' => 'Yes, anytime. Upgrades are immediate; downgrades take effect at the end of the current billing cycle. No data is ever deleted on a downgrade.',
            ],
            [
                'q' => 'Which AI models can I use?',
                'a' => 'All 16 LLMs available through Genspark — Claude (Opus/Sonnet/Haiku 4.x), GPT-5.x, DeepSeek V4 Pro, Gemini, Llama, Grok, Qwen, Kimi and more. The tools panel exposes 11 image models, 18 video models and 13 audio models.',
            ],
            [
                'q' => 'Is there a yearly discount?',
                'a' => 'Yes — paying annually saves you two months on Pro and Team plans (16% off). Enterprise pricing is always annual.',
            ],
        ];
    }
}
