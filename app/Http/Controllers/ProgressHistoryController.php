<?php

namespace App\Http\Controllers;

use App\Enums\StatusRecourseEnum;
use App\Enums\UnitMeasureProgressEnum;
use App\Http\Resources\ProgressCollection;
use App\Http\Resources\ProgressResource;
use App\Models\Recourse;
use Illuminate\Http\Request;
use App\Models\ProgressHistory;
use App\Http\Controllers\ApiController;
use App\Http\Requests\ProgressHistoryStoreRequest;
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

    return $this->showAllResource(new ProgressCollection($progressHistories), Response::HTTP_OK);
  }

  public function store(Recourse $recourse, ProgressHistoryStoreRequest $request)
  {
    $lastProgress = $recourse->progress->last();
    $total = $this->getValueFromUnitMeasureProgress($recourse);

    if ($request->date < $lastProgress->date)
      return $this->errorResponse(["api_response" => ["La fecha ingresada es menor al último registro existente."]], Response::HTTP_UNPROCESSABLE_ENTITY);

    $done =   $request->advanced - $lastProgress->advanced;
    $pending = $total - $request->advanced;
    if ($pending < 0)
      return $this->errorResponse(["api_response" => ["La cantidad avanzada no puede ser mayor a la cantidad total."]], Response::HTTP_UNPROCESSABLE_ENTITY);

    // $pending = $lastProgress->pending - $request->done;
    // if ($pending < 0)
    //   return $this->errorResponse(["api_response" => ["La cantidad de avance no puede ser mayor a la cantidad pendiente."]], Response::HTTP_UNPROCESSABLE_ENTITY);

    $progress = ProgressHistory::create([
      'recourse_id' => $recourse->id,
      'advanced' => $request->advanced,
      'done' => $done,
      'pending' => $pending,
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

    return $this->showOne($progress, Response::HTTP_CREATED);
  }

  //TODO EXtraer esta logica
  private function getValueFromUnitMeasureProgress(Recourse $recourse)
  {
    switch (Settings::getKeyfromId($recourse['unit_measure_progress_id'])) {
      case UnitMeasureProgressEnum::UNIT_CHAPTERS->name:
        return  $recourse->total_chapters;
      case UnitMeasureProgressEnum::UNIT_PAGES->name:
        return  $recourse->total_pages;
      case UnitMeasureProgressEnum::UNIT_HOURS->name:
        return  $recourse->total_hours;
      case UnitMeasureProgressEnum::UNIT_VIDEOS->name:
        return $recourse->total_videos;
    }
  }


  public function show(ProgressHistory $progressHistory)
  {
    //
  }

  public function update(Request $request, ProgressHistory $progressHistory)
  {
    //
  }

  public function destroy(ProgressHistory $progressHistory)
  {
    $recourse = $progressHistory->recourse;

    if ($progressHistory->comment === "REGISTRO INICIAL GENERADO AUTOMATICAMENTE POR EL SISTEMA" && $progressHistory->id === $recourse->progress->first()->id) {
      return $this->errorResponse(
        ["api_response" => ["No se puede eliminar el registro generado por el sistema"]],
        Response::HTTP_UNPROCESSABLE_ENTITY
      );
    }

    if ($progressHistory->id !== $recourse->progress->last()->id) {
      return $this->errorResponse(
        ["api_response" => ["No se puede eliminar el registro, sólo puede eliminarse el ultimo registro del recurso"]],
        Response::HTTP_UNPROCESSABLE_ENTITY
      );
    }

    $progressHistory->delete();
    return $this->showOne(new ProgressResource($progressHistory), Response::HTTP_ACCEPTED);
  }
}
