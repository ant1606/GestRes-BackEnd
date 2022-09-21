<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgressHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        "recourse_id",
        "done",
        "pending",
        "date",
        "comment",
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public function recourse()
    {
        return $this->belongsTo(Recourse::class);
    }
}
