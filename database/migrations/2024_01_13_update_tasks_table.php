<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('tasks', function (Blueprint $table) {
            // Add new columns
            $table->string('task_project')->after('description')->nullable();
            $table->string('task_module')->after('task_project')->nullable();
            $table->integer('task_evaluation_time_period')->after('task_module')->nullable();
            $table->decimal('rice_score', 8, 2)->after('task_evaluation_time_period')->nullable();
            $table->decimal('overall_evaluation_value', 8, 2)->after('rice_score')->default(0);
            $table->foreignId('source_group_id')->after('overall_evaluation_value')->nullable()->constrained('source_groups', 'group_id');
            $table->foreignId('source_id')->after('source_group_id')->nullable()->constrained('sources', 'source_id');
            $table->foreignId('type_id')->after('source_id')->nullable()->constrained('types', 'type_id');
            $table->enum('status', ['pending', 'approved', 'evaluating', 'completed'])->after('type_id')->default('pending');
            $table->timestamp('evaluation_end_time')->after('status')->nullable();
        });

        // Create pivot table for tasks and type categories
        Schema::create('task_type_category', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('tasks')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('type_categories', 'category_id')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('task_type_category');

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['source_group_id']);
            $table->dropForeign(['source_id']);
            $table->dropForeign(['type_id']);

            $table->dropColumn([
                'task_project',
                'task_module',
                'task_evaluation_time_period',
                'rice_score',
                'overall_evaluation_value',
                'source_group_id',
                'source_id',
                'type_id',
                'status',
                'evaluation_end_time'
            ]);
        });
    }
};
