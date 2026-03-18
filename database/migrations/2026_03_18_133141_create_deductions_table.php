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
        Schema::create('deductions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('deposit_id')
                  ->constrained('deposits')
                  ->cascadeOnDelete();

            $table->foreignId('inspection_id')
                  ->nullable()
                  ->constrained('inspections')
                  ->nullOnDelete();

            $table->text('description')->nullable();

            $table->decimal('amount', 12, 2);

            $table->foreignId('approved_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamp('approved_at')->nullable();            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deductions');
    }
};
