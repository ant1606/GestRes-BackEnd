<?php

namespace App\Http\Controllers;

use App\Models\Recourse;
use Illuminate\Http\Request;
use App\Models\ProgressHistory;
use App\Http\Controllers\ApiController;
use App\Http\Requests\ProgressHistoryStoreRequest;
use Symfony\Component\HttpFoundation\Response;

class ProgressHistoryController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
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

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ProgressHistory  $progressHistory
     * @return \Illuminate\Http\Response
     */
    public function show(ProgressHistory $progressHistory)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ProgressHistory  $progressHistory
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ProgressHistory $progressHistory)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ProgressHistory  $progressHistory
     * @return \Illuminate\Http\Response
     */
    public function destroy(ProgressHistory $progressHistory)
    {
        //
    }
}
