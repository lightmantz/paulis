<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('business_id')
                  ->nullable()
                  ->constrained('businesses')
                  ->nullOnDelete();

            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('type')->default('Individual');   // Individual / Business / School
            $table->text('address')->nullable();
            $table->boolean('credit_allowed')->default(false);
            $table->decimal('balance', 15, 2)->default(0);

            $table->timestamps();

            $table->index(['business_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
