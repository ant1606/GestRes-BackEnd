<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SettingsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
//        return parent::toArray($request);
      return [
        'id' => $this->id,
        'type' => $this->type,
        'key' => $this->key,
        'value' => $this->value,
        'value2' => $this->value2,
      ];
    }

  public static function originalAttribute($index)
  {
    $attributes = [
      'id' => 'id',
      'type' => 'type',
      'key' => 'key',
      'value' => 'value',
      'value2' => 'value2'
    ];

    return isset($attributes[$index]) ? $attributes[$index] : null;
  }

  public static function transformedAttribute($index)
  {
    $attributes = [
      'id' => 'id',
      'type' => 'type',
      'key' => 'key',
      'value' => 'value',
      'value2' => 'value2'
    ];

    return isset($attributes[$index]) ? $attributes[$index] : null;
  }
}
