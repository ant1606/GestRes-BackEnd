<?php

namespace App\Traits;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;

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

  /**
   * Store a newly created resource in storage.
   *
   * @param  Illuminate\Database\Eloquent\Model  $instance
   * @param  Symfony\Component\HttpFoundation\Response  $code
   * @return Json
   */
  protected function showOne(Model $instance, $code)
  {
    return $this->successResponse(['data' => $instance], $code);
  }

  protected function showAll(Collection $collection, $code)
  {
    return $this->successResponse(['data' => $collection], $code);
  }
}
