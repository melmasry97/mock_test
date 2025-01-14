<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // Add RICE evaluation end time
            $table->timestamp('rice_evaluation_end_time')->nullable()->after('status');

            // Make evaluation end time nullable if it's not already
            $table->timestamp('evaluation_end_time')->nullable()->change();

            // Add final RICE score fields
            $table->integer('reach')->nullable()->after('rice_evaluation_end_time');
            $table->integer('impact')->nullable()->after('reach');
            $table->integer('confidence')->nullable()->after('impact');
            $table->integer('effort')->nullable()->after('confidence');
            $table->float('rice_score')->nullable()->after('effort');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn([
                'rice_evaluation_end_time',
                'reach',
                'impact',
                'confidence',
                'effort',
                'rice_score',
            ]);
        });
    }
};
