<?php

namespace App\Models;

use Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
  use HasFactory;

  protected $fillable = [
    'name',
    'style'
  ];

  protected $hidden = [
    'pivot',
    'created_at',
    'updated_at'
  ];

  public function recourses()
  {
    return $this->morphedByMany(Recourse::class, 'taggable');
  }

  public function youtubesubscription()
  {
    return $this->morphedByMany(YoutubeSubscription::class, 'taggable');
  }

  public function webpages()
  {
    return $this->morphedByMany(WebPage::class, 'taggable');
  }
}
