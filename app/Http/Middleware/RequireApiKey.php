<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Send users who haven't configured a Genspark API key to the
 * onboarding page (settings.edit). Excludes the settings & profile
 * & logout routes themselves so the user can actually fix it.
 */
class RequireApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $allow = ['settings.edit', 'settings.update', 'profile.edit', 'profile.update', 'profile.destroy', 'logout'];

        if ($user && ! $user->hasApiKey() && ! $request->routeIs($allow)) {
            return redirect()
                ->route('settings.edit')
                ->with('onboarding', true);
        }

        return $next($request);
    }
}
