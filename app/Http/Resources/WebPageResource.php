<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class WebPageResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
   */
  public function toArray($request)
  {
    return [
      "identificador" => $this->id,
      "nombre" => $this->name ?? "",
      "url" => $this->url,
      "descripcion" => $this->description ?? "",
      "totalVisitas" => $this->count_visits ?? 0,
      "tags" => TagResource::collection($this->tags),
    ];
  }

  public static function originalAttribute($index)
  {
    $attributes = [
      "identificador" => "id",
      "nombre" => "name",
      "url" => "url",
      "descripcion" => "description",
      "totalVisitas" => "count_visits",
      "tags" => "tags",
    ];

    return isset($attributes[$index]) ? $attributes[$index] : null;
  }

  public static function transformedAttribute($index)
  {
    $attributes = [
      "id" => "identificador",
      "name" => "nombre",
      "url" => "url",
      "description" => "descripcion",
      "count_visits" => "totalVisitas",
      "tags" => "tags",
    ];

    return isset($attributes[$index]) ? $attributes[$index] : null;
  }
}
