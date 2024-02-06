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
  use Symfony\Component\HttpFoundation\Response;

  //TODO Generar casos de prueba para este controlador
  class YoutubeSubscriptionController extends ApiController
  {

    public function __construct(protected YoutubeSubscriptionService $youtubeSubscriptionService)
    {
    }

    public function index(Request $request): JsonResponse
    {
      $data = $this->youtubeSubscriptionService->get_youtube_subscriptions(
        $request->input('searchTags', []),
        $request->input('searchTitle'),
      );
      return $this->sendResponse(new YoutubeSubscriptionCollection($data), Response::HTTP_OK);
    }

    function checkProcessStatus(): JsonResponse
    {
      $isProcessing = Cache::get('process_status', false);

      return $isProcessing
        ? $this->sendMessage("procesando", Response::HTTP_OK)
        : $this->sendMessage("finalizado", Response::HTTP_OK);
    }

    /**
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
