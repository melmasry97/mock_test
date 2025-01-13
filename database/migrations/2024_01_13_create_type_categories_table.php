<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('type_categories', function (Blueprint $table) {
            $table->id('category_id');
            $table->string('category_name');
            $table->text('category_description')->nullable();
            $table->integer('evaluation_time_period');
            $table->decimal('evaluation_value', 8, 2)->default(0);
            $table->decimal('evaluation_average_value', 8, 2)->default(0);
            $table->foreignId('type_id')->constrained('types', 'type_id')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('type_categories');
    }
};
