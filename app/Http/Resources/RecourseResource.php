<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RecourseResource extends JsonResource
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
            "identificador" => $this->id,
            "nombre" => $this->name,
            "ruta" => $this->source,
            "autor" => $this->author,
            "editorial" => $this->editorial,
            "tipoId" => $this->type_id,
            "totalPaginas" => $this->total_pages,
            "totalCapitulos" => $this->total_chapters,
            "totalVideos" => $this->total_videos,
            "totalHoras" => $this-> total_hours
        ];
    }

    public static function originalAttribute($index)
    {
        $attributes = [
            "identificador" =>"id",
            "nombre" =>"name",
            "ruta" =>"source",
            "autor" =>"author",
            "editorial" =>"editorial",
            "tipoId" =>"type_id",
            "totalPaginas" =>"total_pages",
            "totalCapitulos" =>"total_chapters",
            "totalVideos" =>"total_videos",
            "totalHoras" =>"total_hours"
        ];

        return isset($attributes[$index]) ? $attributes[$index] : null;
    }

    public static function transformedAttribute($index)
    {
        $attributes = [
            "id" => "identificador",
            "name" => "nombre",
            "source" => "ruta",
            "author" => "autor",
            "editorial" => "editorial",
            "type_id" => "tipoId",
            "total_pages" => "totalPaginas",
            "total_chapters" => "totalCapitulos",
            "total_videos" => "totalVideos",
            "total_hours" => "totalHoras",
        ];

        return isset($attributes[$index]) ? $attributes[$index] : null;
    }
}
