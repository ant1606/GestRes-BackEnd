<?php

namespace App\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\ResourceCollection;

trait ApiResponser
{
  private function successResponse($data, $code)
  {
    return response()->json($data, $code);
  }

  protected function errorResponse($message, $code, $title = "Error")
  {
    return response()->json(
      [
        'error' =>  [
          "status" => $code,
          // "title" => $title,
          "detail" => $message
          // "source": { "pointer": "/data/attributes/firstName" },
        ]
      ],
      $code
    );
  }

  protected function showMessage($message, $code = 200)
  {
    return $this->successResponse(['data' => $message], $code);
  }

  protected function showOne($instance, $code)
  {
    return $this->successResponse(['data' => $instance], $code);
  }

  protected function showAll(Collection $collection, $code)
  {
    // $collection = $this->paginate($collection);

    return $this->successResponse(['data' => $collection], $code);
  }

  //TODO Cambiar el contenido de showAll por showAllResource en los controladores
  protected function showAllResource(ResourceCollection $collection, $code)
  {
    if ($collection->count() === 0)
      return $this->successResponse(
        ['data' => [], 'message' => "No se encontraron resultados"],
        Response::HTTP_OK
      );

    $collection = $this->paginate($collection);
    $collection = $this->cacheResponse($collection);
    // dd($collection);
    return $this->successResponse($this->responsePaginateJson($collection), $code);
  }

  protected function paginate(ResourceCollection $collection)
  {

    if ($collection->isEmpty()) {
      return $this->successResponse(['data' => $collection], 200);
    }

    $rules = [
      'per_page' => 'integer|min:2|max:50'
    ];
    Validator::validate(request()->all(), $rules);

    //Identificamos en que pagina nos encontramos
    $page = LengthAwarePaginator::resolveCurrentPage();

    // Cantidad de elementos a mostrar por pagina
    $perPage = 3;
    if (request()->has('per_page')) {
      $perPage = (int) request()->per_page;
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

  protected function responsePaginateJson(LengthAwarePaginator $pagination)
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
