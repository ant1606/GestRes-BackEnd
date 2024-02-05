<?php

  namespace App\Http\Controllers;

  use App\Enums\StatusRecourseEnum;
  use App\Http\Resources\RecourseCollection;
  use App\Http\Services\DashboardService;
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

    public function __construct(
      protected DashboardService $dashboardService
    ){}

    /**
     * Obtener el Top 5 de Recursos con estado porEmpezar o enProceso
     *  @OA\Get(
     *    path="/dashboard/getTop5Recourses",
     *    operationId="GetTop5Recourses",
     *    tags={"Dashboard"},
     *    summary="Get Summary of 5 top Recoures by Status",
     *    description="Get Summary of 5 top Recoures by Status",
     *    security={{"bearerAuth":{}}},
     *    @OA\Parameter(
     *      name="porEmpezar",
     *      in="query",
     *      required=true,
     *      description="Boolean value indicating whether to search for resources with status 'porEmpezar' (true) or 'enProceso' (false)",
     *      @OA\Schema(
     *        type="boolean"
     *      )
     *    ),
     *    @OA\Response(
     *      response=200,
     *      description="Success",
     *      @OA\JsonContent(
     *        @OA\Property(
     *          property="status",
     *          type="string",
     *          example="success"
     *        ),
     *        @OA\Property(
     *          property="code",
     *          type="integer",
     *          example=200
     *        ),
     *        @OA\Property(
     *          property="data",
     *          type="array",
     *          @OA\Items(
     *            @OA\Property(
     *              property="id",
     *              type="integer",
     *              example=1
     *            ),
     *            @OA\Property(
     *              property="name",
     *              type="string",
     *              example="Mi recurso de prueba"
     *            )
     *          )
     *        )
     *      )
     *    )
     *  )
     */
    public function getTop5Recourses(Request $request): JsonResponse
    {
      $data = $this->dashboardService->getTop5Recourses(filter_var($request->query('porEmpezar'), FILTER_VALIDATE_BOOLEAN));
      return $this->sendResponse($data, Response::HTTP_OK, false);
    }

    /**
     * Obtener Cantidad Total de Recursos por Estado
     * @OA\Get(
     *    path="/dashboard/getAmountByState",
     *    operationId="GetAmountByState",
     *    tags={"Dashboard"},
     *    summary="Get summary of amount of recourse by state",
     *    description="Get summary of amount of recourse by state",
     *    security={{ "bearerAuth": {} }},
     *    @OA\Response(
     *      response=200,
     *      description="Success",
     *      @OA\JsonContent(
     *        @OA\Property(property="status", type="string", example="success"),
     *        @OA\Property(property="code", type="number", example=200),
     *        @OA\Property(property="data", type="object",
     *          @OA\Property(property="REGISTRADO", type="number", example=5),
     *          @OA\Property(property="POR EMPEZAR", type="number", example=2),
     *          @OA\Property(property="EN PROCESO", type="number", example=3),
     *          @OA\Property(property="CULMINADO", type="number", example=3),
     *          @OA\Property(property="DESCARTADO", type="number", example=4),
     *          @OA\Property(property="DESFASADO", type="number", example=2),
     *        ),
     *      )
     *    )
     * )
     */
    public function getAmountByState(): JsonResponse
    {
      $data = $this->dashboardService->getAmountByState();
      return $this->sendResponse($data, Response::HTTP_OK, false);
    }
  }
