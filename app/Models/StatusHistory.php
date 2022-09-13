<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatusHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        "recourse_id",
        "status_id",
        "date",
        "comment",
    ];

    public function recourse()
    {
        return $this->belongsTo(Recourse::class);
    }
}
