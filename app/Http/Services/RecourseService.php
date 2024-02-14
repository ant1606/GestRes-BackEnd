<?php

  namespace App\Http\Services;

  use App\Enums\StatusRecourseEnum;
  use App\Enums\UnitMeasureProgressEnum;
  use App\Helpers\TimeHelper;
  use App\Models\ProgressHistory;
  use App\Models\Recourse;
  use App\Models\Settings;
  use App\Models\StatusHistory;
  use Carbon\Carbon;
  use Illuminate\Database\Eloquent\Collection;
  use Illuminate\Support\Facades\Auth;
  use Illuminate\Support\Facades\DB;
  use Mockery\Exception;
  use Symfony\Component\HttpFoundation\Response;

  class RecourseService
  {

    public function get_recourses(
      array  $searchTags = [],
      string $searchNombre = null,
      int    $searchTipo = null,
      int    $searchEstado = null
    ): Collection|array
    {
      $recourses = Recourse::query();

      $recourses = $recourses->where('user_id', auth()->user()->id);

      if ($searchTags !== []) {
        $recourses = $recourses->whereHas('tags', function ($query) use ($searchTags) {
          $query->whereIn('tag_id', $searchTags);
        });
      }
      if ($searchNombre !== null)
        $recourses = $recourses->where('name', 'like', '%' . $searchNombre);

      if ($searchTipo !== null)
        $recourses = $recourses->where('type_id', '=', $searchTipo);

      if ($searchEstado !== null) {
        $recourses = $recourses->where(function ($query) {
          $query->select('status_id')
            ->from('status_histories')
            ->whereColumn('status_histories.recourse_id', 'recourses.id')
            ->orderByDesc('status_histories.id')
            ->limit(1);
        }, $searchEstado);
      }

      return $recourses->latest()->get();
    }

    public function save_recourse(array $recourseRequest): Recourse
    {
      $dateHistoryCreation = Carbon::now()->toDateString();
      $commentAutogenerate = "REGISTRO INICIAL GENERADO AUTOMATICAMENTE POR EL SISTEMA";

      try {
        DB::beginTransaction();

        $recourse = Recourse::create(array_merge($recourseRequest, ["user_id" => Auth::user()->id]));

        ProgressHistory::create([
          "recourse_id" => $recourse->id,
          "done" => Settings::getKeyfromId($recourse['unit_measure_progress_id']) === UnitMeasureProgressEnum::UNIT_HOURS->name ? "00:00:00" : "0",
          "advanced" => Settings::getKeyfromId($recourse['unit_measure_progress_id']) === UnitMeasureProgressEnum::UNIT_HOURS->name ? "00:00:00" : "0",
          "pending" => Recourse::getTotalValueFromUnitMeasureProgress($recourse),
          "date" => $dateHistoryCreation,
          "comment" => $commentAutogenerate
        ]);

        StatusHistory::create([
          "recourse_id" => $recourse->id,
          "status_id" => Settings::getData(StatusRecourseEnum::STATUS_REGISTRADO->name, "id"),
          "date" => $dateHistoryCreation,
          "comment" => $commentAutogenerate
        ]);

        if (array_key_exists("tags", $recourseRequest))
          $recourse->tags()->syncWithoutDetaching($recourseRequest["tags"]);

        DB::commit();

        return $recourse;
      } catch (\Exception $e) {
        DB::rollBack();
        // TODO Escribir los mensajes de error en un log $e->getMessage()
        throw new Exception("Ocurrió un error al registrar el recurso, hable con el administrador", Response::HTTP_UNPROCESSABLE_ENTITY);
      }
    }

    public function update_recourse(Recourse $recourse, array $recourseUpdate): Recourse
    {
      //Obtenemos el typeId y unitMeasureProgressId del recurso y el del objeto enviado en el request
      $old_type_id = $recourse->type_id;
      $new_type_id = $recourseUpdate["type_id"];
      $old_unit_measure_progress_id = $recourse->unit_measure_progress_id;
      $new_unit_measure_progress_id = $recourseUpdate["unit_measure_progress_id"];
      $is_unit_measure_hours = Settings::getKeyfromId($recourseUpdate['unit_measure_progress_id']) === UnitMeasureProgressEnum::UNIT_HOURS->name;

      /**
       * Si el valor total de la unidad de medida a cambiado, verificamos la diferencia existente con el total registrado
       * del recurso para añadirlo o sustraerlo a los registros de progreso existentes
       * Sólo debe realizarse si la unidad de medida no ha sido modificada
       */


      $differenceBetweenTotals = $old_unit_measure_progress_id === $new_unit_measure_progress_id
        ? ($is_unit_measure_hours
          ? TimeHelper::processHours($recourseUpdate["total_hours"], $recourse->total_hours)
          : Recourse::getTotalValueFromUnitMeasureProgress($recourseUpdate) - Recourse::getTotalValueFromUnitMeasureProgress($recourse)
        )
        : 0;

      $recourse->fill($recourseUpdate);
      $existingTags = $recourse->tags()->pluck('taggables.tag_id')->toArray();

      if (
        $recourse->isClean() &&
        (array_key_exists("tags", $recourseUpdate) ? $recourseUpdate["tags"] : []) === $existingTags
      ) {
        throw new Exception("Se debe especificar al menos un valor diferente para actualizar", Response::HTTP_UNPROCESSABLE_ENTITY);
      }

      $dateHistoryCreation = Carbon::now()->toDateString();
      $commentAutogenerate = "REGISTRO INICIAL GENERADO AUTOMATICAMENTE POR EL SISTEMA";

      try {
        DB::beginTransaction();

        $recourse->save();
        if (array_key_exists("tags", $recourseUpdate))
          $recourse->tags()->sync($recourseUpdate["tags"]);

        // Si el type_id o el unit_measure_progress_id han cambiado, se resetearan los progresos existentes
        if ($new_type_id !== $old_type_id || $new_unit_measure_progress_id !== $old_unit_measure_progress_id) {
          $recourse->progress()->forceDelete();

          ProgressHistory::create([
            "recourse_id" => $recourse->id,
            "done" => Settings::getKeyfromId($recourse['unit_measure_progress_id']) === UnitMeasureProgressEnum::UNIT_HOURS->name ? "00:00:00" : "0",
            "advanced" => Settings::getKeyfromId($recourse['unit_measure_progress_id']) === UnitMeasureProgressEnum::UNIT_HOURS->name ? "00:00:00" : "0",
            "pending" => Recourse::getTotalValueFromUnitMeasureProgress($recourse),
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
              ? TimeHelper::processHours($progress->pending, $differenceBetweenTotals, false)
              : $progress->pending + $differenceBetweenTotals;
            $progress->pending < 0 ? $progress->delete() : $progress->save();
          }
        }

        DB::commit();
        return $recourse;
      } catch (\Exception $e) {
        DB::rollBack();
        // TODO Escribir los mensajes de error en un log $e->getMessage()
//        dd($e);
        throw new Exception("Ocurrió un error al actualizar el recurso, hable con el administrador", Response::HTTP_UNPROCESSABLE_ENTITY);
      }
    }

    public function delete_recourse(Recourse $recourse): array
    {
      //TODO Insertar autorizacion para eliminar recurso sólo al usuario que lo creo
      $recourse->status()->forceDelete();
      $recourse->progress()->forceDelete();
      $recourse->tags()->detach();
      $recourse->delete();

      return [];
    }
  }
