<?php

namespace App\Http\Controllers;

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

    public function index()
    {
        //
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
        //
    }
}
