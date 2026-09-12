<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $biz = auth()->user()->business_id;

        $todaySales = 0;
        if (Schema::hasTable('sales') && Schema::hasColumn('sales', 'total')) {
            $todaySales = (float) DB::table('sales')
                ->where('business_id', $biz)
                ->whereDate('created_at', today())
                ->sum('total');
        }

        $stockValue = 0;
        if (Schema::hasTable('products')
            && Schema::hasColumn('products', 'stock')
            && Schema::hasColumn('products', 'unit_cost')) {
            $stockValue = (float) Product::sum(DB::raw('stock * unit_cost'));
        }

        $activeRepairs = 0;
        if (Schema::hasTable('job_cards') && Schema::hasColumn('job_cards', 'status')) {
            $activeRepairs = DB::table('job_cards')
                ->where('business_id', $biz)
                ->whereNotIn('status', ['collected', 'cancelled'])
                ->count();
        }

        $cashIn = 0;
        if (Schema::hasTable('cash_transactions')
            && Schema::hasColumn('cash_transactions', 'type')
            && Schema::hasColumn('cash_transactions', 'amount')) {
            $in = DB::table('cash_transactions')
                ->where('business_id', $biz)->where('type', 'Cash in')->sum('amount');
            $out = DB::table('cash_transactions')
                ->where('business_id', $biz)->where('type', 'Cash out')->sum('amount');
            $cashIn = (float) ($in - $out);
        }

        return view('business.dashboard', compact(
            'todaySales', 'stockValue', 'activeRepairs', 'cashIn'
        ));
    }
}