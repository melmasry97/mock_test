<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Metric;
use Illuminate\Http\Request;

class MetricController extends Controller
{
    public function create(Task $task)
    {
        return view('metrics.create', compact('task'));
    }

    public function store(Request $request, Task $task)
    {
        $validatedData = $request->validate([
            'input1' => 'required|in:1,3,4,6,8,10',
            'input2' => 'required|in:1,3,4,6,8,10',
            'input3' => 'required|in:1,3,4,6,8,10',
            'input4' => 'required|in:1,3,5,7,10',
            'matrix_values' => 'required|array|size:9',
            'matrix_values.*' => 'required|in:0,1,2,3,5',
        ]);

        $calculatedValue = ($validatedData['input1'] * $validatedData['input2'] * $validatedData['input3']) / $validatedData['input4'];

        $metric = new Metric([
            'module_weight' => $task->module_weight,
            'input1' => $validatedData['input1'],
            'input2' => $validatedData['input2'],
            'input3' => $validatedData['input3'],
            'input4' => $validatedData['input4'],
            'calculated_value' => $calculatedValue,
            'matrix_values' => $validatedData['matrix_values'],
        ]);

        $task->metric()->save($metric);
        $task->update(['state' => 'done']);

        return redirect()->route('tasks.edit', $task)->with('success', 'Metric added successfully.');
    }
}
