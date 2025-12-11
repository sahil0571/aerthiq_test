<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('transaction_deductions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained('transactions')->onDelete('cascade');
            $table->foreignId('deduction_id')->constrained('deductions')->onDelete('cascade');
            $table->decimal('amount_applied', 15, 2);
            $table->timestamps();
            
            $table->unique(['transaction_id', 'deduction_id']);
            $table->index(['transaction_id']);
            $table->index(['deduction_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('transaction_deductions');
    }
};