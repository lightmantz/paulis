<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::orderByDesc('id')->paginate(20);
        return view('business.suppliers.index', compact('suppliers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'           => ['required', 'string', 'max:150'],
            'contact_person' => ['nullable', 'string', 'max:120'],
            'phone'          => ['nullable', 'string', 'max:30'],
            'email'          => ['nullable', 'email', 'max:150'],
            'address'        => ['nullable', 'string'],
        ]);

        $data['business_id'] = auth()->user()->business_id;

        Supplier::create($data);

        activity('suppliers')->log("Added supplier: {$data['name']}");

        return back()->with('success', "{$data['name']} added.");
    }

    public function show(Supplier $supplier)
    {
        $purchasesCount = Purchase::where('supplier_id', $supplier->id)->count();
        $purchasesTotal = Purchase::where('supplier_id', $supplier->id)->sum('total');

        return response()->json([
            'id'              => $supplier->id,
            'name'            => $supplier->name,
            'contact_person'  => $supplier->contact_person,
            'phone'           => $supplier->phone,
            'email'           => $supplier->email,
            'address'         => $supplier->address,
            'balance'         => (float) $supplier->balance,
            'purchases_count' => $purchasesCount,
            'purchases_total' => (float) $purchasesTotal,
        ]);
    }

    public function update(Request $request, Supplier $supplier)
    {
        $data = $request->validate([
            'name'           => ['required', 'string', 'max:150'],
            'contact_person' => ['nullable', 'string', 'max:120'],
            'phone'          => ['nullable', 'string', 'max:30'],
            'email'          => ['nullable', 'email', 'max:150'],
            'address'        => ['nullable', 'string'],
        ]);

        $supplier->update($data);

        activity('suppliers')->performedOn($supplier)->log("Updated supplier: {$supplier->name}");

        return back()->with('success', "{$supplier->name} updated.");
    }

    public function destroy(Supplier $supplier)
    {
        $name = $supplier->name;

        if (Purchase::where('supplier_id', $supplier->id)->exists()) {
            return back()->with('error', "{$name} has purchases and cannot be deleted.");
        }

        $supplier->delete();

        activity('suppliers')->log("Deleted supplier: {$name}");

        return back()->with('success', "{$name} deleted.");
    }
}
