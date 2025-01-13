<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('types', function (Blueprint $table) {
            $table->renameColumn('type_id', 'id');
            $table->renameColumn('type_name', 'name');
            $table->renameColumn('type_description', 'description');
        });
    }

    public function down(): void
    {
        Schema::table('types', function (Blueprint $table) {
            $table->renameColumn('id', 'type_id');
            $table->renameColumn('name', 'type_name');
            $table->renameColumn('description', 'type_description');
        });
    }
};
