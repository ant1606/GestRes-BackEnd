<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProgressResource extends JsonResource
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
            "realizado"=> $this->done,
            "pendiente" => $this->pending,
            "fecha" =>$this->date,
            "comentario" =>$this->comment
        ];
    }

    public static function originalAttribute($index)
    {
        $attributes = [
            'realizado' => 'done',
            'pendiente' => 'pending',
            'fecha' => 'date',
            'comentario' => 'comment',
        ];

        return isset($attributes[$index]) ? $attributes[$index] : null;
    }

    public static function transformedAttribute($index)
    {
        $attributes = [
             'done' => 'realizado',
             'pending' => 'pendiente',
             'date' => 'fecha',
             'comment' => 'comentario'
        ];

        return isset($attributes[$index]) ? $attributes[$index] : null;
    }

}
