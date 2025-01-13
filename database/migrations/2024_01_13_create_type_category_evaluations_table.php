<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('type_category_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('type_category_id')->constrained('type_categories', 'category_id')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->integer('fibonacci_weight'); // Will store values: 1,2,3,5,8
            $table->timestamps();

            // Ensure a user can only evaluate a type category once
            $table->unique(['type_category_id', 'user_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('type_category_evaluations');
    }
};
