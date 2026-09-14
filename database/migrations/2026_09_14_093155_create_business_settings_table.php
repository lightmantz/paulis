<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->unique()->constrained()->cascadeOnDelete();

            // Business profile
            $table->string('display_name')->nullable();
            $table->string('tin')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('logo_path')->nullable();

            // Receipt / document branding
            $table->string('receipt_footer')->nullable();
            $table->string('invoice_prefix')->default('INV');
            $table->string('receipt_prefix')->default('RCP');
            $table->string('quotation_prefix')->default('QUO');

            // Currency & tax
            $table->string('currency', 8)->default('TZS');
            $table->decimal('vat_rate', 5, 2)->default(0);

            // Stock rules
            $table->integer('default_reorder_level')->default(5);
            $table->boolean('require_owner_discount_approval')->default(true);
            $table->decimal('max_sales_discount_percent', 5, 2)->default(5);

            // Payment accounts enabled
            $table->json('payment_accounts')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_settings');
    }
};
