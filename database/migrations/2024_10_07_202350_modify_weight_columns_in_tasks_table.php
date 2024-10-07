<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->float('weight')->nullable()->change(); // Make weight nullable
            $table->float('iso_weight')->nullable()->change(); // Make iso_weight nullable
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->float('weight')->nullable(false)->change(); // Revert to not nullable
            $table->float('iso_weight')->nullable(false)->change(); // Revert to not nullable
        });
    }
};
