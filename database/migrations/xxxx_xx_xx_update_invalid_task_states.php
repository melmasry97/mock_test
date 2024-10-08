<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Task;
use App\Enums\TaskState;

return new class extends Migration
{
    public function up()
    {
        Task::whereNotIn('state', [
            TaskState::REPO->value,
            TaskState::TODO->value,
            TaskState::IN_PROGRESS->value,
            TaskState::DONE->value,
        ])->update(['state' => TaskState::TODO->value]);
    }

    public function down()
    {
        // This migration is not reversible
    }
};
