<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class FinancialController extends Controller
{
    public function index()
    {
        $biz = auth()->user()->business_id;

        $productRevenue = 0;
        if (Schema::hasTable('sales') && Schema::hasColumn('sales', 'total')) {
            $productRevenue = (float) DB::table('sales')->where('business_id', $biz)->sum('total');
        }

        $cogs = 0;
        if (Schema::hasTable('sale_items')
            && Schema::hasColumn('sale_items', 'qty')
            && Schema::hasColumn('sale_items', 'unit_cost')) {
            $cogs = (float) DB::table('sale_items')
                ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
                ->where('sales.business_id', $biz)
                ->sum(DB::raw('sale_items.qty * sale_items.unit_cost'));
        }

        $operating = 0;
        if (Schema::hasTable('expenses') && Schema::hasColumn('expenses', 'amount')) {
            $operating = (float) Expense::where('approval_status', 'Approved')->sum('amount');
        }

        $gross = $productRevenue - $cogs;
        $net   = $gross - $operating;
        $margin = $productRevenue > 0 ? ($gross / $productRevenue) * 100 : 0;

        $receivable = Schema::hasTable('customers') && Schema::hasColumn('customers', 'balance')
            ? (float) Customer::sum('balance') : 0;

        $payable = Schema::hasTable('suppliers') && Schema::hasColumn('suppliers', 'balance')
            ? (float) Supplier::sum('balance') : 0;

        $stockValue = 0;
        if (Schema::hasTable('products')
            && Schema::hasColumn('products', 'stock')
            && Schema::hasColumn('products', 'unit_cost')) {
            $stockValue = (float) Product::sum(DB::raw('stock * unit_cost'));
        }

        return view('business.financial.index', compact(
            'productRevenue', 'cogs', 'gross', 'operating', 'net',
            'margin', 'receivable', 'payable', 'stockValue'
        ));
    }
}