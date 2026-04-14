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

            $table->decimal('amount_received', 12, 2);
            $table->decimal('required_amount', 12, 2);
            $table->decimal('current_balance', 12, 2); // 🔥 important for deductions tracking

            $table->date('received_date')->nullable();

            // 🔥 improved lifecycle tracking
            $table->enum('status', [
                'active',
                'held',
                'under_inspection',
                'deductions_applied',
                'pending_refund',
                'refunded'
            ])->default('active');

            $table->text('remarks')->nullable();

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
