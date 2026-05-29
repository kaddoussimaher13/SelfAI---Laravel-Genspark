<?php

return [
    // Page titles
    'title_new'                => 'New Chat',
    'title_chat'               => 'Chat',
    'title_conversations'      => 'Conversations',

    // Sidebar
    'sidebar_heading'          => 'Conversations',
    'sidebar_empty'            => 'No conversations yet.',
    'sidebar_search'           => 'Search conversations…',
    'sidebar_pinned'           => 'Pinned',
    'sidebar_recent'           => 'Recent',

    // Welcome / empty state
    'welcome_hero'             => '👋 Start a conversation',
    'welcome_subtitle'         => 'Type a message below and SelfAI will reply via Genspark.',
    'welcome_suggestions'      => 'Try one of these:',
    'suggestion_1'             => 'Explain quantum computing in simple terms',
    'suggestion_2'             => 'Write a Python script that downloads a file',
    'suggestion_3'             => 'Draft a polite follow-up email to a client',
    'suggestion_4'             => 'Summarize the pros and cons of remote work',

    // Composer
    'composer_placeholder'     => 'Send a message…  (Shift+Enter for newline, Enter to send)',
    'composer_send'            => 'Send',
    'composer_stop'            => 'Stop',
    'composer_attach'          => 'Attach',
    'composer_model'           => 'Model',
    'composer_hint_shortcut'   => 'Press Enter to send, Shift+Enter for newline',
    'composer_thinking'        => 'SelfAI is thinking…',

    // Message bubbles
    'role_user'                => 'You',
    'role_assistant'           => 'SelfAI',
    'role_system'              => 'System',
    'msg_copy'                 => 'Copy',
    'msg_copied'               => 'Copied!',
    'msg_regenerate'           => 'Regenerate',
    'msg_edit'                 => 'Edit',
    'msg_delete'               => 'Delete',
    'msg_tokens'               => ':count tokens',
    'msg_model_label'          => 'Model: :model',
    'msg_edited'               => 'edited',
    'msg_empty_response'       => '(empty response)',

    // Errors
    'error_api'                => '⚠️ Error talking to Genspark: :error',
    'error_key_missing'        => 'Genspark API key is not configured. Add GENSPARK_API_KEY to your .env file.',
    'error_send_failed'        => 'Could not send message. Please try again.',
    'error_rate_limited'       => 'You are sending messages too fast. Please slow down.',

    // Conversation management
    'conv_count_messages'      => ':count messages',
    'conv_updated_ago'         => 'updated :time',
    'conv_default_title'       => 'New chat',
    'conv_delete_confirm'      => 'Delete this conversation? This cannot be undone.',
    'conv_rename_label'        => 'New title',
    'conv_empty'               => 'No conversations yet.',
    'conv_start_one'           => 'Start one',

    // --- Tools panel (image / video / audio / search / …) ---
    'tools_title'              => 'AI Tools',
    'tools_subtitle'           => 'Generate images, videos, audio, run web searches, transcribe files, and more — powered by Genspark.',
    'tools_open'               => 'Open AI tools',
    'tools_run'                => 'Run',
    'tools_running'            => 'Running…',
    'tools_no_key'             => 'You need to save your Genspark API key first.',
    'tools_no_project'         => 'This tool requires a Genspark Project ID — set one on the Settings page.',
    'tools_result_title'       => 'Result',
    'tools_copy_json'          => 'Copy raw JSON',
    'tools_back'               => '← Back to chat',

    'tools_aspect_ratio'       => 'Aspect ratio',
    'tools_image_size'         => 'Image size',
    'tools_duration'           => 'Duration (seconds)',
    'tools_model'              => 'Model',
    'tools_project'            => 'Project',

    // Client-side validation
    'js' => [
        'message_required'  => 'Please type a message before sending.',
        'message_too_long'  => 'Message is too long (max :max characters).',
        'title_required'    => 'Title can\'t be empty.',
        'title_too_long'    => 'Title is too long (max 120 characters).',
        'tool_query_required'   => 'Please describe what you want to generate.',
        'tool_url_required'     => 'A valid URL is required.',
        'send_failed'           => 'Could not reach the server. Check your connection and retry.',
        'send_failed_unknown'   => 'Unexpected error — please try again.',
        'confirm_delete_conv'   => 'Delete this conversation? This cannot be undone.',
        'rename'                => 'Rename',
        'renaming'              => 'Renaming…',
        'deleting'              => 'Deleting…',
        'deleted'               => 'Deleted.',
        'renamed'               => 'Renamed.',
    ],
];
