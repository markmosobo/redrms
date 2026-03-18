<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('deposits', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('tenancy_id')
                  ->constrained('tenancies')
                  ->cascadeOnDelete();
            $table->decimal('amount_received');
            $table->date('received_date')->nullable();
            $table->enum('status', ['held', 'partially_deducted','refunded'])->default('held');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deposits');
    }
};
