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
        Schema::create('receipts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('receipt_number')->unique();
            $table->enum('type', ['deposit', 'payment', 'refund']);
            $table->string('payment_method')->nullable(); 
            $table->string('mpesa_code')->nullable();
            $table->decimal('amount', 10, 2);
            $table->timestamp('issued_at')->useCurrent();
            $table->foreignId('deposit_id')->constrained()->cascadeOnDelete();
            $table->json('data'); // to store extra info like tenant name
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('receipts');
    }
};
