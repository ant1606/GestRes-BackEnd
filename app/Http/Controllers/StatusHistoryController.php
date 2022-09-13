<?php

namespace App\Http\Controllers;

use App\Models\Recourse;
use Illuminate\Http\Request;
use App\Models\StatusHistory;
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
