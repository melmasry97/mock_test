<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_type_category_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('type_id')->constrained('types', 'type_id')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('type_categories', 'category_id')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('weight', 8, 2);
            $table->timestamp('evaluation_end_time')->nullable();
            $table->timestamps();

            // Ensure a user can only evaluate a category once per project and type
            $table->unique(['project_id', 'type_id', 'category_id', 'user_id'], 'unique_evaluation');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_type_category_evaluations');
    }
};
