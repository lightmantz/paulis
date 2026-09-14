<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->foreignId('business_id')
                  ->nullable()
                  ->constrained('businesses')
                  ->nullOnDelete();

            // Identity
            $table->string('name');
            $table->string('sku')->nullable()->index();
            $table->string('barcode')->nullable()->index();

            // Classification
            $table->unsignedBigInteger('category_id')->nullable()->index();
            $table->unsignedBigInteger('group_id')->nullable()->index();
            $table->string('condition')->default('New');           // New / Used / Refurbished
            $table->text('specs')->nullable();

            // Stock control
            $table->string('tracking')->default('quantity');       // quantity / optional_serial / required_serial
            $table->integer('stock')->default(0);
            $table->integer('reorder_level')->default(5);

            // Pricing
            $table->decimal('unit_cost', 15, 2)->default(0);
            $table->decimal('selling_price', 15, 2)->default(0);

            // Relations
            $table->unsignedBigInteger('supplier_id')->nullable()->index();
            $table->unsignedBigInteger('created_by')->nullable();

            $table->timestamps();

            $table->index(['business_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
