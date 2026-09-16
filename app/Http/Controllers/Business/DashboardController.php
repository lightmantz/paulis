<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\JobCard;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index()
    {
        $biz = auth()->user()->business_id;

        // ── KPI 1: Today's sales ────────────────────────────────────
        $todaySales = 0;
        if (Schema::hasTable('sales')) {
            $todaySales = (float) Sale::whereDate('created_at', today())->sum('total');
        }

        // ── KPI 2: Stock value ─────────────────────────────────────
        $stockValue = 0;
        $stockCount = 0;
        $productCount = 0;
        if (Schema::hasTable('products')) {
            $products = Product::all();
            $stockCount = $products->sum('stock');
            $productCount = $products->count();
            $stockValue = $products->sum(fn ($p) => $p->stock * (float) $p->unit_cost);
        }

        // ── KPI 3: Active repairs ──────────────────────────────────
        $activeRepairs = 0;
        $awaitingApproval = 0;
        if (Schema::hasTable('job_cards')) {
            $activeRepairs = JobCard::whereNotIn('status', ['collected'])->count();
            $awaitingApproval = JobCard::where('status', 'awaiting_approval')->count();
        }

        // ── KPI 4: Cash position ───────────────────────────────────
        $cashIn = 0;
        $cashOut = 0;
        if (Schema::hasTable('cash_transactions')) {
            $cashIn  = (float) DB::table('cash_transactions')->where('business_id', $biz)->where('type', 'Cash in')->sum('amount');
            $cashOut = (float) DB::table('cash_transactions')->where('business_id', $biz)->where('type', 'Cash out')->sum('amount');
        }
        $cash = $cashIn - $cashOut;

        // ── Chart data: last 7 days of sales ───────────────────────
        $chart = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = now()->subDays($i);
            $total = Schema::hasTable('sales')
                ? (float) Sale::whereDate('created_at', $day)->sum('total')
                : 0;
            $chart[] = ['label' => $day->format('D'), 'date' => $day->format('d M'), 'total' => $total];
        }
        $chartMax = max(array_column($chart, 'total')) ?: 1;

        // ── Stock alerts ───────────────────────────────────────────
        $lowStockCount = 0;
        $outOfStockCount = 0;
        if (Schema::hasTable('products')) {
            $lowStockCount   = Product::whereColumn('stock', '<=', 'reorder_level')->where('stock', '>', 0)->count();
            $outOfStockCount = Product::where('stock', 0)->count();
        }

        // ── Expenses summary ───────────────────────────────────────
        $expensesToday = 0;
        $expensesMonth = 0;
        $expensesYear  = 0;
        $expensesPendingCount = 0;
        $expensesPendingValue = 0;
        $topExpenseCategories = collect();

        if (Schema::hasTable('expenses')) {
            $expensesToday = (float) Expense::whereDate('expense_date', today())->sum('amount');

            $expensesMonth = (float) Expense::whereYear('expense_date', now()->year)
                ->whereMonth('expense_date', now()->month)
                ->sum('amount');

            $expensesYear = (float) Expense::whereYear('expense_date', now()->year)
                ->sum('amount');

            $expensesPendingCount = Expense::where('approval_status', '!=', 'Approved')->count();
            $expensesPendingValue = (float) Expense::where('approval_status', '!=', 'Approved')->sum('amount');

            $topExpenseCategories = Expense::select('category', DB::raw('SUM(amount) as total'))
                ->whereYear('expense_date', now()->year)
                ->whereMonth('expense_date', now()->month)
                ->groupBy('category')
                ->orderByDesc('total')
                ->limit(3)
                ->get();
        }

        // ── Recent sales ───────────────────────────────────────────
        $recentSales = collect();
        if (Schema::hasTable('sales')) {
            $recentSales = Sale::with(['customer', 'items.product'])
                ->orderByDesc('id')
                ->limit(5)
                ->get();
        }

        // ── Repair desk ────────────────────────────────────────────
        $recentJobs = collect();
        if (Schema::hasTable('job_cards')) {
            $recentJobs = JobCard::with('customer')
                ->orderByDesc('id')
                ->limit(4)
                ->get();
        }

        return view('business.dashboard', compact(
            'todaySales', 'stockValue', 'stockCount', 'productCount',
            'activeRepairs', 'awaitingApproval',
            'cash', 'chart', 'chartMax',
            'lowStockCount', 'outOfStockCount',
            'expensesToday', 'expensesMonth', 'expensesYear',
            'expensesPendingCount', 'expensesPendingValue', 'topExpenseCategories',
            'recentSales', 'recentJobs'
        ));
    }
}
