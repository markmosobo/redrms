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
        Schema::create('inspections', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('tenancy_id')
                  ->constrained('tenancies')
                  ->cascadeOnDelete();
            $table->date('inspection_date'); 
            $table->longText('notes')->nullable(); 
            $table->foreignId('created_by')
                  ->constrained('users')
                  ->cascadeOnDelete(); 
            $table->enum('inspection_type', ['move_in', 'move_out'])->default('move_out');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inspections');
    }
};
