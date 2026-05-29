<?php

namespace App\Http\Controllers;

use App\Models\UsageStat;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Usage statistics dashboard.
 *
 * Each call (success or failure) made through GensparkService::dispatch() is
 * recorded in `usage_stats` aggregated per (user, api_key, date, tool, model).
 * The dashboard exposes:
 *
 *   • Today's totals (calls, tokens, errors, credits-burned estimate)
 *   • 30-day daily chart
 *   • Per-tool breakdown
 *   • Per-model breakdown
 *   • Per-API-key breakdown (how many calls did each key absorb)
 */
class StatsController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $user  = Auth::user();
        $today = Carbon::today();
        $from  = $today->copy()->subDays(29);

        // Totals
        $totals = UsageStat::where('user_id', $user->id)
            ->whereBetween('date', [$from, $today])
            ->selectRaw('SUM(calls) AS calls, SUM(tokens) AS tokens, SUM(errors) AS errors')
            ->first();

        $todayTotals = UsageStat::where('user_id', $user->id)
            ->where('date', $today)
            ->selectRaw('SUM(calls) AS calls, SUM(tokens) AS tokens, SUM(errors) AS errors')
            ->first();

        // Daily series for last 30 days
        $rows = UsageStat::where('user_id', $user->id)
            ->whereBetween('date', [$from, $today])
            ->selectRaw('date, SUM(calls) AS calls, SUM(tokens) AS tokens, SUM(errors) AS errors')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy(fn ($r) => Carbon::parse($r->date)->toDateString());

        $labels  = [];
        $calls   = [];
        $tokens  = [];
        $errors  = [];
        for ($d = $from->copy(); $d->lte($today); $d->addDay()) {
            $key = $d->toDateString();
            $labels[]  = $d->format('M j');
            $calls[]   = (int) ($rows[$key]->calls  ?? 0);
            $tokens[]  = (int) ($rows[$key]->tokens ?? 0);
            $errors[]  = (int) ($rows[$key]->errors ?? 0);
        }

        // Per-tool breakdown (top 10)
        $byTool = UsageStat::where('user_id', $user->id)
            ->whereBetween('date', [$from, $today])
            ->selectRaw('tool, SUM(calls) AS calls, SUM(tokens) AS tokens, SUM(errors) AS errors')
            ->groupBy('tool')
            ->orderByDesc('calls')
            ->limit(10)
            ->get();

        // Per-model breakdown (top 10)
        $byModel = UsageStat::where('user_id', $user->id)
            ->whereBetween('date', [$from, $today])
            ->whereNotNull('model')
            ->selectRaw('model, SUM(calls) AS calls, SUM(tokens) AS tokens')
            ->groupBy('model')
            ->orderByDesc('calls')
            ->limit(10)
            ->get();

        // Per-API-key breakdown — JOIN to get the label
        $byKey = DB::table('usage_stats')
            ->leftJoin('api_keys', 'api_keys.id', '=', 'usage_stats.api_key_id')
            ->where('usage_stats.user_id', $user->id)
            ->whereBetween('usage_stats.date', [$from, $today])
            ->selectRaw('
                usage_stats.api_key_id AS key_id,
                COALESCE(api_keys.label, ?) AS label,
                COALESCE(api_keys.account_email, "") AS account_email,
                SUM(usage_stats.calls)  AS calls,
                SUM(usage_stats.tokens) AS tokens,
                SUM(usage_stats.errors) AS errors
            ', [__('settings.api_key_legacy_label')])
            ->groupBy('usage_stats.api_key_id', 'api_keys.label', 'api_keys.account_email')
            ->orderByDesc('calls')
            ->get();

        $data = [
            'today'      => $todayTotals,
            'period'     => $totals,
            'period_from' => $from->toDateString(),
            'period_to'   => $today->toDateString(),
            'chart' => [
                'labels' => $labels,
                'calls'  => $calls,
                'tokens' => $tokens,
                'errors' => $errors,
            ],
            'by_tool'  => $byTool,
            'by_model' => $byModel,
            'by_key'   => $byKey,
        ];

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['ok' => true] + $data);
        }

        return view('stats.index', $data);
    }
}
