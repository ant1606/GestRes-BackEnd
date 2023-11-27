<?php

namespace Tests\Feature\ProgressHistory;

use Carbon\Carbon;

trait ProgressHistoryDataTrait
{

  protected function progressHistoryValidData($overrides = [])
  {

    return array_merge([
      'done' => 5,
      // 'pendiente' => 10,
      'date' => Carbon::now()->toDateString(),
      'comment' => "Comentario de prueba",
    ], $overrides);
  }
}
