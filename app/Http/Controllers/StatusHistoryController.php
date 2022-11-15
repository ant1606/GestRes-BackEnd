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
        $this->middleware('transform.input:' . StatusResource::class);
    }

    public function index(Recourse $recourse)
    {
      $statusHistories = $recourse->status;

      return $this->showAllResource(new StatusCollection($statusHistories), Response::HTTP_OK);
    }

    public function store(Recourse $recourse, Request $request)
    {
        /*TODO ver si la validacion de la fecha se puede realizar en un formRequest
        ver esta documentacion https://stackoverflow.com/questions/20953525/laravel-4-validation-afterdate-get-date-from-database
         */
        $lastStatus = $recourse->status->last();

        if ($request->date < $lastStatus->date)
            return $this->errorResponse("La fecha ingresada no es correcta", Response::HTTP_UNPROCESSABLE_ENTITY);

        $status = StatusHistory::create([
            'recourse_id' => $recourse->id,
            'status_id' => $request->status_id,
            'date' => $request->date,
            'comment' => $request->comment
        ]);
        return $this->showOne($status, Response::HTTP_CREATED);
    }

    public function show(StatusHistory $statusHistory)
    {
        //
    }

    public function update(Request $request, StatusHistory $statusHistory)
    {
        //
    }

    public function destroy(StatusHistory $statusHistory)
    {
        $recourse = $statusHistory->recourse;

        if($statusHistory->comment === "REGISTRO INICIAL GENERADO AUTOMATICAMENTE POR EL SISTEMA" && $statusHistory->id === $recourse->status->first()->id){
          return $this->errorResponse(
            "No se puede eliminar el registro generado por el sistema",
            Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if($statusHistory->id !== $recourse->status->last()->id){
            return $this->errorResponse(
                "No se puede eliminar el registro, sólo puede eliminarse el ultimo registro del recurso",
                Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $statusHistory->delete();
        return $this->showOne(new StatusResource($statusHistory), Response::HTTP_ACCEPTED);
    }
}
