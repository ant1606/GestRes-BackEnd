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
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class RecourseController extends ApiController
{
    public function store(RecoursePostRequest $request)
    {
        // dd($request->all());


        $dateHistoryCreation = Carbon::now();
        $commentAutogenerate = "REGISTRO INICIAL GENERADO AUTOMATICAMENTE POR EL SISTEMA";

        try {
            DB::beginTransaction();

            $recourse = Recourse::create($request->all());

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

            // dd($request->tags);
            $recourse->tags()->syncWithoutDetaching($request->tags);

            DB::commit();

            return $this->showOne($recourse, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
