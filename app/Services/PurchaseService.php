<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseExpense;
use App\Models\PurchaseItem;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;

class PurchaseService
{
    /**
     * Receive a purchase order — creates a Purchase, allocates
     * additional expenses proportionally, adds stock, updates product cost.
     */
    public function receive(PurchaseOrder $po, array $payload): Purchase
    {
        return DB::transaction(function () use ($po, $payload) {
            $businessId = auth()->user()->business_id;

            $receivedItems = [];
            $subtotal = 0;

            foreach ($po->items as $index => $item) {
                $qty    = max(0, (int) ($payload["qty_{$index}"] ?? 0));
                $cost   = max(0, (float) ($payload["cost_{$index}"] ?? $item->unit_cost));
                if ($qty === 0) continue;

                $lineTotal = $qty * $cost;
                $subtotal += $lineTotal;

                $receivedItems[] = [
                    'product_id' => $item->product_id,
                    'qty'        => $qty,
                    'unit_cost'  => $cost,
                    'line_total' => $lineTotal,
                ];
            }

            // Additional expenses (transport, freight, tax, other)
            $expenses = collect($payload['expenses'] ?? [])
                ->map(fn ($e) => ['name' => $e['name'], 'amount' => max(0, (float) $e['amount'])])
                ->filter(fn ($e) => $e['amount'] > 0)
                ->values();

            $expensesTotal = $expenses->sum('amount');
            $total = $subtotal + $expensesTotal;
            $paid  = min($total, max(0, (float) ($payload['paid'] ?? 0)));
            $balance = $total - $paid;

            $purchase = Purchase::create([
                'business_id'       => $businessId,
                'reference'         => $payload['reference'] ?? $po->reference,
                'supplier_id'       => $po->supplier_id,
                'purchase_order_id' => $po->id,
                'purchase_date'     => $payload['purchase_date'] ?? now()->toDateString(),
                'subtotal'          => $subtotal,
                'expenses_total'    => $expensesTotal,
                'total'             => $total,
                'paid'              => $paid,
                'balance'           => $balance,
                'status'            => 'Received',
                'notes'             => $payload['notes'] ?? null,
            ]);

            // Save items
            foreach ($receivedItems as $ri) {
                $ri['purchase_id'] = $purchase->id;
                PurchaseItem::create($ri);

                // Update product
                $product = Product::find($ri['product_id']);
                if ($product) {
                    $allocatedExpense = $subtotal > 0 ? ($ri['line_total'] / $subtotal) * $expensesTotal : 0;
                    $landedUnitCost = ($ri['line_total'] + $allocatedExpense) / max(1, $ri['qty']);

                    $product->update([
                        'stock'     => $product->stock + $ri['qty'],
                        'unit_cost' => $landedUnitCost,
                    ]);
                }
            }

            // Save additional expenses
            foreach ($expenses as $e) {
                PurchaseExpense::create([
                    'purchase_id' => $purchase->id,
                    'name'        => $e['name'],
                    'amount'      => $e['amount'],
                ]);
            }

            // Update supplier balance
            if ($po->supplier_id && $balance > 0) {
                Supplier::where('id', $po->supplier_id)->increment('balance', $balance);
            }

            // Mark PO as received
            $po->update(['status' => 'Received']);

            activity('purchases')
                ->performedOn($purchase)
                ->log("Received PO {$po->reference} · TSh " . number_format($total));

            return $purchase;
        });
    }
}
