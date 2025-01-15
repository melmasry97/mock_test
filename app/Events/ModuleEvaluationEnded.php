<?php

namespace App\Events;

use App\Models\Project;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ModuleEvaluationEnded
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Project $project
    ) {}
}
