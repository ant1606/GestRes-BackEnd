<?php

namespace App\Http\Controllers;

use App\Enums\StatusRecourseEnum;
use App\Http\Controllers\ApiController;
use App\Http\Resources\RecourseCollection;
use App\Models\Recourse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DashboardController extends ApiController
{

  public function getTop5Recourses(Request $request)
  {
    $statusType = $request->porEmpezar
      ? StatusRecourseEnum::STATUS_POREMPEZAR->value
      :  StatusRecourseEnum::STATUS_ENPROCESO->value;
    $recourses = Recourse::all()->where('current_status_name', $statusType)->take(5);
    return $this->showAllResource(new RecourseCollection($recourses), Response::HTTP_OK);
  }
}
