<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\User;
use Spatie\Activitylog\Models\Activity;

class DashboardController extends Controller
{
    public function index()
    {
        $businesses = Business::all();

        $totalBusinesses = $businesses->count();
        $activeCount     = $businesses->where('status', 'Active')->count();
        $totalUsers      = User::count();
        $activeUsers     = User::where('status', 'Active')->count();
        $mrr             = $businesses->where('status', 'Active')->sum('monthly_fee');
        $needsAction     = $businesses->whereIn('status', ['Trial', 'Suspended', 'Revoked'])->count();

        // Trend for the last 7 months (simple placeholder using current MRR scaled)
        $trend = [];
        for ($i = 6; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $trend[] = [
                'label' => $month->format('M'),
                'value' => $i === 0 ? $mrr : $mrr * (0.6 + (6 - $i) * 0.06),
            ];
        }
        $trendMax = max(array_column($trend, 'value')) ?: 1;

        $recentActivity = Activity::with('causer')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return view('superadmin.dashboard', compact(
            'totalBusinesses', 'activeCount', 'totalUsers', 'activeUsers',
            'mrr', 'needsAction', 'trend', 'trendMax', 'recentActivity'
        ));
    }
}
