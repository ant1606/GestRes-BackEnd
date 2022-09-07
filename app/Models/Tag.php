<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'style'
    ];

    public function recourses()
    {
        return $this->morphedByMany(Recourse::class, 'taggable');
    }
}
