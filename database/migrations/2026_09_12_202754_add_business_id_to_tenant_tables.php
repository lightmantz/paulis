<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'categories',
            'product_groups',
            'products',
            'product_serials',
            'stock_movements',
            'stock_takes',
            'stock_take_items',
            'sales',
            'sale_items',
            'documents',
            'purchase_orders',
            'purchase_order_items',
            'purchases',
            'purchase_items',
            'purchase_expenses',
            'job_cards',
            'job_parts',
            'job_status_history',
            'customers',
            'suppliers',
            'expenses',
            'returns',
            'cash_transactions',
            'approval_requests',
        ];

        foreach ($tables as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }
            if (Schema::hasColumn($table, 'business_id')) {
                continue;
            }

            Schema::table($table, function (Blueprint $t) {
                $t->unsignedBigInteger('business_id')
                  ->nullable()
                  ->after('id');
                $t->index('business_id');
            });
        }
    }

    public function down(): void
    {
        $tables = [
            'categories', 'product_groups', 'products', 'product_serials',
            'stock_movements', 'stock_takes', 'stock_take_items', 'sales',
            'sale_items', 'documents', 'purchase_orders', 'purchase_order_items',
            'purchases', 'purchase_items', 'purchase_expenses', 'job_cards',
            'job_parts', 'job_status_history', 'customers', 'suppliers',
            'expenses', 'returns', 'cash_transactions', 'approval_requests',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'business_id')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropColumn('business_id');
                });
            }
        }
    }
};