<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('businesses', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('owner_name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('city')->nullable();
            $table->string('address')->nullable();
            $table->string('tin')->nullable();
            $table->string('logo')->nullable();

            $table->enum('plan', ['Starter', 'Growth', 'Professional'])
                  ->default('Starter');
            $table->enum('status', ['Active', 'Trial', 'Suspended', 'Revoked'])
                  ->default('Trial');

            $table->decimal('monthly_fee', 15, 2)->default(0);
            $table->date('renewal_date')->nullable();
            $table->timestamp('trial_ends_at')->nullable();

            $table->timestamps();
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('businesses');
    }
};
