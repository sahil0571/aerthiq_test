<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('deductions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->decimal('amount', 15, 2);
            $table->string('description');
            $table->date('date');
            $table->enum('deduction_type', ['tax', 'insurance', 'loan', 'advance', 'other']);
            $table->boolean('is_recurring')->default(false);
            $table->decimal('monthly_deduction', 15, 2)->nullable();
            $table->string('financial_year', 10)->nullable();
            $table->timestamps();
            
            $table->index(['employee_id', 'date']);
            $table->index(['deduction_type', 'is_recurring']);
            $table->index('financial_year');
        });
    }

    public function down()
    {
        Schema::dropIfExists('deductions');
    }
};