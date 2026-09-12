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
        $mrr = Business::where('status', 'Active')->sum('monthly_fee');
        $activeCount = Business::where('status', 'Active')->count();
        $totalBusinesses = Business::count();
        $totalUsers = User::count();
        $activeUsers = User::where('status', 'Active')->count();
        $needsAction = Business::whereIn('status', ['Trial', 'Suspended', 'Revoked'])->count();

        $recentActivity = Activity::with('causer')
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();

        return view('superadmin.dashboard', compact(
            'mrr', 'activeCount', 'totalBusinesses', 'totalUsers',
            'activeUsers', 'needsAction', 'recentActivity'
        ));
    }
}