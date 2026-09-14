<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaleService
{
    /**
     * Complete a sale inside a single DB transaction.
     *
     * @param  array $payload  {
     *     customer_id, customer_name, payment_method, discount,
     *     paid, document_type, notes,
     *     items: [ ['product_id' => 1, 'qty' => 2], ... ]
     * }
     */
    public function complete(array $payload): Sale
    {
        return DB::transaction(function () use ($payload) {
            $businessId = auth()->user()->business_id;
            $userId = auth()->id();

            // Lock the products we're about to sell so stock can't drift
            $productIds = collect($payload['items'])->pluck('product_id')->all();
            $products = Product::whereIn('id', $productIds)->lockForUpdate()->get()->keyBy('id');

            $subtotal = 0;
            $lines = [];

            foreach ($payload['items'] as $row) {
                $product = $products->get($row['product_id']);
                if (! $product) {
                    throw ValidationException::withMessages([
                        'items' => "Product #{$row['product_id']} not found.",
                    ]);
                }
                $qty = max(1, (int) $row['qty']);

                if ($qty > $product->stock) {
                    throw ValidationException::withMessages([
                        'items' => "Only {$product->stock} units of {$product->name} available.",
                    ]);
                }

                $lineTotal = $qty * (float) $product->selling_price;
                $subtotal += $lineTotal;

                $lines[] = [
                    'product_id'  => $product->id,
                    'qty'         => $qty,
                    'unit_price'  => $product->selling_price,
                    'unit_cost'   => $product->unit_cost,
                    'line_total'  => $lineTotal,
                    'serial_ids'  => null,
                ];
            }

            $discount = min($subtotal, max(0, (float) ($payload['discount'] ?? 0)));
            $total = $subtotal - $discount;
            $paid = max(0, min($total, (float) ($payload['paid'] ?? $total)));
            $balance = $total - $paid;

            $sale = Sale::create([
                'business_id'    => $businessId,
                'invoice_no'     => $this->nextInvoiceNo($businessId),
                'customer_id'    => $payload['customer_id'] ?? null,
                'user_id'        => $userId,
                'subtotal'       => $subtotal,
                'discount'       => $discount,
                'total'          => $total,
                'paid'           => $paid,
                'balance'        => $balance,
                'payment_method' => $payload['payment_method'] ?? 'Cash',
                'status'         => 'Posted',
                'document_type'  => $payload['document_type'] ?? 'Receipt',
                'notes'          => $payload['notes'] ?? null,
            ]);

            foreach ($lines as $line) {
                $line['sale_id'] = $sale->id;
                SaleItem::create($line);

                // Reduce stock
                Product::where('id', $line['product_id'])
                    ->decrement('stock', $line['qty']);
            }

            activity('pos')
                ->performedOn($sale)
                ->withProperties([
                    'invoice' => $sale->invoice_no,
                    'total'   => $sale->total,
                    'items'   => count($lines),
                ])
                ->log("Completed sale {$sale->invoice_no}");

            return $sale->fresh(['items.product', 'customer', 'user']);
        });
    }

    protected function nextInvoiceNo(int $businessId): string
    {
        $last = Sale::withoutGlobalScopes()
            ->where('business_id', $businessId)
            ->orderByDesc('id')
            ->value('invoice_no');

        $next = $last
            ? ((int) preg_replace('/\D/', '', $last)) + 1
            : 1;

        return 'INV-' . str_pad((string) $next, 5, '0', STR_PAD_LEFT);
    }
}