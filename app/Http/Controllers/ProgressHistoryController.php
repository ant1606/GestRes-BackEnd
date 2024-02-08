<?php

  namespace App\Http\Controllers;

  use App\Http\Requests\ProgressHistoryStoreRequest;
  use App\Http\Resources\ProgressCollection;
  use App\Http\Resources\ProgressResource;
  use App\Http\Services\ProgressHistoryService;
  use App\Models\ProgressHistory;
  use App\Models\Recourse;
  use Illuminate\Http\JsonResponse;
  use OpenApi\Annotations as OA;
  use Symfony\Component\HttpFoundation\Response;

  //TODO Refactorizar anotaciones
  class ProgressHistoryController extends ApiController
  {

    public function __construct(protected ProgressHistoryService $progressService)
    {
    }

    /**
     * Obteniendo Historial de Progreso de un Recurso
     * @OA\Get(
     *    path="/recourses/{recourse_id}/progress?page=1",
     *    operationId="getProgress",
     *    tags={"Progress"},
     *    summary="List History progress of recourse",
     *    description="List History progress of recourse",
     *    security={{"bearerAuth":{}}},
     *    @OA\Parameter(
     *      name="page",
     *      in="query",
     *      required=true,
     *      description="Number of pagination",
     *      @OA\Schema(
     *        type="number"
     *      )
     *    ),
     *    @OA\Response(
     *      response=200,
     *      description="Success",
     *      @OA\JsonContent(
     *        @OA\Property(property="status", type="string", example="success"),
     *        @OA\Property(property="code", type="number", example=200),
     *        @OA\Property(property="data", type="array",
     *          @OA\Items(
     *              @OA\Property(property="identificador", type="number", example=1),
     *              @OA\Property(property="avanzadoHasta", type="string", example="0"),
     *              @OA\Property(property="realizado", type="string", example="16"),
     *              @OA\Property(property="pendiente", type="string", example="126"),
     *              @OA\Property(property="fecha", type="string", example="2024-01-03"),
     *              @OA\Property(property="comentario", type="string", example=null),
     *              @OA\Property(property="esUltimoRegistro", type="boolean", example=true),
     *              @OA\Property(property="total", type="string", example="142"),
     *          )
     *        ),
     *        @OA\Property (
     *          property="meta",
     *          type="object",
     *          @OA\Property( property="path", type="string",example="http://localhost/api/v1/tag"),
     *          @OA\Property( property="currentPage", type="number",example=1),
     *          @OA\Property( property="perPage", type="number",example=5),
     *          @OA\Property( property="totalPages", type="number",example=3),
     *          @OA\Property( property="from", type="number",example=1),
     *          @OA\Property( property="to", type="number",example=5),
     *          @OA\Property( property="total", type="number",example=15),
     *        ),
     *        @OA\Property (
     *          property="links",
     *          type="object",
     *          @OA\Property( property="self", type="string",example="http://localhost/api/v1/tag?perPage=5&page=1"),
     *          @OA\Property( property="first", type="string",example="http://localhost/api/v1/tag?perPage=5&page=1"),
     *          @OA\Property( property="last", type="string",example="http://localhost/api/v1/tag?perPage=5&page=4"),
     *          @OA\Property( property="next", type="string",example="http://localhost/api/v1/tag?perPage=5&page=2"),
     *          @OA\Property( property="prev", type="string",example=null),
     *        )
     *      )
     *    )
     * )
     */
    public function index(Recourse $recourse): JsonResponse
    {
      $progressHistories = $recourse->progress()->latest()->get();
      return $this->sendResponse(new ProgressCollection($progressHistories), Response::HTTP_OK);
    }

    /**
     * Registrando un Progreso para un recurso
     * @OA\Post(
     *    path="/recourses/{recourse_id}/progress",
     *    operationId="postProgress",
     *    tags={"Progress"},
     *    summary="Save a Progress for a Recourse",
     *    description="Save a Progress for a Recourse",
     *    security={{"bearerAuth":{}}},
     *    @OA\RequestBody(
     *      required=true,
     *      @OA\MediaType(
     *        mediaType="application/json",
     *        @OA\Schema(
     *          required={"advanced", "date", "comment"},
     *          @OA\Property(property="advanced", type="string"),
     *          @OA\Property(property="date",type="string"),
     *          @OA\Property(property="comment",type="string"),
     *          example={"advanced":"01:23:12","date":"2024-12-31","comment":""}
     *        )
     *      )
     *    ),
     *    @OA\Response(
     *      response=201,
     *      description="Success",
     *      @OA\JsonContent(
     *        @OA\Property(property="status", type="string", example="success"),
     *        @OA\Property(property="code", type="number", example=201),
     *        @OA\Property(property="data", type="object",
     *          @OA\Property(property="identificador", type="number", example=1),
     *          @OA\Property(property="avanzadoHasta", type="string", example="0"),
     *          @OA\Property(property="realizado", type="string", example="16"),
     *          @OA\Property(property="pendiente", type="string", example="126"),
     *          @OA\Property(property="fecha", type="string", example="2024-01-03"),
     *          @OA\Property(property="comentario", type="string", example=null),
     *          @OA\Property(property="esUltimoRegistro", type="boolean", example=true),
     *          @OA\Property(property="total", type="string", example="142"),
     *        )
     *      )
     *    )
     * )
     */
    public function store(Recourse $recourse, ProgressHistoryStoreRequest $request): JsonResponse
    {
      $data = $this->progressService->save_progress($recourse, $request->toArray());
      return $data->isSuccess()
        ? $this->sendResponse(new ProgressResource($data->getProgress()), Response::HTTP_CREATED, false)
        : $this->sendError(Response::HTTP_UNPROCESSABLE_ENTITY, $data->getErrorMessage());
    }

    /**
     * Eliminando un Progreso de un recurso
     * @OA\Delete(
     *    path="/recourses/{recourse_id}/progress/{progress_id}",
     *    operationId="deleteProgress",
     *    tags={"Progress"},
     *    summary="Delete a Progress for a Recourse",
     *    description="Delete a Progress for a Recourse",
     *    security={{"bearerAuth":{}}},
     *    @OA\Response(
     *      response=202,
     *      description="Success",
     *      @OA\JsonContent(
     *        @OA\Property(property="status", type="string", example="success"),
     *        @OA\Property(property="code", type="number", example=202),
     *        @OA\Property(property="data", type="object",
     *          @OA\Property(property="identificador", type="number", example=1),
     *          @OA\Property(property="avanzadoHasta", type="string", example="0"),
     *          @OA\Property(property="realizado", type="string", example="16"),
     *          @OA\Property(property="pendiente", type="string", example="126"),
     *          @OA\Property(property="fecha", type="string", example="2024-01-03"),
     *          @OA\Property(property="comentario", type="string", example=null),
     *          @OA\Property(property="esUltimoRegistro", type="boolean", example=true),
     *          @OA\Property(property="total", type="string", example="142"),
     *        )
     *      )
     *    )
     * )
     */
    public function destroy(ProgressHistory $progressHistory): JsonResponse
    {
      $data = $this->progressService->delete_progress($progressHistory);
      return $data->isSuccess()
        ? $this->sendResponse(new ProgressResource($data->getProgress()), Response::HTTP_ACCEPTED, false)
        : $this->sendError(Response::HTTP_UNPROCESSABLE_ENTITY, $data->getErrorMessage());
    }
  }
