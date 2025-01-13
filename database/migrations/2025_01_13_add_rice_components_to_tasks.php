<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->integer('reach')->nullable()->after('rice_score')->comment('R component of RICE score');
            $table->integer('impact')->nullable()->after('reach')->comment('I component of RICE score');
            $table->integer('confidence')->nullable()->after('impact')->comment('C component of RICE score');
            $table->integer('effort')->nullable()->after('confidence')->comment('E component of RICE score');
        });
    }

    public function down()
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn(['reach', 'impact', 'confidence', 'effort']);
        });
    }
};
