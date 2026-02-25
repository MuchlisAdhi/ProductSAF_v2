<?php

namespace App\Http\Controllers\Web\Admin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class TrackerAdminController extends Controller
{
    private const TRACKER_TABLE = 'tracker_visits';

    /**
     * Tracker summary with chart data.
     */
    public function summary(Request $request): View
    {
        $role = Auth::user()->role;
        $days = $this->resolvePageSize((int) $request->query('days', 14), [7, 14, 30, 60, 90], 14);

        if (! $this->trackerReady()) {
            return view('admin.tracker.summary', [
                'roleLabel' => $role instanceof Role ? $role->value : (string) $role,
                'trackerReady' => false,
                'days' => $days,
                'summary' => [
                    'totalVisits' => 0,
                    'guestVisits' => 0,
                    'uniqueVisitors' => 0,
                ],
                'chartLabels' => [],
                'chartVisits' => [],
                'chartGuestVisits' => [],
                'topPaths' => collect(),
            ]);
        }

        $startDate = now()->subDays($days - 1)->startOfDay();
        $baseQuery = DB::table(self::TRACKER_TABLE)->where('visited_at', '>=', $startDate);

        $dailyRows = (clone $baseQuery)
            ->selectRaw('DATE(visited_at) as day, COUNT(*) as visits, SUM(CASE WHEN is_guest = 1 THEN 1 ELSE 0 END) as guest_visits')
            ->groupByRaw('DATE(visited_at)')
            ->orderBy('day')
            ->get()
            ->keyBy('day');

        $chartLabels = [];
        $chartVisits = [];
        $chartGuestVisits = [];

        for ($offset = 0; $offset < $days; $offset += 1) {
            $day = Carbon::parse($startDate)->addDays($offset)->toDateString();
            $label = Carbon::parse($day)->format('d M');
            $row = $dailyRows->get($day);

            $chartLabels[] = $label;
            $chartVisits[] = (int) ($row->visits ?? 0);
            $chartGuestVisits[] = (int) ($row->guest_visits ?? 0);
        }

        $summary = [
            'totalVisits' => (clone $baseQuery)->count(),
            'guestVisits' => (clone $baseQuery)->where('is_guest', true)->count(),
            'uniqueVisitors' => (clone $baseQuery)->distinct()->count('visitor_hash'),
        ];

        $topPaths = (clone $baseQuery)
            ->select('path')
            ->selectRaw('COUNT(*) as visits')
            ->groupBy('path')
            ->orderByDesc('visits')
            ->limit(8)
            ->get();

        return view('admin.tracker.summary', [
            'roleLabel' => $role instanceof Role ? $role->value : (string) $role,
            'trackerReady' => true,
            'days' => $days,
            'summary' => $summary,
            'chartLabels' => $chartLabels,
            'chartVisits' => $chartVisits,
            'chartGuestVisits' => $chartGuestVisits,
            'topPaths' => $topPaths,
        ]);
    }

