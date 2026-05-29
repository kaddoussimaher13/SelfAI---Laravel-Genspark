<?php

namespace App\Http\Controllers;

use App\Services\GensparkService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;

/**
 * AI Tools — image / video / audio / search / transcribe / etc.
 *
 * Goes through GensparkService::setUser($user), so the dispatcher fails over
 * to the next active key when the primary is rate-limited or revoked.
 */
class ToolsController extends Controller
{
    public function __construct(protected GensparkService $genspark) {}

    public function index(): View
    {
        $user = Auth::user();

        return view('tools.index', [
            'tools'             => config('genspark.tools', []),
            'imageModels'       => config('genspark.image_models', []),
            'imageAspectRatios' => config('genspark.image_aspect_ratios', []),
            'imageSizes'        => config('genspark.image_sizes', []),
            'videoModels'       => config('genspark.video_models', []),
            'videoAspectRatios' => config('genspark.video_aspect_ratios', []),
            'audioModels'       => config('genspark.audio_models', []),
            'projects'          => $user->projects()->get(),
            'defaultProject'    => $user->defaultProject(),
            'hasProjectId'      => $user->projects()->exists() || ! empty($user->gsk_project_id),
        ]);
    }

    /**
     * Generic tool runner — POST /tools/run/{tool}
     */
    public function run(Request $request, string $tool): JsonResponse
    {
        $user = Auth::user();

        $allowed = array_keys(config('genspark.tools', []));
        if (! in_array($tool, $allowed, true)) {
            return response()->json(['error' => 'Unknown tool: ' . $tool], 404);
        }

        // The project id can come from the request body (user picked one in
        // the tool form) OR fall back to the user's default project.
        $projectId = $request->input('project_id')
            ?: ($user->defaultProject()?->gsk_id)
            ?: $user->gsk_project_id;

        $needsProject = (bool) (config("genspark.tools.$tool.requires_project") ?? false);
        if ($needsProject && empty($projectId)) {
            return response()->json([
                'error' => __('chat.tools_no_project'),
            ], 422);
        }

        // Light per-tool validation — the upstream Genspark API does the rest.
        $args = $request->validate([
            'query'         => ['nullable', 'string', 'max:4000'],
            'q'             => ['nullable', 'string', 'max:4000'],
            'instruction'   => ['nullable', 'string', 'max:4000'],
            'url'           => ['nullable', 'url', 'max:2048'],
            'question'      => ['nullable', 'string', 'max:1000'],
            'model'         => ['nullable', 'string', 'max:128'],
            'aspect_ratio'  => ['nullable', 'string', 'max:16'],
            'image_size'    => ['nullable', 'string', 'max:16'],
            'duration'      => ['nullable', 'numeric', 'min:1', 'max:60'],
            'symbol'        => ['nullable', 'string', 'max:16'],
            'image_urls'    => ['nullable', 'array'],
            'image_urls.*'  => ['url'],
            'audio_urls'    => ['nullable', 'array'],
            'audio_urls.*'  => ['url'],
            'project_id'    => ['nullable', 'string', 'max:128'],
        ]);
        $args = array_filter(
            $args,
            fn ($v) => $v !== null && $v !== '' && (! is_array($v) || count($v) > 0)
        );
        unset($args['project_id']);   // not passed to upstream

        try {
            $client = (clone $this->genspark)
                ->setUser($user)              // enables dispatcher + auto-failover
                ->setProjectId($projectId);   // explicit override if user picked one

            $result = $client->callTool($tool, $args);

            return response()->json([
                'ok'          => true,
                'data'        => $result,
                'used_key_id' => $client->usedKey()?->id,
                'project_id'  => $projectId,
            ]);
        } catch (RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 502);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Unexpected: ' . $e->getMessage()], 500);
        }
    }
}
