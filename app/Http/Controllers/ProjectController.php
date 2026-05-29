<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Project IDs CRUD.
 *
 * Genspark does NOT expose a public list-projects endpoint, so users save the
 * project IDs they want to use here themselves. After saving, projects appear
 * as one-click choices in every tool that requires a Project ID
 * (image / video / audio generation, deep-research, …).
 */
class ProjectController extends Controller
{
    public function index(Request $request): mixed
    {
        $user     = Auth::user();
        $projects = $user->projects()->get()->map(fn (Project $p) => $this->present($p));

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'ok'                 => true,
                'projects'           => $projects,
                'default_project_id' => $user->default_project_id,
            ]);
        }

        return view('settings.projects', [
            'user'     => $user,
            'projects' => $user->projects()->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'gsk_id' => ['required', 'string', 'max:128', 'regex:/^[A-Za-z0-9_\-:.]+$/'],
            'label'  => ['nullable', 'string', 'max:80'],
        ], [
            'gsk_id.regex' => __('settings.project_id_bad_format'),
        ]);

        $user = Auth::user();

        // Uniqueness per user.
        if ($user->projects()->where('gsk_id', $data['gsk_id'])->exists()) {
            return response()->json([
                'ok'    => false,
                'error' => __('settings.project_id_duplicate'),
            ], 422);
        }

        $isFirst = $user->projects()->count() === 0;

        $row = $user->projects()->create([
            'gsk_id'     => $data['gsk_id'],
            'label'      => $data['label'] ?: $data['gsk_id'],
            'is_default' => $isFirst,
        ]);

        // First project ever → set as user default.
        if ($isFirst) {
            $user->update(['default_project_id' => $row->gsk_id]);
        }

        return response()->json([
            'ok'      => true,
            'project' => $this->present($row),
            'message' => __('settings.project_added'),
        ]);
    }

    public function update(Request $request, Project $project): JsonResponse
    {
        $this->authorizeOwn($project);

        $data = $request->validate([
            'label' => ['required', 'string', 'max:80'],
        ]);

        $project->update($data);

        return response()->json(['ok' => true, 'project' => $this->present($project)]);
    }

    public function destroy(Project $project): JsonResponse
    {
        $this->authorizeOwn($project);
        $user = Auth::user();

        DB::transaction(function () use ($project, $user) {
            $wasDefault = $project->is_default || $user->default_project_id === $project->gsk_id;
            $project->delete();

            if ($wasDefault) {
                $next = $user->projects()->orderBy('id')->first();
                if ($next) {
                    $next->update(['is_default' => true]);
                    $user->update(['default_project_id' => $next->gsk_id]);
                } else {
                    $user->update(['default_project_id' => null]);
                }
            }
        });

        return response()->json(['ok' => true, 'message' => __('settings.project_deleted')]);
    }

    /**
     * POST /settings/projects/{project}/default
     */
    public function makeDefault(Project $project): JsonResponse
    {
        $this->authorizeOwn($project);
        $user = Auth::user();

        DB::transaction(function () use ($project, $user) {
            $user->projects()->update(['is_default' => false]);
            $project->update(['is_default' => true]);
            $user->update(['default_project_id' => $project->gsk_id]);
        });

        return response()->json([
            'ok'      => true,
            'message' => __('settings.project_default_set', ['label' => $project->label]),
        ]);
    }

    // ─────────────── helpers ───────────────

    protected function present(Project $p): array
    {
        return [
            'id'         => $p->id,
            'gsk_id'     => $p->gsk_id,
            'label'      => $p->label,
            'is_default' => (bool) $p->is_default,
            'created_at' => $p->created_at?->diffForHumans(),
        ];
    }

    protected function authorizeOwn(Project $p): void
    {
        abort_if($p->user_id !== Auth::id(), 404);
    }
}
