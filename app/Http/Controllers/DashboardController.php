<?php

  namespace App\Http\Controllers;

  use App\Enums\StatusRecourseEnum;
  use App\Http\Resources\RecourseCollection;
  use App\Models\Recourse;
  use App\Models\Settings;
  use Illuminate\Http\JsonResponse;
  use Illuminate\Http\Request;
  use Illuminate\Support\Arr;
  use Illuminate\Support\Facades\Auth;
  use Illuminate\Support\Facades\DB;
  use OpenApi\Annotations as OA;
  use Symfony\Component\HttpFoundation\Response;

  class DashboardController extends ApiController
  {

    /**
     * Login de usuario
     * @OA\Get(
     *    path="/dashboard/getTop5Recourses",
     *    operationId="GetTop5Recourses",
     *    tags={"Dashboard"},
     *    summary="Get Summary of 5 top Recoures by Status",
     *    description="Get Summary of 5 top Recoures by Status",
     *    security={{ "bearerAuth": {} }},
     *    @OA\Parameter(
     *        name="porEmpezar",
     *        in="query",
     *        required=true,
     *        description="Valor booleano que indica si se buscará recursos con estado porEmpezar (true) o enProceso (false)",
     *        @OA\Schema(
     *            type="boolean"
     *        )
     *    ),
     *    @OA\Response(
     *      response=200,
     *      description="Success",
     *      @OA\JsonContent(
     *        @OA\Property(property="status", type="string", example="success"),
     *        @OA\Property(property="code", type="number", example=200),
     *        @OA\Property(
     *          property="data",
     *          type="object",
     *          @OA\Property(property="bearer_token", type="string", example="41|ylunYCnOo71w2xzckQEnnXLq4m1Qc6HG5JqbJmkZgd4b82742"),
     *          @OA\Property(property="bearer_expire", type="string", example="Wed, 31 Jan 2024 23:16:03 GMT"),
     *          @OA\Property(
     *            property="user",
     *            type="object",
     *            @OA\Property(property="id", type="number", example=1),
     *            @OA\Property(property="name", type="string", example="Dummy User"),
     *            @OA\Property(property="email", type="string", example="dummye@email.com"),
     *            @OA\Property(property="remember_token", type="string", example=null),
     *            @OA\Property(property="is_verified", type="boolean", example=true)
     *          ),
     *        ),
     *      )
     *    ),
     *    @OA\Response(
     *      response="401",
     *      description="User unauthenticated",
     *      @OA\JsonContent(
     *        @OA\Property(property="status", type="string", example="error"),
     *        @OA\Property(property="code", type="number", example=401),
     *        @OA\Property(
     *          property="error",
     *          type="object",
     *          @OA\Property(property="message", type="string", example="Usuario no autentificado"),
     *          @OA\Property(property="details", type="object", example="[]")
     *        ),
     *      )
     *    ),
     * )
     */
    public function getTop5Recourses(Request $request): JsonResponse
    {

      $statusType = filter_var($request->query('porEmpezar'), FILTER_VALIDATE_BOOLEAN)
        ? StatusRecourseEnum::STATUS_POREMPEZAR->name
        : StatusRecourseEnum::STATUS_ENPROCESO->name;

      $statusType = Settings::getData($statusType);

      // $recourses = Recourse::all()->where('current_status_name', $statusType)->take(5);
      // Subconsulta para obtener los IDs de los últimos registros de historial de estado para cada recurso
      $latestStatusHistoryIds = DB::table('status_histories as sh')
        ->select(DB::raw('MAX(sh.id) as max_id'))
        ->groupBy('sh.recourse_id')
        ->get();

//      ->select('recourses.id', 'recourses.name', 'recourses.unit_measure_progress_id', 'sh.status_id', 'sh.date', 'sh.comment')
      $recourses = DB::table('recourses')
        ->select('recourses.id', 'recourses.name')
        ->join('status_histories as sh', 'recourses.id', '=', 'sh.recourse_id')
        ->whereIn('sh.id', $latestStatusHistoryIds->pluck('max_id')->toArray())
        ->where('sh.status_id', $statusType["id"])
        ->orderBy('recourses.id')
        ->take(5)
        ->get();

//      $recourses = Recourse::with(['status' => function ($query) use ($latestStatusHistoryIds) {
//        $query->whereIn('id', $latestStatusHistoryIds->pluck('max_id')->toArray());
//      }])
//        ->whereHas('status', function ($query) use ($statusType) {
//          $query->where('status_id', $statusType["id"]);
//        })
//        ->orderBy('id')
//        ->take(5)
//        ->get();

      return $this->sendResponse($recourses->toArray(), Response::HTTP_OK, false);
//      return $this->sendResponse(new RecourseCollection($recourses), Response::HTTP_OK, false);
    }

    public function getAmountByState(): JsonResponse
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

      return $this->sendResponse($result, Response::HTTP_OK, false);
    }
  }
