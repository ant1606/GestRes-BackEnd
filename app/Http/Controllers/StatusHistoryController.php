<?php

namespace App\Http\Controllers;

use App\Http\Resources\StatusCollection;
use App\Http\Resources\StatusResource;
use App\Http\Services\StatusHistoryService;
use App\Models\Recourse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\StatusHistory;
use Symfony\Component\HttpFoundation\Response;

class StatusHistoryController extends ApiController
{
  public function __construct(protected StatusHistoryService $statusService)
  {
    // $this->middleware('transform.input:' . StatusResource::class);
  }

  public function index(Recourse $recourse): JsonResponse
  {
    $statusHistories = $recourse->status()->latest()->get();
    return $this->sendResponse(new StatusCollection($statusHistories), Response::HTTP_OK);
  }

  /**
   * @throws Exception
   */
  public function store(Recourse $recourse, Request $request): JsonResponse
  {

    $data = $this->statusService->save_status($recourse, $request->toArray());
    return $this->sendResponse(new StatusResource($data), Response::HTTP_CREATED, false);
  }

  /**
   * @throws Exception
   */
  public function destroy(StatusHistory $statusHistory): JsonResponse
  {
    $data = $this->statusService->delete_status($statusHistory);
    return $this->sendResponse(new StatusResource($data), Response::HTTP_ACCEPTED, false);
  }
}
