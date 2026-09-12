<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = Customer::orderByDesc('id')->paginate(20);
        return view('business.customers.index', compact('customers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'   => ['required', 'string', 'max:150'],
            'phone'  => ['nullable', 'string', 'max:30'],
            'email'  => ['nullable', 'email', 'max:150'],
            'type'   => ['required', 'in:Individual,Business,School'],
            'address'=> ['nullable', 'string'],
            'credit_allowed' => ['boolean'],
        ]);

        $data['business_id'] = auth()->user()->business_id;

        Customer::create($data);

        activity('customers')->log("Added customer: {$data['name']}");

        return back()->with('success', "{$data['name']} added.");
    }
}