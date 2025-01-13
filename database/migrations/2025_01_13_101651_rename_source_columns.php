<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sources', function (Blueprint $table) {
            $table->renameColumn('source_id', 'id');
            $table->renameColumn('source_name', 'name');
            $table->renameColumn('source_description', 'description');
            $table->renameColumn('group_id', 'source_group_id'); // This follows Laravel convention for foreign keys
        });
    }

    public function down(): void
    {
        Schema::table('sources', function (Blueprint $table) {
            $table->renameColumn('id', 'source_id');
            $table->renameColumn('name', 'source_name');
            $table->renameColumn('description', 'source_description');
            $table->renameColumn('source_group_id', 'group_id');
        });
    }
};
