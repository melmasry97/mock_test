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
        Task::where('state', TaskState::TODO->value)->update(['state' => TaskState::REPO->value]);
    }

    public function down()
    {
        Task::where('state', TaskState::REPO->value)->update(['state' => TaskState::TODO->value]);
    }
};
