<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('description');
            $table->decimal('amount', 15, 2);
            $table->enum('transaction_type', ['debit', 'credit']);
            $table->foreignId('account_id')->constrained('accounts')->onDelete('cascade');
            $table->foreignId('project_id')->nullable()->constrained('projects')->onDelete('set null');
            $table->foreignId('employee_id')->nullable()->constrained('employees')->onDelete('set null');
            $table->string('category', 100)->nullable();
            $table->string('reference', 100)->nullable();
            $table->text('notes')->nullable();
            $table->string('financial_year', 10)->nullable();
            $table->timestamps();
            
            $table->index(['date']);
            $table->index(['account_id', 'transaction_type']);
            $table->index(['project_id', 'transaction_type']);
            $table->index(['employee_id', 'transaction_type']);
            $table->index('financial_year');
        });
    }

    public function down()
    {
        Schema::dropIfExists('transactions');
    }
};