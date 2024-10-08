<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectModule extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'weight', 'project_id', 'category_id'];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
