<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockTake;
use App\Models\StockTakeItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockTakeController extends Controller
{
    public function index(Request $request)
    {
        $view = $request->get('view', 'count');

        // Current draft or latest stock take
        $take = StockTake::whereIn('status', ['draft', 'pending'])
            ->with(['items.product'])
            ->orderByDesc('id')
            ->first();

        if (!$take && $view === 'count') {
            // Build a new draft snapshot on first visit
            $take = $this->startNewTake();
        }

        $products = Product::orderBy('name')->get();
        $past = StockTake::orderByDesc('id')->limit(10)->get();

        // Aggregate for the header
        $systemUnits = $products->sum('stock');
        $systemValue = $products->sum(fn ($p) => $p->stock * $p->unit_cost);

        $counted = $take ? $take->items->where('physical_qty', '!=', null) : collect();
        $physicalUnits = $counted->sum('physical_qty');
        $physicalValue = $counted->sum(fn ($i) => $i->physical_qty * $i->unit_cost);
        $shortageUnits = $counted->where('variance', '<', 0)->sum(fn ($i) => abs($i->variance));
        $surplusUnits = $counted->where('variance', '>', 0)->sum('variance');
        $lostValue = $counted->where('variance', '<', 0)->sum(fn ($i) => abs($i->variance_value));

        $stats = compact('systemUnits','systemValue','physicalUnits','physicalValue','shortageUnits','surplusUnits','lostValue');

        return view('business.stock-taking.index', compact('take', 'products', 'past', 'stats', 'view'));
    }

    protected function startNewTake(): StockTake
    {
        $take = StockTake::create([
            'business_id' => auth()->user()->business_id,
            'reference'   => $this->nextReference(),
            'status'      => 'draft',
            'counted_by'  => auth()->id(),
        ]);

        foreach (Product::all() as $p) {
            StockTakeItem::create([
                'stock_take_id' => $take->id,
                'product_id'    => $p->id,
                'book_qty'      => $p->stock,
                'unit_cost'     => $p->unit_cost,
                'status'        => 'pending',
            ]);
        }

        return $take->fresh(['items.product']);
    }

    public function startNew()
    {
        // Abandon any existing draft, then start a fresh one
        StockTake::where('business_id', auth()->user()->business_id)
            ->where('status', 'draft')
            ->update(['status' => 'abandoned']);

        $this->startNewTake();

        return redirect()->route('business.stock-taking')
            ->with('success', 'New stock take started.');
    }

    public function updateCount(Request $request, StockTake $take)
    {
        if ($take->status !== 'draft') {
            return response()->json(['error' => 'This stock take is locked'], 422);
        }

        $data = $request->validate([
            'product_id'   => ['required', 'integer', 'exists:products,id'],
            'physical_qty' => ['required', 'integer', 'min:0'],
        ]);

        $item = StockTakeItem::where('stock_take_id', $take->id)
            ->where('product_id', $data['product_id'])
            ->firstOrFail();

        $variance = $data['physical_qty'] - $item->book_qty;

        $item->update([
            'physical_qty'   => $data['physical_qty'],
            'variance'       => $variance,
            'variance_value' => $variance * (float) $item->unit_cost,
            'status'         => 'counted',
        ]);

        return response()->json(['ok' => true, 'variance' => $variance, 'variance_value' => $item->variance_value]);
    }

    public function post(Request $request, StockTake $take)
    {
        if (!in_array(auth()->user()->role, ['owner'])) {
            return back()->with('error', 'Only the Business Owner can post adjustments.');
        }
        if ($take->status !== 'draft') {
            return back()->with('error', 'This stock take has already been posted.');
        }

        DB::transaction(function () use ($take) {
            foreach ($take->items as $item) {
                if ($item->physical_qty === null) continue;

                Product::where('id', $item->product_id)->update(['stock' => $item->physical_qty]);
            }

            $take->update([
                'status'      => 'posted',
                'approved_by' => auth()->id(),
                'posted_at'   => now(),
            ]);

            activity('stock-take')->performedOn($take)->log("Posted stock take {$take->reference}");
        });

        return redirect()->route('business.stock-taking')
            ->with('success', "Stock take {$take->reference} posted. Inventory updated.");
    }

    protected function nextReference(): string
    {
        $last = StockTake::withoutGlobalScopes()
            ->where('business_id', auth()->user()->business_id)
            ->orderByDesc('id')
            ->value('reference');

        $next = $last ? ((int) preg_replace('/\D/', '', $last)) + 1 : 1;

        return 'STK-' . str_pad((string) $next, 5, '0', STR_PAD_LEFT);
    }
}
