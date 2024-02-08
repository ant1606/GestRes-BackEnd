<?php

  namespace App\Http\Controllers;

  use App\Enums\StatusRecourseEnum;
  use App\Enums\UnitMeasureProgressEnum;
  use App\Http\Requests\RecoursePostRequest;
  use App\Http\Requests\RecourseUpdateRequest;
  use App\Http\Resources\RecourseCollection;
  use App\Http\Resources\RecourseResource;
  use App\Http\Services\RecourseService;
  use App\Models\ProgressHistory;
  use App\Models\Recourse;
  use App\Models\Settings;
  use App\Models\StatusHistory;
  use Carbon\Carbon;
  use Illuminate\Http\JsonResponse;
  use Illuminate\Http\Request;
  use Illuminate\Support\Facades\Auth;
  use Illuminate\Support\Facades\DB;
  use OpenApi\Annotations as OA;
  use Symfony\Component\HttpFoundation\Response;

  //TODO Refactorizar anotaciones, analizar en el caso de delete

  class RecourseController extends ApiController
  {
    public function __construct(protected RecourseService $recourseService)
    {
      // $this->middleware('transform.input:' . RecourseResource::class)->only(['store', 'update']);
    }

    /**
     * Obteniendo Listado de Recursos
     * @OA\Get(
     *    path="/recourses?searchTags=[]&searchNombre=''&searchTipo=0&searchEstado=""&page=1&perPage=5",
     *    operationId="getRecourses",
     *    tags={"Recourse"},
     *    summary="List Recourses",
     *    description="List Recourses",
     *    security={{"bearerAuth":{}}},
     *    @OA\Parameter(name="searchTags",in="query",required=false,description="Array of Tag's id",
     *      @OA\Schema(type="array", @OA\Items(type="number"))
     *    ),
     *    @OA\Parameter(name="searchNombre",in="query",required=false,description="Name to filter recourses",
     *      @OA\Schema(type="string")
     *    ),
     *    @OA\Parameter(name="searchTipo",in="query",required=false,description="Filter recourses by Id of Type recourse",
     *      @OA\Schema(type="number")
     *    ),
     *    @OA\Parameter(name="searchEstado",in="query",required=false,description="Filter recourses by Id of Status",
     *      @OA\Schema(type="number")
     *    ),
     *    @OA\Parameter(name="page",in="query",required=false,description="Number of page of pagination",
     *      @OA\Schema(type="number")
     *    ),
     *    @OA\Parameter(name="perPage",in="query",required=false,description="Amount of register for page",
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
     *            @OA\Property(property="identificador", type="integer", example=67),
     *            @OA\Property(property="nombre", type="string", example="Recurso de prueba de horas"),
     *            @OA\Property(property="ruta", type="string", example="AHSBDLXKJDAS"),
     *            @OA\Property(property="autor", type="string", nullable=true),
     *            @OA\Property(property="editorial", type="string", nullable=true),
     *            @OA\Property(property="tipoId", type="integer", example=2),
     *            @OA\Property(property="unidadMedidadProgresoId", type="integer", example=10),
     *            @OA\Property(property="tipoNombre", type="string", example="VIDEO TUTORIAL"),
     *            @OA\Property(property="nombreEstadoActual", type="string", example="CULMINADO"),
     *            @OA\Property(property="totalPaginas", type="integer", nullable=true),
     *            @OA\Property(property="totalCapitulos", type="integer", nullable=true),
     *            @OA\Property(property="totalVideos", type="integer", nullable=true),
     *            @OA\Property(property="totalHoras", type="string", nullable=true),
     *            @OA\Property(property="totalProgresoPorcentaje", type="number", example=100),
     *            @OA\Property(property="status", type="object",
     *              @OA\Property(property="identificador", type="integer", example=68),
     *              @OA\Property(property="fecha", type="string", format="date", example="2024-01-22"),
     *              @OA\Property(property="comentario", type="string", nullable=true),
     *              @OA\Property(property="estadoId", type="integer", example=6),
     *              @OA\Property(property="estadoNombre", type="string", example="CULMINADO"),
     *              @OA\Property(property="esUltimoRegistro", type="boolean", example=true)
     *            ),
     *            @OA\Property(property="tags", type="array",
     *              @OA\Items(
     *                @OA\Property(property="identificador", type="integer", example=1),
     *                @OA\Property(property="nombre", type="string", example="PHP"),
     *                @OA\Property(property="estilos", type="string", example="bg-teal-500 text-white"),
     *                @OA\Property(property="total", type="integer", example=0)
     *              )
     *            ),
     *            @OA\Property(property="progress", type="object",
     *              @OA\Property(property="identificador", type="integer", example=79),
     *              @OA\Property(property="avanzadoHasta", type="string", example="01:20:00"),
     *              @OA\Property(property="realizado", type="string", example="00:39:10"),
     *              @OA\Property(property="pendiente", type="string", example="00:00:00"),
     *              @OA\Property(property="fecha", type="string", format="date", example="2024-01-22"),
     *              @OA\Property(property="comentario", type="string", nullable=true),
     *              @OA\Property(property="esUltimoRegistro", type="boolean", example=true),
     *              @OA\Property(property="total", type="string", example="01:20:00")
     *            )
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
    public function index(Request $request): JsonResponse
    {
      $data = $this->recourseService->get_recourses(
        $request->input('searchTags', []),
        $request->input('searchNombre'),
        $request->input('searchTipo'),
        $request->input('searchEstado'),
      );
      return $this->sendResponse(new RecourseCollection($data), Response::HTTP_OK);
    }


    /**
     * Registrando un Recourse
     * @OA\Post(
     *    path="/recourses",
     *    operationId="postRecourse",
     *    tags={"Recourse"},
     *    summary="Save a Recourse",
     *    description="Save a Recourse",
     *    security={{"bearerAuth":{}}},
     *    @OA\RequestBody(
     *      required=true,
     *      @OA\MediaType(
     *        mediaType="application/json",
     *        @OA\Schema(
     *          required={"name", "source", "total_chapters", "total_pages,", "total_hours", "total_videos", "type_id", "unit_measure_progress_id"},
     *          @OA\Property(property="author", type="string", example=""),
     *          @OA\Property(property="editorial", type="string", example=""),
     *          @OA\Property(property="name", type="string", example="Nueva prueba para horas 23213"),
     *          @OA\Property(property="recourse_id", type="integer", example=0),
     *          @OA\Property(property="source", type="string", example="HTTPS://WWW.YOUTUBE.COM/C/KSI23ZKXO2312"),
     *          @OA\Property(property="tags", type="array", @OA\Items( type="number"), example="[]"),
     *          @OA\Property(property="total_chapters", type="integer", nullable=true),
     *          @OA\Property(property="total_hours", type="string", example="20:10:14"),
     *          @OA\Property(property="total_pages", type="integer", nullable=true),
     *          @OA\Property(property="total_videos", type="integer", example=48),
     *          @OA\Property(property="type_id", type="integer", example=2),
     *          @OA\Property(property="unit_measure_progress_id", type="integer", example=10),
     *          example={
     *            "author": "",
     *            "editorial": "",
     *            "name": "Nueva prueba para horas 23213",
     *            "recourse_id": 0,
     *            "source": "HTTPS://WWW.YOUTUBE.COM/C/KSI23ZKXO2312",
     *            "tags": "[]",
     *            "total_chapters": null,
     *            "total_hours": "20:10:14",
     *            "total_pages": null,
     *            "total_videos": 48,
     *            "type_id": 2,
     *            "unit_measure_progress_id": 10
     *          }
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
     *          @OA\Property(property="identificador", type="integer", example=67),
     *          @OA\Property(property="nombre", type="string", example="Recurso de prueba de horas"),
     *          @OA\Property(property="ruta", type="string", example="AHSBDLXKJDAS"),
     *          @OA\Property(property="autor", type="string", nullable=true),
     *          @OA\Property(property="editorial", type="string", nullable=true),
     *          @OA\Property(property="tipoId", type="integer", example=2),
     *          @OA\Property(property="unidadMedidadProgresoId", type="integer", example=10),
     *          @OA\Property(property="tipoNombre", type="string", example="VIDEO TUTORIAL"),
     *          @OA\Property(property="nombreEstadoActual", type="string", example="CULMINADO"),
     *          @OA\Property(property="totalPaginas", type="integer", nullable=true),
     *          @OA\Property(property="totalCapitulos", type="integer", nullable=true),
     *          @OA\Property(property="totalVideos", type="integer", nullable=true),
     *          @OA\Property(property="totalHoras", type="string", nullable=true),
     *          @OA\Property(property="totalProgresoPorcentaje", type="number", example=100),
     *          @OA\Property(property="status", type="object",
     *            @OA\Property(property="identificador", type="integer", example=68),
     *            @OA\Property(property="fecha", type="string", format="date", example="2024-01-22"),
     *            @OA\Property(property="comentario", type="string", nullable=true),
     *            @OA\Property(property="estadoId", type="integer", example=6),
     *            @OA\Property(property="estadoNombre", type="string", example="CULMINADO"),
     *            @OA\Property(property="esUltimoRegistro", type="boolean", example=true)
     *          ),
     *          @OA\Property(property="tags", type="array",
     *            @OA\Items(
     *              @OA\Property(property="identificador", type="integer", example=1),
     *              @OA\Property(property="nombre", type="string", example="PHP"),
     *              @OA\Property(property="estilos", type="string", example="bg-teal-500 text-white"),
     *              @OA\Property(property="total", type="integer", example=0)
     *            )
     *          ),
     *          @OA\Property(property="progress", type="object",
     *            @OA\Property(property="identificador", type="integer", example=79),
     *            @OA\Property(property="avanzadoHasta", type="string", example="01:20:00"),
     *            @OA\Property(property="realizado", type="string", example="00:39:10"),
     *            @OA\Property(property="pendiente", type="string", example="00:00:00"),
     *            @OA\Property(property="fecha", type="string", format="date", example="2024-01-22"),
     *            @OA\Property(property="comentario", type="string", nullable=true),
     *            @OA\Property(property="esUltimoRegistro", type="boolean", example=true),
     *            @OA\Property(property="total", type="string", example="01:20:00")
     *          )
     *        ),
     *      )
     *    )
     * )
     */
    public function store(RecoursePostRequest $request): JsonResponse
    {
      $data = $this->recourseService->save_recourse($request->toArray());
      return $this->sendResponse(new RecourseResource($data), Response::HTTP_CREATED);
    }

    /**
     * Obteniendo Información detallada de un Recurso
     * @OA\Get(
     *    path="/recourses/{recourse_id",
     *    operationId="getRecourse",
     *    tags={"Recourse"},
     *    summary="Get Data of one Recourse",
     *    description="Get Data of one Recourse",
     *    security={{"bearerAuth":{}}},
     *    @OA\Response(
     *      response=200,
     *      description="Success",
     *      @OA\JsonContent(
     *        @OA\Property(property="status", type="string", example="success"),
     *        @OA\Property(property="code", type="number", example=200),
     *        @OA\Property(property="data", type="array",
     *          @OA\Items(
     *            @OA\Property(property="identificador", type="integer", example=67),
     *            @OA\Property(property="nombre", type="string", example="Recurso de prueba de horas"),
     *            @OA\Property(property="ruta", type="string", example="AHSBDLXKJDAS"),
     *            @OA\Property(property="autor", type="string", nullable=true),
     *            @OA\Property(property="editorial", type="string", nullable=true),
     *            @OA\Property(property="tipoId", type="integer", example=2),
     *            @OA\Property(property="unidadMedidadProgresoId", type="integer", example=10),
     *            @OA\Property(property="tipoNombre", type="string", example="VIDEO TUTORIAL"),
     *            @OA\Property(property="nombreEstadoActual", type="string", example="CULMINADO"),
     *            @OA\Property(property="totalPaginas", type="integer", nullable=true),
     *            @OA\Property(property="totalCapitulos", type="integer", nullable=true),
     *            @OA\Property(property="totalVideos", type="integer", nullable=true),
     *            @OA\Property(property="totalHoras", type="string", nullable=true),
     *            @OA\Property(property="totalProgresoPorcentaje", type="number", example=100),
     *            @OA\Property(property="status", type="object",
     *              @OA\Property(property="identificador", type="integer", example=68),
     *              @OA\Property(property="fecha", type="string", format="date", example="2024-01-22"),
     *              @OA\Property(property="comentario", type="string", nullable=true),
     *              @OA\Property(property="estadoId", type="integer", example=6),
     *              @OA\Property(property="estadoNombre", type="string", example="CULMINADO"),
     *              @OA\Property(property="esUltimoRegistro", type="boolean", example=true)
     *            ),
     *            @OA\Property(property="tags", type="array",
     *              @OA\Items(
     *                @OA\Property(property="identificador", type="integer", example=1),
     *                @OA\Property(property="nombre", type="string", example="PHP"),
     *                @OA\Property(property="estilos", type="string", example="bg-teal-500 text-white"),
     *                @OA\Property(property="total", type="integer", example=0)
     *              )
     *            ),
     *            @OA\Property(property="progress", type="object",
     *              @OA\Property(property="identificador", type="integer", example=79),
     *              @OA\Property(property="avanzadoHasta", type="string", example="01:20:00"),
     *              @OA\Property(property="realizado", type="string", example="00:39:10"),
     *              @OA\Property(property="pendiente", type="string", example="00:00:00"),
     *              @OA\Property(property="fecha", type="string", format="date", example="2024-01-22"),
     *              @OA\Property(property="comentario", type="string", nullable=true),
     *              @OA\Property(property="esUltimoRegistro", type="boolean", example=true),
     *              @OA\Property(property="total", type="string", example="01:20:00")
     *            )
     *          )
     *        )
     *      )
     *    )
     * )
     */
    public function show(Recourse $recourse): JsonResponse
    {
      $recourse->load('status', 'progress', 'tags');
      return $this->sendResponse(new RecourseResource($recourse), Response::HTTP_OK);
    }

    /**
     * Registrando un Recourse
     * @OA\Put(
     *    path="/recourses/{recourse_id}",
     *    operationId="putRecourse",
     *    tags={"Recourse"},
     *    summary="Update a Recourse",
     *    description="Update a Recourse",
     *    security={{"bearerAuth":{}}},
     *    @OA\RequestBody(
     *      required=true,
     *      @OA\MediaType(
     *        mediaType="application/json",
     *        @OA\Schema(
     *          required={"name", "source", "total_chapters", "total_pages,", "total_hours", "total_videos", "type_id", "unit_measure_progress_id"},
     *          @OA\Property(property="author", type="string", example=""),
     *          @OA\Property(property="editorial", type="string", example=""),
     *          @OA\Property(property="name", type="string", example="Nueva prueba para horas 23213"),
     *          @OA\Property(property="recourse_id", type="integer", example=0),
     *          @OA\Property(property="source", type="string", example="HTTPS://WWW.YOUTUBE.COM/C/KSI23ZKXO2312"),
     *          @OA\Property(property="tags", type="array", @OA\Items( type="number"), example="[]"),
     *          @OA\Property(property="total_chapters", type="integer", nullable=true),
     *          @OA\Property(property="total_hours", type="string", example="20:10:14"),
     *          @OA\Property(property="total_pages", type="integer", nullable=true),
     *          @OA\Property(property="total_videos", type="integer", example=48),
     *          @OA\Property(property="type_id", type="integer", example=2),
     *          @OA\Property(property="unit_measure_progress_id", type="integer", example=10),
     *          example={
     *            "author": "",
     *            "editorial": "",
     *            "name": "Nueva prueba para horas 23213",
     *            "recourse_id": 0,
     *            "source": "HTTPS://WWW.YOUTUBE.COM/C/KSI23ZKXO2312",
     *            "tags": "[]",
     *            "total_chapters": null,
     *            "total_hours": "20:10:14",
     *            "total_pages": null,
     *            "total_videos": 48,
     *            "type_id": 2,
     *            "unit_measure_progress_id": 10
     *          }
     *        )
     *      )
     *    ),
     *    @OA\Response(
     *      response=202,
     *      description="Success",
     *      @OA\JsonContent(
     *        @OA\Property(property="status", type="string", example="success"),
     *        @OA\Property(property="code", type="number", example=201),
     *        @OA\Property(property="data", type="object",
     *          @OA\Property(property="identificador", type="integer", example=67),
     *          @OA\Property(property="nombre", type="string", example="Recurso de prueba de horas"),
     *          @OA\Property(property="ruta", type="string", example="AHSBDLXKJDAS"),
     *          @OA\Property(property="autor", type="string", nullable=true),
     *          @OA\Property(property="editorial", type="string", nullable=true),
     *          @OA\Property(property="tipoId", type="integer", example=2),
     *          @OA\Property(property="unidadMedidadProgresoId", type="integer", example=10),
     *          @OA\Property(property="tipoNombre", type="string", example="VIDEO TUTORIAL"),
     *          @OA\Property(property="nombreEstadoActual", type="string", example="CULMINADO"),
     *          @OA\Property(property="totalPaginas", type="integer", nullable=true),
     *          @OA\Property(property="totalCapitulos", type="integer", nullable=true),
     *          @OA\Property(property="totalVideos", type="integer", nullable=true),
     *          @OA\Property(property="totalHoras", type="string", nullable=true),
     *          @OA\Property(property="totalProgresoPorcentaje", type="number", example=100),
     *          @OA\Property(property="status", type="object",
     *            @OA\Property(property="identificador", type="integer", example=68),
     *            @OA\Property(property="fecha", type="string", format="date", example="2024-01-22"),
     *            @OA\Property(property="comentario", type="string", nullable=true),
     *            @OA\Property(property="estadoId", type="integer", example=6),
     *            @OA\Property(property="estadoNombre", type="string", example="CULMINADO"),
     *            @OA\Property(property="esUltimoRegistro", type="boolean", example=true)
     *          ),
     *          @OA\Property(property="tags", type="array",
     *            @OA\Items(
     *              @OA\Property(property="identificador", type="integer", example=1),
     *              @OA\Property(property="nombre", type="string", example="PHP"),
     *              @OA\Property(property="estilos", type="string", example="bg-teal-500 text-white"),
     *              @OA\Property(property="total", type="integer", example=0)
     *            )
     *          ),
     *          @OA\Property(property="progress", type="object",
     *            @OA\Property(property="identificador", type="integer", example=79),
     *            @OA\Property(property="avanzadoHasta", type="string", example="01:20:00"),
     *            @OA\Property(property="realizado", type="string", example="00:39:10"),
     *            @OA\Property(property="pendiente", type="string", example="00:00:00"),
     *            @OA\Property(property="fecha", type="string", format="date", example="2024-01-22"),
     *            @OA\Property(property="comentario", type="string", nullable=true),
     *            @OA\Property(property="esUltimoRegistro", type="boolean", example=true),
     *            @OA\Property(property="total", type="string", example="01:20:00")
     *          )
     *        ),
     *      )
     *    )
     * )
     */
    public function update(Recourse $recourse, RecourseUpdateRequest $request): JsonResponse
    {
      $data = $this->recourseService->update_recourse($recourse, $request->toArray());
      return $this->sendResponse(new RecourseResource($data),Response::HTTP_ACCEPTED, false);
    }

    /**
     * Eliminando un Recurso
     * @OA\Delete(
     *    path="/recourses/{recourse_id}",
     *    operationId="deleteRecourse",
     *    tags={"Recourse"},
     *    summary="Delete a Recourse",
     *    description="Delete a Recourse",
     *    security={{"bearerAuth":{}}},
     *    @OA\Response(
     *      response=202,
     *      description="Success",
     *      @OA\JsonContent(
     *        @OA\Property(property="status", type="string", example="success"),
     *        @OA\Property(property="code", type="number", example=202),
     *        @OA\Property(property="data", type="object", example="[]")
     *      )
     *    )
     * )
     */
    public function destroy(Recourse $recourse): JsonResponse
    {
      $data = $this->recourseService->delete_recourse($recourse);
      return $this->sendResponse($data, Response::HTTP_ACCEPTED);
    }

  }
