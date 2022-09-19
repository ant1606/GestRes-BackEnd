<?php

namespace App\Http\Controllers;

use App\Models\Recourse;
use Illuminate\Http\Request;
use App\Models\StatusHistory;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Response;

class StatusHistoryController extends ApiController
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

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\StatusHistory  $statusHistory
     * @return \Illuminate\Http\Response
     */
    public function show(StatusHistory $statusHistory)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\StatusHistory  $statusHistory
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, StatusHistory $statusHistory)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\StatusHistory  $statusHistory
     * @return \Illuminate\Http\Response
     */
    public function destroy(StatusHistory $statusHistory)
    {
        //
    }
}
