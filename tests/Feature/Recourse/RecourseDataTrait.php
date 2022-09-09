<?php

namespace Tests\Feature\Recourse;

use App\Models\Settings;
use App\Enums\TypeRecourseEnum;

trait RecourseDataTrait
{

  protected function recourseValidData($overrides = [])
  {
    // solo devuelve el key, asi que se genera un valor aleatorio
    $typeName = array_rand([
      TypeRecourseEnum::TYPE_LIBRO->name => 1,
      TypeRecourseEnum::TYPE_VIDEO->name => 1
    ]);


    $total_pages = $total_chapters = $total_videos = $total_hours = null;

    if ($typeName == TypeRecourseEnum::TYPE_LIBRO->name) {
      $total_pages = 150;
      $total_chapters = 20;
    }

    if ($typeName == TypeRecourseEnum::TYPE_VIDEO->name) {
      $total_videos = 50;
      $total_hours = "15:20:13";
    }

    return array_merge([
      "name" => 'Nombre de mi recurso',
      "source" => 'D://micarpeta/misvideos/micurso',
      "author" => "Pepe LUna",
      "editorial" => "Mi editorial de Ejemplo",
      "type_id" => Settings::getData($typeName, "id"),
      "total_pages" => $total_pages,
      "total_chapters" => $total_chapters,
      "total_videos" => $total_videos,
      "total_hours" => $total_hours,
    ], $overrides);
  }
}
