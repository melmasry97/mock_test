<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('types', function (Blueprint $table) {
            $table->foreignId('project_id')->nullable()->after('description')->constrained('projects')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('types', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropColumn('project_id');
        });
    }
};
