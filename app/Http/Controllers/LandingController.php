<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class LandingController extends Controller
{
    public function index()
    {
        $alreadyLoggedIn = Auth::guard('web')->check();
        return view('landing', compact('alreadyLoggedIn'));
    }

    public function showRegister()
    {
        return view('auth.business-register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'business_name' => ['required', 'string', 'max:120'],
            'owner_name'    => ['required', 'string', 'max:120'],
            'email'         => ['required', 'email', 'max:150', 'unique:businesses,email', 'unique:users,email'],
            'phone'         => ['required', 'string', 'max:30'],
            'city'          => ['required', 'string', 'max:80'],
            'plan'          => ['required', Rule::in(['Starter', 'Growth', 'Professional'])],
            'password'      => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        // Build a unique slug
        $baseSlug = Str::slug($data['business_name']) ?: 'business';
        $slug = $baseSlug;
        $i = 1;
        while (Business::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $i++;
        }

        $planPrices = [
            'Starter'      => 95000,
            'Growth'       => 265000,
            'Professional' => 185000,
        ];

        $owner = null;

        try {
            DB::beginTransaction();

            $business = Business::create([
                'slug'          => $slug,
                'name'          => $data['business_name'],
                'owner_name'    => $data['owner_name'],
                'email'         => $data['email'],
                'phone'         => $data['phone'],
                'city'          => $data['city'],
                'plan'          => $data['plan'],
                'status'        => 'Trial',
                'monthly_fee'   => $planPrices[$data['plan']],
                'renewal_date'  => now()->addDays(14)->toDateString(),
                'trial_ends_at' => now()->addDays(14),
            ]);

            Subscription::create([
                'business_id'   => $business->id,
                'plan'          => $data['plan'],
                'monthly_fee'   => $planPrices[$data['plan']],
                'status'        => 'Trial',
                'starts_at'     => now()->toDateString(),
                'trial_ends_at' => now()->addDays(14)->toDateString(),
            ]);

            $baseUsername = Str::slug($data['owner_name'], '') ?: 'owner';
            $username = $baseUsername;
            $j = 1;
            while (User::where('username', $username)->exists()) {
                $username = $baseUsername . $j++;
            }

            $owner = User::create([
                'business_id' => $business->id,
                'name'        => $data['owner_name'],
                'username'    => $username,
                'email'       => $data['email'],
                'phone'       => $data['phone'],
                'role'        => 'owner',
                'status'      => 'Active',
                'password'    => Hash::make($data['password']),
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            // TEMP: show the real error during development
            throw $e;

            // Later, swap the throw for the friendly message:
            // return back()->withInput()
            //     ->withErrors(['business_name' => 'Registration failed. Please try again.']);
        }

        Auth::guard('web')->login($owner);
        $request->session()->regenerate();

        return redirect()
            ->route('business.dashboard')
            ->with('success', 'Welcome, ' . $owner->name . '! Your 14-day trial has started.');
    }
}
