<?php

namespace App\Models;

use App\Enums\UnitMeasureProgressEnum;
use App\Helpers\TimeHelper;
use Illuminate\Database\Eloquent\Casts\Attribute;
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
    'unit_measure_progress_id',
    'total_videos',
    'total_pages',
    'total_chapters',
    'total_videos',
    'total_hours',
    'user_id'
  ];

  protected $hidden = [
    'created_at',
    'updated_at',
  ];

  protected $appends = ['type_name', 'unit_measure_progress_name', 'current_status_name', 'total_progress_percentage'];


  protected function typeName(): Attribute
  {
    return new Attribute(
      get: fn () => Settings::getData(Settings::getKeyfromId($this->type_id), "value")
    );
  }

  protected function unitMeasureProgressName(): Attribute
  {
    return new Attribute(
      get: fn () => Settings::getData(Settings::getKeyfromId($this->unit_measure_progress_id), "value")
    );
  }

  protected function currentStatusName(): Attribute
  {
    if ($this->status()->exists()) {
      return new Attribute(
        get: fn () => !$this->status->isEmpty() ? $this->loadExists('status')->status->last()->status_name : ''
      );
    }else {
      return new Attribute(
        get: null
      );
    }

  }

  protected function totalProgressPercentage(): Attribute
  {
    if ($this->progress()->exists()) {
      return new Attribute(
        get: fn () =>  Settings::getKeyfromId($this->unit_measure_progress_id) === UnitMeasureProgressEnum::UNIT_HOURS->name
          ? round(TimeHelper::convertHourToSeconds($this->progress->last()->advanced) / TimeHelper::convertHourToSeconds($this->progress->first()->pending) * 100, 2)
          : round($this->progress->last()->advanced / $this->progress->first()->pending * 100, 2)
      );
    } else {
      return new Attribute(
        get: null
      );
    }
  }

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

  static public function getTotalValueFromUnitMeasureProgress(Recourse|array $recourse)
  {

    switch (Settings::getKeyfromId($recourse['unit_measure_progress_id'])) {
      case UnitMeasureProgressEnum::UNIT_CHAPTERS->name:
        return $recourse["total_chapters"];
      case UnitMeasureProgressEnum::UNIT_PAGES->name:
        return $recourse["total_pages"];
      case UnitMeasureProgressEnum::UNIT_HOURS->name:
        return $recourse["total_hours"];
      case UnitMeasureProgressEnum::UNIT_VIDEOS->name:
        return $recourse["total_videos"];
    }
  }
}
