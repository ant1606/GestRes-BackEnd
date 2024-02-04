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
    $this->renderable(function (Exception $exception, $request) {

      // dd($request->is('api/*'));
      // dd($exception instanceof NotFoundHttpException);
//       dd($exception);
      // dd(get_class($exception));
      // dd($exception instanceof AuthenticationException);
      if ($request->is('api/*')) {

        return match (true) {
          $exception instanceof ValidationException => $this->sendError(
            Response::HTTP_UNPROCESSABLE_ENTITY,
            detail: $exception->validator->errors()->getMessages(),
          ),
          $exception instanceof ModelNotFoundException, $exception instanceof NotFoundHttpException => $this->sendError(
            Response::HTTP_NOT_FOUND,
            "No se encontró el recurso"
          ),
          $exception instanceof AuthenticationException => $this->sendError(
            Response::HTTP_UNAUTHORIZED,
            "No esta autorizado para continuar"
          ),
          $exception instanceof MethodNotAllowedHttpException => $this->sendError(
            Response::HTTP_METHOD_NOT_ALLOWED,
            "Método no aceptado"
          ),
          default => $this->sendError(
            Response::HTTP_NOT_FOUND,
            "Hubo un problema al comunicarse con el servidor"
          ),
        };
      }

      return $this->sendError(
        Response::HTTP_NOT_FOUND,
        "Hubo un problema al comunicarse con el servidor"
      );

    });
  }

  // private function isFrontend($request)
  // {
  //     return $request->acceptsHtml() &&  collect($request->route()->middleware())->contains('web');
  // }
}
