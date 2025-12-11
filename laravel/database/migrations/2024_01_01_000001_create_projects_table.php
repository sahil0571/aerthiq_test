<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->decimal('budget', 15, 2)->nullable();
            $table->enum('status', ['planned', 'active', 'completed', 'on_hold'])->default('planned');
            $table->string('client_name', 255)->nullable();
            $table->timestamps();
            
            $table->index(['status']);
            $table->index('code');
        });
    }

    public function down()
    {
        Schema::dropIfExists('projects');
    }
};