    /**
     * Visits table.
     */
    public function visits(Request $request): View
    {
        $role = Auth::user()->role;
        $query = trim((string) $request->query('q', ''));
        $pageSize = $this->resolvePageSize((int) $request->query('pageSize', 25), [10, 25, 50, 100], 25);

        if (! $this->trackerReady()) {
            return view('admin.tracker.visits', [
                'roleLabel' => $role instanceof Role ? $role->value : (string) $role,
                'trackerReady' => false,
                'query' => $query,
                'pageSize' => $pageSize,
                'totalCount' => 0,
                'filteredCount' => 0,
                'visits' => new LengthAwarePaginator([], 0, $pageSize),
            ]);
        }

        $builder = DB::table(self::TRACKER_TABLE.' as tracker_visits')
            ->leftJoin('users', 'users.id', '=', 'tracker_visits.user_id')
            ->select([
                'tracker_visits.id',
                'tracker_visits.visited_at',
                'tracker_visits.path',
                'tracker_visits.method',
                'tracker_visits.ip_address',
                'tracker_visits.user_agent',
                'tracker_visits.is_guest',
                'tracker_visits.visitor_hash',
                'users.name as user_name',
            ]);

        if ($query !== '') {
            $builder->where(function ($q) use ($query): void {
                $q->where('tracker_visits.path', 'like', "%{$query}%")
                    ->orWhere('tracker_visits.ip_address', 'like', "%{$query}%")
                    ->orWhere('tracker_visits.user_agent', 'like', "%{$query}%")
                    ->orWhere('users.name', 'like', "%{$query}%")
                    ->orWhere('tracker_visits.visitor_hash', 'like', "%{$query}%");
            });
        }

        $filteredCount = (clone $builder)->count();
        $visits = $builder
            ->orderByDesc('tracker_visits.visited_at')
            ->paginate($pageSize)
            ->withQueryString();

        return view('admin.tracker.visits', [
            'roleLabel' => $role instanceof Role ? $role->value : (string) $role,
            'trackerReady' => true,
            'query' => $query,
            'pageSize' => $pageSize,
            'totalCount' => DB::table(self::TRACKER_TABLE)->count(),
            'filteredCount' => $filteredCount,
            'visits' => $visits,
        ]);
    }

    /**
     * Guest visitor summary.
     */
    public function guests(Request $request): View
    {
        $role = Auth::user()->role;
        $query = trim((string) $request->query('q', ''));
        $pageSize = $this->resolvePageSize((int) $request->query('pageSize', 20), [10, 20, 50, 100], 20);

        if (! $this->trackerReady()) {
            return view('admin.tracker.users', [
                'roleLabel' => $role instanceof Role ? $role->value : (string) $role,
                'trackerReady' => false,
                'query' => $query,
                'pageSize' => $pageSize,
                'uniqueGuestCount' => 0,
                'totalGuestVisits' => 0,
                'guests' => new LengthAwarePaginator([], 0, $pageSize),
            ]);
        }

        $guestVisitsQuery = DB::table(self::TRACKER_TABLE)->where('is_guest', true);

        if ($query !== '') {
            $guestVisitsQuery->where(function ($q) use ($query): void {
                $q->where('path', 'like', "%{$query}%")
                    ->orWhere('ip_address', 'like', "%{$query}%")
                    ->orWhere('user_agent', 'like', "%{$query}%")
                    ->orWhere('visitor_hash', 'like', "%{$query}%");
            });
        }

        $groupedGuestsQuery = (clone $guestVisitsQuery)
            ->selectRaw('visitor_hash, MAX(ip_address) as ip_address, MAX(user_agent) as user_agent, COUNT(*) as visits_count, MIN(visited_at) as first_seen, MAX(visited_at) as last_seen')
            ->groupBy('visitor_hash');

        $guests = DB::query()
            ->fromSub($groupedGuestsQuery, 'guest_visitors')
            ->orderByDesc('last_seen')
            ->paginate($pageSize)
            ->withQueryString();

        $uniqueGuestCount = DB::query()
            ->fromSub($groupedGuestsQuery, 'guest_visitors_count')
            ->count();

        return view('admin.tracker.users', [
            'roleLabel' => $role instanceof Role ? $role->value : (string) $role,
            'trackerReady' => true,
            'query' => $query,
            'pageSize' => $pageSize,
            'uniqueGuestCount' => $uniqueGuestCount,
            'totalGuestVisits' => (clone $guestVisitsQuery)->count(),
            'guests' => $guests,
        ]);
    }

    /**
     * Resolve allowed page size.
     *
     * @param  list<int>  $allowed
     */
    private function resolvePageSize(int $requested, array $allowed, int $default): int
    {
        return in_array($requested, $allowed, true) ? $requested : $default;
    }

    /**
     * Ensure tracker table exists before querying.
     */
    private function trackerReady(): bool
    {
        return Schema::hasTable(self::TRACKER_TABLE);
    }
}
