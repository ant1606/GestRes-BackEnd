<?php

  namespace App\Http\Controllers;

  use App\Http\Requests\YoutubeSubscriptionStoreRequest;
  use App\Http\Resources\YoutubeSubscriptionCollection;
  use App\Http\Resources\YoutubeSubscriptionResource;
  use App\Http\Services\YoutubeSubscriptionService;
  use App\Models\YoutubeSubscription;
  use Exception;
  use Illuminate\Http\JsonResponse;
  use Illuminate\Http\Request;
  use Illuminate\Support\Facades\Cache;
  use OpenApi\Annotations as OA;
  use Symfony\Component\HttpFoundation\Response;

  //TODO Generar casos de prueba para este controlador
  //TODO Refactorizar anotaciones
  class YoutubeSubscriptionController extends ApiController
  {

    public function __construct(protected YoutubeSubscriptionService $youtubeSubscriptionService)
    {
    }

    /**
     * Obteniendo Listado de Youtube Subscriptions
     * @OA\Get(
     *    path="/youtube-subscription?searchTags=[]&searchTitle=''&page=1&perPage=5",
     *    operationId="getYoutubeSubscription",
     *    tags={"YoutubeSubscription"},
     *    summary="List Youtube Subscription",
     *    description="List Youtube Subscription",
     *    security={{"bearerAuth":{}}},
     *    @OA\Parameter(name="searchTags",in="query",required=false,description="Array of Tag's id",
     *      @OA\Schema(type="array", @OA\Items(type="number"))
     *    ),
     *    @OA\Parameter(name="searchTitle",in="query",required=false,description="Name to filter recourses",
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
     *            @OA\Property(property="identificador", type="integer", example=1),
     *            @OA\Property(property="youtubeId", type="string", example="SkZG2_P243X9PONV3QdZukxHB-ASrwsvbB5CvjuovAs"),
     *            @OA\Property(property="usuarioId", type="integer", example=1),
     *            @OA\Property(property="canalId", type="string", example="UCoQPEruJuFCppnu2fBtyEbA"),
     *            @OA\Property(property="titulo", type="string", example="El Loco Pildorita"),
     *            @OA\Property(property="fechaSubscripcion", type="string", format="date", example="2023-09-28"),
     *            @OA\Property(property="descripcion", type="string", example="Hola soy el LOCO PILDORITA, te envito a que te suscribas a mi canal, activa la campanita, dale me gusta, comparte. NO TE ARREPENTIRAS. TU AMIGO EL LOCO PILDORITA\n#ellocopildorita #comicosambulantes #tendencia"),
     *            @OA\Property(property="fotoDefault", type="string", example="https://yt3.ggpht.com/x90MXeHCLyZeGOvdVDqOjegCSpKbvVf-ZsVH_jHz16HowpXuENro3KrJSrfaicJiH9UZN315sw=s88-c-k-c0x00ffffff-no-rj"),
     *            @OA\Property(property="fotoMedium", type="string", example="https://yt3.ggpht.com/x90MXeHCLyZeGOvdVDqOjegCSpKbvVf-ZsVH_jHz16HowpXuENro3KrJSrfaicJiH9UZN315sw=s240-c-k-c0x00ffffff-no-rj"),
     *            @OA\Property(property="fotoHigh", type="string", example="https://yt3.ggpht.com/x90MXeHCLyZeGOvdVDqOjegCSpKbvVf-ZsVH_jHz16HowpXuENro3KrJSrfaicJiH9UZN315sw=s800-c-k-c0x00ffffff-no-rj"),
     *            @OA\Property(property="tags",type="array",
     *              @OA\Items(
     *                @OA\Property(property="identificador", type="integer", example=2),
     *                @OA\Property(property="nombre", type="string", example="PROGRAMACIÓN"),
     *                @OA\Property(property="estilos", type="string", example="bg-gray-900 text-white"),
     *                @OA\Property(property="total", type="integer", example=0),
     *              )
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
      $data = $this->youtubeSubscriptionService->get_youtube_subscriptions(
        $request->input('searchTags', []),
        $request->input('searchTitle'),
      );
      return $this->sendResponse(new YoutubeSubscriptionCollection($data), Response::HTTP_OK);
    }

    /**
     * Obteniendo el estado del proceso de importación de subscripciones Youtube
     * @OA\Get(
     *    path="/youtube-subscription/checkstatus",
     *    operationId="getYoutubeSubscriptionChekcStatus",
     *    tags={"YoutubeSubscription"},
     *    summary="Get Status of proccess to import YoutubeSubscription",
     *    description="Get Status of proccess to import YoutubeSubscription",
     *    security={{"bearerAuth":{}}},
     *    @OA\Response(
     *      response=200,
     *      description="Success",
     *      @OA\JsonContent(
     *        @OA\Property(property="status", type="string", example="success"),
     *        @OA\Property(property="code", type="number", example=200),
     *        @OA\Property(property="message", type="string", example="procesando"),
     *      )
     *    )
     * )
     *
     */
    function checkProcessStatus(): JsonResponse
    {
      $isProcessing = Cache::get('process_status', false);

      return $isProcessing
        ? $this->sendMessage("procesando", Response::HTTP_OK)
        : $this->sendMessage("finalizado", Response::HTTP_OK);
    }

    /**
     * Verifica si estamos dentro de la cantidad de cuota para realizar peticiones a la API Youtube
     * @OA\Get(
     *    path="/youtube-subscription/checkQuota",
     *    operationId="checkQuotaYoutubeSubscription",
     *    tags={"YoutubeSubscription"},
     *    summary="Check Limit Quota to query data from API Youtube",
     *    description="Check Limit Quota to query data from API Youtube",
     *    security={{"bearerAuth":{}}},
     *    @OA\Response(
     *      response=200,
     *      description="Success",
     *      @OA\JsonContent(
     *        @OA\Property(property="status", type="string", example="success"),
     *        @OA\Property(property="code", type="number", example=200),
     *        @OA\Property(property="message", type="string", example="permitido"),
     *      )
     *    )
     * )
     * @throws Exception
     */
    public function checkLimitQuotaAPIYoutube(): JsonResponse
    {
      return $this->youtubeSubscriptionService->check_limit_quota_api_youtube() ?
        $this->sendMessage("permitido", Response::HTTP_OK) :
        $this->sendMessage("denegado", Response::HTTP_SERVICE_UNAVAILABLE);
    }

    /**
     * Importando Suscripciones de Youtube desde Autentificación OAuth con Google
     * @OA\Post(
     *    path="/youtube-subscription",
     *    operationId="postYoutubeSubscription",
     *    tags={"YoutubeSubscription"},
     *    summary="Import Youtube Subscription from Google Login OAuth",
     *    description="Import Youtube Subscription from Google Login OAuth",
     *    security={{"bearerAuth":{}}},
     *    @OA\RequestBody(
     *      required=true,
     *      @OA\MediaType(
     *        mediaType="application/json",
     *        @OA\Schema(
     *          required={"access_token", "order"},
     *          @OA\Property(property="author", type="string", example=""),
     *          @OA\Property(property="editorial", type="string", example=""),
     *          example={
     *            "access_token": "ASK29ZXC_aSD1741%5&4ECS",
     *            "order": "relevance",
     *          }
     *        )
     *      )
     *    ),
     *    @OA\Response(
     *      response=200,
     *      description="Success",
     *      @OA\JsonContent(
     *        @OA\Property(property="status", type="string", example="success"),
     *        @OA\Property(property="code", type="number", example=200),
     *        @OA\Property(property="message", type="string", example="Se insertaron los registros"),
     *      )
     *    )
     * )
     * @throws Exception
     */
    public function store(YoutubeSubscriptionStoreRequest $request): JsonResponse
    {
      $data = $this->youtubeSubscriptionService->save_youtube_subscription(
        $request->input('access_token'),
        $request->input('order')
      );

      return $data ?
        $this->sendMessage("Se insertaron los registros", Response::HTTP_OK) :
        $this->sendError(Response::HTTP_NOT_FOUND, "Falló al importar subscripciones");
    }

    /**
     * Actualizando Etiquetas de una Suscripción de Youtube
     * @OA\Put(
     *    path="/youtube-subscription/{youtubeSubscription_id}",
     *    operationId="putYoutubeSubscription",
     *    tags={"YoutubeSubscription"},
     *    summary="Update Tags on Youtube Subscription",
     *    description="Update Tags on Youtube Subscription",
     *    security={{"bearerAuth":{}}},
     *    @OA\RequestBody(
     *      required=true,
     *      @OA\MediaType(
     *        mediaType="application/json",
     *        @OA\Schema(
     *          required={"tags"},
     *          @OA\Property(property="tags", type="array", @OA\Items( type="number"), example="[]"),
     *          @OA\Property(property="editorial", type="string", example=""),
     *          example={"tags": "[2,3]"}
     *        )
     *      )
     *    ),
     *    @OA\Response(
     *      response=200,
     *      description="Success",
     *      @OA\JsonContent(
     *        @OA\Property(property="status", type="string", example="success"),
     *        @OA\Property(property="code", type="number", example=200),
     *        @OA\Property(property="data", type="object",
     *          @OA\Property(property="identificador", type="integer", example=1),
     *          @OA\Property(property="youtubeId", type="string", example="SkZG2_P243X9PONV3QdZukxHB-ASrwsvbB5CvjuovAs"),
     *          @OA\Property(property="usuarioId", type="integer", example=1),
     *          @OA\Property(property="canalId", type="string", example="UCoQPEruJuFCppnu2fBtyEbA"),
     *          @OA\Property(property="titulo", type="string", example="El Loco Pildorita"),
     *          @OA\Property(property="fechaSubscripcion", type="string", format="date", example="2023-09-28"),
     *          @OA\Property(property="descripcion", type="string", example="Hola soy el LOCO PILDORITA, te envito a que te suscribas a mi canal, activa la campanita, dale me gusta, comparte. NO TE ARREPENTIRAS. TU AMIGO EL LOCO PILDORITA\n#ellocopildorita #comicosambulantes #tendencia"),
     *          @OA\Property(property="fotoDefault", type="string", example="https://yt3.ggpht.com/x90MXeHCLyZeGOvdVDqOjegCSpKbvVf-ZsVH_jHz16HowpXuENro3KrJSrfaicJiH9UZN315sw=s88-c-k-c0x00ffffff-no-rj"),
     *          @OA\Property(property="fotoMedium", type="string", example="https://yt3.ggpht.com/x90MXeHCLyZeGOvdVDqOjegCSpKbvVf-ZsVH_jHz16HowpXuENro3KrJSrfaicJiH9UZN315sw=s240-c-k-c0x00ffffff-no-rj"),
     *          @OA\Property(property="fotoHigh", type="string", example="https://yt3.ggpht.com/x90MXeHCLyZeGOvdVDqOjegCSpKbvVf-ZsVH_jHz16HowpXuENro3KrJSrfaicJiH9UZN315sw=s800-c-k-c0x00ffffff-no-rj"),
     *          @OA\Property(property="tags",type="array",
     *            @OA\Items(
     *              @OA\Property(property="identificador", type="integer", example=2),
     *              @OA\Property(property="nombre", type="string", example="PROGRAMACIÓN"),
     *              @OA\Property(property="estilos", type="string", example="bg-gray-900 text-white"),
     *              @OA\Property(property="total", type="integer", example=0),
     *            )
     *          )
     *        ),
     *      )
     *    )
     * )
     * @throws Exception
     */
    public function update(YoutubeSubscription $youtube_subscription, Request $request): JsonResponse
    {
      $data = $this->youtubeSubscriptionService->update_youtube_subscription(
        $youtube_subscription,
        $request->input("tags", [])
      );
      return $this->sendResponse(
        new YoutubeSubscriptionResource($data),
        Response::HTTP_ACCEPTED,
        false
      );
    }

    /**
     * Eliminando Suscripciones de Youtube
     * @OA\Delete(
     *    path="/youtube-subscription/{youtubeSubscription_id}",
     *    operationId="deleteYoutubeSubscription",
     *    tags={"YoutubeSubscription"},
     *    summary="Delete Youtube Subscription",
     *    description="Delete Youtube Subscription",
     *    security={{"bearerAuth":{}}},
     *    @OA\Response(
     *      response=200,
     *      description="Success",
     *      @OA\JsonContent(
     *        @OA\Property(property="status", type="string", example="success"),
     *        @OA\Property(property="code", type="number", example=200),
     *        @OA\Property(property="data", type="object",
     *          @OA\Property(property="identificador", type="integer", example=1),
     *          @OA\Property(property="youtubeId", type="string", example="SkZG2_P243X9PONV3QdZukxHB-ASrwsvbB5CvjuovAs"),
     *          @OA\Property(property="usuarioId", type="integer", example=1),
     *          @OA\Property(property="canalId", type="string", example="UCoQPEruJuFCppnu2fBtyEbA"),
     *          @OA\Property(property="titulo", type="string", example="El Loco Pildorita"),
     *          @OA\Property(property="fechaSubscripcion", type="string", format="date", example="2023-09-28"),
     *          @OA\Property(property="descripcion", type="string", example="Hola soy el LOCO PILDORITA, te envito a que te suscribas a mi canal, activa la campanita, dale me gusta, comparte. NO TE ARREPENTIRAS. TU AMIGO EL LOCO PILDORITA\n#ellocopildorita #comicosambulantes #tendencia"),
     *          @OA\Property(property="fotoDefault", type="string", example="https://yt3.ggpht.com/x90MXeHCLyZeGOvdVDqOjegCSpKbvVf-ZsVH_jHz16HowpXuENro3KrJSrfaicJiH9UZN315sw=s88-c-k-c0x00ffffff-no-rj"),
     *          @OA\Property(property="fotoMedium", type="string", example="https://yt3.ggpht.com/x90MXeHCLyZeGOvdVDqOjegCSpKbvVf-ZsVH_jHz16HowpXuENro3KrJSrfaicJiH9UZN315sw=s240-c-k-c0x00ffffff-no-rj"),
     *          @OA\Property(property="fotoHigh", type="string", example="https://yt3.ggpht.com/x90MXeHCLyZeGOvdVDqOjegCSpKbvVf-ZsVH_jHz16HowpXuENro3KrJSrfaicJiH9UZN315sw=s800-c-k-c0x00ffffff-no-rj"),
     *          @OA\Property(property="tags",type="array",
     *            @OA\Items(
     *              @OA\Property(property="identificador", type="integer", example=2),
     *              @OA\Property(property="nombre", type="string", example="PROGRAMACIÓN"),
     *              @OA\Property(property="estilos", type="string", example="bg-gray-900 text-white"),
     *              @OA\Property(property="total", type="integer", example=0),
     *            )
     *          )
     *        )
     *      )
     *    )
     * )
     */
    public function destroy(YoutubeSubscription $youtube_subscription): JsonResponse
    {
      $data = $this->youtubeSubscriptionService->delete_youtube_subscription($youtube_subscription);
      return $this->sendResponse(
        new YoutubeSubscriptionResource($data),
        Response::HTTP_ACCEPTED,
        false
      );
    }
  }
