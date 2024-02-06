<?php

namespace App\Models;

use App\Enums\UnitMeasureProgressEnum;
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
    "advanced",
    "date",
    "comment",
  ];

  protected $hidden = [
    'created_at',
    'updated_at'
  ];

  protected $appends = ['total', 'is_last_record'];

  protected function total(): Attribute
  {
    return new Attribute(
      get: fn () => Recourse::getTotalValueFromUnitMeasureProgress($this->recourse)
    );
  }

  protected function isLastRecord(): Attribute
  {
    return new Attribute(
      get: fn () => $this->recourse->progress()->latest('id')->first()->id === $this->id
    );
  }

  public function recourse()
  {
    return $this->belongsTo(Recourse::class);
  }
}
