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
use App\Enums\UnitMeasureProgressEnum;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\ApiController;
use App\Http\Requests\RecoursePostRequest;
use App\Http\Requests\RecourseUpdateRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
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

    $recourses = $recourses->where('user_id', auth()->user()->id);

    if ($request->has('searchTags') && $request->searchTags !== null && $request->searchTags !== []) {
      $recourses = $recourses->whereHas('tags', function ($query) use ($request) {
        $query->whereIn('tag_id', $request->searchTags);
      });
    }
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

    $recourses = $recourses->latest()->get();
    return $this->showAllResource(new RecourseCollection($recourses), Response::HTTP_OK);
  }

  public function store(RecoursePostRequest $request)
  {
    $dateHistoryCreation = Carbon::now()->toDateString();
    $commentAutogenerate = "REGISTRO INICIAL GENERADO AUTOMATICAMENTE POR EL SISTEMA";

    try {
      DB::beginTransaction();

      $request->merge(["user_id" => Auth::user()->id]);
      $recourse = Recourse::create($request->all());

      ProgressHistory::create([
        "recourse_id" => $recourse->id,
        "done" => 0,
        "advanced" => 0,
        "pending" => $this->getValueFromUnitMeasureProgress($recourse),
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
      dd($e);
      // TODO Escribir los mensajes de error en un log $e->getMessage()
      //TODO Envolver los mensajes de error en la nomenclatura usada [api_response => []]
      return $this->errorResponse(
        ["api_response" => ["Ocurrió un error al registrar el recurso, hable con el administrador"]],
        Response::HTTP_UNPROCESSABLE_ENTITY
      );
    }
  }

  //TODO EXtraer esta logica
  /**
   * Gets the total amount in base to the unit_ measure progress_id was  selected by user
   *
   * @param \Illuminate\Http\Request|\App\Models\Recourse $recourse
   *        The resource to store. It can be either an instance of \Illuminate\Http\Request
   *         or an instance of \App\Models\Recourse.
   * @return \Illuminate\Http\Response
   */
  private function getValueFromUnitMeasureProgress($recourse)
  {
    switch (Settings::getKeyfromId($recourse['unit_measure_progress_id'])) {
      case UnitMeasureProgressEnum::UNIT_CHAPTERS->name:
        return  $recourse->total_chapters;
      case UnitMeasureProgressEnum::UNIT_PAGES->name:
        return  $recourse->total_pages;
      case UnitMeasureProgressEnum::UNIT_HOURS->name:
        return  $recourse->total_hours;
      case UnitMeasureProgressEnum::UNIT_VIDEOS->name:
        return $recourse->total_videos;
    }
  }

  public function show(Recourse $recourse)
  {
    $recourse->load('status', 'progress', 'tags');
    return $this->showOne(new RecourseResource($recourse), Response::HTTP_OK);
  }

  public function update(Recourse $recourse, RecourseUpdateRequest $request)
  {
    //Obtenemos el typeId y unitMeasureProgressId del recurso y el del objeto enviado en el request
    $old_type_id = $recourse->type_id;
    $new_type_id = $request->type_id;
    $old_unit_measure_progress_id = $recourse->unit_measure_progress_id;
    $new_unit_measure_progress_id = $request->unit_measure_progress_id;


    /**
     * Si el valor total de la unidad de medida a cambiado, verificamos la diferencia existente con el total registrado
     * del recurso para añadirlo o sustraerlo a los registros de progreso existentes
     * Sólo debe realizarse si la unidad de medida no ha sido modificada
     */
    //TODO Adaptarlo para el caso de las horas
    $differenceBetweenTotals = $old_unit_measure_progress_id === $new_unit_measure_progress_id
      ? $this->getValueFromUnitMeasureProgress($request) - $this->getValueFromUnitMeasureProgress($recourse)
      : 0;
    // $differenceBetweenTotals = Settings::getKeyfromId($request->type_id) === TypeRecourseEnum::TYPE_LIBRO->name
    //   ? $request->total_pages - $recourse->total_pages
    //   : $request->total_videos - $recourse->total_videos;

    $recourse->fill($request->only([
      'name',
      'source',
      'author',
      'editorial',
      'type_id',
      'unit_measure_progress_id',
      'total_pages',
      'total_chapters',
      'total_videos',
      'total_hours',
    ]));
    $existingTags = $recourse->tags()->pluck('taggables.tag_id')->toArray();

    if ($recourse->isClean() && (isset($request->tags) ? $request->tags : []) === $existingTags) {
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

      // Si el type_id o el unit_measure_progress_id han cambiado, se resetearan los progresos existentes
      if ($new_type_id !== $old_type_id || $new_unit_measure_progress_id !== $old_unit_measure_progress_id) {
        $recourse->progress()->forceDelete();

        ProgressHistory::create([
          "recourse_id" => $recourse->id,
          "done" => 0,
          "advanced" => 0,
          "pending" => $this->getValueFromUnitMeasureProgress($recourse),
          "date" => $dateHistoryCreation,
          "comment" => $commentAutogenerate
        ]);
      }

      if ($differenceBetweenTotals !== 0) {
        foreach ($recourse->progress as $progress) {
          $progress->pending += $differenceBetweenTotals;
          $progress->pending < 0 ? $progress->delete() : $progress->save();
        }
      }

      DB::commit();
      return $this->showOne(new RecourseResource($recourse), Response::HTTP_ACCEPTED);
    } catch (\Throwable $th) {
      DB::rollBack();
      // TODO Escribir los mensajes de error en un log $e->getMessage()
      //TODO Envolver los mensajes de error en la nomenclatura usada [api_response => []]
      dd($th);
      return $this->errorResponse(
        ["api_response" => ["Ocurrió un error al actualizar el recurso, hable con el administrador"]],
        Response::HTTP_UNPROCESSABLE_ENTITY
      );
    }
  }

  public function destroy(Recourse $recourse)
  {
    //TODO Insertar autorizacion para eliminar recurso sólo al usuario que lo creo
    $recourse->status()->forceDelete();
    $recourse->progress()->forceDelete();
    $recourse->tags()->detach();
    $recourse->delete();

    return $this->showOne(new RecourseResource($recourse), Response::HTTP_ACCEPTED);
  }
}
