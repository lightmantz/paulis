<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class ActivityController extends Controller
{
    public function index(Request $request)
    {
        $businessId = auth()->user()->business_id;

        $q = Activity::with('causer')
            ->where(function ($w) use ($businessId) {
                $w->whereHasMorph('causer', [User::class], function ($u) use ($businessId) {
                    $u->where('business_id', $businessId);
                });
            });

        if ($log = $request->get('log')) {
            if ($log !== 'All') $q->where('log_name', $log);
        }
        if ($user = $request->get('user')) {
            if ($user !== 'All') $q->where('causer_id', $user);
        }
        if ($s = trim((string) $request->get('q'))) {
            $q->where('description', 'like', "%{$s}%");
        }

        $events = $q->orderByDesc('created_at')->paginate(30)->withQueryString();
        $logs   = Activity::distinct()->pluck('log_name')->filter()->values();
        $users  = User::where('business_id', $businessId)->orderBy('name')->get(['id', 'name']);

        return view('business.activity.index', compact('events', 'logs', 'users'));
    }
}
