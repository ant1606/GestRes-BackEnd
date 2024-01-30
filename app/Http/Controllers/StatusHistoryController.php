<?php

namespace App\Http\Controllers;

use App\Http\Resources\StatusCollection;
use App\Http\Resources\StatusResource;
use App\Models\Recourse;
use Illuminate\Http\Request;
use App\Models\StatusHistory;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Response;

class StatusHistoryController extends ApiController
{
  public function __construct()
  {
    // $this->middleware('transform.input:' . StatusResource::class);
  }

  public function index(Recourse $recourse)
  {
    $statusHistories = $recourse->status()->latest()->get();
    return $this->sendResponse(new StatusCollection($statusHistories), Response::HTTP_OK);
  }

  public function store(Recourse $recourse, Request $request)
  {
    /*TODO ver si la validacion de la fecha se puede realizar en un formRequest
        ver esta documentacion https://stackoverflow.com/questions/20953525/laravel-4-validation-afterdate-get-date-from-database
         */
    $lastStatus = $recourse->status->last();

    if ($request->date < $lastStatus->date)
      return $this->sendError(Response::HTTP_UNPROCESSABLE_ENTITY, "La fecha ingresada no es correcta");

    $status = StatusHistory::create([
      'recourse_id' => $recourse->id,
      'status_id' => $request->status_id,
      'date' => $request->date,
      'comment' => $request->comment
    ]);
    return $this->sendResponse(new StatusResource($status), Response::HTTP_CREATED, false);
  }

  public function destroy(StatusHistory $statusHistory)
  {
    $recourse = $statusHistory->recourse;

    if ($statusHistory->comment === "REGISTRO INICIAL GENERADO AUTOMATICAMENTE POR EL SISTEMA" && $statusHistory->id === $recourse->status->first()->id) {
      return $this->sendError(
        Response::HTTP_UNPROCESSABLE_ENTITY,
        "Acción prohibida, No esta permitido eliminar el registro generado por el sistema"
      );
    }

    if ($statusHistory->id !== $recourse->status->last()->id) {
      return $this->sendError(
        Response::HTTP_UNPROCESSABLE_ENTITY,
        "Acción prohibida, sólo puede eliminarse el último registro de estados del recurso"
      );
    }

    $statusHistory->delete();
    return $this->sendResponse(new StatusResource($statusHistory), Response::HTTP_ACCEPTED, false);
  }
}
