<?php

namespace App\Http\Controllers\Web\Admin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    private const TRACKER_TABLE = 'tracker_visits';

    /**
     * Render admin dashboard summary.
     */
    public function index()
    {
        $role = Auth::user()->role;
        $trackerReady = $this->trackerReady();
        $trackerDays = 14;

        $trackerSummary = [
            'totalVisits' => 0,
            'guestVisits' => 0,
            'uniqueVisitors' => 0,
        ];
        $trackerChartLabels = [];
        $trackerChartVisits = [];
        $recentVisits = collect();

        if ($trackerReady) {
            $trackerSummary = Cache::remember(
                "admin.dashboard.tracker.summary.{$trackerDays}",
                now()->addMinutes(3),
                function () use ($trackerDays): array {
                    $startDate = now()->subDays($trackerDays - 1)->startOfDay();

                    $summary = DB::table(self::TRACKER_TABLE)
                        ->where('visited_at', '>=', $startDate)
                        ->selectRaw('COUNT(*) as total_visits')
                        ->selectRaw('SUM(CASE WHEN is_guest = 1 THEN 1 ELSE 0 END) as guest_visits')
                        ->selectRaw('COUNT(DISTINCT visitor_hash) as unique_visitors')
                        ->first();

                    return [
                        'totalVisits' => (int) ($summary->total_visits ?? 0),
                        'guestVisits' => (int) ($summary->guest_visits ?? 0),
                        'uniqueVisitors' => (int) ($summary->unique_visitors ?? 0),
                    ];
                }
            );

            [$trackerChartLabels, $trackerChartVisits] = Cache::remember(
                "admin.dashboard.tracker.chart.{$trackerDays}",
                now()->addMinutes(3),
                function () use ($trackerDays): array {
                    $startDate = now()->subDays($trackerDays - 1)->startOfDay();
                    $dailyRows = DB::table(self::TRACKER_TABLE)
                        ->where('visited_at', '>=', $startDate)
                        ->selectRaw('DATE(visited_at) as day, COUNT(*) as visits')
                        ->groupByRaw('DATE(visited_at)')
                        ->orderBy('day')
                        ->get()
                        ->keyBy('day');

                    $labels = [];
                    $visits = [];

                    for ($offset = 0; $offset < $trackerDays; $offset += 1) {
                        $day = Carbon::parse($startDate)->addDays($offset)->toDateString();
                        $labels[] = Carbon::parse($day)->format('d M');
                        $visits[] = (int) ($dailyRows->get($day)->visits ?? 0);
                    }

                    return [$labels, $visits];
                }
            );

            $recentVisits = Cache::remember(
                'admin.dashboard.tracker.recent_visits',
                now()->addMinute(),
                fn () => DB::table(self::TRACKER_TABLE.' as tracker_visits')
                    ->leftJoin('users', 'users.id', '=', 'tracker_visits.user_id')
                    ->select([
                        'tracker_visits.visited_at',
                        'tracker_visits.path',
                        'tracker_visits.method',
                        'tracker_visits.ip_address',
                        'tracker_visits.user_agent',
                        'tracker_visits.is_guest',
                        'users.name as user_name',
                    ])
                    ->orderByDesc('tracker_visits.visited_at')
                    ->limit(8)
                    ->get()
            );
        }

        return view('admin.dashboard', [
            'usersCount' => Cache::remember('admin.users.total_count', now()->addMinutes(10), fn () => User::query()->count()),
            'categoriesCount' => Cache::remember('admin.categories.total_count', now()->addMinutes(10), fn () => Category::query()->count()),
            'productsCount' => Cache::remember('admin.products.total_count', now()->addMinutes(10), fn () => Product::query()->count()),
            'roleLabel' => $role instanceof Role ? $role->value : (string) $role,
            'trackerReady' => $trackerReady,
            'trackerDays' => $trackerDays,
            'trackerSummary' => $trackerSummary,
            'trackerChartLabels' => $trackerChartLabels,
            'trackerChartVisits' => $trackerChartVisits,
            'recentVisits' => $recentVisits,
        ]);
    }

    private function trackerReady(): bool
    {
        return Schema::hasTable(self::TRACKER_TABLE);
    }
}
