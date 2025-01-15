<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('project_type_category', function (Blueprint $table) {
            $table->foreignId('type_id')->after('category_id')->constrained('types')->cascadeOnDelete();
        });
    }

    public function down()
    {
        Schema::table('project_type_category', function (Blueprint $table) {
            $table->dropForeign(['type_id']);
            $table->dropColumn('type_id');
        });
    }
};
