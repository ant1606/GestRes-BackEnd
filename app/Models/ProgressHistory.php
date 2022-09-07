<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgressHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        "Recourse_id",
        "done",
        "pending",
        "date",
        "comment",
    ];
}
