<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
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
                  ->orWhere('id', 'like', "%{$s}%");
            });
        }
        if ($status = $request->get('status')) {
            if ($status !== 'All statuses') $q->where('status', $status);
        }
        if ($plan = $request->get('plan')) {
            if ($plan !== 'All plans') $q->where('plan', $plan);
        }

        $businesses = $q->orderByDesc('id')->paginate(15)->withQueryString();

        return view('superadmin.businesses.index', compact('businesses'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'   => ['required', 'string', 'max:150'],
            'owner'  => ['required', 'string', 'max:120'],
            'email'  => ['required', 'email', 'unique:businesses,email', 'unique:users,email'],
            'phone'  => ['required', 'string', 'max:30'],
            'city'   => ['required', 'string', 'max:80'],
            'plan'   => ['required', Rule::in(['Starter', 'Growth', 'Professional'])],
        ]);

        $slug = Str::slug($data['name']);
        $i = 1;
        while (Business::where('slug', $slug)->exists()) {
            $slug = Str::slug($data['name']) . '-' . $i++;
        }

        $prices = ['Starter' => 95000, 'Growth' => 265000, 'Professional' => 185000];

        $business = Business::create([
            'slug'         => $slug,
            'name'         => $data['name'],
            'owner_name'   => $data['owner'],
            'email'        => $data['email'],
            'phone'        => $data['phone'],
            'city'         => $data['city'],
            'plan'         => $data['plan'],
            'status'       => 'Trial',
            'monthly_fee'  => $prices[$data['plan']],
            'renewal_date' => now()->addDays(14)->toDateString(),
            'trial_ends_at'=> now()->addDays(14),
        ]);

        // Create the owner user
        $username = Str::slug($data['owner'], '') ?: 'owner';
        $j = 1;
        while (User::where('username', $username)->exists()) {
            $username = Str::slug($data['owner'], '') . $j++;
        }

        User::create([
            'business_id' => $business->id,
            'name'        => $data['owner'],
            'username'    => $username,
            'email'       => $data['email'],
            'phone'       => $data['phone'],
            'role'        => 'owner',
            'status'      => 'Active',
            'password'    => Hash::make('1234'),
        ]);

        activity('superadmin')->performedOn($business)->log("Created business account: {$business->name}");

        return redirect()->route('superadmin.businesses')
            ->with('success', "Business account {$business->name} created.");
    }

    public function update(Request $request, Business $business)
    {
        $data = $request->validate([
            'status'       => ['required', Rule::in(['Active', 'Trial', 'Suspended', 'Revoked'])],
            'plan'         => ['required', Rule::in(['Starter', 'Growth', 'Professional'])],
            'monthly_fee'  => ['required', 'numeric', 'min:0'],
            'note'         => ['required', 'string', 'min:4'],
        ]);

        $old = $business->status;

        $business->update([
            'status'      => $data['status'],
            'plan'        => $data['plan'],
            'monthly_fee' => $data['monthly_fee'],
        ]);

        activity('superadmin')
            ->performedOn($business)
            ->withProperties(['from' => $old, 'to' => $data['status'], 'note' => $data['note']])
            ->log("Updated business account: {$old} → {$data['status']}");

        return redirect()->route('superadmin.businesses')
            ->with('success', "{$business->name} updated.");
    }

    public function toggle(Business $business)
    {
        $next = $business->status === 'Active' ? 'Suspended' : 'Active';
        $business->update(['status' => $next]);

        activity('superadmin')->performedOn($business)->log("{$next} business account");

        return back()->with('success', "{$business->name} is now {$next}.");
    }
}
