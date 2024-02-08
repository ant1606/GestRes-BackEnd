<?php

  namespace App\Http\Controllers;

  use App\Http\Requests\WebPagePostRequest;
  use App\Http\Resources\WebPageCollection;
  use App\Http\Resources\WebPageResource;
  use App\Http\Services\WebPageService;
  use App\Models\WebPage;
  use Exception;
  use Illuminate\Http\JsonResponse;
  use Illuminate\Http\Request;
  use OpenApi\Annotations as OA;
  use Symfony\Component\HttpFoundation\Response;

  //TODO Realizar los casos de Test faltantes de este controlador
  //TODO Refactorizar anotaciones
  class WebPageController extends ApiController
  {

    public function __construct(protected WebPageService $webPageService)
    {
    }

    /**
     * Obteniendo Listado de WebPages
     * @OA\Get(
     *    path="/webpage?searchTags=[]&searchNombre=''&page=1&perPage=5",
     *    operationId="getWebPages",
     *    tags={"WebPages"},
     *    summary="List WebPages",
     *    description="List WebPages",
     *    security={{"bearerAuth":{}}},
     *    @OA\Parameter(name="searchTags",in="query",required=false,description="Array of Tag's id",
     *      @OA\Schema(type="array", @OA\Items(type="number"))
     *    ),
     *    @OA\Parameter(name="searchNombre",in="query",required=false,description="Name to filter recourses",
     *      @OA\Schema(type="string")
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
     *            @OA\Property(property="identificador", type="integer", example=8),
     *            @OA\Property(property="nombre", type="string", example="Developer Roadmaps"),
     *            @OA\Property(property="url", type="string", example="https://roadmap.sh/"),
     *            @OA\Property(property="descripcion", type="string", example="RoadMaps para formación profesional de Desarrolladores"),
     *            @OA\Property(property="totalVisitas", type="integer", example=0),
     *            @OA\Property(property="tags",type="array",
     *              @OA\Items(
     *                @OA\Property(property="identificador", type="integer", example=2),
     *                @OA\Property(property="nombre", type="string", example="PROGRAMACIÓN"),
     *                @OA\Property(property="estilos", type="string", example="bg-gray-900 text-white"),
     *                @OA\Property(property="total", type="integer", example=0),
     *              )
     *            ),
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
      $data = $this->webPageService->get_web_pages(
        $request->input('searchTags', []),
        $request->input('searchNombre'),
      );
      return $this->sendResponse(new WebPageCollection($data), Response::HTTP_OK);
    }

    /**
     * Registrando un WebPage
     * @OA\Post(
     *    path="/webpage",
     *    operationId="postWebPage",
     *    tags={"WebPages"},
     *    summary="Save a WebPages",
     *    description="Save a WebPages",
     *    security={{"bearerAuth":{}}},
     *    @OA\RequestBody(
     *      required=true,
     *      @OA\MediaType(
     *        mediaType="application/json",
     *        @OA\Schema(
     *          required={"name", "url", "description", "tags"},
     *          @OA\Property(property="name", type="string", example="Mi Etiqueta"),
     *          @OA\Property(property="url", type="string", example="Mi Etiqueta"),
     *          @OA\Property(property="description", type="string", example="Mi Etiqueta"),
     *          @OA\Property(property="tags", type="array", @OA\Items( type="number"), example="[]"),
     *          example={ "name":"miweb", "url":"http://web.com", "description":"Web Testing", "tags":"[]"}
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
     *          @OA\Property(property="identificador", type="integer", example=8),
     *          @OA\Property(property="nombre", type="string", example="Developer Roadmaps"),
     *          @OA\Property(property="url", type="string", example="https://roadmap.sh/"),
     *          @OA\Property(property="descripcion", type="string", example="RoadMaps para formación profesional de Desarrolladores"),
     *          @OA\Property(property="totalVisitas", type="integer", example=0),
     *          @OA\Property(property="tags",type="array",
     *            @OA\Items(
     *              @OA\Property(property="identificador", type="integer", example=2),
     *              @OA\Property(property="nombre", type="string", example="PROGRAMACIÓN"),
     *              @OA\Property(property="estilos", type="string", example="bg-gray-900 text-white"),
     *              @OA\Property(property="total", type="integer", example=0),
     *            )
     *          ),
     *        ),
     *      )
     *    )
     * )
     */
    public function store(WebPagePostRequest $request): JsonResponse
    {
      $data = $this->webPageService->save_web_page(
        $request->input('url'),
        $request->input('name'),
        $request->input('description'),
        $request->input('tags', []),
      );
      return $this->sendResponse(new WebPageResource($data), Response::HTTP_CREATED, false);
    }

    /**
     * Actualizando un WebPage
     * @OA\Put(
     *    path="/webpage/{webpage_id}",
     *    operationId="putWebPage",
     *    tags={"WebPages"},
     *    summary="Update a WebPages",
     *    description="Update a WebPages",
     *    security={{"bearerAuth":{}}},
     *    @OA\RequestBody(
     *      required=true,
     *      @OA\MediaType(
     *        mediaType="application/json",
     *        @OA\Schema(
     *          required={"name", "url", "description", "tags"},
     *          @OA\Property(property="name", type="string", example="Mi Etiqueta"),
     *          @OA\Property(property="url", type="string", example="Mi Etiqueta"),
     *          @OA\Property(property="description", type="string", example="Mi Etiqueta"),
     *          @OA\Property(property="tags", type="array", @OA\Items( type="number"), example="[]"),
     *          example={ "name":"miweb", "url":"http://web.com", "description":"Web Testing", "tags":"[]"}
     *        )
     *      )
     *    ),
     *    @OA\Response(
     *      response=202,
     *      description="Success",
     *      @OA\JsonContent(
     *        @OA\Property(property="status", type="string", example="success"),
     *        @OA\Property(property="code", type="number", example=202),
     *        @OA\Property(property="data", type="object",
     *          @OA\Property(property="identificador", type="integer", example=8),
     *          @OA\Property(property="nombre", type="string", example="Developer Roadmaps"),
     *          @OA\Property(property="url", type="string", example="https://roadmap.sh/"),
     *          @OA\Property(property="descripcion", type="string", example="RoadMaps para formación profesional de Desarrolladores"),
     *          @OA\Property(property="totalVisitas", type="integer", example=0),
     *          @OA\Property(property="tags",type="array",
     *            @OA\Items(
     *              @OA\Property(property="identificador", type="integer", example=2),
     *              @OA\Property(property="nombre", type="string", example="PROGRAMACIÓN"),
     *              @OA\Property(property="estilos", type="string", example="bg-gray-900 text-white"),
     *              @OA\Property(property="total", type="integer", example=0),
     *            )
     *          ),
     *        ),
     *      )
     *    )
     * )
     * @throws Exception
     */
    public function update(WebPage $webpage, Request $request): JsonResponse
    {
      $data = $this->webPageService->update_web_page(
        $webpage,
        $request->input('url'),
        $request->input('name'),
        $request->input('description'),
        $request->input('tags', []),
      );

      return $this->sendResponse(new WebPageResource($data), Response::HTTP_ACCEPTED, false);
    }

    /**
     * Actualizando un WebPage
     * @OA\Delete(
     *    path="/webpage/{webpage_id}",
     *    operationId="deleteWebPage",
     *    tags={"WebPages"},
     *    summary="Delete a WebPages",
     *    description="Delete a WebPages",
     *    security={{"bearerAuth":{}}},
     *    @OA\Response(
     *      response=202,
     *      description="Success",
     *      @OA\JsonContent(
     *        @OA\Property(property="status", type="string", example="success"),
     *        @OA\Property(property="code", type="number", example=202),
     *        @OA\Property(property="data", type="object",
     *          @OA\Property(property="identificador", type="integer", example=8),
     *          @OA\Property(property="nombre", type="string", example="Developer Roadmaps"),
     *          @OA\Property(property="url", type="string", example="https://roadmap.sh/"),
     *          @OA\Property(property="descripcion", type="string", example="RoadMaps para formación profesional de Desarrolladores"),
     *          @OA\Property(property="totalVisitas", type="integer", example=0),
     *          @OA\Property(property="tags",type="array",
     *            @OA\Items(
     *              @OA\Property(property="identificador", type="integer", example=2),
     *              @OA\Property(property="nombre", type="string", example="PROGRAMACIÓN"),
     *              @OA\Property(property="estilos", type="string", example="bg-gray-900 text-white"),
     *              @OA\Property(property="total", type="integer", example=0),
     *            )
     *          ),
     *        ),
     *      )
     *    )
     * )
     * @throws Exception
     */
    public function destroy(WebPage $webpage): JsonResponse
    {
      $data = $this->webPageService->delete_web_page($webpage);
      return $this->sendResponse(new WebPageResource($data), Response::HTTP_ACCEPTED, false);
    }
  }
