<?php

namespace Tests\Feature\ProgressHistory;

use Carbon\Carbon;

trait ProgressHistoryDataTrait
{

  protected function progressHistoryValidData(bool $recourse_measure_is_hour, array $overrides = [])
  {
    return array_merge([
      'advanced' => $recourse_measure_is_hour? "00:05:00": "5",
      // 'pendiente' => 10,
      'date' => Carbon::now()->toDateString(),
      'comment' => "Comentario de prueba",
    ], $overrides);
  }
}
