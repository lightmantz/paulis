<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop and rebuild tenant tables that may be shells
        $drops = [
            'sale_items','sales',
            'purchase_order_items','purchase_orders',
            'purchase_expenses','purchase_items','purchases',
            'job_status_history','job_parts','job_cards',
            'product_serials','stock_movements','stock_take_items','stock_takes',
            'product_groups','categories',
            'products','customers','suppliers',
            'expenses','returns','cash_transactions','approval_requests','documents',
        ];
        foreach ($drops as $t) { Schema::dropIfExists($t); }

        // ── Categories ─────────────────────────────────────────────
        Schema::create('categories', function (Blueprint $t) {
            $t->id();
            $t->foreignId('business_id')->nullable()->constrained('businesses')->nullOnDelete();
            $t->string('name');
            $t->string('slug')->nullable();
            $t->timestamps();
            $t->index(['business_id', 'name']);
        });

        // ── Product groups ─────────────────────────────────────────
        Schema::create('product_groups', function (Blueprint $t) {
            $t->id();
            $t->foreignId('business_id')->nullable()->constrained('businesses')->nullOnDelete();
            $t->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $t->string('name');
            $t->timestamps();
        });

        // ── Products ───────────────────────────────────────────────
        Schema::create('products', function (Blueprint $t) {
            $t->id();
            $t->foreignId('business_id')->nullable()->constrained('businesses')->nullOnDelete();
            $t->string('name');
            $t->string('sku')->nullable()->index();
            $t->string('barcode')->nullable()->index();
            $t->unsignedBigInteger('category_id')->nullable()->index();
            $t->unsignedBigInteger('group_id')->nullable()->index();
            $t->string('condition')->default('New');
            $t->text('specs')->nullable();
            $t->string('tracking')->default('quantity');
            $t->integer('stock')->default(0);
            $t->integer('reorder_level')->default(5);
            $t->decimal('unit_cost', 15, 2)->default(0);
            $t->decimal('selling_price', 15, 2)->default(0);
            $t->unsignedBigInteger('supplier_id')->nullable()->index();
            $t->unsignedBigInteger('created_by')->nullable();
            $t->timestamps();
            $t->index(['business_id', 'name']);
        });

        // ── Product serials ────────────────────────────────────────
        Schema::create('product_serials', function (Blueprint $t) {
            $t->id();
            $t->foreignId('business_id')->nullable()->constrained('businesses')->nullOnDelete();
            $t->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $t->string('serial')->unique();
            $t->string('status')->default('in_stock');
            $t->unsignedBigInteger('purchase_id')->nullable();
            $t->unsignedBigInteger('sale_id')->nullable();
            $t->text('notes')->nullable();
            $t->timestamps();
        });

        // ── Stock movements ────────────────────────────────────────
        Schema::create('stock_movements', function (Blueprint $t) {
            $t->id();
            $t->foreignId('business_id')->nullable()->constrained('businesses')->nullOnDelete();
            $t->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $t->string('type');
            $t->integer('qty');
            $t->decimal('unit_cost', 15, 2)->default(0);
            $t->string('reference')->nullable();
            $t->unsignedBigInteger('user_id')->nullable();
            $t->text('notes')->nullable();
            $t->timestamps();
        });

        // ── Stock takes ────────────────────────────────────────────
        Schema::create('stock_takes', function (Blueprint $t) {
            $t->id();
            $t->foreignId('business_id')->nullable()->constrained('businesses')->nullOnDelete();
            $t->string('reference')->unique();
            $t->string('status')->default('draft');
            $t->unsignedBigInteger('counted_by')->nullable();
            $t->unsignedBigInteger('approved_by')->nullable();
            $t->timestamp('posted_at')->nullable();
            $t->timestamps();
        });

        Schema::create('stock_take_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('stock_take_id')->constrained('stock_takes')->cascadeOnDelete();
            $t->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $t->integer('book_qty')->default(0);
            $t->integer('physical_qty')->nullable();
            $t->integer('variance')->nullable();
            $t->decimal('unit_cost', 15, 2)->default(0);
            $t->decimal('variance_value', 15, 2)->default(0);
            $t->json('serial_ids')->nullable();
            $t->string('status')->default('pending');
            $t->timestamps();
        });

        // ── Sales ──────────────────────────────────────────────────
        Schema::create('sales', function (Blueprint $t) {
            $t->id();
            $t->foreignId('business_id')->nullable()->constrained('businesses')->nullOnDelete();
            $t->string('invoice_no')->nullable()->index();
            $t->unsignedBigInteger('customer_id')->nullable()->index();
            $t->unsignedBigInteger('user_id')->nullable();
            $t->decimal('subtotal', 15, 2)->default(0);
            $t->decimal('discount', 15, 2)->default(0);
            $t->string('discount_type')->nullable();
            $t->text('discount_reason')->nullable();
            $t->decimal('total', 15, 2)->default(0);
            $t->decimal('paid', 15, 2)->default(0);
            $t->decimal('balance', 15, 2)->default(0);
            $t->string('payment_method')->default('Cash');
            $t->string('status')->default('Posted');
            $t->string('document_type')->default('Receipt');
            $t->text('notes')->nullable();
            $t->timestamps();
            $t->index(['business_id', 'created_at']);
        });

        Schema::create('sale_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('sale_id')->constrained('sales')->cascadeOnDelete();
            $t->unsignedBigInteger('product_id')->nullable()->index();
            $t->integer('qty')->default(1);
            $t->decimal('unit_price', 15, 2)->default(0);
            $t->decimal('unit_cost', 15, 2)->default(0);
            $t->decimal('discount', 15, 2)->default(0);
            $t->decimal('line_total', 15, 2)->default(0);
            $t->json('serial_ids')->nullable();
            $t->timestamps();
        });

        // ── Documents ──────────────────────────────────────────────
        Schema::create('documents', function (Blueprint $t) {
            $t->id();
            $t->foreignId('business_id')->nullable()->constrained('businesses')->nullOnDelete();
            $t->string('type');
            $t->string('number')->unique();
            $t->unsignedBigInteger('sale_id')->nullable();
            $t->unsignedBigInteger('customer_id')->nullable();
            $t->decimal('total', 15, 2)->default(0);
            $t->string('status')->default('draft');
            $t->json('payload')->nullable();
            $t->timestamps();
        });

        // ── Purchase orders ────────────────────────────────────────
        Schema::create('purchase_orders', function (Blueprint $t) {
            $t->id();
            $t->foreignId('business_id')->nullable()->constrained('businesses')->nullOnDelete();
            $t->string('reference')->index();
            $t->unsignedBigInteger('supplier_id')->nullable()->index();
            $t->date('order_date')->nullable();
            $t->date('expected_date')->nullable();
            $t->string('status')->default('Draft');
            $t->text('notes')->nullable();
            $t->unsignedBigInteger('created_by')->nullable();
            $t->timestamps();
        });

        Schema::create('purchase_order_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
            $t->unsignedBigInteger('product_id')->nullable();
            $t->string('product_name')->nullable();
            $t->integer('qty')->default(1);
            $t->decimal('unit_cost', 15, 2)->default(0);
            $t->timestamps();
        });

        // ── Purchases ──────────────────────────────────────────────
        Schema::create('purchases', function (Blueprint $t) {
            $t->id();
            $t->foreignId('business_id')->nullable()->constrained('businesses')->nullOnDelete();
            $t->string('reference')->index();
            $t->unsignedBigInteger('supplier_id')->nullable()->index();
            $t->unsignedBigInteger('purchase_order_id')->nullable();
            $t->date('purchase_date')->nullable();
            $t->decimal('subtotal', 15, 2)->default(0);
            $t->decimal('expenses_total', 15, 2)->default(0);
            $t->decimal('total', 15, 2)->default(0);
            $t->decimal('paid', 15, 2)->default(0);
            $t->decimal('balance', 15, 2)->default(0);
            $t->string('status')->default('Received');
            $t->text('notes')->nullable();
            $t->timestamps();
        });

        Schema::create('purchase_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('purchase_id')->constrained('purchases')->cascadeOnDelete();
            $t->unsignedBigInteger('product_id')->nullable();
            $t->integer('qty')->default(1);
            $t->decimal('unit_cost', 15, 2)->default(0);
            $t->decimal('line_total', 15, 2)->default(0);
            $t->timestamps();
        });

        Schema::create('purchase_expenses', function (Blueprint $t) {
            $t->id();
            $t->foreignId('purchase_id')->constrained('purchases')->cascadeOnDelete();
            $t->string('name');
            $t->decimal('amount', 15, 2)->default(0);
            $t->timestamps();
        });

        // ── Job cards / repairs ────────────────────────────────────
        Schema::create('job_cards', function (Blueprint $t) {
            $t->id();
            $t->foreignId('business_id')->nullable()->constrained('businesses')->nullOnDelete();
            $t->string('job_no')->index();
            $t->unsignedBigInteger('customer_id')->nullable();
            $t->string('device')->nullable();
            $t->string('serial')->nullable();
            $t->text('issue')->nullable();
            $t->text('diagnosis')->nullable();
            $t->string('status')->default('received');
            $t->unsignedBigInteger('assigned_to')->nullable();
            $t->decimal('quoted_amount', 15, 2)->default(0);
            $t->decimal('deposit', 15, 2)->default(0);
            $t->decimal('revenue', 15, 2)->default(0);
            $t->decimal('labour_cost', 15, 2)->default(0);
            $t->string('warranty')->nullable();
            $t->timestamps();
        });

        Schema::create('job_parts', function (Blueprint $t) {
            $t->id();
            $t->foreignId('job_card_id')->constrained('job_cards')->cascadeOnDelete();
            $t->unsignedBigInteger('product_id')->nullable();
            $t->string('name')->nullable();
            $t->integer('qty')->default(1);
            $t->decimal('unit_cost', 15, 2)->default(0);
            $t->timestamps();
        });

        Schema::create('job_status_history', function (Blueprint $t) {
            $t->id();
            $t->foreignId('job_card_id')->constrained('job_cards')->cascadeOnDelete();
            $t->string('from_status')->nullable();
            $t->string('to_status');
            $t->unsignedBigInteger('user_id')->nullable();
            $t->text('notes')->nullable();
            $t->timestamps();
        });

        // ── Customers ──────────────────────────────────────────────
        Schema::create('customers', function (Blueprint $t) {
            $t->id();
            $t->foreignId('business_id')->nullable()->constrained('businesses')->nullOnDelete();
            $t->string('name');
            $t->string('phone')->nullable();
            $t->string('email')->nullable();
            $t->string('type')->default('Individual');
            $t->text('address')->nullable();
            $t->boolean('credit_allowed')->default(false);
            $t->decimal('balance', 15, 2)->default(0);
            $t->timestamps();
            $t->index(['business_id', 'name']);
        });

        // ── Suppliers ──────────────────────────────────────────────
        Schema::create('suppliers', function (Blueprint $t) {
            $t->id();
            $t->foreignId('business_id')->nullable()->constrained('businesses')->nullOnDelete();
            $t->string('name');
            $t->string('contact_person')->nullable();
            $t->string('phone')->nullable();
            $t->string('email')->nullable();
            $t->text('address')->nullable();
            $t->decimal('balance', 15, 2)->default(0);
            $t->timestamps();
            $t->index(['business_id', 'name']);
        });

        // ── Expenses ───────────────────────────────────────────────
        Schema::create('expenses', function (Blueprint $t) {
            $t->id();
            $t->foreignId('business_id')->nullable()->constrained('businesses')->nullOnDelete();
            $t->string('description');
            $t->string('category')->nullable();
            $t->decimal('amount', 15, 2)->default(0);
            $t->string('payment_account')->default('Cash register');
            $t->date('expense_date')->nullable();
            $t->string('reference')->nullable();
            $t->string('approval_status')->default('Pending Owner approval');
            $t->text('notes')->nullable();
            $t->unsignedBigInteger('created_by')->nullable();
            $t->timestamps();
            $t->index(['business_id', 'expense_date']);
        });

        // ── Returns ────────────────────────────────────────────────
        Schema::create('returns', function (Blueprint $t) {
            $t->id();
            $t->foreignId('business_id')->nullable()->constrained('businesses')->nullOnDelete();
            $t->string('type')->default('Customer return');
            $t->string('reference')->nullable();
            $t->unsignedBigInteger('sale_id')->nullable();
            $t->unsignedBigInteger('purchase_id')->nullable();
            $t->string('item')->nullable();
            $t->text('reason')->nullable();
            $t->string('resolution')->nullable();
            $t->decimal('amount', 15, 2)->default(0);
            $t->string('approval_status')->default('Awaiting Owner');
            $t->timestamps();
        });

        // ── Cash transactions ──────────────────────────────────────
        Schema::create('cash_transactions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('business_id')->nullable()->constrained('businesses')->nullOnDelete();
            $t->string('type');
            $t->string('account')->default('Cash register');
            $t->decimal('amount', 15, 2)->default(0);
            $t->string('reference')->nullable();
            $t->date('transaction_date')->nullable();
            $t->text('description')->nullable();
            $t->unsignedBigInteger('user_id')->nullable();
            $t->timestamps();
        });

        // ── Approval requests ──────────────────────────────────────
        Schema::create('approval_requests', function (Blueprint $t) {
            $t->id();
            $t->foreignId('business_id')->nullable()->constrained('businesses')->nullOnDelete();
            $t->string('type');
            $t->string('reference')->nullable();
            $t->json('payload')->nullable();
            $t->unsignedBigInteger('requested_by')->nullable();
            $t->string('status')->default('pending');
            $t->unsignedBigInteger('approved_by')->nullable();
            $t->timestamp('approved_at')->nullable();
            $t->text('notes')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        $drops = [
            'approval_requests','cash_transactions','returns','expenses','suppliers','customers',
            'job_status_history','job_parts','job_cards','purchase_expenses','purchase_items','purchases',
            'purchase_order_items','purchase_orders','documents','sale_items','sales',
            'stock_take_items','stock_takes','stock_movements','product_serials','products',
            'product_groups','categories',
        ];
        foreach ($drops as $t) { Schema::dropIfExists($t); }
    }
};
