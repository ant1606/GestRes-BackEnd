<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TagResource extends JsonResource
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
            'identificador' => $this->id,
            'nombre' => $this->name,
            'estilos' => $this->style,
        ];
    }

    // Mapeamos los atributos transformados con los atributos originales
    public static function originalAttribute($index)
    {
        $attributes = [
            'identificador' => 'id',
            'nombre' => 'name',
            'estilos' => 'style'
        ];

        return isset($attributes[$index]) ? $attributes[$index] : null;
    }

    // Mapeamos los atributos originales con los atributos transformados
    public static function transformedAttribute($index)
    {
        $attributes = [
            'id' => 'identificador',
            'name' => 'nombre',
            'style' => 'estilo'
        ];

        return isset($attributes[$index]) ? $attributes[$index] : null;
    }

    // public function with($request)
    // {
    //     return [
    //         'meta' => [
    //             'status' => 'status'
    //         ]
    //     ];
    // }
}
