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
        Schema::create('refunds', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->foreignId('deposit_id')
                ->constrained('deposits')
                ->cascadeOnDelete();

            // 🔥 calculated AFTER deductions
            $table->decimal('refundable_amount', 12, 2);

            $table->date('refund_date')->nullable();

            // 🔥 stronger workflow control
            $table->enum('status', [
                'pending',
                'approved',
                'rejected',
                'paid'
            ])->default('pending');

            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('approved_at')->nullable();

            // 🔥 critical for real-world + defense
            $table->string('payment_reference')->nullable();

            $table->text('remarks')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};
