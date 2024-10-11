<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('iso_tasks', function (Blueprint $table) {
            $table->unsignedBigInteger('project_id')->nullable()->after('id');
            $table->date('end_date')->nullable()->after('weight');

            $table->foreign('project_id')->references('id')->on('projects')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('iso_tasks', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropColumn(['project_id', 'end_date']);
        });
    }
};
