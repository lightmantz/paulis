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
            'items'          => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.qty'        => ['required', 'integer', 'min:1'],
            'items.*.unit_cost'  => ['required', 'numeric', 'min:0'],
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

        // Gather per-line received qty/cost
        foreach ($order->items as $i => $item) {
            $data["qty_{$i}"]  = (int) $request->input("qty_{$i}", $item->qty);
            $data["cost_{$i}"] = (float) $request->input("cost_{$i}", $item->unit_cost);
        }

        // Gather additional expenses
        $data['expenses'] = [];
        foreach (['Transport', 'Freight', 'Tax', 'Other'] as $name) {
            $amount = (float) $request->input("expense_" . strtolower($name), 0);
            if ($amount > 0) $data['expenses'][] = ['name' => $name, 'amount' => $amount];
        }

        $purchase = $service->receive($order, $data);

        return redirect()->route('business.purchases', ['tab' => 'history'])
            ->with('success', "Purchase received · TSh " . number_format($purchase->total));
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
