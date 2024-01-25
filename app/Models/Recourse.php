<?php

namespace App\Models;

use App\Enums\UnitMeasureProgressEnum;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use function PHPUnit\Framework\isNull;

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
    return new Attribute(
      //TODO validar que exista la relacion antes de obtener el status
      get: fn () => !$this->status->isEmpty() ? $this->loadExists('status')->status->last()->status_name : ''
    );
  }

  protected function totalProgressPercentage(): Attribute
  {
    if ($this->progress()->exists()) {
      return new Attribute(
        get: fn () =>  Settings::getKeyfromId($this->unit_measure_progress_id) === UnitMeasureProgressEnum::UNIT_HOURS->name
          ? round($this->convertHourToSeconds($this->progress->last()->advanced) / $this->convertHourToSeconds($this->progress->first()->pending) * 100, 2)
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

  //TODO Pasarlo como un helper
  private function convertHourToSeconds($hour)
  {
    list($hours, $minutes, $seconds) = explode(':', $hour);
    return $hours * 3600 + $minutes * 60 + $seconds;
  }
}
