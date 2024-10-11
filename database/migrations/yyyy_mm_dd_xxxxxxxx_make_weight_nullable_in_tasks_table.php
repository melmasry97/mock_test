<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->float('weight')->nullable()->change();
        });
        Schema::table('projects', function (Blueprint $table) {
            $table->float('weight')->nullable()->change();
        });
        Schema::table('project_modules', function (Blueprint $table) {
            $table->integer('weight')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->float('weight')->nullable(false)->change();
        });
        Schema::table('projects', function (Blueprint $table) {
            $table->float('weight')->nullable(false)->change();
        });
        Schema::table('project_modules', function (Blueprint $table) {
            $table->integer('weight')->nullable(false)->change();
        });
    }
};
