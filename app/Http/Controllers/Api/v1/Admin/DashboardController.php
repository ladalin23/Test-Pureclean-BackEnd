<?php

namespace App\Http\Controllers\Api\v1\Admin;

use App\Models\Admin;
use App\Models\Branch;
use App\Http\Requests\TopUserRequest;
use App\Http\Requests\StartEndDateRequest;
use App\Http\Controllers\Api\v1\BaseAPI;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends BaseAPI
{
    public function revenueOrder(){
        $today = Carbon::now()->startOfDay();
        $rows = DB::select(<<<SQL
            SELECT 'today' period, COUNT(*) orders, SUM(total_price) revenue
            FROM purchaseds WHERE DATE(created_at)=CURRENT_DATE
            UNION ALL
            SELECT 'wtd', COUNT(*), SUM(total_price)
            FROM purchaseds WHERE YEARWEEK(created_at,1)=YEARWEEK(CURDATE(),1)
            UNION ALL
            SELECT 'mtd', COUNT(*), SUM(total_price)
            FROM purchaseds WHERE YEAR(created_at)=YEAR(CURDATE()) AND MONTH(created_at)=MONTH(CURDATE())
            SQL);

        return $this->successResponse(['revenue' => $rows], 'Today\'s revenue retrieved successfully');
    }

    public function topUser(TopUserRequest $request)
    {
        $params = $request->validated();

        // Convert incoming ms timestamps to Carbon (UTC)
        $startDate = $this->msToCarbon($params['start_date'])->startOfDay();
        $endDate   = $this->msToCarbon($params['end_date'])->endOfDay();
        $top       = $params['top'];

        // Debug: see the converted dates
        // dd($startDate, $endDate);

        // Query top users
        $topUsers = DB::table('users')
            ->leftJoin('purchaseds', function($join) use ($startDate, $endDate) {
                $join->on('users.id', '=', 'purchaseds.user_id')
                    ->where('purchaseds.active', 1)
                    ->whereBetween('purchaseds.created_at', [$startDate, $endDate]);
            })
            ->select(
                'users.id',
                'users.global_id',
                'users.username',
                'users.u_id',
                'users.telegram_id',
                'users.phone',
                DB::raw('COUNT(purchaseds.id) AS purchase_count')
            )
            ->groupBy(
                'users.id',
                'users.global_id',
                'users.username',
                'users.u_id',
                'users.telegram_id',
                'users.phone'
            )
            ->orderByDesc('purchase_count')
            ->limit($top)
            ->get();

        // Debug: see what rows are returned
        // dd($topUsers);

        return $this->successResponse($topUsers, 'Top users retrieved successfully');
    }

    public function rewardClaimTrend(StartEndDateRequest $request)
    {
        $p = $request->validated();

        // interpret incoming ms timestamps as UTC instants (adjust if you prefer local tz)
        $start = $this->msToCarbon($p['start_date'])->startOfDay();
        $end   = $this->msToCarbon($p['end_date'])->endOfDay();

        // ONE grouped query from rewards
        $rows = DB::table('rewards')
            ->where('active', 1)
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw("
                DATE(created_at) AS day,
                SUM(CASE WHEN product_id IS NOT NULL THEN 1 ELSE 0 END) AS first_claims,
                SUM(CASE WHEN service_id IS NOT NULL THEN 1 ELSE 0 END) AS second_claims
            ")
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        // Build the full date keyspace and zero-fill
        $first  = [];
        $second = [];

        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            $key = $d->toDateString();      // ALWAYS "YYYY-MM-DD"
            $first[$key]  = 0;
            $second[$key] = 0;
        }

        // Fill with real counts
        foreach ($rows as $r) {
            $key = Carbon::parse($r->day)->toDateString();
            $first[$key]  = (int) $r->first_claims;
            $second[$key] = (int) $r->second_claims;
        }

        return $this->successResponse([
            'first'  => $first,   // e.g. { "2025-09-01": 3, "2025-09-02": 0, ... }
            'second' => $second,  // e.g. { "2025-09-01": 1, "2025-09-02": 2, ... }
        ], 'Reward claim trends by date');
    }

    public function purchasedTrend(StartEndDateRequest $request)
    {
        $p = $request->validated();

        $start = $this->msToCarbon($p['start_date'])->startOfDay();
        $end   = $this->msToCarbon($p['end_date'])->endOfDay();

        // Query from purchaseds table
        $rows = DB::table('purchaseds')
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw("
                DATE(created_at) AS day,
                COUNT(*) AS total_purchases,
                SUM(total_price) AS total_amount
            ")
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->keyBy('day');

        $result = [];

        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            $key = $d->toDateString();

            $result[$key] = [
                'purchases' => isset($rows[$key]) ? (int) $rows[$key]->total_purchases : 0,
                'amounts'   => isset($rows[$key]) ? (float) $rows[$key]->total_amount   : 0,
            ];
        }

        return $this->successResponse($result, 'Purchased trends by date');
    }


    private function msToCarbon($val): ?Carbon
    {
        if ($val === null || $val === '' || !is_numeric($val)) {
            return null;
        }
        // Treat as UTC instants; no tz shifting
        return Carbon::createFromTimestampMs((int) $val, 'UTC');
    }

}