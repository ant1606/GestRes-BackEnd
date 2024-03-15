<?php

  namespace App\Http\Services;

  use App\Enums\APINameEnum;
  use App\Models\Settings;
  use App\Models\YoutubeSubscription;
  use Exception;
  use Google\Service\YouTube as ServiceYouTube;
  use Google\Service\YouTube\Subscription;
  use Google_Client;
  use Illuminate\Database\Eloquent\Collection;
  use Illuminate\Support\Carbon;
  use Illuminate\Support\Facades\Auth;
  use Illuminate\Support\Facades\Cache;
  use Illuminate\Support\Facades\DB;
  use Symfony\Component\HttpFoundation\Response;

  class YoutubeSubscriptionService
  {

    public function get_youtube_subscriptions(array $searchTags = [], string $searchTitle = null): Collection|array
    {
      $subscription = YoutubeSubscription::query();

      $subscription = $subscription->where('user_id', auth()->user()->id);
      if ($searchTags !== []) {
        $subscription = $subscription->whereHas('tags', function ($query) use ($searchTags) {
          $query->whereIn('tag_id', $searchTags);
        });
      }
      if ($searchTitle !== null)
        $subscription = $subscription->where('title', 'like', '%' . $searchTitle . '%');

      return $subscription->get();
    }

    /**
     * @throws Exception
     */
    public function save_youtube_subscription(string $access_token, string $order): bool
    {
      !Cache::has('process_call_APIYoutube')
        ? Cache::add('process_call_APIYoutube', true, now()->addMinutes(30))
        : Cache::put('process_call_APIYoutube', true, now()->addMinutes(30));

      $client = new Google_Client();
      $token = $access_token;
      try {
        $client->setAccessToken($token);
        $youtube = new ServiceYouTube($client);
        $tokenPage = '';
        $buffer = [];
        $limitRateAPI =(int)Settings::getData(APINameEnum::API_YOUTUBE->name)['value'];
        $currentQueryAccumulatorAPI =(int)Settings::getData(APINameEnum::API_YOUTUBE->name)['value2'];
        $APIQueryCounter = 0;

        do {
          $APIQueryCounter++;
          if(($currentQueryAccumulatorAPI + $APIQueryCounter) > $limitRateAPI) {
            // Descontando 1 al APICounter cuando se haya excedido el límite
            $APIQueryCounter--;
            break;
          }
          /*
          El valor por defecto es SUBSCRIPTION_ORDER_RELEVANCE
          alphabetical – Sort alphabetically.
          relevance – Sort by relevance.
          unread – Sort by order of activity.
          */
          $subs = $youtube->subscriptions->listSubscriptions(
            ['snippet'],
            ['mine' => true, 'maxResults' => 50, 'pageToken' => $tokenPage, 'order' => $order]
          );
          $tokenPage = $subs->getNextPageToken();
          array_push($buffer, ...$this->process_items($subs->getItems()));

        } while ($tokenPage !== null);

        if($buffer !== []) {
          YoutubeSubscription::upsert(
            $buffer,
            ['youtube_id', 'channel_id'],
            ['title', 'published_at', 'description', 'thumbnail_default', 'thumbnail_medium', 'thumbnail_high']
          );
        }

        // Actualizando contador de consultas a la API
        Settings::query()->where('key', APINameEnum::API_YOUTUBE->name)->update(['value2' => (string)($currentQueryAccumulatorAPI + $APIQueryCounter)]);
        //Actualizamos los datos de Settings en cache
        Settings::reload_data_settings_to_cache();
        return true;
      } catch (Exception $e) {
        throw new Exception("Ocurrió un error al importar las subscripciones de Youtube", Response::HTTP_NOT_FOUND);

      } finally {
        $client->revokeToken($token);
        Cache::put('process_call_APIYoutube', false, now()->addMinutes(30));
      }
    }

    /**
     * @throws Exception
     */
    public function update_youtube_subscription(YoutubeSubscription $youtube_subscription, array $tags = []): YoutubeSubscription
    {
      $existingTags = $youtube_subscription->tags()->pluck('taggables.tag_id')->toArray();

      //      if ((isset($request->tags) ? $request->tags : []) === $existingTags) {
      if ($tags === $existingTags) {
        throw new Exception("Se debe especificar al menos un valor diferente para actualizar", Response::HTTP_UNPROCESSABLE_ENTITY);
      }

      try {
        DB::beginTransaction();
        $youtube_subscription->tags()->sync($tags);
        DB::commit();
        return $youtube_subscription;
      } catch (Exception $e) {
        DB::rollBack();
        // TODO Escribir los mensajes de error en un log $e->getMessage()
        // dd($e);
        throw new Exception("Ocurrió un error al actualizar la subscripción de Youtube, hable con el administrador", Response::HTTP_UNPROCESSABLE_ENTITY);

      }
    }

    public function delete_youtube_subscription(YoutubeSubscription $youtube_subscription): YoutubeSubscription
    {
      //TODO Insertar autorización para eliminar recurso sólo al usuario que lo creo
      $youtube_subscription->tags()->detach();
      $youtube_subscription->delete();

      return $youtube_subscription;
    }

    public function check_limit_quota_api_youtube(): bool
    {
      Settings::reload_data_settings_to_cache();
      $limitRateAPI = (int)Settings::getData(APINameEnum::API_YOUTUBE->name)['value'];
      $currentQueryAccumulatorAPI = Settings::getData(APINameEnum::API_YOUTUBE->name)['value2'];
      if(!is_numeric($currentQueryAccumulatorAPI))
        throw new Exception("Ocurrio un error al verificar el limite de la cuota", Response::HTTP_INTERNAL_SERVER_ERROR);

      if ($limitRateAPI <= (int)$currentQueryAccumulatorAPI)
        throw new Exception("Se alcanzó el limite de peticiones a la API Youtube, inténtelo el día de mañana", Response::HTTP_SERVICE_UNAVAILABLE);

      return true;
    }

    /**
     * @param Subscription[] $subscriptions
     */
    private function process_items(array $subscriptions): array
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
            "thumbnail_default" => $thumbnails->getDefault()?->getUrl(),
            "thumbnail_medium" => $thumbnails->getMedium()?->getUrl(),
            "thumbnail_high" => $thumbnails->getHigh()?->getUrl(),
          ];
        }
      }
      return $items;
    }
  }
