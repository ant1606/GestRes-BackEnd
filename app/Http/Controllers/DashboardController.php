<?php

namespace App\Http\Controllers;

use App\Enums\StatusRecourseEnum;
use App\Http\Controllers\ApiController;
use App\Http\Resources\RecourseCollection;
use App\Models\Recourse;
use App\Models\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class DashboardController extends ApiController
{

  public function getTop5Recourses(Request $request)
  {
    $statusType = filter_var($request->query('porEmpezar'), FILTER_VALIDATE_BOOLEAN)
      ? StatusRecourseEnum::STATUS_POREMPEZAR->name
      :  StatusRecourseEnum::STATUS_ENPROCESO->name;


    $statusType = Settings::getData($statusType);

    // $recourses = Recourse::all()->where('current_status_name', $statusType)->take(5);
    // Subconsulta para obtener los IDs de los últimos registros de historial de estado para cada recurso
    $latestStatusHistoryIds = DB::table('status_histories as sh')
      ->select(DB::raw('MAX(sh.id) as max_id'))
      ->groupBy('sh.recourse_id');

    // Consulta principal
    $recourses = Recourse::select('recourses.id', 'recourses.name', 'sh.status_id', 'sh.date', 'sh.comment')
      ->join('status_histories as sh', 'recourses.id', '=', 'sh.recourse_id')
      ->whereIn('sh.id', $latestStatusHistoryIds)
      ->where('sh.status_id', $statusType["id"])
      ->orderBy('recourses.id')
      ->take(5)
      ->get();

    return $this->showAllResource(new RecourseCollection($recourses), Response::HTTP_OK);
  }

  public function getAmountByState()
  {
    $recoursesByStatusAmount = Recourse::where('user_id', Auth::user()->id)->get()->pluck('current_status_name')->countBy();
    $result = [
      StatusRecourseEnum::STATUS_REGISTRADO->value  => 0,
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

    return $this->showOne($result, Response::HTTP_OK);
  }

  // $recourses = Recourse::all()->pluck('current_status_name')->countBy();


}
