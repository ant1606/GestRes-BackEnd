<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebPage extends Model
{
  use HasFactory;


  protected $fillable = [
    "url",
    "name",
    "description",
    "count_visits",
    "user_id"
  ];

  protected $hidden = [
    'created_at',
    'updated_at'
  ];
}
