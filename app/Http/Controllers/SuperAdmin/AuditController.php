<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Spatie\Activitylog\Models\Activity;

class AuditController extends Controller
{
    public function index()
    {
        $events = Activity::with('causer')->orderByDesc('created_at')->paginate(30);

        return view('superadmin.audit.index', compact('events'));
    }
}
