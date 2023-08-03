<?php

namespace App\Http\Controllers;

use App\Http\Resources\RecourseCollection;
use App\Http\Resources\RecourseResource;
use Carbon\Carbon;
use App\Models\Recourse;


use App\Models\Settings;
use Illuminate\Http\Request;
use App\Models\StatusHistory;
use App\Enums\TypeRecourseEnum;
use App\Models\ProgressHistory;
use App\Enums\StatusRecourseEnum;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\ApiController;
use App\Http\Requests\RecoursePostRequest;
use App\Http\Requests\RecourseUpdateRequest;
use Symfony\Component\HttpFoundation\Response;

class RecourseController extends ApiController
{
  public function __construct()
  {
    // $this->middleware('transform.input:' . RecourseResource::class)->only(['store', 'update']);
  }

  public function index(Request $request)
  {

    $recourses = Recourse::query();

    //      dd($recourses->get());
    if ($request->has('searchNombre') && $request->searchNombre !== null)
      $recourses = $recourses->where('name', 'like', '%' . $request->searchNombre . '%');

    if ($request->has('searchTipo') && $request->searchTipo !== null)
      $recourses = $recourses->where('type_id', '=', $request->searchTipo);

    if ($request->has('searchEstado') && $request->searchEstado !== null) {
      $recourses = $recourses->where(function ($query) {
        $query->select('status_id')
          ->from('status_histories')
          ->whereColumn('status_histories.recourse_id', 'recourses.id')
          ->orderByDesc('status_histories.id')
          ->limit(1);
      },  $request->searchEstado);
    }

    //      $recourses = $recourses->where('type_id', '=', $request->searchTipo);

    $recourses = $recourses->latest()->get();
    return $this->showAllResource(new RecourseCollection($recourses), Response::HTTP_OK);
  }

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
      return $this->showOne(new RecourseResource($recourse), Response::HTTP_CREATED);
    } catch (\Exception $e) {
      DB::rollBack();
      // TODO Escribir los mensajes de error en un log $e->getMessage()
      //TODO Envolver los mensajes de error en la nomenclatura usada [api_response => []]
      return $this->errorResponse(
        ["api_response" => ["Ocurrió un error al registrar el recurso, hable con el administrador"]],
        Response::HTTP_UNPROCESSABLE_ENTITY
      );
    }
  }

  public function show(Recourse $recourse)
  {
    $recourse->load('status', 'progress', 'tags');
    return $this->showOne(new RecourseResource($recourse), Response::HTTP_OK);
  }

  public function update(Recourse $recourse, RecourseUpdateRequest $request)
  {
    $old_type_id = $recourse->type_id;
    $new_type_id = $request->type_id;

    $recourse->fill($request->only([
      'name',
      'source',
      'author',
      'editorial',
      'type_id',
      'total_pages',
      'total_chapters',
      'total_videos',
      'total_hours',
    ]));
    $existingTags = $recourse->tags()->pluck('taggables.tag_id')->toArray();

    if ($recourse->isClean() && $request->tags === $existingTags) {
      return $this->errorResponse(
        ["api_response" => ["Se debe especificar al menos un valor diferente para actualizar"]],
        Response::HTTP_UNPROCESSABLE_ENTITY
      );
    }

    $dateHistoryCreation = Carbon::now()->toDateString();
    $commentAutogenerate = "REGISTRO INICIAL GENERADO AUTOMATICAMENTE POR EL SISTEMA";

    try {
      DB::beginTransaction();

      $recourse->save();
      $recourse->tags()->sync($request->tags);

      if ($new_type_id !== $old_type_id) {

        $recourse->status()->forceDelete();
        $recourse->progress()->forceDelete();

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
      }
      DB::commit();
      return $this->showOne(new RecourseResource($recourse), Response::HTTP_ACCEPTED);
    } catch (\Throwable $th) {
      DB::rollBack();
      // TODO Escribir los mensajes de error en un log $e->getMessage()
      //TODO Envolver los mensajes de error en la nomenclatura usada [api_response => []]
      return $this->errorResponse(
        ["api_response" => ["Ocurrió un error al actualizar el recurso, hable con el administrador"]],
        Response::HTTP_UNPROCESSABLE_ENTITY
      );
    }
  }

  public function destroy(Recourse $recourse)
  {
    $recourse->status()->forceDelete();
    $recourse->progress()->forceDelete();
    $recourse->tags()->detach();
    $recourse->delete();


    return $this->showOne(new RecourseResource($recourse), Response::HTTP_ACCEPTED);
  }
}
