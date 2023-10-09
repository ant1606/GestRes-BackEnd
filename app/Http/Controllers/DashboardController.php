<?php

namespace App\Http\Controllers;

use App\Enums\StatusRecourseEnum;
use App\Http\Controllers\ApiController;
use App\Http\Resources\RecourseCollection;
use App\Models\Recourse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class DashboardController extends ApiController
{

  public function getTop5Recourses(Request $request)
  {
    $statusType = filter_var($request->query('porEmpezar'), FILTER_VALIDATE_BOOLEAN)
      ? StatusRecourseEnum::STATUS_POREMPEZAR->value
      :  StatusRecourseEnum::STATUS_ENPROCESO->value;
    $recourses = Recourse::all()->where('current_status_name', $statusType)->take(5);
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
