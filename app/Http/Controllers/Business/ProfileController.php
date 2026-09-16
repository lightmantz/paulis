<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function show()
    {
        return view('business.profile.show', ['user' => auth()->user()]);
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $data = $request->validate([
            'name'   => ['required', 'string', 'max:120'],
            'email'  => ['required', 'email', 'max:150', Rule::unique('users')->ignore($user->id)],
            'phone'  => ['nullable', 'string', 'max:30'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:2048'],
        ]);

        if ($request->hasFile('avatar')) {
            if ($user->avatar && \Storage::disk('public')->exists($user->avatar)) {
                \Storage::disk('public')->delete($user->avatar);
            }
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($data);

        activity('profile')->performedOn($user)->log('Updated profile');

        return back()->with('success', 'Profile updated.');
    }

    public function password(Request $request)
    {
        $request->validate([
            'current_password' => ['required'],
            'password'         => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $user = auth()->user();

        if (! Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $user->update(['password' => Hash::make($request->password)]);

        activity('profile')->performedOn($user)->log('Changed password');

        return back()->with('success', 'Password updated.');
    }
}
