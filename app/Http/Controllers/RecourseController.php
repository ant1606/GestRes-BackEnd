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
        $dateHistoryCreation = Carbon::now()->toDateString();
        $commentAutogenerate = "REGISTRO INICIAL GENERADO AUTOMATICAMENTE POR EL SISTEMA";

        try {
            DB::beginTransaction();

            $recourse = Recourse::create($request->all());

            ProgressHistory::create([
                "recourse_id" => $recourse->id,
                "done" => 0,
                "pending" =>
                Settings::getKeyfromId($recourse['type_id']) === TypeRecourseEnum::TYPE_LIBRO->name ?
                    $recourse->total_pages : $recourse->total_videos,
                "date" => $dateHistoryCreation,
                "comment" => $commentAutogenerate
            ]);

            StatusHistory::create([
                "recourse_id" => $recourse->id,
                "status_id" => Settings::getData(StatusRecourseEnum::STATUS_REGISTRADO->name, "id"),
                "date" => $dateHistoryCreation,
                "comment" => $commentAutogenerate
            ]);

            $recourse->tags()->syncWithoutDetaching($request->tags);

            DB::commit();

            // dd($recourse);
            return $this->showOne($recourse, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function show($recourse)
    {
        //TODO si el recurso no tiene una relacion de Tags, falla la consulta
        //   Method Illuminate\Database\Eloquent\Collection::tags does not exist.
        // https://stackoverflow.com/questions/35054498/eager-load-relation-when-relation-doesnt-exist
        // https://laracasts.com/discuss/channels/eloquent/eager-loading-when-relation-does-not-exist
        // https://laravel.com/docs/9.x/eloquent-relationships#eager-loading
        $recourse = Recourse::findOrFail($recourse)
            ->with(['status', 'progress', 'tags'])
            ->get()
            // ->take(1)
            ->first();

        // dd($recourse);

        // dd($this->showOne($recourse, Response::HTTP_OK));
        return $this->showOne($recourse, Response::HTTP_OK);
    }
}
