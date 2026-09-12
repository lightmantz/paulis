<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BusinessController extends Controller
{
    public function index(Request $request)
    {
        $q = Business::query();

        if ($s = trim((string) $request->get('q'))) {
            $q->where(function ($w) use ($s) {
                $w->where('name', 'like', "%{$s}%")
                  ->orWhere('owner_name', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
                  ->orWhere('city', 'like', "%{$s}%")
                  ->orWhere('id', $s);
            });
        }
        if ($status = $request->get('status')) {
            if ($status !== 'All') $q->where('status', $status);
        }
        if ($plan = $request->get('plan')) {
            if ($plan !== 'All') $q->where('plan', $plan);
        }

        $businesses = $q->orderByDesc('created_at')->paginate(15)->withQueryString();

        return view('superadmin.businesses.index', compact('businesses'));
    }

    public function update(Request $request, Business $business)
    {
        $data = $request->validate([
            'plan'         => ['required', Rule::in(['Starter', 'Growth', 'Professional'])],
            'status'       => ['required', Rule::in(['Active', 'Trial', 'Suspended', 'Revoked'])],
            'monthly_fee'  => ['required', 'numeric', 'min:0'],
            'renewal_date' => ['nullable', 'date'],
            'note'         => ['required', 'string', 'min:4'],
        ]);

        $old = $business->status;
        $business->update([
            'plan'         => $data['plan'],
            'status'       => $data['status'],
            'monthly_fee'  => $data['monthly_fee'],
            'renewal_date' => $data['renewal_date'],
        ]);

        activity('superadmin')
            ->performedOn($business)
            ->withProperties(['from' => $old, 'to' => $data['status'], 'note' => $data['note']])
            ->log("Updated business: {$old} → {$data['status']}");

        return redirect()->route('superadmin.businesses')
            ->with('success', "Updated {$business->name}.");
    }

    public function toggle(Business $business)
    {
        $next = $business->status === 'Active' ? 'Suspended' : 'Active';
        $business->update(['status' => $next]);

        activity('superadmin')
            ->performedOn($business)
            ->log("{$next} business account");

        return back()->with('success', "{$business->name} is now {$next}.");
    }
}