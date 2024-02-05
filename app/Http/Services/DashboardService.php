<?php

  namespace App\Http\Services;

  use App\Enums\StatusRecourseEnum;
  use App\Models\Recourse;
  use App\Models\Settings;
  use Illuminate\Support\Arr;
  use Illuminate\Support\Facades\Auth;
  use Illuminate\Support\Facades\DB;

  class DashboardService
  {
    /**
     * @param bool $porEmpezar Indica si se filtrará los recursos por el estado porEmpezar
     * @return array
     */
    public function getTop5Recourses(bool $porEmpezar): array
    {
      $statusType = $porEmpezar
        ? StatusRecourseEnum::STATUS_POREMPEZAR->name
        : StatusRecourseEnum::STATUS_ENPROCESO->name;

      $statusType = Settings::getData($statusType);

      // $recourses = Recourse::all()->where('current_status_name', $statusType)->take(5);
      // Subconsulta para obtener los ID de los últimos registros de historial de estado para cada recurso
      $latestStatusHistoryIds = DB::table('status_histories as sh')
        ->select(DB::raw('MAX(sh.id) as max_id'))
        ->groupBy('sh.recourse_id')
        ->get();

      $recourses = DB::table('recourses')
        ->select('recourses.id', 'recourses.name')
        ->join('status_histories as sh', 'recourses.id', '=', 'sh.recourse_id')
        ->whereIn('sh.id', $latestStatusHistoryIds->pluck('max_id')->toArray())
        ->where('sh.status_id', $statusType["id"])
        ->orderBy('recourses.id')
        ->take(5)
        ->get();

      return $recourses->toArray();
    }

    /**
     * Retorna un resumen de la cantidad de recursos por cada estado
     * @return array
     */
    public function getAmountByState(): array
    {
      $recoursesByStatusAmount = Recourse::where('user_id', Auth::user()->id)->get()->pluck('current_status_name')->countBy();
      $result = [
        StatusRecourseEnum::STATUS_REGISTRADO->value => 0,
        StatusRecourseEnum::STATUS_POREMPEZAR->value => 0,
        StatusRecourseEnum::STATUS_ENPROCESO->value => 0,
        StatusRecourseEnum::STATUS_CULMINADO->value => 0,
        StatusRecourseEnum::STATUS_DESCARTADO->value => 0,
        StatusRecourseEnum::STATUS_DESFASADO->value => 0,
      ];

      foreach ($result as $key => $value) {
        if (Arr::exists($recoursesByStatusAmount, $key)) {
          $result[$key] = $recoursesByStatusAmount[$key];
        }
      }
      return $result;
    }
  }
