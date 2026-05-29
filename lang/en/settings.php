<?php

return [
    'page_title'             => 'AI Settings',
    'page_description'       => 'Choose your default model and customize how SelfAI behaves in every new conversation.',

    // --- Onboarding ---
    'onboarding_title'       => 'Welcome to SelfAI 👋',
    'onboarding_intro'       => 'Before you can start chatting, please add your personal Genspark AI API key. Your key is stored encrypted on this server and is only used to talk to Genspark on your behalf.',
    'onboarding_get_key'     => 'Don\'t have a key yet? Get one from your Genspark account →',
    'onboarding_continue'    => 'Save & continue to chat',

    // --- API key ---
    'api_key'                => 'Genspark API Key',
    'api_key_help'           => 'Paste the key that starts with "gsk-…". Leave blank to keep your current key. The key is encrypted before it is saved to the database.',
    'api_key_placeholder'    => 'gsk-...',
    'api_key_set'            => '✓ Your API key is set. Leave the field blank to keep it.',
    'api_key_remove'         => 'Remove key',

    // --- API-key validation errors (with detailed upstream error) ---
    'api_key_invalid'        => 'Genspark rejected your API key.  :error',
    'api_key_bad_format'     => 'API keys must start with "gsk-" or "gsk_" (the format Genspark gives you).',
    'api_key_empty'          => 'No API key was provided.',
    'api_key_unknown_error'  => 'No further detail was returned by Genspark. Please verify the key and try again.',
    'api_key_hint_401'       => 'The key is unauthorised. Double-check that you copied the whole key and that it has not expired.',
    'api_key_hint_403'       => 'Your Genspark account is not allowed to use this resource — check your plan.',
    'api_key_hint_404'       => 'The Genspark endpoint was not found — the integration may need an update.',
    'api_key_hint_429'       => 'Rate-limited by Genspark. Please wait a moment and retry.',
    'api_key_hint_5xx'       => 'Genspark is currently experiencing an outage. Please retry in a few minutes.',

    'api_account_connected'  => 'Connected as :account',

    // --- Multi-key SaaS UI ---
    'api_keys_title'           => 'API Keys',
    'api_keys_subtitle'        => 'Add as many Genspark keys as you want. SelfAI will automatically roll over to the next active key if your primary key is rate-limited or revoked.',
    'api_keys_empty'           => 'You have not added any Genspark API keys yet.',
    'api_keys_add'             => 'Add new API key',
    'api_keys_validate'        => 'SelfAI validates your key against /me before saving it.',
    'api_key_label_field'      => 'Label (optional)',
    'api_key_label_placeholder'=> 'e.g. Personal Pro key',
    'api_key_default_label'    => 'My Genspark Key',
    'api_key_legacy_label'     => 'Legacy key',
    'api_key_field'            => 'gsk-… key',
    'api_key_saved'            => 'API key added and validated.',
    'api_key_deleted'          => 'API key deleted.',
    'api_key_duplicate'        => 'You already added this exact key.',
    'api_key_primary'          => 'Primary',
    'api_key_active'           => 'Active',
    'api_key_inactive'         => 'Disabled',
    'api_key_cooldown'         => 'Cooling down — re-enables :time',
    'api_key_test_ok'          => 'Key is valid and Genspark accepted it.',
    'api_key_test'             => 'Test',
    'api_key_make_primary'     => 'Make primary',
    'api_key_set_primary_help' => 'The primary key is tried first on every Genspark request.',
    'api_key_primary_set'      => 'Primary key set to :label.',
    'api_key_disable'          => 'Disable',
    'api_key_enable'           => 'Enable',
    'api_key_delete'           => 'Delete',
    'api_key_must_be_active'   => 'Only active keys can be made primary.',
    'api_key_confirm_delete'   => 'Delete this API key? This cannot be undone.',
    'api_key_plan'             => 'plan: :plan',
    'api_key_last_used'        => 'last used :time',
    'api_key_never_used'       => 'never used',

    // --- Auto-failover ---
    'auto_failover'            => 'Auto-failover',
    'auto_failover_help'       => 'When ON, calls that fail with 401/429/5xx are silently retried with the next active key in your pool. When OFF, only the primary key is used.',
    'auto_failover_on'         => 'Auto-failover is ON — rate-limits will retry with your next active key.',
    'auto_failover_off'        => 'Auto-failover is OFF — only the primary key will be used.',

    // --- Project ID (optional, required only for media tools) ---
    'project_id'             => 'Genspark Project ID',
    'project_id_optional'    => 'Genspark Project ID (optional)',
    'project_id_help'        => 'Some tools (image / video / audio generation, deep-research, …) require a Project ID. Add the project IDs you use here, then pick one as default.',
    'project_id_placeholder' => 'proj-…',
    'project_id_bad_format'  => 'Project IDs may only contain letters, numbers, _ - : and dot.',
    'project_id_duplicate'   => 'You already added this project ID.',

    'projects_title'         => 'Projects',
    'projects_subtitle'      => 'Genspark does not expose a list-my-projects API, so you add the projects you want to use here yourself. Once added they become one-click picks in every tool.',
    'projects_empty'         => 'You have not added any projects yet.',
    'projects_add'           => 'Add new project',
    'project_label_field'    => 'Friendly label',
    'project_label_placeholder' => 'e.g. My image-gen project',
    'project_added'          => 'Project added.',
    'project_deleted'        => 'Project deleted.',
    'project_default_set'    => 'Default project set to :label.',
    'project_make_default'   => 'Make default',
    'project_default'        => 'Default',
    'project_confirm_delete' => 'Remove this project from your list? You can re-add it later.',
    'project_help_external'  => 'Find your project IDs at',

    // --- Model / behavior ---
    'preferred_model'        => 'Preferred Model',
    'preferred_model_help'   => 'Used when you start a new chat. You can override per-conversation in the composer.',
    'use_default'            => '— Use default (:model) —',

    'system_prompt'          => 'System Prompt',
    'system_prompt_optional' => 'System Prompt (optional)',
    'system_prompt_help'     => 'This instruction is prepended to every new conversation. Use it to give SelfAI a persona or specific instructions.',
    'system_prompt_placeholder' => 'You are SelfAI, a helpful assistant…',

    'temperature'            => 'Temperature',
    'temperature_help'       => 'Controls randomness. Lower = more focused, higher = more creative. Default: :default',

    'max_tokens'             => 'Max response tokens',
    'max_tokens_help'        => 'The maximum length of each AI response. Default: :default',

    'save_settings'          => 'Save settings',
    'reset_defaults'         => 'Reset to defaults',

    // --- Section tabs ---
    'tab_general'            => 'General',
    'tab_keys'               => 'API Keys',
    'tab_projects'           => 'Projects',
    'tab_advanced'           => 'Advanced',

    // --- Client-side validation messages ---
    'js' => [
        'api_key_required_onboarding' => 'Please paste your Genspark API key to continue.',
        'api_key_bad_format'          => 'Keys must start with "gsk-" or "gsk_" and be at least 20 characters.',
        'api_key_too_short'           => 'API keys must be at least 20 characters long.',
        'temperature_range'           => 'Temperature must be between 0 and 2.',
        'max_tokens_range'            => 'Max tokens must be between 64 and 8192.',
        'system_prompt_too_long'      => 'System prompt is too long (max 8000 characters).',
        'project_id_bad_format'       => 'Project IDs may only contain letters, numbers, _ - : and dot.',
        'project_id_required'         => 'Please paste a project ID.',
        'label_required'              => 'Please enter a label.',
        'saving'                      => 'Saving…',
        'testing'                     => 'Testing…',
        'deleting'                    => 'Deleting…',
        'confirm_delete_key'          => 'Delete this API key? This cannot be undone.',
        'confirm_delete_project'      => 'Remove this project? You can re-add it later.',
    ],
];
