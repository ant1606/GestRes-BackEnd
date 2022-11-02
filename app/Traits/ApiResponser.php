<?php

namespace App\Traits;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\ResourceCollection;

trait ApiResponser
{
  private function successResponse($data, $code)
  {
    return response()->json($data, $code);
  }

  protected function errorResponse($message, $code)
  {
    return response()->json(['error' => $message, 'code' => $code], $code);
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

  protected function showAllResource(ResourceCollection $collection, $code)
  {
    $collection = $this->paginate($collection);

    return $this->successResponse($this->responsePaginateJson($collection), $code);
  }


  protected function paginate(ResourceCollection $collection)
  {
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
