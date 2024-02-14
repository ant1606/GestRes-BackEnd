<?php

  namespace App\Http\Controllers;

  use App\Http\Requests\TagIndexRequest;
  use App\Http\Requests\TagRequest;
  use App\Http\Resources\TagCollection;
  use App\Http\Resources\TagResource;
  use App\Http\Services\TagService;
  use App\Models\Tag;
  use Exception;
  use Illuminate\Http\JsonResponse;
  use OpenApi\Annotations as OA;
  use Symfony\Component\HttpFoundation\Response;

  //TODO Refactorizar anotaciones
  class TagController extends ApiController
  {

    public function __construct(protected TagService $tagService)
    {
      // $this->middleware('transform.input:' . TagResource::class)->only(['store', 'update']);
    }

    /**
     * Obteniendo Listado de Tags
     * @OA\Get(
     *    path="/tag?searchNombre=''&sortNombre=''",
     *    operationId="getTags",
     *    tags={"Tags"},
     *    summary="List Tags",
     *    description="List Tags",
     *    security={{"bearerAuth":{}}},
     *    @OA\Parameter(name="searchNombre",in="query",required=false,description="Filter Tag By Name",
     *      @OA\Schema(type="string")
     *    ),
     *    @OA\Parameter(name="sortNombre",in="query",required=false,description="Sorting Name by desc or asc",
     *      @OA\Schema(type="string")
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
     *            @OA\Property(property="nombre", type="string", example="DESARROLLO WEB"),
     *            @OA\Property(property="estilos", type="string", example="bg-pink-400 text-white"),
     *            @OA\Property(property="total", type="number", nullable=5),
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
    public function index(TagIndexRequest $request): JsonResponse
    {
      $data = $this->tagService->get_tags($request->input("searchNombre"), $request->input('sortNombre'));
      return $this->sendResponse(new TagCollection($data), Response::HTTP_OK);
    }

    /**
     * Obteniendo los Tags para poblar el SelectTag
     * @OA\Get(
     *    path="/tag",
     *    operationId="getTagsToSelecTag",
     *    tags={"Tags"},
     *    summary="Get All Tag to SelectTag",
     *    description="Get All Tag to SelectTag",
     *    security={{"bearerAuth":{}}},
     *    @OA\Response(
     *      response=202,
     *      description="Success",
     *      @OA\JsonContent(
     *        @OA\Property(property="status", type="string", example="success"),
     *        @OA\Property(property="code", type="number", example=200),
     *        @OA\Property(property="data", type="array",
     *          @OA\Items(
     *            @OA\Property(property="id", type="integer", example=67),
     *            @OA\Property(property="name", type="string", example="DESARROLLO WEB"),
     *            @OA\Property(property="style", type="string", example="bg-pink-400 text-white"),
     *          )
     *        ),
     *      )
     *    )
     * )
     */
    public function getTagsForTagSelector(): JsonResponse
    {
      $tags = Tag::all();
      return $this->sendResponse($tags->toArray(), Response::HTTP_ACCEPTED);
    }

    /**
     * Registrando un Tag
     * @OA\Post(
     *    path="/tag",
     *    operationId="postTag",
     *    tags={"Tags"},
     *    summary="Save a Tag",
     *    description="Save a Tag",
     *    security={{"bearerAuth":{}}},
     *    @OA\RequestBody(
     *      required=true,
     *      @OA\MediaType(
     *        mediaType="application/json",
     *        @OA\Schema(
     *          required={"nombre"},
     *          @OA\Property(property="nombre", type="string", example="Mi Etiqueta"),
     *          example={ "nombre": "Mi Etiqueta"}
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
     *            @OA\Property(property="identificador", type="integer", example=67),
     *            @OA\Property(property="nombre", type="string", example="DESARROLLO WEB"),
     *            @OA\Property(property="estilos", type="string", example="bg-pink-400 text-white"),
     *            @OA\Property(property="total", type="number", nullable=5),
     *        ),
     *      )
     *    )
     * )
     */
    public function store(TagRequest $request): JsonResponse
    {
     $data = $this->tagService->save_tag($request->input('name'));
      return $this->sendResponse(new TagResource($data), Response::HTTP_CREATED);
    }

    /**
     * Obteniendo una Tag
     * @OA\Get(
     *    path="/tag/{tag_id}",
     *    operationId="getTag",
     *    tags={"Tags"},
     *    summary="Get Tag",
     *    description="Get Tag",
     *    security={{"bearerAuth":{}}},
     *    @OA\Response(
     *      response=202,
     *      description="Success",
     *      @OA\JsonContent(
     *        @OA\Property(property="status", type="string", example="success"),
     *        @OA\Property(property="code", type="number", example=202),
     *        @OA\Property(property="data", type="object",
     *          @OA\Property(property="identificador", type="integer", example=67),
     *          @OA\Property(property="nombre", type="string", example="DESARROLLO WEB"),
     *          @OA\Property(property="estilos", type="string", example="bg-pink-400 text-white"),
     *          @OA\Property(property="total", type="number", nullable=5),
     *        ),
     *      )
     *    )
     * )
     */
    public function show(Tag $tag): JsonResponse
    {
      return $this->sendResponse(new TagResource($tag), Response::HTTP_ACCEPTED);
    }

    /**
     * Registrando un Tag
     * @OA\Put(
     *    path="/tag/{tag_id}",
     *    operationId="putTag",
     *    tags={"Tags"},
     *    summary="Update a Tag",
     *    description="Update a Tag",
     *    security={{"bearerAuth":{}}},
     *    @OA\RequestBody(
     *      required=true,
     *      @OA\MediaType(
     *        mediaType="application/json",
     *        @OA\Schema(
     *          required={"nombre"},
     *          @OA\Property(property="nombre", type="string", example="Mi Etiqueta"),
     *          example={ "nombre": "Mi Etiqueta"}
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
     *            @OA\Property(property="identificador", type="integer", example=67),
     *            @OA\Property(property="nombre", type="string", example="DESARROLLO WEB"),
     *            @OA\Property(property="estilos", type="string", example="bg-pink-400 text-white"),
     *            @OA\Property(property="total", type="number", nullable=5),
     *        ),
     *      )
     *    )
     * )
     *
     * @throws Exception
     */
    public function update(TagRequest $request, Tag $tag): JsonResponse
    {
      $data = $this->tagService->update_tag($tag, $request->input("name"));
      return $this->sendResponse(new TagResource($data), Response::HTTP_ACCEPTED);
    }

    /**
     * Eliminando un Tag
     * @OA\Delete(
     *    path="/tag/{recourse_id}",
     *    operationId="deleteTag",
     *    tags={"Tags"},
     *    summary="Delete a Tag",
     *    description="Delete a Tag",
     *    security={{"bearerAuth":{}}},
     *    @OA\Response(
     *      response=202,
     *      description="Success",
     *      @OA\JsonContent(
     *        @OA\Property(property="status", type="string", example="success"),
     *        @OA\Property(property="code", type="number", example=202),
     *        @OA\Property(property="data", type="object",
     *            @OA\Property(property="identificador", type="integer", example=67),
     *            @OA\Property(property="nombre", type="string", example="DESARROLLO WEB"),
     *            @OA\Property(property="estilos", type="string", example="bg-pink-400 text-white"),
     *            @OA\Property(property="total", type="number", nullable=5),
     *        ),
     *      )
     *    )
     * )
     */
    public function destroy(Tag $tag): JsonResponse
    {
      $data = $this->tagService->delete_tag($tag);
      return $this->sendResponse(new TagResource($data), Response::HTTP_ACCEPTED);
    }

  }
