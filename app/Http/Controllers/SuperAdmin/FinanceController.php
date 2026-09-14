<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Business;

class FinanceController extends Controller
{
    public function index()
    {
        $billed      = Business::sum('monthly_fee');
        $collected   = Business::where('status', 'Active')->sum('monthly_fee');
        $outstanding = $billed - $collected;
        $cost        = $collected * 0.28;
        $net         = $collected - $cost;
        $byBusiness  = Business::orderByDesc('monthly_fee')->get();

        return view('superadmin.finance.index', compact(
            'billed', 'collected', 'outstanding', 'cost', 'net', 'byBusiness'
        ));
    }
}
