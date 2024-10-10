<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\MorphPivot;

class Evaluationable extends MorphPivot
{
    protected $table = 'evaluationables';
}
