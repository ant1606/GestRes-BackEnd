<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProgressCollection;
use App\Http\Resources\ProgressResource;
use App\Models\Recourse;
use Illuminate\Http\Request;
use App\Models\ProgressHistory;
use App\Http\Controllers\ApiController;
use App\Http\Requests\ProgressHistoryStoreRequest;
use Symfony\Component\HttpFoundation\Response;

class ProgressHistoryController extends ApiController
{

    public function __construct()
    {
        $this->middleware('transform.input:'.ProgressResource::class);
    }

    public function index(Recourse $recourse)
    {
        $progressHistories = $recourse->progress;

        return $this->showAllResource(new ProgressCollection($progressHistories), Response::HTTP_OK);
    }

    public function store(Recourse $recourse, ProgressHistoryStoreRequest $request)
    {
        $lastProgress = $recourse->progress->last();

        if ($request->date < $lastProgress->date)
            return $this->errorResponse("La fecha ingresada es menor al último registro existente.", Response::HTTP_UNPROCESSABLE_ENTITY);

        $progress = ProgressHistory::create([
            'recourse_id' => $recourse->id,
            'done' => $request->done,
            'pending' => $request->pending,
            'date' => $request->date,
            'comment' => $request->comment,
        ]);

        return $this->showOne($progress, Response::HTTP_CREATED);
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

      if($progressHistory->comment === "REGISTRO INICIAL GENERADO AUTOMATICAMENTE POR EL SISTEMA" && $progressHistory->id === $recourse->progress->first()->id){
        return $this->errorResponse(
          "No se puede eliminar el registro generado por el sistema",
          Response::HTTP_UNPROCESSABLE_ENTITY);
      }

      if($progressHistory->id !== $recourse->progress->last()->id){
        return $this->errorResponse(
          "No se puede eliminar el registro, sólo puede eliminarse el ultimo registro del recurso",
          Response::HTTP_UNPROCESSABLE_ENTITY);
      }

      $progressHistory->delete();
      return $this->showOne(new ProgressResource($progressHistory), Response::HTTP_ACCEPTED);
    }
}
