<?php

namespace App\Exceptions;

use Exception;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Throwable;
use App\Traits\ApiResponser;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{
  use ApiResponser;
  /**
   * A list of exception types with their corresponding custom log levels.
   *
   * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
   */
  protected $levels = [
    //
  ];

  /**
   * A list of the exception types that are not reported.
   *
   * @var array<int, class-string<\Throwable>>
   */
  protected $dontReport = [
    //
  ];

  /**
   * A list of the inputs that are never flashed to the session on validation exceptions.
   *
   * @var array<int, string>
   */
  protected $dontFlash = [
    'current_password',
    'password',
    'password_confirmation',
  ];

  /**
   * Register the exception handling callbacks for the application.
   *
   * @return void
   */
  public function register()
  {
    // $this->reportable(function (Throwable $e) {
    //     dd($e);
    // });

    // $this->renderable(function (Throwable $e) {
    //     // dd($e);
    // });

    $this->renderable(function (Exception $exception, $request) {

      // dd($request->is('api/*'));
      // dd($exception instanceof NotFoundHttpException);
      dd($exception);
      // dd(get_class($exception));
      // dd($exception instanceof AuthenticationException);
      if ($request->is('api/*')) {

        // dd(get_class($exception));
        // dd($exception instanceof NotFoundHttpException);
        if ($exception instanceof ValidationException) {
          // dd($exception->validator->errors()->getMessages());
          return $this->errorResponse(
            $exception->validator->errors()->getMessages(),
            Response::HTTP_UNPROCESSABLE_ENTITY
          );
        }
        //Usado cuando un usuario intenta acceder a una ruta no existente (404)
        if ($exception instanceof NotFoundHttpException) {
          return $this->errorResponse(["api_response" => ["No se encontró el recurso"]], 404);
        }

        if ($exception instanceof ModelNotFoundException) {
          return $this->errorResponse(["api_response" => ["No se encontró el modelo"]], 404);
        }

        if ($exception instanceof AuthenticationException) {
          return $this->errorResponse(["api_response" => ["No esta autorizado para continuar"]], 404);
        }

        if ($exception instanceof MethodNotAllowedHttpException) {
          return $this->errorResponse(["api_response" => ["Método no aceptado"]], 404);
        }
        return $this->errorResponse(["api_response" => ["Hubo un problema al comunicarse con el servidor"]], 404);
      }
    });
  }

  // private function isFrontend($request)
  // {
  //     return $request->acceptsHtml() &&  collect($request->route()->middleware())->contains('web');
  // }
}
