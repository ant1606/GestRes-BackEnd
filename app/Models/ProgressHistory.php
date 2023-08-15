<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
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

  protected function isLastRecord(): Attribute
  {
    return new Attribute(
      get: fn () => $this->recourse->progress()->latest()->first()->id === $this->id
    );
  }


  public function recourse()
  {
    return $this->belongsTo(Recourse::class);
  }
}
