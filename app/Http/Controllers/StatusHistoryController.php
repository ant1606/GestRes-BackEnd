<?php

namespace App\Http\Controllers;

use App\Http\Resources\StatusCollection;
use App\Http\Resources\StatusResource;
use App\Http\Services\StatusHistoryService;
use App\Models\Recourse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\StatusHistory;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response;

//TODO Refactorizar anotaciones
class StatusHistoryController extends ApiController
{
  public function __construct(protected StatusHistoryService $statusService)
  {
    // $this->middleware('transform.input:' . StatusResource::class);
  }

  /**
   * Obteniendo Historial de Progreso de un Recurso
   * @OA\Get(
   *    path="/recourses/{recourse_id}/status?page=1",
   *    operationId="getStatus",
   *    tags={"Status"},
   *    summary="List History Status of recourse",
   *    description="List History Status of recourse",
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
   *        @OA\Property(property="data",type="array",
   *          @OA\Items(
   *            @OA\Property(property="identificador", type="integer", example=3),
   *            @OA\Property(property="fecha", type="string", format="date", example="2024-01-03"),
   *            @OA\Property(property="comentario", type="string", nullable=true),
   *            @OA\Property(property="estadoId", type="integer", example=5),
   *            @OA\Property(property="estadoNombre", type="string", example="EN PROCESO"),
   *            @OA\Property(property="esUltimoRegistro", type="boolean", example=true)
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
    $statusHistories = $recourse->status()->latest()->get();
    return $this->sendResponse(new StatusCollection($statusHistories), Response::HTTP_OK);
  }


  /**
   * Registrando un Progreso para un recurso
   * @OA\Post(
   *    path="/recourses/{recourse_id}/status",
   *    operationId="postStatus",
   *    tags={"Status"},
   *    summary="Save a Status for a Recourse",
   *    description="Save a Status for a Recourse",
   *    security={{"bearerAuth":{}}},
   *    @OA\RequestBody(
   *      required=true,
   *      @OA\MediaType(
   *        mediaType="application/json",
   *        @OA\Schema(
   *          required={"status_id", "date", "comment"},
   *          @OA\Property(property="status_id", type="number"),
   *          @OA\Property(property="date",type="string"),
   *          @OA\Property(property="comment",type="string"),
   *          example={"status_id":3,"date":"2024-12-31","comment":""}
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
   *          @OA\Property(property="identificador", type="integer", example=3),
   *          @OA\Property(property="fecha", type="string", format="date", example="2024-01-03"),
   *          @OA\Property(property="comentario", type="string", nullable=true),
   *          @OA\Property(property="estadoId", type="integer", example=5),
   *          @OA\Property(property="estadoNombre", type="string", example="EN PROCESO"),
   *          @OA\Property(property="esUltimoRegistro", type="boolean", example=true)
   *        )
   *      )
   *    )
   * )
   *
   * @throws Exception
   */
  public function store(Recourse $recourse, Request $request): JsonResponse
  {

    $data = $this->statusService->save_status($recourse, $request->toArray());
    return $this->sendResponse(new StatusResource($data), Response::HTTP_CREATED, false);
  }

  /**
   * Eliminando un Status de un recurso
   * @OA\Delete(
   *    path="/recourses/{recourse_id}/status/{status_id}",
   *    operationId="deleteStatus",
   *    tags={"Status"},
   *    summary="Delete a Status for a Recourse",
   *    description="Delete a Status for a Recourse",
   *    security={{"bearerAuth":{}}},
   *    @OA\Response(
   *      response=202,
   *      description="Success",
   *      @OA\JsonContent(
   *        @OA\Property(property="status", type="string", example="success"),
   *        @OA\Property(property="code", type="number", example=202),
   *        @OA\Property(property="data", type="object",
   *          @OA\Property(property="identificador", type="integer", example=3),
   *          @OA\Property(property="fecha", type="string", format="date", example="2024-01-03"),
   *          @OA\Property(property="comentario", type="string", nullable=true),
   *          @OA\Property(property="estadoId", type="integer", example=5),
   *          @OA\Property(property="estadoNombre", type="string", example="EN PROCESO"),
   *          @OA\Property(property="esUltimoRegistro", type="boolean", example=true)
   *        )
   *      )
   *    )
   * )
   *
   * @throws Exception
   */
  public function destroy(StatusHistory $statusHistory): JsonResponse
  {
    $data = $this->statusService->delete_status($statusHistory);
    return $this->sendResponse(new StatusResource($data), Response::HTTP_ACCEPTED, false);
  }
}
