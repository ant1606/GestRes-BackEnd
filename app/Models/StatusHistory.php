<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StatusHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        "recourse_id",
        "status_id",
        "date",
        "comment",
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    protected $appends = ['status_name'];

    protected function statusName(): Attribute
    {
        return new Attribute(
            get: fn () => Settings::getData(Settings::getKeyfromId($this->status_id), "value")
        );
    }

    public function recourse()
    {
        return $this->belongsTo(Recourse::class);
    }
}
