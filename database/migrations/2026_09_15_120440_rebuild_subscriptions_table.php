<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('business_id')
                  ->nullable()
                  ->constrained('businesses')
                  ->cascadeOnDelete();

            $table->string('plan', 50)->default('Starter');
            $table->decimal('monthly_fee', 15, 2)->default(0);
            $table->string('status', 50)->default('Trial');

            $table->date('starts_at')->nullable();
            $table->date('ends_at')->nullable();
            $table->date('trial_ends_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
