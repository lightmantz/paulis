<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $q = User::with('business');

        if ($s = trim((string) $request->get('q'))) {
            $q->where(function ($w) use ($s) {
                $w->where('name', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
                  ->orWhere('username', 'like', "%{$s}%");
            });
        }
        if ($role = $request->get('role')) {
            if ($role !== 'All roles') $q->where('role', $role);
        }
        if ($biz = $request->get('business')) {
            if ($biz !== 'All businesses') $q->where('business_id', $biz);
        }

        $users      = $q->orderByDesc('id')->paginate(20)->withQueryString();
        $businesses = Business::orderBy('name')->get(['id', 'name']);

        return view('superadmin.users.index', compact('users', 'businesses'));
    }

    public function toggle(User $user)
    {
        $next = $user->status === 'Active' ? 'Disabled' : 'Active';
        $user->update(['status' => $next]);

        activity('superadmin')->performedOn($user)->log("{$next} user account");

        return back()->with('success', "{$user->name} is now {$next}.");
    }

    public function resetPassword(User $user)
    {
        $temp = 'Tmp@' . random_int(1000, 9999);
        $user->update(['password' => bcrypt($temp)]);

        activity('superadmin')->performedOn($user)->log("Reset user password");

        return back()->with('reset_password', [
            'user'    => $user->name,
            'user_id' => $user->id,
            'temp'    => $temp,
        ]);
    }
}
