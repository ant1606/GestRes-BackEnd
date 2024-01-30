<?php

  namespace App\Http\Controllers;

  use App\Enums\StatusRecourseEnum;
  use App\Enums\UnitMeasureProgressEnum;
  use App\Http\Requests\ProgressHistoryStoreRequest;
  use App\Http\Resources\ProgressCollection;
  use App\Http\Resources\ProgressResource;
  use App\Models\ProgressHistory;
  use App\Models\Recourse;
  use App\Models\Settings;
  use App\Models\StatusHistory;
  use Symfony\Component\HttpFoundation\Response;

  class ProgressHistoryController extends ApiController
  {

    public function __construct()
    {
      // $this->middleware('transform.input:'.ProgressResource::class);
    }

    public function index(Recourse $recourse)
    {
      $progressHistories = $recourse->progress()->latest()->get();

      return $this->sendResponse(new ProgressCollection($progressHistories), Response::HTTP_OK);
    }

    public function store(Recourse $recourse, ProgressHistoryStoreRequest $request)
    {
      $lastProgress = $recourse->progress->last();
      $total = $this->getValueFromUnitMeasureProgress($recourse);

      if ($request->date < $lastProgress->date)
        return $this->sendError( Response::HTTP_UNPROCESSABLE_ENTITY, "La fecha ingresada es menor al último registro existente.");
      //TODO APlicar cambios para horas y enteros

      $done = Settings::getKeyfromId($recourse['unit_measure_progress_id']) === UnitMeasureProgressEnum::UNIT_HOURS->name
        ? $this->processHours($request->advanced, $lastProgress->advanced)
        : $request->advanced - $lastProgress->advanced;

      // $done =   $request->advanced - $lastProgress->advanced;

      //TODO GENERAR CASO DE PRUEBA PARA ESTA SENTENCIA
      $pending = Settings::getKeyfromId($recourse['unit_measure_progress_id']) === UnitMeasureProgressEnum::UNIT_HOURS->name
        ? $this->convertHourToSeconds($total) - $this->convertHourToSeconds($request->advanced)
        : $total - $request->advanced;
      // $pending = $total - $request->advanced;

      if ($pending < 0)
        return $this->sendError(Response::HTTP_UNPROCESSABLE_ENTITY, "La cantidad avanzada no puede ser mayor a la cantidad total.");

      // $pending = $lastProgress->pending - $request->done;
      // if ($pending < 0)
      //   return $this->errorResponse(["api_response" => ["La cantidad de avance no puede ser mayor a la cantidad pendiente."]], Response::HTTP_UNPROCESSABLE_ENTITY);

      $progress = ProgressHistory::create([
        'recourse_id' => $recourse->id,
        'advanced' => $request->advanced,
        'done' => $done,
        'pending' => Settings::getKeyfromId($recourse['unit_measure_progress_id']) === UnitMeasureProgressEnum::UNIT_HOURS->name
          ? $this->processHours($total, $request->advanced)
          : $pending,
        'date' => $request->date,
        'comment' => $request->comment,
      ]);


      if ($pending === 0) {
        $commentAutogenerate = "REGISTRO GENERADO POR EL SISTEMA POR FINALIZACIÓN DEL RECURSO";
        StatusHistory::create([
          "recourse_id" => $recourse->id,
          "status_id" => Settings::getData(StatusRecourseEnum::STATUS_CULMINADO->name, "id"),
          "date" => $request->date,
          "comment" => $commentAutogenerate
        ]);
      }


      return $this->sendResponse(new ProgressResource($progress), Response::HTTP_CREATED,false);
    }

    //TODO EXtraer esta logica
    private function getValueFromUnitMeasureProgress(Recourse $recourse)
    {
      switch (Settings::getKeyfromId($recourse['unit_measure_progress_id'])) {
        case UnitMeasureProgressEnum::UNIT_CHAPTERS->name:
          return $recourse->total_chapters;
        case UnitMeasureProgressEnum::UNIT_PAGES->name:
          return $recourse->total_pages;
        case UnitMeasureProgressEnum::UNIT_HOURS->name:
          return $recourse->total_hours;
        case UnitMeasureProgressEnum::UNIT_VIDEOS->name:
          return $recourse->total_videos;
      }
    }

    //TODO ExtraerLogica
    private function processHours($hora1, $hora2, $isSubstract = true)
    {
      list($horas1, $minutos1, $segundos1) = explode(':', $hora1);
      list($horas2, $minutos2, $segundos2) = explode(':', $hora2);

      $totalSegundos1 = $horas1 * 3600 + $minutos1 * 60 + $segundos1;
      $totalSegundos2 = $horas2 * 3600 + $minutos2 * 60 + $segundos2;

      $totalSegundos = $isSubstract ? $totalSegundos1 - $totalSegundos2 : $totalSegundos1 + $totalSegundos2;

      $nuevasHoras = floor($totalSegundos / 3600);
      $nuevosMinutos = floor(($totalSegundos % 3600) / 60);
      $nuevosSegundos = $totalSegundos % 60;

      return sprintf('%02d:%02d:%02d', abs($nuevasHoras), abs($nuevosMinutos), abs($nuevosSegundos));
    }

    //TODO ExtraerLogica Pasarlo como un helper
    private function convertHourToSeconds($hour)
    {
      list($hours, $minutes, $seconds) = explode(':', $hour);
      return $hours * 3600 + $minutes * 60 + $seconds;
    }

    public function destroy(ProgressHistory $progressHistory)
    {
      $recourse = $progressHistory->recourse;

      if ($progressHistory->comment === "REGISTRO INICIAL GENERADO AUTOMATICAMENTE POR EL SISTEMA" && $progressHistory->id === $recourse->progress->first()->id) {
        return $this->sendError(
          Response::HTTP_UNPROCESSABLE_ENTITY,
          "No se puede eliminar el registro generado por el sistema"
        );
      }

      if ($progressHistory->id !== $recourse->progress->last()->id) {
        return $this->sendError(
          Response::HTTP_UNPROCESSABLE_ENTITY,
          "No se puede eliminar el registro, sólo puede eliminarse el ultimo registro del recurso"
        );
      }

      $progressHistory->delete();
      return $this->sendResponse(new ProgressResource($progressHistory), Response::HTTP_ACCEPTED, false);
    }
  }
