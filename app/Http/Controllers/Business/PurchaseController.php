<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Services\PurchaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'orders');

        $orders = PurchaseOrder::with(['supplier', 'items'])
            ->orderByDesc('id')
            ->get();

        $purchases = Purchase::with(['supplier', 'items'])
            ->orderByDesc('id')
            ->get();

        $suppliers = Supplier::orderBy('name')->get(['id', 'name', 'phone']);
        $products  = Product::orderBy('name')->get(['id', 'name', 'sku', 'unit_cost', 'stock']);

        $openOrders    = $orders->whereIn('status', ['Draft', 'Ordered', 'Part received']);
        $orderedValue  = $openOrders->sum(fn ($o) => $o->estimated_total);
        $payable       = $suppliers->sum('balance');
        $receivedValue = $purchases->sum('total');

        return view('business.purchases.index', compact(
            'tab', 'orders', 'purchases', 'suppliers', 'products',
            'openOrders', 'orderedValue', 'payable', 'receivedValue'
        ));
    }

    public function storeOrder(Request $request)
    {
        $data = $request->validate([
            'supplier_id'    => ['required', 'integer', 'exists:suppliers,id'],
            'order_date'     => ['required', 'date'],
            'expected_date'  => ['nullable', 'date'],
            'status'         => ['required', 'in:Draft,Ordered'],
            'notes'          => ['nullable', 'string'],
            'items'                  => ['required', 'array', 'min:1'],
            'items.*.product_id'     => ['required', 'integer', 'exists:products,id'],
            'items.*.qty'            => ['required', 'integer', 'min:1'],
            'items.*.unit_cost'      => ['required', 'numeric', 'min:0'],
        ]);

        $po = DB::transaction(function () use ($data) {
            $po = PurchaseOrder::create([
                'business_id'   => auth()->user()->business_id,
                'reference'     => $this->nextPORef(),
                'supplier_id'   => $data['supplier_id'],
                'order_date'    => $data['order_date'],
                'expected_date' => $data['expected_date'] ?? null,
                'status'        => $data['status'],
                'notes'         => $data['notes'] ?? null,
                'created_by'    => auth()->id(),
            ]);

            foreach ($data['items'] as $row) {
                $product = Product::find($row['product_id']);
                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'product_id'        => $product->id,
                    'product_name'      => $product->name,
                    'qty'               => $row['qty'],
                    'unit_cost'         => $row['unit_cost'],
                ]);
            }

            activity('purchases')->performedOn($po)->log("Created PO {$po->reference}");

            return $po;
        });

        return redirect()->route('business.purchases')
            ->with('success', "Purchase order {$po->reference} created.");
    }

    public function receive(Request $request, PurchaseOrder $order, PurchaseService $service)
    {
        $data = $request->validate([
            'purchase_date' => ['nullable', 'date'],
            'reference'     => ['nullable', 'string', 'max:80'],
            'paid'          => ['nullable', 'numeric', 'min:0'],
            'notes'         => ['nullable', 'string'],
        ]);

        foreach ($order->items as $i => $item) {
            $data["qty_{$i}"]  = (int) $request->input("qty_{$i}", $item->qty);
            $data["cost_{$i}"] = (float) $request->input("cost_{$i}", $item->unit_cost);
        }

        $data['expenses'] = [];
        foreach (['Transport', 'Freight', 'Tax', 'Other'] as $name) {
            $amount = (float) $request->input('expense_' . strtolower($name), 0);
            if ($amount > 0) $data['expenses'][] = ['name' => $name, 'amount' => $amount];
        }

        $purchase = $service->receive($order, $data);

        return redirect()->route('business.purchases', ['tab' => 'history'])
            ->with('success', "Purchase received · TSh " . number_format($purchase->total));
    }

    /* ── New CRUD methods for the "history" tab ─────────────── */

    public function show(Purchase $purchase)
    {
        $purchase->load(['supplier', 'items.product', 'expenses']);

        return response()->json([
            'id'             => $purchase->id,
            'reference'      => $purchase->reference,
            'purchase_date'  => optional($purchase->purchase_date)->format('d M Y'),
            'supplier'       => $purchase->supplier?->name,
            'supplier_id'    => $purchase->supplier_id,
            'subtotal'       => (float) $purchase->subtotal,
            'expenses_total' => (float) $purchase->expenses_total,
            'total'          => (float) $purchase->total,
            'paid'           => (float) $purchase->paid,
            'balance'        => (float) $purchase->balance,
            'status'         => $purchase->status,
            'notes'          => $purchase->notes,
            'items'          => $purchase->items->map(fn ($i) => [
                'product_id' => $i->product_id,
                'name'       => $i->product?->name ?? '—',
                'qty'        => $i->qty,
                'unit_cost'  => (float) $i->unit_cost,
                'line_total' => (float) $i->line_total,
            ]),
            'expenses'       => $purchase->expenses->map(fn ($e) => [
                'name'   => $e->name,
                'amount' => (float) $e->amount,
            ]),
        ]);
    }

    public function update(Request $request, Purchase $purchase)
    {
        $data = $request->validate([
            'supplier_id'   => ['nullable', 'integer', 'exists:suppliers,id'],
            'purchase_date' => ['nullable', 'date'],
            'reference'     => ['required', 'string', 'max:80'],
            'subtotal'      => ['required', 'numeric', 'min:0'],
            'expenses_total'=> ['nullable', 'numeric', 'min:0'],
            'paid'          => ['required', 'numeric', 'min:0'],
            'status'        => ['required', Rule::in(['Received', 'Part received', 'Ordered', 'Cancelled'])],
            'notes'         => ['nullable', 'string'],
        ]);

        $total = (float) $data['subtotal'] + (float) ($data['expenses_total'] ?? 0);
        $paid  = min($total, (float) $data['paid']);

        $purchase->update([
            'supplier_id'    => $data['supplier_id'] ?? $purchase->supplier_id,
            'purchase_date'  => $data['purchase_date'] ?? $purchase->purchase_date,
            'reference'      => $data['reference'],
            'subtotal'       => $data['subtotal'],
            'expenses_total' => $data['expenses_total'] ?? 0,
            'total'          => $total,
            'paid'           => $paid,
            'balance'        => $total - $paid,
            'status'         => $data['status'],
            'notes'          => $data['notes'] ?? null,
        ]);

        activity('purchases')->performedOn($purchase)->log("Updated purchase {$purchase->reference}");

        return back()->with('success', "Purchase {$purchase->reference} updated.");
    }

    public function destroy(Purchase $purchase)
    {
        $reference = $purchase->reference;

        DB::transaction(function () use ($purchase) {
            // Roll back stock for each item
            foreach ($purchase->items as $item) {
                $product = Product::find($item->product_id);
                if ($product) {
                    $product->decrement('stock', min($product->stock, $item->qty));
                }
            }

            // Remove related rows
            $purchase->items()->delete();
            $purchase->expenses()->delete();

            // Adjust supplier balance if there was an outstanding amount
            if ($purchase->supplier_id && $purchase->balance > 0) {
                Supplier::where('id', $purchase->supplier_id)
                    ->decrement('balance', $purchase->balance);
            }

            $purchase->delete();
        });

        activity('purchases')->log("Deleted purchase {$reference}");

        return back()->with('success', "Purchase {$reference} deleted. Stock has been reverted.");
    }

    protected function nextPORef(): string
    {
        $last = PurchaseOrder::withoutGlobalScopes()
            ->where('business_id', auth()->user()->business_id)
            ->orderByDesc('id')
            ->value('reference');

        $next = $last ? ((int) preg_replace('/\D/', '', $last)) + 1 : 1;

        return 'PO-' . str_pad((string) $next, 5, '0', STR_PAD_LEFT);
    }
}
