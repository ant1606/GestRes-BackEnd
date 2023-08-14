<?php

namespace App\Models;

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
    'total_videos',
    'total_pages',
    'total_chapters',
    'total_vides',
    'total_hours',
  ];

  protected $hidden = [
    'created_at',
    'updated_at',
  ];

  protected $appends = ['type_name', 'current_status_name'];

  protected function typeName(): Attribute
  {
    return new Attribute(
      get: fn () => Settings::getData(Settings::getKeyfromId($this->type_id), "value")
    );
  }

  protected function currentStatusName(): Attribute
  {
    return new Attribute(
      // validar que exista la relacion antes de obtener el status
      get: fn () => !$this->status->isEmpty() ? $this->loadExists('status')->status->last()->status_name : ''
    );
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
}
