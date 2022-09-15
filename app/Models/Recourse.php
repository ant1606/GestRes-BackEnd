<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recourse extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'source',
        'author',
        'editorial',
        'type_id',
        'total_videos',
        'total_pages',
        'total_chapters',
        'total_vides',
        'total_hours',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function status()
    {
        return $this->hasMany(StatusHistory::class);
    }

    public function progress()
    {
        return $this->hasMany(ProgressHistory::class);
    }
}
