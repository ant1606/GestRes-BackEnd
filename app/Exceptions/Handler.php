<?php

namespace App\Exceptions;

use App\Traits\ApiResponser;
use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

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



            // dd($request);
            if ($request->is('api/*')) {

                if ($exception instanceof ValidationException) {
                    return $this->errorResponse(
                        $exception->validator->errors()->getMessages(),
                        Response::HTTP_UNPROCESSABLE_ENTITY
                    );
                }
            }
        });
    }

    // private function isFrontend($request)
    // {
    //     return $request->acceptsHtml() &&  collect($request->route()->middleware())->contains('web');
    // }
}
