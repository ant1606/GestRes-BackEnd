<?php

  namespace App\Http\Controllers;

  use App\Enums\StatusRecourseEnum;
  use App\Enums\UnitMeasureProgressEnum;
  use App\Http\Requests\RecoursePostRequest;
  use App\Http\Requests\RecourseUpdateRequest;
  use App\Http\Resources\RecourseCollection;
  use App\Http\Resources\RecourseResource;
  use App\Models\ProgressHistory;
  use App\Models\Recourse;
  use App\Models\Settings;
  use App\Models\StatusHistory;
  use Carbon\Carbon;
  use Illuminate\Http\JsonResponse;
  use Illuminate\Http\Request;
  use Illuminate\Support\Facades\Auth;
  use Illuminate\Support\Facades\DB;
  use Symfony\Component\HttpFoundation\Response;


  class RecourseController extends ApiController
  {
    public function __construct()
    {
      // $this->middleware('transform.input:' . RecourseResource::class)->only(['store', 'update']);
    }

    public function index(Request $request): JsonResponse
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
        }, $request->searchEstado);
      }

      $recourses = $recourses->latest()->get();
      return $this->sendResponse(new RecourseCollection($recourses), Response::HTTP_OK);
//    return $this->showAllResource(new RecourseCollection($recourses), Response::HTTP_OK);
    }

    public function store(RecoursePostRequest $request): JsonResponse
    {
      $dateHistoryCreation = Carbon::now()->toDateString();
      $commentAutogenerate = "REGISTRO INICIAL GENERADO AUTOMATICAMENTE POR EL SISTEMA";

      try {
        DB::beginTransaction();

        $request->merge(["user_id" => Auth::user()->id]);
        $recourse = Recourse::create($request->all());

        ProgressHistory::create([
          "recourse_id" => $recourse->id,
          "done" => Settings::getKeyfromId($recourse['unit_measure_progress_id']) === UnitMeasureProgressEnum::UNIT_HOURS->name ? "00:00:00" : "0",
          "advanced" => Settings::getKeyfromId($recourse['unit_measure_progress_id']) === UnitMeasureProgressEnum::UNIT_HOURS->name ? "00:00:00" : "0",
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
        return $this->sendResponse(new RecourseResource($recourse), Response::HTTP_CREATED);
      } catch (\Exception $e) {
        DB::rollBack();
//        dd($e);
        // TODO Escribir los mensajes de error en un log $e->getMessage()
        return $this->sendError(
          Response::HTTP_UNPROCESSABLE_ENTITY,
          "Ocurrió un error al registrar el recurso, hable con el administrador"
        );
      }
    }

    /**
     * Gets the total amount in base to the unit_ measure progress_id was  selected by user
     *
     * @param \Illuminate\Http\Request|\App\Models\Recourse $recourse
     *        The resource to store. It can be either an instance of \Illuminate\Http\Request
     *         or an instance of \App\Models\Recourse.
     * @return \Illuminate\Http\Response
     */

    public function show(Recourse $recourse): JsonResponse
    {
      $recourse->load('status', 'progress', 'tags');
      return $this->sendResponse(new RecourseResource($recourse), Response::HTTP_OK);
    }

    public function update(Recourse $recourse, RecourseUpdateRequest $request): JsonResponse
    {
      //Obtenemos el typeId y unitMeasureProgressId del recurso y el del objeto enviado en el request
      $old_type_id = $recourse->type_id;
      $new_type_id = $request->type_id;
      $old_unit_measure_progress_id = $recourse->unit_measure_progress_id;
      $new_unit_measure_progress_id = $request->unit_measure_progress_id;
      $is_unit_measure_hours = Settings::getKeyfromId($request['unit_measure_progress_id']) === UnitMeasureProgressEnum::UNIT_HOURS->name;

      /**
       * Si el valor total de la unidad de medida a cambiado, verificamos la diferencia existente con el total registrado
       * del recurso para añadirlo o sustraerlo a los registros de progreso existentes
       * Sólo debe realizarse si la unidad de medida no ha sido modificada
       */
      $differenceBetweenTotals = $old_unit_measure_progress_id === $new_unit_measure_progress_id
        ? ($is_unit_measure_hours
          ? $this->processHours($request->total_hours, $recourse->total_hours)
          : $this->getValueFromUnitMeasureProgress($request) - $this->getValueFromUnitMeasureProgress($recourse)
        )
        : 0;

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
        return $this->sendError(
          Response::HTTP_UNPROCESSABLE_ENTITY,
          "Se debe especificar al menos un valor diferente para actualizar"
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

        //TODO Habilitar para Horas
        //TODO Testear esto con los multiples escenarios planteados, falta tomar en cuenta las horas negativas
        //TODO Testear los resultados cuando el recourse existente es de un tipo diferente al recourse a actualizar
        // Actualizando la cantidad pendiente en los progresos si el total de la unidad de medida ha sido modifica
        if ($differenceBetweenTotals !== 0) {
          foreach ($recourse->progress as $progress) {
            $progress->pending = $is_unit_measure_hours
              ? $this->processHours($progress->pending, $differenceBetweenTotals, false)
              : $progress->pending + $differenceBetweenTotals;
            $progress->pending < 0 ? $progress->delete() : $progress->save();
          }
        }

        DB::commit();
        return $this->sendResponse(
          new RecourseResource($recourse),
          Response::HTTP_ACCEPTED, false
        );
      } catch (\Throwable $th) {
        DB::rollBack();
        // TODO Escribir los mensajes de error en un log $e->getMessage()
//        dd($th);
        return $this->sendError(
          Response::HTTP_UNPROCESSABLE_ENTITY,
          "Ocurrió un error al actualizar el recurso, hable con el administrador"
        );
      }
    }

    public function destroy(Recourse $recourse): JsonResponse
    {
      //TODO Insertar autorizacion para eliminar recurso sólo al usuario que lo creo
      $recourse->status()->forceDelete();
      $recourse->progress()->forceDelete();
      $recourse->tags()->detach();
      $recourse->delete();

      return $this->sendResponse(new RecourseResource($recourse), Response::HTTP_ACCEPTED);
    }

    //TODO ExtraerLogica
    private function processHours($hora1, $hora2, $isSubstract = true): string
    {
      list($horas1, $minutos1, $segundos1) = explode(':', $hora1);
      list($horas2, $minutos2, $segundos2) = explode(':', $hora2);

      $totalSegundos1 = $horas1 * 3600 + $minutos1 * 60 + $segundos1;
      $totalSegundos2 = $horas2 * 3600 + $minutos2 * 60 + $segundos2;

      $totalSegundos = $isSubstract ? $totalSegundos1 - $totalSegundos2 : $totalSegundos1 + $totalSegundos2;

      $nuevasHoras = floor($totalSegundos / 3600);
      $nuevosMinutos = floor(($totalSegundos % 3600) / 60);
      $nuevosSegundos = $totalSegundos % 60;

      return sprintf('%02d:%02d:%02d', abs($nuevasHoras), abs($nuevosMinutos), abs($nuevosSegundos));
    }

    //TODO EXTRAER LOGICA
    private function getValueFromUnitMeasureProgress($recourse)
    {
      switch (Settings::getKeyfromId($recourse['unit_measure_progress_id'])) {
        case UnitMeasureProgressEnum::UNIT_CHAPTERS->name:
          return $recourse->total_chapters;
        case UnitMeasureProgressEnum::UNIT_PAGES->name:
          return $recourse->total_pages;
        case UnitMeasureProgressEnum::UNIT_HOURS->name:
          return $recourse->total_hours;
        case UnitMeasureProgressEnum::UNIT_VIDEOS->name:
          return $recourse->total_videos;
      }
    }

  }
