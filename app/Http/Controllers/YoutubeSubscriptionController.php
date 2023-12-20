<?php

namespace App\Http\Controllers;

use App\Http\Requests\YoutubeSubscriptionStoreRequest;
use App\Http\Resources\YoutubeSubscriptionCollection;
use App\Http\Resources\YoutubeSubscriptionResource;
use App\Models\YoutubeSubscription;
use Google\Service\YouTube as ServiceYouTube;
use Google\Service\YouTube\Subscription;
use Google_Client;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class YoutubeSubscriptionController extends ApiController
{
  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index(Request $request)
  {
    $subscription = YoutubeSubscription::query();

    $subscription = $subscription->where('user_id', auth()->user()->id);
    if ($request->has('searchTags') && $request->searchTags !== null && $request->searchTags !== []) {
      $subscription = $subscription->whereHas('tags', function ($query) use ($request) {
        $query->whereIn('tag_id', $request->searchTags);
      });
    }
    if ($request->has('searchTitle') && $request->searchTitle !== null)
      $subscription = $subscription->where('title', 'like', '%' . $request->searchTitle . '%');

    $subscriptions = $subscription->get();
    // $subscriptions = YoutubeSubscription::where('user_id', Auth::user()->id)->get();
    return $this->showAllResource(new YoutubeSubscriptionCollection($subscriptions), Response::HTTP_OK);
  }

  /**
   * Check if process of store subscription is processing
   *
   * @return \Illuminate\Http\Response
   */
  function checkProcessStatus()
  {
    $isProcessing = Cache::get('process_status', false);

    if ($isProcessing) {
      return $this->showMessage(["message" => "procesando"], Response::HTTP_OK);
    } else {
      return $this->showMessage(["message" => "finalizado"], Response::HTTP_OK);
    }
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function store(YoutubeSubscriptionStoreRequest $request)
  {
    !Cache::has('process_call_APIYoutube')
      ? Cache::add('process_call_APIYoutube', true, now()->addMinutes(30))
      : Cache::put('process_call_APIYoutube', true, now()->addMinutes(30));

    $client = new Google_Client();
    $token = $request->get('access_token');
    try {
      // DB::beginTransaction();
      $client->setAccessToken($token);
      $youtube = new ServiceYouTube($client);
      $tokenPage = '';
      $buffer = [];
      do {
        // TODO Considerar usar el parametro order, para poder obtener la mayor cantidad de subscripciones, esto triplicaria el proceso
        /*
        El valor por defecto es SUBSCRIPTION_ORDER_RELEVANCE
        alphabetical – Sort alphabetically.
        relevance – Sort by relevance.
        unread – Sort by order of activity.
        */
        $subs = $youtube->subscriptions->listSubscriptions(
          ['snippet'],
          ['mine' => true, 'maxResults' => 50, 'pageToken' => $tokenPage, 'order' => 'unread']
        );
        $tokenPage = $subs->getNextPageToken();
        array_push($buffer, ...$this->process_items($subs->getItems()));
      } while ($tokenPage !== null);

      YoutubeSubscription::upsert(
        $buffer,
        ['youtube_id', 'channel_id'],
        ['title', 'published_at', 'description', 'thumbnail_default', 'thumbnail_medium', 'thumbnail_high']
      );

      // DB::commit();
      // dd($buffer);
      return ["message" => "Se insertaron los registros"];
    } catch (\Throwable $th) {
      // DB::rollBack();
      return ["error" => $th->getMessage()];
    } finally {
      $client->revokeToken($token);
      Cache::put('process_call_APIYoutube', false, now()->addMinutes(30));
    }
  }

  /**
   * Display the specified resource.
   *
   * @param  \App\Models\YoutubeSubscription $subscription
   * @return \Illuminate\Http\Response
   */
  public function show(YoutubeSubscription $subscription)
  {
    //
  }

  /**
   * Actualiza los Tags del YoutubeSubscription Model
   * 
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  \App\Models\YoutubeSubscription $subscription
   * @return \Illuminate\Http\Response
   */
  public function update(YoutubeSubscription $subscription, Request $request)
  {
    $existingTags = $subscription->tags()->pluck('taggables.tag_id')->toArray();

    if ((isset($request->tags) ? $request->tags : []) === $existingTags) {
      return $this->errorResponse(
        ["api_response" => ["Se debe especificar al menos un valor diferente para actualizar"]],
        Response::HTTP_UNPROCESSABLE_ENTITY
      );
    }

    try {
      DB::beginTransaction();
      $subscription->tags()->sync($request->tags);
      DB::commit();
      return $this->showOne(new YoutubeSubscriptionResource($subscription), Response::HTTP_ACCEPTED);
    } catch (\Throwable $th) {
      DB::rollBack();
      // TODO Escribir los mensajes de error en un log $e->getMessage()
      //TODO Envolver los mensajes de error en la nomenclatura usada [api_response => []]
      dd($th);
      return $this->errorResponse(
        ["api_response" => ["Ocurrió un error al actualizar el recurso, hable con el administrador"]],
        Response::HTTP_UNPROCESSABLE_ENTITY
      );
    }
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  \App\Models\YoutubeSubscription $subscription
   * @return \Illuminate\Http\Response
   */
  public function destroy(YoutubeSubscription $subscription)
  {
    //
  }

  /**
   * @param Array &$buffer
   * @param Subscription[] $subscriptions
   */
  private function process_items($subscriptions)
  {
    $items = [];
    foreach ($subscriptions as $subscription) {
      if ($subscription instanceof Subscription) {
        $snippet = $subscription->getSnippet();
        $resource = $snippet->getResourceId();
        $thumbnails = $snippet->getThumbnails();
        $items[] = [
          "youtube_id" => $subscription->getId(),
          "user_id" => Auth::user()->id,
          "channel_id" => $resource->getChannelId(),
          "title" => $snippet->getTitle(),
          "published_at" => Carbon::parse($snippet->getPublishedAt())->format("Y-m-d"),
          "description" => $snippet->getDescription(),
          "thumbnail_default" => $thumbnails->getDefault() ? $thumbnails->getDefault()->getUrl() : null,
          "thumbnail_medium" => $thumbnails->getMedium() ? $thumbnails->getMedium()->getUrl() : null,
          "thumbnail_high" => $thumbnails->getHigh() ? $thumbnails->getHigh()->getUrl() : null,
        ];
      }
    }
    return $items;
  }
}
