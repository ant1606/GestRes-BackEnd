<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Recourse;


use App\Models\Settings;
use App\Models\StatusHistory;
use App\Enums\TypeRecourseEnum;
use App\Models\ProgressHistory;
use App\Enums\StatusRecourseEnum;
use App\Http\Controllers\ApiController;
use App\Http\Requests\RecoursePostRequest;
use Symfony\Component\HttpFoundation\Response;

class RecourseController extends ApiController
{
    public function store(RecoursePostRequest $request)
    {
        // dd($request->all());
        $recourse = Recourse::create($request->all());

        $dateHistoryCreation = Carbon::now();
        $commentAutogenerate = "REGISTRO INICIAL GENERADO AUTOMATICAMENTE POR EL SISTEMA";

        ProgressHistory::create([
            "Recourse_id" => $recourse->id,
            "done" => 0,
            "pending" => $recourse->total_videos ? $recourse->total_videos : $recourse->total_pages,
            "date" => $dateHistoryCreation,
            "comment" => $commentAutogenerate
        ]);

        StatusHistory::create([
            "Recourse_id" => $recourse->id,
            "status_id" => Settings::getData(StatusRecourseEnum::STATUS_REGISTRADO->name, "id"),
            "date" => $dateHistoryCreation,
            "comment" => $commentAutogenerate
        ]);

        // $recourse->tags()->attach($request->tags);

        return $this->showOne($recourse, Response::HTTP_CREATED);
    }
}
