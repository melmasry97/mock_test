<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Models\IsoTask;

class IsoTaskWeightRule implements ValidationRule
{
    protected $projectId;
    protected $isoTaskId;

    public function __construct($projectId, $isoTaskId = null)
    {
        $this->projectId = $projectId;
        $this->isoTaskId = $isoTaskId;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $totalWeight = IsoTask::where('project_id', $this->projectId)
            ->when($this->isoTaskId, fn($query) => $query->where('id', '!=', $this->isoTaskId))
            ->sum('weight');

        if (($totalWeight + $value) > 100) {
            $fail("The total weight of ISO tasks for this project cannot exceed 100%.");
        }
    }
}
