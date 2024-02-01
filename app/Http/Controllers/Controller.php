<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *   version="1.0.0",
 *   title="Gestor Recursos API",
 * ),
 * @OA\Server (
 *   url="http://localhost:80/api/v1"
 * )
 */
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}
