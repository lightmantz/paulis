<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Sale;
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
            'name'           => ['required', 'string', 'max:150'],
            'phone'          => ['nullable', 'string', 'max:30'],
            'email'          => ['nullable', 'email', 'max:150'],
            'type'           => ['required', 'in:Individual,Business,School'],
            'address'        => ['nullable', 'string'],
            'credit_allowed' => ['boolean'],
        ]);

        $data['business_id'] = auth()->user()->business_id;

        Customer::create($data);

        activity('customers')->log("Added customer: {$data['name']}");

        return back()->with('success', "{$data['name']} added.");
    }

    public function show(Customer $customer)
    {
        $salesCount = Sale::where('customer_id', $customer->id)->count();
        $salesTotal = Sale::where('customer_id', $customer->id)->sum('total');

        return response()->json([
            'id'             => $customer->id,
            'name'           => $customer->name,
            'phone'          => $customer->phone,
            'email'          => $customer->email,
            'type'           => $customer->type,
            'address'        => $customer->address,
            'credit_allowed' => (bool) $customer->credit_allowed,
            'balance'        => (float) $customer->balance,
            'sales_count'    => $salesCount,
            'sales_total'    => (float) $salesTotal,
        ]);
    }

    public function update(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'name'           => ['required', 'string', 'max:150'],
            'phone'          => ['nullable', 'string', 'max:30'],
            'email'          => ['nullable', 'email', 'max:150'],
            'type'           => ['required', 'in:Individual,Business,School'],
            'address'        => ['nullable', 'string'],
            'credit_allowed' => ['boolean'],
        ]);

        $customer->update($data);

        activity('customers')->performedOn($customer)->log("Updated customer: {$customer->name}");

        return back()->with('success', "{$customer->name} updated.");
    }

    public function destroy(Customer $customer)
    {
        $name = $customer->name;

        if (Sale::where('customer_id', $customer->id)->exists()) {
            return back()->with('error', "{$name} has sales and cannot be deleted.");
        }

        $customer->delete();

        activity('customers')->log("Deleted customer: {$name}");

        return back()->with('success', "{$name} deleted.");
    }
}
