<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TypeCategoryEvaluation extends Model
{
    protected $fillable = [
        'type_category_id',
        'user_id',
        'fibonacci_weight',
    ];

    public function typeCategory(): BelongsTo
    {
        return $this->belongsTo(TypeCategory::class, 'type_category_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
