<?php

  namespace App\Traits;

  use Illuminate\Http\JsonResponse;
  use Illuminate\Http\Resources\Json\JsonResource;
  use Illuminate\Http\Resources\Json\ResourceCollection;
  use Illuminate\Pagination\LengthAwarePaginator;
  use Illuminate\Support\Facades\Cache;
  use Illuminate\Support\Facades\Validator;

  trait ApiResponser
  {

    /**Genera una respuesta Json
     * @param int $code
     * @param string $message Indica mensaje de error generado
     * @param array $detail Contiene los errores de validación
     * @return JsonResponse
     */
    protected function sendError(int $code, string $message = "", array $detail = []): JsonResponse
    {
      return response()->json(
        [
          "status" => "error",
          "code" => $code,
          "error" => [
            "message" => $message,
            "details" => $detail
          ]
        ],
        $code
      );
    }

    /**Genera una respuesta Json que envía solo un mensaje al cliente
     * @param string $message Indica mensaje generador por API a enviar a Cliente
     * @param int $code Código de respuesta HTTP
     * @return JsonResponse
     */
    protected function sendMessage(string $message, int $code = 200): JsonResponse
    {
      return response()->json(
        [
          "status" => "success",
          "code" => $code,
          "message" => $message
        ],
        $code
      );
    }

    /**Genera una respuesta Json
     * @param JsonResource|ResourceCollection|array $result
     * @param int $code
     * @param bool $make_pagination
     * @return JsonResponse
     */
    protected function sendResponse(JsonResource|ResourceCollection|array $result, int $code, bool $make_pagination = true): JsonResponse
    {
      $responseData = $this->buildResponseData($result, $code, $make_pagination);
      return response()->json($responseData, $code);
    }

    private function buildResponseData(JsonResource|ResourceCollection|array $result, int $code, bool $make_pagination): array
    {
      // Inicializamos el arreglo de respuesta
      $response = [
        "status" => "success",
        "code" => $code,
      ];

      //Para objetos|Array creados manualmente para una respuesta específica
      if (is_array($result)) {
        $response["data"] = $result;
        return $response;
      }

      //Usado cuando se retorna un ApiResources
      switch (get_parent_class($result)) {
        case JsonResource::class:
          $response["data"] = $result->toArray(request());
          break;
        case ResourceCollection::class:
          if ($result->count() === 0) {
            $response["data"] = [];
            $response["message"] = "No se encontraron resultados";
          } else {
            $result = $this->showAllResource($result, $make_pagination);
            $response["data"] = $result["data"];
            if(array_key_exists('meta', $result)){
              $response["meta"] = $result["meta"];
            }
            if(array_key_exists('links', $result)){
              $response["links"] = $result["links"];
            }
          }
          break;
      }

      return $response;
    }

    protected function showAllResource(ResourceCollection $collection, $make_pagination): array
    {
      if ($make_pagination) $collection = $this->paginate($collection);
      $collection = $this->cacheResponse($collection);
      return $make_pagination ? $this->responsePaginateJson($collection) : ["data" => $collection];
    }

    protected function paginate(ResourceCollection $collection): LengthAwarePaginator
    {
      $rules = [
        'perPage' => 'integer|min:2|max:50'
      ];
      Validator::validate(request()->all(), $rules);

      //Identificamos en que pagina nos encontramos
      $page = LengthAwarePaginator::resolveCurrentPage();

      // Cantidad de elementos a mostrar por pagina
      $perPage = 10;
      if (request()->has('perPage')) {
        $perPage = (int)request()->perPage;
      }

      //Obtenemos los registros segun la pagina actual (0-15, 16-30, 31-45, ....etc)
      $results = $collection->slice(($page - 1) * $perPage, $perPage)->values();

      //Creamos instancia del paginador y la ruta utilizada para determinar en que pagina nos encontramos
      // Tener en cuenta que esto borraria las queryStrings existentes en la ruta al momento de paginar
      $paginated = new LengthAwarePaginator($results, $collection->count(), $perPage, $page, [
        'path' => LengthAwarePaginator::resolveCurrentPath(),
      ]);

      //Rescatamos los queryStrings existentes en la ruta, como filtros u orderBy, y los añadimos a la
      // ruta generada por el paginador
      $paginated->appends(request()->all());
      // $paginated->withQueryString();

      return $paginated;
    }

    protected function cacheResponse($data)
    {
      $url = request()->url();
      //Obtenemos los queryStrings (Parametros de la url) y las ordenamos por su key
      $queryParams = request()->query();
      ksort($queryParams);

      //Generamos un queryString ordenado y lo anexamos a la url del recurso
      $queryString = http_build_query($queryParams);
      $fullUrl = "{$url}?{$queryString}";

      //15/60 se refiere a 15 segundos,
      //Si se desea minutos, solo basta con colocar el numero, sin la division, si se desea en segundos
      // se debe dividir con 60 segundos
      return Cache::remember($fullUrl, 15 / 60, function () use ($data) {
        return $data;
      });
    }

    protected function responsePaginateJson(LengthAwarePaginator $pagination): array
    {
      return [
        'meta' => [
          'path' => $pagination->path(),
          'currentPage' => $pagination->currentPage(),
          'perPage' => $pagination->perPage(),
          'totalPages' => $pagination->lastPage(),
          'from' => $pagination->firstItem(),
          'to' => $pagination->lastItem(),
          'total' => $pagination->total(),
        ],
        'data' => $pagination->items(),
        'links' => [
          'self' => $pagination->url($pagination->currentPage()),
          'first' => $pagination->url(1),
          'last' => $pagination->url($pagination->lastPage()),
          'next' => $pagination->nextPageUrl(),
          'prev' => $pagination->previousPageUrl(),
        ],
      ];
    }

  }
