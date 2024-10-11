<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('project_modules', function (Blueprint $table) {
            $table->date('end_date')->nullable();
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->date('end_date')->nullable();
        });
    }

    public function down()
    {
        Schema::table('project_modules', function (Blueprint $table) {
            $table->dropColumn('end_date');
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('end_date');
        });
    }
};
