<?php
  namespace App\Utils;

  class TimeUtils
  {
    public function processHours($hora1, $hora2, $isSubtract = true): string
    {
      list($horas1, $minutos1, $segundos1) = explode(':', $hora1);
      list($horas2, $minutos2, $segundos2) = explode(':', $hora2);

      $totalSegundos1 = (int)$horas1 * 3600 + (int)$minutos1 * 60 + (int)$segundos1;
      $totalSegundos2 = (int)$horas2 * 3600 + (int)$minutos2 * 60 + (int)$segundos2;

      $totalSegundos = $isSubtract ? $totalSegundos1 - $totalSegundos2 : $totalSegundos1 + $totalSegundos2;

      $nuevasHoras = floor($totalSegundos / 3600);
      $nuevosMinutos = floor(($totalSegundos % 3600) / 60);
      $nuevosSegundos = $totalSegundos % 60;

      return sprintf('%02d:%02d:%02d', abs($nuevasHoras), abs($nuevosMinutos), abs($nuevosSegundos));
    }

    public function convertHourToSeconds($hour): int
    {
      list($hours, $minutes, $seconds) = explode(':', $hour);
      return (int)$hours * 3600 + (int)$minutes * 60 + (int)$seconds;
    }
  }


