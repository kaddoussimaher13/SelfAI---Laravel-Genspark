<?php

namespace App\Http\Controllers;

use App\Services\GensparkService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function __construct(protected GensparkService $genspark) {}

    public function edit(): View
    {
        $user = Auth::user();

        return view('settings.index', [
            'user'              => $user,
            'availableModels'   => config('genspark.models', []),
            'defaultModel'      => config('genspark.default_model'),
            'defaultTemperature'=> (float) config('genspark.temperature'),
            'defaultMaxTokens'  => (int)   config('genspark.max_tokens'),
            'isOnboarding'      => ! $user->hasApiKey(),
            'hasApiKey'         => $user->hasApiKey(),
            'apiKeys'           => $user->apiKeys()->get(),
            'projects'          => $user->projects()->get(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $data = $request->validate([
            // Legacy single-key (kept for first-time setup screen only).
            'genspark_api_key'   => ['nullable', 'string', 'min:20', 'max:4000', 'regex:/^gsk[-_].+/'],
            'gsk_project_id'     => ['nullable', 'string', 'max:128'],
            'default_project_id' => ['nullable', 'string', 'max:128'],
            'preferred_model'    => ['nullable', 'string', Rule::in(array_keys(config('genspark.models', [])))],
            'system_prompt'      => ['nullable', 'string', 'max:8000'],
            'temperature'        => ['nullable', 'numeric', 'min:0', 'max:2'],
            'max_tokens'         => ['nullable', 'integer', 'min:64', 'max:8192'],
            'auto_failover'      => ['nullable', 'boolean'],
        ], [
            'genspark_api_key.regex' => __('settings.api_key_bad_format'),
        ]);

        // Coerce auto_failover from checkbox value (string '1' / absent).
        $data['auto_failover'] = $request->boolean('auto_failover');

        // Validate the legacy key field (if filled) — typically the onboarding form.
        if (! empty($data['genspark_api_key'])) {
            $client  = (clone $this->genspark)->setApiKey($data['genspark_api_key']);
            $account = $client->validateApiKey();

            if ($account === null) {
                $detail = $client->lastError ?: __('settings.api_key_unknown_error');
                return back()
                    ->withErrors([
                        'genspark_api_key' => __('settings.api_key_invalid', ['error' => $detail]),
                    ])
                    ->withInput($request->except('genspark_api_key'));
            }

            // First key ever — also seed the api_keys table so failover works.
            if ($user->apiKeys()->count() === 0) {
                $row = $user->apiKeys()->make([
                    'label'         => $account['email'] ?? __('settings.api_key_default_label'),
                    'plan'          => $account['plan']  ?? null,
                    'account_email' => $account['email'] ?? null,
                    'is_primary'    => true,
                    'is_active'     => true,
                ]);
                $row->key = $data['genspark_api_key'];
                $row->save();
            }

            session()->flash(
                'genspark_account',
                trim(($account['email'] ?? '') . ' · ' . ($account['plan'] ?? 'free'))
            );
        } else {
            unset($data['genspark_api_key']);
        }

        $user->update($data);

        return redirect()
            ->route('settings.edit')
            ->with('status', 'settings-updated');
    }
}
