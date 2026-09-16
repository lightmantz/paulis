<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

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

        $products   = $q->orderByDesc('id')->paginate(20)->withQueryString();
        $categories = DB::table('categories')->orderBy('name')->get();

        return view('business.inventory.index', compact('products', 'categories'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $data['business_id'] = auth()->user()->business_id;
        $data['created_by']  = auth()->id();

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('products', 'public');
        }

        Product::create($data);

        activity('inventory')->log("Added product: {$data['name']}");

        return back()->with('success', "{$data['name']} added to inventory.");
    }

    public function show(Product $product)
    {
        return response()->json([
            'id'            => $product->id,
            'name'          => $product->name,
            'sku'           => $product->sku,
            'barcode'       => $product->barcode,
            'condition'     => $product->condition,
            'tracking'      => $product->tracking,
            'specs'         => $product->specs,
            'photo_url'     => $product->photo_url,
            'stock'         => $product->stock,
            'reorder_level' => $product->reorder_level,
            'unit_cost'     => (float) $product->unit_cost,
            'selling_price' => (float) $product->selling_price,
            'status'        => $product->stock == 0 ? 'Out of stock'
                              : ($product->stock <= $product->reorder_level ? 'Low stock' : 'In stock'),
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $data = $this->validated($request);

        if ($request->hasFile('photo')) {
            // Remove old photo
            if ($product->photo && Storage::disk('public')->exists($product->photo)) {
                Storage::disk('public')->delete($product->photo);
            }
            $data['photo'] = $request->file('photo')->store('products', 'public');
        }

        $product->update($data);

        activity('inventory')->performedOn($product)->log("Updated product: {$product->name}");

        return back()->with('success', "{$product->name} updated.");
    }

    public function destroy(Product $product)
    {
        $name = $product->name;

        $hasSales = DB::table('sale_items')->where('product_id', $product->id)->exists();
        if ($hasSales) {
            return back()->with('error', "{$name} is used in past sales and cannot be deleted. Set its stock to 0 instead.");
        }

        if ($product->photo && Storage::disk('public')->exists($product->photo)) {
            Storage::disk('public')->delete($product->photo);
        }

        $product->delete();

        activity('inventory')->log("Deleted product: {$name}");

        return back()->with('success', "{$name} deleted.");
    }

    /* ── Shared validation ─────────────────────────────────── */

    protected function validated(Request $request): array
    {
        return $request->validate([
            'name'          => ['required', 'string', 'max:160'],
            'sku'           => ['required', 'string', 'max:60'],
            'barcode'       => ['nullable', 'string', 'max:60'],
            'condition'     => ['required', Rule::in(['New', 'Used', 'Refurbished'])],
            'tracking'      => ['required', Rule::in(['quantity', 'optional_serial', 'required_serial'])],
            'stock'         => ['required', 'integer', 'min:0'],
            'reorder_level' => ['required', 'integer', 'min:0'],
            'unit_cost'     => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'specs'         => ['nullable', 'string'],
            'photo'         => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:4096'],
        ]);
    }
}
