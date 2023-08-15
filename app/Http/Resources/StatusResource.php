<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StatusResource extends JsonResource
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
      "fecha" => $this->date,
      "comentario" => $this->comment,
      "estadoId" => $this->status_id,
      "estadoNombre" => $this->status_name,
      'esUltimoRegistro' => $this->is_last_record
    ];
  }

  public static function originalAttribute($index)
  {
    $attributes = [
      'identificador' => 'id',
      'fecha' => 'date',
      'comentario' => 'comment',
      'estadoId' => 'status_id',
      'estadoNombre' => 'status_name',
      'esUltimoRegistro' => 'is_last_record'
    ];

    return isset($attributes[$index]) ? $attributes[$index] : null;
  }

  // Mapeamos los atributos originales con los atributos transformados
  public static function transformedAttribute($index)
  {
    $attributes = [
      'id' => 'identificador',
      'date' => 'fecha',
      'comment' => 'comentario',
      'status_id' => 'estadoId',
      'status_name' => 'estadoNombre',
      'is_last_record' => 'esUltimoRegistro',
    ];

    return isset($attributes[$index]) ? $attributes[$index] : null;
  }
}
