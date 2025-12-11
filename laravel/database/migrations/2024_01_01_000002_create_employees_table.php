<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_code', 20)->unique();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('email', 255)->nullable()->unique();
            $table->string('phone', 20)->nullable();
            $table->string('department', 100)->nullable();
            $table->string('position', 100)->nullable();
            $table->date('hire_date')->nullable();
            $table->decimal('salary', 15, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('project_id')->nullable()->constrained('projects')->onDelete('set null');
            $table->timestamps();
            
            $table->index(['department', 'is_active']);
            $table->index(['project_id', 'is_active']);
            $table->index('employee_code');
        });
    }

    public function down()
    {
        Schema::dropIfExists('employees');
    }
};