<?php

  namespace App\Http\Services;

  use App\DTOs\ProgressHistory\ProgressDeleteResult;
  use App\DTOs\ProgressHistory\ProgressSaveResult;
  use App\Enums\StatusRecourseEnum;
  use App\Enums\UnitMeasureProgressEnum;
  use App\Models\ProgressHistory;
  use App\Models\Recourse;
  use App\Models\Settings;
  use App\Models\StatusHistory;
  use App\Utils\TimeUtils;

  class ProgressHistoryService
  {

    public function __construct(
      protected TimeUtils $timeUtils
    ){}

    public function save_progress(Recourse $recourse, array $progress): ProgressSaveResult
    {
      $lastProgress = $recourse->progress->last();
      $total = Recourse::getTotalValueFromUnitMeasureProgress($recourse);

      if ($progress['date'] < $lastProgress->date)
        return new ProgressSaveResult(
          false,
          null,
          "La fecha ingresada es menor al último registro existente."
        );

      //TODO APlicar cambios para horas y enteros

      $done = Settings::getKeyfromId($recourse['unit_measure_progress_id']) === UnitMeasureProgressEnum::UNIT_HOURS->name
        ? $this->timeUtils->processHours($progress['advanced'], $lastProgress->advanced)
        : $progress['advanced'] - $lastProgress->advanced;

      // $done =   $progress['dvanced - $lastProgress->advanced'];

      //TODO GENERAR CASO DE PRUEBA PARA ESTA SENTENCIA
      $pending = Settings::getKeyfromId($recourse['unit_measure_progress_id']) === UnitMeasureProgressEnum::UNIT_HOURS->name
        ? $this->timeUtils->convertHourToSeconds($total) - $this->timeUtils->convertHourToSeconds($progress['advanced'])
        : $total - $progress['advanced'];
      // $pending = $total - $progress['advanced'];

      if ($pending < 0)
        return new ProgressSaveResult(
          false,
          null,
          "La cantidad avanzada no puede ser mayor a la cantidad total."
        );

      // $pending = $lastProgress->pending - $progress['one'];

      $progress = ProgressHistory::create([
        'recourse_id' => $recourse->id,
        'advanced' => $progress['advanced'],
        'done' => $done,
        'pending' => Settings::getKeyfromId($recourse['unit_measure_progress_id']) === UnitMeasureProgressEnum::UNIT_HOURS->name
          ? $this->timeUtils->processHours($total, $progress['advanced'])
          : $pending,
        'date' => $progress['date'],
        'comment' => $progress['comment'],
      ]);


      if ($pending === 0) {
        $commentAutogenerate = "REGISTRO GENERADO POR EL SISTEMA POR FINALIZACIÓN DEL RECURSO";
        StatusHistory::create([
          "recourse_id" => $recourse->id,
          "status_id" => Settings::getData(StatusRecourseEnum::STATUS_CULMINADO->name, "id"),
          "date" => $progress['date'],
          "comment" => $commentAutogenerate
        ]);
      }

      return new ProgressSaveResult(
        true,
        $progress
      );
    }

    public function delete_progress(ProgressHistory $progress): ProgressDeleteResult
    {
      $recourse = $progress->recourse;

      if ($progress->comment === "REGISTRO INICIAL GENERADO AUTOMATICAMENTE POR EL SISTEMA" && $progress->id === $recourse->progress->first()->id) {
        return new ProgressDeleteResult(
          false,
          null,
          "No se puede eliminar el registro generado por el sistema"
          );
      }

      if ($progress->id !== $recourse->progress->last()->id) {
        return new ProgressDeleteResult(
          false,
          null,
          "No se puede eliminar el registro, sólo puede eliminarse el ultimo registro del recurso"
        );
      }

      $progress->delete();
      return new ProgressDeleteResult(
        true,
        $progress
      );
    }
  }
