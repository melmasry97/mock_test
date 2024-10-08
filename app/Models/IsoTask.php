<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IsoTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'category_id',
        'weight',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($isoTask) {
            if (static::count() >= 9) {
                throw new \Exception('Cannot create more than 9 ISO Tasks.');
            }
        });
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
