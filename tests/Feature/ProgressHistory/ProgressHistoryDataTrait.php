<?php

namespace Tests\Feature\ProgressHistory;

use Carbon\Carbon;

trait ProgressHistoryDataTrait
{

  protected function progressHistoryValidData($overrides = [])
  {

    return array_merge([
      'realizado' => 5,
      'pendiente' => 10,
      'fecha' => Carbon::now()->toDateString(),
      'comentario' => "Comentario de prueba",
    ], $overrides);
  }
}
