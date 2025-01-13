<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('type_categories', function (Blueprint $table) {
            $table->renameColumn('category_id', 'id');
            $table->renameColumn('category_name', 'name');
            $table->renameColumn('category_description', 'description');
            $table->renameColumn('evaluation_time_period', 'time_period');
            $table->renameColumn('evaluation_value', 'value');
            $table->renameColumn('evaluation_average_value', 'average_value');
        });
    }

    public function down(): void
    {
        Schema::table('type_categories', function (Blueprint $table) {
            $table->renameColumn('id', 'category_id');
            $table->renameColumn('name', 'category_name');
            $table->renameColumn('description', 'category_description');
            $table->renameColumn('time_period', 'evaluation_time_period');
            $table->renameColumn('value', 'evaluation_value');
            $table->renameColumn('average_value', 'evaluation_average_value');
        });
    }
};
