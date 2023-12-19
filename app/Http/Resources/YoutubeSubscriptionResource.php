<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class YoutubeSubscriptionResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
   */
  public function toArray($request)
  {
    // return parent::toArray($request);
    return [
      'identificador'  => $this->id,
      'youtubeId'  => $this->youtube_id,
      'usuarioId' => $this->user_id,
      'canalId' => $this->channel_id,
      'titulo' => $this->title,
      'fechaSubscripcion' => $this->published_at,
      'descripcion' => $this->description,
      'fotoDefault' => $this->thumbnail_default,
      'fotoMedium' => $this->thumbnail_medium,
      'fotoHigh' => $this->thumbnail_high,
      "tags" => TagResource::collection($this->tags),
    ];
  }

  // Mapeamos los atributos transformados con los atributos originales
  public static function originalAttribute($index)
  {
    $attributes = [
      'identificador' => 'id',
      'youtubeId'  => 'youtube_id',
      'usuarioId' => 'user_id',
      'canalId' => 'channel_id',
      'titulo' => 'title',
      'fechaSubscripcion' => 'published_at',
      'descripcion' => 'description',
      'fotoDefault' => 'thumbnail_default',
      'fotoMedium' => 'thumbnail_medium',
      'fotoHigh' => 'thumbnail_high',
      "tags" => "tags",
    ];

    return isset($attributes[$index]) ? $attributes[$index] : null;
  }

  // Mapeamos los atributos originales con los atributos transformados
  public static function transformedAttribute($index)
  {
    $attributes = [
      'id' => 'identificador',
      'youtube_id'  => 'youtubeId',
      'user_id' => 'usuarioId',
      'channel_id' => 'canalId',
      'title' => 'titulo',
      'published_at' => 'fechaSubscripcion',
      'description' => 'descripcion',
      'thumbnail_default' => 'fotoDefault',
      'thumbnail_medium' => 'fotoMedium',
      'thumbnail_high' => 'fotoHigh',
      "tags" => "tags",
    ];

    return isset($attributes[$index]) ? $attributes[$index] : null;
  }
}
