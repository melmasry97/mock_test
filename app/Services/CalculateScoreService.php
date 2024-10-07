<?php

namespace App\Services;

use App\Models\Task;
use App\Models\IsoTask;

class CalculateScoreService
{
    /**
     * Calculate the ISO weight score for a given task.
     *
     * @param int $taskId
     * @param array $inputs
     * @return float
     */
    public function calculateScore(int $taskId, array $inputs): float
    {
        // Retrieve the task
        $task = Task::find($taskId);

        // Get the module weight
        $moduleWeight = $task->weight;

        // Retrieve ISO tasks
        $isoTasks = IsoTask::all();

        // Initialize ISO weight
        $isoWeight = 0;

        // Assuming $inputs is an array of user inputs for the first three inputs
        // and the last input is a separate value
        $firstThreeInputs = array_slice($inputs, 0, 3);
        $fifthWeightInput = $inputs[3]; // Assuming the fifth input is at index 3

        // Define the matrix for ISO task weights
        $matrix = [
            [1, 2, 3],
            [4, 5, 6],
            [7, 8, 9]
        ];

        // Calculate the ISO weight based on the matrix and ISO task weights
        foreach ($firstThreeInputs as $index => $inputValue) {
            // Get the corresponding ISO task weight
            $isoTaskIndex = $matrix[$index][0] - 1; // Adjust for zero-based index
            $isoWeight += $inputValue * $isoTasks[$isoTaskIndex]->weight;
        }
        //todo:: divided by 4th input

        // Add the fifth weight input to the score
        $isoWeight += $fifthWeightInput;

        // Store the calculated ISO weight in the task
        $task->iso_weight = $isoWeight;
        $task->save();

        return $isoWeight;
    }
}
