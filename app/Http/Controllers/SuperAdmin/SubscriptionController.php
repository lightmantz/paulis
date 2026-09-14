<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Business;

class SubscriptionController extends Controller
{
    public function index()
    {
        $businesses  = Business::orderBy('name')->get();
        $mrr         = $businesses->where('status', 'Active')->sum('monthly_fee');
        $outstanding = $businesses->whereIn('status', ['Trial', 'Suspended'])->sum('monthly_fee');

        return view('superadmin.subscriptions.index', compact('businesses', 'mrr', 'outstanding'));
    }

    public function recordPayment(Business $business)
    {
        $business->update(['status' => 'Active']);

        activity('superadmin')
            ->performedOn($business)
            ->log('Recorded subscription payment · TSh ' . number_format($business->monthly_fee));

        return back()->with('success', "Subscription payment recorded for {$business->name}.");
    }
}
