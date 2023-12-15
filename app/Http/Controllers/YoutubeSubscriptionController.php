<?php

namespace App\Http\Controllers;

use App\Http\Requests\YoutubeSubscriptionStoreRequest;
use App\Models\YoutubeSubscription;
use Google\Service\YouTube as ServiceYouTube;
use Google\Service\YouTube\Subscription;
use Google_Client;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class YoutubeSubscriptionController extends Controller
{
  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index()
  {
  }

  /**
   * Show the form for creating a new resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function create()
  {
    //
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function store(YoutubeSubscriptionStoreRequest $request)
  {
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
        alphabetical – Sort alphabetically.
        relevance – Sort by relevance.
        unread – Sort by order of activity.
        */
        $subs = $youtube->subscriptions->listSubscriptions(
          ['snippet'],
          ['mine' => true, 'maxResults' => 50, 'pageToken' => $tokenPage]
        );
        $tokenPage = $subs->getNextPageToken();
        array_push($buffer, ...$this->process_items($subs->getItems()));
      } while ($tokenPage !== null);

      YoutubeSubscription::upsert(
        $buffer,
        ['id'],
        ['channel_id', 'title', 'published_at', 'description', 'thumbnail_default', 'thumbnail_medium', 'thumbnail_high', 'user_id']
      );

      // DB::commit();
      // dd($buffer);
      return ["message" => "Se insertaron los registros"];
    } catch (\Throwable $th) {
      // DB::rollBack();
      return ["error" => $th->getMessage()];
    } finally {
      $client->revokeToken($token);
    }
  }

  /**
   * Display the specified resource.
   *
   * @param  \App\Models\YoutubeSubscriptionController  $youtubeSubscriptionController
   * @return \Illuminate\Http\Response
   */
  public function show(YoutubeSubscriptionController $youtubeSubscriptionController)
  {
    //
  }

  /**
   * Show the form for editing the specified resource.
   *
   * @param  \App\Models\YoutubeSubscriptionController  $youtubeSubscriptionController
   * @return \Illuminate\Http\Response
   */
  public function edit(YoutubeSubscriptionController $youtubeSubscriptionController)
  {
    //
  }

  /**
   * Update the specified resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  \App\Models\YoutubeSubscriptionController  $youtubeSubscriptionController
   * @return \Illuminate\Http\Response
   */
  public function update(Request $request, YoutubeSubscriptionController $youtubeSubscriptionController)
  {
    //
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  \App\Models\YoutubeSubscriptionController  $youtubeSubscriptionController
   * @return \Illuminate\Http\Response
   */
  public function destroy(YoutubeSubscriptionController $youtubeSubscriptionController)
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
        //TODO Colocar el id del usuario autentificado
        $items[] = [
          "id" => $subscription->getId(),
          "user_id" => 1,
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
