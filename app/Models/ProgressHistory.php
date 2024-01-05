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
      get: fn () => $this->getValueFromUnitMeasureProgress($this->recourse)
    );
  }

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

  //TODO EXtraer esta logica
  private function getValueFromUnitMeasureProgress(Recourse $recourse)
  {
    switch (Settings::getKeyfromId($recourse['unit_measure_progress_id'])) {
      case UnitMeasureProgressEnum::UNIT_CHAPTERS->name:
        return  $recourse->total_chapters;
      case UnitMeasureProgressEnum::UNIT_PAGES->name:
        return  $recourse->total_pages;
      case UnitMeasureProgressEnum::UNIT_HOURS->name:
        return  $recourse->total_hours;
      case UnitMeasureProgressEnum::UNIT_VIDEOS->name:
        return $recourse->total_videos;
    }
  }
}
