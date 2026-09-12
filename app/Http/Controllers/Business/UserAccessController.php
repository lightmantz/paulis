<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserAccessController extends Controller
{
    public function index()
    {
        $users = User::where('business_id', auth()->user()->business_id)
                     ->orderBy('name')->get();
        return view('business.users.index', compact('users'));
    }

    public function store(Request $request)
    {
        if (auth()->user()->role !== 'owner') {
            return back()->with('error', 'Only the Business Owner can add users.');
        }

        $data = $request->validate([
            'name'     => ['required', 'string', 'max:120'],
            'username' => ['required', 'string', 'max:60', 'unique:users,username'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'phone'    => ['nullable', 'string', 'max:30'],
            'role'     => ['required', 'in:repair_person,sales_person'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        $data['business_id'] = auth()->user()->business_id;
        $data['password']    = Hash::make($data['password']);
        $data['status']      = 'Active';

        User::create($data);

        activity('users')->log("Added user: {$data['name']} ({$data['role']})");

        return back()->with('success', "{$data['name']} added.");
    }
}