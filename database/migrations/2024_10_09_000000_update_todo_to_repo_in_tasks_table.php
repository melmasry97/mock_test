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
        Task::where('state', 'todo')->update(['state' => TaskState::REPO]);
    }

    public function down()
    {
        Task::where('state', TaskState::REPO)->update(['state' => 'todo']);
    }
};
