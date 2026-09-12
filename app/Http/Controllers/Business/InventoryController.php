<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $q = Product::query();

        if ($s = trim((string) $request->get('q'))) {
            $q->where(function ($w) use ($s) {
                $w->where('name', 'like', "%{$s}%")
                  ->orWhere('sku', 'like', "%{$s}%")
                  ->orWhere('barcode', 'like', "%{$s}%")
                  ->orWhere('specs', 'like', "%{$s}%");
            });
        }
        if ($cat = $request->get('category')) {
            if ($cat !== 'All') $q->where('category_id', $cat);
        }

        $products = $q->orderByDesc('id')->paginate(20)->withQueryString();
        $categories = DB::table('categories')->orderBy('name')->get();

        return view('business.inventory.index', compact('products', 'categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'          => ['required', 'string', 'max:160'],
            'sku'           => ['required', 'string', 'max:60'],
            'barcode'       => ['nullable', 'string', 'max:60'],
            'condition'     => ['required', 'in:New,Used,Refurbished'],
            'tracking'      => ['required', 'in:quantity,optional_serial,required_serial'],
            'stock'         => ['required', 'integer', 'min:0'],
            'reorder_level' => ['required', 'integer', 'min:0'],
            'unit_cost'     => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'specs'         => ['nullable', 'string'],
        ]);

        $data['business_id'] = auth()->user()->business_id;
        $data['created_by']  = auth()->id();

        Product::create($data);

        activity('inventory')->log("Added product: {$data['name']}");

        return back()->with('success', "{$data['name']} added to inventory.");
    }
}