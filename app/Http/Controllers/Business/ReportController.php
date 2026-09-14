<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\JobCard;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Return_;
use App\Models\Sale;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        $catalog = [
            ['sales',     '▥', 'Sales report',          'Invoices, products, payment methods and per-sale totals.'],
            ['profit',    '↗', 'Profit & Loss',          'Revenue, cost of goods sold, expenses and net profit.'],
            ['inventory', '▦', 'Inventory valuation',    'Stock quantity, cost value, selling value and potential margin.'],
            ['customers', '♙', 'Customer balances',      'Outstanding invoices, payments and aging.'],
            ['suppliers', '◇', 'Supplier balances',      'Purchases, payments, credits and amounts payable.'],
            ['repairs',   '⚒', 'Repair & Service report','Job cards, statuses, parts, labour, revenue and profit.'],
            ['returns',   '↩', 'Returns report',         'Customer and supplier returns, refunds and approvals.'],
        ];

        $business = auth()->user()->business;

        $revenue = Sale::sum('total');
        $cogs = DB::table('sale_items')->join('sales','sales.id','=','sale_items.sale_id')
            ->where('sales.business_id', $business->id)
            ->sum(DB::raw('sale_items.qty * sale_items.unit_cost'));
        $expenses = Expense::where('approval_status', 'Approved')->sum('amount');
        $stockCost = Product::sum(DB::raw('stock * unit_cost'));
        $stockRetail = Product::sum(DB::raw('stock * selling_price'));

        $stats = [
            'revenue'     => $revenue,
            'cogs'        => $cogs,
            'gross'       => $revenue - $cogs,
            'expenses'    => $expenses,
            'net'         => $revenue - $cogs - $expenses,
            'stock_cost'  => $stockCost,
            'stock_retail'=> $stockRetail,
        ];

        return view('business.reports.index', compact('catalog', 'stats'));
    }

    public function show(Request $request, string $report)
    {
        $business = auth()->user()->business;
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to   = $request->get('to',   now()->toDateString());

        $data = match ($report) {
            'sales'     => $this->salesReport($business->id, $from, $to),
            'profit'    => $this->profitReport($business->id, $from, $to),
            'inventory' => $this->inventoryReport(),
            'customers' => $this->customersReport(),
            'suppliers' => $this->suppliersReport(),
            'repairs'   => $this->repairsReport($from, $to),
            'returns'   => $this->returnsReport($from, $to),
            default     => abort(404),
        };

        return view('business.reports.show', array_merge(
            ['report' => $report, 'from' => $from, 'to' => $to],
            $data
        ));
    }

    protected function salesReport($businessId, $from, $to)
    {
        $sales = Sale::whereBetween(DB::raw('DATE(created_at)'), [$from, $to])
            ->orderByDesc('id')->get();
        return [
            'title'  => 'Sales Report',
            'stats'  => [
                'Revenue'      => 'TSh ' . number_format($sales->sum('total')),
                'Transactions' => $sales->count(),
                'Paid'         => 'TSh ' . number_format($sales->sum('paid')),
                'Outstanding'  => 'TSh ' . number_format($sales->sum('balance')),
            ],
            'heads'  => ['Invoice','Date','Customer','Payment','Total','Paid','Balance'],
            'rows'   => $sales->map(fn ($s) => [
                $s->invoice_no,
                $s->created_at->format('d M Y'),
                $s->customer?->name ?? 'Walk-in',
                $s->payment_method,
                'TSh ' . number_format($s->total),
                'TSh ' . number_format($s->paid),
                'TSh ' . number_format($s->balance),
            ])->toArray(),
        ];
    }

    protected function profitReport($businessId, $from, $to)
    {
        $revenue = Sale::whereBetween(DB::raw('DATE(created_at)'), [$from, $to])->sum('total');
        $cogs = DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->where('sales.business_id', $businessId)
            ->whereBetween(DB::raw('DATE(sales.created_at)'), [$from, $to])
            ->sum(DB::raw('sale_items.qty * sale_items.unit_cost'));
        $expenses = Expense::where('approval_status', 'Approved')
            ->whereBetween('expense_date', [$from, $to])
            ->sum('amount');
        $gross = $revenue - $cogs;
        $net   = $gross - $expenses;
        return [
            'title' => 'Profit & Loss',
            'stats' => [
                'Revenue'       => 'TSh ' . number_format($revenue),
                'COGS'          => 'TSh ' . number_format($cogs),
                'Gross profit'  => 'TSh ' . number_format($gross),
                'Net profit'    => 'TSh ' . number_format($net),
            ],
            'heads' => ['Account','Category','Amount','Notes'],
            'rows'  => [
                ['Sales revenue', 'Income',    'TSh ' . number_format($revenue), 'Posted sales'],
                ['Cost of goods sold', 'Direct cost', 'TSh ' . number_format($cogs), 'Product cost of goods sold'],
                ['Gross profit', 'Result',     'TSh ' . number_format($gross), 'Revenue − COGS'],
                ['Operating expenses', 'Expense', 'TSh ' . number_format($expenses), 'Approved expenses'],
                ['Net profit', 'Result',       'TSh ' . number_format($net), 'Gross − operating expenses'],
            ],
        ];
    }

    protected function inventoryReport()
    {
        $products = Product::orderBy('name')->get();
        return [
            'title' => 'Inventory Valuation',
            'stats' => [
                'Products'       => $products->count(),
                'Units in stock' => $products->sum('stock'),
                'Cost value'     => 'TSh ' . number_format($products->sum(fn ($p) => $p->stock * $p->unit_cost)),
                'Retail value'   => 'TSh ' . number_format($products->sum(fn ($p) => $p->stock * $p->selling_price)),
            ],
            'heads' => ['Product','SKU','Stock','Unit cost','Cost value','Retail value'],
            'rows'  => $products->map(fn ($p) => [
                $p->name,
                $p->sku,
                $p->stock,
                'TSh ' . number_format($p->unit_cost),
                'TSh ' . number_format($p->stock * $p->unit_cost),
                'TSh ' . number_format($p->stock * $p->selling_price),
            ])->toArray(),
        ];
    }

    protected function customersReport()
    {
        $customers = Customer::orderByDesc('balance')->get();
        return [
            'title' => 'Customer Balances',
            'stats' => [
                'Customers'          => $customers->count(),
                'Total receivables'  => 'TSh ' . number_format($customers->sum('balance')),
                'With balance'       => $customers->where('balance', '>', 0)->count(),
                'Credit allowed'     => $customers->where('credit_allowed', true)->count(),
            ],
            'heads' => ['Customer','Phone','Type','Credit allowed','Balance'],
            'rows'  => $customers->map(fn ($c) => [
                $c->name,
                $c->phone ?? '—',
                $c->type,
                $c->credit_allowed ? 'Yes' : 'No',
                'TSh ' . number_format($c->balance),
            ])->toArray(),
        ];
    }

    protected function suppliersReport()
    {
        $suppliers = Supplier::orderByDesc('balance')->get();
        return [
            'title' => 'Supplier Balances',
            'stats' => [
                'Suppliers'        => $suppliers->count(),
                'Total payable'    => 'TSh ' . number_format($suppliers->sum('balance')),
                'With balance'     => $suppliers->where('balance', '>', 0)->count(),
                'Purchases (all)'  => 'TSh ' . number_format(Purchase::sum('total')),
            ],
            'heads' => ['Supplier','Contact','Phone','Balance'],
            'rows'  => $suppliers->map(fn ($s) => [
                $s->name,
                $s->contact_person ?? '—',
                $s->phone ?? '—',
                'TSh ' . number_format($s->balance),
            ])->toArray(),
        ];
    }

    protected function repairsReport($from, $to)
    {
        $jobs = JobCard::whereBetween(DB::raw('DATE(created_at)'), [$from, $to])->orderByDesc('id')->get();
        return [
            'title' => 'Repair & Service Report',
            'stats' => [
                'Jobs'          => $jobs->count(),
                'Revenue'       => 'TSh ' . number_format($jobs->sum('revenue')),
                'Costs'         => 'TSh ' . number_format($jobs->sum(fn ($j) => $j->total_expense)),
                'Net profit'    => 'TSh ' . number_format($jobs->sum(fn ($j) => $j->profit)),
            ],
            'heads' => ['Job','Customer','Device','Status','Revenue','Parts','Labour','Profit'],
            'rows'  => $jobs->map(fn ($j) => [
                $j->job_no,
                $j->customer?->name ?? '—',
                $j->device,
                ucfirst(str_replace('_', ' ', $j->status)),
                'TSh ' . number_format($j->revenue),
                'TSh ' . number_format($j->parts_cost),
                'TSh ' . number_format($j->labour_cost),
                'TSh ' . number_format($j->profit),
            ])->toArray(),
        ];
    }

    protected function returnsReport($from, $to)
    {
        $returns = Return_::whereBetween(DB::raw('DATE(created_at)'), [$from, $to])->orderByDesc('id')->get();
        return [
            'title' => 'Returns Report',
            'stats' => [
                'Total returns' => $returns->count(),
                'Refund value'  => 'TSh ' . number_format($returns->where('resolution', 'Refund')->sum('amount')),
                'Approved'      => $returns->where('approval_status', 'Approved')->count(),
                'Pending'       => $returns->where('approval_status', 'Awaiting Owner')->count(),
            ],
            'heads' => ['Reference','Type','Item','Resolution','Amount','Status'],
            'rows'  => $returns->map(fn ($r) => [
                $r->reference,
                $r->type,
                $r->item,
                $r->resolution,
                'TSh ' . number_format($r->amount),
                $r->approval_status,
            ])->toArray(),
        ];
    }
}
