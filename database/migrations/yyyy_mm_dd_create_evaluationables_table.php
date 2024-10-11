<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('evaluationables', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('evaluationable_id');
            $table->string('evaluationable_type');
            $table->float('weight', 8, 3)->nullable();
            $table->unsignedBigInteger('user_id');
            $table->timestamps();

            $table->index(['evaluationable_id', 'evaluationable_type'], 'eval_id_type_index');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['evaluationable_id', 'evaluationable_type', 'user_id'], 'eval_unique');
        });
    }

    public function down()
    {
        Schema::dropIfExists('evaluationables');
    }
};
