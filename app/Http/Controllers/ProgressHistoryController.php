<?php

  namespace App\Http\Controllers;

  use App\Http\Requests\ProgressHistoryStoreRequest;
  use App\Http\Resources\ProgressCollection;
  use App\Http\Resources\ProgressResource;
  use App\Http\Services\ProgressHistoryService;
  use App\Models\ProgressHistory;
  use App\Models\Recourse;
  use Illuminate\Http\JsonResponse;
  use OpenApi\Annotations as OA;
  use Symfony\Component\HttpFoundation\Response;

  class ProgressHistoryController extends ApiController
  {

    public function __construct(protected ProgressHistoryService $progressService)
    {
    }

    public function index(Recourse $recourse): JsonResponse
    {
      $progressHistories = $recourse->progress()->latest()->get();
      return $this->sendResponse(new ProgressCollection($progressHistories), Response::HTTP_OK);
    }

    public function store(Recourse $recourse, ProgressHistoryStoreRequest $request): JsonResponse
    {
      $data = $this->progressService->save_progress($recourse, $request->toArray());
      return $data->isSuccess()
        ? $this->sendResponse(new ProgressResource($data->getProgress()), Response::HTTP_CREATED, false)
        : $this->sendError(Response::HTTP_UNPROCESSABLE_ENTITY, $data->getErrorMessage());
    }

    public function destroy(ProgressHistory $progressHistory): JsonResponse
    {
      $data = $this->progressService->delete_progress($progressHistory);
      return $data->isSuccess()
        ? $this->sendResponse(new ProgressResource($data->getProgress()), Response::HTTP_ACCEPTED, false)
        : $this->sendError(Response::HTTP_UNPROCESSABLE_ENTITY, $data->getErrorMessage());
    }
  }
