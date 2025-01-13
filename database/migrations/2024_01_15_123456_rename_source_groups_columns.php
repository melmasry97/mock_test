<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('source_groups', function (Blueprint $table) {
            $table->renameColumn('group_id', 'id');
            $table->renameColumn('group_name', 'name');
            $table->renameColumn('group_description', 'description');
        });
    }

    public function down(): void
    {
        Schema::table('source_groups', function (Blueprint $table) {
            $table->renameColumn('id', 'group_id');
            $table->renameColumn('name', 'group_name');
            $table->renameColumn('description', 'group_description');
        });
    }
};
