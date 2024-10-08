<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    // ... other methods ...

    public function edit(Task $task)
    {
        if ($task->state === TaskState::REPO) {
            $task->update(['state' => TaskState::IN_PROGRESS]);
        }

        return view('tasks.edit', compact('task'));
    }

    // ... other methods ...

    public function index()
    {
        $tasks = Task::all();
        return view('tasks.index', compact('tasks'));
    }
}
