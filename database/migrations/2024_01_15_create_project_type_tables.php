<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Create pivot table for projects and types
        Schema::create('project_type', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('type_id')->constrained('types', 'type_id')->onDelete('cascade');
            $table->timestamps();

            // Ensure a type can only be linked once to a project
            $table->unique(['project_id', 'type_id']);
        });

        // Create pivot table for projects and type categories with weight
        Schema::create('project_type_category', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained('type_categories', 'category_id')->onDelete('cascade');
            $table->foreignId('type_id')->constrained('types', 'type_id')->onDelete('cascade');
            $table->decimal('weight', 8, 2)->default(0);
            $table->timestamps();

            // Ensure a category can only be linked once to a project per type
            $table->unique(['project_id', 'category_id', 'type_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_type_category');
        Schema::dropIfExists('project_type');
    }
};
