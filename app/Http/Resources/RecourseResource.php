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
    // status, se Obtienen las relaciones entre status, se ordenan por fecha descendenmente con latest() y sólo se obtiene el primer registro con first()
    // progress, lo mismo que con status
    return [
      "identificador" => $this->id,
      "nombre" => $this->name,
      "ruta" => $this->source,
      "autor" => $this->author,
      "editorial" => $this->editorial,
      "tipoId" => $this->type_id,
      "unidadMedidadProgresoId" => $this->unit_measure_progress_id,
      "tipoNombre" => $this->type_name,
      "nombreEstadoActual" => $this->current_status_name,
      "totalPaginas" => $this->total_pages,
      "totalCapitulos" => $this->total_chapters,
      "totalVideos" => $this->total_videos,
      "totalHoras" => $this->total_hours,
      "totalProgresoPorcentaje" => $this->total_progress_percentage,
      "status" => new StatusResource($this->status()->latest()->first()),
      "tags" => TagResource::collection($this->tags),
      "progress" => new ProgressResource($this->progress()->latest()->first()),
    ];
  }

  public static function originalAttribute($index)
  {
    $attributes = [
      "identificador" => "id",
      "nombre" => "name",
      "ruta" => "source",
      "autor" => "author",
      "editorial" => "editorial",
      "tipoId" => "type_id",
      "unidadMedidadProgresoId" => "unit_measure_progress_id",
      "tipoNombre" => "type_name",
      "nombreEstadoActual" => "current_status_name",
      "totalPaginas" => "total_pages",
      "totalCapitulos" => "total_chapters",
      "totalVideos" => "total_videos",
      "totalHoras" => "total_hours",
      "totalProgresoPorcentaje" => "total_progress_percentage",
      "tags" => "tags",
      "status" => "status",
      "progress" => "progress",
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
      "unit_measure_progress_id" => "unidadMedidadProgresoId",
      "type_name" => "tipoNombre",
      "current_status_name" => "nombreEstadoActual",
      "total_pages" => "totalPaginas",
      "total_chapters" => "totalCapitulos",
      "total_videos" => "totalVideos",
      "total_hours" => "totalHoras",
      "total_progress_percentage" => "totalProgresoPorcentaje",
      "tags" => "tags",
      "status" => "status",
      "progress" => "progress",
    ];

    return isset($attributes[$index]) ? $attributes[$index] : null;
  }
}
