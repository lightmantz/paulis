<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // SALES
        if (Schema::hasTable('sales')) {
            Schema::table('sales', function (Blueprint $t) {
                if (! Schema::hasColumn('sales', 'business_id')) $t->unsignedBigInteger('business_id')->nullable()->index();
                if (! Schema::hasColumn('sales', 'invoice_no')) $t->string('invoice_no')->nullable()->unique();
                if (! Schema::hasColumn('sales', 'customer_id')) $t->unsignedBigInteger('customer_id')->nullable()->index();
                if (! Schema::hasColumn('sales', 'user_id')) $t->unsignedBigInteger('user_id')->nullable();
                if (! Schema::hasColumn('sales', 'subtotal')) $t->decimal('subtotal', 15, 2)->default(0);
                if (! Schema::hasColumn('sales', 'discount')) $t->decimal('discount', 15, 2)->default(0);
                if (! Schema::hasColumn('sales', 'discount_type')) $t->string('discount_type')->nullable();
                if (! Schema::hasColumn('sales', 'discount_reason')) $t->text('discount_reason')->nullable();
                if (! Schema::hasColumn('sales', 'total')) $t->decimal('total', 15, 2)->default(0);
                if (! Schema::hasColumn('sales', 'paid')) $t->decimal('paid', 15, 2)->default(0);
                if (! Schema::hasColumn('sales', 'balance')) $t->decimal('balance', 15, 2)->default(0);
                if (! Schema::hasColumn('sales', 'payment_method')) $t->string('payment_method')->default('Cash');
                if (! Schema::hasColumn('sales', 'status')) $t->string('status')->default('Posted');
                if (! Schema::hasColumn('sales', 'document_type')) $t->string('document_type')->default('Receipt');
                if (! Schema::hasColumn('sales', 'notes')) $t->text('notes')->nullable();
            });
        }

        // SALE ITEMS
        if (Schema::hasTable('sale_items')) {
            Schema::table('sale_items', function (Blueprint $t) {
                if (! Schema::hasColumn('sale_items', 'product_id')) $t->unsignedBigInteger('product_id')->nullable()->index();
                if (! Schema::hasColumn('sale_items', 'qty')) $t->integer('qty')->default(1);
                if (! Schema::hasColumn('sale_items', 'unit_price')) $t->decimal('unit_price', 15, 2)->default(0);
                if (! Schema::hasColumn('sale_items', 'unit_cost')) $t->decimal('unit_cost', 15, 2)->default(0);
                if (! Schema::hasColumn('sale_items', 'discount')) $t->decimal('discount', 15, 2)->default(0);
                if (! Schema::hasColumn('sale_items', 'line_total')) $t->decimal('line_total', 15, 2)->default(0);
                if (! Schema::hasColumn('sale_items', 'serial_ids')) $t->json('serial_ids')->nullable();
            });
        }
    }

    public function down(): void
    {
        // no-op — additive migration
    }
};