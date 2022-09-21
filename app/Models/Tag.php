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

    protected $hidden = [
        'pivot'
    ];

    //TODO Definir como llenar el atributo style tanto en el proceso como en los factories 
    public function recourses()
    {
        return $this->morphedByMany(Recourse::class, 'taggable');
    }
}
