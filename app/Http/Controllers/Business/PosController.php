<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Product;
use App\Services\SaleService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PosController extends Controller
{
    public function index()
    {
        $products = Product::where('stock', '>', 0)
            ->orderBy('name')
            ->get()
            ->map(function ($p) {
                return [
                    'id'            => $p->id,
                    'name'          => $p->name,
                    'sku'           => $p->sku,
                    'barcode'       => $p->barcode,
                    'condition'     => $p->condition,
                    'stock'         => (int) $p->stock,
                    'selling_price' => (float) $p->selling_price,
                    'specs'         => $p->specs,
                    'photo_url'     => $p->photo_url,
                    'tracking'      => $p->tracking,
                ];
            });

        $customers = Customer::orderBy('name')->get(['id', 'name', 'phone']);

        return view('business.pos.index', compact('products', 'customers'));
    }

    public function store(Request $request, SaleService $service)
    {
        $data = $request->validate([
            'customer_id'    => ['nullable', 'integer', 'exists:customers,id'],
            'payment_method' => ['required', 'in:Cash,M-Pesa,Bank transfer,Credit'],
            'discount'       => ['nullable', 'numeric', 'min:0'],
            'paid'           => ['nullable', 'numeric', 'min:0'],
            'document_type'  => ['required', 'in:Receipt,Invoice,Quotation'],
            'notes'          => ['nullable', 'string'],
            'items'                  => ['required', 'array', 'min:1'],
            'items.*.product_id'     => ['required', 'integer', 'exists:products,id'],
            'items.*.qty'            => ['required', 'integer', 'min:1'],
        ]);

        try {
            $sale = $service->complete($data);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return redirect()
            ->route('business.pos')
            ->with('success', "Sale {$sale->invoice_no} completed · TSh " . number_format($sale->total));
    }
}
