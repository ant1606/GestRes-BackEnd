<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class YoutubeSubscription extends Model
{
  use HasFactory;

  protected $fillable = [
    'id',
    'user_id',
    'channel_id',
    'title',
    'published_at',
    'description',
    'thumbnail_default',
    'thumbnail_medium',
    'thumbnail_high'
  ];

  public $incrementing = false;

  public function tags()
  {
    return $this->morphToMany(Tag::class, 'taggable');
  }
}
