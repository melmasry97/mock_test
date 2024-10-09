<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeCategoryIdNullable extends Migration
{
    public function up()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->change();
        });

        Schema::table('project_modules', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->change();
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable(false)->change();
        });

        Schema::table('project_modules', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable(false)->change();
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable(false)->change();
        });
    }
}
