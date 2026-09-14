<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Product;
use App\Services\SaleService;
use Illuminate\Http\Request;

class PosController extends Controller
{
    public function index()
    {
        $products = Product::where('stock', '>', 0)
            ->orderBy('name')
            ->get(['id', 'name', 'sku', 'barcode', 'selling_price', 'stock', 'condition', 'specs']);

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
            'items'          => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.qty'        => ['required', 'integer', 'min:1'],
        ]);

        try {
            $sale = $service->complete($data);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return redirect()
            ->route('business.pos')
            ->with('success', "Sale {$sale->invoice_no} completed · TSh " . number_format($sale->total));
    }
}