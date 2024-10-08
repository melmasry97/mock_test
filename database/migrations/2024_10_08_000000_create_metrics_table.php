<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->float('module_weight');
            $table->integer('input1');
            $table->integer('input2');
            $table->integer('input3');
            $table->integer('input4');
            $table->float('calculated_value');
            $table->json('matrix_values');
            $table->float('matrix_calculated_value'); // New column for matrix calculation
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('metrics');
    }
};
